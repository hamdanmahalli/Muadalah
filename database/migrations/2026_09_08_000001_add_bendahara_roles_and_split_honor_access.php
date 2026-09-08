<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    /** Sub-kunci modul honor hasil pemecahan akses_honor. */
    private const HONOR_SUB = [
        'akses_honor_konfigurasi',
        'akses_honor_proses',
        'akses_honor_final',
        'akses_honor_scan',
    ];

    public function up(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Kunci baru untuk sub-modul honor
        foreach (self::HONOR_SUB as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        // 2. Role lain yang sebelumnya memegang akses_honor penuh (mis. Tata Usaha)
        //    tetap diberi sub-kunci konfigurasi/proses/scan agar kapabilitas lama tidak hilang,
        //    tetapi TANPA finalisasi (final sementara hanya Administrator).
        //    Dihitung SEBELUM role Bendahara/Staf dibuat agar mereka tidak ikut tercoret.
        $nonFinal = ['akses_honor', 'akses_honor_konfigurasi', 'akses_honor_proses', 'akses_honor_scan'];
        $rolePunyaHonor = Role::where('name', '!=', 'Administrator')
            ->whereIn('id', DB::table('role_has_permissions')
                ->whereIn('permission_id', Permission::where('name', 'akses_honor')->pluck('id'))
                ->pluck('role_id'))
            ->get();
        foreach ($rolePunyaHonor as $role) {
            $role->givePermissionTo($nonFinal);
        }

        // 3. Hak akses murid diganti menjadi Bendahara
        Role::where('name', 'Murid')->update(['name' => 'Bendahara']);

        // 4. Role baru: Staf Bendahara
        $bendahara     = Role::firstOrCreate(['name' => 'Bendahara']);
        $stafBendahara = Role::firstOrCreate(['name' => 'Staf Bendahara']);

        // 5. Pembagian akses honor (sync = deterministik, tidak menumpuk saat migrasi diulang):
        //    - Bendahara: lihat + konfigurasi + proses (final & buka HANYA Administrator)
        $bendahara->syncPermissions([
            'akses_honor',
            'akses_honor_konfigurasi',
            'akses_honor_proses',
            'akses_dashboard',
        ]);

        //    - Staf Bendahara: hanya scan penerimaan honor
        $stafBendahara->syncPermissions([
            'akses_honor',
            'akses_honor_scan',
            'akses_dashboard',
        ]);

        //    - Administrator tetap memegang semua kunci termasuk sub-kunci baru
        Role::firstOrCreate(['name' => 'Administrator'])->givePermissionTo(self::HONOR_SUB);

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function down(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        Role::where('name', 'Staf Bendahara')->delete();
        Role::where('name', 'Bendahara')->update(['name' => 'Murid']);

        Permission::whereIn('name', self::HONOR_SUB)->delete();

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
};