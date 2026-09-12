<?php

namespace App\Listeners;

use App\Models\User;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Auth;

/**
 * Listener kejadian Login (berlaku untuk login password & passkey).
 *
 * - Bila sudah ada sesi aktif lain yang masih hidup di perangkat lain,
 *   login yang datang belakangan (jalur passkey / web) DITOLAK dan
 *   diarahkan balik ke form login untuk memilih perangkat.
 * - Bila aman, catat rencana penetapan sesi aktif via flag sesi
 *   (diproses middleware setelah session id final / regenerate).
 */
class TetapkanSesiAktif
{
    public function handle(Login $event): void
    {
        $user = $event->user;

        if (!$user instanceof User) {
            return;
        }

$currentSid = session()->getId();
        $ownerSid = $user->active_session_id;
        $handler = app('session')->getHandler();

        // Konflik nyata dari jalur selain prosesLogin (mis. login sidik jari):
        // sesi lain masih hidup -> tolak login ini.
        if ($ownerSid && $ownerSid !== $currentSid && $handler->read($ownerSid) !== '') {
            Auth::logout();
            session()->invalidate();
            session()->regenerateToken();
            session(['penanda_konflik_passkey' => true]);

            return;
        }

        // Aman: tandai rencana penetapan. Sesi id bisa berubah setelah event
        // ini (regenerate), jadi penetapan final dilakukan middleware.
        session(['sesi_aktif_menunggu' => $user->getKey()]);
    }
}
