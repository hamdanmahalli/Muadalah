<?php

namespace App\Services;

use App\Models\JadwalHarian;
use App\Models\KehadiranGuru;
use App\Models\Guru;
use App\Models\AgendaKaldik;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;

class JadwalService
{
    /**
     * Cek apakah sebuah jadwal kena libur (berdasarkan AgendaKaldik).
     *
     * @param  JadwalHarian  $jadwal
     * @param  \Illuminate\Database\Eloquent\Collection  $daftarLibur  (AgendaKaldik)
     * @param  string|null  $tglStr  Tanggal spesifik (Y-m-d). Jika null, skip pengecekan rentang tanggal.
     * @param  bool  $cekParsial  Apakah perlu cek jam parsial (true) atau cukup cek hari (false).
     * @return array{is_libur: bool, nama_libur: string}
     */
    public function isLibur($jadwal, $daftarLibur, ?string $tglStr = null, bool $cekParsial = true): array
    {
        $isLibur = false;
        $namaLibur = '';

        foreach ($daftarLibur as $agenda) {
            // Cek rentang tanggal jika $tglStr disediakan
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

            // Cek apakah kena target kelas
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

            // Tipe Penuh -> langsung libur
            if ($agenda->tipe_agenda === 'Penuh') {
                $isLibur = true;
                $namaLibur = $agenda->nama_agenda . ' (' . $agenda->jenis_agenda . ' Full)';
                break;
            }

            // Tipe Parsial -> cek apakah jam ke ini diliburkan
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
                // Tanpa cek parsial, cukup hari saja
                $isLibur = true;
                $namaLibur = $agenda->nama_agenda . ' (' . $agenda->jenis_agenda . ')';
                break;
            }
        }

