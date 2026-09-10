<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

/**
 * Konversi sekali-jalan: role lama -> akses per-user.
 *
 * Untuk SETIAP user (kecuali Administrator):
 *   - union permission dari seluruh role-nya + permission langsung yang sudah ada
 *   - disimpan menjadi permission langsung (model_has_permissions)
 *   - role di-strip (syncRoles([]))
 * Administrator dilewati (tetap role penuh + semua kunci).
 *
 * PERHATIAN: jalankan SEKALI saja per environment, setelah migrate.
 * JANGAN jalankan `db:seed PermissionSeeder` di server yang sudah punya
 * tuning matriks hak akses (menimpa). Seeder ini membaca data apa adanya.
 */
class ConvertRoleToDirectPermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $converted = 0;
        $skipped = 0;

        User::with('roles.permissions')->get()->each(function (User $user) use (&$converted, &$skipped) {
            // Administrator dipertahankan (role penuh), tidak pernah di-strip.
            if ($user->hasRole('Administrator')) {
                $skipped++;
                return;
            }

            // Union permission (dari role sang templat + permission langsung yang sudah ada)
            $namaPerm = $user->roles
                ->flatMap(fn ($r) => $r->permissions->pluck('name'))
                ->merge($user->getPermissionNames())
                ->unique()
                ->values()
                ->all();

            $user->syncPermissions($namaPerm);
            $user->syncRoles([]);
            $user->save();

            $converted++;
        });

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->command->info("Selesai: {$converted} user dikonversi ke akses per-user, {$skipped} Administrator dilewati.");
    }
}