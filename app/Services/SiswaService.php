<?php

namespace App\Services;

use App\Models\AngkatanSiswa;
use App\Models\Siswa;

/**
 * Operasi data siswa & penempatannya di kelas (AngkatanSiswa).
 *
 * SRP: Satu tanggung jawab — mengelola data Siswa dan relasi penempatannya.
 * Stateless; logika nomor absen dipusatkan agar konsisten.
 */
class SiswaService
{
    /**
     * NIS baru terurut (NIS terbesar + 1, atau default 1001).
     */
    public function generasikanNIS(): string
    {
        $lastSiswa = Siswa::orderBy('nis', 'desc')->first();
        return ($lastSiswa && is_numeric($lastSiswa->nis))
            ? (string) ((int) $lastSiswa->nis + 1)
            : '1001';
    }

    /**
     * Tempatkan siswa ke kelas untuk periode tertentu dengan nomor absen otomatis.
     */
    public function tempatkan(int $siswaId, int $kelasId, ?int $periodeId, array $opsi = []): AngkatanSiswa
    {
        $angkatan = AngkatanSiswa::firstOrNew([
            'siswa_id' => $siswaId,
            'periode_id' => $periodeId,
        ]);

        // Nomor absen TETAP selama satu tahun ajaran: hanya diisi jika belum ada
        if (!$angkatan->exists || $angkatan->nomor_absen === null) {
            $max = AngkatanSiswa::where('kelas_id', $kelasId)
                ->where('periode_id', $periodeId)
                ->max('nomor_absen');
            $angkatan->nomor_absen = $max ? (int) $max + 1 : 1;
        }

        $angkatan->kelas_id      = $kelasId;
        $angkatan->status        = $opsi['status'] ?? $angkatan->status ?? 'Aktif';
        $angkatan->tanggal_masuk = $opsi['tanggal_masuk'] ?? $angkatan->tanggal_masuk;
        $angkatan->save();

        return $angkatan;
    }
}
