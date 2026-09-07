<?php

namespace App\Services\Honor;

use App\Models\HonorKonfigurasi;
use App\Models\HonorGuruConfig;
use App\Models\HonorStrukturalConfig;
use App\Models\HonorPeriode;
use App\Models\HonorDetail;
use App\Models\Guru;
use App\Models\KehadiranGuru;
use App\Models\JadwalHarian;
use App\Models\AgendaKaldik;
use App\Models\Kelas;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;

class HonorService
{
    public function hitung($bulan, $tahun, $periodeAktif): HonorPeriode
    {
        $config = HonorKonfigurasi::with('periode')->where('periode_id', $periodeAktif->id)
            ->where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->first();

        if (!$config) {
            throw new \Exception('Konfigurasi honor untuk bulan ' . $bulan . ' tahun ' . $tahun . ' belum dibuat.');
        }

        $periodeHonor = HonorPeriode::updateOrCreate(
            [
                'honor_konfigurasi_id' => $config->id,
                'bulan' => $bulan,
                'tahun' => $tahun,
            ],
            [
                'status' => 'draft',
                'created_by' => auth()->id(),
            ]
        );

        HonorDetail::where('honor_periode_id', $periodeHonor->id)->delete();

        $tglMulai = Carbon::create($tahun, $bulan, 1)->startOfMonth()->format('Y-m-d');
        $tglSelesai = Carbon::create($tahun, $bulan, 1)->endOfMonth()->format('Y-m-d');

        $daftarLibur = AgendaKaldik::where('periode_id', $periodeAktif->id)
            ->whereIn('jenis_agenda', ['Libur', 'UTS', 'UAS'])
            ->where('tanggal_mulai', '<=', $tglSelesai)
            ->where('tanggal_selesai', '>=', $tglMulai)
            ->get();

        $jadwalAll = JadwalHarian::withTrashed()
            ->where('tahun_ajaran', $config->periode?->tahun_ajaran)
            ->whereNotNull('guru_id')
            ->get();

        $allGurus = Guru::all();

        foreach ($allGurus as $guru) {
            $guruConfig = HonorGuruConfig::where('honor_konfigurasi_id', $config->id)
                ->where('guru_id', $guru->id)
                ->first();

            if (!$guruConfig) {
                continue;
            }

            $jadwalGuru = $jadwalAll->where('guru_id', $guru->id)->values();

            $jamWajib = $this->hitungJamWajib($jadwalGuru, $daftarLibur, $tglMulai, $tglSelesai);

            $kehadiran = DB::table('kehadiran_gurus')
                ->join('jadwal_harians', 'kehadiran_gurus.jadwal_id', '=', 'jadwal_harians.id')
                ->where('jadwal_harians.guru_id', $guru->id)
                ->whereBetween('kehadiran_gurus.tanggal', [$tglMulai, $tglSelesai])
                ->select('kehadiran_gurus.status')
                ->get();

            $hadir = $kehadiran->where('status', 'Hadir')->count();
            $izin = $kehadiran->where('status', 'Izin')->count();
            $sakit = $kehadiran->where('status', 'Sakit')->count();
            $alpa = max(0, $jamWajib - ($hadir + $izin + $sakit));

            $realitaJam = max(0, $jamWajib - $alpa - $izin - $sakit);
            $persentase = $jamWajib > 0 ? round(($realitaJam / $jamWajib) * 100, 2) : 0;

            $keterangan = $this->getKeterangan($persentase);

            $tarifPerJam = $guruConfig->tarif_override
                ?? ($guruConfig->status_honor === 'Tetap' ? $config->tarif_jam_normal : $config->tarif_jam_magang);

            $honorPokok = $realitaJam * $tarifPerJam;

            $piketJam = KehadiranGuru::where('nig_pengganti', $guru->nig)
                ->whereBetween('tanggal', [$tglMulai, $tglSelesai])
                ->count();
            $honorPiket = $piketJam * $config->tarif_piket;

            $tunjanganStruktural = $this->hitungStruktural($config, $guru);
            $tunjanganWaliKelas = $this->hitungWaliKelas($guru, $config->tarif_wali_kelas);
            $transport = $guruConfig->dari_luar ? $config->tarif_transport : 0;

            $total = $honorPokok + $honorPiket + $tunjanganStruktural + $tunjanganWaliKelas + $transport;

            HonorDetail::create([
                'honor_periode_id' => $periodeHonor->id,
                'guru_id' => $guru->id,
                'jam_wajib' => $jamWajib,
                'alpa' => $alpa,
                'izin' => $izin,
                'sakit' => $sakit,
                'piket_jam' => $piketJam,
                'realita_jam' => $realitaJam,
                'persentase' => $persentase,
                'keterangan' => $keterangan,
                'honor_pokok' => $honorPokok,
                'tunjangan_struktural' => $tunjanganStruktural,
                'tunjangan_wali_kelas' => $tunjanganWaliKelas,
                'transport' => $transport,
                'honor_piket' => $honorPiket,
                'total' => $total,
            ]);
        }

        return $periodeHonor;
    }

