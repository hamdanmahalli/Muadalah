<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\JadwalController;
use App\Http\Controllers\GuruController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PeriodeController;
use App\Http\Controllers\PelajaranController;
use App\Http\Controllers\KelasController;
use App\Http\Controllers\PlotJadwalController;
use App\Http\Controllers\JadwalHarianController;
use App\Http\Controllers\HariOperasionalController;
use App\Http\Controllers\RolePermissionController;
use App\Http\Controllers\BarcodeController;
use App\Http\Controllers\ScanController;
use App\Http\Controllers\AgendaKaldikController;
use App\Http\Controllers\MasterImportController;
use App\Http\Controllers\BatasPelajaranController;
use App\Http\Controllers\AgendaKegiatanController;
use App\Http\Controllers\DatabaseManagerController;
use App\Http\Controllers\NotifikasiController;
use App\Http\Controllers\MonitoringKehadiranController;
use App\Http\Controllers\PengumumanController;


// ==========================================================
// 1. ZONA PUBLIK (Bisa diakses tanpa login)
// ==========================================================
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'prosesLogin']);

// ==========================================================
// 2. BENTENG UTAMA (Seluruh rute di dalam ini WAJIB LOGIN)
// ==========================================================
Route::middleware(['auth'])->group(function () {
        
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::put('/ganti-password', [AuthController::class, 'gantiPassword']);

    // GERBANG PENGALIHAN CERDAS (Titik Masuk Pertama)
    Route::get('/dashboard-utama', [JadwalController::class, 'dashboard'])->name('dashboard.utama');

    Route::get('/', function (\Illuminate\Http\Request $request) {
        $user = auth()->user();

        // Jika memiliki jabatan Guru / Dewan Guru, lempar ke Dashboard HP
        if ($user->hasAnyRole(['Dewan Guru', 'Guru'])) {
            return redirect('/dashboard-guru');
        }

        // Jika bukan Guru (Admin/TU), lempar ke Dashboard Utama secara aman
        return redirect('/dashboard-utama');
    })->name('home');


    // ----------------------------------------------------------
    // ZONA OPERASIONAL & LAPORAN (Admin & TU)
    // ----------------------------------------------------------
    Route::middleware(['can:akses_meja_kontrol'])->group(function () {
        Route::get('/meja-kontrol', [JadwalController::class, 'mejaKontrol']);
        Route::post('/simpan-kehadiran', [JadwalController::class, 'simpanKehadiran']);
        Route::get('/cek-kehadiran-terbaru', [JadwalController::class, 'cekKehadiranTerbaru']);
    });

    // Monitoring / Valdasi Kehadiran Guru (Admin & TU)
    Route::middleware(['can:akses_meja_kontrol'])->group(function () {
        Route::get('/monitoring-kehadiran', [MonitoringKehadiranController::class, 'index']);
        Route::get('/monitoring-kehadiran/detail-guru', [MonitoringKehadiranController::class, 'detailGuru']);
        Route::post('/monitoring-kehadiran/simpan', [MonitoringKehadiranController::class, 'update']);
    });

    // Rute Kelola Agenda & QR Code
    Route::get('/agenda-kegiatan', [AgendaKegiatanController::class, 'index']);
    Route::post('/agenda-kegiatan', [AgendaKegiatanController::class, 'store']);
    Route::get('/agenda-kegiatan/{id}/proyektor', [AgendaKegiatanController::class, 'proyektor']);
    Route::get('/agenda-kegiatan/{id}/laporan', [AgendaKegiatanController::class, 'laporan']);
    Route::post('/agenda-kegiatan/{id}/manual', [AgendaKegiatanController::class, 'hadirManual']);
    Route::get('/agenda-kegiatan/{id}/scan-qr', [AgendaKegiatanController::class, 'scanQR']);
    Route::post('/agenda-kegiatan/{id}/scan-proses-guru', [AgendaKegiatanController::class, 'prosesScanQR']);
    Route::get('/agenda-kegiatan/{id}/pdf', [AgendaKegiatanController::class, 'cetakPdf']);
    Route::get('/api/agenda-kegiatan/{id}/realtime', [AgendaKegiatanController::class, 'getKehadiranRealtime']);
    
    // Pabrik Barcode (Masuk ke dalam benteng Auth agar aman)
    Route::get('/pabrik-barcode', [BarcodeController::class, 'index']);
    Route::get('/pabrik-barcode/cetak/{kelas_id}', [BarcodeController::class, 'cetak']);

    Route::middleware(['can:akses_laporan'])->group(function () {
        Route::get('/laporan', [JadwalController::class, 'laporanKehadiran']);
        Route::get('/laporan/cetak', [JadwalController::class, 'cetakPdf']);
        Route::get('/laporan/riwayat-guru', [JadwalController::class, 'riwayatGuruAjax']);
    });


    // ----------------------------------------------------------
    // ZONA MASTER DATA DASAR
    // ----------------------------------------------------------
    Route::resource('master-guru', GuruController::class)->middleware('can:akses_master_guru');
    Route::resource('master-pelajaran', PelajaranController::class)->middleware('can:akses_master_pelajaran');
    Route::resource('master-kelas', KelasController::class)->middleware('can:akses_master_kelas');

    // Rute Batas Pelajaran / Kurikulum
    Route::get('/batas-pelajaran', [BatasPelajaranController::class, 'index']);
    Route::post('/batas-pelajaran', [BatasPelajaranController::class, 'store']);
    
    // ----------------------------------------------------------
    // ZONA PUSAT IMPORT DATA (EXCEL)
    // ----------------------------------------------------------
    // Untuk keamanan, rute ini kita lindungi dengan hak akses admin data master
    Route::middleware(['can:akses_master_guru'])->group(function () {
        Route::get('/master-import', [MasterImportController::class, 'index'])->name('master.import');
        Route::post('/master-import/kelas', [MasterImportController::class, 'importKelas']);
        Route::post('/master-import/pelajaran', [MasterImportController::class, 'importPelajaran']);
        Route::post('/master-import/guru', [MasterImportController::class, 'importGuru']);
        Route::post('/master-import/plot-jadwal', [MasterImportController::class, 'importPlotJadwal']);
        Route::post('/master-import/jadwal-harian', [MasterImportController::class, 'importJadwalHarian']);
    });

    // ----------------------------------------------------------
    // ZONA AKADEMIK & JADWAL
    // ----------------------------------------------------------
    Route::resource('agenda-kaldik', AgendaKaldikController::class)->middleware('can:akses_hari_libur');
    Route::resource('pengumuman', PengumumanController::class)->middleware('can:akses_pengumuman');

    Route::middleware(['can:akses_target_mengajar'])->group(function () {
        Route::get('/master-plot-jadwal', [PlotJadwalController::class, 'index']);
        Route::post('/master-plot-jadwal', [PlotJadwalController::class, 'store']);
    });

    Route::middleware(['can:akses_jadwal_harian'])->group(function () {
        Route::get('/master-jadwal-harian', [JadwalHarianController::class, 'index']);
        Route::post('/master-jadwal-harian', [JadwalHarianController::class, 'store']);
        Route::delete('/master-jadwal-harian/{id}', [JadwalHarianController::class, 'destroy']);
        Route::post('/master-jadwal-harian/drag-drop', [JadwalHarianController::class, 'prosesDragDrop']);
        Route::get('/plot-jadwal/{id}/mutasi', [PlotJadwalController::class, 'formMutasi']);
        Route::post('/plot-jadwal/{id}/mutasi', [PlotJadwalController::class, 'mutasiGuru']);
        Route::get('/master-jadwal-harian/{id}/mutasi', [JadwalHarianController::class, 'formMutasi']);
        Route::post('/master-jadwal-harian/{id}/mutasi', [JadwalHarianController::class, 'mutasiGuru']);
    });

    Route::middleware(['can:akses_hari_operasional'])->group(function () {
        Route::get('/master-hari-operasional', [HariOperasionalController::class, 'index']);
        Route::post('/master-hari-operasional', [HariOperasionalController::class, 'store']);
    });

    Route::middleware(['can:akses_master_periode'])->group(function () {
        Route::get('/master-periode', [PeriodeController::class, 'index']);
        Route::post('/master-periode', [PeriodeController::class, 'store']);
        Route::put('/master-periode/{id}', [PeriodeController::class, 'update']);
        Route::post('/master-periode/set-aktif/{id}', [PeriodeController::class, 'setAktif']);
        Route::delete('/master-periode/{id}', [PeriodeController::class, 'destroy']);
    });


    // ----------------------------------------------------------
    // ZONA SETUP PENGGUNA & HAK AKSES
    // ----------------------------------------------------------
    Route::middleware(['can:akses_manajemen_user'])->group(function () {
        Route::resource('setup-user', UserController::class);
        Route::put('/setup-user/{id}/reset-password', [UserController::class, 'resetPassword']);
    });

    Route::middleware(['can:akses_manajemen_akses'])->group(function () {
        Route::get('/manajemen-akses', [RolePermissionController::class, 'index']);
        Route::put('/manajemen-akses', [RolePermissionController::class, 'update']);
    });

    // Halaman Panduan & Penjelasan Aplikasi (khusus Administrator)
    Route::middleware(['role:Administrator'])->group(function () {
        Route::get('/panduan-aplikasi', [\App\Http\Controllers\PanduanController::class, 'index']);
    });

    // Halaman Riwayat Mutasi & Kelola Tanggal Masa Berlaku Jadwal
    Route::middleware(['can:akses_riwayat_mutasi'])->group(function () {
        Route::get('/riwayat-mutasi', [\App\Http\Controllers\RiwayatMutasiController::class, 'index']);
        Route::get('/riwayat-mutasi/kelola-tanggal', [\App\Http\Controllers\RiwayatMutasiController::class, 'kelolaTanggal']);
        Route::post('/riwayat-mutasi/kelola-tanggal', [\App\Http\Controllers\RiwayatMutasiController::class, 'simpanTanggal']);
    });

    Route::get('/backup-restore', [DatabaseManagerController::class, 'index']);
    Route::post('/backup-restore/export', [DatabaseManagerController::class, 'exportSql']);
    Route::post('/backup-restore/import', [DatabaseManagerController::class, 'importSql']);


    // ----------------------------------------------------------
    // ZONA KHUSUS GURU (Aplikasi Mobile)
    // ----------------------------------------------------------
    Route::middleware(['can:akses_dashboard_guru'])->group(function () {
        Route::get('/dashboard-guru', [JadwalController::class, 'dashboardGuru']);
    });

    

    // Rute API untuk pop-up target kurikulum di Beranda Guru
    Route::get('/api/target-kurikulum', [BatasPelajaranController::class, 'getTargetKurikulum']);

    Route::middleware(['can:akses_jadwal_saya'])->group(function () {
        Route::get('/jadwal-saya', [JadwalController::class, 'jadwalSaya']);
        
        Route::get('/scan-kelas', [ScanController::class, 'index']);
        Route::post('/scan-proses', [ScanController::class, 'proses']);
        // TAMBAHAN RUTE BARU UNTUK PROSES PIKET:
        Route::post('/scan-piket', [ScanController::class, 'prosesPiket']);
        
        Route::get('/rekap-presensi', [JadwalController::class, 'rekapPresensiPribadi']);
        Route::get('/kaldik', [JadwalController::class, 'kaldikGuru']);
        // Halaman Profil Guru
        Route::get('/profil-guru', [JadwalController::class, 'profilLengkap'])->name('guru.profil');
        // Rute untuk Menu Sistem Guru
        Route::get('/menu', [JadwalController::class, 'menu'])->name('guru.menu');
        // Halaman Profil Lengkap & Edit Biodata Guru
        Route::get('/profil', [JadwalController::class, 'profilLengkap'])->name('guru.profil.lengkap');
        Route::put('/profil/update', [JadwalController::class, 'updateProfil'])->name('guru.profil.update');

        // Notifikasi
        Route::get('/notifikasi/pengaturan', [NotifikasiController::class, 'pengaturan'])->name('guru.notifikasi');
        Route::post('/notifikasi/simpan', [NotifikasiController::class, 'simpan']);
        Route::post('/notifikasi/subscribe', [NotifikasiController::class, 'subscribe']);
        Route::post('/notifikasi/unsubscribe', [NotifikasiController::class, 'unsubscribe']);
        Route::post('/notifikasi/test', [NotifikasiController::class, 'test']);
        Route::post('/notifikasi/test-pulse', [NotifikasiController::class, 'testPulse']);
    });

}); // <--- PENUTUP BENTENG UTAMA (auth)

// Telemetri push dari Service Worker (pulse) — di luar auth & CSRF (hanya mencatat log)
Route::post('/notifikasi/pulse', [\App\Http\Controllers\NotifikasiController::class, 'pulse']);
