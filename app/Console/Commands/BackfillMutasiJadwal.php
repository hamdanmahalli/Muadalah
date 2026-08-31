<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use App\Models\JadwalHarian;
use App\Models\MutasiJadwal;

#[Signature('mutasi:backfill')]
#[Description('Rekonstruksi riwayat mutasi/perubahan jadwal dari data jadwal_harians lama (backfill data historis).')]
class BackfillMutasiJadwal extends Command
{
    public function handle()
    {
        $periodeAktif = get_periode_aktif();
        $periodeId = $periodeAktif?->id;
        $tahunAjaran = $periodeAktif?->tahun_ajaran;

        $this->info("Periode aktif: {$tahunAjaran} (id={$periodeId})");

        // Semua jadwal yang pernah di-soft-delete (jadwal lama yang diganti/dihapus)
        $deleted = JadwalHarian::onlyTrashed()
            ->where(function ($q) use ($tahunAjaran) {
                $q->where('tahun_ajaran', $tahunAjaran)->orWhereNull('tahun_ajaran');
            })
            ->orderBy('kelas_id')->orderBy('hari')->orderBy('jam_ke')
            ->get();

        $this->info("Jumlah jadwal soft-deleted yang diproses: " . $deleted->count());

        // Cari pengganti (baris aktif pada slot yang sama tapi guru berbeda)
        $deletedBySlot = [];
        foreach ($deleted as $j) {
            $replacement = JadwalHarian::where('kelas_id', $j->kelas_id)
                ->where('pelajaran_id', $j->pelajaran_id)
                ->where('hari', $j->hari)
                ->where('jam_ke', $j->jam_ke)
                ->where('guru_id', '!=', $j->guru_id)
                ->first();

            $key = $j->kelas_id . '|' . $j->pelajaran_id . '|' . $j->hari . '|' . $j->guru_id . '|' . ($replacement?->guru_id ?? 'none');
            $deletedBySlot[$key][] = [
                'jam_ke'      => (int) $j->jam_ke,
                'deleted_at'  => $j->deleted_at?->format('Y-m-d'),
                'user_id'     => null,
                'kelas_id'    => $j->kelas_id,
                'pelajaran_id'=> $j->pelajaran_id,
                'hari'        => $j->hari,
                'guru_lama_id'=> $j->guru_id,
                'guru_baru_id'=> $replacement?->guru_id,
                'tipe'        => $replacement ? 'ganti_guru' : 'hapus_slot',
            ];
        }

        $created = 0;
        $skipped = 0;

        foreach ($deletedBySlot as $group) {
            // Urutkan jam_ke, deteksi rentang (contoh 5-6)
            $jams = array_column($group, 'jam_ke');
            sort($jams);
            $min = $jams[0];
            $max = $jams[count($jams) - 1];
            $range = ($min === $max) ? (string) $min : $min . '-' . $max;

            $first = $group[0];
            $tanggal = $first['deleted_at'] ?? now()->format('Y-m-d');

            $keterangan = $first['tipe'] === 'ganti_guru'
                ? 'Backfill: pergantian guru dari data lama (blok jam ' . $range . ')'
                : 'Backfill: slot dikosongkan dari data lama (blok jam ' . $range . ')';

            // Idempotens: lewati jika sudah ada entri dengan tanda yang sama
            $exists = MutasiJadwal::where('kelas_id', $first['kelas_id'])
                ->where('pelajaran_id', $first['pelajaran_id'])
                ->where('hari', $first['hari'])
                ->where('guru_lama_id', $first['guru_lama_id'])
                ->where('guru_baru_id', $first['guru_baru_id'])
                ->where('tipe', $first['tipe'])
                ->exists();

            if ($exists) {
                $skipped++;
                continue;
            }

            MutasiJadwal::create([
                'periode_id'       => $periodeId,
                'tahun_ajaran'     => $tahunAjaran,
                'kelas_id'         => $first['kelas_id'],
                'pelajaran_id'     => $first['pelajaran_id'],
                'hari'             => $first['hari'],
                'jam_ke'           => $min,
                'guru_lama_id'     => $first['guru_lama_id'],
                'guru_baru_id'     => $first['guru_baru_id'],
                'tipe'             => $first['tipe'],
                'tanggal_kejadian' => $tanggal,
                'tanggal_efektif'  => $tanggal,
                'keterangan'       => $keterangan,
                'user_id'          => null,
            ]);

            $created++;
        }

        $this->info("Backfill selesai: {$created} riwayat baru dibuat, {$skipped} dilewati (sudah ada).");
        return Command::SUCCESS;
    }
}
