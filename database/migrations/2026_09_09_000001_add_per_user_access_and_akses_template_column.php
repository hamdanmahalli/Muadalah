<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Kunci baru hasil pemecahan kunci yang dipakai 2 menu sekaligus:
        //    - akses_master_jabatan  -> halaman Master Jabatan
        //    - akses_pabrik_barcode  -> halaman Cetak Barcode (admin)
        $baru = ['akses_master_jabatan', 'akses_pabrik_barcode'];
        foreach ($baru as $name) {
            Permission::firstOrCreate(['name' => $name]);
        }

        // 2. Administrator tetap super (memegang semua kunci).
        //    Templat Tata Usaha yang sebelumnya membuka Master Guru & Jabatan / cetak barcode
        //    tetap diberi kunci baru agar kapabilitas lamanya tidak hilang.
        Role::firstOrCreate(['name' => 'Administrator'])->givePermissionTo($baru);

        $tu = Role::where('name', 'Tata Usaha')->first();
        if ($tu) {
            $tu->givePermissionTo($baru);
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function down(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        Permission::whereIn('name', ['akses_master_jabatan', 'akses_pabrik_barcode'])->delete();

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
};