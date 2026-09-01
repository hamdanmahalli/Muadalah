<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\JadwalHarian;
use App\Models\KehadiranGuru;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Pelajaran;
use App\Models\MasterJam;
use App\Services\JadwalService;
use Carbon\Carbon;

class JadwalController extends Controller
{
    protected JadwalService $service;

    public function __construct(JadwalService $service)
    {
        $this->service = $service;
    }

    // ========================================================
    // 1. DASHBOARD UTAMA
    // ========================================================
    public function dashboard()
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
        // Sakit dimasukkan ke kategori "perlu perhatian" agar konsisten dengan monitoring
        $guruIzinKosong = $kehadiranHariIni->whereIn('status', ['Izin', 'Kosong', 'Alpha', 'Sakit'])->count();
        $totalGuru = Guru::count();

        // ===== GRAFIK KEHADIRAN 7 HARI (Area Chart) =====
        $labelsGrafik = [];
        $dataHadirGrafik = [];
        $dataIzinGrafik = [];
        $dataKosongGrafik = [];
        $sparkJadwal = [];
        $namaHariSingkat = ['Monday' => 'Sen', 'Tuesday' => 'Sel', 'Wednesday' => 'Rab', 'Thursday' => 'Kam', 'Friday' => 'Jum', 'Saturday' => 'Sab', 'Sunday' => 'Min'];

        $startMinggu = $waktuSekarang->copy()->startOfWeek();
        for ($i = 0; $i < 7; $i++) {
            $tanggal = $startMinggu->copy()->addDays($i);
            $tanggalStr = $tanggal->format('Y-m-d');
            $labelHari = $namaHariSingkat[$tanggal->format('l')] ?? $tanggal->format('D');
            $labelsGrafik[] = $labelHari;

            $kh = KehadiranGuru::where('tanggal', $tanggalStr)->get();
            $dataHadirGrafik[] = $kh->where('status', 'Hadir')->count();
            $dataIzinGrafik[] = $kh->whereIn('status', ['Izin', 'Kosong', 'Sakit'])->count();
            $dataKosongGrafik[] = $kh->where('status', 'Alpha')->count();

            $hr = map_hari($tanggal->format('l'));
            $sparkJadwal[] = JadwalHarian::where('hari', 'ilike', $hr)
                                          ->where('tahun_ajaran', $tahunAjaran)
                                          ->count();
        }

        // ===== DATA PENDUKUNG KARTU STATISTIK ATAS (delta & spark per kartu) =====
        $rataRataHadir7 = count($dataHadirGrafik) > 0 ? (array_sum($dataHadirGrafik) / count($dataHadirGrafik)) : 0;
        $rataRataIzinKosong7 = (count($dataIzinGrafik) + count($dataKosongGrafik)) > 0 ? ((array_sum($dataIzinGrafik) + array_sum($dataKosongGrafik)) / 7) : 0;
        $rataRataJadwal7 = count($sparkJadwal) > 0 ? (array_sum($sparkJadwal) / count($sparkJadwal)) : 0;

        $deltaTotalJadwal = $rataRataJadwal7 > 0 ? round((($totalJadwal - $rataRataJadwal7) / $rataRataJadwal7) * 100, 1) : 0;
        $deltaGuruHadir = $rataRataHadir7 > 0 ? round((($guruHadir - $rataRataHadir7) / $rataRataHadir7) * 100, 1) : 0;
        $deltaIzinKosong = $rataRataIzinKosong7 > 0 ? round((($guruIzinKosong - $rataRataIzinKosong7) / $rataRataIzinKosong7) * 100, 1) : 0;
        $deltaTotalGuru = 0; // belum ada data pembanding historis total guru

        $sparkHadir = $dataHadirGrafik;
        $sparkIzin = array_map(function ($izin, $alpa) { return $izin + $alpa; }, $dataIzinGrafik, $dataKosongGrafik);

        // ===== STRIP KALENDER MINGGU INI =====
        $stripMinggu = [];
        for ($i = 0; $i < 7; $i++) {
            $tanggal = $startMinggu->copy()->addDays($i);
            $stripMinggu[] = [
                'nama'   => $namaHariSingkat[$tanggal->format('l')] ?? $tanggal->format('D'),
                'tanggal'=> (int) $tanggal->format('d'),
                'bulan'  => $tanggal->format('M'),
                'penuh'  => $tanggal->format('Y-m-d'),
                'aktif'  => ($tanggal->format('Y-m-d') === $tanggalHariIni),
            ];
        }

