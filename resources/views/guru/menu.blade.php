@extends('layouts.app')
@section('title', 'Menu - SmartPesantren')
@section('content')
<style>
    header, aside { display: none !important; }
    main { padding: 0 !important; background-color: #f8fafc !important; overflow: hidden !important; }
    body { overflow: hidden !important; background-color: #f8fafc !important; }
    .scrollbar-none::-webkit-scrollbar { display: none; }
    .scrollbar-none { -ms-overflow-style: none; scrollbar-width: none; }
    @supports (padding-bottom: env(safe-area-inset-bottom)) { .pb-safe { padding-bottom: env(safe-area-inset-bottom); } }
</style>

<div class="max-w-md mx-auto h-[100dvh] bg-slate-50 flex flex-col relative font-sans overflow-hidden">
    
    <!-- BANNER ATAS (Konsisten 100% dengan Kaldik & Rekap) -->
    <div class="shrink-0 bg-gradient-to-br from-emerald-600 to-teal-700 px-6 pt-8 pb-6 rounded-b-[2.5rem] shadow-md flex justify-between items-center relative z-20">
        <div class="flex-1 min-w-0 relative z-10">
            <h2 class="text-2xl font-black text-white tracking-tight drop-shadow-md truncate">Menu Sistem</h2>
            <p class="text-emerald-100 text-xs font-medium mt-1">Pengaturan & Keamanan Akun</p>
        </div>
        <div class="shrink-0 bg-white/20 backdrop-blur-md border border-white/20 px-3 py-1.5 rounded-2xl flex flex-col items-center shadow-lg ml-3 relative z-10">
            <span class="text-[9px] uppercase tracking-wider text-emerald-100 font-bold mb-0.5">AKSES</span>
            <span class="text-xs font-black text-white">GURU</span>
        </div>
    </div>

    <!-- AREA KONTEN -->
    <div class="flex-1 overflow-y-auto bg-slate-50 relative z-10 pt-6 scrollbar-none px-5">
        
        <div class="space-y-3">
            
            <!-- MENU 1: Profil Saya -->
            <a href="{{ route('guru.profil.lengkap') }}" class="w-full flex items-center justify-between bg-white p-4 rounded-2xl active:scale-[0.98] transition-all shadow-[0_4px_15px_rgba(0,0,0,0.03)] border border-slate-100 hover:border-emerald-200 group">
                <div class="flex items-center space-x-4">
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600 group-hover:bg-emerald-600 group-hover:text-white transition-colors">
                        <i class="fas fa-user text-lg"></i>
                    </div>
                    <span class="text-sm font-bold text-slate-700">Profil Saya</span>
                </div>
                <i class="fas fa-chevron-right text-slate-300 text-xs"></i>
            </a>

            <!-- MENU 2: Tentang Aplikasi -->
            <button onclick="bukaModal('modal-tentang', 'bg-tentang', 'box-tentang')" class="w-full flex items-center justify-between bg-white p-4 rounded-2xl active:scale-[0.98] transition-all shadow-[0_4px_15px_rgba(0,0,0,0.03)] border border-slate-100 hover:border-emerald-200 group">
                <div class="flex items-center space-x-4">
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600 group-hover:bg-emerald-600 group-hover:text-white transition-colors">
                        <i class="fas fa-info-circle text-lg"></i>
                    </div>
                    <span class="text-sm font-bold text-slate-700">Tentang Aplikasi</span>
                </div>
                <i class="fas fa-chevron-right text-slate-300 text-xs"></i>
            </button>

            <!-- MENU 3: Ganti Password (Memanggil Fungsi Global dari app.blade.php) -->
            <button onclick="bukaModalGantiPassword()" class="w-full flex items-center justify-between bg-white p-4 rounded-2xl active:scale-[0.98] transition-all shadow-[0_4px_15px_rgba(0,0,0,0.03)] border border-slate-100 hover:border-emerald-200 group">
                <div class="flex items-center space-x-4">
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600 group-hover:bg-emerald-600 group-hover:text-white transition-colors">
                        <i class="fas fa-lock text-lg"></i>
                    </div>
                    <span class="text-sm font-bold text-slate-700">Ganti Password</span>
                </div>
                <i class="fas fa-chevron-right text-slate-300 text-xs"></i>
            </button>

            <!-- MENU 4: Logout (Proteksi CSRF + Loading Animasi Premium) -->
            <form method="POST" action="/logout" class="w-full mt-8" onsubmit="loadingElegan(event, this)">
                @csrf
                <!-- Ditambahkan min-h-[72px] untuk memastikan tinggi kotak terkunci mutlak -->
                <button id="btn-logout" type="submit" class="relative w-full flex items-center justify-between bg-white p-4 rounded-2xl active:scale-[0.98] transition-all duration-300 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100 hover:border-rose-100 hover:shadow-[0_8px_20px_rgba(225,29,72,0.08)] group cursor-pointer text-left overflow-hidden min-h-[72px]">
                    
                    <!-- Aksen Garis Merah Dinamis -->
                    <div class="absolute left-0 top-1/4 bottom-1/4 w-1 bg-rose-500 rounded-r-md transform -translate-x-full group-hover:translate-x-0 transition-transform duration-300"></div>

                    <div class="flex items-center space-x-4 pl-1 group-hover:pl-2 transition-all duration-300">
                        <div id="box-ikon-logout" class="w-10 h-10 rounded-xl bg-slate-50 flex items-center justify-center text-slate-400 group-hover:bg-rose-50 group-hover:text-rose-600 transition-colors duration-300">
                            <i id="ikon-logout" class="fas fa-power-off text-lg transition-transform"></i>
                        </div>
                        <span id="teks-logout" class="text-sm font-bold text-slate-700 group-hover:text-rose-600 transition-colors duration-300">Keluar Sistem</span>
                    </div>
                    
                    <div id="ikon-panah-logout" class="w-8 h-8 rounded-full flex items-center justify-center bg-transparent group-hover:bg-rose-50 transition-colors duration-300">
                        <i class="fas fa-sign-out-alt text-slate-300 text-xs group-hover:text-rose-500 transform group-hover:translate-x-0.5 transition-all duration-300"></i>
                    </div>
                </button>
            </form>

            <!-- Script Khusus Bypass Loading Global -->
            <script>
                function loadingElegan(event, form) {
                    // 1. Hentikan propagasi agar TIDAK DITANGKAP oleh script loading global yang merusak desain
                    event.preventDefault();
                    event.stopPropagation();

                    // 2. Ambil elemen-elemen di dalam tombol
                    let btn = document.getElementById('btn-logout');
                    let boxIkon = document.getElementById('box-ikon-logout');
                    let ikon = document.getElementById('ikon-logout');
                    let teks = document.getElementById('teks-logout');
                    let panah = document.getElementById('ikon-panah-logout');

                    // 3. Terapkan efek loading khas UI Premium (Elegan, struktur kotak tetap utuh)
                    btn.classList.add('opacity-80', 'cursor-wait', 'pointer-events-none');
                    btn.classList.remove('hover:border-rose-100', 'hover:shadow-[0_8px_20px_rgba(225,29,72,0.08)]');
                    
                    // Ubah box ikon menjadi merah lembut dan putar ikonnya
                    boxIkon.classList.replace('bg-slate-50', 'bg-rose-100');
                    boxIkon.classList.replace('text-slate-400', 'text-rose-500');
                    ikon.className = 'fas fa-circle-notch fa-spin text-lg';
                    
                    // Ubah teksnya dengan mulus
                    teks.innerText = 'Mengamankan sesi...';
                    teks.classList.remove('text-slate-700');
                    teks.classList.add('text-rose-600');

                    // Sembunyikan panah ujung dengan transisi pudar (fade-out)
                    panah.style.opacity = '0';

                    // 4. Lanjutkan proses logout secara asli setelah efek visual terlihat
                    setTimeout(() => {
                        form.submit();
                    }, 400);
                }
            </script>

        </div>
    </div>

    <!-- NAVIGASI BAWAH -->
    <div class="shrink-0 z-40 w-full bg-white/95 backdrop-blur-xl border-t border-slate-100 shadow-[0_-10px_25px_rgba(0,0,0,0.06)] px-4 py-2 flex justify-between items-end pb-safe pt-2">
        <a href="/dashboard-guru" class="flex flex-col items-center justify-center w-12 text-slate-400 hover:text-emerald-500 transition pb-1"><i class="fas fa-home text-xl mb-1"></i><span class="text-[9px] font-bold">Beranda</span></a>
        <a href="/kaldik" class="flex flex-col items-center justify-center w-12 text-slate-400 hover:text-emerald-500 transition pb-1"><i class="fas fa-calendar-alt text-xl mb-1"></i><span class="text-[9px] font-bold">Kaldik</span></a>
        <div class="relative -top-6 flex justify-center items-center"><a href="/scan-kelas" class="w-16 h-16 rounded-full bg-gradient-to-b from-emerald-400 to-emerald-600 text-white flex items-center justify-center text-3xl shadow-[0_8px_20px_rgba(16,185,129,0.4)] border-4 border-slate-50 transform hover:scale-105 active:scale-95 transition-all"><i class="fas fa-qrcode"></i></a></div>
        <a href="/rekap-presensi" class="flex flex-col items-center justify-center w-12 text-slate-400 hover:text-emerald-500 transition pb-1"><i class="fas fa-file-invoice text-xl mb-1"></i><span class="text-[9px] font-bold">Rekap</span></a>
        <a href="/menu" class="flex flex-col items-center justify-center w-12 text-emerald-600 pb-1"><i class="fas fa-bars text-xl mb-1"></i><span class="text-[9px] font-black">Menu</span></a>
    </div>

    <!-- ========================================== -->
    <!-- MODAL LOKAL (PROFIL, LOGOUT, TENTANG) -->
    <!-- ========================================== -->

    
    <!-- MODAL 2: LOGOUT -->
    <div id="modal-logout" class="fixed inset-0 z-[100] hidden">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity opacity-0" id="bg-logout" onclick="tutupModal('modal-logout', 'bg-logout', 'box-logout')"></div>
        <div class="flex items-center justify-center min-h-screen px-4 pb-10">
            <div class="bg-white w-full max-w-sm rounded-[2rem] p-6 shadow-2xl transform scale-95 opacity-0 transition-all duration-300 flex flex-col items-center text-center" id="box-logout">
                <div class="w-16 h-16 rounded-full bg-rose-100 flex items-center justify-center text-rose-500 mb-4 text-3xl">
                    <i class="fas fa-power-off"></i>
                </div>
                <h3 class="text-xl font-black text-slate-800 mb-2">Keluar Aplikasi?</h3>
                <p class="text-sm font-medium text-slate-500 mb-6 px-4">Sesi Anda akan diakhiri dan Anda harus login kembali untuk masuk.</p>
                
                <div class="flex w-full space-x-3">
                    <button onclick="tutupModal('modal-logout', 'bg-logout', 'box-logout')" class="flex-1 py-3.5 bg-slate-100 hover:bg-slate-200 text-slate-600 text-sm font-bold rounded-xl transition">Batal</button>
                    <form action="{{ route('logout') }}" method="POST" class="flex-1">
                        @csrf
                        <button type="submit" class="w-full py-3.5 bg-rose-500 hover:bg-rose-600 text-white text-sm font-bold rounded-xl shadow-md shadow-rose-500/30 transition">Ya, Logout</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL 3: TENTANG APLIKASI -->
    <div id="modal-tentang" class="fixed inset-0 z-[100] hidden">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity opacity-0" id="bg-tentang" onclick="tutupModal('modal-tentang', 'bg-tentang', 'box-tentang')"></div>
        <div class="flex items-center justify-center min-h-screen px-4 pb-10">
            <div class="bg-white w-full max-w-sm rounded-[2rem] p-6 shadow-2xl transform scale-95 opacity-0 transition-all duration-300 flex flex-col items-center text-center" id="box-tentang">
                <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-emerald-400 to-teal-600 shadow-lg flex items-center justify-center text-white text-4xl mb-4 border-4 border-emerald-50">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <h3 class="text-xl font-black text-slate-800">SmartPesantren</h3>
                <p class="text-[10px] font-bold text-emerald-600 uppercase tracking-widest mt-1 mb-4">Versi 2.0.0</p>
                
                <div class="bg-slate-50 rounded-xl p-4 text-xs text-slate-500 font-medium mb-6 w-full text-left leading-relaxed border border-slate-100">
                    <p class="mb-2">Sistem Informasi Akademik dan Manajemen Pesantren terpadu.</p>
                    <p class="mt-3 text-center text-[10px] font-bold text-slate-400">&copy; {{ date('Y') }} Sancod Builder.</p>
                </div>
                <button onclick="tutupModal('modal-tentang', 'bg-tentang', 'box-tentang')" class="w-full py-3.5 bg-slate-800 hover:bg-slate-900 text-white text-sm font-bold rounded-xl transition shadow-md">Tutup Panel</button>
            </div>
        </div>
    </div>

</div>

<!-- SCRIPT PENGENDALI MODAL LOKAL -->
<script>
    function bukaModal(modalId, bgId, boxId) {
        let modal = document.getElementById(modalId);
        let bg = document.getElementById(bgId);
        let box = document.getElementById(boxId);
        
        modal.classList.remove('hidden');
        setTimeout(() => {
            bg.classList.remove('opacity-0');
            box.classList.remove('opacity-0', 'scale-95');
        }, 10);
    }

    function tutupModal(modalId, bgId, boxId) {
        let bg = document.getElementById(bgId);
        let box = document.getElementById(boxId);
        
        bg.classList.add('opacity-0');
        box.classList.add('opacity-0', 'scale-95');
        setTimeout(() => {
            document.getElementById(modalId).classList.add('hidden');
        }, 300);
    }
</script>
@endsection