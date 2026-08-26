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