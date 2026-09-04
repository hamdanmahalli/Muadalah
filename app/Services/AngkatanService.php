<?php

namespace App\Services;

use App\Models\AngkatanSiswa;
use App\Models\Kelas;
use App\Models\Periode;
use App\Models\Siswa;
use Illuminate\Support\Facades\DB;

/**
 * Operasi penempatan siswa ke kelas (AngkatanSiswa) & nomor absen.
 *
 * SRP: Satu tanggung jawab — pengelolaan penempatan siswa beserta nomor
 * absen auto-increment & rantai pergeseran agar selalu unik. Stateless.
 */
class AngkatanService
{
    /**
     * Nomor absen berikutnya (tertinggi + 1) dalam kelas & periode tertentu.
     */
    public function nomorAbsenBerikutnya(int $kelasId, ?int $periodeId): int
    {
        $max = AngkatanSiswa::where('kelas_id', $kelasId)
            ->when($periodeId, fn ($q) => $q->where('periode_id', $periodeId))
            ->max('nomor_absen');

        return $max ? (int) $max + 1 : 1;
    }

    /**
     * Tempatkan siswa ke kelas (firstOrNew + nomor absen otomatis bila kosong).
     */
    public function tempatkan(int $siswaId, int $kelasId, ?int $periodeId, array $opsi = []): AngkatanSiswa
    {
        $angkatan = AngkatanSiswa::firstOrNew([
            'siswa_id'   => $siswaId,
            'periode_id' => $periodeId,
        ]);

        if (!$angkatan->exists || $angkatan->nomor_absen === null) {
            $angkatan->nomor_absen = $this->nomorAbsenBerikutnya($kelasId, $periodeId);
        }

        $angkatan->kelas_id      = $kelasId;
        $angkatan->status        = $opsi['status'] ?? 'Aktif';
        $angkatan->tanggal_masuk = $opsi['tanggal_masuk'] ?? $angkatan->tanggal_masuk ?? now();
        $angkatan->save();

        return $angkatan;
    }

    /**
     * Tempatkan otomatis hingga 50 siswa aktif (belum di kelas ini) sekaligus.
     */
    public function autoPlace(int $kelasId, ?int $periodeId): int
    {
        $sudah = AngkatanSiswa::where('kelas_id', $kelasId)
            ->when($periodeId, fn ($q) => $q->where('periode_id', $periodeId))
            ->pluck('siswa_id');

        $calon = Siswa::aktif()
            ->when($sudah->isNotEmpty(), fn ($q) => $q->whereNotIn('id', $sudah))
            ->orderBy('nama_siswa', 'asc')
            ->limit(50)
            ->get();

        $count = 0;
        foreach ($calon as $s) {
            $this->tempatkan($s->id, $kelasId, $periodeId, ['tanggal_masuk' => now()]);
            $count++;
        }

        return $count;
    }

    /**
     * Tetapkan nomor absen unik (action transaksional).
     *
     * Bila nomor yang diminta sudah dipakai siswa lain pada kelas & periode yang
     * sama, siswa itu digeser otomatis ke nomor berikutnya (rantai) sampai
     * ditemukan nomor kosong. Dijalankan dalam satu transaksi.
     */
    public function assignNomorAbsenUnik(AngkatanSiswa $angkatan, int $kelasId, ?int $periodeId, int $nomor): int
    {
        return DB::transaction(function () use ($angkatan, $kelasId, $periodeId, $nomor) {
            $nomor = max(1, $nomor);

            // Peta pemilik nomor saat ini (tanpa angkatan yang sedang diubah)
            $peta = AngkatanSiswa::where('kelas_id', $kelasId)
                ->where('periode_id', $periodeId)
                ->whereNotNull('nomor_absen')
                ->where('id', '!=', $angkatan->id)
                ->pluck('id', 'nomor_absen');

            // Cari slot kosong pertama di atas nomor yang diminta (rantai terpakai)
            $target = $nomor;
            while (isset($peta[$target])) {
                $target++;
            }

            // Angkatan tujuan mengambil nomor yang diminta
            $angkatan->kelas_id = $kelasId;
            $angkatan->nomor_absen = $nomor;
            $angkatan->save();

            // Geser pemilik nomor di blok [nomor, target-1] naik satu angka,
            // urut menurun agar nomor tujuan slot selalu bebas
            for ($k = $target - 1; $k >= $nomor; $k--) {
                if (isset($peta[$k])) {
                    AngkatanSiswa::where('id', $peta[$k])->update(['nomor_absen' => $k + 1]);
                }
            }

            return $nomor;
        });
    }

    /**
     * Tahun ajaran untuk dropdown (dari model Periode).
     */
    public function tahunAjaranList()
    {
        return Periode::tahunAjaranList();
    }
}
