<?php

namespace App\Services\Kehadiran;

use App\Models\AgendaKaldik;
use App\Models\Guru;
use App\Models\JadwalHarian;
use App\Models\KehadiranGuru;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

/**
 * Penyusun slot kehadiran guru dari jadwal harian + catatan kehadiran.
 *
 * SRP: Satu tanggung jawab — menyusun baris slot kehadiran (termasuk logika
 * libur & rentang tanggal) yang akan ditampilkan pada monitoring. Stateless.
 */
class KehadiranSlotService
{
    // Status yang boleh diisi Admin (Kosong lama ditampilkan sebagai Alpa)
    public const DAFTAR_STATUS = ['Hadir', 'Izin', 'Sakit', 'Alpa', 'Menunggu'];

    /**
     * Hitung batas tanggal minimum & maksimum, lalu isi sisi yang kosong.
     *
     * @return array{tgl_mulai: string, tgl_selesai: string}
     */
    public function normalisasiRentang(?string $tglMulai, ?string $tglSelesai): array
    {
        $jadwalMinRaw = JadwalHarian::withTrashed()->min('created_at');
        $jadwalMaxRaw = JadwalHarian::withTrashed()->max('created_at');
        $jadwalMin = $jadwalMinRaw ? substr((string) $jadwalMinRaw, 0, 10) : null;
        $jadwalMax = $jadwalMaxRaw ? substr((string) $jadwalMaxRaw, 0, 10) : null;

        $batasMin = min(
            KehadiranGuru::min('tanggal') ?? '9999-12-31',
            $jadwalMin ?? '9999-12-31'
        );
        $batasMax = max(
            KehadiranGuru::max('tanggal') ?? '1970-01-01',
            $jadwalMax ?? '1970-01-01'
        );

        if (!$tglMulai) {
            $tglMulai = $batasMin !== '9999-12-31' ? $batasMin : Carbon::now()->startOfMonth()->format('Y-m-d');
        }
        if (!$tglSelesai) {
            $tglSelesai = $batasMax !== '1970-01-01' ? $batasMax : Carbon::now()->format('Y-m-d');
        }

        return ['tgl_mulai' => $tglMulai, 'tgl_selesai' => $tglSelesai];
    }

    /**
     * Ambil daftar libur/UTS/UAS yang menimpa rentang tanggal dari periode aktif.
     */
    public function daftarLiburPeriode(?int $periodeId, string $tglMulai, string $tglSelesai)
    {
        return AgendaKaldik::where('periode_id', $periodeId)
            ->whereIn('jenis_agenda', ['Libur', 'UTS', 'UAS'])
            ->where('tanggal_mulai', '<=', $tglSelesai)
            ->where('tanggal_selesai', '>=', $tglMulai)
            ->get();
    }

    /**
     * Bangun slot kehadiran untuk halaman monitoring (dengan filter).
     *
     * @return array{slots: array, total: array}
     */
    public function bangunSlotIndex(string $tglMulai, string $tglSelesai, $cari, $kelasId, $statusF, ?int $periodeId): array
    {
        $daftarLibur = $this->daftarLiburPeriode($periodeId, $tglMulai, $tglSelesai);
        $semuaJadwal = JadwalHarian::withTrashed()->with(['kelas', 'pelajaran', 'guru'])->get();
        $records = KehadiranGuru::whereBetween('tanggal', [$tglMulai, $tglSelesai])->get()
            ->keyBy(fn ($r) => $r->jadwal_id . '|' . $r->tanggal);

        $slots = [];
        $period = CarbonPeriod::create($tglMulai, $tglSelesai);
        foreach ($period as $date) {
            $tglStr   = $date->format('Y-m-d');
            $hariIndo = map_hari($date->format('l'));

            foreach ($semuaJadwal as $j) {
                if ($kelasId !== '' && $kelasId !== null && (int) $j->kelas_id !== (int) $kelasId) {
                    continue;
                }
                if ($cari !== '' && $cari !== null) {
                    $namaG = $j->guru->nama_guru ?? '';
                    $nigG  = $j->guru->nig ?? '';
                    if (stripos($namaG, $cari) === false && stripos($nigG, $cari) === false) {
                        continue;
                    }
                }
                if (strtolower($j->hari) !== strtolower($hariIndo)) {
                    continue;
                }
                $mulaiAktif = $j->created_at ? $j->created_at->format('Y-m-d') : '2000-01-01';
                $selesaiAktif = $j->deleted_at ? $j->deleted_at->format('Y-m-d') : '2099-12-31';
                if ($tglStr < $mulaiAktif || $tglStr > $selesaiAktif) {
                    continue;
                }
                $libur = $this->isLibur($j, $daftarLibur, $tglStr);
                if ($libur['is_libur']) {
                    continue;
                }

                $record = $records->get($j->id . '|' . $tglStr);

                $statusAsli = $record ? $record->status : 'Menunggu';
                $statusTampil = in_array($statusAsli, ['Kosong', 'Alpha']) ? 'Alpa' : $statusAsli;

                if ($statusF !== '' && $statusF !== null && $statusTampil !== $statusF) {
                    continue;
                }

                $slots[] = [
                    'jadwal_id'   => $j->id,
                    'tanggal'     => $tglStr,
                    'hari'        => $hariIndo,
                    'jam_ke'      => (int) ($j->jam_ke ?? 0),
                    'kelas'       => $j->kelas->nama_kelas ?? '-',
                    'kelas_urut'  => (int) ($j->kelas_id ?? 0),
                    'mapel'       => $j->pelajaran->nama_pelajaran ?? '-',
                    'guru'        => $j->guru->nama_guru ?? '-',
                    'guru_urut'   => (int) ($j->guru_id ?? 0),
                    'status'      => $statusTampil,
                    'ada_record'  => (bool) $record,
                    'keterangan'  => $record ? $record->keterangan : null,
                    'pengganti'   => $record ? $record->nig_pengganti : null,
                ];
            }
        }

        usort($slots, fn ($a, $b) =>
            [$a['tanggal'], $a['kelas_urut'], $a['guru_urut'], $a['jam_ke']]
            <=> [$b['tanggal'], $b['kelas_urut'], $b['guru_urut'], $b['jam_ke']]);

        $total = ['Hadir' => 0, 'Izin' => 0, 'Sakit' => 0, 'Alpa' => 0, 'Menunggu' => 0];
        foreach ($slots as $s) {
            if (isset($total[$s['status']])) {
                $total[$s['status']]++;
            }
        }

        return ['slots' => $slots, 'total' => $total];
    }

