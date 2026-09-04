<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\AuthService;

class AuthController extends Controller
{
    public function __construct(
        protected AuthService $auth
    ) {}

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

        // Untuk permintaan AJAX (fetch), kembalikan JSON tanpa reload halaman.
        if ($request->expectsJson()) {
            return $this->prosesLoginJson($request);
        }

        // 1. CARI USERNYA DULU
        $user = $this->auth->temukanUser($request->login_id);

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

    // Versi JSON untuk login via fetch/AJAX (tanpa reload halaman → tanpa kedip).
    private function prosesLoginJson(Request $request)
    {
        $user = $this->auth->temukanUser($request->login_id);

        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'User tidak ditemukan di dalam sistem!'], 422);
        }

        if ($user->status !== 'Aktif') {
            return response()->json(['status' => 'error', 'message' => 'Akun Anda dinonaktifkan. Silakan hubungi Admin TU.'], 422);
        }

        if (!\Illuminate\Support\Facades\Hash::check($request->password, $user->password)) {
            return response()->json(['status' => 'error', 'message' => 'Kata sandi yang Anda masukkan salah!'], 422);
        }

        \Illuminate\Support\Facades\Auth::login($user);
        $request->session()->regenerate();

        return response()->json([
            'status' => 'success',
            'redirect' => $request->session()->pull('url.intended', '/'),
        ]);
    }

    // ==========================================================
    // INTIP JADWAL HARI INI (Khusus Guru, Tanpa Login)
    // ==========================================================
    public function intipJadwal(Request $request)
    {
        return response()->json(
            $this->auth->intipJadwalData($request->input('login_id'))
        );
    }

    public function gantiPassword(Request $request)
    {
        $user = Auth::user();
        $result = $this->auth->gantiPassword(
            $user,
            $request->password_lama,
            $request->password_baru,
            $request->password_baru_confirmation
        );

        return response()->json($result, $result['status'] ?? 200);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}
