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
use Carbon\Carbon;

class JadwalController extends Controller
{
    // ========================================================
    // 1. DASHBOARD UTAMA
    // ========================================================
    public function dashboard()
    {
        $waktuSekarang = Carbon::now();
        $tanggalHariIni = $waktuSekarang->format('Y-m-d');
        
        $namaHariInggris = $waktuSekarang->format('l');
        $daftarHari = [
            'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu', 'Sunday' => 'Ahad'
        ];
        $hariIni = $daftarHari[$namaHariInggris];

        $periodeAktif = \App\Models\Periode::where('is_active', true)->first();
        $tahunAjaran = $periodeAktif ? $periodeAktif->tahun_ajaran : null;

        // Menghitung jadwal HANYA untuk Tahun Ajaran aktif
        $totalJadwal = JadwalHarian::where('hari', 'ilike', $hariIni)
                                    ->where('tahun_ajaran', $tahunAjaran)
                                    ->count();

        $kehadiranHariIni = KehadiranGuru::where('tanggal', $tanggalHariIni)->get();
        $guruHadir = $kehadiranHariIni->where('status', 'Hadir')->count();
        $guruIzinKosong = $kehadiranHariIni->whereIn('status', ['Izin', 'Kosong', 'Alpha'])->count();

        return view('dashboard', compact('totalJadwal', 'guruHadir', 'guruIzinKosong', 'waktuSekarang'));
    }

