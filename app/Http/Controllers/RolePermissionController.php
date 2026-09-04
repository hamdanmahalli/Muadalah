<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionController extends Controller
{
    public function index()
    {
        // Ambil SEMUA role tanpa terkecuali
        $roles = Role::orderBy('id', 'asc')->get();
        $permissions = Permission::orderBy('id', 'asc')->get();

        // Pengelompokan permission mengikuti 6 grup menu pada sidebar
        $grupMenu = [
            'Beranda & Monitoring' => [
                'akses_dashboard',
                'akses_meja_kontrol',
                'akses_laporan',
                'akses_agenda',
            ],
            'Master Data' => [
                'akses_master_guru',
                'akses_master_kelas',
                'akses_master_pelajaran',
                'akses_batas_pelajaran',
                'akses_master_siswa',
                'akses_master_periode',
            ],
            'Jadwal & Kaldik' => [
                'akses_hari_operasional',
                'akses_hari_libur',
                'akses_pengumuman',
                'akses_target_mengajar',
                'akses_jadwal_harian',
                'akses_riwayat_mutasi',
            ],
            'Guru' => [
                'akses_dashboard_guru',
                'akses_jadwal_saya',
                'akses_siswa_saya',
            ],
            'Siswa' => [
                'akses_penempatan_siswa',
                'akses_absen_siswa',
                'akses_input_nilai',
                'akses_laporan_siswa',
                'akses_pembayaran',
            ],
            'Pengaturan Sistem' => [
                'akses_manajemen_user',
                'akses_manajemen_akses',
                'akses_import_excel',
                'akses_backup_restore',
            ],
        ];

        // Label ramah untuk setiap permission (nama menu di sidebar)
        $labelMenu = [
            'akses_dashboard' => 'Dashboard',
            'akses_meja_kontrol' => 'Meja Kontrol & Monitoring Kehadiran',
            'akses_laporan' => 'Rekap Laporan Kehadiran',
            'akses_agenda' => 'Agenda Kegiatan',
            'akses_master_guru' => 'Master Pengurus/Guru & Jabatan',
            'akses_master_kelas' => 'Master Kelas',
            'akses_master_pelajaran' => 'Master Pelajaran',
            'akses_batas_pelajaran' => 'Batas Pelajaran',
            'akses_master_siswa' => 'Master Siswa',
            'akses_master_periode' => 'Master Periode',
            'akses_hari_operasional' => 'Hari Operasional',
            'akses_hari_libur' => 'Kalender Pendidikan',
            'akses_pengumuman' => 'Pengumuman',
            'akses_target_mengajar' => 'Target Mengajar (Plot Jadwal)',
            'akses_jadwal_harian' => 'Jadwal Harian',
            'akses_riwayat_mutasi' => 'Riwayat Mutasi Jadwal',
            'akses_dashboard_guru' => 'Beranda Guru',
            'akses_jadwal_saya' => 'Jadwal Saya, Scan Hadir & Cetak Barcode',
            'akses_siswa_saya' => 'Siswa Saya (Wali Kelas)',
            'akses_penempatan_siswa' => 'Penempatan Siswa',
            'akses_absen_siswa' => 'Absensi Siswa',
            'akses_input_nilai' => 'Input Nilai',
            'akses_laporan_siswa' => 'Raport & Laporan Siswa',
            'akses_pembayaran' => 'Tagihan & Pembayaran',
            'akses_manajemen_user' => 'Setup User',
            'akses_manajemen_akses' => 'Hak Akses',
            'akses_import_excel' => 'Pusat Import (Excel)',
            'akses_backup_restore' => 'Manajemen Database (Backup & Restore)',
        ];

        return view('role-permission', compact('roles', 'permissions', 'grupMenu', 'labelMenu'));
    }

    public function update(Request $request)
    {
        // HANYA Administrator yang boleh mengubah matriks hak akses.
        // Mencegah eskalasi privilege dari role lain yang memegang akses_manajemen_akses.
        if (!auth()->user() || !auth()->user()->hasRole('Administrator')) {
            abort(403, 'Hanya Administrator yang dapat mengubah hak akses.');
        }

        $permissions = Permission::pluck('id')->all();
        $request->validate([
            'permissions' => 'nullable|array',
            'permissions.*' => 'array',
            'permissions.*.*' => 'integer',
        ]);

        // Ambil SEMUA role untuk diupdate hak aksesnya
        $roles = Role::all();

        foreach ($roles as $role) {
            $safeRoleName = str_replace(' ', '_', $role->name);
            $permissionsForRole = $request->input('permissions.' . $safeRoleName, []);

            // Whitelist: hanya tampung id permission yang benar-benar terdaftar
            $permissionsForRole = array_values(array_intersect($permissionsForRole, $permissions));

            // Konversi id -> model Permission agar syncPermissions tidak salah baca (integer = nama)
            $permissionModels = Permission::whereIn('id', $permissionsForRole)->get();

            // Sinkronisasi kunci ke jabatan tersebut
            $role->syncPermissions($permissionModels);
        }

        return redirect()->back()->with('sukses', 'Matriks Hak Akses berhasil diperbarui! Perubahan langsung aktif.');
    }
}