        // ===== LIST MONITORING (analog "daftar pasien") =====
        $monitorGuru = [];
$queryMonitor = KehadiranGuru::where('tanggal', $tanggalHariIni)
                                     ->whereIn('status', ['Izin', 'Kosong', 'Alpha', 'Sakit'])
                                     ->orderBy('id')
                                     ->get();
        foreach ($queryMonitor as $kh) {
            $jadwal = $kh->jadwal_id ? JadwalHarian::with(['guru', 'kelas'])->find($kh->jadwal_id) : null;
            $guruInfo = $jadwal ? $jadwal->guru : null;
            $kelasNama = $jadwal && $jadwal->kelas ? $jadwal->kelas->nama_kelas : '-';
            $jamKe = ($jadwal && $jadwal->jam_ke) ? 'Jam Ke-'.$jadwal->jam_ke : '';

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

// ===== KARTU TUGAS 2x2 (persentase data riil) =====
        $jmlHariOperasionalAktif = \App\Models\HariOperasional::where('is_active', true)->count();
        $jmlAgendaTotal = \App\Models\AgendaKegiatan::count();
        $jmlAgendaMendatang = \App\Models\AgendaKegiatan::whereDate('tanggal', '>=', $tanggalHariIni)->count();
        $jmlPlotTotal = \App\Models\PlotJadwal::count();
        $jmlPlotBerGuru = \App\Models\PlotJadwal::whereNotNull('guru_id')->count();

        $kartuTugas = [
            [
                'judul'   => 'Periode Aktif',
                'sub'     => $periodeAktif ? 'TA. '.$periodeAktif->tahun_ajaran.' ('.$periodeAktif->semester.')' : 'Belum diatur',
                'ikon'    => 'fa-calendar-check',
                'warna'   => 'sky',
                'link'    => '/master-periode',
                'pct'     => $periodeAktif ? 100 : 0,
            ],
            [
                'judul'   => 'Hari Operasional',
                'sub'     => $jmlHariOperasionalAktif.' dari 7 hari aktif',
                'ikon'    => 'fa-calendar-week',
                'warna'   => 'emerald',
                'link'    => '/master-hari-operasional',
                'pct'     => $jmlHariOperasionalAktif > 0 ? (int) round(($jmlHariOperasionalAktif / 7) * 100) : 0,
            ],
            [
                'judul'   => 'Agenda Kegiatan',
                'sub'     => $jmlAgendaMendatang.' agenda mendatang',
                'ikon'    => 'fa-calendar-alt',
                'warna'   => 'indigo',
                'link'    => '/agenda-kegiatan',
                'pct'     => $jmlAgendaTotal > 0 ? (int) round(($jmlAgendaMendatang / $jmlAgendaTotal) * 100) : 0,
            ],
            [
                'judul'   => 'Target Mengajar',
                'sub'     => $jmlPlotBerGuru.' dari '.$jmlPlotTotal.' plot ber-guru',
                'ikon'    => 'fa-sitemap',
                'warna'   => 'rose',
                'link'    => '/master-plot-jadwal',
                'pct'     => $jmlPlotTotal > 0 ? (int) round(($jmlPlotBerGuru / $jmlPlotTotal) * 100) : 0,
            ],
        ];

        return view('dashboard', compact(
            'totalJadwal', 'guruHadir', 'guruIzinKosong', 'totalGuru', 'waktuSekarang',
            'labelsGrafik', 'dataHadirGrafik', 'dataIzinGrafik', 'dataKosongGrafik', 'sparkJadwal',
            'sparkHadir', 'sparkIzin', 'stripMinggu', 'monitorGuru', 'belumCatatCount', 'kartuTugas',
            'deltaTotalJadwal', 'deltaGuruHadir', 'deltaIzinKosong', 'deltaTotalGuru'
        ));
    }

