<?php

namespace App\Providers;

use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\ValidationException;
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
    public function boot(): void
    {
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
