<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Services\AuthService;
use App\Services\SesiManager;

class AuthController extends Controller
{
    public function __construct(
        protected AuthService $auth,
        protected SesiManager $sesi
    ) {}

    public function showLogin()
    {
        // Jika sudah login, lempar langsung ke Dashboard
        if (Auth::check()) {
            return redirect('/');
        }

        return view('login', [
            'penandaKonflikPasskey' => (bool) session()->pull('penanda_konflik_passkey'),
            'konflikDevice' => session('konflik_device'),
        ]);
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
        if (!Hash::check($request->password, $user->password)) {
            // Password salah -> Kembalikan ke halaman login DENGAN membawa inputan sebelumnya (withInput)
            return back()->withInput($request->only('login_id'))->with('error', 'Kata sandi yang Anda masukkan salah!');
        }

        // 4. CEK ATURAN SATU PERANGKAT (masih aktif di perangkat lain?)
        $konflik = $this->hadapiKonflikSesi($request, $user);
        if ($konflik) {
            return redirect()->route('login')->with('konflik_device', $konflik);
        }

        // 5. JIKA SEMUA BENAR, IZINKAN MASUK
        \Illuminate\Support\Facades\Auth::login($user);
        $request->session()->regenerate();
        $request->session()->forget('pending_login');

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

        if (!Hash::check($request->password, $user->password)) {
            return response()->json(['status' => 'error', 'message' => 'Kata sandi yang Anda masukkan salah!'], 422);
        }

        $konflik = $this->hadapiKonflikSesi($request, $user);
        if ($konflik) {
            return response()->json($konflik);
        }

        \Illuminate\Support\Facades\Auth::login($user);
        $request->session()->regenerate();
        $request->session()->forget('pending_login');

        return response()->json([
            'status' => 'success',
            'redirect' => $request->session()->pull('url.intended', '/'),
        ]);
    }

    /**
     * Deteksi konflik "satu perangkat". Bila ada sesi lama yang masih hidup,
     * simpan rencana login di sesi (pending) untuk diputuskan user, lalu
     * kembalikan deskripsi konflik.
     *
     * @return array|null Deskripsi konflik, atau null bila aman untuk login.
     */
    private function hadapiKonflikSesi(Request $request, User $user): ?array
    {
        $sesiLama = $this->sesi->cariSesiLain($user, $request->session()->getId());

        if (!$sesiLama) {
            return null;
        }

        $request->session()->put('pending_login', [
            'user_id' => $user->id,
            'sesi_lama' => $sesiLama,
            'intended' => $request->session()->pull('url.intended', '/'),
        ]);

        return [
            'status' => 'konflik',
            'message' => 'Akun ini sedang aktif di perangkat lain.',
        ];
    }

    /**
     * Keputusan user terhadap konflik sesi: tetap di perangkat lama
     * (batal login di sini) atau pindah ke perangkat ini (putus sesi lama).
     */
    public function keputusanDevice(Request $request)
    {
        $valid = $request->validate([
            'keputusan' => 'required|in:tetap_lama,pindah',
        ]);

        $pending = $request->session()->pull('pending_login');

        if (!$pending || empty($pending['user_id'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Sesi pilihan sudah kedaluwarsa. Silakan ulangi login.',
            ], 422);
        }

        $user = User::find($pending['user_id']);

        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Akun tidak ditemukan.'], 422);
        }

        // KEPUTUSAN 1: TETAP DI PERANGKAT LAMA -> batal login di perangkat ini.
        if ($valid['keputusan'] === 'tetap_lama') {
            return response()->json([
                'status' => 'tetap_lama',
                'message' => 'Anda tetap di perangkat lama. Login di sini dibatalkan.',
            ]);
        }

        // KEPUTUSAN 2: PINDAH KE PERANGKAT INI -> putus sesi lama, ambil alih.
        $this->sesi->putusSesi($pending['sesi_lama'] ?? null);
        $user->update(['active_session_id' => null]); // kosongkan dulu agar listener tidak menolak

        Auth::login($user);
        $request->session()->regenerate();

        return response()->json([
            'status' => 'success',
            'redirect' => $pending['intended'] ?? '/',
        ]);
    }

    /**
     * Status sesi untuk polling perangkat lama.
     * Bila sesi ini bukan pemilik aktif lagi -> 'berpindah'.
     */
    public function statusSesi(Request $request)
    {
        if (Auth::check() && Auth::user()->active_session_id === $request->session()->getId()) {
            return response()->json(['status' => 'ok']);
        }

        return response()->json(['status' => 'berpindah']);
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

    public function simpanTema(Request $request)
    {
        $valid = $request->validate([
            'tema' => 'required|in:sistem,terang,gelap',
        ]);

        Auth::user()->update(['tema' => $valid['tema']]);

        return response()->json(['status' => 'success', 'tema' => $valid['tema']]);
    }

    public function logout(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            $this->sesi->lepas($user, $request->session()->getId());
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}