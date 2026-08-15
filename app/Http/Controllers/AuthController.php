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
            'login_id' => 'required', // Bisa berisi Username atau Email
            'password' => 'required',
        ]);

        // KECERDASAN GANDA: Mendeteksi apakah inputan berupa Email atau Username
        $loginType = filter_var($request->login_id, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        // Syarat login: ID cocok, Password cocok, dan Status WAJIB Aktif
        $credentials = [
            $loginType => $request->login_id,
            'password' => $request->password,
            'status'   => 'Aktif' // Filter User Aktif
        ];

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended('/');
        }

        // Jika gagal login (salah sandi atau status nonaktif)
        return back()->with('error', 'Login gagal! Periksa kembali data Anda atau hubungi Admin jika akun nonaktif.');
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