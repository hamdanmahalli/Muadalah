<?php

namespace App\Services;

use App\Models\AngkatanSiswa;
use App\Models\KehadiranSiswa;
use App\Models\Siswa;
use Illuminate\Support\Facades\DB;

/**
 * Operasi absensi siswa (bulk input & data cetak rekap).
 *
 * SRP: Satu tanggung jawab — menyimpan status absensi siswa massal dan
 * menyusun data untuk cetak rekap mingguan. Stateless.
 */
class KehadiranSiswaService
{
    private const STATUS_VALID = ['hadir', 'sakit', 'izin', 'alpha'];

    /**
     * Simpan status absensi massal per siswa (updateOrCreate) dalam satu transaksi.
     *
     * @param array<string, string> $statusBySiswaId peta siswa_id => status
     * @param array<string, string|null> $keterangan peta siswa_id => keterangan
     */
    public function simpanBulk(string $tanggal, int $kelasId, ?int $periodeId, array $statusBySiswaId, array $keterangan, ?int $userId): int
    {
        return DB::transaction(function () use ($tanggal, $kelasId, $periodeId, $statusBySiswaId, $keterangan, $userId) {
            $count = 0;
            foreach ($statusBySiswaId as $siswaId => $status) {
                if (!in_array($status, self::STATUS_VALID)) {
                    continue;
                }
                KehadiranSiswa::updateOrCreate(
                    ['siswa_id' => $siswaId, 'tanggal' => $tanggal],
                    [
                        'periode_id' => $periodeId,
                        'kelas_id' => $kelasId,
                        'status' => $status,
                        'keterangan' => $keterangan[$siswaId] ?? null,
                        'user_id' => $userId,
                    ]
                );
                $count++;
            }
            return $count;
        });
    }

    /**
     * Daftar siswa aktif pada kelas (diurutkan nomor absen) untuk rekap.
     */
    public function dataSiswaKelas(int $kelasId): \Illuminate\Support\Collection
    {
        return Siswa::whereHas('angkatan', fn ($q) => $q->where('kelas_id', $kelasId))
            ->with(['angkatan' => fn ($q) => $q->where('kelas_id', $kelasId)])
            ->where('status', 'Aktif')
            ->orderBy('nama_siswa', 'asc')->get()
            ->sortBy(fn ($s) => $s->angkatan->first()?->nomor_absen ?? PHP_INT_MAX)
            ->values();
    }
}
