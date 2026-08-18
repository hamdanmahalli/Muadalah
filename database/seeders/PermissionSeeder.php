<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Bersihkan memori cache Spatie agar tidak terjadi bentrok
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 2. Daftar semua "Kunci Pintu" menu di sistem kita
        $permissions = [
            'akses_dashboard',
            'akses_meja_kontrol',
            'akses_laporan',
            'akses_import_excel',
            'akses_master_guru',
            'akses_master_pelajaran',
            'akses_master_kelas',
            'akses_target_mengajar',
            'akses_jadwal_harian',
            'akses_hari_operasional',
            'akses_hari_libur',
            'akses_master_periode',
            'akses_manajemen_user',
            'akses_manajemen_akses',
            'akses_jadwal_saya' 
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        // =========================================================
        // FITUR PEMBERSIH: Menghapus Role (Jabatan) Lama yang Ganda
        // =========================================================
        Role::whereNotIn('name', [
            'Administrator', 'Pimpinan', 'Tata Usaha', 'Kepanitiaan', 
            'Wali Kelas', 'Dewan Guru', 'Murid', 'Wali Murid'
        ])->delete();

        // 3. Mendaftarkan 8 Jabatan (Role) Resmi Sesuai Permintaan
        $roleAdmin       = Role::firstOrCreate(['name' => 'Administrator']);
        $rolePimpinan    = Role::firstOrCreate(['name' => 'Pimpinan']);
        $roleTataUsaha   = Role::firstOrCreate(['name' => 'Tata Usaha']);
        $roleKepanitiaan = Role::firstOrCreate(['name' => 'Kepanitiaan']);
        $roleWaliKelas   = Role::firstOrCreate(['name' => 'Wali Kelas']);
        $roleDewanGuru   = Role::firstOrCreate(['name' => 'Dewan Guru']);
        $roleMurid       = Role::firstOrCreate(['name' => 'Murid']);
        $roleWaliMurid   = Role::firstOrCreate(['name' => 'Wali Murid']);

        // 4. Suntikan Hak Akses Standar Sementara (Bisa diubah 100% via layar Web nanti)
        $roleAdmin->syncPermissions($permissions); // Admin pegang semua kunci
        $rolePimpinan->syncPermissions(['akses_dashboard', 'akses_laporan']);
        $roleTataUsaha->syncPermissions(['akses_dashboard', 'akses_meja_kontrol', 'akses_laporan', 'akses_master_guru', 'akses_master_pelajaran', 'akses_master_kelas']);
        $roleDewanGuru->syncPermissions(['akses_dashboard', 'akses_jadwal_saya']);
    }
}