    // ========================================================
    // 2. MEJA KONTROL (SISTEM BLOK JAM OTOMATIS)
    // ========================================================
    public function mejaKontrol(Request $request)
    {
        $tanggalPilihan = $request->input('tanggal', Carbon::now()->format('Y-m-d'));
        $waktuSekarang = Carbon::parse($tanggalPilihan);
        
        $waktuAsliKomputer = Carbon::now();
        $isHariIni = ($tanggalPilihan === $waktuAsliKomputer->format('Y-m-d'));
        $jamSekarang = $waktuAsliKomputer->format('H:i:s');
        
        $hariIni = map_hari($waktuSekarang->format('l'));

        $semuaJam = MasterJam::orderBy('jam_ke', 'asc')->get();
        
        $opsiBlokJam = [];
        $blokAktifOtomatis = null;

        for ($i = 0; $i < count($semuaJam); $i += 2) {
            $jam1 = $semuaJam[$i];
            $jam2 = $semuaJam[$i + 1] ?? $jam1; 
            
            $keyBlok = $jam1->jam_ke . '-' . $jam2->jam_ke;
            if ($jam1->jam_ke == $jam2->jam_ke) {
                $keyBlok = (string) $jam1->jam_ke;
            }

            $waktu = Carbon::parse($jam1->jam_mulai)->format('H:i') . ' - ' . Carbon::parse($jam2->jam_selesai)->format('H:i');
            $opsiBlokJam[] = ['nilai' => $keyBlok, 'label' => "Jam Ke-$keyBlok ($waktu)"];

            if ($isHariIni && $jamSekarang >= $jam1->jam_mulai && $jamSekarang <= $jam2->jam_selesai) {
                $blokAktifOtomatis = $keyBlok;
            }
        }

        $jamDefault = $blokAktifOtomatis ?? ($opsiBlokJam[0]['nilai'] ?? '1-2');
        $jamPilihan = $request->input('jam', $jamDefault);
        $arrayJamPilihan = explode('-', $jamPilihan); 

        $periodeAktif = get_periode_aktif();
        $tahunAjaran = $periodeAktif ? $periodeAktif->tahun_ajaran : null;
        $periodeId = $periodeAktif ? $periodeAktif->id : null;

        $jadwalsMentah = JadwalHarian::with(['kelas', 'pelajaran', 'guru'])
                         ->where('hari', 'ilike', $hariIni)
                         ->whereIn('jam_ke', $arrayJamPilihan)
                         ->where('tahun_ajaran', $tahunAjaran)
                         ->get();

        $daftarLibur = $this->service->getDaftarLibur($periodeId, $tanggalPilihan, $tanggalPilihan);

        $jadwals = [];
        foreach ($jadwalsMentah as $j) {
            $kunci = ($j->kelas_id ?? '0') . '_' . ($j->guru_id ?? '0') . '_' . ($j->pelajaran_id ?? '0'); 
            
            $libur = $this->service->isLibur($j, $daftarLibur, null, true);
            $isLibur = $libur['is_libur'];
            $namaLibur = $libur['nama_libur'];

            $namaKelas = $j->kelas->nama_kelas ?? 'Kelas -';
            $tingkatKelas = preg_replace('/[^0-9]/', '', $namaKelas); 
            $petaKitab = $j->pelajaran->kitab_tingkat ?? [];
            $namaKitab = $petaKitab[$tingkatKelas] ?? ($j->pelajaran->nama_kitab ?? '-');

            if (!isset($jadwals[$kunci])) {
                $jadwals[$kunci] = [
                    'id_list' => [], 
                    'kelas' => $namaKelas,
                    'mata_pelajaran' => $j->pelajaran->nama_pelajaran ?? 'Tanpa Pelajaran',
                    'nama_kitab' => $namaKitab,
                    'nig_guru' => $j->guru->nig ?? '-',
                    'nama_guru' => $j->guru->nama_guru ?? 'Belum ada guru',
                    'jam_tampil' => $jamPilihan,
                    'is_libur' => $isLibur,
                    'nama_libur' => $namaLibur
                ];
            } else {
                if ($isLibur) {
                    $jadwals[$kunci]['is_libur'] = true;
                    $jadwals[$kunci]['nama_libur'] = $namaLibur;
                }
            }
            $jadwals[$kunci]['id_list'][] = $j->id;
        }

        $infoJamLengkap = collect($opsiBlokJam)->firstWhere('nilai', $jamPilihan);
        $infoJam = $infoJamLengkap ? $infoJamLengkap['label'] : "Jam Ke-" . $jamPilihan;
        $daftarGuru = Guru::all();
        $kehadiranHariIni = KehadiranGuru::whereDate('tanggal', $tanggalPilihan)->get()->keyBy('jadwal_id');

        $jadwals = collect($jadwals)->sortBy('kelas')->values()->all();

        return view('meja-kontrol', compact('jadwals', 'kehadiranHariIni', 'infoJam', 'daftarGuru', 'opsiBlokJam', 'jamPilihan', 'hariIni', 'tanggalPilihan', 'waktuSekarang', 'daftarLibur'));
    }

