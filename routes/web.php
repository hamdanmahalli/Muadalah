<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\JadwalController;
use App\Http\Controllers\GuruController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PeriodeController;
use App\Http\Controllers\HariLiburController;
use App\Http\Controllers\PelajaranController;
use App\Http\Controllers\KelasController;
use App\Http\Controllers\PlotJadwalController;
use App\Http\Controllers\JadwalHarianController;
use App\Http\Controllers\HariOperasionalController;
use App\Http\Controllers\RolePermissionController;

// ==========================================================
// JALUR TERBUKA (Bisa diakses tanpa login)
// ==========================================================
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'prosesLogin']);

// ==========================================================
// PINTU GERBANG UTAMA (Harus Login)
// ==========================================================
Route::middleware('auth')->group(function () {
    
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::put('/ganti-password', [AuthController::class, 'gantiPassword']);
    
    // Dashboard (Bisa diakses jika punya kunci akses_dashboard)
    Route::get('/', [JadwalController::class, 'dashboard'])->middleware('can:akses_dashboard');

    // ==========================================================
    // ZONA OPERASIONAL & LAPORAN
    // ==========================================================
    Route::middleware(['can:akses_meja_kontrol'])->group(function () {
        Route::get('/meja-kontrol', [JadwalController::class, 'mejaKontrol']);
        Route::post('/simpan-kehadiran', [JadwalController::class, 'simpanKehadiran']);
        Route::get('/cek-kehadiran-terbaru', [JadwalController::class, 'cekKehadiranTerbaru']);
    });

    // Pabrik Barcode
    Route::get('/pabrik-barcode', [\App\Http\Controllers\BarcodeController::class, 'index']);
    Route::get('/pabrik-barcode/cetak/{kelas_id}', [\App\Http\Controllers\BarcodeController::class, 'cetak']);

    Route::middleware(['can:akses_laporan'])->group(function () {
        Route::get('/laporan', [JadwalController::class, 'laporanKehadiran']);
        Route::get('/laporan/cetak', [JadwalController::class, 'cetakPdf']);
        Route::get('/laporan/riwayat-guru', [JadwalController::class, 'riwayatGuruAjax']);
    });

    // ==========================================================
    // ZONA MASTER DATA DASAR
    // ==========================================================
    Route::resource('master-guru', GuruController::class)->middleware('can:akses_master_guru');
    Route::resource('master-pelajaran', PelajaranController::class)->middleware('can:akses_master_pelajaran');
    Route::resource('master-kelas', KelasController::class)->middleware('can:akses_master_kelas');
    
    // ==========================================================
    // ZONA AKADEMIK & JADWAL
    // ==========================================================
    Route::resource('hari-libur', HariLiburController::class)->middleware('can:akses_hari_libur');
    
    Route::middleware(['can:akses_target_mengajar'])->group(function () {
        Route::get('/master-plot-jadwal', [PlotJadwalController::class, 'index']);
        Route::post('/master-plot-jadwal', [PlotJadwalController::class, 'store']);
    });

    Route::middleware(['can:akses_jadwal_harian'])->group(function () {
        Route::get('/master-jadwal-harian', [JadwalHarianController::class, 'index']);
        Route::post('/master-jadwal-harian', [JadwalHarianController::class, 'store']);
        Route::delete('/master-jadwal-harian/{id}', [JadwalHarianController::class, 'destroy']);
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

    // ==========================================================
    // ZONA SETUP PENGGUNA & HAK AKSES
    // ==========================================================
    Route::middleware(['can:akses_manajemen_user'])->group(function () {
        Route::resource('setup-user', UserController::class);
        Route::put('/setup-user/{id}/reset-password', [UserController::class, 'resetPassword']);
    });

    Route::middleware(['can:akses_manajemen_akses'])->group(function () {
        Route::get('/manajemen-akses', [RolePermissionController::class, 'index']);
        Route::put('/manajemen-akses', [RolePermissionController::class, 'update']);
    });

    // ==========================================================
    // ZONA KHUSUS GURU
    // ==========================================================
    Route::middleware(['can:akses_jadwal_saya'])->group(function () {
        Route::get('/jadwal-saya', [JadwalController::class, 'jadwalSaya']);

        // Fitur Scanner Kelas
        Route::get('/scan-kelas', [\App\Http\Controllers\ScanController::class, 'index']);
        Route::post('/scan-proses', [\App\Http\Controllers\ScanController::class, 'proses']);
    });
        
});