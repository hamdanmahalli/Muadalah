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
        
        $hariIni = map_hari($waktuSekarang->format('l'));

        $periodeAktif = get_periode_aktif();
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
        
        $hariIni = map_hari($waktuSekarang->format('l'));

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

        $periodeAktif = get_periode_aktif();
        $tahunAjaran = $periodeAktif ? $periodeAktif->tahun_ajaran : null;
        $periodeId = $periodeAktif ? $periodeAktif->id : null;

        $jadwalsMentah = \App\Models\JadwalHarian::with(['kelas', 'pelajaran', 'guru'])
                         ->where('hari', 'ilike', $hariIni)
                         ->whereIn('jam_ke', $arrayJamPilihan)
                         ->where('tahun_ajaran', $tahunAjaran)
                         ->get();

        // KECERDASAN BARU: Mengambil data dari AgendaKaldik (Bukan HariLibur)
        $daftarLibur = \App\Models\AgendaKaldik::where('periode_id', $periodeId)
                        ->whereIn('jenis_agenda', ['Libur', 'UTS', 'UAS'])
                        ->whereDate('tanggal_mulai', '<=', $tanggalPilihan)
                        ->whereDate('tanggal_selesai', '>=', $tanggalPilihan)
                        ->get();

        $jadwals = [];
        foreach ($jadwalsMentah as $j) {
            $kunci = ($j->kelas_id ?? '0') . '_' . ($j->guru_id ?? '0') . '_' . ($j->pelajaran_id ?? '0'); 
            
            $isLibur = false;
            $namaLibur = '';

            foreach ($daftarLibur as $agenda) {
                $kenaTargetKelas = false;
                $arrKls = is_array($agenda->kelas_ids) ? $agenda->kelas_ids : (is_string($agenda->kelas_ids) ? json_decode($agenda->kelas_ids, true) : []);

                if ($agenda->target_libur == 'semua') {
                    $kenaTargetKelas = true;
                } elseif ($agenda->target_libur == 'kelas_tertentu' && in_array($j->kelas_id, $arrKls)) {
                    $kenaTargetKelas = true;
                }

                if ($kenaTargetKelas) {
                    // KECERDASAN PARSIAL DIKEMBALIKAN
                    if ($agenda->tipe_agenda == 'Penuh') {
                        $isLibur = true;
                        $namaLibur = $agenda->nama_agenda . ' (' . $agenda->jenis_agenda . ' Full)';
                        break; 
                    } else {
                        // Cek apakah jam jadwal ini termasuk jam yang diliburkan
                        $arrJam = is_array($agenda->jam_diliburkan) ? $agenda->jam_diliburkan : (json_decode($agenda->jam_diliburkan, true) ?? []);
                        foreach ($arrJam as $jamLibur) {
                            if ((int)$j->jam_ke == (int)$jamLibur) {
                                $isLibur = true;
                                $namaLibur = $agenda->nama_agenda . " (Parsial)";
                                break 2; // Keluar dari loop jam dan loop agenda
                            }
                        }
                    }
                }
            }

            // --- LOGIKA KECERDASAN TINGKAT KELAS ---
            $namaKelas = $j->kelas->nama_kelas ?? 'Kelas -';
            
            // Ekstrak hanya angkanya saja dari nama kelas (Contoh: "7A" menjadi "7", "9 Putra" menjadi "9")
            $tingkatKelas = preg_replace('/[^0-9]/', '', $namaKelas); 
            
            // Ambil data array JSON kitab_tingkat dari database
            $petaKitab = $j->pelajaran->kitab_tingkat ?? [];
            
            // Tentukan nama kitab: Cek apakah di JSON ada data untuk tingkat kelas tersebut.
            // Jika tidak ada, gunakan fallback (kolom nama_kitab lama atau tanda strip)
            $namaKitab = $petaKitab[$tingkatKelas] ?? ($j->pelajaran->nama_kitab ?? '-');
            // ---------------------------------------

            if (!isset($jadwals[$kunci])) {
                $jadwals[$kunci] = [
                    'id_list' => [], 
                    'kelas' => $namaKelas,
                    'mata_pelajaran' => $j->pelajaran->nama_pelajaran ?? 'Tanpa Pelajaran',
                    'nama_kitab' => $namaKitab, // <--- Variabel cerdas dimasukkan ke sini
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

        // =========================================================================
        // KUNCI STABILITAS URUTAN (Agar tidak melompat-lompat setelah di-update)
        // Mengurutkan berdasarkan Nama Kelas secara alfabetis (A - Z)
        // =========================================================================
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
            'tanggal' => 'nullable|date' // Validasi tambahan untuk tanggal dinamis
        ]);

        // KECERDASAN: Ambil ID Periode yang sedang aktif
        $periodeAktif = get_periode_aktif();
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
    public function laporanKehadiran(Request $request)
    {
        $tglMulai = $request->input('tgl_mulai', date('Y-m-01'));
        $tglSelesai = $request->input('tgl_selesai', date('Y-m-d'));
        $periodeTeks = \Carbon\Carbon::parse($tglMulai)->translatedFormat('d F Y') . " s/d " . \Carbon\Carbon::parse($tglSelesai)->translatedFormat('d F Y');

        $gurus = \App\Models\Guru::orderBy('nama_guru', 'asc')->get();
        $rekapData = [];
        $totalSeluruhWajib = 0; $totalSeluruhRealita = 0; $totalSeluruhKosong = 0;

        $periodeAktif = get_periode_aktif();
        $periodeId = $periodeAktif ? $periodeAktif->id : null;
        $tahunAjaran = $periodeAktif ? $periodeAktif->tahun_ajaran : null;

        // 🛡️ PERBAIKAN: Gunakan withTrashed() agar jadwal guru lama tidak hilang dari sejarah
        $semuaJadwal = \App\Models\JadwalHarian::withTrashed()->where('tahun_ajaran', $tahunAjaran)->get();

        $daftarLibur = \App\Models\AgendaKaldik::where('periode_id', $periodeId)
                        ->whereIn('jenis_agenda', ['Libur', 'UTS', 'UAS'])
                        ->where('tanggal_mulai', '<=', $tglSelesai)
                        ->where('tanggal_selesai', '>=', $tglMulai)
                        ->get();

        foreach ($gurus as $guru) {
            $jamWajib = 0;
            $period = \Carbon\CarbonPeriod::create($tglMulai, $tglSelesai);
            
            foreach ($period as $date) {
                $tglStr = $date->format('Y-m-d');
                $hariIndo = map_hari($date->format('l'));
                
                $jadwalHariIni = $semuaJadwal->where('guru_id', $guru->id)
                                             ->filter(function($j) use ($hariIndo, $tglStr) {
                                                 $isHariSama = strtolower($j->hari) == strtolower($hariIndo) || (strtolower($hariIndo) == 'ahad' && strtolower($j->hari) == 'Ahad');
                                                 
                                                 // 🛡️ KECERDASAN HISTORIS: Baca kapan Soft Delete terjadi
                                                 $mulaiAktif = $j->created_at ? $j->created_at->format('Y-m-d') : '2000-01-01';
                                                 $selesaiAktif = $j->deleted_at ? $j->deleted_at->format('Y-m-d') : '2099-12-31';
                                                 $isDalamRentang = ($tglStr >= $mulaiAktif && $tglStr <= $selesaiAktif);

                                                 return $isHariSama && $isDalamRentang;
                                             });

                foreach ($jadwalHariIni as $j) {
                    $isLibur = false;
                    foreach ($daftarLibur as $agenda) {
                        $mulai = \Carbon\Carbon::parse($agenda->tanggal_mulai)->format('Y-m-d');
                        $selesai = \Carbon\Carbon::parse($agenda->tanggal_selesai)->format('Y-m-d');
                        
                        if ($tglStr >= $mulai && $tglStr <= $selesai) {
                            $kenaTarget = false;
                            $arrKls = is_array($agenda->kelas_ids) ? $agenda->kelas_ids : (is_string($agenda->kelas_ids) ? json_decode($agenda->kelas_ids, true) : []);

                            if ($agenda->target_libur == 'semua') {
                                $kenaTarget = true;
                            } elseif ($agenda->target_libur == 'kelas_tertentu' && in_array($j->kelas_id, $arrKls)) {
                                $kenaTarget = true;
                            }

                            if ($kenaTarget) {
                                if ($agenda->tipe_agenda == 'Penuh') {
                                    $isLibur = true;
                                    break;
                                } else {
                                    $arrJam = is_array($agenda->jam_diliburkan) ? $agenda->jam_diliburkan : (json_decode($agenda->jam_diliburkan, true) ?? []);
                                    if (in_array($j->jam_ke, $arrJam)) {
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

            // Tarik Presensi yang bersangkutan
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

        return view('laporan', compact(
            'rekapData', 'tglMulai', 'tglSelesai', 'periodeTeks',
            'totalSeluruhWajib', 'totalSeluruhRealita', 'totalSeluruhKosong',
            'persenTotalRealita', 'persenTotalKosong', 'daftarLibur'
        ));
    }

    // ========================================================
    // 5. CETAK PDF (STRUKTUR DATA IDENTIK DENGAN WEB)
    // ========================================================
    public function cetakPdf(Request $request)
    {
        $tglMulai = $request->input('tgl_mulai', date('Y-m-01'));
        $tglSelesai = $request->input('tgl_selesai', date('Y-m-d'));
        $periodeTeks = \Carbon\Carbon::parse($tglMulai)->translatedFormat('d F Y') . " s/d " . \Carbon\Carbon::parse($tglSelesai)->translatedFormat('d F Y');

        $gurus = \App\Models\Guru::orderBy('nama_guru', 'asc')->get();
        $rekapData = [];
        $totalSeluruhWajib = 0; $totalSeluruhRealita = 0; $totalSeluruhKosong = 0;

        $periodeAktif = get_periode_aktif();
        $periodeId = $periodeAktif ? $periodeAktif->id : null;
        $tahunAjaran = $periodeAktif ? $periodeAktif->tahun_ajaran : null;

        // 🛡️ PERBAIKAN: Gunakan withTrashed()
        $semuaJadwal = \App\Models\JadwalHarian::withTrashed()->where('tahun_ajaran', $tahunAjaran)->get();

        $daftarLibur = \App\Models\AgendaKaldik::where('periode_id', $periodeId)
                        ->whereIn('jenis_agenda', ['Libur', 'UTS', 'UAS'])
                        ->where('tanggal_mulai', '<=', $tglSelesai)
                        ->where('tanggal_selesai', '>=', $tglMulai)
                        ->get();

        foreach ($gurus as $guru) {
            $jamWajib = 0;
            $period = \Carbon\CarbonPeriod::create($tglMulai, $tglSelesai);
            
            foreach ($period as $date) {
                $tglStr = $date->format('Y-m-d');
                $hariIndo = map_hari($date->format('l'));
                
                $jadwalHariIni = $semuaJadwal->where('guru_id', $guru->id)
                                             ->filter(function($j) use ($hariIndo, $tglStr) {
                                                 $isHariSama = strtolower($j->hari) == strtolower($hariIndo) || (strtolower($hariIndo) == 'ahad' && strtolower($j->hari) == 'Ahad');
                                                 
                                                 $mulaiAktif = $j->created_at ? $j->created_at->format('Y-m-d') : '2000-01-01';
                                                 $selesaiAktif = $j->deleted_at ? $j->deleted_at->format('Y-m-d') : '2099-12-31';
                                                 $isDalamRentang = ($tglStr >= $mulaiAktif && $tglStr <= $selesaiAktif);

                                                 return $isHariSama && $isDalamRentang;
                                             });

                foreach ($jadwalHariIni as $j) {
                    $isLibur = false;
                    foreach ($daftarLibur as $agenda) {
                        $mulai = \Carbon\Carbon::parse($agenda->tanggal_mulai)->format('Y-m-d');
                        $selesai = \Carbon\Carbon::parse($agenda->tanggal_selesai)->format('Y-m-d');
                        if ($tglStr >= $mulai && $tglStr <= $selesai) {
                            $kenaTarget = false;
                            $arrKls = is_array($agenda->kelas_ids) ? $agenda->kelas_ids : (is_string($agenda->kelas_ids) ? json_decode($agenda->kelas_ids, true) : []);
                            if ($agenda->target_libur == 'semua') {
                                $kenaTarget = true;
                            } elseif ($agenda->target_libur == 'kelas_tertentu' && in_array($j->kelas_id, $arrKls)) {
                                $kenaTarget = true;
                            }
                            if ($kenaTarget) {
                                $isLibur = true;
                                break;
                            }
                        }
                    }
                    if (!$isLibur) $jamWajib++;
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

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('laporan-pdf', compact(
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

    // ==========================================================
    // FUNGSI BANTUAN 2: Pop-Up AJAX Riwayat (Layar Admin)
    // ==========================================================
    public function riwayatGuruAjax(Request $request)
    {
        $guruId = $request->guru_id;
        $tglMulai = $request->tgl_mulai;
        $tglSelesai = $request->tgl_selesai;

        $guru = \App\Models\Guru::find($guruId);
        if (!$guru) return response()->json([]);

        $periodeAktif = get_periode_aktif();
        $tahunAjaran = $periodeAktif ? $periodeAktif->tahun_ajaran : null;
        $periodeId = $periodeAktif ? $periodeAktif->id : null;

        $riwayat = $this->getRiwayatPribadi($guruId, $tglMulai, $tglSelesai, $periodeId, $tahunAjaran);

        return response()->json($riwayat);
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

        $periodeAktif = get_periode_aktif();
        $tahunAjaran = $periodeAktif ? $periodeAktif->tahun_ajaran : null;
        $periodeId = $periodeAktif ? $periodeAktif->id : null;

        // 1. Ambil Jadwal dan Kelompokkan 
        // 🛡️ PERBAIKAN: Tidak perlu withTrashed() maupun tgl_efektif, karena di layar jadwal yang tampil hanya jadwal aktif saat ini
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
        $tglMulaiPeriode   = ($periodeAktif && $periodeAktif->tanggal_mulai) ? $periodeAktif->tanggal_mulai : $sekarang->copy()->startOfYear()->format('Y-m-d');
        $tglSelesaiPeriode = ($periodeAktif && $periodeAktif->tanggal_selesai) ? $periodeAktif->tanggal_selesai : $sekarang->copy()->endOfYear()->format('Y-m-d');

        $awalBulan = $sekarang->copy()->startOfMonth()->format('Y-m-d');
        $akhirBulan = $sekarang->copy()->endOfMonth()->format('Y-m-d');

        if ($awalBulan < $tglMulaiPeriode) $awalBulan = $tglMulaiPeriode;
        if ($akhirBulan > $tglSelesaiPeriode) $akhirBulan = $tglSelesaiPeriode;

        $rekapBulan = $this->hitungRekapGuru($guru->id, $awalBulan, $akhirBulan, $periodeId, $tahunAjaran);
        $rekapTahun = $this->hitungRekapGuru($guru->id, $tglMulaiPeriode, $tglSelesaiPeriode, $periodeId, $tahunAjaran);

        return view('jadwal-saya', compact('guru', 'jadwalTerstruktur', 'periodeAktif', 'rekapBulan', 'rekapTahun'));
    }

    
    // ==========================================================
    // KHUSUS GURU: HALAMAN REKAP PRESENSI PRIBADI & RIWAYAT
    // ==========================================================
    public function rekapPresensiPribadi(Request $request)
    {
        $user = auth()->user();
        $guru = \App\Models\Guru::where('nama_guru', $user->name)->orWhere('nig', $user->username)->first();

        if (!$guru) {
            return redirect('/dashboard-guru')->with('pesan', 'Akun Anda belum terhubung dengan Data Master Guru.');
        }

        $periodeAktif = get_periode_aktif();
        $tahunAjaran = $periodeAktif ? $periodeAktif->tahun_ajaran : null;
        $periodeId = $periodeAktif ? $periodeAktif->id : null;

        $sekarang = \Carbon\Carbon::now();
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

        $rekap = $this->hitungRekapGuru($guru->id, $tglMulai, $tglSelesai, $periodeId, $tahunAjaran);
        $riwayat = $this->getRiwayatPribadi($guru->id, $tglMulai, $tglSelesai, $periodeId, $tahunAjaran);

        return view('rekap-presensi', compact('guru', 'periodeAktif', 'rekap', 'filterTipe', 'tglMulai', 'tglSelesai', 'riwayat'));
    }

    // ==========================================================
    // KHUSUS GURU: HALAMAN KALDIK (TARGET KURIKULUM & PETA MENGAJAR)
    // ==========================================================
    public function kaldikGuru()
    {
        $user = auth()->user();
        $guru = \App\Models\Guru::where('nama_guru', $user->name)->orWhere('nig', $user->username)->first();

        if (!$guru) return redirect('/dashboard-guru')->with('pesan', 'Akun Anda belum terhubung dengan Data Master Guru.');

        $periodeAktif = get_periode_aktif();
        if (!$periodeAktif || !$periodeAktif->tanggal_mulai || !$periodeAktif->tanggal_selesai) {
            return redirect('/dashboard-guru')->with('pesan', 'Tanggal mulai dan selesai Periode/Semester belum diatur oleh Admin.');
        }

        $tglMulai = $periodeAktif->tanggal_mulai;
        $tglSelesai = $periodeAktif->tanggal_selesai;
        $hariIni = date('Y-m-d');
        $periodeId = $periodeAktif->id;

        // Kaldik guru fokus pada masa depan / saat ini, tidak perlu withTrashed
        $jadwals = \App\Models\JadwalHarian::with(['kelas', 'pelajaran'])
                        ->where('guru_id', $guru->id)
                        ->where('tahun_ajaran', $periodeAktif->tahun_ajaran)
                        ->get();

        $batasPelajaran = \App\Models\BatasPelajaran::where('periode_id', $periodeId)->get();
        $semuaAgenda = \App\Models\AgendaKaldik::where('periode_id', $periodeId)->get();
        $agendaUts = $semuaAgenda->where('jenis_agenda', 'UTS')->first();
        $agendaPemotongKBM = $semuaAgenda->whereIn('jenis_agenda', ['Libur', 'UTS', 'UAS']);

        $targetMengajar = [];

        foreach ($jadwals as $j) {
            $keyGrup = $j->kelas_id . '_' . $j->pelajaran_id;
            $hIndo = strtolower($j->hari);
            if($hIndo == 'Ahad') $hIndo = 'ahad';

            if (!isset($targetMengajar[$keyGrup])) {
                $namaKel = $j->kelas->nama_kelas ?? '-';
                $tingkat = preg_replace('/[^0-9]/', '', $namaKel); 

                $targetMengajar[$keyGrup] = (object)[
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
                    
                    $isLibur = false;
                    foreach ($agendaPemotongKBM as $libur) {
                        $mulai = $libur->tanggal_mulai->format('Y-m-d');
                        $selesai = $libur->tanggal_selesai->format('Y-m-d');
                        
                        if ($tglStr >= $mulai && $tglStr <= $selesai) {
                            $kenaTarget = ($libur->target_libur == 'semua') || (is_array($libur->kelas_ids) && in_array($item->kelas_id, $libur->kelas_ids));
                            if ($kenaTarget) {
                                $isLibur = true; break; 
                            }
                        }
                    }

                    if (!$isLibur) {
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
    // FUNGSI BANTUAN: Menarik Daftar Riwayat Mengajar Pribadi
    // ==========================================================
    private function getRiwayatPribadi($guruId, $tglMulai, $tglSelesai, $periodeId, $tahunAjaran)
    {
        $guru = \App\Models\Guru::find($guruId);
        
        // 🛡️ PERBAIKAN HISTORIS: Tambahkan withTrashed() agar bisa melacak riwayat dari jadwal yang telah dihapus
        $jadwalAsli = \App\Models\JadwalHarian::withTrashed()->with(['kelas', 'pelajaran'])->where('guru_id', $guruId)->where('tahun_ajaran', $tahunAjaran)->get();
        
        $kehadiranAsli = \Illuminate\Support\Facades\DB::table('kehadiran_gurus')
            ->whereIn('jadwal_id', $jadwalAsli->pluck('id'))
            ->whereDate('tanggal', '>=', $tglMulai)
            ->whereDate('tanggal', '<=', $tglSelesai)
            ->get();

        $daftarLibur = \App\Models\AgendaKaldik::where('periode_id', $periodeId)
                        ->whereIn('jenis_agenda', ['Libur', 'UTS', 'UAS'])
                        ->where('tanggal_mulai', '<=', $tglSelesai)
                        ->where('tanggal_selesai', '>=', $tglMulai)
                        ->get();

        $riwayatMentah = [];
        $batasTglHitung = min($tglSelesai, date('Y-m-d')); 
        
        if ($tglMulai <= $batasTglHitung) {
            $period = \Carbon\CarbonPeriod::create($tglMulai, $batasTglHitung);
            foreach ($period as $date) {
                $tglStr = $date->format('Y-m-d');
                $hariIndo = map_hari($date->format('l'));
                $formatTanggalCantik = $hariIndo . ', ' . $date->translatedFormat('d M Y');

                $jadwalHariIni = $jadwalAsli->filter(function($j) use ($hariIndo, $tglStr) {
                    $isHariSama = (strtolower($j->hari) == strtolower($hariIndo)) || (strtolower($hariIndo) == 'ahad' && strtolower($j->hari) == 'Ahad');
                    
                    // 🛡️ KECERDASAN HISTORIS: Baca kapan Soft Delete terjadi
                    $mulaiAktif = $j->created_at ? $j->created_at->format('Y-m-d') : '2000-01-01';
                    $selesaiAktif = $j->deleted_at ? $j->deleted_at->format('Y-m-d') : '2099-12-31';
                    $isDalamRentang = ($tglStr >= $mulaiAktif && $tglStr <= $selesaiAktif);

                    return $isHariSama && $isDalamRentang;
                });

                foreach ($jadwalHariIni as $j) {
                    $isLibur = false;
                    foreach ($daftarLibur as $agenda) {
                        $mulai = \Carbon\Carbon::parse($agenda->tanggal_mulai)->format('Y-m-d');
                        $selesai = \Carbon\Carbon::parse($agenda->tanggal_selesai)->format('Y-m-d');
                        
                        if ($tglStr >= $mulai && $tglStr <= $selesai) {
                            $kenaTarget = false;
                            $arrKls = is_array($agenda->kelas_ids) ? $agenda->kelas_ids : (is_string($agenda->kelas_ids) ? json_decode($agenda->kelas_ids, true) : []);

                            if ($agenda->target_libur == 'semua') {
                                $kenaTarget = true;
                            } elseif ($agenda->target_libur == 'kelas_tertentu' && in_array($j->kelas_id, $arrKls)) {
                                $kenaTarget = true;
                            }

                            if ($kenaTarget) {
                                if ($agenda->tipe_agenda == 'Penuh') {
                                    $isLibur = true;
                                    break; 
                                } else {
                                    $arrJam = is_array($agenda->jam_diliburkan) ? $agenda->jam_diliburkan : (json_decode($agenda->jam_diliburkan, true) ?? []);
                                    if (in_array($j->jam_ke, $arrJam)) {
                                        $isLibur = true;
                                        break;
                                    }
                                }
                            }
                        }
                    }
                    
                    if (!$isLibur) {
                        $rekam = $kehadiranAsli->where('jadwal_id', $j->id)->where('tanggal', $tglStr)->first();
                        $statusAktual = $rekam ? $rekam->status : 'Alpa';
                        $keteranganAktual = $rekam ? $rekam->keterangan : null;

                        $riwayatMentah[] = (object)[
                            'tanggal' => $tglStr, 'tanggal_indo' => $formatTanggalCantik,
                            'status' => $statusAktual, 'keterangan' => $keteranganAktual,
                            'jam_ke' => (int)$j->jam_ke, 'nama_kelas' => trim($j->kelas->nama_kelas ?? '?'),
                            'nama_pelajaran' => trim($j->pelajaran->nama_pelajaran ?? '?')
                        ];
                    }
                }
             }
        }

        $piketRecords = \Illuminate\Support\Facades\DB::table('kehadiran_gurus')
            ->join('jadwal_harians', 'kehadiran_gurus.jadwal_id', '=', 'jadwal_harians.id')
            ->leftJoin('kelas', 'jadwal_harians.kelas_id', '=', 'kelas.id')
            ->leftJoin('pelajarans', 'jadwal_harians.pelajaran_id', '=', 'pelajarans.id')
            ->where('kehadiran_gurus.nig_pengganti', $guru->nig)
            ->whereDate('kehadiran_gurus.tanggal', '>=', $tglMulai)
            ->whereDate('kehadiran_gurus.tanggal', '<=', $tglSelesai)
            ->select('kehadiran_gurus.tanggal', 'kehadiran_gurus.keterangan', 'jadwal_harians.jam_ke', 'kelas.nama_kelas', 'pelajarans.nama_pelajaran')
            ->get();

        foreach ($piketRecords as $p) {
            $tglPiket = \Carbon\Carbon::parse($p->tanggal);
            $hariIndoPiket = map_hari($tglPiket->format('l'));
            $riwayatMentah[] = (object)[
                'tanggal' => $tglPiket->format('Y-m-d'), 
                'tanggal_indo' => $hariIndoPiket . ', ' . $tglPiket->translatedFormat('d M Y'),
                'status' => 'Piket', 'keterangan' => 'Inval / Mengganti Guru Lain',
                'jam_ke' => (int)$p->jam_ke, 
                'nama_kelas' => trim($p->nama_kelas ?? '?'), 
                'nama_pelajaran' => trim($p->nama_pelajaran ?? '?')
            ];
        }

        usort($riwayatMentah, function($a, $b) {
            $tglCmp = strcmp($b->tanggal, $a->tanggal);
            if ($tglCmp === 0) return $a->jam_ke <=> $b->jam_ke;
            return $tglCmp;
        });

        $groupedRiwayat = [];
        $currentGroup = null;

        foreach ($riwayatMentah as $item) {
            if ($currentGroup === null) {
                $currentGroup = clone $item;
                $currentGroup->jam_list = [$item->jam_ke];
            } else {
                $lastJam = max($currentGroup->jam_list);
                if (
                    $currentGroup->tanggal === $item->tanggal && 
                    strtolower($currentGroup->status) === strtolower($item->status) &&
                    strtolower($currentGroup->nama_kelas) === strtolower($item->nama_kelas) && 
                    strtolower($currentGroup->nama_pelajaran) === strtolower($item->nama_pelajaran) &&
                    ($lastJam + 1 === $item->jam_ke) &&
                    count($currentGroup->jam_list) < 2 // 🛡️ KECERDASAN BARU: Batasi maksimal 2 jam per blok
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

        return $groupedRiwayat;
    }

    
    // ==========================================================
    // FUNGSI BANTUAN: Mesin Penghitung Rekap Guru Pribadi 
    // ==========================================================
    private function hitungRekapGuru($guruId, $tglMulai, $tglSelesai, $periodeId, $tahunAjaran)
    {
        
        // 🛡️ PERBAIKAN HISTORIS: Sertakan jadwal yang di-Soft Delete agar beban wajib mengajar di bulan lalu ikut terhitung
        $jadwalMentah = \App\Models\JadwalHarian::withTrashed()->where('guru_id', $guruId)->where('tahun_ajaran', $tahunAjaran)->get();
        
        $daftarLibur = \App\Models\AgendaKaldik::where('periode_id', $periodeId)
                        ->whereIn('jenis_agenda', ['Libur', 'UTS', 'UAS'])
                        ->where('tanggal_mulai', '<=', $tglSelesai)
                        ->where('tanggal_selesai', '>=', $tglMulai)
                        ->get();
                        
        $jamWajib = 0;
        
        $batasTglHitung = min($tglSelesai, date('Y-m-d'));
        
        if ($tglMulai <= $batasTglHitung) {
            $period = \Carbon\CarbonPeriod::create($tglMulai, $batasTglHitung);
            
            foreach ($period as $date) {
                $tglStr = $date->format('Y-m-d');
                $hariIndo = map_hari($date->format('l'));
                
                $jadwalHariIni = $jadwalMentah->filter(function($j) use ($hariIndo, $tglStr) {
                    $isHariSama = (strtolower($j->hari) == strtolower($hariIndo)) || (strtolower($hariIndo) == 'ahad' && strtolower($j->hari) == 'Ahad');
                    
                    // 🛡️ KECERDASAN HISTORIS: Gunakan created_at & deleted_at
                    $mulaiAktif = $j->created_at ? $j->created_at->format('Y-m-d') : '2000-01-01'; 
                    $selesaiAktif = $j->deleted_at ? $j->deleted_at->format('Y-m-d') : '2099-12-31'; 
                    $isDalamRentang = ($tglStr >= $mulaiAktif && $tglStr <= $selesaiAktif);

                    return $isHariSama && $isDalamRentang;
                });

                foreach ($jadwalHariIni as $j) {
                    $isLibur = false;
                    
                    foreach ($daftarLibur as $agenda) {
                        $mulai = \Carbon\Carbon::parse($agenda->tanggal_mulai)->format('Y-m-d');
                        $selesai = \Carbon\Carbon::parse($agenda->tanggal_selesai)->format('Y-m-d');
                        
                        if ($tglStr >= $mulai && $tglStr <= $selesai) {
                            $kenaTarget = false;
                            $arrKls = is_array($agenda->kelas_ids) ? $agenda->kelas_ids : (is_string($agenda->kelas_ids) ? json_decode($agenda->kelas_ids, true) : []);

                            if ($agenda->target_libur == 'semua') {
                                $kenaTarget = true;
                            } elseif ($agenda->target_libur == 'kelas_tertentu' && in_array($j->kelas_id, $arrKls)) {
                                $kenaTarget = true;
                            }

                            if ($kenaTarget) {
                                if ($agenda->tipe_agenda == 'Penuh') {
                                    $isLibur = true;
                                    break; 
                                } else {
                                    $arrJam = is_array($agenda->jam_diliburkan) ? $agenda->jam_diliburkan : (json_decode($agenda->jam_diliburkan, true) ?? []);
                                    if (in_array($j->jam_ke, $arrJam)) {
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

    // ==========================================================
    // KHUSUS GURU: HALAMAN DASHBOARD MOBILE SPA
    // ==========================================================
    public function dashboardGuru()
    {
        $user = auth()->user();
        
        $guru = \App\Models\Guru::where('nama_guru', $user->name)
                                ->orWhere('nig', $user->username)
                                ->first();

        $periodeAktif = get_periode_aktif();
        $tahunAjaran = $periodeAktif ? $periodeAktif->tahun_ajaran : null;

        $jadwals = [];

        if ($guru) {
            // 🛡️ PERBAIKAN: Bersihkan logika rentang tanggal yang sudah tidak relevan
            // Di Dashboard Guru cukup panggil jadwal yang AKTIF (tidak perlu withTrashed)
            $jadwalMentah = \App\Models\JadwalHarian::with(['kelas', 'pelajaran'])
                        ->where('guru_id', $guru->id)
                        ->where('tahun_ajaran', $tahunAjaran)
                        ->get();

            $hariIniStr = map_hari(\Carbon\Carbon::now()->format('l'));

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
                        // 🛡️ KECERDASAN BARU: Selisih awal dan akhir tidak boleh lebih dari 1
                        if ($currentBlock['mata_pelajaran'] == $namaPel && 
                            $currentBlock['kelas'] == $namaKel && 
                            $j->jam_ke == $currentBlock['jam_selesai'] + 1 &&
                            ($currentBlock['jam_selesai'] - $currentBlock['jam_mulai'] < 1)
                        ) {
                            $currentBlock['jam_selesai'] = $j->jam_ke;
                        } else {
                            $currentBlock['jam_tampil'] = ($currentBlock['jam_mulai'] == $currentBlock['jam_selesai']) ? (string)$currentBlock['jam_mulai'] : $currentBlock['jam_mulai'] . '-' . $currentBlock['jam_selesai'];
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
                    $currentBlock['jam_tampil'] = ($currentBlock['jam_mulai'] == $currentBlock['jam_selesai']) ? (string)$currentBlock['jam_mulai'] : $currentBlock['jam_mulai'] . '-' . $currentBlock['jam_selesai'];
                    $blokJadwal[] = $currentBlock;
                }
                if (count($blokJadwal) > 0) $jadwals[$hari] = $blokJadwal;
            }
        }

        return view('dashboard-guru', compact('guru', 'jadwals', 'periodeAktif'));
    }

    // ==========================================================
    // KHUSUS GURU: HALAMAN PROFIL PENGGUNA
    // ==========================================================
    public function menu()
    {
        $user = auth()->user();
        $guru = \App\Models\Guru::where('nama_guru', $user->name)->orWhere('nig', $user->username)->first();

        if (!$guru) {
            return redirect('/dashboard-guru')->with('pesan', 'Akun Anda belum terhubung dengan Data Master Guru.');
        }

        // Pastikan Anda sudah me-rename (mengubah nama) file profil.blade.php menjadi menu.blade.php
        return view('guru.menu', compact('guru', 'user'));
    }

    // ==========================================================
    // KHUSUS GURU: HALAMAN PROFIL LENGKAP & EDIT BIODATA
    // ==========================================================
    public function profilLengkap()
    {
        $user = auth()->user();
        $guru = \App\Models\Guru::where('nama_guru', $user->name)->orWhere('nig', $user->username)->first();

        if (!$guru) {
            return redirect('/dashboard-guru')->with('pesan', 'Akun Anda belum terhubung dengan Data Master Guru.');
        }

        return view('guru.profil-lengkap', compact('guru'));
    }

    public function updateProfil(Request $request)
    {
        $user = auth()->user();
        $guru = \App\Models\Guru::where('nama_guru', $user->name)->orWhere('nig', $user->username)->first();

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