    // ========================================================
    // 3. SIMPAN KEHADIRAN (AJAX)
    // ========================================================
    public function simpanKehadiran(Request $request)
    {
        $request->validate([
            'jadwal_ids' => 'required|array',
            'status' => 'required|string',
            'tanggal' => 'nullable|date'
        ]);

        $periodeAktif = get_periode_aktif();
        $periodeId = $periodeAktif ? $periodeAktif->id : null;

        $tanggalPilihan = $request->tanggal ?? Carbon::now()->format('Y-m-d');

        foreach ($request->jadwal_ids as $id) {
            KehadiranGuru::updateOrCreate(
                [
                    'jadwal_id' => $id,
                    'tanggal' => $tanggalPilihan
                ],
                [
                    'status' => $request->status,
                    'nig_pengganti' => $request->nig_pengganti ?? null,
                    'keterangan' => $request->keterangan ?? null,
                    'periode_id' => $periodeId 
                ]
            );
        }

        return response()->json(['pesan' => 'Status blok berhasil dikunci!']);
    }

    // ========================================================
    // 4. LAPORAN REKAPITULASI (WEB)
    // ========================================================
    public function laporanKehadiran(Request $request)
    {
        $tglMulai = $request->input('tgl_mulai', date('Y-m-01'));
        $tglSelesai = $request->input('tgl_selesai', date('Y-m-d'));
        $periodeTeks = Carbon::parse($tglMulai)->translatedFormat('d F Y') . " s/d " . Carbon::parse($tglSelesai)->translatedFormat('d F Y');

        $result = $this->service->getRekapDataLaporan($tglMulai, $tglSelesai);

        $rekapData = $result['rekapData'];
        $totalSeluruhWajib = $result['totalWajib'];
        $totalSeluruhKelasTerisi = $result['totalKelasTerisi'];
        $totalSeluruhKosong = $result['totalKosong'];
        $daftarLibur = $result['daftarLibur'];

        $persenTotalKelasTerisi = $totalSeluruhWajib > 0 ? round(($totalSeluruhKelasTerisi / $totalSeluruhWajib) * 100, 1) : 0;
        $persenTotalKosong = $totalSeluruhWajib > 0 ? round(($totalSeluruhKosong / $totalSeluruhWajib) * 100, 1) : 0;

        return view('laporan', compact(
            'rekapData', 'tglMulai', 'tglSelesai', 'periodeTeks',
            'totalSeluruhWajib', 'totalSeluruhKelasTerisi', 'totalSeluruhKosong',
            'persenTotalKelasTerisi', 'persenTotalKosong', 'daftarLibur'
        ));
    }

    // ========================================================
    // 5. CETAK PDF (STRUKTUR DATA IDENTIK DENGAN WEB)
    // ========================================================
    public function cetakPdf(Request $request)
    {
        $tglMulai = $request->input('tgl_mulai', date('Y-m-01'));
        $tglSelesai = $request->input('tgl_selesai', date('Y-m-d'));
        $periodeTeks = Carbon::parse($tglMulai)->translatedFormat('d F Y') . " s/d " . Carbon::parse($tglSelesai)->translatedFormat('d F Y');

        $result = $this->service->getRekapDataLaporan($tglMulai, $tglSelesai);

        $rekapData = $result['rekapData'];
        $totalSeluruhWajib = $result['totalWajib'];
        $totalSeluruhKelasTerisi = $result['totalKelasTerisi'];
        $totalSeluruhKosong = $result['totalKosong'];
        $daftarLibur = $result['daftarLibur'];

        $persenTotalKelasTerisi = $totalSeluruhWajib > 0 ? round(($totalSeluruhKelasTerisi / $totalSeluruhWajib) * 100, 1) : 0;
        $persenTotalKosong = $totalSeluruhWajib > 0 ? round(($totalSeluruhKosong / $totalSeluruhWajib) * 100, 1) : 0;

        $pdf = Pdf::loadView('laporan-pdf', compact(
            'rekapData', 'tglMulai', 'tglSelesai', 'periodeTeks',
            'totalSeluruhWajib', 'totalSeluruhKelasTerisi', 'totalSeluruhKosong',
            'persenTotalKelasTerisi', 'persenTotalKosong', 'daftarLibur'
        ));

        return $pdf->download('Rekap_Kehadiran_'.$tglMulai.'_hingga_'.$tglSelesai.'.pdf');
    }

    // Hitungan rekap di-cache per rentang tanggal, dipakai ulang oleh web & PDF agar tidak diulang.
    // Hanya data murni (array/scalar) yang di-cache — hindari serialisasi objek (stdClass/Eloquent)
    // yang bisa memicu "incomplete object" saat unserialize akibat opcache.preload.


    // FITUR BARU: Menyuplai data terbaru untuk Radar Layar TU
    public function cekKehadiranTerbaru()
    {
        $tanggalHariIni = Carbon::now()->format('Y-m-d');
        $kehadiran = KehadiranGuru::where('tanggal', $tanggalHariIni)
                        ->get(['jadwal_id', 'status', 'keterangan', 'nig_pengganti']);
                        
        return response()->json($kehadiran);
    }

