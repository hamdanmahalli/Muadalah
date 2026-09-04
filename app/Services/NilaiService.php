<?php

namespace App\Services;

use App\Models\Nilai;
use Illuminate\Support\Facades\DB;

/**
 * Operasi nilai akademik siswa.
 *
 * Alur nilai (per semester / periode):
 *   - Nilai Harian diinput guru (Dewan Guru).
 *   - Skor UTS / Skor UAS diinput panitia (Kepanitiaan).
 *   - Nilai UTS akhir = rata-rata Nilai Harian UTS dan Skor UTS.
 *   - Nilai UAS akhir = rata-rata Nilai Harian UAS dan Skor UAS.
 *   - Nilai Rapot (nilai_akhir) = rata-rata Nilai UTS akhir dan Nilai UAS akhir.
 *   - Predikat dihitung dari Nilai Rapot.
 *
 * SRP: Satu tanggung jawab — menghitung nilai akhir/predikat dan menyimpan
 * nilai (tunggal atau massal) ke database. Stateless.
 */
class NilaiService
{
    /**
     * Hitung nilai akhir dari dua komponen (rata-rata bila dua-duanya ada).
     */
    protected function gabung($a, $b)
    {
        if (is_numeric($a) && is_numeric($b)) {
            return round((($a + $b) / 2), 2);
        }
        if (is_numeric($a)) {
            return round((float) $a, 2);
        }
        if (is_numeric($b)) {
            return round((float) $b, 2);
        }
        return null;
    }

    /**
     * Nilai UTS akhir = rata-rata Nilai Harian UTS dan Skor UTS.
     */
    public function hitungUtsAkhir($harianUts, $skorUts)
    {
        return $this->gabung($harianUts, $skorUts);
    }

    /**
     * Nilai UAS akhir = rata-rata Nilai Harian UAS dan Skor UAS.
     */
    public function hitungUasAkhir($harianUas, $skorUas)
    {
        return $this->gabung($harianUas, $skorUas);
    }

    /**
     * Nilai Rapot = rata-rata Nilai UTS akhir dan Nilai UAS akhir.
     */
    public function hitungRapor($utsAkhir, $uasAkhir)
    {
        return $this->gabung($utsAkhir, $uasAkhir);
    }

    /**
     * Alias kompatibilitas: nilai akhir (rapor) dari UTS akhir & UAS akhir.
     */
    public function hitungAkhir($utsAkhir, $uasAkhir)
    {
        return $this->hitungRapor($utsAkhir, $uasAkhir);
    }

    /**
     * Konversi nilai ke predikat huruf.
     */
    public function predikat($nilai)
    {
        if (!is_numeric($nilai)) {
            return null;
        }
        if ($nilai >= 90) {
            return 'A';
        }
        if ($nilai >= 80) {
            return 'B';
        }
        if ($nilai >= 70) {
            return 'C';
        }
        if ($nilai >= 60) {
            return 'D';
        }
        return 'E';
    }

    /**
     * Siapkan nilai komponen + turunannya untuk penyimpanan.
     */
    protected function hitungKomponen(array $fields): array
    {
        $harianUts = $fields['nilai_harian_uts'] ?? null;
        $harianUas = $fields['nilai_harian_uas'] ?? null;
        $skorUts   = $fields['skor_uts'] ?? null;
        $skorUas   = $fields['skor_uas'] ?? null;

        $utsAkhir = $this->hitungUtsAkhir($harianUts, $skorUts);
        $uasAkhir = $this->hitungUasAkhir($harianUas, $skorUas);
        $rapor    = $this->hitungRapor($utsAkhir, $uasAkhir);

        return [
            'nilai_uts_akhir' => $utsAkhir,
            'nilai_uas_akhir' => $uasAkhir,
            'nilai_akhir'     => $rapor,
            'predikat'        => $this->predikat($rapor),
        ];
    }

    /**
     * Simpan satu baris nilai (updateOrCreate).
     */
    public function simpan(
        int $siswaId,
        int $pelajaranId,
        ?int $periodeId,
        array $fields
    ): Nilai {
        $turunan = $this->hitungKomponen($fields);

        return Nilai::updateOrCreate(
            [
                'siswa_id' => $siswaId,
                'pelajaran_id' => $pelajaranId,
                'periode_id' => $periodeId,
            ],
            array_merge($fields, $turunan)
        );
    }

    /**
     * Simpan nilai massal (grid) dalam satu transaksi.
     *
     * @param array<string, array{harian_uts?: mixed, harian_uas?: mixed, skor_uts?: mixed, skor_uas?: mixed}> $skorBySiswaId
     */
    public function simpanMassal(
        int $kelasId,
        int $pelajaranId,
        ?int $periodeId,
        ?int $guruId,
        array $skorBySiswaId
    ): int {
        return DB::transaction(function () use ($kelasId, $pelajaranId, $periodeId, $guruId, $skorBySiswaId) {
            $count = 0;
            foreach ($skorBySiswaId as $siswaId => $vals) {
                $harianUts = $vals['harian_uts'] ?? null;
                $harianUas = $vals['harian_uas'] ?? null;
                $skorUts   = $vals['skor_uts'] ?? null;
                $skorUas   = $vals['skor_uas'] ?? null;
                if (!is_numeric($harianUts) && !is_numeric($harianUas) && !is_numeric($skorUts) && !is_numeric($skorUas)) {
                    continue;
                }
                $this->simpan((int) $siswaId, $pelajaranId, $periodeId, [
                    'kelas_id'        => $kelasId,
                    'guru_id'         => $guruId,
                    'nilai_harian_uts' => $harianUts,
                    'nilai_harian_uas' => $harianUas,
                    'skor_uts'        => $skorUts,
                    'skor_uas'        => $skorUas,
                ]);
                $count++;
            }
            return $count;
        });
    }
}
