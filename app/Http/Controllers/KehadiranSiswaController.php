<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\KehadiranSiswa;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\Periode;
use App\Models\AngkatanSiswa;

class KehadiranSiswaController extends Controller
{
    public function index(Request $request)
    {
        $kelas = Kelas::orderBy('nama_kelas', 'asc')->get();
        $periodes = Periode::orderBy('tahun_ajaran', 'desc')->get();
        $aktif = Periode::where('is_active', true)->first();

        $periodeId = $request->periode_id ?? ($aktif ? $aktif->id : null);
        $kelasId = $request->kelas_id;
        $tanggal = $request->tanggal ?? now()->format('Y-m-d');

        $siswas = collect();
        if ($kelasId) {
            $siswaIds = AngkatanSiswa::where('kelas_id', $kelasId)
                ->when($periodeId, fn($q) => $q->where('periode_id', $periodeId))
                ->pluck('siswa_id');
            $siswas = Siswa::whereIn('id', $siswaIds)
                ->with(['angkatan' => fn($q) => $q->where('kelas_id', $kelasId)])
                ->get()
                ->sortBy(fn($s) => $s->angkatan->first()?->nomor_absen ?? PHP_INT_MAX);
        }

        $kehadiranMap = KehadiranSiswa::where('tanggal', $tanggal)
            ->when($kelasId, fn($q) => $q->where('kelas_id', $kelasId))
            ->when($periodeId, fn($q) => $q->where('periode_id', $periodeId))
            ->get()->keyBy('siswa_id');

        return view('absen-siswa', compact('kelas', 'periodes', 'periodeId', 'kelasId', 'tanggal', 'siswas', 'kehadiranMap'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'kelas_id' => 'required|exists:kelas,id',
        ]);

        $count = 0;
        foreach (($request->status ?? []) as $siswaId => $status) {
            if (!in_array($status, ['hadir', 'sakit', 'izin', 'alpha'])) continue;
            KehadiranSiswa::updateOrCreate(
                ['siswa_id' => $siswaId, 'tanggal' => $request->tanggal],
                [
                    'periode_id' => $request->periode_id,
                    'kelas_id' => $request->kelas_id,
                    'status' => $status,
                    'keterangan' => $request->keterangan[$siswaId] ?? null,
                    'user_id' => auth()->id(),
                ]
            );
            $count++;
        }

        return redirect()->back()->with('sukses', "Absensi {$count} siswa tersimpan untuk tanggal {$request->tanggal}.");
    }

    public function cetak(Request $request)
    {
        $kelasId = $request->kelas_id;
        $tanggalAwal = $request->tanggal_awal ?? now()->startOfWeek()->format('Y-m-d');
        $tanggalAkhir = $request->tanggal_akhir ?? now()->format('Y-m-d');

        $kelas = Kelas::findOrFail($kelasId);
        $siswas = Siswa::whereHas('angkatan', fn($q) => $q->where('kelas_id', $kelasId))
            ->with(['angkatan' => fn($q) => $q->where('kelas_id', $kelasId)])
            ->where('status', 'Aktif')
            ->orderBy('nama_siswa', 'asc')->get()
            ->sortBy(fn($s) => $s->angkatan->first()?->nomor_absen ?? PHP_INT_MAX);

        $kehadiran = KehadiranSiswa::where('kelas_id', $kelasId)
            ->whereBetween('tanggal', [$tanggalAwal, $tanggalAkhir])
            ->get()
            ->groupBy('siswa_id');

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.absen-mingguan', compact('kelas', 'siswas', 'kehadiran', 'tanggalAwal', 'tanggalAkhir'))
            ->setPaper('a4', 'landscape');
        return $pdf->download('absen-mingguan-' . $kelas->nama_kelas . '.pdf');
    }
}
