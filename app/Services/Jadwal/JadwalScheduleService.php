<?php

namespace App\Services\Jadwal;

use App\Models\Guru;
use App\Models\JadwalHarian;
use App\Models\PlotJadwal;
use App\Models\Periode;
use App\Services\MutasiLogService;

/**
 * Operasi penyimpanan & penghapusan jadwal harian beserta pencatatan riwayat mutasi.
 *
 * SRP: Satu tanggung jawab — mempersistenkan perubahan jadwal (create/update/destroy)
 * dan merekam log mutasi. Dikonsumsi controller sebagai orkestrator.
 */
class JadwalScheduleService
{
    public function __construct(private MutasiLogService $logService) {}

    /**
     * Simpan jadwal untuk rentang blok jam (updateOrCreate).
     *
     * @param array $data berisi kelas_id, hari, jam_ke[], pelajaran_id, guru_id
     */
    public function simpan(array $data, string $tahunAjaran): void
    {
        foreach ($data['jam_ke'] as $jamKe) {
            JadwalHarian::updateOrCreate(
                [
                    'kelas_id' => $data['kelas_id'],
                    'hari' => $data['hari'],
                    'jam_ke' => (int) $jamKe,
                    'tahun_ajaran' => $tahunAjaran,
                ],
                [
                    'pelajaran_id' => $data['pelajaran_id'],
                    'guru_id' => $data['guru_id'],
                ]
            );
        }
    }

    /**
     * Hapus slot jadwal beserta pasangan blok jamnya, lalu catat log.
     */
    public function destroyJadwal(int $jadwalId): bool
    {
        $jadwal = JadwalHarian::find($jadwalId);
        if (!$jadwal) {
            return false;
        }

        $jamKe = $jadwal->jam_ke;
        $pasanganJam = ($jamKe % 2 == 1) ? [$jamKe, $jamKe + 1] : [$jamKe - 1, $jamKe];

        JadwalHarian::where('kelas_id', $jadwal->kelas_id)
            ->where('hari', $jadwal->hari)
            ->whereIn('jam_ke', $pasanganJam)
            ->where('tahun_ajaran', $jadwal->tahun_ajaran)
            ->delete();

        $this->logService->catat([
            'kelas_id'     => $jadwal->kelas_id,
            'pelajaran_id' => $jadwal->pelajaran_id,
            'hari'         => $jadwal->hari,
            'jam_ke'       => $jadwal->jam_ke,
            'guru_lama_id' => $jadwal->guru_id,
            'guru_baru_id' => null,
            'tipe'         => 'hapus_slot',
            'keterangan'   => 'Slot jadwal dikosongkan (blok jam ke-' . min($pasanganJam) . '/' . max($pasanganJam) . ')',
        ]);

        return true;
    }

    /**
     * Mutasi guru per-slot dengan tanggal efektif (clone + masa berlaku).
     */
    public function mutasiGuruPerSlot(int $jadwalId, int $guruBaruId, string $tanggalEfektif): JadwalHarian
    {
        $jadwalLama = JadwalHarian::findOrFail($jadwalId);
        $kemarin = \Carbon\Carbon::parse($tanggalEfektif)->subDay()->format('Y-m-d');

        return \Illuminate\Support\Facades\DB::transaction(function () use ($jadwalLama, $guruBaruId, $tanggalEfektif, $kemarin) {
            $jadwalLama->update(['berlaku_sampai' => $kemarin]);

            $jadwalBaru = $jadwalLama->replicate();
            $jadwalBaru->guru_id = $guruBaruId;
            $jadwalBaru->berlaku_mulai = $tanggalEfektif;
            $jadwalBaru->berlaku_sampai = null;
            $jadwalBaru->save();

            $this->logService->catat([
                'kelas_id'         => $jadwalLama->kelas_id,
                'pelajaran_id'     => $jadwalLama->pelajaran_id,
                'hari'             => $jadwalLama->hari,
                'jam_ke'           => $jadwalLama->jam_ke,
                'guru_lama_id'     => $jadwalLama->guru_id,
                'guru_baru_id'     => $guruBaruId,
                'tipe'             => 'ganti_guru',
                'tanggal_efektif'  => $tanggalEfektif,
                'keterangan'       => 'Mutasi per-slot dengan tanggal efektif',
            ]);

            return $jadwalBaru;
        });
    }

    /**
     * Hapus jadwal lama & pindahkan guru pada plot (mutasi massal dari plot).
     */
    public function mutasiGuruPlot(int $plotId, int $guruBaruId): PlotJadwal
    {
        $plot = PlotJadwal::findOrFail($plotId);
        $guruLamaId = $plot->guru_id;

        return \Illuminate\Support\Facades\DB::transaction(function () use ($plot, $guruLamaId, $guruBaruId) {
            JadwalHarian::where('kelas_id', $plot->kelas_id)
                ->where('pelajaran_id', $plot->pelajaran_id)
                ->where('guru_id', $guruLamaId)
                ->delete();

            $plot->update(['guru_id' => $guruBaruId]);

            $this->logService->catat([
                'kelas_id'        => $plot->kelas_id,
                'pelajaran_id'    => $plot->pelajaran_id,
                'guru_lama_id'    => $guruLamaId,
                'guru_baru_id'    => $guruBaruId,
                'tipe'            => 'ganti_guru',
                'tanggal_efektif' => now()->format('Y-m-d'),
                'keterangan'      => 'Mutasi massal dari Plot Target Mengajar',
            ]);

            return $plot;
        });
    }

    /**
     * Daftar guru pengganti (status Aktif, kecuali guru yang sedang menjabat).
     */
    public function daftarGuruPengganti(?int $kecualiId = null)
    {
        $query = Guru::where('status', 'Aktif');
        if ($kecualiId) {
            $query->where('id', '!=', $kecualiId);
        }
        return $query->orderBy('nama_guru', 'asc')->get();
    }
}