    /**
     * Bangun slot kehadiran untuk satu guru (AJAX detail).
     */
    public function bangunSlotGuru(int $guruId, string $tglMulai, string $tglSelesai, ?int $periodeId): array
    {
        $daftarLibur = $this->daftarLiburPeriode($periodeId, $tglMulai, $tglSelesai);
        $semuaJadwal = JadwalHarian::withTrashed()->with(['kelas', 'pelajaran'])->get();
        $records = KehadiranGuru::whereBetween('tanggal', [$tglMulai, $tglSelesai])->get()
            ->keyBy(fn ($r) => $r->jadwal_id . '|' . $r->tanggal);

        $slots = [];
        $period = CarbonPeriod::create($tglMulai, $tglSelesai);
        foreach ($period as $date) {
            $tglStr   = $date->format('Y-m-d');
            $hariIndo = map_hari($date->format('l'));

            foreach ($semuaJadwal->where('guru_id', $guruId) as $j) {
                if (strtolower($j->hari) !== strtolower($hariIndo)) {
                    continue;
                }
                $mulaiAktif = $j->created_at ? $j->created_at->format('Y-m-d') : '2000-01-01';
                $selesaiAktif = $j->deleted_at ? $j->deleted_at->format('Y-m-d') : '2099-12-31';
                if ($tglStr < $mulaiAktif || $tglStr > $selesaiAktif) {
                    continue;
                }
                $libur = $this->isLibur($j, $daftarLibur, $tglStr);
                if ($libur['is_libur']) {
                    continue;
                }

                $record = $records->get($j->id . '|' . $tglStr);
                $slots[] = [
                    'jadwal_id'   => $j->id,
                    'tanggal'     => $tglStr,
                    'hari'        => $hariIndo,
                    'jam_ke'      => $j->jam_ke,
                    'kelas'       => $j->kelas->nama_kelas ?? '-',
                    'mapel'       => $j->pelajaran->nama_pelajaran ?? '-',
                    'status'      => $record ? $record->status : 'Menunggu',
                    'ada_record'  => (bool) $record,
                    'keterangan'  => $record ? $record->keterangan : null,
                    'pengganti'   => $record ? $record->nig_pengganti : null,
                ];
            }
        }

        usort($slots, fn ($a, $b) => [$a['tanggal'], $a['jam_ke']] <=> [$b['tanggal'], $b['jam_ke']]);

        return $slots;
    }

    /**
     * Deteksi apakah jadwal terkena libur/UTS/UAS pada tanggal tertentu.
     *
     * @return array{is_libur: bool, nama_libur: string}
     */
    public function isLibur($jadwal, $daftarLibur, ?string $tglStr = null): array
    {
        $isLibur = false;
        $namaLibur = '';

        foreach ($daftarLibur as $agenda) {
            if ($tglStr !== null) {
                $mulai = Carbon::parse($agenda->tanggal_mulai)->format('Y-m-d');
                $selesai = Carbon::parse($agenda->tanggal_selesai)->format('Y-m-d');
                if ($tglStr < $mulai || $tglStr > $selesai) {
                    continue;
                }
            }

            $arrKls = is_array($agenda->kelas_ids)
                ? $agenda->kelas_ids
                : (is_string($agenda->kelas_ids) ? json_decode($agenda->kelas_ids, true) : []);

            $kenaTarget = false;
            if ($agenda->target_libur === 'semua') {
                $kenaTarget = true;
            } elseif ($agenda->target_libur === 'kelas_tertentu' && in_array($jadwal->kelas_id, $arrKls)) {
                $kenaTarget = true;
            }

            if (!$kenaTarget) {
                continue;
            }

            if ($agenda->tipe_agenda === 'Penuh') {
                $isLibur = true;
                $namaLibur = $agenda->nama_agenda;
                break;
            }

            if ($agenda->tipe_agenda === 'Parsial') {
                $jamArr = is_array($agenda->jam_diliburkan)
                    ? $agenda->jam_diliburkan
                    : (is_string($agenda->jam_diliburkan) ? json_decode($agenda->jam_diliburkan, true) : []);
                if (in_array($jadwal->jam_ke, $jamArr)) {
                    $isLibur = true;
                    $namaLibur = $agenda->nama_agenda;
                    break;
                }
            }
        }

        return ['is_libur' => $isLibur, 'nama_libur' => $namaLibur];
    }
}
