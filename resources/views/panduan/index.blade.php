@extends('layouts.app')

@section('title', 'Panduan Aplikasi')

@section('content')
<style>
    /* Styling khusus halaman panduan */
    .pg-section { scroll-margin-top: 20px; }
    .pg-toc a.active-pg { color: #00c0c7; font-weight: 700; }
    .pg-table th { background: #f3faf9; }
    .pg-code { background: #0f172a; color: #7dd3fc; padding: 3px 7px; border-radius: 6px; font-size: 12px; font-family: monospace; }
    .pg-badge { display: inline-block; padding: 2px 9px; border-radius: 9999px; font-size: 11px; font-weight: 700; }
    .pg-h2 { font-size: 1.35rem; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 10px; margin-bottom: 14px; }
    .pg-h2 i { color: #00c0c7; }
    .pg-h3 { font-size: 1.05rem; font-weight: 700; color: #00a8b8; margin: 18px 0 8px; }
    .pg-p { color: #475569; line-height: 1.75; margin-bottom: 10px; }
    .pg-card { background: #fff; border: 1px solid #eef2f1; border-radius: 14px; padding: 22px; }
    .pg-note { background: #ecfdf5; border-left: 4px solid #10b981; padding: 12px 16px; border-radius: 8px; font-size: 13px; color: #065f46; margin: 12px 0; }
    .pg-warn { background: #fff7ed; border-left: 4px solid #f97316; padding: 12px 16px; border-radius: 8px; font-size: 13px; color: #9a3412; margin: 12px 0; }
    .pg-callout { background: #eff6ff; border-left: 4px solid #3b82f6; padding: 12px 16px; border-radius: 8px; font-size: 13px; color: #1e40af; margin: 12px 0; }
    .pg-steps { counter-reset: step; list-style: none; padding-left: 0; }
    .pg-steps > li { position: relative; padding-left: 42px; margin-bottom: 12px; color: #475569; line-height: 1.7; }
    .pg-steps > li::before { counter-increment: step; content: counter(step); position: absolute; left: 0; top: 0; width: 30px; height: 30px; background: #00c0c7; color: #fff; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 13px; }
</style>

<div class="grid grid-cols-1 lg:grid-cols-4 gap-6">

    <!-- ===================== DAFTAR ISI (kiri, sticky) ===================== -->
    <div class="lg:col-span-1">
        <div class="pg-card lg:sticky lg:top-0">
            <div class="flex items-center gap-2 mb-4">
                <div class="h-10 w-10 rounded-xl bg-[#00c0c7]/15 text-[#00c0c7] flex items-center justify-center text-lg"><i class="fas fa-book-open"></i></div>
                <div>
                    <p class="font-black text-gray-800">Panduan Aplikasi</p>
                    <p class="text-[11px] text-gray-400 font-semibold">Khusus Administrator</p>
                </div>
            </div>
            <ul class="pg-toc text-[13px] font-semibold text-gray-500 space-y-2">
                <li><a class="hover:text-[#00c0c7] transition" href="#pengenalan">1. Pengenalan Aplikasi</a></li>
                <li><a class="hover:text-[#00c0c7] transition" href="#teknologi">2. Teknologi &amp; Infrastruktur</a></li>
                <li><a class="hover:text-[#00c0c7] transition" href="#hak-aske">3. Hak Akses &amp; Peran</a></li>
                <li><a class="hover:text-[#00c0c7] transition" href="#menu-utama">4. Menu Utama</a></li>
                <li><a class="hover:text-[#00c0c7] transition" href="#basis-data">5. Basis Data Master</a></li>
                <li><a class="hover:text-[#00c0c7] transition" href="#akademik">6. Akademik &amp; Jadwal</a></li>
                <li><a class="hover:text-[#00c0c7] transition" href="#riwayat">7. Riwayat Mutasi Jadwal</a></li>
                <li><a class="hover:text-[#00c0c7] transition" href="#setup">8. Setup &amp; Lainnya</a></li>
                <li><a class="hover:text-[#00c0c7] transition" href="#alur">9. Alur Logika Kebutuhan</a></li>
                <li><a class="hover:text-[#00c0c7] transition" href="#tips">10. Tip &amp; Pemecahan Masalah</a></li>
            </ul>
            <div class="mt-5 pt-4 border-t border-gray-100 text-[11px] text-gray-400 font-medium">
                <p>Ringkasan data saat ini</p>
                <div class="grid grid-cols-3 gap-2 mt-2 text-center">
                    <div class="bg-gray-50 rounded-lg py-2"><p class="font-black text-gray-800">{{ $data['jumlahPeriode'] }}</p><p class="text-[10px]">Periode</p></div>
                    <div class="bg-gray-50 rounded-lg py-2"><p class="font-black text-gray-800">{{ $data['jumlahGuru'] }}</p><p class="text-[10px]">Guru</p></div>
                    <div class="bg-gray-50 rounded-lg py-2"><p class="font-black text-gray-800">{{ $data['jumlahKelas'] }}</p><p class="text-[10px]">Kelas</p></div>
                    <div class="bg-gray-50 rounded-lg py-2"><p class="font-black text-gray-800">{{ $data['jumlahPelajaran'] }}</p><p class="text-[10px]">Pelajaran</p></div>
                    <div class="bg-gray-50 rounded-lg py-2"><p class="font-black text-gray-800">{{ $data['jumlahMutasi'] }}</p><p class="text-[10px]">Riwayat</p></div>
                    <div class="bg-gray-50 rounded-lg py-2"><p class="font-black text-gray-800">{{ $data['jumlahRole'] }}/{{ $data['jumlahPermission'] }}</p><p class="text-[10px]">Role/Perm</p></div>
                </div>
            </div>
        </div>
    </div>

    <!-- ===================== KONTEN (kanan) ===================== -->
    <div class="lg:col-span-3 space-y-6">

        <!-- ============ 1. PENGENALAN ============ -->
        <div id="pengenalan" class="pg-section pg-card">
            <h2 class="pg-h2"><i class="fas fa-rocket"></i> 1. Pengenalan Aplikasi</h2>
            <p class="pg-p">
                Aplikasi ini adalah <strong>Sistem Manajemen Pesantren / Sekolah</strong> (SmartPesantren – Muadalah Wustha / SPM Wustha). Sistem dirancang untuk mengelola operasional harian madrasah secara digital, mencakup penyusunan jadwal pelajaran, pencatatan absensi guru, penggantian guru (mutasi), koordinasi agenda dan kegiatan, hingga pembuatan laporan.
            </p>
            <p class="pg-p">Beberapa kebutuhan utama yang dijawab sistem ini:</p>
            <ol class="pg-steps">
                <li><strong>Penyusunan jadwal mengajar</strong> yang rapi per kelas dan per guru, dengan deteksi bentrok otomatis.</li>
                <li><strong>Absensi guru</strong> harian — bisa lewat pindai QR, meja kontrol, atau piket (inval) pengganti.</li>
                <li><strong>Mutasi / penggantian guru</strong> tanpa menghapus riwayat mengajar guru sebelumnya, lengkap dengan <strong>riwayat otomatis</strong>.</li>
                <li><strong>Laporan presensi</strong> per guru dengan rekap jumlah hari dan persentase, bisa dicetak PDF.</li>
                <li><strong>Pengelolaan hak akses</strong> berbasis peran, serta backup/restore database.</li>
            </ol>
            <div class="pg-note"><strong>Login default Administrator:</strong> username <span class="pg-code">admin</span>, password <span class="pg-code">password123</span>. Segera ganti password setelah login pertama.</div>
        </div>

        <!-- ============ 2. TEKNOLOGI ============ -->
        <div id="teknologi" class="pg-section pg-card">
            <h2 class="pg-h2"><i class="fas fa-cube"></i> 2. Teknologi &amp; Infrastruktur</h2>
            <p class="pg-p">Aplikasi dibangun di atas ekosistem berikut:</p>
            <table class="pg-table w-full text-sm text-left border-collapse">
                <thead><tr class="border-b border-gray-200"><th class="px-4 py-2">Komponen</th><th class="px-4 py-2">Keterangan</th></tr></thead>
                <tbody class="text-gray-600">
                    <tr class="border-b border-gray-100"><td class="px-4 py-2 font-bold text-gray-800">Framework</td><td class="px-4 py-2">Laravel (PHP) — struktur MVC</td></tr>
                    <tr class="border-b border-gray-100"><td class="px-4 py-2 font-bold text-gray-800">Database</td><td class="px-4 py-2">PostgreSQL <span class="pg-code">smart_tu</span></td></tr>
                    <tr class="border-b border-gray-100"><td class="px-4 py-2 font-bold text-gray-800">Hak Akses</td><td class="px-4 py-2">Spatie Permission (role &amp; permission)</td></tr>
                    <tr class="border-b border-gray-100"><td class="px-4 py-2 font-bold text-gray-800">Laporan PDF</td><td class="px-4 py-2">DomPDF / Barryvdh</td></tr>
                    <tr class="border-b border-gray-100"><td class="px-4 py-2 font-bold text-gray-800">Import Excel</td><td class="px-4 py-2">Maatwebsite Excel</td></tr>
                    <tr class="border-b border-gray-100"><td class="px-4 py-2 font-bold text-gray-800">Tampilan</td><td class="px-4 py-2">Tailwind CSS (Play CDN) + Font Poppins</td></tr>
                    <tr class="border-b border-gray-100"><td class="px-4 py-2 font-bold text-gray-800">PWA &amp; Notifikasi</td><td class="px-4 py-2">Service Worker + Push API (jika diaktifkan)</td></tr>
                    <tr><td class="px-4 py-2 font-bold text-gray-800">QR / Barcode</td><td class="px-4 py-2">Pabrik barcode + pemindai kehadiran</td></tr>
                </tbody>
            </table>
            <div class="pg-callout"><i class="fas fa-info-circle mr-1"></i> Tema visual memakai aksen warna <strong>teal</strong> <span class="pg-code">#00c0c7</span> dan gradien <span class="pg-code">from-[#00c0c7] to-[#00a8b8]</span>.</div>
        </div>

        <!-- ============ 3. HAK AKSES ============ -->
        <div id="hak-aske" class="pg-section pg-card">
            <h2 class="pg-h2"><i class="fas fa-user-shield"></i> 3. Hak Akses &amp; Peran</h2>
            <p class="pg-p">Sistem memakai dua lapis pengaman: <strong>Peran (Role)</strong> dan <strong>Kunci Akses (Permission)</strong>. Setiap menu hanya tampil jika user memiliki permission yang sesuai (direktif <code>@@can</code>).</p>
            <p class="pg-p"><strong>Peran yang tersedia:</strong> Administrator, Pimpinan, Tata Usaha, Kepanitiaan, Wali Kelas, Dewan Guru, Murid, Wali Murid.</p>

            <h3 class="pg-h3">Matriks Kunci Akses per Peran (sesuai PermissionSeeder)</h3>
            <table class="pg-table w-full text-sm text-left border-collapse">
                <thead><tr class="border-b border-gray-200"><th class="px-4 py-2">Peran</th><th class="px-4 py-2">Kunci Akses (permission)</th></tr></thead>
                <tbody class="text-gray-600">
                    @foreach($matriksRole as $role => $perms)
                    <tr class="border-b border-gray-100">
                        <td class="px-4 py-2 font-bold text-gray-800">{{ $role }}</td>
                        <td class="px-4 py-2">
                            @if(is_array($perms))
                                @foreach($perms as $p)<span class="pg-badge bg-gray-100 text-gray-600 mb-1 mr-1">{{ $p }}</span>@endforeach
                            @else
                                <span class="pg-badge bg-[#00c0c7]/15 text-[#0e9aa0]">{{ $perms }}</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <h3 class="pg-h3">Daftar Kunci Akses pada Aplikasi ({{ $data['jumlahPermission'] }})</h3>
            <div class="flex flex-wrap gap-2">
                @foreach($permissions as $p)<span class="pg-badge bg-gray-100 text-gray-600">{{ $p }}</span>@endforeach
            </div>

            <div class="pg-warn"><i class="fas fa-exclamation-triangle mr-1"></i> <strong>Gerbang pengalihan login:</strong> ketika pengguna masuk (<code>/</code>), sistem mengecek peran. Jika berperan <strong>Dewan Guru</strong>, langsung diarahkan ke <em>Beranda Guru</em> (versi mobile/HP). Selainnya diarahkan ke <em>Dashboard Utama</em>. Halaman panduan ini hanya dapat diakses <span class="pg-code">Administrator</span>.</div>
        </div>

        <!-- ============ 4. MENU UTAMA ============ -->
        <div id="menu-utama" class="pg-section pg-card">
            <h2 class="pg-h2"><i class="fas fa-th-large"></i> 4. Menu Utama</h2>
            <p class="pg-p">Grup pertama pada sidebar, berisi akses operasional harian.</p>

            <h3 class="pg-h3">Dashboard (kunci: akses_dashboard)</h3>
            <p class="pg-p">Beranda untuk Admin/TU. Menampilkan: jumlah jadwal hari ini, rekap kehadiran guru (Hadir / Izin / Alpha / Kosong), grafik kehadiran 7 hari terakhir, strip kalender minggu berjalan, daftar monitor guru yang berstatus <em>Izin/Kosong/Alpha</em>, serta kartu tugas &amp; link cepat (Periode Aktif, dsb).</p>

            <h3 class="pg-h3">Beranda Guru (kunci: akses_dashboard_guru)</h3>
            <p class="pg-p">Dashboard khusus guru (tampilan mobile-first). Ini tampilan awal ketika guru login. Berisi jadwal mengajar hari itu, target kurikulum, dan pintu cepat.</p>

            <h3 class="pg-h3">Meja Kontrol (kunci: akses_meja_kontrol)</h3>
            <p class="pg-p">Papan input absensi manual oleh petugas. Memilih slot (kelas/mapel/guru/jam) lalu menetapkan status: <span class="pg-badge bg-emerald-100 text-emerald-700">Hadir</span> <span class="pg-badge bg-amber-100 text-amber-700">Izin</span> <span class="pg-badge bg-rose-100 text-rose-700">Alpha</span> <span class="pg-badge bg-gray-100 text-gray-600">Kosong</span>. Data tersimpan ke <span class="pg-code">kehadiran_gurus</span> dan bisa dipantau realtime.</p>

            <h3 class="pg-h3">Rekap Laporan (kunci: akses_laporan)</h3>
            <p class="pg-p">Rekap presensi per guru dalam rentang tanggal: jumlah jam wajib, hitungan Hadir/Izin/Alpha/Kosong, piket (inval), realita mengajar, dan persentase realita. Dapat dicetak menjadi PDF (DomPDF). Data sumber dari <span class="pg-code">kehadiran_gurus</span> yang digabung dengan jadwal harian.</p>

            <h3 class="pg-h3">Jadwal Saya (kunci: akses_jadwal_saya)</h3>
            <p class="pg-p">Jadwal pribadi milik guru yang sedang login, beserta fasilitas absen hadir &amp; piket pengganti. Berhubungan dengan <em>Beranda Guru</em> mobile.</p>

            <h3 class="pg-h3">Cetak Barcode (kunci: akses_jadwal_saya)</h3>
            <p class="pg-p">Mencetak barcode/QR per kelas. Barcode ini ditempel sehingga saat jam pelajaran, guru/petugas memindainya untuk absensi kelas.</p>

            <h3 class="pg-h3">Scan Hadir (kunci: akses_jadwal_saya)</h3>
            <p class="pg-p">Pemindai QR: memindai barcode kelas → sistem mencocokkan dengan jadwal hari/jam tersebut (menghormati masa berlaku <code>berlaku_mulai/sampai</code>) → otomatis membuat baris <span class="pg-code">kehadiran_gurus</span> berstatus <strong>Hadir</strong>.</p>

            <h3 class="pg-h3">Agenda Kegiatan (akses umum yang login)</h3>
            <p class="pg-p">Kelola agenda/kegiatan sekolah. Mendukung: daftar kegiatan, buat QR kehadiran acara, mode proyektor (papan hadir realtime), check-in manual maupun pindai QR, serta laporan &amp; cetak PDF kehadiran kegiatan.</p>
        </div>

        <!-- ============ 5. BASIS DATA MASTER ============ -->
        <div id="basis-data" class="pg-section pg-card">
            <h2 class="pg-h2"><i class="fas fa-database"></i> 5. Basis Data Master</h2>
            <p class="pg-p">Data dasar yang menjadi fondasi seluruh modul. Sebaiknya diisi lengkap sebelum menyusun jadwal.</p>

            <h3 class="pg-h3">Master Guru (akses_master_guru)</h3>
            <p class="pg-p">Data tenaga pengajar: nama, NIG, status (Aktif/non-aktif), profil lengkap, jenis kelamin. Dihubungkan ke akun <strong>Dewan Guru</strong> untuk login guru (username = NIG, password default <span class="pg-code">123456</span>).</p>

            <h3 class="pg-h3">Master Pelajaran (akses_master_pelajaran)</h3>
            <p class="pg-p">Data mata pelajaran: kode, nama, nama kitab/referensi, status, tingkat (kitab_tingkat). Menjadi rujukan pemetaan ke kelas.</p>

            <h3 class="pg-h3">Master Kelas (akses_master_kelas)</h3>
            <p class="pg-p">Daftar kelas (contoh 7-A, 7-B, ... 9-B). Setiap kelas punya kapasitas jam yang dibatasi oleh <em>Hari Operasional</em>.</p>

            <h3 class="pg-h3">Batas Pelajaran / Target Kurikulum (akses_master_pelajaran)</h3>
            <p class="pg-p">Menentukan target kemajuan kurikulum per mapel per periode: <em>mulai_dari</em>, batas UTS/UAS ganjil &amp; genap, per tingkat. Dipakai guru untuk melihat target capaian pada <em>Beranda Guru</em>.</p>

            <h3 class="pg-h3">Pusat Import (akses_master_guru)</h3>
            <p class="pg-p">Import massal dari Excel: Kelas, Pelajaran, Guru, Plot Jadwal, Jadwal Harian. Berguna untuk migrasi data awal (templat — isi sesuai kolom yang diminta). <strong>Catatan:</strong> import plot/jadwal digunakan untuk pengisian massal; perubahan manual via menu tetap direkomendasikan untuk akurasi.</p>
        </div>

        <!-- ============ 6. AKADEMIK & JADWAL ============ -->
        <div id="akademik" class="pg-section pg-card">
            <h2 class="pg-h2"><i class="fas fa-calendar-alt"></i> 6. Akademik &amp; Jadwal</h2>
            <p class="pg-p">Inti penyusunan jadwal dan operasional akademik. Urutan ideal pengaturannya dijelaskan di bagian <a href="#alur" class="text-[#00c0c7] font-bold">Alur</a>.</p>

            <h3 class="pg-h3">Master Periode (akses_master_periode)</h3>
            <p class="pg-p">Mengelola tahun ajaran &amp; semester, termasuk <strong>set Periode Aktif</strong>. Hampir semua modul (jadwal, rekap, mutasi) memakai periode aktif ini sebagai konteks.</p>

            <h3 class="pg-h3">Kalender Pendidikan / Kaldik (akses_hari_libur)</h3>
            <p class="pg-p">Agenda tanggal penting: libur, UTS, UAS, kegiatan. Sistem mengecek kalender ini untuk menandai hari libur sehingga tidak dihitung sebagai ketidakhadiran.</p>

            <h3 class="pg-h3">Hari Operasional (akses_hari_operasional)</h3>
            <p class="pg-p">Menentukan hari apa saja yang aktif (mis. Senin–Sabtu) beserta <strong>maksimum jam</strong> per hari. Menjadi batas kapasitas kelas saat plotting.</p>

            <h3 class="pg-h3">Target Mengajar / Master Plot (akses_target_mengajar)</h3>
            <p class="pg-p">Halaman <span class="pg-code">/master-plot-jadwal</span>: mengalokasikan <strong>beban jam</strong> setiap mapel terhadap tiap kelas dan memilih <strong>guru pengajar</strong>. Dilengkapi tombol <em>Mutasi</em> untuk mengganti guru. Sistem otomatis mengecek kapasitas (overload) dan bentrok guru, lalu menyinkronkan guru ke jadwal harian terkait.</p>

            <h3 class="pg-h3">Jadwal Harian (akses_jadwal_harian)</h3>
            <p class="pg-p">Halaman <span class="pg-code">/master-jadwal-harian</span>: grid jadwal per kelas (baris = hari, kolom = jam). Fungsi utama:
                <ul class="list-disc ml-6 mt-2 space-y-1 text-gray-600">
                    <li><strong>Tambah/edit</strong> blok jadwal (kelas, hari, jam, mapel, guru).</li>
                    <li><strong>Drag-drop</strong> untuk memindahkan (move) atau <strong>tukar posisi (swap)</strong> blok antar slot.</li>
                    <li><strong>Deteksi bentrok</strong> (guru mengajar dua tempat di jam sama) dengan opsi konfirmasi/timpa.</li>
                    <li><strong>Mutasi per-slot</strong>: ganti guru pada satu slot dengan <strong>tanggal efektif</strong> (masa berlaku <code>berlaku_mulai/sampai</code>).</li>
                    <li><strong>Hapus slot</strong> (soft-delete, riwayat tetap aman).</li>
                </ul>
            </p>
        </div>

        <!-- ============ 7. RIWAYAT MUTASI ============ -->
        <div id="riwayat" class="pg-section pg-card">
            <h2 class="pg-h2"><i class="fas fa-history"></i> 7. Riwayat Mutasi Jadwal (fitur baru)</h2>
            <p class="pg-p">Halaman <span class="pg-code">/riwayat-mutasi</span> (kunci: <strong>akses_riwayat_mutasi</strong>) menampilkan <strong>catatan otomatis</strong> setiap perubahan jadwal. Tidak perlu input manual — sistem merekam sendiri dari 5 jalur perubahan:</p>

            <table class="pg-table w-full text-sm text-left border-collapse">
                <thead><tr class="border-b border-gray-200"><th class="px-4 py-2">Tipe</th><th class="px-4 py-2">Kapan tercatat</th><th class="px-4 py-2">Lokasi</th></tr></thead>
                <tbody class="text-gray-600">
                    <tr class="border-b border-gray-100"><td class="px-4 py-2"><span class="pg-badge bg-sky-100 text-sky-700">plot_sync</span></td><td class="px-4 py-2">Guru diubah di Target Mengajar</td><td class="px-4 py-2">PlotJadwalController@store</td></tr>
                    <tr class="border-b border-gray-100"><td class="px-4 py-2"><span class="pg-badge bg-indigo-100 text-indigo-700">ganti_guru</span></td><td class="px-4 py-2">Mutasi massal plot / mutasi per-slot</td><td class="px-4 py-2">PlotJadwalController &amp; JadwalHarianController</td></tr>
                    <tr class="border-b border-gray-100"><td class="px-4 py-2"><span class="pg-badge bg-amber-100 text-amber-700">tukar_jam</span></td><td class="px-4 py-2">Tukar posisi blok antar slot</td><td class="px-4 py-2">prosesDragDrop</td></tr>
                    <tr class="border-b border-gray-100"><td class="px-4 py-2"><span class="pg-badge bg-amber-100 text-amber-700">pindah_blok</span></td><td class="px-4 py-2">Memindahkan blok ke slot kosong</td><td class="px-4 py-2">prosesDragDrop</td></tr>
                    <tr><td class="px-4 py-2"><span class="pg-badge bg-rose-100 text-rose-700">hapus_slot</span></td><td class="px-4 py-2">Slot jadwal dikosongkan</td><td class="px-4 py-2">JadwalHarianController@destroy</td></tr>
                </tbody>
            </table>

            <p class="pg-p mt-4">Tiap catatan berisi: <strong>tanggal kejadian</strong>, <strong>tipe</strong>, mapel, kelas, hari/jam, <strong>guru lama → guru baru</strong>, tanggal efektif, keterangan, dan siapa yang melakukannya. Dilengkapi filter (tipe/kelas/guru/periode/pencarian) dan kartu statistik.</p>

            <h3 class="pg-h3">Kelola Tanggal Masa Berlaku</h3>
            <p class="pg-p">Sub-halaman <span class="pg-code">/riwayat-mutasi/kelola-tanggal</span>: mengatur/perbaiki <code>berlaku_mulai</code> &amp; <code>berlaku_sampai</code> tiap slot jadwal sesuai kebutuhan. Berguna mengoreksi tanggal efektif mutasi.</p>

            <h3 class="pg-h3">Backfill data lama</h3>
            <p class="pg-p">Karena tabel riwayat baru ada setelah sebelumnya berjalan, data perubahan yang terjadi <strong>sebelum</strong> modul bisa direkonstruksi menjalankan perintah: <span class="pg-code">php artisan mutasi:backfill</span>. Perintah ini membaca <span class="pg-code">jadwal_harians</span> (soft-deleted + masa berlaku) dan membuat catatan yang sesuai. Aman dijalankan ulang (tidak membuat duplikat).</p>
            <div class="pg-note"><i class="fas fa-check-circle mr-1"></i> Contoh nyata: kasus "Ny. Lailatul Badriyah menggantikan Ustd. Nabila di PPKN, kelas 9-B, Ahad jam 5-6" kini tercatat sebagai tipe <strong>ganti_guru</strong> berkat backfill ini.</div>
        </div>

        <!-- ============ 8. SETUP & LAINNYA ============ -->
        <div id="setup" class="pg-section pg-card">
            <h2 class="pg-h2"><i class="fas fa-cog"></i> 8. Setup &amp; Lainnya</h2>

            <h3 class="pg-h3">Setup User (akses_manajemen_user)</h3>
            <p class="pg-p">Membuat/edit akun login, menentukan peran (role), dan reset password. Akun guru biasanya dibuat otomatis dari data Master Guru (username = NIG).</p>

            <h3 class="pg-h3">Hak Akses (akses_manajemen_akses)</h3>
            <p class="pg-p">Matriks peran × kunci akses. Admin dapat menambah/menghapus permission dari tiap peran tanpa ubah kode.</p>

            <h3 class="pg-h3">Manajemen Database</h3>
            <p class="pg-p">Ekspor (backup) seluruh database ke file SQL dan impor (restore) dari file SQL. Sangat disarankan backup berkala sebelum perubahan besar.</p>
        </div>

        <!-- ============ 9. ALUR LOGIKA ============ -->
        <div id="alur" class="pg-section pg-card">
            <h2 class="pg-h2"><i class="fas fa-project-diagram"></i> 9. Alur Logika Kebutuhan</h2>

            <h3 class="pg-h3">A. Alur penyusunan jadwal (urutan yang benar)</h3>
            <ol class="pg-steps">
                <li><strong>Master Periode</strong> — tentukan tahun ajaran &amp; aktifkan periode.</li>
                <li><strong>Hari Operasional</strong> — tentukan hari aktif &amp; maksimal jam per hari.</li>
                <li><strong>Basis Data Master</strong> — pastikan Guru, Pelajaran, Kelas sudah terisi.</li>
                <li><strong>Target Mengajar (Master Plot)</strong> — alokasikan beban jam tiap mapel ke kelas dan tunjuk guru pengajar.</li>
                <li><strong>Jadwal Harian</strong> — susun posisi hari &amp; jam tiap blok via drag-drop; sistem mengecek bentrok guru.</li>
                <li><strong>Mutasi / tukar</strong> — jika guru berubah di tengah periode, lakukan mutasi (otomatis tercatat riwayat).</li>
            </ol>

            <h3 class="pg-h3">B. Alur absensi guru</h3>
            <ol class="pg-steps">
                <li>Guru hadir → <strong>scan QR barcode kelas</strong> (atau petugas lewat <strong>Meja Kontrol</strong> manual).</li>
                <li>Sistem mencocokkan dengan jadwal hari/jam &amp; masa berlaku → buat baris <span class="pg-code">kehadiran_gurus</span> status <strong>Hadir</strong>.</li>
                <li>Jika guru berhalangan → status <strong>Izin / Alpha / Kosong</strong>; bisa diisi <strong>piket pengganti</strong> (nig_pengganti / inval).</li>
                <li>Data ini direkap di <strong>Rekap Laporan</strong> dan bisa dicetak PDF.</li>
            </ol>

            <h3 class="pg-h3">C. Alur mutasi &amp; riwayat</h3>
            <ol class="pg-steps">
                <li>Guru A berhenti/tugaskan ulang → ganti guru di <strong>Target Mengajar</strong> (massal) atau <strong>mutasi per-slot</strong> (dengan tanggal efektif).</li>
                <li>Sistem bisa memakai mekanisme <strong>soft-delete</strong> (baris lama disembunyikan, riwayat aman) atau <strong>masa berlaku</strong> (berlaku_mulai/sampai).</li>
                <li>Setiap perubahan otomatis ditulis ke <span class="pg-code">mutasi_jadwals</span>.</li>
                <li>Untuk data lama (sebelum modul), jalankan <span class="pg-code">php artisan mutasi:backfill</span>.</li>
            </ol>

            <h3 class="pg-h3">D. Alur agenda &amp; kegiatan</h3>
            <p class="pg-p">Buat agenda → sistem generate QR → peserta check-in (scan QR / manual / proyektor realtime) → kehadiran kegiatan tercatat → laporan PDF.</p>

            <h3 class="pg-h3">E. Alur notifikasi push</h3>
            <p class="pg-p">Layanan pengecekan jadwal berjalan periodik (jika variabel lingkungan <span class="pg-code">NOTIFIKASI_AKTIF</span> diaktifkan). Guru yang berlangganan menerima pengingat jadwal mengajar. Pengaturan per guru tersedia di menu Notifikasi pada Beranda Guru.</p>

            <h3 class="pg-h3">F. Alur backup/restore</h3>
            <p class="pg-p">Manajemen Database → <strong>Ekspor</strong> menghasilkan file SQL lengkap. <strong>Impor</strong> memulihkan kondisi dari file tersebut. Jalankan berkala untuk keamanan data.</p>
        </div>

        <!-- ============ 10. TIPS ============ -->
        <div id="tips" class="pg-section pg-card">
            <h2 class="pg-h2"><i class="fas fa-lightbulb"></i> 10. Tip &amp; Pemecahan Masalah</h2>

            <h3 class="pg-h3">Import Excel vs input manual</h3>
            <p class="pg-p">Import berguna untuk data awal dalam jumlah besar. Untuk perubahan jadwal harian yang presisi (bentrok, mutasi, tukar jam), lakukan manual di <em>Jadwal Harian</em> / <em>Target Mengajar</em>.</p>

            <h3 class="pg-h3">Mengoreksi tanggal efektif</h3>
            <p class="pg-p">Gunakan <strong>Kelola Tanggal Masa Berlaku</strong> (dari halaman Riwayat Mutasi) untuk memperbaiki <code>berlaku_mulai</code>/<code>berlaku_sampai</code> — tercatat otomatis sebagai perbaikan.</p>

            <h3 class="pg-h3">Menjalankan backfill</h3>
            <p class="pg-p">Dari terminal proyek: <span class="pg-code">php artisan mutasi:backfill</span>. Aman dijalankan berulang kali.</p>

            <h3 class="pg-h3">Lokasi berkas penting</h3>
            <table class="pg-table w-full text-sm text-left border-collapse">
                <thead><tr class="border-b border-gray-200"><th class="px-4 py-2">Berfungsi</th><th class="px-4 py-2">Lokasi</th></tr></thead>
                <tbody class="text-gray-600">
                    <tr class="border-b border-gray-100"><td class="px-4 py-2 font-bold text-gray-800">Rute</td><td class="px-4 py-2"><span class="pg-code">routes/web.php</span></td></tr>
                    <tr class="border-b border-gray-100"><td class="px-4 py-2 font-bold text-gray-800">Kontrol Jadwal/Mutasi</td><td class="px-4 py-2"><span class="pg-code">app/Http/Controllers/JadwalHarianController.php</span>, <span class="pg-code">PlotJadwalController.php</span>, <span class="pg-code">RiwayatMutasiController.php</span></td></tr>
                    <tr class="border-b border-gray-100"><td class="px-4 py-2 font-bold text-gray-800">Log riwayat</td><td class="px-4 py-2"><span class="pg-code">app/Services/MutasiLogService.php</span> + model <span class="pg-code">MutasiJadwal.php</span></td></tr>
                    <tr class="border-b border-gray-100"><td class="px-4 py-2 font-bold text-gray-800">Layanan jadwal</td><td class="px-4 py-2"><span class="pg-code">app/Services/JadwalService.php</span></td></tr>
                    <tr class="border-b border-gray-100"><td class="px-4 py-2 font-bold text-gray-800">Tampilan default</td><td class="px-4 py-2"><span class="pg-code">resources/views/layouts/app.blade.php</span> (sidebar &amp; tema)</td></tr>
                    <tr class="border-b border-gray-100"><td class="px-4 py-2 font-bold text-gray-800">Hak akses</td><td class="px-4 py-2"><span class="pg-code">database/seeders/PermissionSeeder.php</span></td></tr>
                    <tr><td class="px-4 py-2 font-bold text-gray-800">Backfill</td><td class="px-4 py-2"><span class="pg-code">app/Console/Commands/BackfillMutasiJadwal.php</span></td></tr>
                </tbody>
            </table>

            <div class="pg-note"><i class="fas fa-info-circle mr-1"></i> Halaman ini sendiri hanya dapat dibuka oleh <strong>Administrator</strong> dan tidak mengubah data apa pun — murni referensi &amp; penjelasan.</div>
        </div>

    </div>
</div>
@endsection
