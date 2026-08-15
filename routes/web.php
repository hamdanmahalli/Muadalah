<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\JadwalController;
use App\Http\Controllers\GuruController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;

// JALUR TERBUKA (Bisa diakses tanpa login)
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'prosesLogin']);

// JALUR TERKUNCI (Hanya bisa diakses jika sudah login)
Route::middleware('auth')->group(function () {
    
    // Fitur Logout
    Route::post('/logout', [AuthController::class, 'logout']);

    // Fitur Ganti Password User Sendiri
    Route::put('/ganti-password', [AuthController::class, 'gantiPassword']);

    // Halaman Utama
    Route::get('/', [JadwalController::class, 'dashboard']);

    // Modul Jadwal & Kehadiran
    Route::get('/form-upload-jadwal', [JadwalController::class, 'index']);
    Route::post('/import-jadwal', [JadwalController::class, 'importExcel']);
    Route::get('/meja-kontrol', [JadwalController::class, 'mejaKontrol']);
    Route::post('/simpan-kehadiran', [\App\Http\Controllers\JadwalController::class, 'simpanKehadiran']);
    Route::get('/laporan', [JadwalController::class, 'laporanKehadiran']);
    Route::get('/laporan/cetak', [JadwalController::class, 'cetakPdf']);

    // Master Data
    Route::get('/master-guru', [GuruController::class, 'index']);
    Route::post('/master-guru', [GuruController::class, 'store']);
    Route::put('/master-guru/{id}', [GuruController::class, 'update']);
    Route::delete('/master-guru/{id}', [GuruController::class, 'destroy']);

    // Setup User
    Route::resource('setup-user', UserController::class)->except(['create', 'show', 'edit']);

    // Jalur khusus untuk fitur Reset Password ke 123456
    Route::put('/setup-user/{id}/reset-password', [UserController::class, 'resetPassword']);

    // Jalur untuk Master Mata Pelajaran
    Route::resource('master-pelajaran', \App\Http\Controllers\PelajaranController::class);

    // Jalur untuk Master Kelas
    Route::resource('master-kelas', \App\Http\Controllers\KelasController::class);

    // Jalur untuk Plotting Jadwal (Beban Mengajar)
    Route::get('master-plot-jadwal', [\App\Http\Controllers\PlotJadwalController::class, 'index']);
    Route::post('master-plot-jadwal', [\App\Http\Controllers\PlotJadwalController::class, 'store']);

    // Jalur untuk Master Jadwal Harian (Roster)
    Route::get('master-jadwal-harian', [\App\Http\Controllers\JadwalHarianController::class, 'index']);
    Route::post('master-jadwal-harian', [\App\Http\Controllers\JadwalHarianController::class, 'store']);
    Route::delete('master-jadwal-harian/{id}', [\App\Http\Controllers\JadwalHarianController::class, 'destroy']);

    Route::get('master-guru/export', [\App\Http\Controllers\GuruController::class, 'export']);
    Route::post('master-guru/import', [\App\Http\Controllers\GuruController::class, 'import']);
    Route::resource('master-guru', \App\Http\Controllers\GuruController::class);

    // Jalur Pusat Master Import Excel
    Route::get('master-import', [\App\Http\Controllers\MasterImportController::class, 'index']);
    Route::post('master-import/guru', [\App\Http\Controllers\MasterImportController::class, 'importGuru']);
    Route::post('master-import/pelajaran', [\App\Http\Controllers\MasterImportController::class, 'importPelajaran']);
    Route::post('master-import/kelas', [\App\Http\Controllers\MasterImportController::class, 'importKelas']);
    Route::post('master-import/plot-jadwal', [\App\Http\Controllers\MasterImportController::class, 'importPlotJadwal']);
    Route::post('master-import/jadwal-harian', [\App\Http\Controllers\MasterImportController::class, 'importJadwalHarian']);

});