    // ==========================================================
    // FUNGSI BANTUAN 2: Pop-Up AJAX Riwayat (Layar Admin)
    // ==========================================================
    public function riwayatGuruAjax(Request $request)
    {
        $guruId = $request->guru_id;
        $tglMulai = $request->tgl_mulai;
        $tglSelesai = $request->tgl_selesai;

        $guru = Guru::find($guruId);
        if (!$guru) {
            return response()->json([]);
        }

        $periodeAktif = get_periode_aktif();
        $tahunAjaran = $periodeAktif ? $periodeAktif->tahun_ajaran : null;
        $periodeId = $periodeAktif ? $periodeAktif->id : null;

        $riwayat = $this->service->getRiwayatPribadi($guruId, $tglMulai, $tglSelesai, $periodeId, $tahunAjaran);

        return response()->json($riwayat);
    }

    // ==========================================================
    // KHUSUS GURU: HALAMAN JADWAL & RIWAYAT SAYA (PERIODE DINAMIS)
    // ==========================================================
    public function jadwalSaya()
    {
        $user = auth()->user();
        $guru = Guru::where('nama_guru', $user->name)->first();

        if (!$guru) {
            return view('jadwal-saya', [
                'guru' => null, 
                'jadwalTerstruktur' => [], 
                'pesan' => 'Akun Anda belum terhubung dengan Data Guru. Silakan hubungi Administrator/TU.'
            ]);
        }

        $periodeAktif = get_periode_aktif();
        $tahunAjaran = $periodeAktif ? $periodeAktif->tahun_ajaran : null;
        $periodeId = $periodeAktif ? $periodeAktif->id : null;

        $jadwalMentah = JadwalHarian::with(['kelas', 'pelajaran'])
            ->where('guru_id', $guru->id)
            ->where('tahun_ajaran', $tahunAjaran)
            ->orderBy('hari', 'asc')
            ->orderBy('jam_ke', 'asc')
            ->get();

        $jadwalTerstruktur = [];
        $urutanHari = ['Senin' => 1, 'Selasa' => 2, 'Rabu' => 3, 'Kamis' => 4, 'Jumat' => 5, 'Sabtu' => 6, 'Ahad' => 7];

        $jadwalGrouped = $jadwalMentah->groupBy('hari')->sortBy(function ($item, $key) use ($urutanHari) {
            return $urutanHari[$key] ?? 99;
        });

        foreach ($jadwalGrouped as $hari => $jadwals) {
            $blokJadwal = [];
            $currentBlock = null;

            foreach ($jadwals as $j) {
                $namaPel = $j->pelajaran->nama_pelajaran ?? '-';
                $namaKel = $j->kelas->nama_kelas ?? '-';

                if (!$currentBlock) {
                    $currentBlock = ['jam_mulai' => $j->jam_ke, 'jam_selesai' => $j->jam_ke, 'pelajaran' => $namaPel, 'kelas' => $namaKel];
                } else {
                    if ($currentBlock['pelajaran'] == $namaPel && $currentBlock['kelas'] == $namaKel && $j->jam_ke == $currentBlock['jam_selesai'] + 1) {
                        $currentBlock['jam_selesai'] = $j->jam_ke;
                    } else {
                        $blokJadwal[] = $currentBlock;
                        $currentBlock = ['jam_mulai' => $j->jam_ke, 'jam_selesai' => $j->jam_ke, 'pelajaran' => $namaPel, 'kelas' => $namaKel];
                    }
                }
            }
            if ($currentBlock) {
                $blokJadwal[] = $currentBlock;
            }
            if (count($blokJadwal) > 0) {
                $jadwalTerstruktur[$hari] = $blokJadwal;
            }
        }

        $sekarang = Carbon::now();
        $tglMulaiPeriode   = ($periodeAktif && $periodeAktif->tanggal_mulai) ? $periodeAktif->tanggal_mulai : $sekarang->copy()->startOfYear()->format('Y-m-d');
        $tglSelesaiPeriode = ($periodeAktif && $periodeAktif->tanggal_selesai) ? $periodeAktif->tanggal_selesai : $sekarang->copy()->endOfYear()->format('Y-m-d');

        $awalBulan = $sekarang->copy()->startOfMonth()->format('Y-m-d');
        $akhirBulan = $sekarang->copy()->endOfMonth()->format('Y-m-d');

        if ($awalBulan < $tglMulaiPeriode) {
            $awalBulan = $tglMulaiPeriode;
        }
        if ($akhirBulan > $tglSelesaiPeriode) {
            $akhirBulan = $tglSelesaiPeriode;
        }

        $rekapBulan = $this->service->hitungRekapGuru($guru->id, $awalBulan, $akhirBulan, $periodeId, $tahunAjaran, true);
        $rekapTahun = $this->service->hitungRekapGuru($guru->id, $tglMulaiPeriode, $tglSelesaiPeriode, $periodeId, $tahunAjaran, true);

        return view('jadwal-saya', compact('guru', 'jadwalTerstruktur', 'periodeAktif', 'rekapBulan', 'rekapTahun'));
    }

