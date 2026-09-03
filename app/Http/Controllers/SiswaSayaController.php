<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Guru;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\AngkatanSiswa;
use App\Models\Periode;

class SiswaSayaController extends Controller
{
    public function index(Request $request)
    {
        // Guru pengguna saat ini (cari via NIG=username, fallback via nama)
        $user = auth()->user();
        $guru = Guru::where('nig', $user->username)->first()
            ?? $user->guru
            ?? Guru::where('nama_guru', $user->name)->first();

        // Periode aktif
        $periode = Periode::where('is_active', true)->first();
        $periodeId = $request->periode_id ?? ($periode?->id);

        // Kelas yang diampu sebagai wali kelas
        $kelasWali = $guru ? Kelas::where('wali_kelas_id', $guru->id)->get() : collect();

        $kelasId = $request->kelas_id ?? $kelasWali->first()?->id;

        $siswas = collect();
        if ($kelasId) {
            $ids = AngkatanSiswa::where('kelas_id', $kelasId)
                ->when($periodeId, fn($q) => $q->where('periode_id', $periodeId))
                ->pluck('siswa_id');
            $siswas = Siswa::with(['tagihans.jenisTagihan', 'angkatan.periode'])
                ->whereIn('id', $ids)
                ->get()
                ->sortBy(fn($s) => $s->angkatan
                    ->where('kelas_id', $kelasId)
                    ->sortByDesc('periode.is_active')
                    ->first()?->nomor_absen ?? PHP_INT_MAX);
        }

        return view('siswa-saya', compact('kelasWali', 'kelasId', 'siswas', 'periode', 'periodeId'));
    }

    public function detail($siswa)
    {
        $siswa = Siswa::with(['angkatan.kelas', 'angkatan.periode', 'tagihans.jenisTagihan', 'nilais.pelajaran', 'kehadiran'])->findOrFail($siswa);
        $tagihans = $siswa->tagihans;
        $nilai = $siswa->nilais;
        $kehadiran = $siswa->kehadiran;

        return view('siswa-saya-detail', compact('siswa', 'tagihans', 'nilai', 'kehadiran'));
    }
}