        return ['is_libur' => $isLibur, 'nama_libur' => $namaLibur];
    }

    /**
     * Normalisasi tanggal efekif jadwal menjadi 'Y-m-d'.
     * Nilai defaultSelesai memakai tanggal aman tergantung jenis batas.
     */
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

    /**
     * Hitung rekap kehadiran guru (jam wajib, hadir, izin, sakit, alpha, persentase).
     *
     * @param  int  $guruId
     * @param  string  $tglMulai
     * @param  string  $tglSelesai
     * @param  int|null  $periodeId
     * @param  string|null  $tahunAjaran
     * @param  bool  $hitungSampaiHariIni  Jika true, batasi hitungan sampai hari ini (untuk rekap pribadi).
     * @return object
     */
    public function hitungRekapGuru(
        int $guruId,
        string $tglMulai,
        string $tglSelesai,
        ?int $periodeId,
        ?string $tahunAjaran,
        bool $hitungSampaiHariIni = false
    ): object {
        $jadwalMentah = JadwalHarian::withTrashed()
            ->where('guru_id', $guruId)
            ->where('tahun_ajaran', $tahunAjaran)
            ->get();

        $daftarLibur = AgendaKaldik::where('periode_id', $periodeId)
            ->whereIn('jenis_agenda', ['Libur', 'UTS', 'UAS'])
            ->where('tanggal_mulai', '<=', $tglSelesai)
            ->where('tanggal_selesai', '>=', $tglMulai)
            ->get();

        $jamWajib = 0;
        $batasTglHitung = $hitungSampaiHariIni
            ? min($tglSelesai, date('Y-m-d'))
            : $tglSelesai;

        if ($tglMulai <= $batasTglHitung) {
            $period = CarbonPeriod::create($tglMulai, $batasTglHitung);

            foreach ($period as $date) {
                $tglStr = $date->format('Y-m-d');
                $hariIndo = map_hari($date->format('l'));

                $jadwalHariIni = $jadwalMentah->filter(function ($j) use ($hariIndo, $tglStr) {
                    $isHariSama = strtolower($j->hari) === strtolower($hariIndo)
                        || (strtolower($hariIndo) === 'ahad' && strtolower($j->hari) === 'ahad');

                    // B4: Rentang aktif pakai kolom efektif (lebih akurat dari created_at)
                    $mulaiAktif = $this->normalizeTanggalEfektif($j->berlaku_mulai ?? $j->tgl_efektif_mulai ?? ($j->created_at ? $j->created_at->format('Y-m-d') : '2000-01-01'), false);
                    $selesaiAktif = $this->normalizeTanggalEfektif($j->berlaku_sampai ?? $j->tgl_efektif_selesai ?? ($j->deleted_at ? $j->deleted_at->format('Y-m-d') : '2099-12-31'), true);
                    $isDalamRentang = $tglStr >= $mulaiAktif && $tglStr <= $selesaiAktif;

                    return $isHariSama && $isDalamRentang;
                });

                foreach ($jadwalHariIni as $j) {
                    $libur = $this->isLibur($j, $daftarLibur, $tglStr, true);
                    if (!$libur['is_libur']) {
                        $jamWajib++;
                    }
                }
            }
        }

        $kehadiran = DB::table('kehadiran_gurus')
            ->join('jadwal_harians', 'kehadiran_gurus.jadwal_id', '=', 'jadwal_harians.id')
            ->where('jadwal_harians.guru_id', $guruId)
            ->whereBetween('kehadiran_gurus.tanggal', [$tglMulai, $tglSelesai])
            ->where('kehadiran_gurus.periode_id', $periodeId)
            ->select('kehadiran_gurus.status')
            ->get();

        $hadir = $kehadiran->where('status', 'Hadir')->count();
        $izin  = $kehadiran->where('status', 'Izin')->count();
        $sakit = $kehadiran->where('status', 'Sakit')->count();
        $alpha = max(0, $jamWajib - ($hadir + $izin + $sakit));
        $persentase = $jamWajib > 0 ? round(($hadir / $jamWajib) * 100, 1) : 0;

        return (object) [
            'wajib'    => $jamWajib,
            'hadir'    => $hadir,
            'izin'     => $izin,
            'sakit'    => $sakit,
            'alpha'    => $alpha,
            'persen'   => $persentase,
        ];
    }

    /**
     * Generate data rekap untuk laporan (digunakan oleh laporanKehadiran & cetakPdf).
     *
     * @param  string  $tglMulai
     * @param  string  $tglSelesai
     * @return array{rekapData: array, totalWajib: int, totalKelasTerisi: int, totalKosong: int, daftarLibur: \Illuminate\Database\Eloquent\Collection}
     */
    public function getRekapDataLaporan(string $tglMulai, string $tglSelesai): array
    {
        $gurus = Guru::orderBy('nama_guru', 'asc')->get();
        $rekapData = [];
        $totalSeluruhWajib = 0;
        $totalSeluruhKelasTerisi = 0;
        $totalSeluruhKosong = 0;

        $periodeAktif = get_periode_aktif();
        $periodeId = $periodeAktif ? $periodeAktif->id : null;
        $tahunAjaran = $periodeAktif ? $periodeAktif->tahun_ajaran : null;

        $semuaJadwal = JadwalHarian::withTrashed()
            ->where('tahun_ajaran', $tahunAjaran)
            ->get();

        $daftarLibur = AgendaKaldik::where('periode_id', $periodeId)
            ->whereIn('jenis_agenda', ['Libur', 'UTS', 'UAS'])
            ->where('tanggal_mulai', '<=', $tglSelesai)
            ->where('tanggal_selesai', '>=', $tglMulai)
            ->get();

        // ===== B5: SATU QUERY utk semua kehadiran blok  (hindari N+1) =====
        $kehadiranSemua = DB::table('kehadiran_gurus')
            ->join('jadwal_harians', 'kehadiran_gurus.jadwal_id', '=', 'jadwal_harians.id')
            ->whereBetween('kehadiran_gurus.tanggal', [$tglMulai, $tglSelesai])
            ->where('kehadiran_gurus.periode_id', $periodeId)
            ->select(
                'jadwal_harians.guru_id',
                'kehadiran_gurus.tanggal',
                'kehadiran_gurus.status',
                'kehadiran_gurus.nig_pengganti'
            )
            ->get();

        // Piket: satu query global per NIG pengganti
        $piketSemua = DB::table('kehadiran_gurus')
            ->whereBetween('tanggal', [$tglMulai, $tglSelesai])
            ->where('periode_id', $periodeId)
            ->whereNotNull('nig_pengganti')
            ->select('tanggal', 'nig_pengganti')
            ->get();

        foreach ($gurus as $guru) {
            $jamWajib = 0;
            $period = CarbonPeriod::create($tglMulai, $tglSelesai);

            // ---- Hitung jam wajib per hari (dengan rentang kolom efektif) ----
            $hariDenganJadwal = [];
            foreach ($period as $date) {
                $tglStr = $date->format('Y-m-d');
                $hariIndo = map_hari($date->format('l'));

                $jadwalHariIni = $semuaJadwal->where('guru_id', $guru->id)
                    ->filter(function ($j) use ($hariIndo, $tglStr) {
                        $isHariSama = strtolower($j->hari) === strtolower($hariIndo)
                            || (strtolower($hariIndo) === 'ahad' && strtolower($j->hari) === 'ahad');

                        // B4: Rentang aktif pakai kolom efektif (lebih akurat dari created_at)
                        $mulaiAktif = $this->normalizeTanggalEfektif($j->berlaku_mulai ?? $j->tgl_efektif_mulai ?? ($j->created_at ? $j->created_at->format('Y-m-d') : '2000-01-01'), false);
                        $selesaiAktif = $this->normalizeTanggalEfektif($j->berlaku_sampai ?? $j->tgl_efektif_selesai ?? ($j->deleted_at ? $j->deleted_at->format('Y-m-d') : '2099-12-31'), true);

                        $isDalamRentang = $tglStr >= $mulaiAktif && $tglStr <= $selesaiAktif;

                        return $isHariSama && $isDalamRentang;
                    });

                foreach ($jadwalHariIni as $j) {
                    $libur = $this->isLibur($j, $daftarLibur, $tglStr, true);
                    if (!$libur['is_libur']) {
                        $jamWajib++;
                        $hariDenganJadwal[$tglStr] = true;
                    }
                }
            }

            if ($jamWajib === 0) {
                continue;
            }

            // ===== B5: Ambil kehadiran guru ini dari hasil query tunggal =====
            $kehadiranGuru = $kehadiranSemua->where('guru_id', $guru->id);

            $hadir = 0;
            $izin = 0;
            $sakit = 0;

            foreach ($kehadiranGuru as $kh) {
                // Simpan status awal untuk kebutuhan perhitungan
                $statusKh = $kh->status;

                // B3: Lewati tanggal yang merupakan hari libur (jangan menambah beban saat libur)
                if (!empty($hariDenganJadwal[$kh->tanggal])) {
                    // Tanggal ini kena jadwal wajib guru; tetap dipertimbangkan
                } else {
                    // Bila tanggal kehadiran bukan hari jadwal wajib guru (mis. pengganti di kelas lain),
                    // jangan dihitung sebagai hadir/izin/sakit guru ini.
                    continue;
                }

                // B2: Hadir hanya status 'Hadir' TANPA nig_pengganti
                // (kehadiran dengan nig_pengganti adalah status piket/pengganti kelas lain)
                if ($statusKh === 'Hadir' && empty($kh->nig_pengganti)) {
                    $hadir++;
                } elseif ($statusKh === 'Izin') {
                    $izin++;
                } elseif ($statusKh === 'Sakit') {
                    $sakit++;
                }
            }

            $alpha = $jamWajib - ($hadir + $izin + $sakit);
            if ($alpha < 0) {
                $alpha = 0;
            }

            // Piket = kehadiran guru ini saat jadi pengganti kelas lain
            $piket = $piketSemua->where('nig_pengganti', $guru->nig)->count();

            $kelasTerisi = $hadir + $piket;
            $persentase = $jamWajib > 0 ? round(($hadir / $jamWajib) * 100, 1) : 0;

            if ($persentase >= 85) {
                $ket = 'Sangat Baik';
                $badgeBg = 'bg-emerald-50 text-emerald-700 border-emerald-200';
            } elseif ($persentase >= 70) {
                $ket = 'Baik';
                $badgeBg = 'bg-blue-50 text-blue-700 border-blue-200';
            } elseif ($persentase >= 50) {
                $ket = 'Cukup';
                $badgeBg = 'bg-amber-50 text-amber-700 border-amber-200';
            } else {
                $ket = 'Kurang';
                $badgeBg = 'bg-rose-50 text-rose-700 border-rose-200';
            }

            $rekapData[] = (object) [
                'guru_id'    => $guru->id,
                'nama_guru'  => $guru->nama_guru,
                'jam_wajib'  => $jamWajib,
                'a'          => $alpha,
                'i'          => $izin,
                's'          => $sakit,
                'piket'        => $piket,
                'realita'      => $hadir,
                'persen'       => $persentase,
                'ket'        => $ket,
                'badge_bg'   => $badgeBg,
            ];

            $totalSeluruhWajib        += $jamWajib;
            $totalSeluruhKelasTerisi  += $kelasTerisi;
        }

        // Total Kosong = kelas yang TIDAK ada gurunya = Total Wajib - Total Kelas Terisi
        $totalSeluruhKosong = max(0, $totalSeluruhWajib - $totalSeluruhKelasTerisi);

        return [
            'rekapData'       => $rekapData,
            'totalWajib'      => $totalSeluruhWajib,
            'totalKelasTerisi'=> $totalSeluruhKelasTerisi,
            'totalKosong'     => $totalSeluruhKosong,
            'daftarLibur'     => $daftarLibur,
        ];
    }

    /**
     * Ambil riwayat mengajar pribadi guru (untuk rekap-presensi & riwayat-guru-ajax).
     *
     * @param  int  $guruId
     * @param  string  $tglMulai
     * @param  string  $tglSelesai
     * @param  int|null  $periodeId
     * @param  string|null  $tahunAjaran
     * @return array
     */
    public function getRiwayatPribadi(
        int $guruId,
        string $tglMulai,
        string $tglSelesai,
        ?int $periodeId,
        ?string $tahunAjaran
    ): array {
        $guru = Guru::find($guruId);

        $jadwalAsli = JadwalHarian::withTrashed()
            ->with(['kelas', 'pelajaran'])
            ->where('guru_id', $guruId)
            ->where('tahun_ajaran', $tahunAjaran)
            ->get();

        $kehadiranAsli = DB::table('kehadiran_gurus')
            ->whereIn('jadwal_id', $jadwalAsli->pluck('id'))
            ->whereDate('tanggal', '>=', $tglMulai)
            ->whereDate('tanggal', '<=', $tglSelesai)
            ->get();

        $daftarLibur = AgendaKaldik::where('periode_id', $periodeId)
            ->whereIn('jenis_agenda', ['Libur', 'UTS', 'UAS'])
            ->where('tanggal_mulai', '<=', $tglSelesai)
            ->where('tanggal_selesai', '>=', $tglMulai)
            ->get();

        $riwayatMentah = [];
        $batasTglHitung = min($tglSelesai, date('Y-m-d'));

        if ($tglMulai <= $batasTglHitung) {
            $period = CarbonPeriod::create($tglMulai, $batasTglHitung);
            foreach ($period as $date) {
                $tglStr = $date->format('Y-m-d');
                $hariIndo = map_hari($date->format('l'));
                $formatTanggalCantik = $hariIndo . ', ' . $date->translatedFormat('d M Y');

                $jadwalHariIni = $jadwalAsli->filter(function ($j) use ($hariIndo, $tglStr) {
                    $isHariSama = strtolower($j->hari) === strtolower($hariIndo)
                        || (strtolower($hariIndo) === 'ahad' && strtolower($j->hari) === 'ahad');

                    $mulaiAktif = $j->created_at ? $j->created_at->format('Y-m-d') : '2000-01-01';
                    $selesaiAktif = $j->deleted_at ? $j->deleted_at->format('Y-m-d') : '2099-12-31';
                    $isDalamRentang = $tglStr >= $mulaiAktif && $tglStr <= $selesaiAktif;

                    return $isHariSama && $isDalamRentang;
                });

                foreach ($jadwalHariIni as $j) {
                    $libur = $this->isLibur($j, $daftarLibur, $tglStr, true);
                    if (!$libur['is_libur']) {
                        $rekam = $kehadiranAsli->where('jadwal_id', $j->id)->where('tanggal', $tglStr)->first();
                        $statusAktual = $rekam ? $rekam->status : 'Alpa';
                        $keteranganAktual = $rekam ? $rekam->keterangan : null;

                        $riwayatMentah[] = (object) [
                            'tanggal'       => $tglStr,
                            'tanggal_indo'  => $formatTanggalCantik,
                            'status'        => $statusAktual,
                            'keterangan'    => $keteranganAktual,
                            'jam_ke'        => (int) $j->jam_ke,
                            'nama_kelas'    => trim($j->kelas->nama_kelas ?? '?'),
                            'nama_pelajaran'=> trim($j->pelajaran->nama_pelajaran ?? '?'),
                        ];
                    }
                }
            }
        }

        // Tambahkan data piket (inval)
        $piketRecords = DB::table('kehadiran_gurus')
            ->join('jadwal_harians', 'kehadiran_gurus.jadwal_id', '=', 'jadwal_harians.id')
            ->leftJoin('kelas', 'jadwal_harians.kelas_id', '=', 'kelas.id')
            ->leftJoin('pelajarans', 'jadwal_harians.pelajaran_id', '=', 'pelajarans.id')
            ->where('kehadiran_gurus.nig_pengganti', $guru->nig)
            ->whereDate('kehadiran_gurus.tanggal', '>=', $tglMulai)
            ->whereDate('kehadiran_gurus.tanggal', '<=', $tglSelesai)
            ->select('kehadiran_gurus.tanggal', 'kehadiran_gurus.keterangan', 'jadwal_harians.jam_ke', 'kelas.nama_kelas', 'pelajarans.nama_pelajaran')
            ->get();

        foreach ($piketRecords as $p) {
            $tglPiket = Carbon::parse($p->tanggal);
            $hariIndoPiket = map_hari($tglPiket->format('l'));
            $riwayatMentah[] = (object) [
                'tanggal'       => $tglPiket->format('Y-m-d'),
                'tanggal_indo'  => $hariIndoPiket . ', ' . $tglPiket->translatedFormat('d M Y'),
                'status'        => 'Piket',
                'keterangan'    => 'Inval / Mengganti Guru Lain',
                'jam_ke'        => (int) $p->jam_ke,
                'nama_kelas'    => trim($p->nama_kelas ?? '?'),
                'nama_pelajaran'=> trim($p->nama_pelajaran ?? '?'),
            ];
        }

        // Urutkan berdasarkan tanggal DESC, jam_ke ASC
        usort($riwayatMentah, function ($a, $b) {
            $tglCmp = strcmp($b->tanggal, $a->tanggal);
            if ($tglCmp === 0) {
                return $a->jam_ke <=> $b->jam_ke;
            }
            return $tglCmp;
        });

        // Grouping: gabungkan jam berurutan dengan status/kelas/pelajaran yang sama (maks 2 jam per blok)
        $groupedRiwayat = [];
        $currentGroup = null;

        foreach ($riwayatMentah as $item) {
            if ($currentGroup === null) {
                $currentGroup = clone $item;
                $currentGroup->jam_list = [$item->jam_ke];
            } else {
                $lastJam = max($currentGroup->jam_list);
                if (
                    $currentGroup->tanggal === $item->tanggal
                    && strtolower($currentGroup->status) === strtolower($item->status)
                    && strtolower($currentGroup->nama_kelas) === strtolower($item->nama_kelas)
                    && strtolower($currentGroup->nama_pelajaran) === strtolower($item->nama_pelajaran)
                    && ($lastJam + 1 === $item->jam_ke)
                    && count($currentGroup->jam_list) < 2
                ) {
                    $currentGroup->jam_list[] = $item->jam_ke;
                } else {
                    $jamMulai = min($currentGroup->jam_list);
                    $jamSelesai = max($currentGroup->jam_list);
                    $currentGroup->jam_tampil = ($jamMulai === $jamSelesai) ? (string) $jamMulai : $jamMulai . '-' . $jamSelesai;
                    $groupedRiwayat[] = $currentGroup;
                    $currentGroup = clone $item;
                    $currentGroup->jam_list = [$item->jam_ke];
                }
            }
        }

        if ($currentGroup !== null) {
            $jamMulai = min($currentGroup->jam_list);
            $jamSelesai = max($currentGroup->jam_list);
            $currentGroup->jam_tampil = ($jamMulai === $jamSelesai) ? (string) $jamMulai : $jamMulai . '-' . $jamSelesai;
            $groupedRiwayat[] = $currentGroup;
        }

        return $groupedRiwayat;
    }

    /**
     * Ambil daftar libur (AgendaKaldik) untuk rentang tanggal tertentu.
     *
     * @param  int|null  $periodeId
     * @param  string  $tglMulai
     * @param  string  $tglSelesai
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getDaftarLibur(?int $periodeId, string $tglMulai, string $tglSelesai)
    {
        return AgendaKaldik::where('periode_id', $periodeId)
            ->whereIn('jenis_agenda', ['Libur', 'UTS', 'UAS'])
            ->where('tanggal_mulai', '<=', $tglSelesai)
            ->where('tanggal_selesai', '>=', $tglMulai)
            ->get();
    }

    /**
     * Ambil semua AgendaKaldik untuk satu periode (untuk kaldikGuru).
     *
     * @param  int|null  $periodeId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getSemuaAgenda(?int $periodeId)
    {
        return AgendaKaldik::where('periode_id', $periodeId)->get();
    }
}