    // ==========================================================
    // KHUSUS GURU: HALAMAN REKAP PRESENSI PRIBADI & RIWAYAT
    // ==========================================================
    public function rekapPresensiPribadi(Request $request)
    {
        $user = auth()->user();
        $guru = Guru::where('nama_guru', $user->name)->orWhere('nig', $user->username)->first();

        if (!$guru) {
            return redirect('/dashboard-guru')->with('pesan', 'Akun Anda belum terhubung dengan Data Master Guru.');
        }

        $periodeAktif = get_periode_aktif();
        $tahunAjaran = $periodeAktif ? $periodeAktif->tahun_ajaran : null;
        $periodeId = $periodeAktif ? $periodeAktif->id : null;

        $sekarang = Carbon::now();
        $awalBulan = $sekarang->copy()->startOfMonth()->format('Y-m-d');
        $akhirBulan = $sekarang->copy()->endOfMonth()->format('Y-m-d');

        $tglMulaiPeriode   = ($periodeAktif && $periodeAktif->tanggal_mulai) ? $periodeAktif->tanggal_mulai : $sekarang->copy()->startOfYear()->format('Y-m-d');
        $tglSelesaiPeriode = ($periodeAktif && $periodeAktif->tanggal_selesai) ? $periodeAktif->tanggal_selesai : $sekarang->copy()->endOfYear()->format('Y-m-d');

        $filterTipe = $request->input('filter_tipe', 'bulan');
        $tglMulai = $request->input('tgl_mulai', $awalBulan);
        $tglSelesai = $request->input('tgl_selesai', $akhirBulan);

        if ($filterTipe == 'tahun') {
            $tglMulai = $tglMulaiPeriode;
            $tglSelesai = $tglSelesaiPeriode;
        } elseif ($filterTipe == 'bulan') {
            $tglMulai = $awalBulan;
            $tglSelesai = $akhirBulan;
        }

        $rekap = $this->service->hitungRekapGuru($guru->id, $tglMulai, $tglSelesai, $periodeId, $tahunAjaran, true);
        $riwayat = $this->service->getRiwayatPribadi($guru->id, $tglMulai, $tglSelesai, $periodeId, $tahunAjaran);

        return view('rekap-presensi', compact('guru', 'periodeAktif', 'rekap', 'filterTipe', 'tglMulai', 'tglSelesai', 'riwayat'));
    }

