<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\JadwalHarian;
use App\Models\HariOperasional;
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
            
            // 1. Validasi Format Barcode (Harus berawalan SP-)
            $parts = explode('-', $qr_data);
            if (count($parts) != 3 || $parts[0] != 'SP') {
                return response()->json(['status' => 'error', 'pesan' => 'Barcode tidak valid / bukan milik sistem SmartPesantren!']);
            }

            $kelas_id = $parts[1];
            $token = $parts[2];

            // 2. Validasi Kriptografi (Anti Barcode Bekas)
            $tanggalKunci = Carbon::now()->startOfWeek(Carbon::SATURDAY)->format('Y-m-d');
            $teksRahasia = $kelas_id . '|' . $tanggalKunci . '|' . env('APP_KEY');
            $tokenValid = hash('md5', $teksRahasia);

            if ($token !== $tokenValid) {
                return response()->json(['status' => 'error', 'pesan' => 'BARCODE KADALUARSA! Silakan scan barcode terbaru minggu ini.']);
            }

            // 3. Identifikasi Guru
            $user = auth()->user();
            $guru = Guru::where('nama_guru', $user->name)->orWhere('nig', $user->username)->first();
            if (!$guru) {
                return response()->json(['status' => 'error', 'pesan' => 'Akun Anda belum terhubung dengan data Master Guru.']);
            }

            // 4. Cari Jadwal Blok Hari Ini di Kelas Tersebut
            $mapHari = ['Sunday'=>'Minggu', 'Monday'=>'Senin', 'Tuesday'=>'Selasa', 'Wednesday'=>'Rabu', 'Thursday'=>'Kamis', 'Friday'=>'Jumat', 'Saturday'=>'Sabtu'];
            $hariIni = $mapHari[Carbon::now()->format('l')];
            $waktuSekarang = Carbon::now()->format('H:i:s');

            // Ambil jadwal guru ini, di kelas yang discan, pada hari ini
            $jadwalBlok = JadwalHarian::where('guru_id', $guru->id)
                                      ->where('kelas_id', $kelas_id)
                                      ->where('hari', $hariIni)
                                      ->orderBy('jam_ke', 'asc')
                                      ->get();

            if ($jadwalBlok->isEmpty()) {
                return response()->json(['status' => 'error', 'pesan' => 'Maaf, Anda tidak memiliki jadwal mengajar di kelas ini untuk hari ini.']);
            }

            // 5. Eksekusi Gembok Waktu ±5 Menit (Logika Longgar)
            // Ambil jam_mulai dari jam_ke terkecil, dan jam_selesai dari jam_ke terbesar
            $jamAwal = $jadwalBlok->first()->jam_ke;
            $jamAkhir = $jadwalBlok->last()->jam_ke;

            // Catatan: Pastikan model/tabel HariOperasional punya kolom 'hari', 'jam_ke', 'jam_mulai', 'jam_selesai'
            $waktuMulai = HariOperasional::where('hari', $hariIni)->where('jam_ke', $jamAwal)->value('jam_mulai');
            $waktuSelesai = HariOperasional::where('hari', $hariIni)->where('jam_ke', $jamAkhir)->value('jam_selesai');

            if ($waktuMulai && $waktuSelesai) {
                $batasBawah = Carbon::parse($waktuMulai)->subMinutes(5)->format('H:i:s');
                $batasAtas = Carbon::parse($waktuSelesai)->addMinutes(5)->format('H:i:s');

                if ($waktuSekarang < $batasBawah) {
                    return response()->json(['status' => 'error', 'pesan' => 'Terlalu cepat! Jam pelajaran belum dimulai.']);
                }
                if ($waktuSekarang > $batasAtas) {
                    return response()->json(['status' => 'error', 'pesan' => 'Waktu absen ditutup! Blok jam pelajaran Anda sudah lewat.']);
                }
            }

            // 6. Tembak Status HADIR untuk seluruh blok jam tersebut!
            $periodeAktif = \App\Models\Periode::where('is_active', true)->first();
            $tanggalSekarang = Carbon::now()->format('Y-m-d');
            $jumlahDisimpan = 0;

            foreach ($jadwalBlok as $jadwal) {
                // Cek apakah sudah absen sebelumnya agar tidak dobel
                $sudahAbsen = DB::table('kehadiran_gurus')
                    ->where('jadwal_id', $jadwal->id)
                    ->where('tanggal', $tanggalSekarang)
                    ->exists();

                if (!$sudahAbsen) {
                    DB::table('kehadiran_gurus')->insert([
                        'jadwal_id'  => $jadwal->id,
                        'tanggal'    => $tanggalSekarang,
                        'status'     => 'Hadir',
                        'periode_id' => $periodeAktif ? $periodeAktif->id : null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $jumlahDisimpan++;
                }
            }

            if ($jumlahDisimpan > 0) {
                return response()->json(['status' => 'success', 'pesan' => 'Alhamdulillah, Hadir ('. $jumlahDisimpan .' Jam) berhasil dicatat!']);
            } else {
                return response()->json(['status' => 'success', 'pesan' => 'Kehadiran Anda di blok kelas ini sudah tercatat sebelumnya.']);
            }

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'pesan' => 'Terjadi kesalahan sistem: ' . $e->getMessage()]);
        }
    }
}