    // ========================================================
    // 2. MEJA KONTROL (SISTEM BLOK JAM OTOMATIS)
    // ========================================================
    public function mejaKontrol(Request $request)
    {
        $tanggalPilihan = $request->input('tanggal', \Carbon\Carbon::now()->format('Y-m-d'));
        $waktuSekarang = \Carbon\Carbon::parse($tanggalPilihan);
        
        $waktuAsliKomputer = \Carbon\Carbon::now();
        $isHariIni = ($tanggalPilihan === $waktuAsliKomputer->format('Y-m-d'));
        $jamSekarang = $waktuAsliKomputer->format('H:i:s');
        
        $namaHariInggris = $waktuSekarang->format('l');
        $daftarHari = [
            'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu', 'Sunday' => 'Ahad' 
        ];
        $hariIni = $daftarHari[$namaHariInggris];

        $semuaJam = \App\Models\MasterJam::orderBy('jam_ke', 'asc')->get();
        
        $opsiBlokJam = [];
        $blokAktifOtomatis = null;

        for ($i = 0; $i < count($semuaJam); $i += 2) {
            $jam1 = $semuaJam[$i];
            $jam2 = $semuaJam[$i + 1] ?? $jam1; 
            
            $keyBlok = $jam1->jam_ke . '-' . $jam2->jam_ke;
            if ($jam1->jam_ke == $jam2->jam_ke) $keyBlok = (string)$jam1->jam_ke;

            $waktu = \Carbon\Carbon::parse($jam1->jam_mulai)->format('H:i') . ' - ' . \Carbon\Carbon::parse($jam2->jam_selesai)->format('H:i');
            $opsiBlokJam[] = ['nilai' => $keyBlok, 'label' => "Jam Ke-$keyBlok ($waktu)"];

            if ($isHariIni && $jamSekarang >= $jam1->jam_mulai && $jamSekarang <= $jam2->jam_selesai) {
                $blokAktifOtomatis = $keyBlok;
            }
        }

        $jamDefault = $blokAktifOtomatis ?? ($opsiBlokJam[0]['nilai'] ?? '1-2');
        $jamPilihan = $request->input('jam', $jamDefault);
        $arrayJamPilihan = explode('-', $jamPilihan); 

        $periodeAktif = \App\Models\Periode::where('is_active', true)->first();
        $tahunAjaran = $periodeAktif ? $periodeAktif->tahun_ajaran : null;

        $jadwalsMentah = \App\Models\JadwalHarian::with(['kelas', 'pelajaran', 'guru'])
                         ->where('hari', 'ilike', $hariIni)
                         ->whereIn('jam_ke', $arrayJamPilihan)
                         ->where('tahun_ajaran', $tahunAjaran)
                         ->get();

        $daftarLibur = \App\Models\HariLibur::whereDate('tanggal_mulai', '<=', $tanggalPilihan)
                        ->whereDate('tanggal_selesai', '>=', $tanggalPilihan)
                        ->get();

        $jadwals = [];
        foreach ($jadwalsMentah as $j) {
            $kunci = ($j->kelas_id ?? '0') . '_' . ($j->guru_id ?? '0') . '_' . ($j->pelajaran_id ?? '0'); 
            
            $isLibur = false;
            $namaLibur = '';

            foreach ($daftarLibur as $libur) {
                $kenaTarget = false;
                
                // KECERDASAN 1: DEKODE AMAN UNTUK TARGET KELAS
                $rawKls = $libur->kelas_ids;
                $arrKls = is_string($rawKls) ? json_decode($rawKls, true) : $rawKls;
                $arrKls = is_array($arrKls) ? $arrKls : [];

                if ($libur->target_libur == 'semua') {
                    $kenaTarget = true;
                } elseif ($libur->target_libur == 'kelas_tertentu') {
                    if (in_array($j->kelas_id, $arrKls)) {
                        $kenaTarget = true;
                    }
                }

                if ($kenaTarget) {
                    if ($libur->tipe_libur == 'Penuh') {
                        $isLibur = true;
                        $namaLibur = $libur->nama_libur;
                        break;
                    } else {
                        // KECERDASAN 2: DEKODE AMAN UNTUK TARGET JAM (PARSIAL)
                        $rawJam = $libur->jam_diliburkan;
                        $arrJam = is_string($rawJam) ? json_decode($rawJam, true) : $rawJam;
                        $arrJam = is_array($arrJam) ? $arrJam : [];
                        
                        // PENCOCOKAN TAHAN BANTING (Konversi Angka Mutlak)
                        foreach ($arrJam as $jamLibur) {
                            if ((int)$j->jam_ke == (int)$jamLibur) {
                                $isLibur = true;
                                $namaLibur = $libur->nama_libur . " (Parsial)";
                                break 2; // Keluar dari loop jam dan loop libur sekaligus
                            }
                        }
                    }
                }
            }

            if (!isset($jadwals[$kunci])) {
                $jadwals[$kunci] = [
                    'id_list' => [], 
                    'kelas' => $j->kelas->nama_kelas ?? 'Kelas -',
                    'mata_pelajaran' => $j->pelajaran->nama_pelajaran ?? 'Tanpa Pelajaran',
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
        $daftarGuru = \App\Models\Guru::all();
        $kehadiranHariIni = \App\Models\KehadiranGuru::whereDate('tanggal', $tanggalPilihan)->get()->keyBy('jadwal_id');

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
            'tanggal' => 'nullable|date' // Validasi tambahan untuk tanggal dinamis
        ]);

        // KECERDASAN: Ambil ID Periode yang sedang aktif
        $periodeAktif = \App\Models\Periode::where('is_active', true)->first();
        $periodeId = $periodeAktif ? $periodeAktif->id : null;

        // Menerima tanggal dari Kalender di layar TU, jika kosong baru pakai Hari Ini
        $tanggalPilihan = $request->tanggal ?? \Carbon\Carbon::now()->format('Y-m-d');

        foreach ($request->jadwal_ids as $id) {
            \App\Models\KehadiranGuru::updateOrCreate(
                [
                    'jadwal_id' => $id,
                    'tanggal' => $tanggalPilihan // <-- SUNTIKAN TANGGAL DINAMIS
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
    // 4. LAPORAN REKAPITULASI (WEB) DENGAN SIMULASI KALENDER
    // ========================================================
    // ========================================================
    // 4. LAPORAN REKAPITULASI (WEB) DENGAN SIMULASI KALENDER
    // ========================================================
    public function laporanKehadiran(Request $request)
    {
        $tglMulai = $request->input('tgl_mulai', date('Y-m-01'));
        $tglSelesai = $request->input('tgl_selesai', date('Y-m-d'));
        $periodeTeks = \Carbon\Carbon::parse($tglMulai)->translatedFormat('d F Y') . " s/d " . \Carbon\Carbon::parse($tglSelesai)->translatedFormat('d F Y');

        $gurus = \App\Models\Guru::orderBy('nama_guru', 'asc')->get();
        
        $rekapData = [];
        $totalSeluruhWajib = 0; $totalSeluruhRealita = 0; $totalSeluruhKosong = 0;

        $mapHari = [
            'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu', 'Sunday' => 'Minggu'
        ];

        $periodeAktif = \App\Models\Periode::where('is_active', true)->first();
        $periodeId = $periodeAktif ? $periodeAktif->id : null;
        $tahunAjaran = $periodeAktif ? $periodeAktif->tahun_ajaran : null;

        $semuaJadwal = \App\Models\JadwalHarian::where('tahun_ajaran', $tahunAjaran)->get();

        // KECERDASAN BARU: Tarik data Hari Libur di rentang tanggal laporan
        $daftarLibur = \App\Models\HariLibur::where('tanggal_mulai', '<=', $tglSelesai)
                        ->where('tanggal_selesai', '>=', $tglMulai)
                        ->get();

        foreach ($gurus as $guru) {
            // 1. HITUNG JAM WAJIB BERDASARKAN KALENDER & HARI LIBUR
            $jamWajib = 0;
            $period = \Carbon\CarbonPeriod::create($tglMulai, $tglSelesai);
            
            foreach ($period as $date) {
                $tglStr = $date->format('Y-m-d');
                $namaHari = $date->format('l');
                $hariIndo = $mapHari[$namaHari];
                
                $jadwalHariIni = $semuaJadwal->where('guru_id', $guru->id)
                                         ->filter(function($j) use ($hariIndo) {
                                             return strtolower($j->hari) == strtolower($hariIndo) || 
                                                    (strtolower($hariIndo) == 'ahad' && strtolower($j->hari) == 'minggu');
                                         });

                foreach ($jadwalHariIni as $j) {
                    $isLibur = false;

                    foreach ($daftarLibur as $libur) {
                        // Apakah tanggal ini masuk rentang libur?
                        if ($tglStr >= $libur->tanggal_mulai && $tglStr <= $libur->tanggal_selesai) {
                            $kenaTarget = false;
                            
                            // Apakah untuk semua kelas atau kelas tertentu?
                            if ($libur->target_libur == 'semua') {
                                $kenaTarget = true;
                            } elseif ($libur->target_libur == 'kelas_tertentu') {
                                $kelasDiliburkan = is_string($libur->kelas_ids) ? json_decode($libur->kelas_ids, true) : (is_array($libur->kelas_ids) ? $libur->kelas_ids : []);
                                if (is_array($kelasDiliburkan) && in_array($j->kelas_id, $kelasDiliburkan)) {
                                    $kenaTarget = true;
                                }
                            }

                            // Jika kelasnya libur, cek apakah full atau parsial jamnya
                            if ($kenaTarget) {
                                if ($libur->tipe_libur == 'Penuh') {
                                    $isLibur = true;
                                    break;
                                } else {
                                    $jamDiliburkan = is_string($libur->jam_diliburkan) ? json_decode($libur->jam_diliburkan, true) : (is_array($libur->jam_diliburkan) ? $libur->jam_diliburkan : []);
                                    if (is_array($jamDiliburkan) && in_array($j->jam_ke, $jamDiliburkan)) {
                                        $isLibur = true;
                                        break;
                                    }
                                }
                            }
                        }
                    }

                    // Jika tidak terkena libur apa pun, baru hitung sebagai Jam Wajib
                    if (!$isLibur) {
                        $jamWajib++;
                    }
                }
            }

            if ($jamWajib == 0) continue; // Sembunyikan guru jika tidak ada jadwal wajib di rentang ini

            // 2. AMBIL REALITA KEHADIRAN (JADWAL SENDIRI)
            $kehadiran = \Illuminate\Support\Facades\DB::table('kehadiran_gurus')
                ->join('jadwal_harians', 'kehadiran_gurus.jadwal_id', '=', 'jadwal_harians.id')
                ->where('jadwal_harians.guru_id', $guru->id)
                ->whereBetween('kehadiran_gurus.tanggal', [$tglMulai, $tglSelesai])
                ->where('kehadiran_gurus.periode_id', $periodeId)
                ->select('kehadiran_gurus.status')
                ->get();

            $hadir = $kehadiran->where('status', 'Hadir')->count();
            $izin  = $kehadiran->where('status', 'Izin')->count();
            $sakit = $kehadiran->where('status', 'Sakit')->count();
            
            // 3. SAPU BERSIH: UBAH "MENUNGGU" MENJADI "ALPA"
            $alpha = $jamWajib - ($hadir + $izin + $sakit);
            if($alpha < 0) $alpha = 0; 

            // 4. HITUNG PIKET / INVAL (TIDAK MEMPENGARUHI PERSENTASE JAM WAJIB)
            $piket = \Illuminate\Support\Facades\DB::table('kehadiran_gurus')
                ->whereBetween('tanggal', [$tglMulai, $tglSelesai])
                ->where('periode_id', $periodeId)
                ->where('nig_pengganti', $guru->nig)
                ->count();

            // RUMUS REGULASI: Persentase kehadiran HANYA dari jam wajib sendiri
            $persentase = round(($hadir / $jamWajib) * 100, 1);

            if ($persentase >= 85) {
                $ket = 'Sangat Baik'; $badgeBg = 'bg-emerald-50 text-emerald-700 border-emerald-200';
            } elseif ($persentase >= 70) {
                $ket = 'Baik'; $badgeBg = 'bg-blue-50 text-blue-700 border-blue-200';
            } elseif ($persentase >= 50) {
                $ket = 'Cukup'; $badgeBg = 'bg-amber-50 text-amber-700 border-amber-200';
            } else {
                $ket = 'Kurang'; $badgeBg = 'bg-rose-50 text-rose-700 border-rose-200';
            }

            $rekapData[] = (object)[
                'guru_id' => $guru->id,
                'nama_guru' => $guru->nama_guru,
                'jam_wajib' => $jamWajib,
                'a' => $alpha,
                'i' => $izin,
                's' => $sakit,
                'piket' => $piket, // Muncul di tabel web/PDF
                'realita' => $hadir,
                'persen' => $persentase, // Aman, tidak terpengaruh piket
                'ket' => $ket,
                'badge_bg' => $badgeBg
            ];

            $totalSeluruhWajib += $jamWajib;
            $totalSeluruhRealita += $hadir;
            $totalSeluruhKosong += $alpha;
        }

        $persenTotalRealita = $totalSeluruhWajib > 0 ? round(($totalSeluruhRealita / $totalSeluruhWajib) * 100, 1) : 0;
        $persenTotalKosong = $totalSeluruhWajib > 0 ? round(($totalSeluruhKosong / $totalSeluruhWajib) * 100, 1) : 0;

        return view('laporan', compact(
            'rekapData', 'tglMulai', 'tglSelesai', 'periodeTeks',
            'totalSeluruhWajib', 'totalSeluruhRealita', 'totalSeluruhKosong',
            'persenTotalRealita', 'persenTotalKosong', 'daftarLibur'
        ));
    }

    // ========================================================
    // 5. CETAK PDF (DISINKRONKAN 100% DENGAN LOGIKA WEB)
    // ========================================================
    public function cetakPdf(Request $request)
    {
        $tglMulai = $request->input('tgl_mulai', date('Y-m-01'));
        $tglSelesai = $request->input('tgl_selesai', date('Y-m-d'));
        $periodeTeks = \Carbon\Carbon::parse($tglMulai)->translatedFormat('d F Y') . " s/d " . \Carbon\Carbon::parse($tglSelesai)->translatedFormat('d F Y');

        $gurus = \App\Models\Guru::orderBy('nama_guru', 'asc')->get();
        $rekapData = [];
        $totalSeluruhWajib = 0; $totalSeluruhRealita = 0; $totalSeluruhKosong = 0;

        $mapHari = [
            'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu', 'Sunday' => 'Minggu'
        ];

        $periodeAktif = \App\Models\Periode::where('is_active', true)->first();
        $periodeId = $periodeAktif ? $periodeAktif->id : null;
        $tahunAjaran = $periodeAktif ? $periodeAktif->tahun_ajaran : null;

        $semuaJadwal = \App\Models\JadwalHarian::where('tahun_ajaran', $tahunAjaran)->get();

        // KECERDASAN BARU: Tarik data Hari Libur di rentang tanggal laporan
        $daftarLibur = \App\Models\HariLibur::where('tanggal_mulai', '<=', $tglSelesai)
                        ->where('tanggal_selesai', '>=', $tglMulai)
                        ->get();

        foreach ($gurus as $guru) {
            $jamWajib = 0;
            $period = \Carbon\CarbonPeriod::create($tglMulai, $tglSelesai);
            
            foreach ($period as $date) {
                $tglStr = $date->format('Y-m-d');
                $namaHari = $date->format('l');
                $hariIndo = $mapHari[$namaHari];
                
                $jadwalHariIni = $semuaJadwal->where('guru_id', $guru->id)
                                         ->filter(function($j) use ($hariIndo) {
                                             return strtolower($j->hari) == strtolower($hariIndo) || 
                                                    (strtolower($hariIndo) == 'ahad' && strtolower($j->hari) == 'minggu');
                                         });

                foreach ($jadwalHariIni as $j) {
                    $isLibur = false;

                    foreach ($daftarLibur as $libur) {
                        if ($tglStr >= $libur->tanggal_mulai && $tglStr <= $libur->tanggal_selesai) {
                            $kenaTarget = false;
                            
                            if ($libur->target_libur == 'semua') {
                                $kenaTarget = true;
                            } elseif ($libur->target_libur == 'kelas_tertentu') {
                                $kelasDiliburkan = is_string($libur->kelas_ids) ? json_decode($libur->kelas_ids, true) : (is_array($libur->kelas_ids) ? $libur->kelas_ids : []);
                                if (is_array($kelasDiliburkan) && in_array($j->kelas_id, $kelasDiliburkan)) {
                                    $kenaTarget = true;
                                }
                            }

                            if ($kenaTarget) {
                                if ($libur->tipe_libur == 'Penuh') {
                                    $isLibur = true;
                                    break;
                                } else {
                                    $jamDiliburkan = is_string($libur->jam_diliburkan) ? json_decode($libur->jam_diliburkan, true) : (is_array($libur->jam_diliburkan) ? $libur->jam_diliburkan : []);
                                    if (is_array($jamDiliburkan) && in_array($j->jam_ke, $jamDiliburkan)) {
                                        $isLibur = true;
                                        break;
                                    }
                                }
                            }
                        }
                    }

                    if (!$isLibur) {
                        $jamWajib++;
                    }
                }
            }

            if ($jamWajib == 0) continue; 

            $kehadiran = \Illuminate\Support\Facades\DB::table('kehadiran_gurus')
                ->join('jadwal_harians', 'kehadiran_gurus.jadwal_id', '=', 'jadwal_harians.id')
                ->where('jadwal_harians.guru_id', $guru->id)
                ->whereBetween('kehadiran_gurus.tanggal', [$tglMulai, $tglSelesai])
                ->where('kehadiran_gurus.periode_id', $periodeId)
                ->select('kehadiran_gurus.status')
                ->get();

            $hadir = $kehadiran->where('status', 'Hadir')->count();
            $izin  = $kehadiran->where('status', 'Izin')->count();
            $sakit = $kehadiran->where('status', 'Sakit')->count();
            
            $alpha = $jamWajib - ($hadir + $izin + $sakit);
            if($alpha < 0) $alpha = 0; 

            $piket = \Illuminate\Support\Facades\DB::table('kehadiran_gurus')
                ->whereBetween('tanggal', [$tglMulai, $tglSelesai])
                ->where('periode_id', $periodeId)
                ->where('nig_pengganti', $guru->nig)
                ->count();

            $persentase = round(($hadir / $jamWajib) * 100, 1);

            if ($persentase >= 85) {
                $ket = 'Sangat Baik'; $badgeBg = 'bg-emerald-50 text-emerald-700 border-emerald-200';
            } elseif ($persentase >= 70) {
                $ket = 'Baik'; $badgeBg = 'bg-blue-50 text-blue-700 border-blue-200';
            } elseif ($persentase >= 50) {
                $ket = 'Cukup'; $badgeBg = 'bg-amber-50 text-amber-700 border-amber-200';
            } else {
                $ket = 'Kurang'; $badgeBg = 'bg-rose-50 text-rose-700 border-rose-200';
            }

            $rekapData[] = (object)[
                'guru_id' => $guru->id,
                'nama_guru' => $guru->nama_guru,
                'jam_wajib' => $jamWajib,
                'a' => $alpha,
                'i' => $izin,
                's' => $sakit,
                'piket' => $piket,
                'realita' => $hadir,
                'persen' => $persentase,
                'ket' => $ket,
                'badge_bg' => $badgeBg
            ];

            $totalSeluruhWajib += $jamWajib;
            $totalSeluruhRealita += $hadir;
            $totalSeluruhKosong += $alpha;
        }

        $persenTotalRealita = $totalSeluruhWajib > 0 ? round(($totalSeluruhRealita / $totalSeluruhWajib) * 100, 1) : 0;
        $persenTotalKosong = $totalSeluruhWajib > 0 ? round(($totalSeluruhKosong / $totalSeluruhWajib) * 100, 1) : 0;

        $pdf = Pdf::loadView('laporan-pdf', compact(
            'rekapData', 'tglMulai', 'tglSelesai', 'periodeTeks',
            'totalSeluruhWajib', 'totalSeluruhRealita', 'totalSeluruhKosong',
            'persenTotalRealita', 'persenTotalKosong', 'daftarLibur'
        ));

        return $pdf->download('Rekap_Kehadiran_'.$tglMulai.'_hingga_'.$tglSelesai.'.pdf');
    }

    // FITUR BARU: Menyuplai data terbaru untuk Radar Layar TU
    public function cekKehadiranTerbaru()
    {
        $tanggalHariIni = \Carbon\Carbon::now()->format('Y-m-d');
        $kehadiran = \App\Models\KehadiranGuru::where('tanggal', $tanggalHariIni)
                        ->get(['jadwal_id', 'status', 'keterangan', 'nig_pengganti']);
                        
        return response()->json($kehadiran);
    }

    // FITUR BARU: Menyuplai Riwayat Kelas via AJAX (TERMASUK ALPA OTOMATIS & PIKET)
    // FITUR BARU: Menyuplai Riwayat Kelas via AJAX (TERMASUK ALPA OTOMATIS, PIKET, & FORMAT HARI LENGKAP)
    public function riwayatGuruAjax(Request $request)
    {
        $guruId = $request->guru_id;
        $tglMulai = $request->tgl_mulai;
        $tglSelesai = $request->tgl_selesai;

        $guru = \App\Models\Guru::find($guruId);
        if (!$guru) return response()->json([]);

        $periodeAktif = \App\Models\Periode::where('is_active', true)->first();
        $tahunAjaran = $periodeAktif ? $periodeAktif->tahun_ajaran : null;
        $periodeId = $periodeAktif ? $periodeAktif->id : null;

        // Peta terjemahan hari ke Bahasa Indonesia
        $mapHari = [
            'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu', 'Sunday' => 'Ahad'
        ];

        // 1. Ambil Jadwal Asli (Wajib) Guru Tersebut
        $jadwalAsli = \App\Models\JadwalHarian::with(['kelas', 'pelajaran'])
                        ->where('guru_id', $guruId)
                        ->where('tahun_ajaran', $tahunAjaran)
                        ->get();

        // 2. Ambil Rekaman Kehadiran untuk jadwal asli
        $kehadiranAsli = \Illuminate\Support\Facades\DB::table('kehadiran_gurus')
            ->whereIn('jadwal_id', $jadwalAsli->pluck('id'))
            ->whereBetween('tanggal', [$tglMulai, $tglSelesai])
            ->where('periode_id', $periodeId)
            ->get()->keyBy(function($item) {
                return $item->jadwal_id . '_' . $item->tanggal; // ID Jadwal + Tanggal
            });

        $riwayat = [];
        $period = \Carbon\CarbonPeriod::create($tglMulai, $tglSelesai);
        
        // 3. SIMULASIKAN KALENDER UNTUK MENGELUARKAN ALPA OTOMATIS
        foreach ($period as $date) {
            $tglStr = $date->format('Y-m-d');
            $namaHari = $date->format('l');
            $hariIndo = $mapHari[$namaHari] ?? $namaHari;

            // KECERDASAN BARU: Meracik format "Hari, Tanggal Bulan Tahun" (Contoh: Rabu, 4 Juli 2026)
            $formatTanggalCantik = $hariIndo . ', ' . $date->translatedFormat('j F Y');

            $jadwalHariIni = $jadwalAsli->filter(function($j) use ($hariIndo) {
                return strtolower($j->hari) == strtolower($hariIndo) || 
                       (strtolower($hariIndo) == 'ahad' && strtolower($j->hari) == 'minggu');
            });

            foreach ($jadwalHariIni as $j) {
                $key = $j->id . '_' . $tglStr;
                $rekam = $kehadiranAsli->get($key);

                if ($rekam) {
                    $riwayat[] = (object)[
                        'tanggal' => $tglStr,
                        'tanggal_indo' => $formatTanggalCantik, // <-- Format baru disuntikkan
                        'status' => $rekam->status,
                        'keterangan' => $rekam->keterangan,
                        'jam_ke' => $j->jam_ke,
                        'nama_kelas' => $j->kelas->nama_kelas ?? '?',
                        'nama_pelajaran' => $j->pelajaran->nama_pelajaran ?? '?'
                    ];
                } else {
                    // JIKA TIDAK ADA RECORD, JADI ALPA (Tersapu Sistem)
                    $riwayat[] = (object)[
                        'tanggal' => $tglStr,
                        'tanggal_indo' => $formatTanggalCantik, // <-- Format baru disuntikkan
                        'status' => 'Alpa',
                        'keterangan' => 'Belum dikonfirmasi (Tersapu Sistem)',
                        'jam_ke' => $j->jam_ke,
                        'nama_kelas' => $j->kelas->nama_kelas ?? '?',
                        'nama_pelajaran' => $j->pelajaran->nama_pelajaran ?? '?'
                    ];
                }
            }
        }

        // 4. TAMBAHKAN DATA PIKET (INVAL) DI KELAS GURU LAIN
        $piketRecords = \Illuminate\Support\Facades\DB::table('kehadiran_gurus')
            ->join('jadwal_harians', 'kehadiran_gurus.jadwal_id', '=', 'jadwal_harians.id')
            ->leftJoin('kelas', 'jadwal_harians.kelas_id', '=', 'kelas.id')
            ->leftJoin('pelajarans', 'jadwal_harians.pelajaran_id', '=', 'pelajarans.id')
            ->where('kehadiran_gurus.nig_pengganti', $guru->nig) // Karena Inval disimpan lewat NIG
            ->whereBetween('kehadiran_gurus.tanggal', [$tglMulai, $tglSelesai])
            ->where('kehadiran_gurus.periode_id', $periodeId)
            ->select('kehadiran_gurus.tanggal', 'kehadiran_gurus.keterangan', 'jadwal_harians.jam_ke', 'kelas.nama_kelas', 'pelajarans.nama_pelajaran')
            ->get();

        foreach ($piketRecords as $p) {
            $tglPiket = \Carbon\Carbon::parse($p->tanggal);
            $namaHariPiket = $tglPiket->format('l');
            $hariIndoPiket = $mapHari[$namaHariPiket] ?? $namaHariPiket;
            
            // Format cantik untuk Piket
            $formatTanggalCantikPiket = $hariIndoPiket . ', ' . $tglPiket->translatedFormat('j F Y');

            $riwayat[] = (object)[
                'tanggal' => $p->tanggal,
                'tanggal_indo' => $formatTanggalCantikPiket, // <-- Format baru disuntikkan
                'status' => 'Piket',
                'keterangan' => 'Inval / Mengganti Guru Lain',
                'jam_ke' => $p->jam_ke,
                'nama_kelas' => $p->nama_kelas ?? '?',
                'nama_pelajaran' => $p->nama_pelajaran ?? '?'
            ];
        }

        // 5. URUTKAN DARI TANGGAL TERBARU LALU JAM AWAL
        usort($riwayat, function($a, $b) {
            $tglCmp = strcmp($b->tanggal, $a->tanggal);
            if ($tglCmp === 0) {
                return $a->jam_ke <=> $b->jam_ke;
            }
            return $tglCmp;
        });

        // 6. GABUNGKAN BLOK JAM & PASTIKAN TIDAK PECAH
        $groupedRiwayat = [];
        $currentGroup = null;

        foreach ($riwayat as $item) {
            $item->jam_ke = (int)$item->jam_ke;
            
            if ($currentGroup === null) {
                $currentGroup = clone $item;
                $currentGroup->jam_list = [$item->jam_ke];
            } else {
                // Solusi Mutlak: Gunakan max() agar urutan terpantau
                $lastJam = max($currentGroup->jam_list);
                
                if (
                    $currentGroup->tanggal === $item->tanggal &&
                    $currentGroup->status === $item->status &&
                    $currentGroup->nama_kelas === $item->nama_kelas &&
                    $currentGroup->nama_pelajaran === $item->nama_pelajaran &&
                    ($lastJam + 1 === $item->jam_ke)
                ) {
                    $currentGroup->jam_list[] = $item->jam_ke;
                } else {
                    $jamMulai = min($currentGroup->jam_list);
                    $jamSelesai = max($currentGroup->jam_list);
                    $currentGroup->jam_tampil = ($jamMulai == $jamSelesai) ? (string)$jamMulai : $jamMulai . '-' . $jamSelesai;
                    
                    $groupedRiwayat[] = $currentGroup;
                    
                    $currentGroup = clone $item;
                    $currentGroup->jam_list = [$item->jam_ke];
                }
            }
        }
        
        if ($currentGroup !== null) {
            $jamMulai = min($currentGroup->jam_list);
            $jamSelesai = max($currentGroup->jam_list);
            $currentGroup->jam_tampil = ($jamMulai == $jamSelesai) ? (string)$jamMulai : $jamMulai . '-' . $jamSelesai;
            $groupedRiwayat[] = $currentGroup;
        }

        return response()->json($groupedRiwayat);
    }

    // ==========================================================
    // KHUSUS GURU: HALAMAN JADWAL & RIWAYAT SAYA (PERIODE DINAMIS)
    // ==========================================================
    public function jadwalSaya()
    {
        $user = auth()->user();
        $guru = \App\Models\Guru::where('nama_guru', $user->name)->first();

        if (!$guru) {
            return view('jadwal-saya', [
                'guru' => null, 
                'jadwalTerstruktur' => [], 
                'pesan' => 'Akun Anda belum terhubung dengan Data Guru. Silakan hubungi Administrator/TU.'
            ]);
        }

        $periodeAktif = \App\Models\Periode::where('is_active', true)->first();
        $tahunAjaran = $periodeAktif ? $periodeAktif->tahun_ajaran : null;
        $periodeId = $periodeAktif ? $periodeAktif->id : null;

        // 1. Ambil Jadwal dan Kelompokkan (Sistem Blok Jam)
        $jadwalMentah = \App\Models\JadwalHarian::with(['kelas', 'pelajaran'])
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
            if ($currentBlock) $blokJadwal[] = $currentBlock;
            if (count($blokJadwal) > 0) $jadwalTerstruktur[$hari] = $blokJadwal;
        }

        // 2. TANGGAL PERIODE DINAMIS
        $sekarang = \Carbon\Carbon::now();
        
        // Batas Tanggal 1 Periode/Semester dari Master Periode
        $tglMulaiPeriode   = ($periodeAktif && $periodeAktif->tanggal_mulai) ? $periodeAktif->tanggal_mulai : $sekarang->copy()->startOfYear()->format('Y-m-d');
        $tglSelesaiPeriode = ($periodeAktif && $periodeAktif->tanggal_selesai) ? $periodeAktif->tanggal_selesai : $sekarang->copy()->endOfYear()->format('Y-m-d');

        // Batas Bulan Ini (Tetap Di dalam Rentang Periode)
        $awalBulan = $sekarang->copy()->startOfMonth()->format('Y-m-d');
        $akhirBulan = $sekarang->copy()->endOfMonth()->format('Y-m-d');

        if ($awalBulan < $tglMulaiPeriode) $awalBulan = $tglMulaiPeriode;
        if ($akhirBulan > $tglSelesaiPeriode) $akhirBulan = $tglSelesaiPeriode;

        // Hitung Rekap
        $rekapBulan = $this->hitungRekapGuru($guru->id, $awalBulan, $akhirBulan, $periodeId, $tahunAjaran);
        $rekapTahun = $this->hitungRekapGuru($guru->id, $tglMulaiPeriode, $tglSelesaiPeriode, $periodeId, $tahunAjaran);

        return view('jadwal-saya', compact('guru', 'jadwalTerstruktur', 'periodeAktif', 'rekapBulan', 'rekapTahun'));
    }

    // FUNGSI BANTUAN: Mesin Penghitung Rekap Guru Pribadi Berbasis Rentang Tanggal
    private function hitungRekapGuru($guruId, $tglMulai, $tglSelesai, $periodeId, $tahunAjaran)
    {
        $mapHari = ['Monday'=>'Senin','Tuesday'=>'Selasa','Wednesday'=>'Rabu','Thursday'=>'Kamis','Friday'=>'Jumat','Saturday'=>'Sabtu','Sunday'=>'Minggu'];
        $jadwalMentah = \App\Models\JadwalHarian::where('guru_id', $guruId)->where('tahun_ajaran', $tahunAjaran)->get();
        $daftarLibur = \App\Models\HariLibur::where('tanggal_mulai', '<=', $tglSelesai)->where('tanggal_selesai', '>=', $tglMulai)->get();

        $jamWajib = 0;
        
        // Hitung jam wajib s/d hari ini (agar hari esok yang belum dilewati tidak terhitung Alpa)
        $batasTglHitung = min($tglSelesai, date('Y-m-d'));
        
        if ($tglMulai <= $batasTglHitung) {
            $period = \Carbon\CarbonPeriod::create($tglMulai, $batasTglHitung);
            
            foreach ($period as $date) {
                $tglStr = $date->format('Y-m-d');
                $hariIndo = $mapHari[$date->format('l')];
                
                $jadwalHariIni = $jadwalMentah->filter(function($j) use ($hariIndo) {
                    return strtolower($j->hari) == strtolower($hariIndo) || (strtolower($hariIndo) == 'ahad' && strtolower($j->hari) == 'minggu');
                });

                foreach ($jadwalHariIni as $j) {
                    $isLibur = false;
                    foreach ($daftarLibur as $libur) {
                        if ($tglStr >= $libur->tanggal_mulai && $tglStr <= $libur->tanggal_selesai) {
                            $kenaTarget = ($libur->target_libur == 'semua') || 
                                          (in_array($j->kelas_id, is_string($libur->kelas_ids) ? json_decode($libur->kelas_ids, true) : (is_array($libur->kelas_ids) ? $libur->kelas_ids : [])));
                            
                            if ($kenaTarget) {
                                if ($libur->tipe_libur == 'Penuh') { $isLibur = true; break; } 
                                else {
                                    $jamLibur = is_string($libur->jam_diliburkan) ? json_decode($libur->jam_diliburkan, true) : (is_array($libur->jam_diliburkan) ? $libur->jam_diliburkan : []);
                                    if (in_array($j->jam_ke, $jamLibur)) { $isLibur = true; break; }
                                }
                            }
                        }
                    }
                    if (!$isLibur) $jamWajib++;
                }
            }
        }

        $kehadiran = \Illuminate\Support\Facades\DB::table('kehadiran_gurus')
            ->join('jadwal_harians', 'kehadiran_gurus.jadwal_id', '=', 'jadwal_harians.id')
            ->where('jadwal_harians.guru_id', $guruId)
            ->whereBetween('kehadiran_gurus.tanggal', [$tglMulai, $tglSelesai])
            ->where('kehadiran_gurus.periode_id', $periodeId)
            ->select('kehadiran_gurus.status')->get();

        $hadir = $kehadiran->where('status', 'Hadir')->count();
        $izin  = $kehadiran->where('status', 'Izin')->count();
        $sakit = $kehadiran->where('status', 'Sakit')->count();
        $alpha = max(0, $jamWajib - ($hadir + $izin + $sakit));
        $persentase = $jamWajib > 0 ? round(($hadir / $jamWajib) * 100, 1) : 0;

        return (object)[
            'wajib' => $jamWajib, 
            'hadir' => $hadir, 
            'izin' => $izin, 
            'sakit' => $sakit, 
            'alpha' => $alpha, 
            'persen' => $persentase
        ];
    }
}