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
        // 1. Bersihkan memori cache Spatie
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 2. Daftar Kunci Pintu (Permissions)
        $permissions = [
            'akses_dashboard',
            'akses_dashboard_guru', // <--- FITUR BARU: Kunci untuk Dashboard Guru (Mobile)
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
            'akses_jadwal_saya',
            'akses_riwayat_mutasi',
            'akses_pengumuman' 
        ];

        // Buat Kunci di Database
        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        // Hapus Role Lama (Pembersihan)
        Role::whereNotIn('name', [
            'Administrator', 'Pimpinan', 'Tata Usaha', 'Kepanitiaan', 
            'Wali Kelas', 'Dewan Guru', 'Murid', 'Wali Murid'
        ])->delete();

        // 3. Mendaftarkan Jabatan Resmi
        $roleAdmin       = Role::firstOrCreate(['name' => 'Administrator']);
        $rolePimpinan    = Role::firstOrCreate(['name' => 'Pimpinan']);
        $roleTataUsaha   = Role::firstOrCreate(['name' => 'Tata Usaha']);
        $roleKepanitiaan = Role::firstOrCreate(['name' => 'Kepanitiaan']);
        $roleWaliKelas   = Role::firstOrCreate(['name' => 'Wali Kelas']);
        $roleDewanGuru   = Role::firstOrCreate(['name' => 'Dewan Guru']);
        $roleMurid       = Role::firstOrCreate(['name' => 'Murid']);
        $roleWaliMurid   = Role::firstOrCreate(['name' => 'Wali Murid']);

        // 4. Sinkronisasi Kunci Sementara
        $roleAdmin->syncPermissions($permissions); // Admin pegang semua kunci
        $rolePimpinan->syncPermissions(['akses_dashboard', 'akses_laporan', 'akses_riwayat_mutasi', 'akses_target_mengajar', 'akses_jadwal_harian']);
        $roleTataUsaha->syncPermissions(['akses_dashboard', 'akses_meja_kontrol', 'akses_laporan', 'akses_master_guru', 'akses_master_pelajaran', 'akses_master_kelas', 'akses_riwayat_mutasi', 'akses_pengumuman']);
        
        // PENTING: Berikan Kunci Dashboard Guru ke Dewan Guru
        $roleDewanGuru->syncPermissions(['akses_dashboard_guru', 'akses_jadwal_saya']);
    }
}

