<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    /** Sub-kunci modul kebendaharaan (termasuk toko buku). */
    private const SUB = [
        'akses_kebendaharaan',
        'akses_anggaran',
        'akses_pencairan',
        'akses_validasi_pencairan',
        'akses_laporan_kebendaharaan',
        'akses_validasi_laporan',
        'akses_pemasukan',
        'akses_rekap_kebendaharaan',
        'akses_toko_buku',
        'akses_toko_buku_kelola',
        'akses_toko_buku_distribusi',
        'akses_toko_buku_penjualan',
        'akses_toko_buku_setoran',
    ];

    /** Kunci yang dipegang Bendahara. */
    private const BENDAHARA = [
        'akses_kebendaharaan',
        'akses_anggaran',
        'akses_pencairan',
        'akses_validasi_pencairan',
        'akses_laporan_kebendaharaan',
        'akses_validasi_laporan',
        'akses_pemasukan',
        'akses_rekap_kebendaharaan',
        'akses_toko_buku',
        'akses_toko_buku_kelola',
        'akses_toko_buku_distribusi',
        'akses_toko_buku_penjualan',
        'akses_toko_buku_setoran',
    ];

    /** Kunci yang dipegang Staf Bendahara. */
    private const STAF = [
        'akses_kebendaharaan',
        'akses_pencairan',
        'akses_laporan_kebendaharaan',
        'akses_rekap_kebendaharaan',
        'akses_toko_buku',
        'akses_toko_buku_kelola',
        'akses_toko_buku_distribusi',
        'akses_toko_buku_penjualan',
        'akses_toko_buku_setoran',
    ];

    /** Kunci yang dipegang Wali Kelas (sebatas beranda toko & kelola kelasnya). */
    private const WALI_KELAS = [
        'akses_toko_buku',
        'akses_toko_buku_distribusi',
        'akses_toko_buku_penjualan',
    ];

    public function up(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Buat kunci baru (additive, tidak menghapus apapun)
        foreach (self::SUB as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        // 2. Berikan kunci ke role (pakai givePermissionTo: hanya menambah, tidak mencoret).
        $admin = Role::firstOrCreate(['name' => 'Administrator']);
        $bendahara = Role::firstOrCreate(['name' => 'Bendahara']);
        $staf = Role::firstOrCreate(['name' => 'Staf Bendahara']);
        $wali = Role::firstOrCreate(['name' => 'Wali Kelas']);

        $admin->givePermissionTo(self::SUB);
        $bendahara->givePermissionTo(self::BENDAHARA);
        $staf->givePermissionTo(self::STAF);
        $wali->givePermissionTo(self::WALI_KELAS);

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function down(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        Permission::whereIn('name', self::SUB)->delete();

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
};