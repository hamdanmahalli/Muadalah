<?php

namespace App\Services\Jadwal;

use App\Models\JadwalHarian;
use App\Services\MutasiLogService;
use Illuminate\Support\Facades\DB;

/**
 * Mesin pemindah / penukar blok jadwal (drag & drop).
 *
 * SRP: Satu tanggung jawab — mengolah perpindahan blok jadwal antar hari/jam,
 * baik swap (tukar antar blok) maupun move (pindah ke slot kosong).
 * Dijalankan dalam satu transaksi; controller hanya meneruskan input & respons.
 */
class JadwalDragDropService
{
    public function __construct(private MutasiLogService $logService) {}

    /**
     * Proses perpindahan blok.
     *
     * @param int         $sourceId    id jadwal sumber
     * @param int|null    $targetId    id jadwal target (null = move ke kosong)
     * @param string      $targetHari  hari tujuan
     * @param array<int>  $targetJam   daftar jam tujuan (dari input "3-4")
     * @return array{status: string, pesan: string}
     *
     * @throws \Exception bila terjadi kegagalan proses
     */
    public function pindahBlok(int $sourceId, ?int $targetId, string $targetHari, array $targetJam): array
    {
        [$sourceRecord, $sourceHari, $kelasId, $guruId, $pelajaranId] = DB::transaction(function () use ($sourceId, $targetId, $targetHari, $targetJam) {

            $sourceRecord = JadwalHarian::find($sourceId);
            if (!$sourceRecord) {
                throw new \Exception("Jadwal sumber tidak ditemukan.");
            }

            $sourceHari = $sourceRecord->hari;
            $kelasId = $sourceRecord->kelas_id;
            $guruId = $sourceRecord->guru_id;
            $pelajaranId = $sourceRecord->pelajaran_id;

            // Identifikasi blok jam sumber
            $sourceJamList = $this->jamListBlok($sourceHari, $kelasId, $pelajaranId, $guruId);
            $sourceJamGroup = $this->grupJamBerurutan($sourceJamList, $sourceRecord->jam_ke);
            $sourceIds = DB::table('jadwal_harians')
                ->where('hari', $sourceHari)
                ->where('kelas_id', $kelasId)
                ->where('pelajaran_id', $pelajaranId)
                ->where('guru_id', $guruId)
                ->whereIn('jam_ke', $sourceJamGroup)
                ->pluck('id')
                ->toArray();

            if (!empty($targetId)) {
                // ===== SKENARIO SWAP (tukar antar blok) =====
                $targetRecord = DB::table('jadwal_harians')->where('id', $targetId)->first();
                if (!$targetRecord) {
                    throw new \Exception("Jadwal target tidak ditemukan.");
                }

                $targetKelId = $targetRecord->kelas_id;
                $targetGuruId = $targetRecord->guru_id;
                $targetPelId = $targetRecord->pelajaran_id;

                $targetJamList = DB::table('jadwal_harians')
                    ->where('hari', $targetHari)
                    ->where('kelas_id', $targetKelId)
                    ->where('pelajaran_id', $targetPelId)
                    ->where('guru_id', $targetGuruId)
                    ->pluck('jam_ke')
                    ->toArray();
                sort($targetJamList);

                $targetJamGroup = $this->grupJamBerurutan($targetJamList, $targetRecord->jam_ke);
                $targetIds = DB::table('jadwal_harians')
                    ->where('hari', $targetHari)
                    ->where('kelas_id', $targetKelId)
                    ->where('pelajaran_id', $targetPelId)
                    ->where('guru_id', $targetGuruId)
                    ->whereIn('jam_ke', $targetJamGroup)
                    ->pluck('id')
                    ->toArray();

                // A: pindahkan source ke transit
                DB::table('jadwal_harians')->whereIn('id', $sourceIds)
                    ->update(['jam_ke' => 999, 'hari' => 'TRANSIT']);

                // B: pindahkan target ke posisi source
                foreach ($targetIds as $index => $tId) {
                    $newJam = $sourceJamGroup[$index] ?? $sourceJamGroup[0];
                    DB::table('jadwal_harians')->where('id', $tId)
                        ->update(['hari' => $sourceHari, 'jam_ke' => $newJam]);
                }

                // C: pindahkan source ke posisi target
                foreach ($sourceIds as $index => $sId) {
                    $newJam = $targetJamGroup[$index] ?? $targetJamGroup[0];
                    DB::table('jadwal_harians')->where('id', $sId)
                        ->update(['hari' => $targetHari, 'jam_ke' => $newJam]);
                }
            } else {
                // ===== SKENARIO MOVE (pindah ke kosong) =====
                foreach ($sourceIds as $index => $sId) {
                    $newJam = $targetJam[$index] ?? $targetJam[0];
                    DB::table('jadwal_harians')->where('id', $sId)
                        ->update(['hari' => $targetHari, 'jam_ke' => $newJam]);
                }
            }

            return [$sourceRecord, $sourceHari, $kelasId, $guruId, $pelajaranId];
        });

        // Pencatatan riwayat di luar transaksi (tidak menggagalkan operasi utama)
        $isSwap = !empty($targetId);
        $tipeLog = $isSwap ? 'tukar_jam' : 'pindah_blok';
        $keterangan = $isSwap
            ? 'Tukar posisi blok jadwal ke Hari ' . $targetHari . ' Jam ke-' . implode('-', $targetJam)
            : 'Memindahkan blok jadwal ke Hari ' . $targetHari . ' Jam ke-' . implode('-', $targetJam);

        $this->logService->catat([
            'kelas_id'     => $sourceRecord->kelas_id,
            'pelajaran_id' => $sourceRecord->pelajaran_id,
            'hari'         => $targetHari,
            'jam_ke'       => count($targetJam) === 1 ? $targetJam[0] : null,
            'guru_lama_id' => $sourceRecord->guru_id,
            'guru_baru_id' => $sourceRecord->guru_id,
            'tipe'         => $tipeLog,
            'keterangan'   => $keterangan,
        ]);

        return [
            'status' => 'success',
            'pesan'  => 'Blok jadwal berhasil ' . ($isSwap ? 'ditukar seutuhnya!' : 'dipindahkan seutuhnya!'),
        ];
    }

    /**
     * Kumpulan jam_ke dari seluruh jadwal pada kombinasi tertentu (blok sumber/target).
     */
    private function jamListBlok(string $hari, int $kelasId, int $pelajaranId, int $guruId): array
    {
        $list = DB::table('jadwal_harians')
            ->where('hari', $hari)
            ->where('kelas_id', $kelasId)
            ->where('pelajaran_id', $pelajaranId)
            ->where('guru_id', $guruId)
            ->pluck('jam_ke')
            ->toArray();
        sort($list);
        return $list;
    }

    /**
     * Kelompokan daftar jam menjadi grup berurutan yang memuat $jamKe.
     */
    private function grupJamBerurutan(array $jamList, int $jamKe): array
    {
        $group = [];
        $tempGroup = [];
        foreach ($jamList as $jKe) {
            if (empty($tempGroup) || $jKe == end($tempGroup) + 1) {
                $tempGroup[] = $jKe;
            } else {
                if (in_array($jamKe, $tempGroup)) {
                    $group = $tempGroup;
                    break;
                }
                $tempGroup = [$jKe];
            }
        }
        if (empty($group) && in_array($jamKe, $tempGroup)) {
            $group = $tempGroup;
        }
        if (empty($group)) {
            $group = [$jamKe];
        }
        return $group;
    }
}
