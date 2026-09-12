<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * MENEGAKKAN aturan satu-perangkat di setiap rute ber-`auth`.
 *
 * 1. Finalisasi penetapan sesi aktif (dari kejadian Login) memakai
 *    session id FINAL (setelah regenerate).
 * 2. Adopsi sesi lama sebelum fitur ini (active_session_id kosong).
 * 3. Tolak request dari sesi yang bukan pemilik -> logout paksa & arahkan
 *    kembali ke halaman login.
 */
class MenegakkanSatuDevice
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();
            $finalSid = session()->getId();

            if (session('sesi_aktif_menunggu') == $user->getKey()) {
                $user->update(['active_session_id' => $finalSid]);
                $user->refresh();
                session()->forget('sesi_aktif_menunggu');
            }

            if ($user->active_session_id === null) {
                // Sesi warisan (sebelum fitur ini) -> jadikan pemilik agar
                // tidak memaksa semua user logout serentak saat rilis.
                $user->update(['active_session_id' => $finalSid]);
            } elseif ($user->active_session_id !== $finalSid) {
                // Sesi ini BUKAN pemilik -> sesi di perangkat lain yang dipakai.
                Auth::logout();
                session()->invalidate();
                session()->regenerateToken();

                return redirect()->route('login', ['sesi' => 'berpindah']);
            }
        }

        return $next($request);
    }
}