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

        /* OFFLINE BANNER */
        #offline-banner {
            position: fixed; top: 0; left: 0; right: 0; z-index: 60;
            transform: translateY(-100%);
            transition: transform 0.35s ease;
        }
        #offline-banner.show {
            transform: translateY(0);
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
                navigator.serviceWorker.register('/sw.js?v=7')
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

            <!-- OFFLINE BANNER -->
            <div id="offline-banner" class="bg-gradient-to-r from-amber-500 to-red-500 text-white shadow-lg shadow-red-300/40">
                <div class="flex items-center gap-3 px-4 py-2.5">
                    <i class="fas fa-wifi-slash text-sm"></i>
                    <div class="flex-1">
                        <p class="text-xs font-bold">Anda sedang offline</p>
                        <p class="text-[10px] text-white/80">Data yang tampil adalah data terakhir saat online</p>
                    </div>
                </div>
            </div>

            <aside id="sidebar" class="fixed inset-y-0 left-0 z-50 w-64 bg-white border-r border-gray-200 flex flex-col shadow-2xl md:shadow-sm transform -translate-x-full md:relative md:translate-x-0 transition-transform duration-300 ease-in-out">
                <div class="h-16 flex items-center justify-between px-6 border-b border-gray-100">
                    <div class="flex items-center">
                        <span class="text-green-600 text-2xl mr-3"><i class="fas fa-mosque"></i></span>
                        <span class="font-bold text-lg text-gray-800">Muadalah Wustha</span>
                    </div>
                    <button onclick="toggleSidebar()" class="md:hidden text-gray-400 hover:text-red-500 focus:outline-none transition p-1">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                
                <div class="flex-1 overflow-y-auto py-4">
                    <div class="px-6 mb-2 text-xs font-bold text-green-600 uppercase tracking-wider">Menu Utama</div>
                    <nav class="space-y-1 pb-6">
                        @can('akses_dashboard')
                        <a href="/dashboard-utama" class="flex items-center px-6 py-3 {{ request()->is('/') ? 'bg-green-50 text-green-700 border-r-4 border-green-600 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-green-600 transition font-medium' }}">
                            <i class="fas fa-desktop w-6"></i> Dashboard
                        </a>
                        @endcan

                        @can('akses_dashboard_guru')
                        <a href="/dashboard-guru" class="flex items-center px-6 py-3 {{ request()->is('dashboard-guru') ? 'bg-green-50 text-green-700 border-r-4 border-green-600 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-green-600 transition font-medium' }}">
                            <i class="fas fa-chalkboard-teacher w-6"></i> Beranda Guru
                        </a>
                        @endcan
                        
                        @can('akses_meja_kontrol')
                        <a href="/meja-kontrol" class="flex items-center px-6 py-3 {{ request()->is('meja-kontrol') ? 'bg-green-50 text-green-700 border-r-4 border-green-600 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-green-600 transition font-medium' }}">
                            <i class="fas fa-tv w-6"></i> Meja Kontrol
                        </a>
                        @endcan

                        @can('akses_laporan')
                        <a href="/laporan" class="flex items-center px-6 py-3 {{ request()->is('laporan') ? 'bg-green-50 text-green-700 border-r-4 border-green-600 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-green-600 transition font-medium' }}">
                            <i class="fas fa-print w-6"></i> Rekap Laporan
                        </a>
                        @endcan

                        @can('akses_jadwal_saya')
                        <a href="/jadwal-saya" class="flex items-center px-6 py-3 {{ request()->is('jadwal-saya') ? 'bg-green-50 text-green-700 border-r-4 border-green-600 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-green-600 transition font-medium' }}">
                            <i class="fas fa-calendar-check w-6"></i> Jadwal Saya
                        </a>
                        @endcan

                        @can('akses_jadwal_saya')
                        <a href="/pabrik-barcode" class="flex items-center px-6 py-3 {{ request()->is('pabrik-barcode') ? 'bg-green-50 text-green-700 border-r-4 border-green-600 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-green-600 transition font-medium' }}">
                            <i class="fas fa-calendar-check w-6"></i> Cetak Barcode
                        </a>
                        @endcan

                        @can('akses_jadwal_saya')
                        <a href="/scan-kelas" class="flex items-center px-6 py-3 {{ request()->is('scan-kelas') ? 'bg-green-50 text-green-700 border-r-4 border-green-600 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-green-600 transition font-medium' }}">
                            <i class="fas fa-calendar-check w-6"></i> Scan Hadir
                        </a>
                        @endcan

                        <!-- Menu Agenda & Kehadiran Kegiatan -->
                        <a href="/agenda-kegiatan" class="flex items-center px-6 py-3 {{ request()->is('agenda-kegiatan*') ? 'bg-green-50 text-green-700 border-r-4 border-green-600 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-green-600 transition font-medium' }}">
                            <i class="fas fa-calendar-check w-6"></i> Agenda Kegiatan
                        </a>
                    </nav>

                    @canany(['akses_master_guru', 'akses_master_pelajaran', 'akses_master_kelas'])
                    <div class="px-6 mb-2 text-xs font-bold text-green-600 uppercase tracking-wider border-t border-gray-100 pt-4">Basis Data Master</div>
                    <nav class="space-y-1 pb-6">
                        @can('akses_master_guru')
                        <a href="/master-guru" class="flex items-center px-6 py-3 {{ request()->is('master-guru*') ? 'bg-green-50 text-green-700 border-r-4 border-green-600 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-green-600 transition font-medium' }}">
                            <i class="fas fa-chalkboard-teacher w-6"></i> Master Guru
                        </a>
                        @endcan
                        
                        @can('akses_master_pelajaran')
                        <a href="/master-pelajaran" class="flex items-center px-6 py-3 {{ request()->is('master-pelajaran*') ? 'bg-green-50 text-green-700 border-r-4 border-green-600 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-green-600 transition font-medium' }}">
                            <i class="fas fa-book-open w-6"></i> Master Pelajaran
                        </a>
                        @endcan

                        @can('akses_master_pelajaran')
                        <a href="/batas-pelajaran" class="flex items-center px-6 py-3 {{ request()->is('batas-pelajaran*') ? 'bg-green-50 text-green-700 border-r-4 border-green-600 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-green-600 transition font-medium' }}">
                            <i class="fas fa-book-open w-6"></i> Batas Pelajaran
                        </a>
                        @endcan
                        
                        @can('akses_master_kelas')
                        <a href="/master-kelas" class="flex items-center px-6 py-3 {{ request()->is('master-kelas*') ? 'bg-green-50 text-green-700 border-r-4 border-green-600 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-green-600 transition font-medium' }}">
                            <i class="fas fa-school w-6"></i> Master Kelas
                        </a>
                        @endcan
                        
                        <!-- TAMBAHAN: TOMBOL PUSAT IMPORT -->
                        @can('akses_master_guru')
                        <a href="/master-import" class="flex items-center px-6 py-3 {{ request()->is('master-import*') ? 'bg-indigo-50 text-indigo-700 border-r-4 border-indigo-600 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-indigo-600 transition font-medium' }}">
                            <i class="fas fa-file-excel w-6"></i> Pusat Import
                        </a>
                        @endcan
                    </nav>
                    @endcanany

                    @canany(['akses_master_periode', 'akses_hari_libur', 'akses_hari_operasional', 'akses_target_mengajar', 'akses_jadwal_harian'])
                    <div class="px-6 mb-2 text-xs font-bold text-green-600 uppercase tracking-wider border-t border-gray-100 pt-4">Akademik & Jadwal</div>
                    <nav class="space-y-1 pb-6">
                        @can('akses_master_periode')
                        <a href="/master-periode" class="flex items-center px-6 py-3 {{ request()->is('master-periode*') ? 'bg-green-50 text-green-700 border-r-4 border-green-600 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-green-600 transition font-medium' }}">
                            <i class="fas fa-calendar-check w-6"></i> Master Periode
                        </a>
                        @endcan
                        @can('akses_hari_libur')
                        <a href="/agenda-kaldik" class="flex items-center px-6 py-3 {{ request()->is('agenda-kaldik*') ? 'bg-green-50 text-green-700 border-r-4 border-green-600 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-green-600 transition font-medium' }}">
                            <i class="fas fa-calendar-times w-6"></i> Kalender Pendidikan
                        </a>
                        @endcan
                        @can('akses_hari_operasional')
                        <a href="/master-hari-operasional" class="flex items-center px-6 py-3 {{ request()->is('master-hari-operasional*') ? 'bg-green-50 text-green-700 border-r-4 border-green-600 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-green-600 transition font-medium' }}">
                            <i class="fas fa-calendar-week w-6"></i> Hari Operasional
                        </a>
                        @endcan
                        @can('akses_target_mengajar')
                        <a href="/master-plot-jadwal" class="flex items-center px-6 py-3 {{ request()->is('master-plot-jadwal*') ? 'bg-green-50 text-green-700 border-r-4 border-green-600 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-green-600 transition font-medium' }}">
                            <i class="fas fa-sitemap w-6"></i> Target Mengajar
                        </a>
                        @endcan
                        @can('akses_jadwal_harian')
                        <a href="/master-jadwal-harian" class="flex items-center px-6 py-3 {{ request()->is('master-jadwal-harian*') ? 'bg-green-50 text-green-700 border-r-4 border-green-600 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-green-600 transition font-medium' }}">
                            <i class="fas fa-calendar-alt w-6"></i> Jadwal Harian
                        </a>
                        @endcan
                    </nav>
                    @endcanany
                    
                    @canany(['akses_manajemen_user', 'akses_manajemen_akses'])
                    <div class="px-6 py-2 text-xs font-bold text-gray-400 uppercase tracking-wider border-t border-gray-100 pt-4">Setup & Lainnya</div>
                    <nav class="space-y-1">
                        @can('akses_manajemen_user')
                        <a href="/setup-user" class="flex items-center px-6 py-3 {{ request()->is('setup-user') || request()->is('user*') ? 'bg-green-50 text-green-700 border-r-4 border-green-600 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-green-600 transition font-medium' }}">
                            <i class="fas fa-users-cog w-6"></i> Setup User
                        </a>
                        @endcan
                        @can('akses_manajemen_akses')
                        <a href="/manajemen-akses" class="flex items-center px-6 py-3 {{ request()->is('manajemen-akses') ? 'bg-green-50 text-green-700 border-r-4 border-green-600 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-green-600 transition font-medium' }}">
                            <i class="fas fa-key w-6 text-center"></i> Hak Akses
                        </a>
                        @endcan

                        <!-- Menu Sidebar: Manajemen Database -->
                        <a href="/backup-restore" class="flex items-center px-4 py-3 text-sm font-bold rounded-xl transition-all {{ request()->is('backup-restore*') ? 'bg-indigo-600 text-white shadow-[0_4px_15px_-3px_rgba(79,70,229,0.4)]' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center mr-3 {{ request()->is('backup-restore*') ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500' }}">
                                <i class="fas fa-database text-sm"></i>
                            </div>
                            <span>Manajemen Database</span>
                        </a>
                    </nav>
                    @endcanany
                </div>
            </aside>

            <div id="sidebar-backdrop" onclick="toggleSidebar()" class="fixed inset-0 bg-gray-900 bg-opacity-50 z-40 hidden md:hidden transition-opacity duration-300"></div>

            <div class="flex-1 flex flex-col overflow-hidden">
                
                <header class="sticky top-0 z-50 h-16 bg-white border-b border-gray-200 flex items-center justify-between px-4 md:px-6 shadow-sm w-full">

            <div class="flex items-center space-x-3">
                <button onclick="toggleSidebar()" type="button" class="md:hidden text-gray-600 hover:text-green-600 hover:bg-green-50 p-2 rounded-xl focus:outline-none transition">
                    <i class="fas fa-bars text-xl"></i>
                </button>
                <div class="hidden md:block text-gray-600 font-medium">
                    Assalamu'alaikum, Selamat datang di Muadalah Wustha Maqna'ul Ulum!
                </div>
                
                @php
                    $periodeAktif = \App\Models\Periode::where('is_active', true)->first();
                    $teksPeriode = $periodeAktif ? 'TA. ' . $periodeAktif->tahun_ajaran . ' (' . $periodeAktif->semester . ')' : '⚠ Periode Belum Diatur';
                    $warnaPeriode = $periodeAktif ? 'bg-indigo-50 text-indigo-700 border-indigo-200' : 'bg-red-50 text-red-700 border-red-200 animate-pulse';
                @endphp
                <div class="hidden lg:flex ml-4 px-3 py-1.5 rounded-lg text-xs font-bold border {{ $warnaPeriode }} items-center shadow-sm">
                    <i class="fas fa-calendar-check mr-2"></i> {{ $teksPeriode }}
                </div>

                <div class="flex items-center md:hidden">
                    <span class="text-green-600 text-xl mr-2"><i class="fas fa-mosque"></i></span>
                    <span class="font-bold text-gray-800 text-base tracking-tight">Muadalah</span>
                </div>
            </div>

            <div class="flex items-center space-x-4 text-gray-600 relative">
                <button class="hover:text-green-600 transition hidden sm:block"><i class="fas fa-bell text-lg"></i></button>

                <div class="relative">
                    <button onclick="toggleUserMenu()" class="flex items-center space-x-2 focus:outline-none hover:bg-gray-50 p-1 rounded-lg transition">
                        <div class="text-right hidden md:block">
                            <p class="text-xs text-gray-500 font-bold uppercase tracking-wider">{{ auth()->user()->role ?? 'ADMIN INSTANSI' }}</p>
                            <p class="text-sm font-bold text-green-700">{{ auth()->user()->name ?? 'Nama User' }}</p>
                        </div>
                        <div class="h-9 w-9 rounded-full bg-green-600 flex items-center justify-center text-white shadow-sm border-2 border-white">
                            <i class="fas fa-user"></i>
                        </div>
                        <i class="fas fa-chevron-down text-xs text-gray-400"></i>
                    </button>

                    <div id="user-dropdown" class="hidden absolute right-0 mt-2 w-64 bg-white rounded-xl shadow-2xl border border-gray-100 z-50 overflow-hidden transform transition-all">
                        <div class="p-4 border-b border-gray-100 flex items-center space-x-3 bg-gray-50">
                            <div class="h-12 w-12 rounded-full bg-gray-700 flex items-center justify-center text-white flex-shrink-0 shadow-inner">
                                <i class="fas fa-user text-xl"></i>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-gray-800">{{ auth()->user()->name ?? 'Nama User' }}</p>
                                <p class="text-xs text-gray-500 font-semibold">{{ auth()->user()->role ?? 'Role' }}</p>
                            </div>
                        </div>

                        <div class="p-2">
                            <button type="button" onclick="bukaModalGantiPassword()" class="w-full flex items-center px-4 py-2.5 text-sm font-semibold text-gray-600 hover:bg-gray-50 rounded-lg transition text-left cursor-pointer">
                                <i class="fas fa-key w-6 text-gray-400"></i> Ganti Password
                            </button>
                        </div>
                        
                        <div class="p-3 border-t border-gray-100 bg-gray-50">
                            <form method="POST" action="/logout">
                                @csrf
                                <button type="submit" class="flex items-center justify-center w-full px-4 py-2 text-sm font-bold text-white bg-red-500 hover:bg-red-600 rounded-lg transition shadow-sm cursor-pointer">
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

        // OFFLINE BANNER: tampilkan saat koneksi putus
        (function() {
            var banner = document.getElementById('offline-banner');
            if (!banner) return;
            function setState() {
                if (!navigator.onLine) {
                    banner.classList.add('show');
                } else {
                    banner.classList.remove('show');
                }
            }
            window.addEventListener('online', setState);
            window.addEventListener('offline', setState);
            setState();
        })();

        // =====================================================
        // GUARD OFFLINE GLOBAL:
        //   - wajibOnline(event): mencegah aksi menu saat offline,
        //     namun elemen tetap terlihat (tidak disembunyikan).
        //   - Cek navigator.onLine dan tampilkan toast bila offline.
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

        // Blocker global untuk tautan/aksi offline (bottom-nav & kartu menu)
        // Menangkap klik secara delegasi agar tetap berfungsi saat navigasi Turbo
        window.addEventListener('click', function(e) {
            if (navigator.onLine) return;
            var el = e.target && e.target.closest ? e.target.closest('[data-offline-block]') : null;
            if (!el) return;
            e.preventDefault();
            e.stopPropagation();
            tampilToastOffline(el.getAttribute('data-offline-msg') || 'Fitur memerlukan koneksi internet');
        }, true);

    </script>

    @stack('scripts')
</body>


</html>