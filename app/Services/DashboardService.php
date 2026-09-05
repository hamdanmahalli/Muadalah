<?php

namespace App\Services;

use App\Models\AgendaKegiatan;
use App\Models\Guru;
use App\Models\JadwalHarian;
use App\Models\KehadiranGuru;
use App\Models\MasterJam;
use Carbon\Carbon;

/**
 * Menyusun seluruh data statistik untuk Dashboard Utama TU/Admin.
 *
 * SRP: Satu tanggung jawab — menghitung kartu statistik, grafik 7 hari,
 * strip kalender, daftar monitoring, dan kartu tugas.
 */
class DashboardService
{
    protected $namaHariSingkat = [
        'Monday' => 'Sen', 'Tuesday' => 'Sel', 'Wednesday' => 'Rab',
        'Thursday' => 'Kam', 'Friday' => 'Jum', 'Saturday' => 'Sab', 'Sunday' => 'Min',
    ];

    /**
     * Kumpulan data dashboard utama.
     *
     * @return array<string, mixed>
     */
    public function dataDashboard(): array
    {
        $waktuSekarang = Carbon::now();
        $tanggalHariIni = $waktuSekarang->format('Y-m-d');
        $hariIni = map_hari($waktuSekarang->format('l'));

        $periodeAktif = get_periode_aktif();
        $tahunAjaran = $periodeAktif ? $periodeAktif->tahun_ajaran : null;

        // ===== KARTU STATISTIK ATAS =====
        $totalJadwal = JadwalHarian::where('hari', 'ilike', $hariIni)
            ->where('tahun_ajaran', $tahunAjaran)
            ->count();

        $kehadiranHariIni = KehadiranGuru::where('tanggal', $tanggalHariIni)->get();
        $guruHadir = $kehadiranHariIni->where('status', 'Hadir')->count();
        $guruIzin = $kehadiranHariIni->whereIn('status', ['Izin'])->count();
        $guruSakit = $kehadiranHariIni->whereIn('status', ['Sakit'])->count();
        $guruAlpa = $kehadiranHariIni->whereIn('status', ['Alpha', 'Kosong', 'Alpa'])->count();
        $guruMenunggu = $kehadiranHariIni->whereIn('status', ['Menunggu'])->count();
        $guruIzinKosong = $guruIzin + $guruSakit + $guruAlpa + $guruMenunggu;
        $totalGuru = Guru::count();

        // ===== GRAFIK KEHADIRAN 7 HARI =====
        $startMinggu = $waktuSekarang->copy()->startOfWeek();
        $labelsGrafik = [];
        $dataHadirGrafik = [];
        $dataIzinGrafik = [];
        $dataKosongGrafik = [];
        $dataMenungguGrafik = [];
        $sparkJadwal = [];

        for ($i = 0; $i < 7; $i++) {
            $tanggal = $startMinggu->copy()->addDays($i);
            $tanggalStr = $tanggal->format('Y-m-d');
            $labelsGrafik[] = $this->namaHariSingkat[$tanggal->format('l')] ?? $tanggal->format('D');

            $kh = KehadiranGuru::where('tanggal', $tanggalStr)->get();
            $dataHadirGrafik[] = $kh->where('status', 'Hadir')->count();
            $dataIzinGrafik[] = $kh->whereIn('status', ['Izin', 'Sakit'])->count();
            $dataKosongGrafik[] = $kh->whereIn('status', ['Alpha', 'Kosong', 'Alpa'])->count();
            $dataMenungguGrafik[] = $kh->where('status', 'Menunggu')->count();

            $hr = map_hari($tanggal->format('l'));
            $sparkJadwal[] = JadwalHarian::where('hari', 'ilike', $hr)
                ->where('tahun_ajaran', $tahunAjaran)
                ->count();
        }

        // ===== DELTA & SPARK =====
        $rataRataHadir7 = count($dataHadirGrafik) > 0 ? (array_sum($dataHadirGrafik) / count($dataHadirGrafik)) : 0;
        $rataRataIzinKosong7 = (count($dataIzinGrafik) + count($dataKosongGrafik)) > 0 ? ((array_sum($dataIzinGrafik) + array_sum($dataKosongGrafik)) / 7) : 0;
        $rataRataJadwal7 = count($sparkJadwal) > 0 ? (array_sum($sparkJadwal) / count($sparkJadwal)) : 0;

        $deltaTotalJadwal = $rataRataJadwal7 > 0 ? round((($totalJadwal - $rataRataJadwal7) / $rataRataJadwal7) * 100, 1) : 0;
        $deltaGuruHadir = $rataRataHadir7 > 0 ? round((($guruHadir - $rataRataHadir7) / $rataRataHadir7) * 100, 1) : 0;
        $deltaIzinKosong = $rataRataIzinKosong7 > 0 ? round((($guruIzinKosong - $rataRataIzinKosong7) / $rataRataIzinKosong7) * 100, 1) : 0;
        $deltaTotalGuru = 0;

        $sparkHadir = $dataHadirGrafik;
        $sparkIzin = array_map(function ($izin, $alpa) { return $izin + $alpa; }, $dataIzinGrafik, $dataKosongGrafik);

        // ===== STRIP KALENDER =====
        $stripMinggu = [];
        for ($i = 0; $i < 7; $i++) {
            $tanggal = $startMinggu->copy()->addDays($i);
            $stripMinggu[] = [
                'nama'    => $this->namaHariSingkat[$tanggal->format('l')] ?? $tanggal->format('D'),
                'tanggal' => (int) $tanggal->format('d'),
                'bulan'   => $tanggal->format('M'),
                'penuh'   => $tanggal->format('Y-m-d'),
                'aktif'   => ($tanggal->format('Y-m-d') === $tanggalHariIni),
            ];
        }

        // ===== DAFTAR MONITORING =====
        $monitorGuru = [];
        $queryMonitor = KehadiranGuru::where('tanggal', $tanggalHariIni)
            ->whereIn('status', ['Izin', 'Kosong', 'Alpha', 'Sakit'])
            ->orderBy('id')
            ->get();

        foreach ($queryMonitor as $kh) {
            $jadwal = $kh->jadwal_id ? JadwalHarian::with(['guru', 'kelas'])->find($kh->jadwal_id) : null;
            $guruInfo = $jadwal ? $jadwal->guru : null;
            $kelasNama = $jadwal && $jadwal->kelas ? $jadwal->kelas->nama_kelas : '-';
            $jamKe = ($jadwal && $jadwal->jam_ke) ? 'Jam Ke-' . $jadwal->jam_ke : '';

            $monitorGuru[] = [
                'nama'   => $guruInfo ? $guruInfo->nama_guru : ($kh->nig_pengganti ?? '-'),
                'kelas'  => $kelasNama,
                'jam'    => $jamKe,
                'status' => $kh->status,
                'nig'    => $guruInfo ? $guruInfo->nig : null,
            ];
            if (count($monitorGuru) >= 6) break;
        }
        $belumCatatCount = max(0, $totalJadwal - $kehadiranHariIni->count());

        // ===== KARTU TUGAS =====
        $jmlAgendaTotal = AgendaKegiatan::count();
        $jmlAgendaMendatang = AgendaKegiatan::whereDate('tanggal', '>=', $tanggalHariIni)->count();

        $kartuTugas = [
            [
                'judul' => 'Periode Aktif',
                'sub'   => $periodeAktif ? 'TA. ' . $periodeAktif->tahun_ajaran . ' (' . $periodeAktif->semester . ')' : 'Belum diatur',
                'ikon'  => 'fa-calendar-check', 'warna' => 'sky',
                'link'  => '/master-periode', 'pct' => $periodeAktif ? 100 : 0,
            ],
            [
                'judul' => 'Agenda Kegiatan',
                'sub'   => $jmlAgendaMendatang . ' agenda mendatang',
                'ikon'  => 'fa-calendar-alt', 'warna' => 'indigo',
                'link'  => '/agenda-kegiatan',
                'pct'   => $jmlAgendaTotal > 0 ? (int) round(($jmlAgendaMendatang / $jmlAgendaTotal) * 100) : 0,
            ],
        ];

        // ===== MONITORING GURU (JAM AKTIF SEKARANG) =====
        $monitor = $this->susunMonitorAktif($tanggalHariIni, $hariIni, $tahunAjaran);
        $monitorGuruAktif = $monitor['guru'];
        $blokAktif = $monitor['blok'];

        return compact(
            'totalJadwal', 'guruHadir', 'guruIzin', 'guruSakit', 'guruAlpa', 'guruMenunggu',
            'guruIzinKosong', 'totalGuru', 'waktuSekarang',
            'labelsGrafik', 'dataHadirGrafik', 'dataIzinGrafik', 'dataKosongGrafik', 'dataMenungguGrafik', 'sparkJadwal',
            'sparkHadir', 'sparkIzin', 'stripMinggu', 'monitorGuru', 'belumCatatCount', 'kartuTugas',
            'monitorGuruAktif', 'blokAktif',
            'deltaTotalJadwal', 'deltaGuruHadir', 'deltaIzinKosong', 'deltaTotalGuru'
        );
    }

