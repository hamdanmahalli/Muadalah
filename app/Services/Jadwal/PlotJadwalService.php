<?php

namespace App\Services\Jadwal;

use App\Models\Guru;
use App\Models\HariOperasional;
use App\Models\JadwalHarian;
use App\Models\Pelajaran;
use App\Models\PlotJadwal;
use App\Services\MutasiLogService;

/**
 * Pengelola Target Mengajar (Plot Jadwal) & sinkronisasi jadwal harian.
 *
 * SRP: Satu tanggung jawab — menyimpan plot target mengajar beserta seluruh
 * validasi (overload, bentrok guru) dan sinkronisasi jadwal harian + riwayat mutasi.
 * Stateless: menerima input, mengembalikan hasil terstruktur.
 */
class PlotJadwalService
{
    public function __construct(private MutasiLogService $logService) {}

    /**
     * Simpan target mengajar untuk kelas+pelajaran.
     *
     * @return array{status: string, pesan?: string, ...} hasil untuk UI
     */
    public function simpan(int $kelasId, int $pelajaranId, int $bebanJam, ?int $guruId, bool $forceUpdate, ?string $tahunAjaran): array
    {
        $kapasitasMaksimal = HariOperasional::where('is_active', true)->sum('max_jam');

        // 1. CEK OVERLOAD KAPASITAS
        $existingPlots = PlotJadwal::where('kelas_id', $kelasId)->get();
        $totalLain = $existingPlots->where('pelajaran_id', '!=', $pelajaranId)->sum('beban_jam');
        $totalPlotBaru = $totalLain + $bebanJam;

        if ($totalPlotBaru > $kapasitasMaksimal) {
            return [
                'status' => 'error_overload',
                'pesan' => "<b>KAPASITAS OVERLOAD!</b><br>Total beban jam ({$totalPlotBaru} Jam) melebihi kapasitas kelas ({$kapasitasMaksimal} Jam).",
            ];
        }

        // 2. CEK BENTROK GURU
        $konfirmasiBentrok = [];
        $jadwalDihapus = [];

        if ($guruId) {
            $jadwalEksisting = JadwalHarian::where('kelas_id', $kelasId)
                                           ->where('pelajaran_id', $pelajaranId);
            if ($tahunAjaran) {
                $jadwalEksisting->where('tahun_ajaran', $tahunAjaran);
            }
            $jadwalEksisting = $jadwalEksisting->get();

            foreach ($jadwalEksisting as $jadwal) {
                $bentrok = JadwalHarian::with('kelas')
                                       ->where('hari', $jadwal->hari)
                                       ->where('jam_ke', $jadwal->jam_ke)
                                       ->where('tahun_ajaran', $tahunAjaran)
                                       ->where('guru_id', $guruId)
                                       ->where('kelas_id', '!=', $kelasId)
                                       ->first();

                if ($bentrok) {
                    $guruInfo = Guru::find($guruId);
                    $pelajaranInfo = Pelajaran::find($pelajaranId);

                    if (!$forceUpdate) {
                        $konfirmasiBentrok[] = "Hari <b>{$jadwal->hari} Jam Ke-{$jadwal->jam_ke}</b>: Ust. {$guruInfo->nama_guru} sedang mengajar di <b>Kelas {$bentrok->kelas->nama_kelas}</b>.<br><span class='text-red-600 font-bold text-xs'>➜ Jadwal {$pelajaranInfo->nama_pelajaran} di kelas ini akan DIHAPUS.</span>";
                    } else {
                        $jadwalDihapus[] = $jadwal->id;
                    }
                }
            }
        }

        if (count($konfirmasiBentrok) > 0 && !$forceUpdate) {
            return [
                'status' => 'error_bentrok',
                'pesan' => "Guru yang Anda pilih mengalami bentrok jadwal. Apakah Anda setuju MENGHAPUS jadwal pelajaran (milik guru sebelumnya) di kelas ini?",
                'rincian' => $konfirmasiBentrok,
                'pelajaran_id' => $pelajaranId,
                'guru_id' => $guruId,
                'beban_jam' => $bebanJam,
            ];
        }

        // 3. EKSEKUSI HAPUS JADWAL BENTROK (JIKA TU SETUJU)
        if (count($jadwalDihapus) > 0) {
            JadwalHarian::whereIn('id', $jadwalDihapus)->delete();
        }

        // 3b. Catat guru lama sebelum diubah (untuk riwayat mutasi)
        $plotLama = PlotJadwal::where('kelas_id', $kelasId)
                              ->where('pelajaran_id', $pelajaranId)
                              ->first();
        $guruLamaId = $plotLama?->guru_id;

        // 4. SIMPAN TARGET MENGAJAR (PLOT)
        PlotJadwal::updateOrCreate(
            ['kelas_id' => $kelasId, 'pelajaran_id' => $pelajaranId],
            ['guru_id' => $guruId, 'beban_jam' => $bebanJam]
        );

        // 5. SINKRONKAN JADWAL HARIAN
        $queryJadwal = JadwalHarian::where('kelas_id', $kelasId)
                                   ->where('pelajaran_id', $pelajaranId);
        if ($tahunAjaran) {
            $queryJadwal->where('tahun_ajaran', $tahunAjaran);
        }
        $queryJadwal->update(['guru_id' => $guruId]);

        // 6. HITUNG STATISTIK BARU UNTUK UI
        $allPlots = PlotJadwal::where('kelas_id', $kelasId)->get();
        $totalTarget = $allPlots->sum('beban_jam');
        $totalTerjadwal = JadwalHarian::where('kelas_id', $kelasId)
                                      ->where('tahun_ajaran', $tahunAjaran)
                                      ->count();
        $terjadwalMapel = JadwalHarian::where('kelas_id', $kelasId)
                                      ->where('pelajaran_id', $pelajaranId)
                                      ->where('tahun_ajaran', $tahunAjaran)
                                      ->count();

        // 6b. REKAM RIWAYAT perubahan guru di plot (Mutasi)
        if ($guruLamaId != $guruId) {
            $this->logService->catat([
                'kelas_id'        => $kelasId,
                'pelajaran_id'    => $pelajaranId,
                'guru_lama_id'    => $guruLamaId,
                'guru_baru_id'    => $guruId,
                'tipe'            => 'plot_sync',
                'tanggal_efektif' => now()->format('Y-m-d'),
                'keterangan'      => 'Guru diubah di halaman Plotting Target Mengajar',
            ]);
        }

        return [
            'status' => 'success',
            'kapasitasMaksimal' => $kapasitasMaksimal,
            'totalTarget' => $totalTarget,
            'totalTerjadwal' => $totalTerjadwal,
            'sisaBelum' => $totalTarget - $totalTerjadwal,
            'terjadwalMapel' => $terjadwalMapel,
            'beban_jam' => $bebanJam,
            'pelajaran_id' => $pelajaranId,
        ];
    }

    /**
     * Daftar guru pengganti untuk mutasi plot (Aktif, kecuali guru yang menjabat).
     */
    public function daftarGuruPengganti(?int $kecualiId = null)
    {
        return Guru::where('status', 'Aktif')
            ->when($kecualiId, fn ($q) => $q->where('id', '!=', $kecualiId))
            ->orderBy('nama_guru', 'asc')
            ->get();
    }
}
