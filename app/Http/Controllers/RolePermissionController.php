<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RolePermissionController extends Controller
{
    /** Kunci "sistem" yang tidak boleh dicabut pengguna dari dirinya sendiri (anti self-lockout). */
    public const SISTEM = [
        'akses_manajemen_akses',
        'akses_manajemen_user',
        'akses_backup_restore',
        'akses_dashboard',
    ];

    /**
     * Tolak bila pemilik sesi akan kehilangan kunci sistem sendiri.
     * Reusable untuk simpan akses manual, terapkan templat, dan setup user.
     */
    public static function pastikanSistemAman(User $target, array $permTerpilih): void
    {
        if ((int) $target->id !== (int) auth()->id()) {
            return;
        }

        $hilang = array_values(array_diff(self::SISTEM, $permTerpilih));
        if ($hilang) {
            abort(422, 'Tidak boleh: akun Anda sendiri akan kehilangan akses sistem (' . implode(', ', $hilang) . '). Minta Administrator lain yang mengatur.');
        }
    }

    /** Daftar permission yang boleh di-centang di UI (whitelist). */
    public const PERMISSIONS = [
        'akses_dashboard',
        'akses_meja_kontrol',
        'akses_monitoring_kehadiran',
        'akses_laporan',
        'akses_agenda',
        'akses_pabrik_barcode',
        'akses_honor',
        'akses_honor_konfigurasi',
        'akses_honor_proses',
        'akses_honor_final',
        'akses_honor_scan',
        'akses_master_guru',
        'akses_master_jabatan',
        'akses_master_kelas',
        'akses_master_pelajaran',
        'akses_batas_pelajaran',
        'akses_master_siswa',
        'akses_master_periode',
        'akses_hari_operasional',
        'akses_hari_libur',
        'akses_pengumuman',
        'akses_target_mengajar',
        'akses_jadwal_harian',
        'akses_riwayat_mutasi',
        'akses_dashboard_guru',
        'akses_jadwal_saya',
        'akses_siswa_saya',
        'akses_penempatan_siswa',
        'akses_absen_siswa',
        'akses_input_nilai',
        'akses_laporan_siswa',
        'akses_pembayaran',
        'akses_manajemen_user',
        'akses_manajemen_akses',
        'akses_import_excel',
        'akses_backup_restore',
    ];

    /** Pohon menu (sama dengan grup sidebar) — sumber render ceklis di panel Fasilitas Menu. */
    public const GRUP_MENU = [
        'Beranda & Monitoring' => [
            ['perm' => 'akses_dashboard', 'label' => 'Dashboard'],
            ['perm' => 'akses_meja_kontrol', 'label' => 'Meja Kontrol'],
            ['perm' => 'akses_monitoring_kehadiran', 'label' => 'Monitoring Kehadiran'],
            ['perm' => 'akses_laporan', 'label' => 'Rekap Laporan Kehadiran'],
            ['perm' => 'akses_agenda', 'label' => 'Agenda Kegiatan'],
            ['perm' => 'akses_pabrik_barcode', 'label' => 'Pabrik Barcode (Cetak QR)'],
            [
                'perm' => 'akses_honor',
                'label' => 'Honor Guru',
                'sub' => [
                    ['perm' => 'akses_honor_konfigurasi', 'label' => 'Honor — Konfigurasi Tarif'],
                    ['perm' => 'akses_honor_proses', 'label' => 'Honor — Proses & Rekap'],
                    ['perm' => 'akses_honor_final', 'label' => 'Honor — Finalisasi / Buka Kembali'],
                    ['perm' => 'akses_honor_scan', 'label' => 'Honor — Scan Penerimaan'],
                ],
            ],
        ],
        'Master Data' => [
            ['perm' => 'akses_master_guru', 'label' => 'Master Pengurus/Guru'],
            ['perm' => 'akses_master_jabatan', 'label' => 'Master Jabatan'],
            ['perm' => 'akses_master_kelas', 'label' => 'Master Kelas'],
            ['perm' => 'akses_master_pelajaran', 'label' => 'Master Pelajaran'],
            ['perm' => 'akses_batas_pelajaran', 'label' => 'Batas Pelajaran'],
            ['perm' => 'akses_master_siswa', 'label' => 'Master Siswa'],
            ['perm' => 'akses_master_periode', 'label' => 'Master Periode'],
        ],
        'Jadwal & Kaldik' => [
            ['perm' => 'akses_hari_operasional', 'label' => 'Hari Operasional'],
            ['perm' => 'akses_hari_libur', 'label' => 'Kalender Pendidikan'],
            ['perm' => 'akses_pengumuman', 'label' => 'Pengumuman'],
            ['perm' => 'akses_target_mengajar', 'label' => 'Target Mengajar (Plot Jadwal)'],
            ['perm' => 'akses_jadwal_harian', 'label' => 'Jadwal Harian'],
            ['perm' => 'akses_riwayat_mutasi', 'label' => 'Riwayat Mutasi Jadwal'],
        ],
        'Guru (Aplikasi Mobile)' => [
            ['perm' => 'akses_dashboard_guru', 'label' => 'Beranda Guru (Mobile)'],
            ['perm' => 'akses_jadwal_saya', 'label' => 'Jadwal Saya, Scan Hadir, Rekap, Kaldik & Profil'],
            ['perm' => 'akses_siswa_saya', 'label' => 'Siswa Saya (Wali Kelas)'],
        ],
        'Siswa' => [
            ['perm' => 'akses_penempatan_siswa', 'label' => 'Penempatan Siswa'],
            ['perm' => 'akses_absen_siswa', 'label' => 'Absensi Siswa'],
            ['perm' => 'akses_input_nilai', 'label' => 'Input Nilai'],
            ['perm' => 'akses_laporan_siswa', 'label' => 'Raport & Laporan Siswa'],
            ['perm' => 'akses_pembayaran', 'label' => 'Tagihan & Pembayaran'],
        ],
        'Pengaturan Sistem' => [
            ['perm' => 'akses_manajemen_user', 'label' => 'Setup User'],
            ['perm' => 'akses_manajemen_akses', 'label' => 'Hak Akses (Kunci Sistem)'],
            ['perm' => 'akses_import_excel', 'label' => 'Pusat Import (Excel)'],
            ['perm' => 'akses_backup_restore', 'label' => 'Manajemen Database'],
        ],
    ];

    public const IKON_GRUP = [
        'Beranda & Monitoring' => 'fa-th-large',
        'Master Data' => 'fa-database',
        'Jadwal & Kaldik' => 'fa-calendar-alt',
        'Guru (Aplikasi Mobile)' => 'fa-chalkboard-teacher',
        'Siswa' => 'fa-user-graduate',
        'Pengaturan Sistem' => 'fa-cog',
    ];

    public const WARNA_GRUP = [
        'Beranda & Monitoring' => 'bg-teal-50',
        'Master Data' => 'bg-blue-50',
        'Jadwal & Kaldik' => 'bg-indigo-50',
        'Guru (Aplikasi Mobile)' => 'bg-violet-50',
        'Siswa' => 'bg-amber-50',
        'Pengaturan Sistem' => 'bg-rose-50',
    ];

    public const WARNA_NAMA_GRUP = [
        'Beranda & Monitoring' => 'text-teal-700',
        'Master Data' => 'text-blue-700',
        'Jadwal & Kaldik' => 'text-indigo-700',
        'Guru (Aplikasi Mobile)' => 'text-violet-700',
        'Siswa' => 'text-amber-700',
        'Pengaturan Sistem' => 'text-rose-700',
    ];

    // ========================================================
    // HAK AKSES PER-USER (dipakai panel "Fasilitas Menu" di Setup User)
    // ========================================================
    /** AJAX: permission yang sedang dimiliki sebuah user. */
    public function getUserPermissions(User $user): JsonResponse
    {
        $this->proteksiTargetAdmin($user);

        return response()->json([
            'permissions' => $user->getPermissionNames()->values()->all(),
            'status' => $user->status,
            'locked' => $user->hasRole('Administrator'),
        ]);
    }

    /** Simpan akses manual per user (satu-satunya sumber: centang per-user). */
    public function simpanAkses(Request $request, User $user)
    {
        $this->proteksiTargetAdmin($user);

        $request->validate([
            'permissions' => 'nullable|array',
            'permissions.*' => 'string',
        ]);

        // Whitelist: hanya kunci terdaftar yang diterima.
        $terpilih = array_values(array_intersect(
            $request->input('permissions', []),
            self::PERMISSIONS
        ));

        // Anti self-lockout: jangan sampai admin (pemilik sesi) mencabut akses sistemnya sendiri.
        self::pastikanSistemAman($user, $terpilih);

        $user->syncPermissions($terpilih);

        return redirect()->back()->with('sukses', "Fasilitas menu '{$user->name}' berhasil disimpan dan langsung aktif.");
    }

    /** Hapus seluruh fasilitas menu user terpilih. */
    public function hapusSemuaFasilitas(User $user)
    {
        $this->proteksiTargetAdmin($user);

        // Anti self-lockout: pemilik sesi tidak boleh menghapus akses sistemnya sendiri.
        self::pastikanSistemAman($user, []);

        $user->syncPermissions([]);

        return redirect()->back()->with('sukses', 'Semua fasilitas ' . $user->name . ' berhasil dihapus. Akun tetap aktif.');
    }

    private function proteksiTargetAdmin(User $user): void
    {
        if ($user->hasRole('Administrator') && !auth()->user()->hasRole('Administrator')) {
            abort(403, 'Anda tidak berhak mengubah hak akses user Administrator.');
        }
    }
}