<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\JadwalController;
use App\Http\Controllers\GuruController;
use App\Http\Controllers\JabatanController;
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
use App\Http\Controllers\SiswaController;
use App\Http\Controllers\AngkatanSiswaController;
use App\Http\Controllers\TagihanController;
use App\Http\Controllers\PembayaranController;
use App\Http\Controllers\NilaiController;
use App\Http\Controllers\KehadiranSiswaController;
use App\Http\Controllers\RaportController;
use App\Http\Controllers\SiswaLaporanController;
use App\Http\Controllers\SiswaSayaController;


// ==========================================================
// 1. ZONA PUBLIK (Bisa diakses tanpa login)
// ==========================================================
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'prosesLogin'])->middleware('throttle:5,1');

// Keputusan konflik "satu perangkat": tetap di perangkat lama / pindah ke sini.
Route::post('/login/keputusan-device', [AuthController::class, 'keputusanDevice'])
    ->name('login.keputusan')
    ->middleware('throttle:10,1');

// Deteksi sesi yang dipindah ke perangkat lain (untuk polling di perangkat lama).
Route::get('/saya/status-sesi', [AuthController::class, 'statusSesi'])->middleware('throttle:60,30');

// Intip Jadwal Hari Ini (khusus guru, tanpa login)
Route::get('/login/intip-jadwal', [AuthController::class, 'intipJadwal'])->middleware('throttle:30,1');

// Login 1-klik MODE DEMO (hanya aktif bila APP_DEMO=true) — masuk langsung ke role demo.
Route::post('/login/demo/{role}', [AuthController::class, 'masukDemo'])
    ->name('login.demo')
    ->middleware(['demo', 'throttle:20,1']);

