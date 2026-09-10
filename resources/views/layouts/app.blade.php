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
    
    @vite(['resources/css/app.css'])
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- Turbo Drive: navigasi AJAX anti-kedip antar halaman (scope di halaman guru via data-turbo) -->
    <script src="https://cdn.jsdelivr.net/npm/@hotwired/turbo@8/dist/turbo.es2017-umd.js" data-turbo-eval="false"></script>
    
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }

        @supports (padding-bottom: env(safe-area-inset-bottom)) {
            .pb-safe { padding-bottom: env(safe-area-inset-bottom); }
        }

        main {
            padding-bottom: max(5rem, calc(1rem + env(safe-area-inset-bottom)));
        }
        @media (min-width: 768px) {
            main { padding-bottom: 2rem; }
        }

        /* Sembunyikan bar progress bawaan Turbo (ubah warna biru #0076ff menjadi transparan; sudah ada indikator #nav-loading sendiri) */
        div.turbo-progress-bar {
            visibility: hidden !important;
        }

        /* NAV-CLICK LOADING: indikator kecil di atas (tidak menutupi halaman) */
        #nav-loading {
            position: fixed; top: 14px; left: 50%; transform: translateX(-50%);
            z-index: 180; pointer-events: none;
            background: rgba(255,255,255,0.92);
            -webkit-backdrop-filter: blur(10px); backdrop-filter: blur(10px);
            border: 1px solid rgba(16,185,129,0.25);
            border-radius: 999px;
            padding: 9px 16px;
            box-shadow: 0 10px 28px rgba(2,6,23,0.14);
            display: none; align-items: center; justify-content: center; gap: 10px;
        }
        #nav-loading.show { display: flex; }
        #nav-loading .dots { display: flex; gap: 6px; }
        #nav-loading .dot {
            width: 8px; height: 8px; border-radius: 50%;
            background: #10b981;
            box-shadow: 0 0 8px 2px rgba(16,185,129,0.4);
            animation: navDotBounce 1.2s ease-in-out infinite;
        }
        #nav-loading .dot:nth-child(2) { animation-delay: 0.15s; }
        #nav-loading .dot:nth-child(3) { animation-delay: 0.3s; }
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