    /**
     * Susun daftar guru yang mengajar pada blok jam AKTIF sekarang.
     *
     * Blok jam dibentuk berpasangan dari MasterJam (mis. 1-2, 3-4, ...).
     * Bila waktu sekarang berada dalam rentang sebuah blok, seluruh guru yang
     * terjadwal pada blok itu ditampilkan dengan STATUS ASLI masing-masing
     * (Hadir/Izin/Sakit/Alpa/Menunggu). Status "Menunggu" TIDAK diubah menjadi
     * Alpa — ia hanya menandakan slot belum tercatat/pending.
     *
     * @return array{guru: array<int,array<string,mixed>>, blok: array<string,mixed>|null}
     */
    protected function susunMonitorAktif(string $tglStr, string $hariIni, ?string $tahunAjaran): array
    {
        $semuaJam = MasterJam::orderBy('jam_ke', 'asc')->get();
        $daftarBlok = [];

        for ($i = 0; $i < count($semuaJam); $i += 2) {
            $jam1 = $semuaJam[$i];
            $jam2 = $semuaJam[$i + 1] ?? $jam1;

            $key = ($jam1->jam_ke == $jam2->jam_ke) ? (string) $jam1->jam_ke : $jam1->jam_ke . '-' . $jam2->jam_ke;
            $daftarBlok[] = [
                'key'         => $key,
                'label'       => 'Jam Ke-' . $key,
                'waktu'       => Carbon::parse($jam1->jam_mulai)->format('H:i') . ' - ' . Carbon::parse($jam2->jam_selesai)->format('H:i'),
                'jam_ke_awal' => (int) $jam1->jam_ke,
                'jam_ke_akhir'=> (int) $jam2->jam_ke,
                'jam_mulai'   => $jam1->jam_mulai,
                'jam_selesai' => $jam2->jam_selesai,
            ];
        }

        $waktuSekarang = Carbon::now();

        // Blok aktif = blok yang rentang waktunya mencakup sekarang
        $blokAktif = null;
        foreach ($daftarBlok as $b) {
            $mulai = Carbon::parse($b['jam_mulai']);
            $selesai = Carbon::parse($b['jam_selesai']);
            if ($waktuSekarang->between($mulai, $selesai)) {
                $blokAktif = $b;
                break;
            }
        }

        // Bila belum ada blok yang sedang berlangsung, jadikan blok pertama.
        $blokAktif = $blokAktif ?? ($daftarBlok[0] ?? null);
        if ($blokAktif === null) {
            return ['guru' => [], 'blok' => null];
        }

        $jadwals = JadwalHarian::with(['kelas', 'guru'])
            ->where('hari', 'ilike', $hariIni)
            ->where('tahun_ajaran', $tahunAjaran)
            ->whereBetween('jam_ke', [$blokAktif['jam_ke_awal'], $blokAktif['jam_ke_akhir']])
            ->get();

        $records = KehadiranGuru::where('tanggal', $tglStr)->get()->keyBy('jadwal_id');

        // Kelompokkan per guru + kelas, lalu gabungkan jam-nya menjadi blok
        // (mis. 1-2, 3-4) agar nama guru tidak tampil dua kali.
        $kelompok = [];
        foreach ($jadwals as $jadwal) {
            $record = $records->get($jadwal->id);
            $statusAsli = $record ? $record->status : 'Menunggu';
            $statusTampil = in_array($statusAsli, ['Kosong', 'Alpha']) ? 'Alpa' : $statusAsli;

            $nama = $jadwal->guru->nama_guru ?? '-';
            $kunci = $nama . '|' . ($jadwal->guru->nig ?? '') . '|' . ($jadwal->kelas->nama_kelas ?? '-');

            if (! isset($kelompok[$kunci])) {
                $kelompok[$kunci] = [
                    'nama'     => $nama,
                    'nig'      => $jadwal->guru->nig ?? null,
                    'kelas'    => $jadwal->kelas->nama_kelas ?? '-',
                    'jam_list' => [(int) $jadwal->jam_ke],
                    'status'   => $statusTampil,
                ];
            } else {
                $kelompok[$kunci]['jam_list'][] = (int) $jadwal->jam_ke;
                if ($statusTampil !== 'Hadir' && $kelompok[$kunci]['status'] === 'Hadir') {
                    $kelompok[$kunci]['status'] = $statusTampil;
                }
            }
        }

        $guru = [];
        foreach ($kelompok as $row) {
            $jamList = array_values(array_unique($row['jam_list']));
            sort($jamList);

            $guru[] = [
                'nama'       => $row['nama'],
                'nig'        => $row['nig'],
                'kelas'      => $row['kelas'],
                'jam_ke'     => $jamList[0] ?? 0,
                'jam_tampil' => $this->formatBlokJam($jamList),
                'status'     => $row['status'],
            ];
        }

        // urutkan: yang bermasalah/menunggu di atas, lalu hadir
        usort($guru, fn ($a, $b) =>
            strcmp($a['status'] === 'Hadir' ? 'z' : 'a', $b['status'] === 'Hadir' ? 'z' : 'a'));

        return ['guru' => $guru, 'blok' => $blokAktif];
    }

    /**
     * Gabungkan daftar jam_ke menjadi blok berurutan (maks 2 jam per blok),
     * mengikuti konvensi tampilan jam di aplikasi: 1-2, 3-4, dst.
     *
     * @param  array<int, int>  $jamList
     */
    protected function formatBlokJam(array $jamList): string
    {
        $blok = [];
        $sekarang = [];

        foreach ($jamList as $jam) {
            if ($sekarang === []) {
                $sekarang[] = $jam;
            } elseif (($jam === max($sekarang) + 1) && count($sekarang) < 2) {
                $sekarang[] = $jam;
            } else {
                $blok[] = (count($sekarang) === 1) ? (string) $sekarang[0] : min($sekarang) . '-' . max($sekarang);
                $sekarang = [$jam];
            }
        }

        if ($sekarang !== []) {
            $blok[] = (count($sekarang) === 1) ? (string) $sekarang[0] : min($sekarang) . '-' . max($sekarang);
        }

        return implode(', ', $blok);
    }
}