// ==========================================================
// 2. BENTENG UTAMA (Seluruh rute di dalam ini WAJIB LOGIN)
// ==========================================================
Route::middleware(['auth', \App\Http\Middleware\MenegakkanSatuDevice::class])->group(function () {
        
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::put('/ganti-password', [AuthController::class, 'gantiPassword']);
    Route::post('/preferensi-tema', [AuthController::class, 'simpanTema']);

    // GERBANG PENGALIHAN CERDAS (Titik Masuk Pertama)
    Route::get('/dashboard-utama', [JadwalController::class, 'dashboard'])->name('dashboard.utama');

    Route::get('/', function (\Illuminate\Http\Request $request) {
        $user = auth()->user();

// Jika pegangan akses Dashboard Guru (mobile), arahkan ke Dashboard HP.
        // Otorisasi kini berbasis permission langsung per user, bukan role.
        if ($user->can('akses_dashboard_guru')) {
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
    Route::middleware(['can:akses_monitoring_kehadiran'])->group(function () {
        Route::get('/monitoring-kehadiran', [MonitoringKehadiranController::class, 'index']);
        Route::get('/monitoring-kehadiran/detail-guru', [MonitoringKehadiranController::class, 'detailGuru']);
        Route::post('/monitoring-kehadiran/simpan', [MonitoringKehadiranController::class, 'update']);
    });

    // Rute Kelola Agenda & QR Code (khusus yang berhak mengelola agenda)
    Route::middleware(['can:akses_agenda'])->group(function () {
        Route::get('/agenda-kegiatan', [AgendaKegiatanController::class, 'index']);
        Route::post('/agenda-kegiatan', [AgendaKegiatanController::class, 'store']);
        Route::get('/agenda-kegiatan/{id}/proyektor', [AgendaKegiatanController::class, 'proyektor']);
        Route::get('/agenda-kegiatan/{id}/laporan', [AgendaKegiatanController::class, 'laporan']);
        Route::post('/agenda-kegiatan/{id}/manual', [AgendaKegiatanController::class, 'hadirManual']);
        Route::get('/agenda-kegiatan/{id}/scan-qr', [AgendaKegiatanController::class, 'scanQR']);
        Route::post('/agenda-kegiatan/{id}/scan-proses-guru', [AgendaKegiatanController::class, 'prosesScanQR']);
        Route::get('/agenda-kegiatan/{id}/pdf', [AgendaKegiatanController::class, 'cetakPdf']);
        Route::get('/api/agenda-kegiatan/{id}/realtime', [AgendaKegiatanController::class, 'getKehadiranRealtime']);
        Route::delete('/agenda-kegiatan/{id}', [AgendaKegiatanController::class, 'destroy']);
    });
    
// Pabrik Barcode (QR presensi guru per kelas) — kunci khusus terpisah dari payung mobile guru
    Route::middleware(['can:akses_pabrik_barcode'])->group(function () {
        Route::get('/pabrik-barcode', [BarcodeController::class, 'index']);
        Route::get('/pabrik-barcode/cetak/{kelas_id}', [BarcodeController::class, 'cetak']);
    });

    Route::middleware(['can:akses_laporan'])->group(function () {
        Route::get('/laporan', [JadwalController::class, 'laporanKehadiran']);
        Route::get('/laporan/cetak', [JadwalController::class, 'cetakPdf']);
        Route::get('/laporan/riwayat-guru', [JadwalController::class, 'riwayatGuruAjax']);
    });


    // ----------------------------------------------------------
    // ZONA MASTER DATA DASAR
    // ----------------------------------------------------------
    Route::resource('master-guru', GuruController::class)->middleware('can:akses_master_guru');
    // Kelengkapan data guru (data dasar + kelengkapan + dokumen).
    // Izin dilihat di dalam controller: akses_master_guru || Administrator/Pimpinan.
    Route::get('/master-guru/{id}/kelengkapan', [GuruController::class, 'kelengkapan']);
    Route::post('/master-guru/{id}/kelengkapan/toggle', [GuruController::class, 'toggleEditKelengkapan']);
    Route::post('/master-guru/{id}/kelengkapan', [GuruController::class, 'simpanKelengkapan']);
    Route::post('/master-guru/{id}/dokumen', [GuruController::class, 'uploadDokumen']);
    Route::post('/master-guru-dokumen/{id}/hapus', [GuruController::class, 'hapusDokumen']);
    Route::get('/master-guru/{id}/detail', [GuruController::class, 'detail'])->middleware('can:akses_master_guru');
    // Buat akun login dari data guru (jabatan selain Guru yang tak dibuat otomatis)
    Route::post('/master-guru/{id}/buat-akun', [GuruController::class, 'buatAkun'])->middleware('can:akses_master_guru');
    Route::resource('master-jabatan', JabatanController::class)->only(['index', 'store', 'update', 'destroy'])->middleware('can:akses_master_jabatan');
    Route::resource('master-pelajaran', PelajaranController::class)->middleware('can:akses_master_pelajaran');
    Route::resource('master-kelas', KelasController::class)->middleware('can:akses_master_kelas');

    // ----------------------------------------------------------
    // ZONA MODUL SISWA (Data Murid)
    // ----------------------------------------------------------
    Route::middleware(['can:akses_master_siswa'])->group(function () {
        Route::resource('master-siswa', SiswaController::class);
        Route::get('/master-siswa/{id}/lengkapi', [SiswaController::class, 'lengkapi'])->name('siswa.lengkapi');
        Route::put('/master-siswa/{id}/lengkapi', [SiswaController::class, 'simpanLengkapi'])->name('siswa.simpanLengkapi');
        Route::post('/master-siswa/import', [SiswaController::class, 'import'])->name('master-siswa.import');
    });

    // Penempatan siswa ke kelas per periode (multi tahun ajaran)
    Route::middleware(['can:akses_penempatan_siswa'])->group(function () {
        Route::get('/penempatan-siswa', [AngkatanSiswaController::class, 'index'])->name('penempatan-siswa.index');
        Route::post('/penempatan-siswa', [AngkatanSiswaController::class, 'store'])->name('penempatan-siswa.store');
        Route::post('/penempatan-siswa/auto', [AngkatanSiswaController::class, 'autoPlace'])->name('penempatan-siswa.auto');
        Route::patch('/penempatan-siswa/{id}/nomor-absen', [AngkatanSiswaController::class, 'updateNomorAbsen'])->name('penempatan-siswa.updateNomorAbsen');
        Route::delete('/penempatan-siswa/{id}', [AngkatanSiswaController::class, 'destroy'])->name('penempatan-siswa.destroy');
    });

    // Pembayaran / Tagihan
    Route::middleware(['can:akses_pembayaran'])->group(function () {
        Route::get('/tagihan', [TagihanController::class, 'index'])->name('tagihan.index');
        Route::post('/tagihan/jenis', [TagihanController::class, 'storeJenis'])->name('tagihan.jenis.store');
        Route::put('/tagihan/jenis/{id}', [TagihanController::class, 'updateJenis'])->name('tagihan.jenis.update');
        Route::delete('/tagihan/jenis/{id}', [TagihanController::class, 'destroyJenis'])->name('tagihan.jenis.destroy');
        Route::get('/tagihan/buat', [TagihanController::class, 'buat'])->name('tagihan.buat');
        Route::post('/tagihan/buat', [TagihanController::class, 'store'])->name('tagihan.store');
        Route::get('/tagihan/{id}/pembayaran', [TagihanController::class, 'detail'])->name('tagihan.detail');
        Route::post('/tagihan/{id}/pembayaran', [PembayaranController::class, 'store'])->name('tagihan.bayar');
        Route::delete('/tagihan/{id}/bayar/{bayar}', [PembayaranController::class, 'destroy'])->name('tagihan.hapusBayar');
    });

    // Nilai siswa
    Route::middleware(['can:akses_input_nilai'])->group(function () {
        Route::get('/input-nilai', [NilaiController::class, 'index'])->name('nilai.index');
        Route::post('/input-nilai', [NilaiController::class, 'store'])->name('nilai.store');
        Route::get('/input-nilai/import-template', [NilaiController::class, 'downloadTemplate']);
        Route::post('/input-nilai/simpan-massal', [NilaiController::class, 'simpanMassal'])->name('nilai.simpanMassal');
        Route::post('/input-nilai/kolom', [NilaiController::class, 'updateKolom'])->name('nilai.updateKolom');
    });

    // Absensi siswa (mingguan)
    Route::middleware(['can:akses_absen_siswa'])->group(function () {
        Route::get('/absen-siswa', [KehadiranSiswaController::class, 'index'])->name('absen-siswa.index');
        Route::post('/absen-siswa', [KehadiranSiswaController::class, 'store'])->name('absen-siswa.store');
        Route::get('/absen-siswa/cetak', [KehadiranSiswaController::class, 'cetak'])->name('absen-siswa.cetak');
        Route::post('/absen-siswa/import', [KehadiranSiswaController::class, 'importAbsen'])->name('absen-siswa.import');
        Route::get('/absen-siswa/import/template', [KehadiranSiswaController::class, 'templateAbsen'])->name('absen-siswa.import-template');
    });

    // Raport
    Route::middleware(['can:akses_laporan_siswa'])->group(function () {
        Route::get('/raport', [RaportController::class, 'index'])->name('raport.index');
        Route::get('/raport/cetak/{siswa}', [RaportController::class, 'cetak'])->name('raport.cetak');
    });

    // Laporan siswa (buku induk, rekap pembayaran)
    Route::middleware(['can:akses_laporan_siswa'])->group(function () {
        Route::get('/laporan-siswa', [SiswaLaporanController::class, 'index'])->name('laporan-siswa.index');
        Route::get('/laporan-siswa/buku-induk', [SiswaLaporanController::class, 'bukuInduk'])->name('laporan-siswa.buku-induk');
        Route::get('/laporan-siswa/buku-induk/{siswa}', [SiswaLaporanController::class, 'bukuIndukSiswa'])->name('laporan-siswa.buku-induk-siswa');
        Route::get('/laporan-siswa/rekap-pembayaran', [SiswaLaporanController::class, 'rekapPembayaran'])->name('laporan-siswa.rekap-pembayaran');
    });

    // Menu Wali Kelas: Siswa Saya (hanya melihat siswa kelasnya)
    Route::middleware(['can:akses_siswa_saya'])->group(function () {
        Route::get('/siswa-saya', [SiswaSayaController::class, 'index'])->name('siswa-saya.index');
        Route::get('/siswa-saya/{siswa}', [SiswaSayaController::class, 'detail'])->name('siswa-saya.detail');
    });



    // Rute Batas Pelajaran / Kurikulum
    Route::get('/batas-pelajaran', [BatasPelajaranController::class, 'index'])->middleware('can:akses_batas_pelajaran');
    Route::post('/batas-pelajaran', [BatasPelajaranController::class, 'store'])->middleware('can:akses_batas_pelajaran');
    
    // ----------------------------------------------------------
    // ZONA PUSAT IMPORT DATA (EXCEL)
    // ----------------------------------------------------------
// Untuk keamanan, rute ini dilindungi hak akses akses_import_excel (diatur via Hak Akses)
    Route::middleware(['can:akses_import_excel'])->group(function () {
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
    // ZONA HONOR GURU (Admin) — akses pecah per sub-modul:
    // akses_honor            = melihat beranda, rekap, & mengunduh slip bisyaroh
    // akses_honor_konfigurasi = mengatur tarif konfigurasi
    // akses_honor_proses      = hitung, edit nominal draft
    // akses_honor_final       = finalisasi / buka kembali (HANYA Administrator)
    // akses_honor_scan        = scan penerimaan honor (Staf Bendahara)
    Route::middleware(['can:akses_honor'])->group(function () {
        Route::get('/honor', [\App\Http\Controllers\HonorController::class, 'index'])->name('honor.index');
        Route::get('/honor/rekap/{id}', [\App\Http\Controllers\HonorController::class, 'rekap'])->name('honor.rekap');
        Route::get('/honor/slip/{periode}', [\App\Http\Controllers\HonorController::class, 'cetakSlip'])->name('honor.slip');
    });

    Route::middleware(['can:akses_honor_konfigurasi'])->group(function () {
        Route::get('/honor/konfigurasi', [\App\Http\Controllers\HonorController::class, 'konfigurasi'])->name('honor.konfigurasi');
        Route::post('/honor/konfigurasi', [\App\Http\Controllers\HonorController::class, 'simpanKonfigurasi'])->name('honor.konfigurasi.simpan');
        Route::post('/honor/konfigurasi/salin', [\App\Http\Controllers\HonorController::class, 'salinKonfigurasi'])->name('honor.konfigurasi.salin');
    });

    Route::middleware(['can:akses_honor_proses'])->group(function () {
        Route::post('/honor/hitung', [\App\Http\Controllers\HonorController::class, 'hitung'])->name('honor.hitung');
        Route::post('/honor/detail/{id}', [\App\Http\Controllers\HonorController::class, 'updateDetail'])->name('honor.detail.update');
    });

    Route::middleware(['can:akses_honor_final'])->group(function () {
        Route::post('/honor/final/{id}', [\App\Http\Controllers\HonorController::class, 'finalisasi'])->name('honor.final');
        Route::post('/honor/buka/{id}', [\App\Http\Controllers\HonorController::class, 'buka'])->name('honor.buka');
    });

    Route::middleware(['can:akses_honor_scan'])->group(function () {
        Route::get('/honor/scan-penerimaan', [\App\Http\Controllers\HonorController::class, 'scanPenerimaan'])->name('honor.scan');
        Route::post('/honor/proses-scan', [\App\Http\Controllers\HonorController::class, 'prosesScan'])->name('honor.proses-scan');
    });


// ----------------------------------------------------------
    // ZONA KEBENDAHARAAN (Bendahara)
    // akses_kebendaharaan          = beranda modul & pinjaman
    // akses_rekap_kebendaharaan    = halaman rekap/neraca + PDF
    // ----------------------------------------------------------
    Route::middleware(['can:akses_kebendaharaan'])->group(function () {
        Route::get('/kebendaharaan', [\App\Http\Controllers\KebendaharaanController::class, 'index'])->name('kebendaharaan.index');

        // Pinjaman dana
        Route::get('/kebendaharaan/pinjaman', [\App\Http\Controllers\PinjamanController::class, 'index'])->name('kebendaharaan.pinjaman.index');
        Route::get('/kebendaharaan/pinjaman/buat', [\App\Http\Controllers\PinjamanController::class, 'create'])->name('kebendaharaan.pinjaman.create');
        Route::post('/kebendaharaan/pinjaman', [\App\Http\Controllers\PinjamanController::class, 'store'])->name('kebendaharaan.pinjaman.store');
        Route::post('/kebendaharaan/pinjaman/{id}/lunasi', [\App\Http\Controllers\PinjamanController::class, 'lunasi'])->name('kebendaharaan.pinjaman.lunasi');
        Route::post('/kebendaharaan/pinjaman/{id}/aktifkan', [\App\Http\Controllers\PinjamanController::class, 'aktifkan'])->name('kebendaharaan.pinjaman.aktifkan');
        Route::delete('/kebendaharaan/pinjaman/{id}', [\App\Http\Controllers\PinjamanController::class, 'destroy'])->name('kebendaharaan.pinjaman.destroy');
    });

    Route::middleware(['can:akses_rekap_kebendaharaan'])->group(function () {
        Route::get('/kebendaharaan/rekap', [\App\Http\Controllers\KebendaharaanController::class, 'rekap'])->name('kebendaharaan.rekap');
        Route::get('/kebendaharaan/rekap/pdf', [\App\Http\Controllers\KebendaharaanController::class, 'rekapPdf'])->name('kebendaharaan.rekap.pdf');
    });

    // Anggaran (RAB)
    Route::middleware(['can:akses_anggaran'])->group(function () {
        Route::get('/kebendaharaan/anggaran', [\App\Http\Controllers\AnggaranController::class, 'index'])->name('kebendaharaan.anggaran.index');
        Route::get('/kebendaharaan/anggaran/buat', [\App\Http\Controllers\AnggaranController::class, 'create'])->name('kebendaharaan.anggaran.create');
        Route::post('/kebendaharaan/anggaran', [\App\Http\Controllers\AnggaranController::class, 'store'])->name('kebendaharaan.anggaran.store');
        Route::post('/kebendaharaan/anggaran/import', [\App\Http\Controllers\AnggaranController::class, 'import'])->name('kebendaharaan.anggaran.import');
        Route::get('/kebendaharaan/anggaran/{id}', [\App\Http\Controllers\AnggaranController::class, 'show'])->name('kebendaharaan.anggaran.show');
        Route::get('/kebendaharaan/anggaran/{id}/pdf', [\App\Http\Controllers\AnggaranController::class, 'pdf'])->name('kebendaharaan.anggaran.pdf');

        Route::post('/kebendaharaan/anggaran/{id}/kelompok', [\App\Http\Controllers\AnggaranController::class, 'storeKelompok'])->name('kebendaharaan.anggaran.kelompok.store');
        Route::delete('/kebendaharaan/anggaran/kelompok/{id}', [\App\Http\Controllers\AnggaranController::class, 'destroyKelompok'])->name('kebendaharaan.anggaran.kelompok.destroy');

        Route::post('/kebendaharaan/anggaran/{id}/pos', [\App\Http\Controllers\AnggaranController::class, 'storePos'])->name('kebendaharaan.anggaran.pos.store');
        Route::put('/kebendaharaan/anggaran/pos/{id}', [\App\Http\Controllers\AnggaranController::class, 'updatePos'])->name('kebendaharaan.anggaran.pos.update');
        Route::put('/kebendaharaan/anggaran/pos/{id}/detail', [\App\Http\Controllers\AnggaranController::class, 'updateDetail'])->name('kebendaharaan.anggaran.pos.update-detail');
        Route::put('/kebendaharaan/anggaran/pos/{id}/alokasi-bulan', [\App\Http\Controllers\AnggaranController::class, 'updateAlokasiBulan'])->name('kebendaharaan.anggaran.pos.alokasi-bulan');
        Route::delete('/kebendaharaan/anggaran/pos/{id}', [\App\Http\Controllers\AnggaranController::class, 'destroyPos'])->name('kebendaharaan.anggaran.pos.destroy');

        Route::post('/kebendaharaan/anggaran/{id}/pemasukan', [\App\Http\Controllers\AnggaranController::class, 'storePemasukanRen'])->name('kebendaharaan.anggaran.pemasukan.store');
        Route::delete('/kebendaharaan/anggaran/pemasukan/{id}', [\App\Http\Controllers\AnggaranController::class, 'destroyPemasukanRen'])->name('kebendaharaan.anggaran.pemasukan.destroy');

        Route::post('/kebendaharaan/anggaran/{id}/final', [\App\Http\Controllers\AnggaranController::class, 'finalisasi'])->name('kebendaharaan.anggaran.final');
        Route::post('/kebendaharaan/anggaran/{id}/buka', [\App\Http\Controllers\AnggaranController::class, 'buka'])->name('kebendaharaan.anggaran.buka');
    });

    // Pencairan (SPP)
    Route::middleware(['can:akses_pencairan'])->group(function () {
        Route::get('/kebendaharaan/pencairan', [\App\Http\Controllers\PencairanController::class, 'index'])->name('kebendaharaan.pencairan.index');
        Route::post('/kebendaharaan/pencairan', [\App\Http\Controllers\PencairanController::class, 'store'])->name('kebendaharaan.pencairan.store');
    });

    Route::middleware(['can:akses_validasi_pencairan'])->group(function () {
        Route::post('/kebendaharaan/pencairan/{id}/bayar', [\App\Http\Controllers\PencairanController::class, 'bayar'])->name('kebendaharaan.pencairan.bayar');
        Route::post('/kebendaharaan/pencairan/{id}/tolak', [\App\Http\Controllers\PencairanController::class, 'tolak'])->name('kebendaharaan.pencairan.tolak');
    });

    // Laporan Pertanggung Jawaban (LPJ)
    Route::middleware(['can:akses_laporan_kebendaharaan'])->group(function () {
        Route::get('/kebendaharaan/laporan', [\App\Http\Controllers\LaporanPengeluaranController::class, 'index'])->name('kebendaharaan.laporan.index');
        Route::get('/kebendaharaan/laporan/buat', [\App\Http\Controllers\LaporanPengeluaranController::class, 'create'])->name('kebendaharaan.laporan.create');
        Route::post('/kebendaharaan/laporan', [\App\Http\Controllers\LaporanPengeluaranController::class, 'store'])->name('kebendaharaan.laporan.store');
    });

    Route::middleware(['can:akses_validasi_laporan'])->group(function () {
        Route::post('/kebendaharaan/laporan/{id}/validasi', [\App\Http\Controllers\LaporanPengeluaranController::class, 'validasi'])->name('kebendaharaan.laporan.validasi');
        Route::post('/kebendaharaan/laporan/{id}/tolak', [\App\Http\Controllers\LaporanPengeluaranController::class, 'tolak'])->name('kebendaharaan.laporan.tolak');
    });

    // Pemasukan dana
    Route::middleware(['can:akses_pemasukan'])->group(function () {
        Route::get('/kebendaharaan/pemasukan', [\App\Http\Controllers\PemasukanController::class, 'index'])->name('kebendaharaan.pemasukan.index');
        Route::get('/kebendaharaan/pemasukan/buat', [\App\Http\Controllers\PemasukanController::class, 'create'])->name('kebendaharaan.pemasukan.create');
        Route::post('/kebendaharaan/pemasukan', [\App\Http\Controllers\PemasukanController::class, 'store'])->name('kebendaharaan.pemasukan.store');
        Route::delete('/kebendaharaan/pemasukan/{id}', [\App\Http\Controllers\PemasukanController::class, 'destroy'])->name('kebendaharaan.pemasukan.destroy');
    });

    // ----------------------------------------------------------
    // ZONA TOKO BUKU
    // ----------------------------------------------------------
    Route::middleware(['can:akses_toko_buku'])->group(function () {
        Route::get('/kebendaharaan/toko', [\App\Http\Controllers\TokoController::class, 'index'])->name('kebendaharaan.toko.index');

        // Master barang & pembelian (hanya pengelola stok)
        Route::middleware(['can:akses_toko_buku_kelola'])->group(function () {
            Route::get('/kebendaharaan/toko/barang', [\App\Http\Controllers\TokoController::class, 'barangIndex'])->name('kebendaharaan.toko.barang.index');
            Route::post('/kebendaharaan/toko/barang', [\App\Http\Controllers\TokoController::class, 'barangStore'])->name('kebendaharaan.toko.barang.store');
            Route::put('/kebendaharaan/toko/barang/{id}', [\App\Http\Controllers\TokoController::class, 'barangUpdate'])->name('kebendaharaan.toko.barang.update');
            Route::post('/kebendaharaan/toko/barang/{id}/toggle', [\App\Http\Controllers\TokoController::class, 'barangToggle'])->name('kebendaharaan.toko.barang.toggle');
            Route::delete('/kebendaharaan/toko/barang/{id}', [\App\Http\Controllers\TokoController::class, 'barangDestroy'])->name('kebendaharaan.toko.barang.destroy');

            Route::get('/kebendaharaan/toko/pembelian', [\App\Http\Controllers\TokoController::class, 'pembelianIndex'])->name('kebendaharaan.toko.pembelian.index');
            Route::get('/kebendaharaan/toko/pembelian/buat', [\App\Http\Controllers\TokoController::class, 'pembelianCreate'])->name('kebendaharaan.toko.pembelian.create');
            Route::post('/kebendaharaan/toko/pembelian', [\App\Http\Controllers\TokoController::class, 'pembelianStore'])->name('kebendaharaan.toko.pembelian.store');
            Route::get('/kebendaharaan/toko/pembelian/{id}', [\App\Http\Controllers\TokoController::class, 'pembelianShow'])->name('kebendaharaan.toko.pembelian.show');
        });

        // Distribusi stok ke wali kelas
        Route::get('/kebendaharaan/toko/distribusi', [\App\Http\Controllers\TokoController::class, 'distribusiIndex'])->name('kebendaharaan.toko.distribusi.index');

        Route::middleware(['can:akses_toko_buku_distribusi'])->group(function () {
            Route::get('/kebendaharaan/toko/distribusi/buat', [\App\Http\Controllers\TokoController::class, 'distribusiCreate'])->name('kebendaharaan.toko.distribusi.create');
            Route::post('/kebendaharaan/toko/distribusi', [\App\Http\Controllers\TokoController::class, 'distribusiStore'])->name('kebendaharaan.toko.distribusi.store');
            Route::delete('/kebendaharaan/toko/distribusi/{id}', [\App\Http\Controllers\TokoController::class, 'distribusiDestroy'])->name('kebendaharaan.toko.distribusi.destroy');
        });

        // Detail distribusi: didaftarkan SETELAH /buat agar tidak tertelan sebagai {id}="buat"
        Route::get('/kebendaharaan/toko/distribusi/{id}', [\App\Http\Controllers\TokoController::class, 'distribusiShow'])->name('kebendaharaan.toko.distribusi.show');

        // Penjualan per murid (wali kelas mencatat)
        Route::middleware(['can:akses_toko_buku_penjualan'])->group(function () {
            Route::post('/kebendaharaan/toko/penjualan', [\App\Http\Controllers\TokoController::class, 'penjualanStore'])->name('kebendaharaan.toko.penjualan.store');
            Route::post('/kebendaharaan/toko/penjualan/{id}/lunas', [\App\Http\Controllers\TokoController::class, 'penjualanLunas'])->name('kebendaharaan.toko.penjualan.lunas');
        });

        // Setoran wali kelas
        Route::middleware(['can:akses_toko_buku_setoran'])->group(function () {
            Route::post('/kebendaharaan/toko/setoran', [\App\Http\Controllers\TokoController::class, 'setoranStore'])->name('kebendaharaan.toko.setoran.store');
            Route::delete('/kebendaharaan/toko/setoran/{id}', [\App\Http\Controllers\TokoController::class, 'setoranDestroy'])->name('kebendaharaan.toko.setoran.destroy');
        });
    });

    // ----------------------------------------------------------
    // ZONA SETUP PENGGUNA & HAK AKSES
    // ----------------------------------------------------------
    Route::middleware(['can:akses_manajemen_user'])->group(function () {
        Route::get('setup-user', [UserController::class, 'index'])->name('setup-user.index');
        Route::put('/setup-user/{id}/reset-password', [UserController::class, 'resetPassword'])->name('setup-user.reset-password');

        // HANYA Administrator: tambah/edit/hapus user & atur fasilitas menu.
        // Pemegang "akses_manajemen_user" lainnya hanya boleh lihat daftar & reset sandi.
        Route::middleware(['role:Administrator'])->group(function () {
            Route::post('setup-user', [UserController::class, 'store'])->name('setup-user.store');
            Route::put('setup-user/{user}', [UserController::class, 'update'])->name('setup-user.update');
            Route::delete('setup-user/{user}', [UserController::class, 'destroy'])->name('setup-user.destroy');

            // Hak akses per-user (popup "Fasilitas Menu" di halaman Setup User)
            Route::get('/setup-user/akses/{user}', [RolePermissionController::class, 'getUserPermissions'])->name('setup-user.akses');
            Route::put('/setup-user/akses/{user}', [RolePermissionController::class, 'simpanAkses'])->name('setup-user.akses.simpan');
            Route::put('/setup-user/akses/{user}/hapus-semua', [RolePermissionController::class, 'hapusSemuaFasilitas'])->name('setup-user.akses.hapus-semua');
        });
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
        Route::delete('/riwayat-mutasi/{id}', [\App\Http\Controllers\RiwayatMutasiController::class, 'destroy']);
    });

    // Backup dan Restore (total & destruktif) — dikunci akses_backup_restore via Hak Akses
    Route::middleware(['can:akses_backup_restore'])->group(function () {
        Route::get('/backup-restore', [DatabaseManagerController::class, 'index']);
        Route::post('/backup-restore/export', [DatabaseManagerController::class, 'exportSql']);
        Route::post('/backup-restore/import', [DatabaseManagerController::class, 'importSql']);
    });


    // ----------------------------------------------------------
    // ZONA KHUSUS GURU (Aplikasi Mobile)
    // ----------------------------------------------------------
    Route::middleware(['can:akses_dashboard_guru'])->group(function () {
        Route::get('/dashboard-guru', [JadwalController::class, 'dashboardGuru']);
    });

    

// Rute API untuk pop-up target kurikulum di Beranda Guru
    Route::get('/api/target-kurikulum', [BatasPelajaranController::class, 'getTargetKurikulum'])->middleware('can:akses_batas_pelajaran');

    Route::middleware(['can:akses_jadwal_saya'])->group(function () {
        Route::get('/jadwal-saya', [JadwalController::class, 'jadwalSaya']);
        
        Route::get('/scan-kelas', [ScanController::class, 'index']);
        Route::post('/scan-proses', [ScanController::class, 'proses']);
        // TAMBAHAN RUTE BARU UNTUK PROSES PIKET:
        Route::post('/scan-piket', [ScanController::class, 'prosesPiket']);
        
        // Rute Honor Guru
        Route::get('/guru/honor', [\App\Http\Controllers\GuruHonorController::class, 'index'])->name('guru.honor');
        Route::post('/guru/honor/scan', [\App\Http\Controllers\GuruHonorController::class, 'scan'])->name('guru.honor.scan');
        Route::get('/guru/honor/status', [\App\Http\Controllers\GuruHonorController::class, 'status'])->name('guru.honor.status');

        Route::get('/rekap-presensi', [JadwalController::class, 'rekapPresensiPribadi']);
        Route::get('/kaldik', [JadwalController::class, 'kaldikGuru']);
        // Halaman Profil Guru
        Route::get('/profil-guru', [JadwalController::class, 'profilLengkap'])->name('guru.profil');
        // Rute untuk Menu Sistem Guru
        Route::get('/menu', [JadwalController::class, 'menu'])->name('guru.menu');
// Halaman Profil Lengkap & Edit Biodata Guru
        Route::get('/profil', [JadwalController::class, 'profilLengkap'])->name('guru.profil.lengkap');
        Route::put('/profil/update', [JadwalController::class, 'updateProfil'])->name('guru.profil.update');
        // Unggah / hapus dokumen kelengkapan dari halaman Profil (guru sendiri)
        Route::post('/profil/dokumen', [GuruController::class, 'uploadDokumenProfil'])->name('guru.profil.dokumen');
        Route::post('/profil/dokumen/{id}/hapus', [GuruController::class, 'hapusDokumen'])->name('guru.profil.dokumen.hapus');

        // Notifikasi
        Route::get('/notifikasi/pengaturan', [NotifikasiController::class, 'pengaturan'])->name('guru.notifikasi');
        Route::post('/notifikasi/simpan', [NotifikasiController::class, 'simpan']);
        Route::post('/notifikasi/subscribe', [NotifikasiController::class, 'subscribe']);
        Route::post('/notifikasi/unsubscribe', [NotifikasiController::class, 'unsubscribe']);
        Route::post('/notifikasi/test', [NotifikasiController::class, 'test']);
        Route::post('/notifikasi/test-pulse', [NotifikasiController::class, 'testPulse']);
        // Telemetri push dari Service Worker (wajib login; CSRF tetap dikecualikan di bootstrap)
        Route::post('/notifikasi/pulse', [\App\Http\Controllers\NotifikasiController::class, 'pulse']);
    });

}); // <--- PENUTUP BENTENG UTAMA (auth)

