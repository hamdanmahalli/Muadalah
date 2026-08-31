<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}"> 
    <title>@yield('title', 'SmartPesantren')</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- Turbo Drive: navigasi AJAX anti-kedip antar halaman (scope di halaman guru via data-turbo) -->
    <script src="https://cdn.jsdelivr.net/npm/@hotwired/turbo@8/dist/turbo.es2017-umd.js" data-turbo-eval="false"></script>
    
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }

        /* NAV-CLICK LOADING OVERLAY */
        #nav-loading {
            position: fixed; top: 130px; bottom: 80px; left: 0; right: 0; z-index: 15;
            background: #f8fafc;
            display: none; align-items: center; justify-content: center;
            flex-direction: column; gap: 12px;
        }
        #nav-loading.show { display: flex; }
        #nav-loading .dots { display: flex; gap: 8px; }
        #nav-loading .dot {
            width: 12px; height: 12px; border-radius: 50%;
            background: #10b981;
            box-shadow: 0 0 12px 3px rgba(16,185,129,0.4);
            animation: navDotBounce 1.2s ease-in-out infinite;
        }
        #nav-loading .dot:nth-child(2) { animation-delay: 0.15s; }
        #nav-loading .dot:nth-child(3) { animation-delay: 0.3s; }
        #nav-loading .nav-load-text {
            font-size: 11px; font-weight: 700; color: #94a3b8;
            letter-spacing: 2px; text-transform: uppercase;
        }
        @keyframes navDotBounce {
            0%, 80%, 100% { transform: scale(0.4); opacity: 0.3; }
            40% { transform: scale(1.1); opacity: 1; }
        }

        /* OFFLINE NOTIF: kartu "Mode Ofline" di atas layar, tema emerald, bisa diusap ke atas */
        #offline-banner {
            position: fixed; top: -150px; left: 50%; transform: translateX(-50%);
            z-index: 60;
            display: flex; align-items: center; gap: 14px;
            width: min(92%, 340px);
            padding: 13px 18px;
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(16, 185, 129, 0.18);
            box-shadow: 0 12px 32px rgba(255, 255, 255, 0.9), 0 0 0 1px rgba(255, 255, 255, 0.6);
            pointer-events: auto;
            opacity: 0;
            transition: top 0.4s cubic-bezier(0.22, 1, 0.36, 1), opacity 0.3s ease;
            cursor: grab;
            touch-action: pan-y;
        }
        #offline-banner.show {
            top: 16px; opacity: 1;
        }
        #offline-banner.gone {
            top: -150px; opacity: 0;
        }
        #offline-banner .offline-ikon {
            width: 44px; height: 44px; flex-shrink: 0;
            border-radius: 14px;
            background: rgba(16, 185, 129, 0.12);
            display: flex; align-items: center; justify-content: center;
            color: #059669;
            font-size: 20px;
        }
        #offline-banner .offline-teks {
            line-height: 1.2; min-width: 0;
        }
        #offline-banner .offline-judul {
            margin: 0; font-size: 16px; font-weight: 800; color: #047857;
        }
        #offline-banner .offline-sub {
            margin: 2px 0 0; font-size: 12px; font-weight: 600; color: #10b981;
        }

        /* NOTIFIKASI REUSABLE: kartu atas, auto-hilang, tema emerald */
        #notif-stack {
            position: fixed; top: 16px; left: 50%; transform: translateX(-50%);
            z-index: 210; width: min(92%, 360px);
            display: flex; flex-direction: column; gap: 8px;
            pointer-events: none;
        }
        .notif-item {
            display: flex; align-items: center; gap: 12px;
            padding: 12px 16px;
            border-radius: 16px;
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            box-shadow: 0 12px 28px rgba(0, 0, 0, 0.12);
            animation: notif-in 0.3s ease both;
            pointer-events: auto;
        }
        .notif-item.hide { animation: notif-out 0.3s ease both; }
        .notif-item .notif-ikon {
            width: 36px; height: 36px; flex-shrink: 0; border-radius: 12px;
            display: flex; align-items: center; justify-content: center; font-size: 16px;
        }
        .notif-item .notif-teks { line-height: 1.2; min-width: 0; }
        .notif-item .notif-judul { margin: 0; font-size: 14px; font-weight: 800; color: #0f172a; }
        .notif-item .notif-sub { margin: 2px 0 0; font-size: 11px; font-weight: 600; color: #64748b; }
        /* Varian warna */
        .notif-item.success .notif-ikon { background: rgba(16,185,129,0.14); color: #059669; }
        .notif-item.error .notif-ikon { background: rgba(244,63,94,0.14); color: #e11d48; }
        .notif-item.info .notif-ikon { background: rgba(99,102,241,0.14); color: #4f46e5; }
        .notif-item.success { border: 1px solid rgba(16,185,129,0.2); }
        .notif-item.error { border: 1px solid rgba(244,63,94,0.2); }
        .notif-item.info { border: 1px solid rgba(99,102,241,0.2); }
        @keyframes notif-in { from { opacity: 0; transform: translateY(-12px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes notif-out { to { opacity: 0; transform: translateY(-14px); } }

        /* ============ SIDEBAR COLLAPSE (md+): icons-only -> hover melebar ============
           Ikon (brand & menu) TIDAK bergeser saat menciut/melebar — selalu di posisi kiri. */
        .sb-sidebar { width: 16rem; }
        @media (min-width: 768px) {
            .sb-sidebar { width: 70px; }
            .sb-sidebar:hover { width: 270px; } 

            .sb-sidebar .sb-text,
            .sb-sidebar .sb-glabel,
            .sb-sidebar .sb-chev,
            .sb-sidebar .sb-brand-txt,
            .sb-sidebar .sb-footer-txt,
            .sb-sidebar .sb-ta-txt,
            .sb-sidebar .sb-search { width: 0; min-width: 0; visibility: hidden; opacity: 0; overflow: hidden; white-space: nowrap; }
            
            .sb-sidebar:hover .sb-text,
            .sb-sidebar:hover .sb-glabel,
            .sb-sidebar:hover .sb-chev,
            .sb-sidebar:hover .sb-brand-txt,
            .sb-sidebar:hover .sb-footer-txt,
            .sb-sidebar:hover .sb-ta-txt { visibility: visible; opacity: 1; }
            .sb-sidebar:hover .sb-search { visibility: visible; opacity: 1; width: 8.5rem; }
            
            /* --- KODE YANG DIREVISI: Kembalikan lebar otomatis untuk logo dan footer --- */
            .sb-sidebar:hover .sb-brand-txt,
            .sb-sidebar:hover .sb-footer-txt,
            .sb-sidebar:hover .sb-ta-txt,
            .sb-sidebar:hover .sb-text,
            .sb-sidebar:hover .sb-glabel { 
                width: auto; 
            }

            /* --- Paksa sub menu tertutup secara visual saat menciut --- */
            .sb-sidebar:not(:hover) .sb-sub { display: none !important; }

            /* --- Menghapus blok hijau pada grup menu saat menciut --- */
            .sb-sidebar:not(:hover) .sb-group:has(.sb-active) .sb-group-toggle {
                background: transparent !important; 
                box-shadow: none !important; 
            }

            /* --- Hanya jadikan ikonnya saja yang warna hijau saat menciut --- */
            .sb-sidebar:not(:hover) .sb-group:has(.sb-active) .sb-group-toggle .sb-gicon i {
                color: #10b981 !important; 
            }
        
        }

        /* ============ SPACING & TIPOGRAFI MENU ============ */
        .sb-item { font-weight: 500; color: #9ca3af; }
        .sb-item.sb-active { font-weight: 700; color: #fff; }
        .sb-sidebar:hover .sb-item,
        .sb-sidebar:hover .sb-group-toggle { margin-bottom: 2px; }
        .sb-sidebar:hover .sb-item { margin: 1px 0; }
        .sb-sidebar:hover .sb-sub .sb-item { margin: 2px 0; }

        /* ============ ITEM MENU & SUB MENU ============ */
        .sb-group-toggle, .sb-item { width: 95%; border-radius: 0 9999px 9999px 0; } 

        /* PENGATURAN SUB-MENU (Lebih kecil dari menu utama) */
        .sb-item {
            display: flex; align-items: center;
            padding: 9px 24px 9px 12px; /* Atas-bawah diubah jadi 9px agar lebih pipih */
            font-weight: 500;
            color: #9ca3af;
            transition: background 0.2s ease, color 0.2s ease, box-shadow 0.2s ease;
        }
        
        .sb-icon {
            width: 24px; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.05rem;
            margin-right: 0;
            transition: all 0.2s ease;
        }
        
        .sb-item .sb-text { 
            font-size: 13px !important; /* Ukuran font sub-menu dikecilkan */
            margin-left: 0; /* Menghapus margin ganda, agar jarak icon dekat */
        }
        
        /* WARNA SUB-MENU SAAT DIPILIH (AKTIF) */
        .sb-item.sb-active {
            background: rgba(16,185,129,0.08); /* Latar belakang hijau transparan (sama seperti hover) */
            box-shadow: none; /* Menghapus efek bayangan */
        }
        
        .sb-item.sb-active .sb-text,
        .sb-item.sb-active .sb-icon i {
            color: #10b981 !important; /* Warna teks & icon berubah jadi hijau emerald */
            font-weight: 700; /* Memberi efek tebal agar tetap terlihat bahwa ini menu aktif */
        }
        
        /* Hover saat tidak aktif */
        .sb-item.sb-inactive:hover { background: rgba(16,185,129,0.08); color: #10b981; }
        .sb-item.sb-inactive:hover .sb-icon,
        .sb-item.sb-inactive:hover .sb-text { color: #10b981 !important; }

        /* ============ GRUP ACCORDION (MENU UTAMA) ============ */
        .sb-group { margin-bottom: 4px; }
        .sb-group-toggle {
            display: flex; align-items: center;
            padding: 13px 24px 13px 12px; /* Menu utama tetap besar (13px) */
            font-weight: 500; font-size: 14px;
            color: #9ca3af;
            cursor: pointer;
            text-align: left;
            transition: background 0.2s ease, color 0.2s ease, box-shadow 0.2s ease;
        }
        
        .sb-group-toggle .sb-glabel { 
            margin-left: 0;  /* Menghapus margin ganda, agar jarak icon dekat */
        }
        
        /* Hover Menu Utama */
        .sb-group-toggle:hover { background: rgba(16,185,129,0.08); color: #10b981; }
        .sb-group-toggle:hover .sb-gicon i,
        .sb-group-toggle:hover .sb-glabel,
        .sb-group-toggle:hover .sb-chev { color: #10b981 !important; }

        /* WARNA MENU UTAMA BERDASARKAN HALAMAN AKTIF (Bukan karena diklik) */
        .sb-group:has(.sb-active) .sb-group-toggle {
            background: #10b981 !important;
            box-shadow: 0 8px 20px rgba(16,185,129,0.3) !important;
        }

        .sb-group:has(.sb-active) .sb-group-toggle,
        .sb-group:has(.sb-active) .sb-group-toggle .sb-glabel,
        .sb-group:has(.sb-active) .sb-group-toggle .sb-gicon i,
        .sb-group:has(.sb-active) .sb-group-toggle .sb-chev {
            color: #ffffff !important; /* Teks, icon, panah jadi putih */
        }
        
        .sb-gicon {
            width: 24px; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.05rem;
            margin-right: 0;
            color: #9ca3af;
            transition: color 0.2s ease;
        }
        
        /* FUNGSI BUKA/TUTUP ACCORDION (Tetap menggunakan .sb-open) */
        .sb-chev { 
            margin-left: auto; font-size: 12px; color: #94a3b8; transition: transform 0.25s ease; 
        }
        .sb-group.sb-open .sb-chev { transform: rotate(180deg); }
        
        .sb-sub {
            display: none;
            padding: 4px 0 0 0;
            margin-left: 0;
        }
        .sb-group.sb-open > .sb-sub { display: block; }

    </style>
    
    @stack('styles')
    <!-- ================= PWA SETUP ================= -->
    <!-- Memanggil KTP Aplikasi -->
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#4f46e5">
    <!-- Ikon khusus untuk perangkat Apple / iOS -->
    <link rel="apple-touch-icon" href="{{ asset('icons/icon-192x192.png') }}">

    <!-- Memanggil Asisten (Service Worker) -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js?v=10')
                    .then(registration => {
                        console.log('PWA Asisten siap bertugas di jalur:', registration.scope);
                        return registration.update();
                    })
                    .catch(error => {
                        console.log('PWA Asisten gagal dipanggil:', error);
                    });
            });
        }
    </script>
    <!-- ============================================= -->
</head>
        <body data-turbo="false" class="bg-[#f4f7f6] flex h-screen overflow-hidden text-sm antialiased p-0 md:p-6">

            <!-- CONTAINER-SHELL: frame bulat 2.5rem + pemotong otomatis (overflow:hidden) -->
            <div class="sb-shell flex flex-1 overflow-hidden rounded-[2.5rem] shadow-2xl bg-white border border-gray-100">

            <!-- OFFLINE NOTIF: Mode Ofline -->
            <div id="offline-banner" role="alert" aria-label="Mode Ofline">
                <div class="offline-ikon"><i class="fas fa-wifi-slash"></i></div>
                <div class="offline-teks">
                    <p class="offline-judul">Mode Offline</p>
                    <p class="offline-sub">Data tidak bisa diperbarui</p>
                </div>
            </div>

            <!-- NOTIFIKASI REUSABLE (atas, auto-hilang) -->
            <div id="notif-stack" role="status" aria-live="polite"></div>

            <aside id="sidebar" class="sb-sidebar fixed inset-y-0 left-0 z-50 bg-white border-r border-gray-50 flex flex-col shadow-2xl md:shadow-sm transform -translate-x-full md:relative md:translate-x-0 overflow-hidden transition-[width,transform] duration-300 ease-in-out">
                @php
                    $periodeAktif = \App\Models\Periode::where('is_active', true)->first();
                    $teksPeriode = $periodeAktif ? 'TA. ' . $periodeAktif->tahun_ajaran . ' (' . $periodeAktif->semester . ')' : '⚠ Periode Belum Diatur';
                @endphp
                <!-- HEADER LOGO: SPM WUSTHA + TA kecil di bawahnya -->
                <!-- Ubah px-5 menjadi pl-[12px] pr-5 agar titik awal (kiri) sejajar persis dengan menu -->
                <div class="sb-brand shrink-0 pl-[12px] pr-5 pt-6 pb-4 flex items-center justify-between">
                    <!-- Gunakan gap-3 agar jarak icon ke teks sama dengan jarak pada menu -->
                    <div class="sb-brand-inner flex items-center gap-3 overflow-hidden">
                        
                        <!-- Tambahkan w-10 h-10 flex justify-center items-center agar lebar icon sama dengan icon menu -->
                        <span class="sb-brand-icon text-[#10b981] text-2xl w-10 h-10 flex justify-center items-center flex-shrink-0">
                            <i class="fas fa-mosque"></i>
                        </span>
                        
                        <div class="sb-brand-txt min-w-0">
                            <span class="sb-brand-text block font-bold text-slate-800 text-[17px] tracking-wide leading-tight">SPM WUSTHA</span>
                            <span class="block text-[11px] font-semibold text-slate-500 mt-0.5">{{ $teksPeriode }}</span>
                        </div>
                    </div>
                    
                    <button onclick="toggleSidebar()" class="md:hidden text-gray-400 hover:text-red-500 focus:outline-none transition p-1">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto flex flex-col gap-1 py-2 px-2 scrollbar-none">

                    @php
                        $gMenuUtama   = request()->is('/', 'dashboard-guru', 'meja-kontrol', 'monitoring-kehadiran*', 'laporan', 'jadwal-saya', 'pabrik-barcode', 'scan-kelas', 'agenda-kegiatan*');
                        $gBasis       = request()->is('master-guru*', 'master-pelajaran*', 'batas-pelajaran*', 'master-kelas*', 'master-import*');
                        $gAkademik    = request()->is('master-periode*', 'agenda-kaldik*', 'master-hari-operasional*', 'master-plot-jadwal*', 'master-jadwal-harian*', 'riwayat-mutasi*');
                        $gSetup       = request()->is('setup-user', 'user*', 'manajemen-akses', 'backup-restore*', 'panduan-aplikasi*');
                    @endphp

                    <!-- GRUP: MENU UTAMA -->
                    <div class="sb-group {{ $gMenuUtama ? 'sb-open' : '' }}">
                        <div class="relative flex items-center group">
                            <button type="button" class="sb-group-toggle w-full flex items-center gap-3 p-2 rounded-xl" onclick="sbSelectGroup(this)">
                                <span class="sb-gicon h-10 w-10 flex justify-center items-center flex-shrink-0"><i class="fas fa-th-large text-xl text-slate-500"></i></span>
                                <span class="sb-glabel flex-1 text-left text-sm font-bold text-slate-500">Menu Utama</span>
                                <i class="fas fa-chevron-down sb-chev text-xs text-slate-400"></i>
                            </button>
                        </div>
                        <div class="sb-sub">
                            @can('akses_dashboard')
                            <div class="relative flex items-center group">
                                <a href="/dashboard-utama" class="sb-item w-full flex items-center gap-3 p-2 rounded-xl {{ request()->is('/') ? 'sb-active' : 'sb-inactive' }}">
                                    <div class="sb-icon h-10 w-10 flex justify-center items-center flex-shrink-0"><i class="fas fa-desktop text-xl"></i></div>
                                    <span class="sb-text flex-1 text-left text-sm font-semibold text-slate-500">Dashboard</span>
                                </a>
                            </div>
                            @endcan
                            @can('akses_dashboard_guru')
                            <div class="relative flex items-center group">
                                <a href="/dashboard-guru" class="sb-item w-full flex items-center gap-3 p-2 rounded-xl {{ request()->is('dashboard-guru') ? 'sb-active' : 'sb-inactive' }}">
                                    <div class="sb-icon h-10 w-10 flex justify-center items-center flex-shrink-0"><i class="fas fa-chalkboard-teacher text-xl"></i></div>
                                    <span class="sb-text flex-1 text-left text-sm font-semibold text-slate-500">Beranda Guru</span>
                                </a>
                            </div>
                            @endcan
                            @can('akses_meja_kontrol')
                            <div class="relative flex items-center group">
                                <a href="/meja-kontrol" class="sb-item w-full flex items-center gap-3 p-2 rounded-xl {{ request()->is('meja-kontrol') ? 'sb-active' : 'sb-inactive' }}">
                                    <div class="sb-icon h-10 w-10 flex justify-center items-center flex-shrink-0"><i class="fas fa-tv text-xl"></i></div>
                                    <span class="sb-text flex-1 text-left text-sm font-semibold text-slate-500">Meja Kontrol</span>
                                </a>
                            </div>
                            <div class="relative flex items-center group">
                                <a href="/monitoring-kehadiran" class="sb-item w-full flex items-center gap-3 p-2 rounded-xl {{ request()->is('monitoring-kehadiran*') ? 'sb-active' : 'sb-inactive' }}">
                                    <div class="sb-icon h-10 w-10 flex justify-center items-center flex-shrink-0"><i class="fas fa-user-check text-xl"></i></div>
                                    <span class="sb-text flex-1 text-left text-sm font-semibold text-slate-500">Monitoring Kehadiran</span>
                                </a>
                            </div>
                            @endcan
                            @can('akses_laporan')
                            <div class="relative flex items-center group">
                                <a href="/laporan" class="sb-item w-full flex items-center gap-3 p-2 rounded-xl {{ request()->is('laporan') ? 'sb-active' : 'sb-inactive' }}">
                                    <div class="sb-icon h-10 w-10 flex justify-center items-center flex-shrink-0"><i class="fas fa-print text-xl"></i></div>
                                    <span class="sb-text flex-1 text-left text-sm font-semibold text-slate-500">Rekap Laporan</span>
                                </a>
                            </div>
                            @endcan
                            @can('akses_jadwal_saya')
                            <div class="relative flex items-center group">
                                <a href="/jadwal-saya" class="sb-item w-full flex items-center gap-3 p-2 rounded-xl {{ request()->is('jadwal-saya') ? 'sb-active' : 'sb-inactive' }}">
                                    <div class="sb-icon h-10 w-10 flex justify-center items-center flex-shrink-0"><i class="fas fa-calendar-check text-xl"></i></div>
                                    <span class="sb-text flex-1 text-left text-sm font-semibold text-slate-500">Jadwal Saya</span>
                                </a>
                            </div>
                            @endcan
                            @can('akses_jadwal_saya')
                            <div class="relative flex items-center group">
                                <a href="/pabrik-barcode" class="sb-item w-full flex items-center gap-3 p-2 rounded-xl {{ request()->is('pabrik-barcode') ? 'sb-active' : 'sb-inactive' }}">
                                    <div class="sb-icon h-10 w-10 flex justify-center items-center flex-shrink-0"><i class="fas fa-barcode text-xl"></i></div>
                                    <span class="sb-text flex-1 text-left text-sm font-semibold text-slate-500">Cetak Barcode</span>
                                </a>
                            </div>
                            @endcan
                            @can('akses_jadwal_saya')
                            <div class="relative flex items-center group">
                                <a href="/scan-kelas" class="sb-item w-full flex items-center gap-3 p-2 rounded-xl {{ request()->is('scan-kelas') ? 'sb-active' : 'sb-inactive' }}">
                                    <div class="sb-icon h-10 w-10 flex justify-center items-center flex-shrink-0"><i class="fas fa-qrcode text-xl"></i></div>
                                    <span class="sb-text flex-1 text-left text-sm font-semibold text-slate-500">Scan Hadir</span>
                                </a>
                            </div>
                            @endcan
                            <div class="relative flex items-center group">
                                <a href="/agenda-kegiatan" class="sb-item w-full flex items-center gap-3 p-2 rounded-xl {{ request()->is('agenda-kegiatan*') ? 'sb-active' : 'sb-inactive' }}">
                                    <div class="sb-icon h-10 w-10 flex justify-center items-center flex-shrink-0"><i class="fas fa-calendar-alt text-xl"></i></div>
                                    <span class="sb-text flex-1 text-left text-sm font-semibold text-slate-500">Agenda Kegiatan</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    @canany(['akses_master_guru', 'akses_master_pelajaran', 'akses_master_kelas'])
                    <!-- GRUP: BASIS DATA MASTER -->
                    <div class="sb-group {{ $gBasis ? 'sb-open' : '' }}">
                        <div class="relative flex items-center group">
                            <button type="button" class="sb-group-toggle w-full flex items-center gap-3 p-2 rounded-xl" onclick="sbSelectGroup(this)">
                                <span class="sb-gicon h-10 w-10 flex justify-center items-center flex-shrink-0"><i class="fas fa-database text-xl text-slate-500"></i></span>
                                <span class="sb-glabel flex-1 text-left text-sm font-bold text-slate-500">Basis Data Master</span>
                                <i class="fas fa-chevron-down sb-chev text-xs text-slate-400"></i>
                            </button>
                        </div>
                        <div class="sb-sub">
                            @can('akses_master_guru')
                            <div class="relative flex items-center group">
                                <a href="/master-guru" class="sb-item w-full flex items-center gap-3 p-2 rounded-xl {{ request()->is('master-guru*') ? 'sb-active' : 'sb-inactive' }}">
                                    <div class="sb-icon h-10 w-10 flex justify-center items-center flex-shrink-0"><i class="fas fa-chalkboard-teacher text-xl"></i></div>
                                    <span class="sb-text flex-1 text-left text-sm font-semibold text-slate-500">Master Guru</span>
                                </a>
                            </div>
                            @endcan
                            @can('akses_master_pelajaran')
                            <div class="relative flex items-center group">
                                <a href="/master-pelajaran" class="sb-item w-full flex items-center gap-3 p-2 rounded-xl {{ request()->is('master-pelajaran*') ? 'sb-active' : 'sb-inactive' }}">
                                    <div class="sb-icon h-10 w-10 flex justify-center items-center flex-shrink-0"><i class="fas fa-book-open text-xl"></i></div>
                                    <span class="sb-text flex-1 text-left text-sm font-semibold text-slate-500">Master Pelajaran</span>
                                </a>
                            </div>
                            @endcan
                            @can('akses_master_pelajaran')
                            <div class="relative flex items-center group">
                                <a href="/batas-pelajaran" class="sb-item w-full flex items-center gap-3 p-2 rounded-xl {{ request()->is('batas-pelajaran*') ? 'sb-active' : 'sb-inactive' }}">
                                    <div class="sb-icon h-10 w-10 flex justify-center items-center flex-shrink-0"><i class="fas fa-layer-group text-xl"></i></div>
                                    <span class="sb-text flex-1 text-left text-sm font-semibold text-slate-500">Batas Pelajaran</span>
                                </a>
                            </div>
                            @endcan
                            @can('akses_master_kelas')
                            <div class="relative flex items-center group">
                                <a href="/master-kelas" class="sb-item w-full flex items-center gap-3 p-2 rounded-xl {{ request()->is('master-kelas*') ? 'sb-active' : 'sb-inactive' }}">
                                    <div class="sb-icon h-10 w-10 flex justify-center items-center flex-shrink-0"><i class="fas fa-school text-xl"></i></div>
                                    <span class="sb-text flex-1 text-left text-sm font-semibold text-slate-500">Master Kelas</span>
                                </a>
                            </div>
                            @endcan
                            @can('akses_master_guru')
                            <div class="relative flex items-center group">
                                <a href="/master-import" class="sb-item w-full flex items-center gap-3 p-2 rounded-xl {{ request()->is('master-import*') ? 'sb-active' : 'sb-inactive' }}">
                                    <div class="sb-icon h-10 w-10 flex justify-center items-center flex-shrink-0"><i class="fas fa-file-excel text-xl"></i></div>
                                    <span class="sb-text flex-1 text-left text-sm font-semibold text-slate-500">Pusat Import</span>
                                </a>
                            </div>
                            @endcan
                        </div>
                    </div>
                    @endcanany

                    @canany(['akses_master_periode', 'akses_hari_libur', 'akses_hari_operasional', 'akses_target_mengajar', 'akses_jadwal_harian', 'akses_riwayat_mutasi'])
                    <!-- GRUP: AKADEMIK & JADWAL -->
                    <div class="sb-group {{ $gAkademik ? 'sb-open' : '' }}">
                        <div class="relative flex items-center group">
                            <button type="button" class="sb-group-toggle w-full flex items-center gap-3 p-2 rounded-xl" onclick="sbSelectGroup(this)">
                                <span class="sb-gicon h-10 w-10 flex justify-center items-center flex-shrink-0"><i class="fas fa-calendar-alt text-xl text-slate-500"></i></span>
                                <span class="sb-glabel flex-1 text-left text-sm font-bold text-slate-500">Akademik &amp; Jadwal</span>
                                <i class="fas fa-chevron-down sb-chev text-xs text-slate-400"></i>
                            </button>
                        </div>
                        <div class="sb-sub">
                            @can('akses_master_periode')
                            <div class="relative flex items-center group">
                                <a href="/master-periode" class="sb-item w-full flex items-center gap-3 p-2 rounded-xl {{ request()->is('master-periode*') ? 'sb-active' : 'sb-inactive' }}">
                                    <div class="sb-icon h-10 w-10 flex justify-center items-center flex-shrink-0"><i class="fas fa-calendar-check text-xl"></i></div>
                                    <span class="sb-text flex-1 text-left text-sm font-semibold text-slate-500">Master Periode</span>
                                </a>
                            </div>
                            @endcan
                            @can('akses_hari_libur')
                            <div class="relative flex items-center group">
                                <a href="/agenda-kaldik" class="sb-item w-full flex items-center gap-3 p-2 rounded-xl {{ request()->is('agenda-kaldik*') ? 'sb-active' : 'sb-inactive' }}">
                                    <div class="sb-icon h-10 w-10 flex justify-center items-center flex-shrink-0"><i class="fas fa-calendar-times text-xl"></i></div>
                                    <span class="sb-text flex-1 text-left text-sm font-semibold text-slate-500">Kalender Pendidikan</span>
                                </a>
                            </div>
                            @endcan
                            @can('akses_hari_operasional')
                            <div class="relative flex items-center group">
                                <a href="/master-hari-operasional" class="sb-item w-full flex items-center gap-3 p-2 rounded-xl {{ request()->is('master-hari-operasional*') ? 'sb-active' : 'sb-inactive' }}">
                                    <div class="sb-icon h-10 w-10 flex justify-center items-center flex-shrink-0"><i class="fas fa-calendar-week text-xl"></i></div>
                                    <span class="sb-text flex-1 text-left text-sm font-semibold text-slate-500">Hari Operasional</span>
                                </a>
                            </div>
                            @endcan
                            @can('akses_target_mengajar')
                            <div class="relative flex items-center group">
                                <a href="/master-plot-jadwal" class="sb-item w-full flex items-center gap-3 p-2 rounded-xl {{ request()->is('master-plot-jadwal*') ? 'sb-active' : 'sb-inactive' }}">
                                    <div class="sb-icon h-10 w-10 flex justify-center items-center flex-shrink-0"><i class="fas fa-sitemap text-xl"></i></div>
                                    <span class="sb-text flex-1 text-left text-sm font-semibold text-slate-500">Target Mengajar</span>
                                </a>
                            </div>
                            @endcan
                            @can('akses_jadwal_harian')
                            <div class="relative flex items-center group">
                                <a href="/master-jadwal-harian" class="sb-item w-full flex items-center gap-3 p-2 rounded-xl {{ request()->is('master-jadwal-harian*') ? 'sb-active' : 'sb-inactive' }}">
                                    <div class="sb-icon h-10 w-10 flex justify-center items-center flex-shrink-0"><i class="fas fa-calendar-alt text-xl"></i></div>
                                    <span class="sb-text flex-1 text-left text-sm font-semibold text-slate-500">Jadwal Harian</span>
                                </a>
                            </div>
                            @endcan
                            @can('akses_riwayat_mutasi')
                            <div class="relative flex items-center group">
                                <a href="/riwayat-mutasi" class="sb-item w-full flex items-center gap-3 p-2 rounded-xl {{ request()->is('riwayat-mutasi*') ? 'sb-active' : 'sb-inactive' }}">
                                    <div class="sb-icon h-10 w-10 flex justify-center items-center flex-shrink-0"><i class="fas fa-history text-xl"></i></div>
                                    <span class="sb-text flex-1 text-left text-sm font-semibold text-slate-500">Riwayat Mutasi Jadwal</span>
                                </a>
                            </div>
                            @endcan
                        </div>
                    </div>
                    @endcanany

                    @canany(['akses_manajemen_user', 'akses_manajemen_akses'])
                    <!-- GRUP: SETUP & LAINNYA -->
                    <div class="sb-group {{ $gSetup ? 'sb-open' : '' }}">
                        <div class="relative flex items-center group">
                            <button type="button" class="sb-group-toggle w-full flex items-center gap-3 p-2 rounded-xl" onclick="sbSelectGroup(this)">
                                <span class="sb-gicon h-10 w-10 flex justify-center items-center flex-shrink-0"><i class="fas fa-cog text-xl text-slate-500"></i></span>
                                <span class="sb-glabel flex-1 text-left text-sm font-bold text-slate-500">Setup &amp; Lainnya</span>
                                <i class="fas fa-chevron-down sb-chev text-xs text-slate-400"></i>
                            </button>
                        </div>
                        <div class="sb-sub">
                            @can('akses_manajemen_user')
                            <div class="relative flex items-center group">
                                <a href="/setup-user" class="sb-item w-full flex items-center gap-3 p-2 rounded-xl {{ request()->is('setup-user') || request()->is('user*') ? 'sb-active' : 'sb-inactive' }}">
                                    <div class="sb-icon h-10 w-10 flex justify-center items-center flex-shrink-0"><i class="fas fa-users-cog text-xl"></i></div>
                                    <span class="sb-text flex-1 text-left text-sm font-semibold text-slate-500">Setup User</span>
                                </a>
                            </div>
                            @endcan
                            @can('akses_manajemen_akses')
                            <div class="relative flex items-center group">
                                <a href="/manajemen-akses" class="sb-item w-full flex items-center gap-3 p-2 rounded-xl {{ request()->is('manajemen-akses') ? 'sb-active' : 'sb-inactive' }}">
                                    <div class="sb-icon h-10 w-10 flex justify-center items-center flex-shrink-0"><i class="fas fa-key text-xl"></i></div>
                                    <span class="sb-text flex-1 text-left text-sm font-semibold text-slate-500">Hak Akses</span>
                                </a>
                            </div>
                            @endcan
                            <div class="relative flex items-center group">
                                <a href="/backup-restore" class="sb-item w-full flex items-center gap-3 p-2 rounded-xl {{ request()->is('backup-restore*') ? 'sb-active' : 'sb-inactive' }}">
                                    <div class="sb-icon h-10 w-10 flex justify-center items-center flex-shrink-0"><i class="fas fa-database text-xl"></i></div>
                                    <span class="sb-text flex-1 text-left text-sm font-semibold text-slate-500">Manajemen Database</span>
                                </a>
                            </div>
                            @role('Administrator')
                            <div class="relative flex items-center group">
                                <a href="/panduan-aplikasi" class="sb-item w-full flex items-center gap-3 p-2 rounded-xl {{ request()->is('panduan-aplikasi*') ? 'sb-active' : 'sb-inactive' }}">
                                    <div class="sb-icon h-10 w-10 flex justify-center items-center flex-shrink-0"><i class="fas fa-book-open text-xl"></i></div>
                                    <span class="sb-text flex-1 text-left text-sm font-semibold text-slate-500">Panduan Aplikasi</span>
                                </a>
                            </div>
                            @endrole
                        </div>
                    </div>
                    @endcanany

                    <!-- LOGO CARI: ikon saja, di atas user -->
                    <div class="sb-search-logo px-6 pt-1 pb-2 flex justify-center">
                        <button type="button" class="w-9 h-8 flex items-center justify-center rounded-xl text-slate-500 hover:text-[#00c0c7] hover:bg-[#00c0c7]/10 transition cursor-pointer">
                            <i class="fas fa-search text-xl"></i>
                        </button>
                    </div>

                </div>

                <!-- FOOTER SIDEBAR: LOGO CARI (di atas) + USER (aktif, buka dropdown) -->
                <div class="sb-footer shrink-0 p-3 border-t border-slate-100">
                    
                    <!-- USER: klik -> buka dropdown (Ganti Password + Logout) -->
                    <button id="user-trigger" onclick="toggleUserMenu(this)" type="button" class="w-full flex items-center gap-3 p-2 rounded-2xl hover:bg-slate-50 transition cursor-pointer">
                        <!-- Lingkaran Avatar tetap seperti semula -->
                        <div class="h-10 w-10 rounded-full bg-gradient-to-br from-[#00c0c7] to-[#00a8b8] flex items-center justify-center text-white shadow-md border-2 border-white flex-shrink-0">
                            <i class="fas fa-user text-sm"></i>
                        </div>
                        <div class="sb-footer-txt flex-1 text-left min-w-0 overflow-hidden">
                            <p class="text-sm font-bold text-slate-500 truncate">{{ auth()->user()->name ?? 'Nama User' }}</p>
                            <p class="text-[11px] font-semibold text-slate-400 truncate">{{ auth()->user()->role ?? 'Role' }}</p>
                        </div>
                        <i class="sb-footer-chev fas fa-chevron-down text-xs text-slate-400"></i>
                    </button>
                </div>

                <!-- DROPDOWN USER (fixed overlay: Ganti Password + Logout) -->
                <div id="user-dropdown" class="hidden fixed z-[80] w-64 bg-white rounded-2xl shadow-2xl border border-slate-100 overflow-hidden">
                    <div class="p-4 border-b border-slate-100 flex items-center space-x-3 bg-gradient-to-br from-[#00c0c7]/10 to-[#00a8b8]/10">
                        <div class="h-11 w-11 rounded-full bg-gradient-to-br from-[#00c0c7] to-[#00a8b8] flex items-center justify-center text-white flex-shrink-0 shadow-md">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-black text-slate-500 truncate">{{ auth()->user()->name ?? 'Nama User' }}</p>
                            <p class="text-xs text-slate-500 font-semibold truncate">{{ auth()->user()->role ?? 'Role' }}</p>
                        </div>
                    </div>
                    <div class="p-2">
                        <button type="button" onclick="bukaModalGantiPassword()" class="w-full flex items-center px-4 py-2.5 text-sm font-semibold text-slate-600 hover:bg-[#00c0c7]/10 hover:text-[#0e9aa0] rounded-lg transition text-left cursor-pointer">
                            <i class="fas fa-key w-6 text-slate-400"></i> Ganti Password
                        </button>
                    </div>
                    <div class="p-3 border-t border-slate-100 bg-slate-50">
                        <form method="POST" action="/logout">
                            @csrf
                            <button type="submit" class="flex items-center justify-center w-full px-4 py-2 text-sm font-bold text-white bg-gradient-to-r from-rose-500 to-red-500 hover:from-rose-600 hover:to-red-600 rounded-lg transition shadow-sm cursor-pointer">
                                Logout <i class="fas fa-sign-out-alt ml-2"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </aside>
            
            <div id="sidebar-backdrop" onclick="toggleSidebar()" class="fixed inset-0 bg-gray-900 bg-opacity-50 z-40 hidden md:hidden transition-opacity duration-300"></div>

            <div class="flex-1 flex flex-col overflow-hidden">

        <main class="flex-1 overflow-y-auto p-8 bg-[#f8fbfa] relative">
            @if(session('sukses'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded shadow-sm">
                    <i class="fas fa-check-circle mr-2"></i> {{ session('sukses') }}
                </div>
            @endif
            
            @if(session('error'))
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded shadow-sm">
                    <i class="fas fa-exclamation-triangle mr-2"></i> {{ session('error') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded shadow-sm">
                    <strong>Gagal menyimpan:</strong>
                    <ul class="list-disc ml-5 mt-1 text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>
            </div>

            </div>
            <!-- /CONTAINER-SHELL -->

    <div id="modal-ganti-password" class="hidden fixed inset-0 bg-gray-900 bg-opacity-60 overflow-y-auto h-full w-full z-50 flex items-center justify-center backdrop-blur-sm">
        <div class="relative mx-auto p-6 border w-full max-w-sm shadow-2xl rounded-xl bg-white">
            <div class="flex justify-between items-center mb-5 border-b pb-3">
                <h3 class="text-lg font-bold text-gray-800">Ganti Password</h3>
                <button type="button" onclick="tutupModalGantiPassword()" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times text-xl"></i></button>
            </div>
            
            <form id="form-ganti-password" onsubmit="prosesGantiPassword(event)">
                <p id="pesan-notif-password" class="text-xs font-medium text-red-700 mb-4 hidden"></p>

                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Password Lama</label>
                    <input type="password" name="password_lama" required class="w-full border border-gray-300 rounded-lg p-2.5 outline-none focus:border-green-500" placeholder="Masukkan password saat ini">
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Password Baru</label>
                    <input type="password" name="password_baru" required minlength="6" class="w-full border border-gray-300 rounded-lg p-2.5 outline-none focus:border-green-500" placeholder="Minimal 6 karakter">
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Konfirmasi Password Baru</label>
                    <input type="password" name="password_baru_confirmation" required minlength="6" class="w-full border border-gray-300 rounded-lg p-2.5 outline-none focus:border-green-500" placeholder="Ketik ulang password baru">
                </div>

                <div class="flex justify-end space-x-2 border-t pt-4 mt-2">
                    <button type="button" onclick="tutupModalGantiPassword()" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg font-semibold hover:bg-gray-300 transition">Batal</button>
                    <button type="submit" id="btn-simpan-password" class="px-4 py-2 bg-green-600 text-white rounded-lg font-semibold hover:bg-green-700 transition shadow-sm">Simpan Password</button>
                </div>
            </form>
        </div>
    </div>

    <!-- NAV-CLICK LOADING OVERLAY -->
    <div id="nav-loading">
        <div class="dots">
            <div class="dot"></div>
            <div class="dot"></div>
            <div class="dot"></div>
        </div>
        <span class="nav-load-text">Memuat</span>
    </div>

    <script>
        // JAM DIGITAL
        function updateClock() {
            const clockElement = document.getElementById('live-clock');
            if(clockElement) {
                const now = new Date();
                const hours = String(now.getHours()).padStart(2, '0');
                const minutes = String(now.getMinutes()).padStart(2, '0');
                const seconds = String(now.getSeconds()).padStart(2, '0');
                clockElement.innerText = hours + ':' + minutes + ':' + seconds;
            }
        }
        updateClock();
        // GUARD TURBO: <body> ditukar saat navigasi SPA, mencegah timer jam menumpuk
        if (!window.__shellClockGuard) {
            window.__shellClockGuard = 1;
            setInterval(updateClock, 1000);
        }

        // TOGGLE SIDEBAR MOBILE
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const backdrop = document.getElementById('sidebar-backdrop');
            if (sidebar) sidebar.classList.toggle('-translate-x-full');
            if (backdrop) backdrop.classList.toggle('hidden');
        }

        // ============ ACCORDION GRUP SIDEBAR (toggle klik: buka/tutup) ============
        function sbSelectGroup(btn) {
            const group = btn.closest('.sb-group');
            if (!group) return;
            const isOpen = group.classList.contains('sb-open');
            // Tutup semua grup
            document.querySelectorAll('.sb-group.sb-open').forEach(function (g) {
                g.classList.remove('sb-open');
            });
            // Buka grup yang diklik hanya jika tadinya tertutup (toggle)
            if (!isOpen) group.classList.add('sb-open');
        }

        // TOGGLE DROPDOWN USER (posisikan di atas tombol di footer sidebar)
        function toggleUserMenu(btn) {
            const menu = document.getElementById('user-dropdown');
            if (!menu) return;
            if (menu.classList.contains('hidden')) {
                menu.classList.remove('hidden');
                const r = btn.getBoundingClientRect();
                const w = 256; // 16rem
                const h = menu.offsetHeight || 220;
                let left = r.left;
                if (left + w > window.innerWidth - 8) left = window.innerWidth - w - 8;
                menu.style.left = left + 'px';
                menu.style.top = Math.max(8, r.top - h - 8) + 'px';
            } else {
                menu.classList.add('hidden');
            }
        }

        window.addEventListener('click', function(e) {
            const menu = document.getElementById('user-dropdown');
            const trigger = document.getElementById('user-trigger');
            if (menu && trigger && !trigger.contains(e.target) && !menu.contains(e.target)) {
                menu.classList.add('hidden');
            }
        });

        // POPUP GANTI PASSWORD
        function bukaModalGantiPassword() {
            document.getElementById('modal-ganti-password').classList.remove('hidden');
            document.getElementById('user-dropdown').classList.add('hidden'); 
            document.getElementById('form-ganti-password').reset();
            let pesanNotif = document.getElementById('pesan-notif-password');
            pesanNotif.classList.add('hidden');
            pesanNotif.innerText = '';
        }

        function tutupModalGantiPassword() {
            document.getElementById('modal-ganti-password').classList.add('hidden');
        }

        function prosesGantiPassword(event) {
            event.preventDefault();

            let passLama = document.getElementsByName('password_lama')[0].value;
            let passBaru = document.getElementsByName('password_baru')[0].value;
            let passKonfirm = document.getElementsByName('password_baru_confirmation')[0].value;
            let pesanNotif = document.getElementById('pesan-notif-password');
            let btnSimpan = document.getElementById('btn-simpan-password');

            if (passBaru !== passKonfirm) {
                pesanNotif.innerText = "âŒ Konfirmasi password baru tidak cocok!";
                pesanNotif.className = "text-xs font-medium text-red-700 mb-4 block";
                return false;
            }

            btnSimpan.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Memproses...';
            btnSimpan.disabled = true;

            let token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            fetch('/ganti-password', {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token
                },
                body: JSON.stringify({
                    password_lama: passLama,
                    password_baru: passBaru,
                    password_baru_confirmation: passKonfirm
                })
            })
            .then(async response => {
                let data = await response.json();
                if (!response.ok) throw new Error(data.pesan || data.message || "Terjadi kesalahan sistem.");
                return data;
            })
            .then(data => {
                document.getElementById('form-ganti-password').reset();
                btnSimpan.innerText = "Simpan Password";
                btnSimpan.disabled = false;
                pesanNotif.classList.add('hidden');
                
                tutupModalGantiPassword();

                let notifUtama = `
                    <div id="notif-sukses-ajax" class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded shadow-sm transition-all">
                        <i class="fas fa-check-circle mr-2"></i> ${data.pesan}
                    </div>
                `;
                document.querySelector('main').insertAdjacentHTML('afterbegin', notifUtama);

                setTimeout(() => {
                    let elemenNotif = document.getElementById('notif-sukses-ajax');
                    if(elemenNotif) elemenNotif.remove();
                }, 4000);
            })
            .catch((error) => {
                pesanNotif.innerText = "âŒ " + error.message;
                pesanNotif.className = "text-xs font-medium text-red-700 mb-4 block";
                btnSimpan.innerText = "Simpan Password";
                btnSimpan.disabled = false;
            });
        }

        // LOADING GLOBAL UNTUK SEMUA FORM KECUALI AJAX
        document.addEventListener('submit', function(e) {
            let form = e.target;
            if (form.id === 'form-ganti-password' || form.id === 'form-pencarian') return;

            let btnSubmit = form.querySelector('button[type="submit"]');
            if (btnSubmit) {
                if (btnSubmit.disabled) { e.preventDefault(); return; }
                btnSubmit.disabled = true;
                btnSubmit.classList.add('opacity-75', 'cursor-not-allowed');
                btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Memproses...';
            }
        });

        // NAV-CLICK LOADING: tampilkan overlay hanya saat navigasi antar-halaman melambat (hindari "kedip" saat cepat)
        (function() {
            var overlay = document.getElementById('nav-loading');
            if (!overlay) return;
            function sembunyikan() { overlay.classList.remove('show'); }
            document.addEventListener('click', function(e) {
                var link = e.target.closest('a');
                if (!link) return;
                var href = link.getAttribute('href');
                if (!href) return;
                if (href.startsWith('#') || href.startsWith('javascript:') || href.startsWith('mailto:')) return;
                if (link.target === '_blank') return;
                if (e.ctrlKey || e.shiftKey || e.altKey || e.metaKey) return;
                // Lewati tautan dengan data-lazy (mis. sub-menu) yang tidak memuat ulang halaman
                if (link.hasAttribute('data-lazy')) return;
                try {
                    var url = new URL(href, window.location.origin);
                    if (url.pathname === window.location.pathname) return;
                } catch(ex) {}
                // Tunda sebelum menampilkan, agar navigasi cepat tidak sempat "berkedip"
                overlay._navTimer = setTimeout(function() { overlay.classList.add('show'); }, 1200);
            });
            // Bersihkan sentuhan jika halaman sempat kembali/dihidupkan ulang
            document.addEventListener('visibilitychange', function() {
                if (document.visibilityState === 'hidden') sembunyikan();
            });
            window.addEventListener('pageshow', sembunyikan);
        })();

        // OFFLINE NOTIF: tampilkan kartu "Mode Ofline", bisa diusap ke atas untuk menutup
        (function() {
            var banner = document.getElementById('offline-banner');
            if (!banner) return;
            var dismissed = false;
            function setState() {
                if (!navigator.onLine) {
                    // Tampilkan hanya jika belum diusap pada sesi offline ini
                    if (!dismissed) banner.classList.add('show');
                    banner.classList.remove('gone');
                } else {
                    banner.classList.remove('show', 'gone');
                    dismissed = false;
                }
            }
            window.addEventListener('online', setState);
            window.addEventListener('offline', setState);
            setState();

            // Swipe ke atas untuk menutup notifikasi
            var startY = null;
            function mulaiUsap(e) {
                var touch = e.touches ? e.touches[0] : e;
                startY = touch.clientY;
                if (e.target.closest && !e.target.closest('#offline-banner')) startY = null;
            }
            function selesaiUsap(e) {
                if (startY === null) return;
                var touch = e.changedTouches ? e.changedTouches[0] : e;
                var deltaY = touch.clientY - startY;
                startY = null;
                // Usap ke atas bernilai negatif
                if (deltaY < -40) {
                    dismissed = true;
                    banner.classList.remove('show');
                    banner.classList.add('gone');
                }
            }
            banner.addEventListener('touchstart', mulaiUsap, { passive: true });
            banner.addEventListener('touchend', selesaiUsap, { passive: true });
            banner.addEventListener('mousedown', mulaiUsap);
            banner.addEventListener('mouseup', selesaiUsap);
        })();

        // =====================================================
        // GUARD OFFLINE GLOBAL:
        //   - wajibOnline(event): mencegah aksi penulisan data saat
        //     offline, namun halaman tetap bisa dibuka & dilihat.
        //   - Blokir fetch non-GET & submit form saat offline,
        //     KECUALI logout (/logout) yang tetap boleh jalan.
        // =====================================================
        window.__toastOffline = null;
        function tampilToastOffline(pesan) {
            if (!window.__toastOffline) {
                var t = document.createElement('div');
                t.id = 'toast-offline';
                t.style.cssText = 'position:fixed;left:16px;right:16px;bottom:96px;z-index:200;background:#f59e0b;color:#fff;padding:12px 16px;border-radius:14px;font-size:13px;font-weight:700;text-align:center;box-shadow:0 10px 25px rgba(0,0,0,0.2);opacity:0;transform:translateY(10px);transition:opacity .25s ease,transform .25s ease;';
                document.body.appendChild(t);
                window.__toastOffline = t;
            }
            var t = window.__toastOffline;
            t.textContent = pesan;
            t.style.opacity = '1';
            t.style.transform = 'translateY(0)';
            clearTimeout(t._timer);
            t._timer = setTimeout(function() {
                t.style.opacity = '0';
                t.style.transform = 'translateY(10px)';
            }, 2200);
        }
        window.wajibOnline = function(event, pesan) {
            if (navigator.onLine) return true;
            if (event && event.preventDefault) event.preventDefault();
            if (event && event.stopPropagation) event.stopPropagation();
            tampilToastOffline(pesan || 'Fitur memerlukan koneksi internet');
            return false;
        };

        // =====================================================
        // NOTIFIKASI REUSABLE (atas, auto-hilang)
        //   tampilNotif('success'|'error'|'info', judul, sub)
        // =====================================================
        window.tampilNotif = function(tipe, judul, sub) {
            var stack = document.getElementById('notif-stack');
            if (!stack) return;

            var ikonMap = { success: 'fa-check-circle', error: 'fa-exclamation-circle', info: 'fa-info-circle' };
            var item = document.createElement('div');
            item.className = 'notif-item ' + (tipe || 'info');
            item.innerHTML =
                '<div class="notif-ikon"><i class="fas ' + (ikonMap[tipe] || ikonMap.info) + '"></i></div>' +
                '<div class="notif-teks">' +
                    '<p class="notif-judul"></p>' +
                    '<p class="notif-sub"></p>' +
                '</div>';
            item.querySelector('.notif-judul').textContent = judul || '';
            item.querySelector('.notif-sub').textContent = sub || '';
            stack.appendChild(item);

            setTimeout(function() {
                item.classList.add('hide');
                setTimeout(function() { item.remove(); }, 320);
            }, 2600);
        };

        function isMutationMethod(method) {
            method = (method || 'GET').toUpperCase();
            return method === 'POST' || method === 'PUT' || method === 'PATCH' || method === 'DELETE';
        }
        function isLogoutUrl(url) {
            try {
                var p = new URL(url, window.location.origin).pathname;
                return p === '/logout';
            } catch (e) {
                return false;
            }
        }

        // Blokir fetch penulisan data (scan, ganti password, notifikasi, profil, dll.) saat offline
        (function() {
            var gemuk = window.fetch;
            window.fetch = function(input, init) {
                var url = typeof input === 'string' ? input : (input && input.url);
                var method = (init && init.method) || (input && input.method) || 'GET';
                if (!navigator.onLine && isMutationMethod(method) && !isLogoutUrl(url)) {
                    tampilToastOffline('Perlu koneksi internet untuk menyimpan/update data');
                    return Promise.reject(new Error('Offline: tidak dapat menyimpan data'));
                }
                return gemuk.apply(this, arguments);
            };
        })();

        // Blokir submit form (kecuali logout) saat offline
        document.addEventListener('submit', function(e) {
            if (navigator.onLine) return;
            var form = e.target;
            if (form && isLogoutUrl(form.action)) return;
            e.preventDefault();
            e.stopPropagation();
            tampilToastOffline('Perlu koneksi internet untuk menyimpan/update data');
        }, true);

    </script>

    @stack('scripts')

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const sidebar = document.querySelector('.sb-sidebar');
            
            if (sidebar) {
                // Ketika mouse keluar dari area sidebar
                sidebar.addEventListener('mouseleave', function() {
                    // Cari semua grup menu yang saat ini sedang terbuka
                    const openGroups = sidebar.querySelectorAll('.sb-group.sb-open');
                    
                    openGroups.forEach(function(group) {
                        // Cari tau apakah di dalam grup ini ada menu yang halamannya sedang aktif
                        const hasActiveItem = group.querySelector('.sb-item.sb-active');
                        
                        // Jika TIDAK ADA menu aktif di dalamnya, tutup otomatis
                        if (!hasActiveItem) {
                            group.classList.remove('sb-open');
                        }
                    });
                });
            }
        });
    </script>
</body>


</html>