    // ==========================================================
    // KHUSUS GURU: HALAMAN KALDIK (TARGET KURIKULUM & PETA MENGAJAR)
    // ==========================================================
    public function kaldikGuru()
    {
        $user = auth()->user();
        $guru = Guru::where('nama_guru', $user->name)->orWhere('nig', $user->username)->first();

        if (!$guru) {
            return redirect('/dashboard-guru')->with('pesan', 'Akun Anda belum terhubung dengan Data Master Guru.');
        }

        $periodeAktif = get_periode_aktif();
        if (!$periodeAktif || !$periodeAktif->tanggal_mulai || !$periodeAktif->tanggal_selesai) {
            return redirect('/dashboard-guru')->with('pesan', 'Tanggal mulai dan selesai Periode/Semester belum diatur oleh Admin.');
        }

        $tglMulai = $periodeAktif->tanggal_mulai;
        $tglSelesai = $periodeAktif->tanggal_selesai;
        $hariIni = date('Y-m-d');
        $periodeId = $periodeAktif->id;

        $jadwals = JadwalHarian::with(['kelas', 'pelajaran'])
                        ->where('guru_id', $guru->id)
                        ->where('tahun_ajaran', $periodeAktif->tahun_ajaran)
                        ->get();

        $batasPelajaran = \App\Models\BatasPelajaran::where('periode_id', $periodeId)->get();
        $semuaAgenda = $this->service->getSemuaAgenda($periodeId);
        $agendaUts = $semuaAgenda->where('jenis_agenda', 'UTS')->first();
        $agendaPemotongKBM = $semuaAgenda->whereIn('jenis_agenda', ['Libur', 'UTS', 'UAS']);

        $targetMengajar = [];

        foreach ($jadwals as $j) {
            $keyGrup = $j->kelas_id . '_' . $j->pelajaran_id;
            $hIndo = strtolower($j->hari);
            if ($hIndo == 'Ahad') {
                $hIndo = 'ahad';
            }

            if (!isset($targetMengajar[$keyGrup])) {
                $namaKel = $j->kelas->nama_kelas ?? '-';
                $tingkat = preg_replace('/[^0-9]/', '', $namaKel); 

                $targetMengajar[$keyGrup] = (object) [
                    'nama_kelas' => $namaKel,
                    'nama_pelajaran' => $j->pelajaran->nama_pelajaran ?? '?',
                    'kelas_id' => $j->kelas_id,
                    'pelajaran_id' => $j->pelajaran_id,
                    'target_total' => 0,
                    'telah_berlalu' => 0,
                    'sisa_pertemuan_total' => 0,
                    'sisa_pertemuan_pra_uts' => 0,
                    'hari_mengajar' => [],
                    'batas' => $batasPelajaran->where('tingkat', $tingkat)->where('pelajaran_id', $j->pelajaran_id)->first()
                ];
            }
            if (!in_array($hIndo, $targetMengajar[$keyGrup]->hari_mengajar)) {
                $targetMengajar[$keyGrup]->hari_mengajar[] = $hIndo;
            }
        }

        $period = \Carbon\CarbonPeriod::create($tglMulai, $tglSelesai);
        foreach ($period as $date) {
            $tglStr = $date->format('Y-m-d');
            $hariIndo = strtolower(map_hari($date->format('l')));

            foreach ($targetMengajar as $keyGrup => $item) {
                if (in_array($hariIndo, $item->hari_mengajar)) {
                    // Cek libur tanpa parsial (cukup cek hari)
                    $libur = $this->service->isLibur(
                        (object) ['kelas_id' => $item->kelas_id, 'jam_ke' => 0],
                        $agendaPemotongKBM,
                        $tglStr,
                        false
                    );

                    if (!$libur['is_libur']) {
                        $item->target_total++; 
                        
                        if ($tglStr <= $hariIni) {
                            $item->telah_berlalu++;
                        } else {
                            $item->sisa_pertemuan_total++;
                            if ($agendaUts && $tglStr < $agendaUts->tanggal_mulai->format('Y-m-d')) {
                                $item->sisa_pertemuan_pra_uts++;
                            }
                        }
                    }
                }
            }
        }

        foreach ($targetMengajar as $item) {
            $item->persentase_waktu = $item->target_total > 0 ? round(($item->telah_berlalu / $item->target_total) * 100) : 0;
        }

        return view('kaldik', compact('guru', 'periodeAktif', 'targetMengajar', 'agendaUts'));
    }

