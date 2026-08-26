<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\JadwalHarian;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ScanController extends Controller
{
    // Membuka halaman kamera di HP Guru
    public function index()
    {
        return view('scan-kelas');
    }

    // Mesin pemroses saat kamera berhasil membaca QR Code
    public function proses(Request $request)
    {
        try {
            $qr_data = $request->qr_data;
            
            // ========================================================
            // 💡 KECERDASAN BUATAN (SMART HUB ROUTER)
            // ========================================================
            
            // 1. Identifikasi Guru Pen-scan (Ditaruh di atas karena kedua jalur butuh info ini)
            $user = auth()->user();
            $guruSAYA = Guru::where('nama_guru', $user->name)->orWhere('nig', $user->username)->first();
            
            if (!$guruSAYA) {
                return response()->json(['status' => 'error', 'pesan' => 'Akun Anda belum terhubung dengan data Master Guru.']);
            }

            // ========================================================
            // JALUR A: SCAN QR AGENDA / KEGIATAN HRIS
            // ========================================================
            if (strpos($qr_data, 'AGENDA-') === 0) {
                
                // Cari data acara berdasarkan kode unik
                $agenda = \App\Models\AgendaKegiatan::where('qr_token', $qr_data)->first();
                
                if (!$agenda) {
                    return response()->json(['status' => 'error', 'pesan' => 'QR Code Agenda tidak valid atau acara tidak ditemukan!']);
                }

                // Cek apakah admin sudah menutup absen untuk acara ini
                if (!$agenda->is_open) {
                    return response()->json(['status' => 'error', 'pesan' => 'Absensi untuk kegiatan ini sudah ditutup oleh TU.']);
                }

                // Proteksi Anti-Double Scan (Mencegah absen ganda)
                $sudahHadir = \App\Models\KehadiranKegiatan::where('agenda_kegiatan_id', $agenda->id)
                                ->where('guru_id', $guruSAYA->id)
                                ->exists();

                if ($sudahHadir) {
                    return response()->json(['status' => 'success', 'pesan' => 'Anda sudah tercatat hadir pada kegiatan ini sebelumnya.']);
                }

                // Rekam Kehadiran Kegiatan ke Database
                \App\Models\KehadiranKegiatan::create([
                    'agenda_kegiatan_id' => $agenda->id,
                    'guru_id' => $guruSAYA->id,
                    'waktu_hadir' => \Carbon\Carbon::now(),
                    'metode' => 'Scan QR'
                ]);

                return response()->json(['status' => 'success', 'pesan' => 'Kehadiran Kegiatan: ' . $agenda->nama_kegiatan . ' berhasil dicatat!']);
            }


            // ========================================================
            // JALUR B: SCAN QR KELAS / JADWAL MENGAJAR (Logika Asli)
            // ========================================================
            $parts = explode('-', $qr_data);
            if (count($parts) != 3 || $parts[0] != 'SP') {
                return response()->json(['status' => 'error', 'pesan' => 'Barcode tidak dikenali / bukan dari SmartPesantren!']);
            }

            $kelas_id = $parts[1];
            $token = $parts[2];

            // 2. Validasi Kriptografi Token Kelas
            $tanggalKunci = \Carbon\Carbon::now()->startOfWeek(\Carbon\Carbon::SATURDAY)->format('Y-m-d');
            $teksRahasia = $kelas_id . '|' . $tanggalKunci . '|' . config('app.key');
            if ($token !== hash_hmac('sha256', $teksRahasia, config('app.key'))) {
                return response()->json(['status' => 'error', 'pesan' => 'BARCODE KADALUARSA! Silakan scan barcode terbaru minggu ini.']);
            }

            // 4. Identifikasi Hari dan Waktu Sekarang
            $hariIni = map_hari(\Carbon\Carbon::now()->format('l'));
            $tanggalSekarang = \Carbon\Carbon::now()->format('Y-m-d');

            // 5. AMBIL JADWAL YANG VALID PADA HARI INI (Konsep Effective-Dated)
            $semuaJadwalRuanganIni = JadwalHarian::with(['guru', 'pelajaran'])
                ->where('kelas_id', $kelas_id)
                ->where('hari', $hariIni)
                ->where(function ($query) use ($tanggalSekarang) {
                    // Syarat 1: Mulai berlaku SEBELUM/SAAT hari ini, ATAU dari dulu (null)
                    $query->whereNull('berlaku_mulai')
                          ->orWhere('berlaku_mulai', '<=', $tanggalSekarang);
                })
                ->where(function ($query) use ($tanggalSekarang) {
                    // Syarat 2: Berlaku sampai SESUDAH/SAAT hari ini, ATAU selamanya (null)
                    $query->whereNull('berlaku_sampai')
                          ->orWhere('berlaku_sampai', '>=', $tanggalSekarang);
                })
                ->get();

            // 6. KECERDASAN BLOK JAM: Filter mana jadwal yang sedang aktif "saat detik ini"
            $masterJams = \App\Models\MasterJam::all()->keyBy('jam_ke');
            $jadwalAktif = [];
            $jamKeDitemukan = [];
            
            foreach ($semuaJadwalRuanganIni as $j) {
                $masterJam = $masterJams[$j->jam_ke] ?? null;
                if ($masterJam) {
                    $batasBawah = \Carbon\Carbon::parse($masterJam->jam_mulai)->subMinutes(15)->format('H:i:s');
                    $batasAtas = \Carbon\Carbon::parse($masterJam->jam_selesai)->addMinutes(15)->format('H:i:s');

                    if ($waktuSekarang >= $batasBawah && $waktuSekarang <= $batasAtas) {
                        $jadwalAktif[] = $j;
                        $jamKeDitemukan[] = $j->jam_ke;
                    }
                }
            }

            if (empty($jadwalAktif)) {
                return response()->json(['status' => 'error', 'pesan' => 'Waktu absen tertutup! Tidak ada KBM yang sedang berlangsung di kelas ini pada pukul ' . \Carbon\Carbon::now()->format('H:i')]);
            }

            // 7. PERCABANGAN LOGIKA: Apakah ini jadwal SAYA atau jadwal ORANG LAIN?
            $jadwalMilikSaya = array_filter($jadwalAktif, function($j) use ($guruSAYA) {
                return $j->guru_id == $guruSAYA->id;
            });

            $periodeAktif = get_periode_aktif();
            $tanggalSekarang = \Carbon\Carbon::now()->format('Y-m-d');

            if (count($jadwalMilikSaya) > 0) {
                // ---> SKENARIO A: NORMAL (Jadwal Milik Sendiri)
                $jumlahDisimpan = 0;
                foreach ($jadwalMilikSaya as $j) {
                    $sudahAbsen = \Illuminate\Support\Facades\DB::table('kehadiran_gurus')
                        ->where('jadwal_id', $j->id)->where('tanggal', $tanggalSekarang)->exists();

                    if (!$sudahAbsen) {
                        \Illuminate\Support\Facades\DB::table('kehadiran_gurus')->insert([
                            'jadwal_id'  => $j->id, 'tanggal' => $tanggalSekarang, 'status' => 'Hadir',
                            'periode_id' => $periodeAktif ? $periodeAktif->id : null,
                            'created_at' => now(), 'updated_at' => now(),
                        ]);
                        $jumlahDisimpan++;
                    }
                }
                
                if ($jumlahDisimpan > 0) {
                    return response()->json(['status' => 'success', 'pesan' => 'Hadir (Jam Ke: ' . implode(',', $jamKeDitemukan) . ') berhasil dicatat!']);
                } else {
                    return response()->json(['status' => 'success', 'pesan' => 'Kehadiran Anda di jam ini sudah tercatat sebelumnya.']);
                }
            } 
            else {
                // ---> SKENARIO B: INVAL / PIKET (Jadwal Milik Guru Lain)
                $jadwalGuruLain = reset($jadwalAktif);
                $jadwalIds = array_map(function($j) { return $j->id; }, $jadwalAktif);
                
                $namaGuruAsli = $jadwalGuruLain->guru->nama_guru ?? 'Guru Tanpa Nama';
                $matpelAsli = $jadwalGuruLain->pelajaran->nama_pelajaran ?? 'Pelajaran';

                return response()->json([
                    'status' => 'confirm_piket',
                    'pesan' => "Jadwal ini milik Ust/Ustz. <b>{$namaGuruAsli}</b> ({$matpelAsli}).<br>Apakah Anda masuk untuk menggantikan (Piket)?",
                    'data' => [
                        'jadwal_ids' => $jadwalIds,
                        'nama_asli' => $namaGuruAsli,
                        'jam_ke' => implode(',', $jamKeDitemukan)
                    ]
                ]);
            }

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'pesan' => 'Kesalahan sistem: ' . $e->getMessage()]);
        }
    }

    // Fungsi: Mengeksekusi Keputusan Piket
    public function prosesPiket(Request $request)
    {
        $request->validate(['jadwal_ids' => 'required|array']);
        
        $user = auth()->user();
        $guruSAYA = Guru::where('nama_guru', $user->name)->orWhere('nig', $user->username)->first();
        $periodeAktif = get_periode_aktif();
        $tanggalSekarang = \Carbon\Carbon::now()->format('Y-m-d');
        
        $jumlahDisimpan = 0;
        foreach ($request->jadwal_ids as $id) {
            \App\Models\KehadiranGuru::updateOrCreate(
                [
                    'jadwal_id' => $id,
                    'tanggal' => $tanggalSekarang
                ],
                [
                    'status' => 'Kosong', 
                    'keterangan' => 'Inval/Piket. Menunggu validasi TU.',
                    'nig_pengganti' => $guruSAYA->nig, 
                    'periode_id' => $periodeAktif ? $periodeAktif->id : null
                ]
            );
            $jumlahDisimpan++;
        }

        return response()->json(['status' => 'success', 'pesan' => 'Tercatat! Anda bertugas sebagai Guru Piket untuk jam ini. TU akan memvalidasi alasannya.']);
    }
}