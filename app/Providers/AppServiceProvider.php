<?php

namespace App\Providers;

use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Events\Login;

use App\Listeners\TetapkanSesiAktif;
use Laravel\Passkeys\Contracts\PasskeyUser;
use Laravel\Passkeys\Passkey;
use Laravel\Passkeys\Passkeys;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(Request $request): void
    {
        // Aturan satu-perangkat: proses penetapan sesi aktif setelah login
        // (berlaku juga untuk login sidik jari / passkey).
        Event::listen(Login::class, TetapkanSesiAktif::class);

        // WebAuthn: sesuaikan allowed_origins dengan host & skema yang benar-benar
        // dipakai saat ini, agar tidak bergantung pada nilai APP_URL di server
        // (mencegah error "Invalid origin. Not in the list of allowed origins.").
        config([
            'passkeys.allowed_origins' => array_values(array_filter([
                'https://'.$request->getHost(),
                'http://'.$request->getHost(),
            ])),
        ]);

        // Tolak login passkey bila akun tidak Aktif (konsisten dengan prosesLogin)
        Passkeys::authorizeLoginUsing(function (Request $request, PasskeyUser $user, Passkey $passkey): bool {
            if ($user instanceof \App\Models\User && $user->status !== 'Aktif') {
                throw ValidationException::withMessages([
                    'credential' => ['Akun Anda dinonaktifkan. Silakan hubungi Admin TU.'],
                ]);
            }

            return true;
        });
    }
}