    // ==========================================================
    // KHUSUS GURU: HALAMAN DASHBOARD MOBILE SPA
    // ==========================================================
    public function dashboardGuru()
    {
        $user = auth()->user();
        
        $guru = Guru::where('nama_guru', $user->name)
                                ->orWhere('nig', $user->username)
                                ->first();

        $periodeAktif = get_periode_aktif();
        $tahunAjaran = $periodeAktif ? $periodeAktif->tahun_ajaran : null;

        $jadwals = [];

        if ($guru) {
            $jadwalMentah = JadwalHarian::with(['kelas', 'pelajaran'])
                        ->where('guru_id', $guru->id)
                        ->where('tahun_ajaran', $tahunAjaran)
                        ->get();

            $hariIniStr = map_hari(Carbon::now()->format('l'));

            $hariSeAhad = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Ahad'];
            
            $indexHariIni = array_search($hariIniStr, $hariSeAhad);
            
            $hariDiurutkan = array_merge(
                array_slice($hariSeAhad, $indexHariIni), 
                array_slice($hariSeAhad, 0, $indexHariIni) 
            );

            $urutanDinamis = [];
            foreach ($hariDiurutkan as $index => $hari) {
                $urutanDinamis[$hari] = $index + 1;
            }

            $jadwalGrouped = $jadwalMentah->groupBy('hari')->sortBy(function ($item, $key) use ($urutanDinamis) {
                return $urutanDinamis[$key] ?? 99;
            });

            foreach ($jadwalGrouped as $hari => $list) {
                $list = $list->sortBy('jam_ke')->values();
                
                $blokJadwal = [];
                $currentBlock = null;

                foreach ($list as $j) {
                    $namaPel = $j->pelajaran->nama_pelajaran ?? 'Pelajaran';
                    $namaKel = $j->kelas->nama_kelas ?? '-';
                    $pelajaranId = $j->pelajaran_id ?? ''; 
                    
                    $tingkatKelas = preg_replace('/[^0-9]/', '', $namaKel); 
                    $petaKitab = $j->pelajaran->kitab_tingkat ?? [];
                    $namaKitab = $petaKitab[$tingkatKelas] ?? ($j->pelajaran->nama_kitab ?? '-');

                    if (!$currentBlock) {
                        $currentBlock = [
                            'jam_mulai' => $j->jam_ke, 
                            'jam_selesai' => $j->jam_ke, 
                            'mata_pelajaran' => $namaPel, 
                            'nama_kitab' => $namaKitab, 
                            'kelas' => $namaKel,
                            'pelajaran_id' => $pelajaranId 
                        ];
                    } else {
                        if ($currentBlock['mata_pelajaran'] == $namaPel && 
                            $currentBlock['kelas'] == $namaKel && 
                            $j->jam_ke == $currentBlock['jam_selesai'] + 1 &&
                            ($currentBlock['jam_selesai'] - $currentBlock['jam_mulai'] < 1)
                        ) {
                            $currentBlock['jam_selesai'] = $j->jam_ke;
                        } else {
                            $currentBlock['jam_tampil'] = ($currentBlock['jam_mulai'] == $currentBlock['jam_selesai']) ? (string) $currentBlock['jam_mulai'] : $currentBlock['jam_mulai'] . '-' . $currentBlock['jam_selesai'];
                            $blokJadwal[] = $currentBlock;
                            
                            $currentBlock = [
                                'jam_mulai' => $j->jam_ke, 
                                'jam_selesai' => $j->jam_ke, 
                                'mata_pelajaran' => $namaPel, 
                                'nama_kitab' => $namaKitab, 
                                'kelas' => $namaKel,
                                'pelajaran_id' => $pelajaranId
                            ];
                        }
                    }
                }
                if ($currentBlock) {
                    $currentBlock['jam_tampil'] = ($currentBlock['jam_mulai'] == $currentBlock['jam_selesai']) ? (string) $currentBlock['jam_mulai'] : $currentBlock['jam_mulai'] . '-' . $currentBlock['jam_selesai'];
                    $blokJadwal[] = $currentBlock;
                }
                if (count($blokJadwal) > 0) {
                    $jadwals[$hari] = $blokJadwal;
                }
            }
        }

        $pengumumans = \App\Models\Pengumuman::where('aktif', true)
                        ->where(function ($q) {
                            $q->whereNull('tanggal_mulai')->orWhere('tanggal_mulai', '<=', date('Y-m-d'));
                        })
                        ->where(function ($q) {
                            $q->whereNull('tanggal_selesai')->orWhere('tanggal_selesai', '>=', date('Y-m-d'));
                        })
                        ->latest()
                        ->get();

        return view('dashboard-guru', compact('guru', 'jadwals', 'periodeAktif', 'pengumumans'));
    }

    // ==========================================================
    // KHUSUS GURU: HALAMAN PROFIL PENGGUNA
    // ==========================================================
    public function menu()
    {
        $user = auth()->user();
        $guru = Guru::where('nama_guru', $user->name)->orWhere('nig', $user->username)->first();

        return view('guru.menu', compact('guru', 'user'));
    }

    // ==========================================================
    // KHUSUS GURU: HALAMAN PROFIL LENGKAP & EDIT BIODATA
    // ==========================================================
    public function profilLengkap()
    {
        $user = auth()->user();
        $guru = Guru::where('nama_guru', $user->name)->orWhere('nig', $user->username)->first();

        if (!$guru) {
            return redirect('/dashboard-guru')->with('pesan', 'Akun Anda belum terhubung dengan Data Master Guru.');
        }

        return view('guru.profil-lengkap', compact('guru'));
    }

    public function updateProfil(Request $request)
    {
        $user = auth()->user();
        $guru = Guru::where('nama_guru', $user->name)->orWhere('nig', $user->username)->first();

        if (!$guru) {
            return back()->with('error', 'Data Guru tidak ditemukan.');
        }

        $data = $request->only([
            'nama_guru', 'no_hp', 'gender', 'alamat', 'status', 'alamat_asal',
            'tempat_lahir', 'tanggal_lahir', 'pendidikan', 'nama_ayah', 'nama_ibu',
            'alamat_ortu', 'foto', 'email_pribadi'
        ]);

        $guru->update($data);

        return back()->with('status', 'Biodata profil berhasil diperbarui!');
    }
}


