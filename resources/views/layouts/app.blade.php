<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}"> 
    <title>@yield('title', 'SmartPesantren')</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- Turbo Drive: navigasi AJAX anti-kedip antar halaman (scope di halaman guru via data-turbo) -->
    <script src="https://cdn.jsdelivr.net/npm/@hotwired/turbo@8/dist/turbo.es2017-umd.js" data-turbo-eval="false"></script>
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
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

        /* ============================================================
           SIDEBAR COLLAPSIBLE (Boardto style) - hanya di desktop (md+)
           ============================================================ */
        #sidebar { transition: width 0.3s ease; }
        @media (min-width: 768px) {
            #sidebar { width: 16rem; }
            #sidebar.sidebar-collapsed { width: 5rem; }
            #sidebar.sidebar-collapsed .sb-text { display: none; }
            #sidebar.sidebar-collapsed .sb-group-title { display: none; }
            #sidebar.sidebar-collapsed .sb-brand-text { display: none; }
            #sidebar.sidebar-collapsed .sb-item { justify-content: center; padding-left: 0; padding-right: 0; }
            #sidebar.sidebar-collapsed .sb-icon { margin: 0; }
            #sidebar.sidebar-collapsed .sb-brand { justify-content: center; padding-left: 0; padding-right: 0; }
        }

        /* Item sidebar gaya Boardto */
        .sb-item {
            display: flex; align-items: center;
            padding: 10px 12px;
            border-radius: 0.75rem;
            font-weight: 700; font-size: 14px;
            transition: all 0.2s ease;
        }
        .sb-icon {
            width: 34px; height: 34px; flex-shrink: 0;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 15px;
            margin-right: 12px;
            transition: all 0.2s ease;
        }
        .sb-item.sb-active {
            background: #10b981; color: #fff;
            box-shadow: 0 4px 15px -3px rgba(16,185,129,0.5);
        }
        .sb-item.sb-active .sb-icon { background: rgba(255,255,255,0.2); color: #fff; }
        .sb-item.sb-inactive { color: #64748b; }
        .sb-item.sb-inactive:hover { background: #f1f5f9; color: #0f766e; }
        .sb-item.sb-inactive:hover .sb-icon { background: #ecfdf5; color: #059669; }

        .sb-group-title {
            font-size: 10px; font-weight: 800; letter-spacing: 0.08em;
            text-transform: uppercase; color: #10b981;
            padding: 0 12px; margin: 18px 0 6px;
        }

        /* Header modern (Boardto) */
        .app-header {
            height: 5rem;
            background: rgba(255,255,255,0.8);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid #e2e8f0;
        }
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
                navigator.serviceWorker.register('/sw.js?v=8')
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
        <body data-turbo="false" class="bg-gray-50 flex h-screen overflow-hidden text-sm antialiased">

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

            <aside id="sidebar" class="fixed inset-y-0 left-0 z-50 w-64 bg-white border-r border-slate-200 flex flex-col shadow-2xl md:shadow-sm transform -translate-x-full md:relative md:translate-x-0 transition-transform duration-300 ease-in-out overflow-hidden">
                <div class="h-20 sb-brand shrink-0 flex items-center justify-between px-6 border-b border-slate-100 shrink-0">
                    <div class="flex items-center">
                        <span class="text-emerald-500 text-2xl mr-3"><i class="fas fa-mosque"></i></span>
                        <span class="sb-brand-text font-black text-lg text-slate-800 tracking-tight">Muadalah Wustha</span>
                    </div>
                    <button onclick="toggleSidebar()" class="md:hidden text-gray-400 hover:text-red-500 focus:outline-none transition p-1">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                
                <div class="flex-1 overflow-y-auto py-2 px-3 scrollbar-none">
                    <div class="sb-group-title">Menu Utama</div>
                    <nav class="space-y-1 pb-3">
                        @can('akses_dashboard')
                        <a href="/dashboard-utama" class="sb-item {{ request()->is('/') ? 'sb-active' : 'sb-inactive' }}">
                            <div class="sb-icon"><i class="fas fa-desktop"></i></div>
                            <span class="sb-text">Dashboard</span>
                        </a>
                        @endcan

                        @can('akses_dashboard_guru')
                        <a href="/dashboard-guru" class="sb-item {{ request()->is('dashboard-guru') ? 'sb-active' : 'sb-inactive' }}">
                            <div class="sb-icon"><i class="fas fa-chalkboard-teacher"></i></div>
                            <span class="sb-text">Beranda Guru</span>
                        </a>
                        @endcan
                        
                        @can('akses_meja_kontrol')
                        <a href="/meja-kontrol" class="sb-item {{ request()->is('meja-kontrol') ? 'sb-active' : 'sb-inactive' }}">
                            <div class="sb-icon"><i class="fas fa-tv"></i></div>
                            <span class="sb-text">Meja Kontrol</span>
                        </a>
                        @endcan

                        @can('akses_laporan')
                        <a href="/laporan" class="sb-item {{ request()->is('laporan') ? 'sb-active' : 'sb-inactive' }}">
                            <div class="sb-icon"><i class="fas fa-print"></i></div>
                            <span class="sb-text">Rekap Laporan</span>
                        </a>
                        @endcan

                        @can('akses_jadwal_saya')
                        <a href="/jadwal-saya" class="sb-item {{ request()->is('jadwal-saya') ? 'sb-active' : 'sb-inactive' }}">
                            <div class="sb-icon"><i class="fas fa-calendar-check"></i></div>
                            <span class="sb-text">Jadwal Saya</span>
                        </a>
                        @endcan

                        @can('akses_jadwal_saya')
                        <a href="/pabrik-barcode" class="sb-item {{ request()->is('pabrik-barcode') ? 'sb-active' : 'sb-inactive' }}">
                            <div class="sb-icon"><i class="fas fa-barcode"></i></div>
                            <span class="sb-text">Cetak Barcode</span>
                        </a>
                        @endcan

                        @can('akses_jadwal_saya')
                        <a href="/scan-kelas" class="sb-item {{ request()->is('scan-kelas') ? 'sb-active' : 'sb-inactive' }}">
                            <div class="sb-icon"><i class="fas fa-qrcode"></i></div>
                            <span class="sb-text">Scan Hadir</span>
                        </a>
                        @endcan

                        <a href="/agenda-kegiatan" class="sb-item {{ request()->is('agenda-kegiatan*') ? 'sb-active' : 'sb-inactive' }}">
                            <div class="sb-icon"><i class="fas fa-calendar-alt"></i></div>
                            <span class="sb-text">Agenda Kegiatan</span>
                        </a>
                    </nav>

                    @canany(['akses_master_guru', 'akses_master_pelajaran', 'akses_master_kelas'])
                    <div class="sb-group-title border-t border-slate-100">Basis Data Master</div>
                    <nav class="space-y-1 pb-3">
                        @can('akses_master_guru')
                        <a href="/master-guru" class="sb-item {{ request()->is('master-guru*') ? 'sb-active' : 'sb-inactive' }}">
                            <div class="sb-icon"><i class="fas fa-chalkboard-teacher"></i></div>
                            <span class="sb-text">Master Guru</span>
                        </a>
                        @endcan
                        
                        @can('akses_master_pelajaran')
                        <a href="/master-pelajaran" class="sb-item {{ request()->is('master-pelajaran*') ? 'sb-active' : 'sb-inactive' }}">
                            <div class="sb-icon"><i class="fas fa-book-open"></i></div>
                            <span class="sb-text">Master Pelajaran</span>
                        </a>
                        @endcan

                        @can('akses_master_pelajaran')
                        <a href="/batas-pelajaran" class="sb-item {{ request()->is('batas-pelajaran*') ? 'sb-active' : 'sb-inactive' }}">
                            <div class="sb-icon"><i class="fas fa-layer-group"></i></div>
                            <span class="sb-text">Batas Pelajaran</span>
                        </a>
                        @endcan
                        
                        @can('akses_master_kelas')
                        <a href="/master-kelas" class="sb-item {{ request()->is('master-kelas*') ? 'sb-active' : 'sb-inactive' }}">
                            <div class="sb-icon"><i class="fas fa-school"></i></div>
                            <span class="sb-text">Master Kelas</span>
                        </a>
                        @endcan
                        
                        @can('akses_master_guru')
                        <a href="/master-import" class="sb-item {{ request()->is('master-import*') ? 'sb-active' : 'sb-inactive' }}">
                            <div class="sb-icon"><i class="fas fa-file-excel"></i></div>
                            <span class="sb-text">Pusat Import</span>
                        </a>
                        @endcan
                    </nav>
                    @endcanany

                    @canany(['akses_master_periode', 'akses_hari_libur', 'akses_hari_operasional', 'akses_target_mengajar', 'akses_jadwal_harian'])
                    <div class="sb-group-title border-t border-slate-100">Akademik &amp; Jadwal</div>
                    <nav class="space-y-1 pb-3">
                        @can('akses_master_periode')
                        <a href="/master-periode" class="sb-item {{ request()->is('master-periode*') ? 'sb-active' : 'sb-inactive' }}">
                            <div class="sb-icon"><i class="fas fa-calendar-check"></i></div>
                            <span class="sb-text">Master Periode</span>
                        </a>
                        @endcan
                        @can('akses_hari_libur')
                        <a href="/agenda-kaldik" class="sb-item {{ request()->is('agenda-kaldik*') ? 'sb-active' : 'sb-inactive' }}">
                            <div class="sb-icon"><i class="fas fa-calendar-times"></i></div>
                            <span class="sb-text">Kalender Pendidikan</span>
                        </a>
                        @endcan
                        @can('akses_hari_operasional')
                        <a href="/master-hari-operasional" class="sb-item {{ request()->is('master-hari-operasional*') ? 'sb-active' : 'sb-inactive' }}">
                            <div class="sb-icon"><i class="fas fa-calendar-week"></i></div>
                            <span class="sb-text">Hari Operasional</span>
                        </a>
                        @endcan
                        @can('akses_target_mengajar')
                        <a href="/master-plot-jadwal" class="sb-item {{ request()->is('master-plot-jadwal*') ? 'sb-active' : 'sb-inactive' }}">
                            <div class="sb-icon"><i class="fas fa-sitemap"></i></div>
                            <span class="sb-text">Target Mengajar</span>
                        </a>
                        @endcan
                        @can('akses_jadwal_harian')
                        <a href="/master-jadwal-harian" class="sb-item {{ request()->is('master-jadwal-harian*') ? 'sb-active' : 'sb-inactive' }}">
                            <div class="sb-icon"><i class="fas fa-calendar-alt"></i></div>
                            <span class="sb-text">Jadwal Harian</span>
                        </a>
                        @endcan
                    </nav>
                    @endcanany
                    
                    @canany(['akses_manajemen_user', 'akses_manajemen_akses'])
                    <div class="sb-group-title border-t border-slate-100">Setup &amp; Lainnya</div>
                    <nav class="space-y-1 pb-3">
                        @can('akses_manajemen_user')
                        <a href="/setup-user" class="sb-item {{ request()->is('setup-user') || request()->is('user*') ? 'sb-active' : 'sb-inactive' }}">
                            <div class="sb-icon"><i class="fas fa-users-cog"></i></div>
                            <span class="sb-text">Setup User</span>
                        </a>
                        @endcan
                        @can('akses_manajemen_akses')
                        <a href="/manajemen-akses" class="sb-item {{ request()->is('manajemen-akses') ? 'sb-active' : 'sb-inactive' }}">
                            <div class="sb-icon"><i class="fas fa-key"></i></div>
                            <span class="sb-text">Hak Akses</span>
                        </a>
                        @endcan

                        <a href="/backup-restore" class="sb-item {{ request()->is('backup-restore*') ? 'sb-active' : 'sb-inactive' }}">
                            <div class="sb-icon"><i class="fas fa-database"></i></div>
                            <span class="sb-text">Manajemen Database</span>
                        </a>
                    </nav>
                    @endcanany
                </div>
            </aside>

            <div id="sidebar-backdrop" onclick="toggleSidebar()" class="fixed inset-0 bg-gray-900 bg-opacity-50 z-40 hidden md:hidden transition-opacity duration-300"></div>

            <div class="flex-1 flex flex-col overflow-hidden">
                
                <header class="app-header sticky top-0 z-50 flex items-center justify-between px-4 md:px-6 w-full">

            <div class="flex items-center min-w-0">
                <button onclick="toggleSidebar()" type="button" class="md:hidden text-slate-600 hover:text-emerald-600 hover:bg-emerald-50 p-2 rounded-xl focus:outline-none transition">
                    <i class="fas fa-bars text-xl"></i>
                </button>
                <button onclick="toggleSidebarCollapse()" id="sidebar-collapse-btn" type="button" class="hidden md:flex mr-3 text-slate-500 hover:text-emerald-600 hover:bg-emerald-50 p-2 rounded-xl focus:outline-none transition cursor-pointer">
                    <i id="collapse-chevron" class="fas fa-chevron-left text-sm"></i>
                </button>

                <div class="flex items-center space-x-2 md:hidden">
                    <span class="text-emerald-500 text-xl"><i class="fas fa-mosque"></i></span>
                    <span class="font-black text-slate-800 text-base tracking-tight">Muadalah</span>
                </div>

                @php
                    $periodeAktif = \App\Models\Periode::where('is_active', true)->first();
                    $teksPeriode = $periodeAktif ? 'TA. ' . $periodeAktif->tahun_ajaran . ' (' . $periodeAktif->semester . ')' : '⚠ Periode Belum Diatur';
                    $warnaPeriode = $periodeAktif ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-red-50 text-red-700 border-red-200 animate-pulse';
                @endphp
                <div class="hidden xl:flex ml-3 px-3 py-1.5 rounded-lg text-xs font-bold border {{ $warnaPeriode }} items-center shadow-sm">
                    <i class="fas fa-calendar-check mr-2"></i> {{ $teksPeriode }}
                </div>
            </div>

            <div class="flex items-center space-x-3 sm:space-x-4 text-slate-600 relative">
                <div class="relative hidden lg:block">
                    <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-sm text-slate-400"></i>
                    <input type="text" id="global-search" placeholder="Cari menu..." class="w-56 bg-slate-100/80 focus:bg-white border border-transparent focus:border-emerald-200 placeholder:text-slate-400 pl-11 pr-4 py-2 rounded-full text-sm font-medium focus:outline-none focus:ring-2 focus:ring-emerald-100 transition" />
                </div>

                <button class="relative hover:text-emerald-600 hover:bg-emerald-50 p-2.5 rounded-xl transition cursor-pointer w-11 h-11 flex items-center justify-center">
                    <i class="fas fa-bell text-lg"></i>
                    <span class="absolute top-2 right-2.5 w-2 h-2 bg-rose-500 rounded-full ring-2 ring-white"></span>
                </button>

                <div class="relative">
                    <button onclick="toggleUserMenu()" class="flex items-center space-x-2 focus:outline-none hover:bg-slate-50 p-1.5 rounded-xl transition cursor-pointer w-full">
                        <div class="text-right hidden md:block">
                            <p class="text-xs text-slate-500 font-bold uppercase tracking-wider">{{ auth()->user()->role ?? 'ADMIN INSTANSI' }}</p>
                            <p class="text-sm font-black text-emerald-700">{{ auth()->user()->name ?? 'Nama User' }}</p>
                        </div>
                        <div class="h-10 w-10 rounded-full bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center text-white shadow-md border-2 border-white">
                            <i class="fas fa-user"></i>
                        </div>
                        <i class="fas fa-chevron-down text-xs text-slate-400"></i>
                    </button>

                    <div id="user-dropdown" class="hidden absolute right-0 mt-3 w-64 bg-white rounded-2xl shadow-2xl border border-slate-100 z-50 overflow-hidden transform transition-all">
                        <div class="p-4 border-b border-slate-100 flex items-center space-x-3 bg-gradient-to-br from-emerald-50 to-teal-50">
                            <div class="h-12 w-12 rounded-full bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center text-white flex-shrink-0 shadow-md">
                                <i class="fas fa-user text-xl"></i>
                            </div>
                            <div>
                                <p class="text-sm font-black text-slate-800">{{ auth()->user()->name ?? 'Nama User' }}</p>
                                <p class="text-xs text-slate-500 font-semibold">{{ auth()->user()->role ?? 'Role' }}</p>
                            </div>
                        </div>

                        <div class="p-2">
                            <button type="button" onclick="bukaModalGantiPassword()" class="w-full flex items-center px-4 py-2.5 text-sm font-semibold text-slate-600 hover:bg-emerald-50 hover:text-emerald-700 rounded-lg transition text-left cursor-pointer">
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
                </div>
            </div>

        </header>

        <main class="flex-1 overflow-y-auto p-4 md:p-6 bg-gray-50 relative">
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

        // TOGGLE SIDEBAR COLLAPSE (desktop, hanya ikon)
        function toggleSidebarCollapse() {
            const sidebar = document.getElementById('sidebar');
            const collapsed = sidebar.classList.toggle('sidebar-collapsed');
            const chevron = document.getElementById('collapse-chevron');
            if (chevron) chevron.classList.toggle('fa-chevron-left', collapsed);
            if (chevron) chevron.classList.toggle('fa-chevron-right', !collapsed);
            try { localStorage.setItem('sb-collapsed', collapsed ? '1' : '0'); } catch (e) {}
        }

        (function applySidebarCollapse() {
            try {
                if (localStorage.getItem('sb-collapsed') === '1') {
                    const sidebar = document.getElementById('sidebar');
                    const chevron = document.getElementById('collapse-chevron');
                    if (sidebar) sidebar.classList.add('sidebar-collapsed');
                    if (chevron) { chevron.classList.remove('fa-chevron-left'); chevron.classList.add('fa-chevron-right'); }
                }
            } catch (e) {}
        })();

        // TOGGLE DROPDOWN USER
        function toggleUserMenu() {
            const menu = document.getElementById('user-dropdown');
            menu.classList.toggle('hidden');
        }

        window.addEventListener('click', function(e) {
            const menu = document.getElementById('user-dropdown');
            const button = menu.previousElementSibling;
            if (menu && button && !button.contains(e.target) && !menu.contains(e.target)) {
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
                pesanNotif.innerText = "❌ Konfirmasi password baru tidak cocok!";
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
                pesanNotif.innerText = "❌ " + error.message;
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
</body>


</html>