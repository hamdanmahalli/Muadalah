<?php

namespace App\Services;

use App\Models\MutasiJadwal;
use App\Models\Periode;

class MutasiLogService
{
    /**
     * Catat satu kejadian perubahan jadwal (mutasi / tukar jam / dll).
     *
     * @param  array  $data  kombinasi: kelas_id, pelajaran_id, hari, jam_ke, jadwal_id,
     *                       guru_lama_id, guru_baru_id, tipe, tanggal_efektif, keterangan
     */
    public static function catat(array $data): ?MutasiJadwal
    {
        $periode = get_periode_aktif();

        try {
            $log = MutasiJadwal::create([
                'periode_id'       => $data['periode_id'] ?? ($periode->id ?? null),
                'tahun_ajaran'     => $data['tahun_ajaran'] ?? ($periode->tahun_ajaran ?? null),
                'kelas_id'         => $data['kelas_id'] ?? null,
                'pelajaran_id'     => $data['pelajaran_id'] ?? null,
                'hari'             => $data['hari'] ?? null,
                'jam_ke'           => $data['jam_ke'] ?? null,
                'jadwal_id'        => $data['jadwal_id'] ?? null,
                'guru_lama_id'     => $data['guru_lama_id'] ?? null,
                'guru_baru_id'     => $data['guru_baru_id'] ?? null,
                'tipe'             => $data['tipe'] ?? 'ganti_guru',
                'tanggal_kejadian' => $data['tanggal_kejadian'] ?? now()->format('Y-m-d'),
                'tanggal_efektif'  => $data['tanggal_efektif'] ?? null,
                'keterangan'       => $data['keterangan'] ?? null,
                'user_id'          => $data['user_id'] ?? auth()->id(),
            ]);
        } catch (\Throwable $e) {
            // Pencatatan riwayat tidak boleh menggagalkan operasi mutasi utama.
            report($e);
            return null;
        }

        return $log;
    }
}
