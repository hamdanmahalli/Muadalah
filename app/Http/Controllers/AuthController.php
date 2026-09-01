<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        // Jika sudah login, lempar langsung ke Dashboard
        if (Auth::check()) {
            return redirect('/');
        }
        return view('login');
    }

    public function prosesLogin(Request $request)
    {
        $request->validate([
            'login_id' => 'required', 
            'password' => 'required',
        ]);

        // Deteksi apakah inputan berupa Email atau Username
        $loginType = filter_var($request->login_id, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        // 1. CARI USERNYA DULU
        $user = \App\Models\User::where($loginType, $request->login_id)->first();

        // JIKA USER TIDAK DITEMUKAN
        if (!$user) {
            return back()->with('error', 'User tidak ditemukan di dalam sistem!');
        }

        // 2. JIKA USER DITEMUKAN, CEK STATUSNYA
        if ($user->status !== 'Aktif') {
            return back()->withInput($request->only('login_id'))->with('error', 'Akun Anda dinonaktifkan. Silakan hubungi Admin TU.');
        }

        // 3. JIKA STATUS AKTIF, CEK PASSWORDNYA
        if (!\Illuminate\Support\Facades\Hash::check($request->password, $user->password)) {
            // Password salah -> Kembalikan ke halaman login DENGAN membawa inputan sebelumnya (withInput)
            return back()->withInput($request->only('login_id'))->with('error', 'Kata sandi yang Anda masukkan salah!');
        }

        // 4. JIKA SEMUA BENAR, IZINKAN MASUK
        \Illuminate\Support\Facades\Auth::login($user);
        $request->session()->regenerate();
        
        return redirect()->intended('/');
    }

    // ==========================================================
    // INTIP JADWAL HARI INI (Khusus Guru, Tanpa Login)
    // Dipanggil dari halaman login saat username diketik/teringat.
    // ==========================================================
    public function intipJadwal(Request $request)
    {
        $loginId = trim($request->input('login_id'));

        if ($loginId === '') {
            return response()->json(['ok' => false, 'pesan' => 'Identitas tidak ditemukan.']);
        }

        // Deteksi apakah inputan berupa Email atau Username
        $loginType = filter_var($loginId, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $user = \App\Models\User::where($loginType, $loginId)->first();

        if (!$user) {
            return response()->json(['ok' => false, 'pesan' => 'Identitas tidak ditemukan.']);
        }

        // Cari data guru dari akun tersebut
        $guru = \App\Models\Guru::where('nama_guru', $user->name)
                                ->orWhere('nig', $user->username)
                                ->first();

        // Bukan akun guru (Admin/TU) -> tanpa jadwal mengajar
        if (!$guru) {
            return response()->json(['ok' => false, 'pesan' => 'Akun tanpa jadwal mengajar.']);
        }

        $hariIni = map_hari(\Carbon\Carbon::now()->format('l'));
        $periodeAktif = get_periode_aktif();
        $tahunAjaran = $periodeAktif ? $periodeAktif->tahun_ajaran : null;

        $jadwalMentah = \App\Models\JadwalHarian::with(['kelas', 'pelajaran'])
                            ->where('guru_id', $guru->id)
                            ->where('hari', 'ilike', $hariIni)
                            ->where('tahun_ajaran', $tahunAjaran)
                            ->orderBy('jam_ke')
                            ->get();

        $masterJam = \App\Models\MasterJam::orderBy('jam_ke', 'asc')->get()->keyBy('jam_ke');

        // Gabungkan jam berurutan menjadi blok (pola sama seperti dashboardGuru)
        $blok = [];
        $currentBlock = null;

        foreach ($jadwalMentah as $j) {
            $namaPel = $j->pelajaran->nama_pelajaran ?? 'Pelajaran';
            $namaKel = $j->kelas->nama_kelas ?? '-';
            $tingkatKelas = preg_replace('/[^0-9]/', '', $namaKel);
            $petaKitab = $j->pelajaran->kitab_tingkat ?? [];
            $namaKitab = $petaKitab[$tingkatKelas] ?? ($j->pelajaran->nama_kitab ?? '-');

            if (!$currentBlock) {
                $currentBlock = [
                    'jam_mulai'  => $j->jam_ke,
                    'jam_selesai' => $j->jam_ke,
                    'pelajaran'  => $namaPel,
                    'kitab'      => $namaKitab,
                    'kelas'      => $namaKel,
                ];
            } elseif ($currentBlock['pelajaran'] == $namaPel
                    && $currentBlock['kelas'] == $namaKel
                    && $j->jam_ke == $currentBlock['jam_selesai'] + 1
                    && ($currentBlock['jam_selesai'] - $currentBlock['jam_mulai'] < 1)) {
                $currentBlock['jam_selesai'] = $j->jam_ke;
            } else {
                $blok[] = $currentBlock;
                $currentBlock = [
                    'jam_mulai'  => $j->jam_ke,
                    'jam_selesai' => $j->jam_ke,
                    'pelajaran'  => $namaPel,
                    'kitab'      => $namaKitab,
                    'kelas'      => $namaKel,
                ];
            }
        }

        if ($currentBlock) {
            $blok[] = $currentBlock;
        }

        $jadwal = [];
        foreach ($blok as $item) {
            $jam1 = $masterJam->get($item['jam_mulai']);
            $jam2 = $masterJam->get($item['jam_selesai']);
            $waktu = '';

            if ($jam1) {
                $waktu = \Carbon\Carbon::parse($jam1->jam_mulai)->format('H:i');
                if ($jam2 && $jam2->jam_selesai) {
                    $waktu .= ' - ' . \Carbon\Carbon::parse($jam2->jam_selesai)->format('H:i');
                }
            }

            $jadwal[] = [
                'jam'      => ($item['jam_mulai'] == $item['jam_selesai'])
                                ? (string) $item['jam_mulai']
                                : $item['jam_mulai'] . '-' . $item['jam_selesai'],
                'waktu'    => $waktu,
                'pelajaran'=> $item['pelajaran'],
                'kitab'    => $item['kitab'],
                'kelas'    => $item['kelas'],
            ];
        }

        return response()->json([
            'ok'        => true,
            'nama_guru' => $guru->nama_guru,
            'hari'      => $hariIni,
            'tanggal'   => \Carbon\Carbon::now()->format('d/m/Y'),
            'jadwal'    => $jadwal,
        ]);
    }

    public function gantiPassword(Request $request)
    {
        $user = Auth::user();
        
        // PRIORITAS 1: Cek apakah Password Lama salah
        if (!Hash::check($request->password_lama, $user->password)) {
            return response()->json(['pesan' => 'Gagal! Password lama Anda salah.'], 400);
        }

        // PRIORITAS 2: Cek apakah Password Baru dan Konfirmasi tidak sama
        if ($request->password_baru !== $request->password_baru_confirmation) {
            return response()->json(['pesan' => 'Gagal! Konfirmasi password baru tidak cocok.'], 400);
        }

        // PRIORITAS 3: Pengamanan panjang minimal password (opsional)
        if (strlen($request->password_baru) < 6) {
            return response()->json(['pesan' => 'Gagal! Password baru minimal 6 karakter.'], 400);
        }

        // Jika semua tahap lolos, ubah password di brankas
        $user->update([
            'password' => Hash::make($request->password_baru)
        ]);

        return response()->json(['pesan' => 'Alhamdulillah, Password berhasil diperbarui!']);
    }
    
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}