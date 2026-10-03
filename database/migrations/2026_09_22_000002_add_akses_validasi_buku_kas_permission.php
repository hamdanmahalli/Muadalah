<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    private const PERM = 'akses_validasi_buku_kas';

    public function up(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        Permission::firstOrCreate(['name' => self::PERM]);

        // Administrator selalu memegang semua kunci.
        Role::firstOrCreate(['name' => 'Administrator'])->givePermissionTo(self::PERM);

        // Bila role Pimpinan/Kepala Sekolah ada, berikan juga (pengesah laporan).
        foreach (['Pimpinan', 'Kepala Sekolah'] as $nama) {
            $role = Role::where('name', $nama)->first();
            if ($role) {
                $role->givePermissionTo(self::PERM);
            }
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function down(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        Permission::where('name', self::PERM)->delete();
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
