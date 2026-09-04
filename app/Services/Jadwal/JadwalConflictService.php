<?php

namespace App\Services\Jadwal;

use App\Models\JadwalHarian;
use Illuminate\Support\Collection;

/**
 * Deteksi konflik jadwal (bentrok guru & slot kelas) dan kuota target plot.
 *
 * SRP: Satu tanggung jawab — menjawab pertanyaan "apakah ada konflik?"
 * tanpa menyimpan/memodifikasi data. Stateless sehingga mudah di-unit-test.
 */
class JadwalConflictService
{
    /**
     * Cari jadwal bentrok guru di kelas LAIN pada hari & blok jam tertentu.
     */
    public function bentrokGuruDiKelasLain(
        int $guruId,
        string $hari,
        array $jamKe,
        string $tahunAjaran,
        int $kecualiKelasId
    ): Collection {
        return JadwalHarian::with('kelas')
            ->where('hari', $hari)
            ->whereIn('jam_ke', $jamKe)
            ->where('tahun_ajaran', $tahunAjaran)
            ->where('guru_id', $guruId)
            ->where('kelas_id', '!=', $kecualiKelasId)
            ->get();
    }

    /**
     * Cari seluruh jadwal pengisi slot kelas pada hari & blok jam tertentu.
     */
    public function slotTerisiKelas(
        int $kelasId,
        string $hari,
        array $jamKe,
        string $tahunAjaran
    ): Collection {
        return JadwalHarian::with(['guru', 'pelajaran'])
            ->where('kelas_id', $kelasId)
            ->where('hari', $hari)
            ->whereIn('jam_ke', $jamKe)
            ->where('tahun_ajaran', $tahunAjaran)
            ->get();
    }

    /**
     * Ambil plot jadwal (target mengajar) untuk kombinasi kelas + pelajaran.
     */
    public function plotUntuk(\App\Models\Kelas|int $kelasId, int $pelajaranId): ?\App\Models\PlotJadwal
    {
        return \App\Models\PlotJadwal::where('kelas_id', $kelasId)
            ->where('pelajaran_id', $pelajaranId)
            ->first();
    }

    /**
     * Jumlah sesi yang sudah dipakai di luar blok jam yang sedang diisikan
     * untuk kombinasi kelas + pelajaran pada tahun ajaran tertentu.
     */
    public function sesiTerpakaiDiLuar(
        int $kelasId,
        int $pelajaranId,
        string $tahunAjaran,
        array $jamKe
    ): int {
        return JadwalHarian::where('kelas_id', $kelasId)
            ->where('pelajaran_id', $pelajaranId)
            ->where('tahun_ajaran', $tahunAjaran)
            ->whereNotIn('jam_ke', $jamKe)
            ->count();
    }
}
