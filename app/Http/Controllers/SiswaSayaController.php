<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\AngkatanSiswa;
use App\Models\Periode;
use App\Services\AuthenticatedGuruService;

class SiswaSayaController extends Controller
{
    public function __construct(
        protected AuthenticatedGuruService $guruService
    ) {}

    public function index(Request $request)
    {
        // Guru pengguna saat ini (cari via NIG=username, fallback via nama)
        $guru = $this->guruService->fromAuthUser();

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
        $guru = $this->guruService->fromAuthUser();

        $siswa = Siswa::with(['angkatan.kelas', 'angkatan.periode', 'tagihans.jenisTagihan', 'nilais.pelajaran', 'kehadiran'])->findOrFail($siswa);

        // Hanya wali kelas yang sah (wali kelas dari salah satu kelas murid ini) yang boleh mengakses detail.
        if (!$guru) {
            abort(403, 'Anda tidak memiliki akses ke data siswa ini.');
        }

        $kelasIdsSiswa = $siswa->angkatan->pluck('kelas_id');
        $kelasWaliIds = Kelas::where('wali_kelas_id', $guru->id)->pluck('id');

        if ($kelasIdsSiswa->intersect($kelasWaliIds)->isEmpty()) {
            abort(403, 'Anda bukan wali kelas dari siswa ini.');
        }

        $tagihans = $siswa->tagihans;
        $nilai = $siswa->nilais;
        $kehadiran = $siswa->kehadiran;

        return view('siswa-saya-detail', compact('siswa', 'tagihans', 'nilai', 'kehadiran'));
    }
}