    private function hitungJamWajib($jadwalGuru, $daftarLibur, $tglMulai, $tglSelesai): int
    {
        if ($jadwalGuru->isEmpty()) {
            return 0;
        }

        $jamWajib = 0;
        $period = CarbonPeriod::create($tglMulai, $tglSelesai);

        foreach ($period as $date) {
            $tglStr = $date->format('Y-m-d');
            $hariIndo = map_hari($date->format('l'));

            $jadwalHariIni = $jadwalGuru->filter(function ($j) use ($hariIndo, $tglStr) {
                $isHariSama = strtolower($j->hari) === strtolower($hariIndo);

                $mulaiAktif = $this->normalizeTanggalEfektif(
                    $j->berlaku_mulai ?? $j->tgl_efektif_mulai ?? ($j->created_at ? $j->created_at->format('Y-m-d') : '2000-01-01'),
                    false
                );
                $selesaiAktif = $this->normalizeTanggalEfektif(
                    $j->berlaku_sampai ?? $j->tgl_efektif_selesai ?? ($j->deleted_at ? $j->deleted_at->format('Y-m-d') : '2099-12-31'),
                    true
                );

                return $isHariSama && $tglStr >= $mulaiAktif && $tglStr <= $selesaiAktif;
            });

            foreach ($jadwalHariIni as $j) {
                $libur = $this->isLibur($j, $daftarLibur, $tglStr, true);
                if (!$libur['is_libur']) {
                    $jamWajib++;
                }
            }
        }

        return $jamWajib;
    }

    private function isLibur($jadwal, $daftarLibur, ?string $tglStr = null, bool $cekParsial = true): array
    {
        $isLibur = false;
        $namaLibur = '';

        foreach ($daftarLibur as $agenda) {
            if ($tglStr !== null) {
                $mulai = $agenda->tanggal_mulai instanceof Carbon
                    ? $agenda->tanggal_mulai->format('Y-m-d')
                    : Carbon::parse($agenda->tanggal_mulai)->format('Y-m-d');
                $selesai = $agenda->tanggal_selesai instanceof Carbon
                    ? $agenda->tanggal_selesai->format('Y-m-d')
                    : Carbon::parse($agenda->tanggal_selesai)->format('Y-m-d');

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
                $namaLibur = $agenda->nama_agenda . ' (' . $agenda->jenis_agenda . ' Full)';
                break;
            }

            if ($cekParsial) {
                $arrJam = is_array($agenda->jam_diliburkan)
                    ? $agenda->jam_diliburkan
                    : (json_decode($agenda->jam_diliburkan, true) ?? []);

                foreach ($arrJam as $jamLibur) {
                    if ((int) $jadwal->jam_ke === (int) $jamLibur) {
                        $isLibur = true;
                        $namaLibur = $agenda->nama_agenda . ' (Parsial)';
                        break 2;
                    }
                }
            } else {
                $isLibur = true;
                $namaLibur = $agenda->nama_agenda . ' (' . $agenda->jenis_agenda . ')';
                break;
            }
        }

        return ['is_libur' => $isLibur, 'nama_libur' => $namaLibur];
    }

    private function normalizeTanggalEfektif($nilai, bool $isBatasSelesai): string
    {
        if ($nilai instanceof Carbon || $nilai instanceof \DateTimeInterface) {
            return $nilai->format('Y-m-d');
        }

        $str = trim((string) $nilai);
        if ($str !== '') {
            return substr($str, 0, 10);
        }

        return $isBatasSelesai ? '2099-12-31' : '2000-01-01';
    }

    private function getKeterangan($persentase): string
    {
        if ($persentase >= 90) return 'Sangat Baik';
        if ($persentase >= 75) return 'Baik';
        if ($persentase >= 50) return 'Cukup';
        if ($persentase >= 25) return 'Kurang';
        return 'Sangat Kurang';
    }

    private function hitungStruktural(HonorKonfigurasi $config, Guru $guru): int
    {
        $jabatanIds = $guru->jabatans->pluck('id');

        $struktural = HonorStrukturalConfig::where('honor_konfigurasi_id', $config->id)
            ->whereIn('jabatan_id', $jabatanIds)
            ->sum('nominal');

        return (int) $struktural;
    }

    private function hitungWaliKelas(Guru $guru, $tarifWali): int
    {
        $isWali = Kelas::where('wali_kelas_id', $guru->id)->exists();
        return $isWali ? $tarifWali : 0;
    }

    public function prosesScan($qrToken, $metode = 'Scan QR'): HonorDetail|null
    {
        $detail = HonorDetail::where('qr_token', $qrToken)->first();

        if (!$detail) {
            return null;
        }

        if ($detail->is_diterima) {
            return $detail;
        }

        $detail->update([
            'is_diterima' => true,
            'waktu_diterima' => now(),
            'metode_penerimaan' => $metode,
        ]);

        return $detail;
    }
}