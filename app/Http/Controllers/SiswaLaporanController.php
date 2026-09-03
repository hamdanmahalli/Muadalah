<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\Periode;
use App\Models\Tagihan;
use App\Models\AngkatanSiswa;

class SiswaLaporanController extends Controller
{
    public function index()
    {
        $kelas = Kelas::orderBy('nama_kelas', 'asc')->get();
        $periodes = Periode::orderBy('tahun_ajaran', 'desc')->get();
        $aktif = Periode::where('is_active', true)->first();
        return view('laporan-siswa.index', compact('kelas', 'periodes', 'aktif'));
    }

    // Buku induk per kelas (atau semua)
    public function bukuInduk(Request $request)
    {
        $kelasId = $request->kelas_id;
        $periodeId = $request->periode_id;

        $siswas = Siswa::with('angkatan.kelas')
            ->where('status', 'Aktif')
            ->when($kelasId, function ($q) use ($kelasId, $periodeId) {
                return $q->whereHas('angkatan', fn($w) =>
                    $w->where('kelas_id', $kelasId)
                      ->when($periodeId, fn($p) => $p->where('periode_id', $periodeId))
                );
            })
            ->orderBy('nama_siswa', 'asc')
            ->get();

        $kelas = $kelasId ? Kelas::find($kelasId) : null;
        $periode = $periodeId ? Periode::find($periodeId) : null;

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.buku-induk', compact('siswas', 'kelas', 'periode'))
            ->setPaper('a4', 'landscape');
        return $pdf->download('buku-induk-murid.pdf');
    }

    public function bukuIndukSiswa($siswa)
    {
        $siswa = Siswa::with(['angkatan.kelas', 'angkatan.periode', 'nilais.pelajaran'])->findOrFail($siswa);
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.buku-induk-siswa', compact('siswa'))
            ->setPaper('a4');
        return $pdf->download('buku-induk-' . $siswa->nis . '.pdf');
    }

    public function rekapPembayaran(Request $request)
    {
        $tagihans = Tagihan::with(['siswa', 'jenisTagihan', 'periode'])
            ->latest()
            ->when($request->periode_id, fn($q) => $q->where('periode_id', $request->periode_id))
            ->when($request->jenis_tagihan_id, fn($q) => $q->where('jenis_tagihan_id', $request->jenis_tagihan_id))
            ->get();

        $periode = $request->periode_id ? Periode::find($request->periode_id) : null;

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.rekap-pembayaran', compact('tagihans', 'periode'))
            ->setPaper('a4', 'landscape');
        return $pdf->download('rekap-pembayaran.pdf');
    }
}
