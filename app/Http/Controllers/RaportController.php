<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Nilai;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\Periode;
use App\Models\AngkatanSiswa;

class RaportController extends Controller
{
    public function index(Request $request)
    {
        $kelas = Kelas::orderBy('nama_kelas', 'asc')->get();
        $periodes = Periode::orderBy('tahun_ajaran', 'desc')->get();
        $aktif = Periode::where('is_active', true)->first();
        $periodeId = $request->periode_id ?? ($aktif ? $aktif->id : null);
        $kelasId = $request->kelas_id;

        $siswas = collect();
        if ($kelasId) {
            $ids = AngkatanSiswa::where('kelas_id', $kelasId)
                ->when($periodeId, fn($q) => $q->where('periode_id', $periodeId))
                ->pluck('siswa_id');
            $siswas = Siswa::whereIn('id', $ids)->orderBy('nama_siswa', 'asc')->get();
        }

        return view('raport.index', compact('kelas', 'periodes', 'periodeId', 'kelasId', 'siswas'));
    }

    public function cetak(Request $request, $siswa)
    {
        $siswa = Siswa::findOrFail($siswa);
        $periodeId = $request->periode_id;
        $periode = Periode::find($periodeId);

        $angkatan = $siswa->angkatan()->when($periodeId, fn($q) => $q->where('periode_id', $periodeId))->latest()->first();
        $kelas = $angkatan?->kelas;

        $nilai = Nilai::with('pelajaran')
            ->where('siswa_id', $siswa->id)
            ->when($periodeId, fn($q) => $q->where('periode_id', $periodeId))
            ->get();

        $jumlah = $nilai->count();
        $rataRata = $jumlah ? round($nilai->avg('nilai_akhir'), 2) : 0;
        $tertinggi = $nilai->max('nilai_akhir');
        $terendah = $nilai->min('nilai_akhir');

        // Rekap absensi periode
        $kehadiran = \App\Models\KehadiranSiswa::where('siswa_id', $siswa->id)
            ->when($periodeId, fn($q) => $q->where('periode_id', $periodeId))
            ->get()
            ->groupBy('status')
            ->map->count();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.raport', compact(
            'siswa', 'periode', 'kelas', 'nilai', 'jumlah', 'rataRata', 'tertinggi', 'terendah', 'kehadiran'
        ))->setPaper('a4');
        return $pdf->download('raport-' . $siswa->nis . '.pdf');
    }
}