/* ================================================================
           SIDEBAR NAVIGASI (gaya Codinglab): Light/Dark + Expand/Collapse
        ================================================================ */
        .sb-sidebar {
            width: 250px;
            background-color: #ffffff;
            border-right: 1px solid #e5e7eb;
            overflow-x: hidden;
            transition: width 0.3s ease, background-color 0.3s ease, border-color 0.3s ease, box-shadow 0.3s ease;
        }
        html.dark .sb-sidebar {
            background-color: #0e1116;
            border-right-color: #1f2937;
        }

        /* scrollbar sisi: tipis & tidak menabrak ikon */
        .sb-sidebar ::-webkit-scrollbar { width: 5px; }
        .sb-sidebar ::-webkit-scrollbar-thumb { background: rgba(148, 163, 184, 0.35); border-radius: 999px; }
        .sb-sidebar ::-webkit-scrollbar-track { background: transparent; }
        .sb-scroll { overflow-y: auto; overflow-x: hidden; scrollbar-width: thin; scrollbar-color: rgba(148, 163, 184, 0.35) transparent; }

        /* ============ HEADER / BRAND ============ */
        .sb-brand { display: flex; align-items: center; gap: 12px; }
        .sb-brand-inner { display: flex; align-items: center; gap: 12px; min-width: 0; flex: 1; }
        .sb-brand-icon {
            width: 42px; height: 42px; flex-shrink: 0; overflow: hidden;
            display: flex; align-items: center; justify-content: center;
            background: linear-gradient(135deg, #10b981, #059669);
            border-radius: 14px;
            box-shadow: 0 6px 14px rgba(5, 150, 105, 0.35);
        }
        .sb-brand-icon img { width: 100%; height: 100%; object-fit: contain; }
        .sb-brand-title { color: #111827; font-size: 15px; font-weight: 800; letter-spacing: 0.02em; line-height: 1.2; }
        .sb-brand-sub { color: #6b7280; font-size: 11px; font-weight: 600; margin-top: 1px; }
        html.dark .sb-brand-title { color: #f9fafb; }
        html.dark .sb-brand-sub { color: #94a3b8; }

        /* ============ PENCARIAN (ikon sejajar kolom ikon menu) ============ */
        .sb-search { padding: 8px 8px 16px; }
        .sb-search-wrap {
            display: flex; align-items: center; overflow: hidden;
            background-color: #f3f4f6; border: 1px solid transparent; border-radius: 12px;
            transition: background-color 0.3s ease, border-color 0.3s ease;
        }
        .sb-search-wrap:hover, .sb-search-wrap:focus-within { background-color: #ffffff; border-color: #10b981; }
        .sb-search-ikon {
            width: 40px; height: 40px; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center;
            color: #9ca3af; font-size: 1.25rem; pointer-events: none;
            transition: color 0.3s ease;
        }
        .sb-search-input {
            flex: 1; min-width: 0; height: 40px;
            background-color: transparent; border: none; outline: none;
            color: #0f172a; font-size: 13px; font-weight: 500; letter-spacing: 0.01em;
            padding: 0 14px 0 0;
            transition: opacity 0.2s ease;
        }
        .sb-search-input::placeholder { color: #9ca3af; }
        html.dark .sb-search-wrap { background-color: #1e2733; }
        html.dark .sb-search-wrap:hover, html.dark .sb-search-wrap:focus-within { background-color: #273343; border-color: #10b981; }
        html.dark .sb-search-input { color: #f3f4f6; }
        html.dark .sb-search-ikon { color: #64748b; }

        /* ============ MENU (item & grup) ============ */
        .sb-item, .sb-group-toggle {
            display: flex; align-items: center; gap: 12px;
            flex-wrap: nowrap;
            width: 100%;
            padding: 8px;
            margin: 2px 0;
            border-radius: 12px;
            color: #6b7280;
            font-size: 14px; font-weight: 500;
            cursor: pointer;
            text-align: left;
            transition: background 0.2s ease, color 0.2s ease;
        }
        html.dark .sb-item, html.dark .sb-group-toggle { color: #9ca3af; }
        .sb-item:hover, .sb-group-toggle:hover { background: #f3f4f6; color: #111827; }
        html.dark .sb-item:hover, html.dark .sb-group-toggle:hover { background: #1e2733; color: #f9fafb; }
        .sb-icon, .sb-gicon {
            width: 40px; height: 40px; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center;
        }
        .sb-item .sb-text { font-size: 13px; font-weight: 500; }
        .sb-group-toggle .sb-glabel { font-size: 13px; flex: 1; font-weight: 700; }
        .sb-chev { margin-left: auto; font-size: 12px; flex-shrink: 0; transition: transform 0.25s ease; }
        .sb-group.sb-open .sb-chev { transform: rotate(180deg); }
        .sb-item.sb-inactive { color: #6b7280; }
        html.dark .sb-item.sb-inactive { color: #9ca3af; }
        .sb-sub { display: none; padding: 2px 0; }
        .sb-group.sb-open > .sb-sub { display: block; }
        .sb-sub .sb-item { padding-left: 38px; }
        .sb-sub .sb-icon { width: 28px; height: 28px; }
        .sb-sub .sb-icon i { font-size: 0.9rem; }
        .sb-group { margin-bottom: 2px; }

        /* ACTIVE STATE: emerald solid + teks/ikon putih */
        .sb-item.sb-active {
            background: #059669;
            box-shadow: 0 6px 16px rgba(5, 150, 105, 0.35);
            color: #ffffff;
        }
        .sb-item.sb-active .sb-text,
        .sb-item.sb-active .sb-icon i { color: #ffffff !important; font-weight: 700; }
        .sb-group:has(.sb-active) .sb-group-toggle { color: #059669; }
        html.dark .sb-group:has(.sb-active) .sb-group-toggle { color: #34d399; }

        /* ============ FOOTER (user, logout, toggle tema) ============ */
        .sb-footer { padding: 8px; }
        .sb-divider { height: 1px; background: #e5e7eb; margin: 6px 0; }
        html.dark .sb-divider { background: #1f2937; }
        .sb-user-trigger {
            display: flex; align-items: center; gap: 12px; width: 100%;
            padding: 8px; border-radius: 12px; cursor: pointer;
            transition: background 0.2s ease;
        }
        .sb-user-trigger:hover { background: #f3f4f6; }
        html.dark .sb-user-trigger:hover { background: #1e2733; }
        .sb-avatar {
            width: 40px; height: 40px; flex-shrink: 0; border-radius: 12px;
            display: flex; align-items: center; justify-content: center; color: #fff;
            background: linear-gradient(135deg, #10b981, #059669);
            box-shadow: 0 4px 10px rgba(5, 150, 105, 0.3);
        }
        /* dropdown user: mengikuti gaya menu sidebar */
        #user-dropdown { background: #ffffff; border-color: #e5e7eb; }
        html.dark #user-dropdown { background: #161b22; border-color: #1f2937; }
        .sb-ub-header { border-color: #e5e7eb; }
        .sb-ub-judul { color: #111827; }
        .sb-ub-sub { color: #64748b; }
        html.dark .sb-ub-header { border-color: #1f2937; }
        html.dark .sb-ub-judul { color: #f9fafb; }
        html.dark .sb-ub-sub { color: #94a3b8; }
        .sb-drop-item {
            display: flex; align-items: center; gap: 12px; width: 100%;
            padding: 6px 8px; margin: 2px 0; border-radius: 12px;
            color: #6b7280; font-size: 13px; font-weight: 600; cursor: pointer;
            transition: background 0.2s ease, color 0.2s ease;
        }
        .sb-drop-item:hover { background: #f3f4f6; color: #111827; }
        .sb-drop-item .sb-drop-ikon {
            width: 40px; height: 40px; flex-shrink: 0; border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            background: #f3f4f6; color: #059669; font-size: 1rem;
        }
        .sb-drop-item:hover .sb-drop-ikon { background: #d1fae5; color: #047857; }
        .sb-drop-danger { color: #ef4444; }
        .sb-drop-danger:hover { background: rgba(239, 68, 68, 0.08); color: #dc2626; }
        .sb-drop-danger .sb-drop-ikon { background: #fee2e2; color: #ef4444; }
        .sb-drop-danger:hover .sb-drop-ikon { background: #fecaca; color: #dc2626; }
        html.dark .sb-drop-item { color: #9ca3af; }
        html.dark .sb-drop-item:hover { background: #1e2733; color: #f9fafb; }
        html.dark .sb-drop-item .sb-drop-ikon { background: #1e2733; color: #34d399; }
        html.dark .sb-drop-item:hover .sb-drop-ikon { background: #123524; color: #10b981; }
        html.dark .sb-drop-danger { color: #fca5a5; }
        html.dark .sb-drop-danger:hover { background: rgba(239, 68, 68, 0.12); color: #fecaca; }
        html.dark .sb-drop-danger .sb-drop-ikon { background: #3b1212; color: #f87171; }
        html.dark .sb-drop-danger:hover .sb-drop-ikon { background: #4c1d1d; color: #fca5a5; }

        /* toggle tema */
        .sb-theme {
            display: flex; align-items: center; gap: 12px; width: 100%;
            padding: 7px 8px; border-radius: 12px; cursor: pointer;
            transition: background 0.2s ease;
        }
        .sb-theme:hover { background: #f3f4f6; }
        html.dark .sb-theme:hover { background: #1e2733; }
        .sb-theme-switch-show { display: flex; align-items: center; flex-shrink: 0; }
        .sb-theme-switch { position: relative; width: 38px; height: 22px; border-radius: 999px; background: #e5e7eb; transition: background 0.3s ease; }
        html.dark .sb-theme-switch { background: #059669; }
        .sb-theme-knob {
            position: absolute; top: 2px; left: 2px; width: 18px; height: 18px;
            border-radius: 999px; background: #ffffff;
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
            transition: transform 0.3s ease;
        }
        html.dark .sb-theme-knob { transform: translateX(16px); }
        .sb-theme-knob i { font-size: 9px; color: #f59e0b; }
        html.dark .sb-theme-knob i { color: #10b981; }
        .sb-theme-label { font-size: 13px; font-weight: 600; color: #374151; }
        html.dark .sb-theme-label { color: #e5e7eb; }
        .sb-theme-icon-show {
            display: none; align-items: center; justify-content: center;
            width: 40px; height: 40px; border-radius: 12px;
            background: #d1fae5; color: #047857; font-size: 15px; flex-shrink: 0;
        }
        html.dark .sb-theme-icon-show { background: #1e2733; color: #34d399; }

        /* ============ COLLAPSE (md+): 80px ikon saja -> hover 250px ============ */
        @media (max-width: 767.98px) {
            .sb-sidebar { width: 250px !important; }
        }
        @media (min-width: 768px) {
            .sb-sidebar { width: 80px; }
            .sb-sidebar:hover { width: 250px; }

            .sb-sidebar .sb-text,
            .sb-sidebar .sb-glabel,
            .sb-sidebar .sb-footer-txt,
            .sb-sidebar .sb-theme-label,
            .sb-sidebar .sb-brand-inner .sb-brand-txt {
                width: 0; min-width: 0; visibility: hidden; opacity: 0;
                overflow: hidden; white-space: nowrap;
            }
            .sb-sidebar:hover .sb-text,
            .sb-sidebar:hover .sb-glabel,
            .sb-sidebar:hover .sb-footer-txt,
            .sb-sidebar:hover .sb-theme-label,
            .sb-sidebar:hover .sb-brand-inner .sb-brand-txt {
                visibility: visible; opacity: 1; width: auto;
            }

            /* IKON TIDAK BERGANDA POSISI: saat tertutup, ikon tetap di kolom yang sama (hanya teks/elemen samping yang disembunyikan) */

            /* pencarian: tutup = chip ikon saja di kiri (sejajar kolom ikon menu) */
            .sb-sidebar:not(:hover) .sb-search-wrap { background-color: transparent; border-color: transparent; }
            .sb-sidebar:not(:hover) .sb-search-input { flex: 0 0 0; width: 0; min-width: 0; padding: 0; opacity: 0; }
            .sb-sidebar:hover .sb-search-input { flex: 1; width: auto; min-width: 0; padding: 0 14px 0 0; opacity: 1; }

            .sb-sidebar:not(:hover) .sb-chev { display: none; }
            .sb-sidebar:hover .sb-chev { display: inline-block; }
            .sb-sidebar:not(:hover) .sb-footer-chev { display: none; }
            .sb-sidebar:hover .sb-footer-chev { display: inline-block; }

            .sb-sidebar:not(:hover) .sb-divider { margin: 6px 0; }
            .sb-sidebar:not(:hover) .sb-theme-switch-show { display: none; }
            .sb-sidebar:not(:hover) .sb-theme-icon-show { display: flex; }
            .sb-sidebar:hover .sb-theme-icon-show { display: none; }
            .sb-sidebar:hover .sb-theme-switch-show { display: flex; }

            .sb-sidebar:not(:hover) .sb-sub { display: none !important; }
            .sb-sidebar:not(:hover) .sb-group:has(.sb-active) .sb-group-toggle .sb-gicon i { color: #059669; }
        }

    </style>
    
    @stack('styles')
    <!-- ================= PWA SETUP ================= -->
    <!-- Memanggil KTP Aplikasi -->
    <link rel="manifest" href="{{ asset('manifest.json?v=3') }}">
    <meta name="theme-color" content="#065f46">
    <!-- Ikon browser / tab -->
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('icons/favicon-32x32-v2.png') }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('icons/icon-192x192-v2.png') }}">
    <!-- Ikon khusus untuk perangkat Apple / iOS -->
    <link rel="apple-touch-icon" href="{{ asset('icons/icon-192x192-v2.png') }}">

    <!-- Memanggil Asisten (Service Worker) -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js?v=14')
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
        <body data-turbo="false" class="bg-[#f4f7f6] flex h-[100dvh] overflow-hidden text-sm antialiased p-0">

            <!-- CONTAINER-SHELL: full-bleed, tanpa lengkungan & tanpa frame -->
            <div class="sb-shell flex flex-1 overflow-hidden bg-white">

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

            <aside id="sidebar" class="sb-sidebar fixed inset-y-0 left-0 z-50 flex flex-col shadow-md md:shadow-sm transform -translate-x-full md:relative md:translate-x-0 overflow-hidden">
                @php
                    $periodeAktif = \App\Models\Periode::where('is_active', true)->first();
                    $teksPeriode = $periodeAktif ? 'TA. ' . $periodeAktif->tahun_ajaran . ' (' . $periodeAktif->semester . ')' : '⚠ Periode Belum Diatur';
                @endphp
                <!-- HEADER LOGO (gaya Codinglab): kotak biru rounded + judul + subjudul -->
                <div class="sb-brand shrink-0 pl-[16px] pr-5 pt-5 pb-4">
                    <div class="sb-brand-inner">
                        <!-- KOTAK LOGO BIRU ROUNDED -->
                        <span class="sb-brand-icon">
                            <img src="{{ asset('img/logo-muadalah.png') }}" alt="Logo Mu'adalah" class="w-full h-full object-contain">
                        </span>
                        <div class="sb-brand-txt min-w-0">
                            <span class="sb-brand-title block">SPM WUSTHA</span>
                            <span class="sb-brand-sub block">{{ $teksPeriode }}</span>
                        </div>
                    </div>

                    <button onclick="toggleSidebar()" class="md:hidden text-gray-400 hover:text-red-500 focus:outline-none transition p-1 shrink-0">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <div class="sb-scroll flex-1 flex flex-col gap-1 py-2 px-2">

                    @php
                        $gBeranda  = request()->is('/', 'meja-kontrol', 'monitoring-kehadiran*', 'laporan', 'pabrik-barcode', 'agenda-kegiatan*', 'honor*');
                        $gMaster   = request()->is('master-guru*', 'master-jabatan*', 'master-pelajaran*', 'batas-pelajaran*', 'master-kelas*', 'master-siswa*', 'master-periode*');
                        $gJadwal   = request()->is('master-hari-operasional*', 'agenda-kaldik*', 'pengumuman*', 'master-plot-jadwal*', 'master-jadwal-harian*', 'riwayat-mutasi*');
                        $gGuru     = request()->is('dashboard-guru', 'jadwal-saya', 'scan-kelas', 'siswa-saya*');
                        $gSiswa    = request()->is('penempatan-siswa*', 'absen-siswa*', 'input-nilai*', 'raport*', 'laporan-siswa*', 'tagihan*');
                        $gSetup    = request()->is('setup-user', 'user*', 'master-import*', 'backup-restore*', 'panduan-aplikasi*');
                    @endphp

                    <!-- GRUP: BERANDA & MONITORING -->
                    @canany(['akses_dashboard', 'akses_meja_kontrol', 'akses_monitoring_kehadiran', 'akses_laporan', 'akses_pabrik_barcode', 'akses_agenda', 'akses_honor'])
                    <div class="sb-group {{ $gBeranda ? 'sb-open' : '' }}">
                        <div class="relative flex items-center group">
                            <button type="button" class="sb-group-toggle w-full flex items-center gap-3 p-2 rounded-xl" onclick="sbSelectGroup(this)">
                                <span class="sb-gicon h-10 w-10 flex justify-center items-center flex-shrink-0"><i class="fas fa-th-large text-xl"></i></span>
                                <span class="sb-glabel flex-1 text-left text-sm font-bold">Beranda &amp; Monitoring</span>
                                <i class="fas fa-chevron-down sb-chev text-xs ml-auto flex-shrink-0"></i>
                            </button>
                        </div>
                        <div class="sb-sub">
                            @can('akses_dashboard')
                            <div class="relative flex items-center group">
                                <a href="/dashboard-utama" class="sb-item w-full flex items-center gap-3 p-2 rounded-xl {{ request()->is('/') ? 'sb-active' : 'sb-inactive' }}">
                                    <div class="sb-icon h-10 w-10 flex justify-center items-center flex-shrink-0"><i class="fas fa-desktop text-xl"></i></div>
                                    <span class="sb-text flex-1 text-left text-sm font-semibold">Dashboard</span>
                                </a>
                            </div>
                            @endcan
                            @can('akses_meja_kontrol')
                            <div class="relative flex items-center group">
                                <a href="/meja-kontrol" class="sb-item w-full flex items-center gap-3 p-2 rounded-xl {{ request()->is('meja-kontrol') ? 'sb-active' : 'sb-inactive' }}">
                                    <div class="sb-icon h-10 w-10 flex justify-center items-center flex-shrink-0"><i class="fas fa-tv text-xl"></i></div>
                                    <span class="sb-text flex-1 text-left text-sm font-semibold">Meja Kontrol</span>
                                </a>
                            </div>
                            @endcan
                            @can('akses_monitoring_kehadiran')
                            <div class="relative flex items-center group">
                                <a href="/monitoring-kehadiran" class="sb-item w-full flex items-center gap-3 p-2 rounded-xl {{ request()->is('monitoring-kehadiran*') ? 'sb-active' : 'sb-inactive' }}">
                                    <div class="sb-icon h-10 w-10 flex justify-center items-center flex-shrink-0"><i class="fas fa-user-check text-xl"></i></div>
                                    <span class="sb-text flex-1 text-left text-sm font-semibold">Monitoring Kehadiran</span>
                                </a>
                            </div>
                            @endcan
                            @can('akses_laporan')
                            <div class="relative flex items-center group">
                                <a href="/laporan" class="sb-item w-full flex items-center gap-3 p-2 rounded-xl {{ request()->is('laporan') ? 'sb-active' : 'sb-inactive' }}">
                                    <div class="sb-icon h-10 w-10 flex justify-center items-center flex-shrink-0"><i class="fas fa-print text-xl"></i></div>
                                    <span class="sb-text flex-1 text-left text-sm font-semibold">Rekap Laporan</span>
                                </a>
                            </div>
                            @endcan
                            @can('akses_pabrik_barcode')
                            <div class="relative flex items-center group">
                                <a href="/pabrik-barcode" class="sb-item w-full flex items-center gap-3 p-2 rounded-xl {{ request()->is('pabrik-barcode') ? 'sb-active' : 'sb-inactive' }}">
                                    <div class="sb-icon h-10 w-10 flex justify-center items-center flex-shrink-0"><i class="fas fa-barcode text-xl"></i></div>
                                    <span class="sb-text flex-1 text-left text-sm font-semibold">Cetak Barcode</span>
                                </a>
                            </div>
                            @endcan
                            @can('akses_agenda')
                            <div class="relative flex items-center group">
                                <a href="/agenda-kegiatan" class="sb-item w-full flex items-center gap-3 p-2 rounded-xl {{ request()->is('agenda-kegiatan*') ? 'sb-active' : 'sb-inactive' }}">
                                    <div class="sb-icon h-10 w-10 flex justify-center items-center flex-shrink-0"><i class="fas fa-calendar-alt text-xl"></i></div>
                                    <span class="sb-text flex-1 text-left text-sm font-semibold">Agenda Kegiatan</span>
                                </a>
                            </div>
                            @endcan
                            @can('akses_honor')
                            <div class="relative flex items-center group">
                                <a href="/honor" class="sb-item w-full flex items-center gap-3 p-2 rounded-xl {{ request()->is('honor*') ? 'sb-active' : 'sb-inactive' }}">
                                    <div class="sb-icon h-10 w-10 flex justify-center items-center flex-shrink-0"><i class="fas fa-money-bill-wave text-xl"></i></div>
                                    <span class="sb-text flex-1 text-left text-sm font-semibold">Honor Guru</span>
                                </a>
                            </div>
                            @endcan
                        </div>
                    </div>
                    @endcanany

                    @canany(['akses_master_guru', 'akses_master_jabatan', 'akses_master_pelajaran', 'akses_master_kelas', 'akses_batas_pelajaran', 'akses_master_siswa', 'akses_master_periode'])
                    <!-- GRUP: MASTER DATA -->
                    <div class="sb-group {{ $gMaster ? 'sb-open' : '' }}">
                        <div class="relative flex items-center group">
                            <button type="button" class="sb-group-toggle w-full flex items-center gap-3 p-2 rounded-xl" onclick="sbSelectGroup(this)">
                                <span class="sb-gicon h-10 w-10 flex justify-center items-center flex-shrink-0"><i class="fas fa-database text-xl"></i></span>
                                <span class="sb-glabel flex-1 text-left text-sm font-bold">Master Data</span>
                                <i class="fas fa-chevron-down sb-chev text-xs ml-auto flex-shrink-0"></i>
                            </button>
                        </div>
                        <div class="sb-sub">
                            @can('akses_master_guru')
                            <div class="relative flex items-center group">
                                <a href="/master-guru" class="sb-item w-full flex items-center gap-3 p-2 rounded-xl {{ request()->is('master-guru*') ? 'sb-active' : 'sb-inactive' }}">
                                    <div class="sb-icon h-10 w-10 flex justify-center items-center flex-shrink-0"><i class="fas fa-chalkboard-teacher text-xl"></i></div>
                                    <span class="sb-text flex-1 text-left text-sm font-semibold">Master Pengurus/Guru</span>
                                </a>
                            </div>
                            @endcan
                            @can('akses_master_jabatan')
                            <div class="relative flex items-center group">
                                <a href="/master-jabatan" class="sb-item w-full flex items-center gap-3 p-2 rounded-xl {{ request()->is('master-jabatan*') ? 'sb-active' : 'sb-inactive' }}">
                                    <div class="sb-icon h-10 w-10 flex justify-center items-center flex-shrink-0"><i class="fas fa-briefcase text-xl"></i></div>
                                    <span class="sb-text flex-1 text-left text-sm font-semibold">Master Jabatan</span>
                                </a>
                            </div>
                            @endcan
                            @can('akses_master_kelas')
                            <div class="relative flex items-center group">
                                <a href="/master-kelas" class="sb-item w-full flex items-center gap-3 p-2 rounded-xl {{ request()->is('master-kelas*') ? 'sb-active' : 'sb-inactive' }}">
                                    <div class="sb-icon h-10 w-10 flex justify-center items-center flex-shrink-0"><i class="fas fa-school text-xl"></i></div>
                                    <span class="sb-text flex-1 text-left text-sm font-semibold">Master Kelas</span>
                                </a>
                            </div>
                            @endcan
                            @can('akses_master_pelajaran')
                            <div class="relative flex items-center group">
                                <a href="/master-pelajaran" class="sb-item w-full flex items-center gap-3 p-2 rounded-xl {{ request()->is('master-pelajaran*') ? 'sb-active' : 'sb-inactive' }}">
                                    <div class="sb-icon h-10 w-10 flex justify-center items-center flex-shrink-0"><i class="fas fa-book-open text-xl"></i></div>
                                    <span class="sb-text flex-1 text-left text-sm font-semibold">Master Pelajaran</span>
                                </a>
                            </div>
                            @endcan
                            @can('akses_batas_pelajaran')
                            <div class="relative flex items-center group">
                                <a href="/batas-pelajaran" class="sb-item w-full flex items-center gap-3 p-2 rounded-xl {{ request()->is('batas-pelajaran*') ? 'sb-active' : 'sb-inactive' }}">
                                    <div class="sb-icon h-10 w-10 flex justify-center items-center flex-shrink-0"><i class="fas fa-layer-group text-xl"></i></div>
                                    <span class="sb-text flex-1 text-left text-sm font-semibold">Batas Pelajaran</span>
                                </a>
                            </div>
                            @endcan
                            @can('akses_master_siswa')
                            <div class="relative flex items-center group">
                                <a href="/master-siswa" class="sb-item w-full flex items-center gap-3 p-2 rounded-xl {{ request()->is('master-siswa*') ? 'sb-active' : 'sb-inactive' }}">
                                    <div class="sb-icon h-10 w-10 flex justify-center items-center flex-shrink-0"><i class="fas fa-user-graduate text-xl"></i></div>
                                    <span class="sb-text flex-1 text-left text-sm font-semibold">Master Siswa</span>
                                </a>
                            </div>
                            @endcan
                            @can('akses_master_periode')
                            <div class="relative flex items-center group">
                                <a href="/master-periode" class="sb-item w-full flex items-center gap-3 p-2 rounded-xl {{ request()->is('master-periode*') ? 'sb-active' : 'sb-inactive' }}">
                                    <div class="sb-icon h-10 w-10 flex justify-center items-center flex-shrink-0"><i class="fas fa-calendar-check text-xl"></i></div>
                                    <span class="sb-text flex-1 text-left text-sm font-semibold">Master Periode</span>
                                </a>
                            </div>
                            @endcan
                        </div>
                    </div>
                    @endcanany

                    @canany(['akses_hari_operasional', 'akses_hari_libur', 'akses_pengumuman', 'akses_target_mengajar', 'akses_jadwal_harian', 'akses_riwayat_mutasi'])
                    <!-- GRUP: JADWAL & KALDIK -->
                    <div class="sb-group {{ $gJadwal ? 'sb-open' : '' }}">
                        <div class="relative flex items-center group">
                            <button type="button" class="sb-group-toggle w-full flex items-center gap-3 p-2 rounded-xl" onclick="sbSelectGroup(this)">
                                <span class="sb-gicon h-10 w-10 flex justify-center items-center flex-shrink-0"><i class="fas fa-calendar-alt text-xl"></i></span>
                                <span class="sb-glabel flex-1 text-left text-sm font-bold">Jadwal &amp; Kaldik</span>
                                <i class="fas fa-chevron-down sb-chev text-xs ml-auto flex-shrink-0"></i>
                            </button>
                        </div>
                        <div class="sb-sub">
                            @can('akses_hari_operasional')
                            <div class="relative flex items-center group">
                                <a href="/master-hari-operasional" class="sb-item w-full flex items-center gap-3 p-2 rounded-xl {{ request()->is('master-hari-operasional*') ? 'sb-active' : 'sb-inactive' }}">
                                    <div class="sb-icon h-10 w-10 flex justify-center items-center flex-shrink-0"><i class="fas fa-calendar-week text-xl"></i></div>
                                    <span class="sb-text flex-1 text-left text-sm font-semibold">Hari Operasional</span>
                                </a>
                            </div>
                            @endcan
                            @can('akses_hari_libur')
                            <div class="relative flex items-center group">
                                <a href="/agenda-kaldik" class="sb-item w-full flex items-center gap-3 p-2 rounded-xl {{ request()->is('agenda-kaldik*') ? 'sb-active' : 'sb-inactive' }}">
                                    <div class="sb-icon h-10 w-10 flex justify-center items-center flex-shrink-0"><i class="fas fa-calendar-times text-xl"></i></div>
                                    <span class="sb-text flex-1 text-left text-sm font-semibold">Kalender Pendidikan</span>
                                </a>
                            </div>
                            @endcan
                            @can('akses_pengumuman')
                            <div class="relative flex items-center group">
                                <a href="/pengumuman" class="sb-item w-full flex items-center gap-3 p-2 rounded-xl {{ request()->is('pengumuman*') ? 'sb-active' : 'sb-inactive' }}">
                                    <div class="sb-icon h-10 w-10 flex justify-center items-center flex-shrink-0"><i class="fas fa-bullhorn text-xl"></i></div>
                                    <span class="sb-text flex-1 text-left text-sm font-semibold">Pengumuman</span>
                                </a>
                            </div>
                            @endcan
                            @can('akses_target_mengajar')
                            <div class="relative flex items-center group">
                                <a href="/master-plot-jadwal" class="sb-item w-full flex items-center gap-3 p-2 rounded-xl {{ request()->is('master-plot-jadwal*') ? 'sb-active' : 'sb-inactive' }}">
                                    <div class="sb-icon h-10 w-10 flex justify-center items-center flex-shrink-0"><i class="fas fa-sitemap text-xl"></i></div>
                                    <span class="sb-text flex-1 text-left text-sm font-semibold">Target Mengajar</span>
                                </a>
                            </div>
                            @endcan
                            @can('akses_jadwal_harian')
                            <div class="relative flex items-center group">
                                <a href="/master-jadwal-harian" class="sb-item w-full flex items-center gap-3 p-2 rounded-xl {{ request()->is('master-jadwal-harian*') ? 'sb-active' : 'sb-inactive' }}">
                                    <div class="sb-icon h-10 w-10 flex justify-center items-center flex-shrink-0"><i class="fas fa-calendar-alt text-xl"></i></div>
                                    <span class="sb-text flex-1 text-left text-sm font-semibold">Jadwal Harian</span>
                                </a>
                            </div>
                            @endcan
                            @can('akses_riwayat_mutasi')
                            <div class="relative flex items-center group">
                                <a href="/riwayat-mutasi" class="sb-item w-full flex items-center gap-3 p-2 rounded-xl {{ request()->is('riwayat-mutasi*') ? 'sb-active' : 'sb-inactive' }}">
                                    <div class="sb-icon h-10 w-10 flex justify-center items-center flex-shrink-0"><i class="fas fa-history text-xl"></i></div>
                                    <span class="sb-text flex-1 text-left text-sm font-semibold">Riwayat Mutasi Jadwal</span>
                                </a>
                            </div>
                            @endcan
                        </div>
                    </div>
                    @endcanany

                    @canany(['akses_dashboard_guru', 'akses_jadwal_saya', 'akses_siswa_saya'])
                    <!-- GRUP: GURU -->
                    <div class="sb-group {{ $gGuru ? 'sb-open' : '' }}">
                        <div class="relative flex items-center group">
                            <button type="button" class="sb-group-toggle w-full flex items-center gap-3 p-2 rounded-xl" onclick="sbSelectGroup(this)">
                                <span class="sb-gicon h-10 w-10 flex justify-center items-center flex-shrink-0"><i class="fas fa-chalkboard-teacher text-xl"></i></span>
                                <span class="sb-glabel flex-1 text-left text-sm font-bold">Guru</span>
                                <i class="fas fa-chevron-down sb-chev text-xs ml-auto flex-shrink-0"></i>
                            </button>
                        </div>
                        <div class="sb-sub">
                            @can('akses_dashboard_guru')
                            <div class="relative flex items-center group">
                                <a href="/dashboard-guru" class="sb-item w-full flex items-center gap-3 p-2 rounded-xl {{ request()->is('dashboard-guru') ? 'sb-active' : 'sb-inactive' }}">
                                    <div class="sb-icon h-10 w-10 flex justify-center items-center flex-shrink-0"><i class="fas fa-chalkboard-teacher text-xl"></i></div>
                                    <span class="sb-text flex-1 text-left text-sm font-semibold">Beranda Guru</span>
                                </a>
                            </div>
                            @endcan
                            @can('akses_jadwal_saya')
                            <div class="relative flex items-center group">
                                <a href="/jadwal-saya" class="sb-item w-full flex items-center gap-3 p-2 rounded-xl {{ request()->is('jadwal-saya') ? 'sb-active' : 'sb-inactive' }}">
                                    <div class="sb-icon h-10 w-10 flex justify-center items-center flex-shrink-0"><i class="fas fa-calendar-check text-xl"></i></div>
                                    <span class="sb-text flex-1 text-left text-sm font-semibold">Jadwal Saya</span>
                                </a>
                            </div>
                            @endcan
                            @can('akses_jadwal_saya')
                            <div class="relative flex items-center group">
                                <a href="/scan-kelas" class="sb-item w-full flex items-center gap-3 p-2 rounded-xl {{ request()->is('scan-kelas') ? 'sb-active' : 'sb-inactive' }}">
                                    <div class="sb-icon h-10 w-10 flex justify-center items-center flex-shrink-0"><i class="fas fa-qrcode text-xl"></i></div>
                                    <span class="sb-text flex-1 text-left text-sm font-semibold">Scan Hadir</span>
                                </a>
                            </div>
                            @endcan
                            @can('akses_siswa_saya')
                            <div class="relative flex items-center group">
                                <a href="/siswa-saya" class="sb-item w-full flex items-center gap-3 p-2 rounded-xl {{ request()->is('siswa-saya*') ? 'sb-active' : 'sb-inactive' }}">
                                    <div class="sb-icon h-10 w-10 flex justify-center items-center flex-shrink-0"><i class="fas fa-user-friends text-xl"></i></div>
                                    <span class="sb-text flex-1 text-left text-sm font-semibold">Siswa Saya (Wali)</span>
                                </a>
                            </div>
                            @endcan
                        </div>
                    </div>
                    @endcanany

                    @canany(['akses_penempatan_siswa', 'akses_absen_siswa', 'akses_input_nilai', 'akses_laporan_siswa', 'akses_pembayaran'])
                    <!-- GRUP: SISWA -->
                    <div class="sb-group {{ $gSiswa ? 'sb-open' : '' }}">
                        <div class="relative flex items-center group">
                            <button type="button" class="sb-group-toggle w-full flex items-center gap-3 p-2 rounded-xl" onclick="sbSelectGroup(this)">
                                <span class="sb-gicon h-10 w-10 flex justify-center items-center flex-shrink-0"><i class="fas fa-user-graduate text-xl"></i></span>
                                <span class="sb-glabel flex-1 text-left text-sm font-bold">Siswa</span>
                                <i class="fas fa-chevron-down sb-chev text-xs ml-auto flex-shrink-0"></i>
                            </button>
                        </div>
                        <div class="sb-sub">
                            @can('akses_penempatan_siswa')
                            <div class="relative flex items-center group">
                                <a href="/penempatan-siswa" class="sb-item w-full flex items-center gap-3 p-2 rounded-xl {{ request()->is('penempatan-siswa*') ? 'sb-active' : 'sb-inactive' }}">
                                    <div class="sb-icon h-10 w-10 flex justify-center items-center flex-shrink-0"><i class="fas fa-people-arrows text-xl"></i></div>
                                    <span class="sb-text flex-1 text-left text-sm font-semibold">Penempatan Siswa</span>
                                </a>
                            </div>
                            @endcan
                            @can('akses_absen_siswa')
                            <div class="relative flex items-center group">
                                <a href="/absen-siswa" class="sb-item w-full flex items-center gap-3 p-2 rounded-xl {{ request()->is('absen-siswa*') ? 'sb-active' : 'sb-inactive' }}">
                                    <div class="sb-icon h-10 w-10 flex justify-center items-center flex-shrink-0"><i class="fas fa-clipboard-check text-xl"></i></div>
                                    <span class="sb-text flex-1 text-left text-sm font-semibold">Absensi Siswa</span>
                                </a>
                            </div>
                            @endcan
                            @can('akses_input_nilai')
                            <div class="relative flex items-center group">
                                <a href="/input-nilai" class="sb-item w-full flex items-center gap-3 p-2 rounded-xl {{ request()->is('input-nilai*') ? 'sb-active' : 'sb-inactive' }}">
                                    <div class="sb-icon h-10 w-10 flex justify-center items-center flex-shrink-0"><i class="fas fa-clipboard-list text-xl"></i></div>
                                    <span class="sb-text flex-1 text-left text-sm font-semibold">Input Nilai</span>
                                </a>
                            </div>
                            @endcan
                            @can('akses_laporan_siswa')
                            <div class="relative flex items-center group">
                                <a href="/raport" class="sb-item w-full flex items-center gap-3 p-2 rounded-xl {{ request()->is('raport*') ? 'sb-active' : 'sb-inactive' }}">
                                    <div class="sb-icon h-10 w-10 flex justify-center items-center flex-shrink-0"><i class="fas fa-file-alt text-xl"></i></div>
                                    <span class="sb-text flex-1 text-left text-sm font-semibold">Raport</span>
                                </a>
                            </div>
                            <div class="relative flex items-center group">
                                <a href="/laporan-siswa" class="sb-item w-full flex items-center gap-3 p-2 rounded-xl {{ request()->is('laporan-siswa*') ? 'sb-active' : 'sb-inactive' }}">
                                    <div class="sb-icon h-10 w-10 flex justify-center items-center flex-shrink-0"><i class="fas fa-print text-xl"></i></div>
                                    <span class="sb-text flex-1 text-left text-sm font-semibold">Laporan Siswa</span>
                                </a>
                            </div>
                            @endcan
                            @can('akses_pembayaran')
                            <div class="relative flex items-center group">
                                <a href="/tagihan" class="sb-item w-full flex items-center gap-3 p-2 rounded-xl {{ request()->is('tagihan*') ? 'sb-active' : 'sb-inactive' }}">
                                    <div class="sb-icon h-10 w-10 flex justify-center items-center flex-shrink-0"><i class="fas fa-file-invoice-dollar text-xl"></i></div>
                                    <span class="sb-text flex-1 text-left text-sm font-semibold">Tagihan &amp; Pembayaran</span>
                                </a>
                            </div>
                            @endcan
                        </div>
                    </div>
                    @endcanany

                    @canany(['akses_manajemen_user', 'akses_import_excel', 'akses_backup_restore'])
                    <!-- GRUP: PENGATURAN SISTEM -->
                    <div class="sb-group {{ $gSetup ? 'sb-open' : '' }}">
                        <div class="relative flex items-center group">
                            <button type="button" class="sb-group-toggle w-full flex items-center gap-3 p-2 rounded-xl" onclick="sbSelectGroup(this)">
                                <span class="sb-gicon h-10 w-10 flex justify-center items-center flex-shrink-0"><i class="fas fa-cog text-xl"></i></span>
                                <span class="sb-glabel flex-1 text-left text-sm font-bold">Pengaturan Sistem</span>
                                <i class="fas fa-chevron-down sb-chev text-xs ml-auto flex-shrink-0"></i>
                            </button>
                        </div>
                        <div class="sb-sub">
                            @can('akses_manajemen_user')
                            <div class="relative flex items-center group">
                                <a href="/setup-user" class="sb-item w-full flex items-center gap-3 p-2 rounded-xl {{ request()->is('setup-user') || request()->is('user*') ? 'sb-active' : 'sb-inactive' }}">
                                    <div class="sb-icon h-10 w-10 flex justify-center items-center flex-shrink-0"><i class="fas fa-users-cog text-xl"></i></div>
                                    <span class="sb-text flex-1 text-left text-sm font-semibold">Setup User</span>
                                </a>
                            </div>
                            @endcan
                            @can('akses_import_excel')
                            <div class="relative flex items-center group">
                                <a href="/master-import" class="sb-item w-full flex items-center gap-3 p-2 rounded-xl {{ request()->is('master-import*') ? 'sb-active' : 'sb-inactive' }}">
                                    <div class="sb-icon h-10 w-10 flex justify-center items-center flex-shrink-0"><i class="fas fa-file-excel text-xl"></i></div>
                                    <span class="sb-text flex-1 text-left text-sm font-semibold">Pusat Import</span>
                                </a>
                            </div>
                            @endcan
                            @can('akses_backup_restore')
                            <div class="relative flex items-center group">
                                <a href="/backup-restore" class="sb-item w-full flex items-center gap-3 p-2 rounded-xl {{ request()->is('backup-restore*') ? 'sb-active' : 'sb-inactive' }}">
                                    <div class="sb-icon h-10 w-10 flex justify-center items-center flex-shrink-0"><i class="fas fa-database text-xl"></i></div>
                                    <span class="sb-text flex-1 text-left text-sm font-semibold">Manajemen Database</span>
                                </a>
                            </div>
                            @endcan
                            @role('Administrator')
                            <div class="relative flex items-center group">
                                <a href="/panduan-aplikasi" class="sb-item w-full flex items-center gap-3 p-2 rounded-xl {{ request()->is('panduan-aplikasi*') ? 'sb-active' : 'sb-inactive' }}">
                                    <div class="sb-icon h-10 w-10 flex justify-center items-center flex-shrink-0"><i class="fas fa-book-open text-xl"></i></div>
                                    <span class="sb-text flex-1 text-left text-sm font-semibold">Panduan Aplikasi</span>
                                </a>
                            </div>
                            @endrole
                        </div>
                    </div>
                    @endcanany

                    <!-- PENCARIAN MENU (rounded, ikon kaca pembesar sejajar kolom ikon menu) -->
                    <div class="sb-search">
                        <div class="sb-search-wrap">
                            <span class="sb-search-ikon"><i class="fas fa-magnifying-glass"></i></span>
                            <input type="text" id="sb-cari" class="sb-search-input" placeholder="Cari menu..." autocomplete="off" oninput="sbFilterMenu(this.value)">
                        </div>
                    </div>

                </div>

                <!-- FOOTER SIDEBAR: USER + divider + LOGOUT + TEMA (gaya Codinglab) -->
                <div class="sb-footer shrink-0 border-t" style="border-color: var(--sb-border, #e5e7eb);">

                    <!-- USER: klik -> buka dropdown (Ganti Password + Logout) -->
                    <button id="user-trigger" onclick="toggleUserMenu(this)" type="button" class="sb-user-trigger">
                        <span class="sb-avatar"><i class="fas fa-user text-xl"></i></span>
                        <span class="sb-footer-txt flex-1 text-left min-w-0 overflow-hidden">
                            <span class="block text-[13px] font-bold text-slate-700 truncate">{{ auth()->user()->name ?? 'Nama User' }}</span>
                            <span class="block text-[10px] font-semibold text-slate-400 truncate">{{ auth()->user()->aksesLabel() }}</span>
                        </span>
                        <i class="sb-footer-chev fas fa-chevron-down text-xs text-slate-400 flex-shrink-0"></i>
                    </button>

                    <div class="sb-divider"></div>

                    <!-- TOGGLE TEMA: Dark Mode / Light Mode -->
                    <button type="button" class="sb-theme" onclick="toggleTema()">
                        <span class="sb-theme-switch-show">
                            <span class="sb-theme-switch">
                                <span class="sb-theme-knob"><i class="fas fa-moon" data-sb-tema-ikon></i></span>
                            </span>
                        </span>
                        <span class="sb-theme-icon-show" data-sb-tema-ikon-bulat><i class="fas fa-moon" data-sb-tema-ikon></i></span>
                        <span class="sb-theme-label sb-text" data-sb-tema-label>Dark Mode</span>
                    </button>
                </div>

                <!-- DROPDOWN USER (fixed overlay: identitas + Ganti Password + Logout) -->
                <div id="user-dropdown" class="hidden fixed z-[80] w-56 rounded-2xl border shadow-2xl overflow-hidden">
                    <div class="sb-ub-header p-3 border-b flex items-center gap-3 bg-gradient-to-br from-emerald-500/10 to-emerald-600/10">
                        <span class="sb-avatar !h-11 !w-11"><i class="fas fa-user text-xl"></i></span>
                        <div class="min-w-0">
                            <p class="sb-ub-judul text-sm font-bold truncate">{{ auth()->user()->name ?? 'Nama User' }}</p>
                            <p class="sb-ub-sub text-[11px] font-semibold truncate">{{ auth()->user()->aksesLabel() }}</p>
                        </div>
                    </div>
                    <div class="p-1.5">
                        <button type="button" onclick="bukaModalGantiPassword()" class="sb-drop-item">
                            <span class="sb-drop-ikon"><i class="fas fa-key"></i></span>
                            <span class="text-sm font-semibold">Ganti Password</span>
                        </button>
                        <form method="POST" action="/logout">
                            @csrf
                            <button type="submit" class="sb-drop-item sb-drop-danger">
                                <span class="sb-drop-ikon"><i class="fas fa-sign-out-alt"></i></span>
                                <span class="text-sm font-semibold">Logout</span>
                            </button>
                        </form>
                    </div>
                </div>
            </aside>
            
            <div id="sidebar-backdrop" onclick="toggleSidebar()" class="fixed inset-0 bg-gray-900 bg-opacity-50 z-40 hidden md:hidden transition-opacity duration-300"></div>

            <div class="flex-1 flex flex-col overflow-hidden">

        <main class="flex-1 overflow-y-auto p-4 md:p-8 bg-[#f8fbfa] relative">
            <!-- TOMBOL BUKA SIDEBAR (untuk mobile) -->
            @if(!request()->is('dashboard-guru', 'kaldik', 'scan-kelas', 'rekap-presensi', 'menu', 'jadwal-saya'))
            <button id="btn-buka-sidebar" onclick="toggleSidebar()" class="md:hidden w-10 h-10 mb-4 flex items-center justify-center rounded-xl bg-white text-slate-600 shadow-md border border-gray-100 hover:bg-gray-50 transition" title="Buka Menu">
                <i class="fas fa-bars text-lg"></i>
            </button>
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

    <!-- NAV-CLICK LOADING: indikator memuat tanpa menutupi halaman -->
    <div id="nav-loading">
        <div class="dots">
            <div class="dot"></div>
            <div class="dot"></div>
            <div class="dot"></div>
        </div>
        <span style="font-size:12px;font-weight:700;color:#0f172a;white-space:nowrap;">Memuat…</span>
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

        // ============ CARI MENU SIDEBAR ============
        function sbFilterMenu(q) {
            q = (q || '').trim().toLowerCase();
            document.querySelectorAll('.sb-group').forEach(function (g) {
                if (!q) { g.style.display = ''; return; }
                var teks = (g.textContent || '').toLowerCase();
                g.style.display = teks.indexOf(q) !== -1 ? '' : 'none';
            });
        }

        // ============ MODE GELAP / TERANG (per akun, disimpan via API) ============
        var TEMA_AKUN = @json(auth()->user()->tema ?? 'sistem');

        function temaSistemGelap() {
            return window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
        }

        // Apakah tema aktif adalah gelap? ('sistem' ikut preferensi perangkat)
        function temaAktif() {
            if (TEMA_AKUN === 'gelap') return true;
            if (TEMA_AKUN === 'terang') return false;
            return temaSistemGelap();
        }

        function perbaruiIkonTema(gelap) {
            document.querySelectorAll('[data-sb-tema-ikon]').forEach(function (el) {
                el.className = 'fas ' + (gelap ? 'fa-moon' : 'fa-sun');
            });
            var lbl = document.querySelector('[data-sb-tema-label]');
            if (lbl) lbl.textContent = gelap ? 'Light Mode' : 'Dark Mode';
        }

        function terapkanTema() {
            var gelap = temaAktif();
            document.documentElement.classList.toggle('dark', gelap);
            var meta = document.querySelector('meta[name="theme-color"]');
            if (meta) meta.setAttribute('content', gelap ? '#0b1220' : '#065f46');
            perbaruiIkonTema(gelap);
        }

        function toggleTema() {
            var gelap = !temaAktif();
            document.documentElement.classList.toggle('dark', gelap);
            var nilai = gelap ? 'gelap' : 'terang';
            TEMA_AKUN = nilai;
            perbaruiIkonTema(gelap);
            // Simpan preferensi ke akun
            var csrf = document.querySelector('meta[name="csrf-token"]');
            fetch('/preferensi-tema', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrf ? csrf.content : '' },
                body: new URLSearchParams('tema=' + nilai)
            }).catch(function () {});
        }

        if (!window.__temaInit) {
            window.__temaInit = 1;
            terapkanTema();
        }

        // TOGGLE DROPDOWN USER (posisikan di atas tombol di footer sidebar)
        function toggleUserMenu(btn) {
            const menu = document.getElementById('user-dropdown');
            if (!menu) return;
            if (menu.classList.contains('hidden')) {
                menu.classList.remove('hidden');
                const r = btn.getBoundingClientRect();
                const w = 224; // 14rem (w-56)
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

        // SAAT SIDEBAR TERLIPAT (<200px), tutup otomatis popup user
        (function() {
            const side = document.getElementById('sidebar');
            if (side && typeof ResizeObserver === 'function') {
                new ResizeObserver(function() {
                    if (side.getBoundingClientRect().width < 200) {
                        const menu = document.getElementById('user-dropdown');
                        if (menu) menu.classList.add('hidden');
                    }
                }).observe(side);
            }
        })();

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
                if (link.hasAttribute('data-lazy')) return;
                if (link.hasAttribute('download')) return;
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

    @if(session('sukses'))
    <script>
        window.addEventListener('DOMContentLoaded', function () {
            var pesan = @json(session('sukses'));
            if (pesan && window.tampilNotif) window.tampilNotif('success', 'Sukses', pesan);
        });
    </script>
    @endif

    @if(session('error'))
    <script>
        window.addEventListener('DOMContentLoaded', function () {
            var pesan = @json(session('error'));
            if (pesan && window.tampilNotif) window.tampilNotif('error', 'Gagal', pesan);
        });
    </script>
    @endif

    @if($errors->any())
    <script>
        window.addEventListener('DOMContentLoaded', function () {
            if (window.tampilNotif) window.tampilNotif('error', 'Gagal Menyimpan', @json(implode('; ', $errors->all())));
        });
    </script>
    @endif

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



