<?php

namespace App\Services\Jadwal;

use App\Models\JadwalHarian;
use App\Models\MasterJam;
use App\Services\MutasiLogService;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

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
     * Proses perpindahan blok (effective-dated).
     *
     * Blok sumber tidak lagi "dipindah" secara langsung melainkan ditutup
     * (berlaku_sampai = sehari sebelum tanggal efektif) lalu dibuatkan salinan
     * baru di posisi tujuan (berlaku_mulai = tanggal efektif). Dengan begitu
     * jadwal sebelum tanggal efektif tetap tampil seperti semula.
     *
     * @param int         $sourceId       id jadwal sumber
     * @param int|null    $targetId       id jadwal target (null = move ke kosong)
     * @param string      $targetHari     hari tujuan
     * @param array<int>  $targetJam      daftar jam tujuan (dari input "3-4")
     * @param string      $tanggalEfektif tanggal mulai berlakunya perubahan (Y-m-d)
     * @return array{status: string, pesan: string}
     *
     * @throws \Exception bila terjadi kegagalan proses
     */
    public function pindahBlok(int $sourceId, ?int $targetId, string $targetHari, array $targetJam, string $tanggalEfektif): array
    {
        $kemarin = Carbon::parse($tanggalEfektif)->subDay()->format('Y-m-d');
        $refTgl  = now()->format('Y-m-d');

        $info = DB::transaction(function () use ($sourceId, $targetId, $targetHari, $targetJam, $tanggalEfektif, $kemarin, $refTgl) {
            $sourceRecord = JadwalHarian::find($sourceId);
            if (!$sourceRecord) {
                throw new \Exception("Jadwal sumber tidak ditemukan.");
            }

            $sourceHari = $sourceRecord->hari;
            $kelasId = $sourceRecord->kelas_id;
            $guruId = $sourceRecord->guru_id;
            $pelajaranId = $sourceRecord->pelajaran_id;

            // Blok jam TETAP sesuai posisi yang diklik (mis. 7-8 / 9-10), TIDAK melebar
            // ke blok berdampingan yang kebetulan berurutan.
            $sourceJamGroup = $this->pasanganBlokJam($sourceRecord->jam_ke);
            $sourceRows = JadwalHarian::aktifPada($refTgl)
                ->where('hari', $sourceHari)
                ->where('kelas_id', $kelasId)
                ->where('pelajaran_id', $pelajaranId)
                ->where('guru_id', $guruId)
                ->whereIn('jam_ke', $sourceJamGroup)
                ->orderBy('jam_ke')
                ->get();

            if (!empty($targetId)) {
                // ===== SKENARIO SWAP (tukar antar blok) =====
                $targetRecord = DB::table('jadwal_harians')->where('id', $targetId)->first();
                if (!$targetRecord) {
                    throw new \Exception("Jadwal target tidak ditemukan.");
                }

                $targetJamGroup = $this->pasanganBlokJam((int) $targetRecord->jam_ke);
                $targetRows = JadwalHarian::aktifPada($refTgl)
                    ->where('hari', $targetHari)
                    ->where('kelas_id', $targetRecord->kelas_id)
                    ->where('pelajaran_id', $targetRecord->pelajaran_id)
                    ->where('guru_id', $targetRecord->guru_id)
                    ->whereIn('jam_ke', $targetJamGroup)
                    ->orderBy('jam_ke')
                    ->get();

                // A: tutup rekaman sumber & salin ke posisi target
                foreach ($sourceRows as $index => $s) {
                    $salinan = $s->replicate();
                    $salinan->hari = $targetHari;
                    $salinan->jam_ke = $targetJamGroup[$index] ?? $targetJamGroup[0];
                    $salinan->berlaku_mulai = $tanggalEfektif;
                    $salinan->berlaku_sampai = null;
                    $salinan->save();

                    $s->berlaku_sampai = $kemarin;
                    $s->save();
                }

                // B: tutup rekaman target & salin ke posisi sumber
                foreach ($targetRows as $index => $t) {
                    $salinan = $t->replicate();
                    $salinan->hari = $sourceHari;
                    $salinan->jam_ke = $sourceJamGroup[$index] ?? $sourceJamGroup[0];
                    $salinan->berlaku_mulai = $tanggalEfektif;
                    $salinan->berlaku_sampai = null;
                    $salinan->save();

                    $t->berlaku_sampai = $kemarin;
                    $t->save();
                }
            } else {
                // ===== SKENARIO MOVE (pindah ke kosong) =====
                foreach ($sourceRows as $index => $s) {
                    $salinan = $s->replicate();
                    $salinan->hari = $targetHari;
                    $salinan->jam_ke = $targetJam[$index] ?? $targetJam[0];
                    $salinan->berlaku_mulai = $tanggalEfektif;
                    $salinan->berlaku_sampai = null;
                    $salinan->save();

                    $s->berlaku_sampai = $kemarin;
                    $s->save();
                }
            }

            return [
                'sourceHari'   => $sourceHari,
                'kelasId'      => $kelasId,
                'guruId'       => $guruId,
                'pelajaranId'  => $pelajaranId,
                'targetGuruId' => $targetRecord->guru_id ?? null,
                'targetPelId'  => $targetRecord->pelajaran_id ?? null,
                'sourceJamGroup' => $sourceJamGroup,
            ];
        });

        // Pencatatan riwayat di luar transaksi (tidak menggagalkan operasi utama)
        $isSwap = !empty($targetId);
        $tipeLog = $isSwap ? 'tukar_jam' : 'pindah_blok';
        $keterangan = $isSwap
            ? 'Tukar posisi blok jadwal ke Hari ' . $targetHari . ' Jam ke-' . implode('-', $targetJam) . ' (efektif ' . $tanggalEfektif . ')'
            : 'Memindahkan blok jadwal ke Hari ' . $targetHari . ' Jam ke-' . implode('-', $targetJam) . ' (efektif ' . $tanggalEfektif . ')';

        // Sisi sumber
        $this->logService->catat([
            'kelas_id'         => $info['kelasId'],
            'pelajaran_id'     => $info['pelajaranId'],
            'hari'             => $targetHari,
            'jam_ke'           => count($targetJam) === 1 ? $targetJam[0] : null,
            'guru_lama_id'     => $info['guruId'],
            'guru_baru_id'     => $info['guruId'],
            'tipe'             => $tipeLog,
            'tanggal_efektif'  => $tanggalEfektif,
            'keterangan'       => $keterangan,
        ]);

        // Sisi target (agar keduanya muncul di riwayat mutasi & filter guru)
        if ($isSwap && !empty($info['targetGuruId'])) {
            $this->logService->catat([
                'kelas_id'         => $info['kelasId'],
                'pelajaran_id'     => $info['targetPelId'] ?? $info['pelajaranId'],
                'hari'             => $info['sourceHari'],
                'jam_ke'           => count($info['sourceJamGroup']) === 1 ? $info['sourceJamGroup'][0] : null,
                'guru_lama_id'     => $info['targetGuruId'],
                'guru_baru_id'     => $info['targetGuruId'],
                'tipe'             => 'tukar_jam',
                'tanggal_efektif'  => $tanggalEfektif,
                'keterangan'       => $keterangan,
            ]);
        }

        return [
            'status' => 'success',
            'pesan'  => 'Blok jadwal berhasil ' . ($isSwap ? 'ditukar seutuhnya!' : 'dipindahkan seutuhnya!'),
        ];
    }

    /**
     * Blok jam TETAP tempat $jamKe berada (pola pasangan MasterJam, mis. 7-8 / 9-10).
     * Menjamin blok yang digeser/ditukar TIDAK melebar ke blok panggdamping
     * yang kebetulan berurutan (mis. 9-10 ikut tergerus ketika menggeser 7-8).
     */
    private function pasanganBlokJam(int $jamKe): array
    {
        $semuaJam = MasterJam::orderBy('jam_ke', 'asc')->pluck('jam_ke')->toArray();
        for ($i = 0; $i < count($semuaJam); $i += 2) {
            $blok = array_slice($semuaJam, $i, 2);
            if (in_array($jamKe, $blok)) {
                return $blok;
            }
        }
        return [$jamKe];
    }
}
