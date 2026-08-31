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

<div data-turbo="true" class="max-w-md mx-auto h-[100dvh] bg-slate-50 flex flex-col relative font-sans overflow-hidden">

    <!-- HEADER MODERN -->
    <div class="shrink-0 bg-white px-5 pt-7 pb-5 border-b border-slate-100 relative z-20">
        <div class="flex items-center justify-between gap-3">
            <div class="min-w-0">
                <h2 class="text-2xl font-black text-slate-900 tracking-tight">Menu</h2>
                <p class="text-xs font-semibold text-slate-400 mt-1">Pengaturan &amp; Fitur Akun</p>
            </div>
            <div class="shrink-0 px-3 py-1.5 rounded-full bg-emerald-50 border border-emerald-100">
                <span class="text-[10px] font-black text-emerald-600 uppercase tracking-wider">AKSES GURU</span>
            </div>
        </div>
    </div>

    <!-- AREA KONTEN -->
    <div class="flex-1 overflow-y-auto bg-slate-50 relative z-10 pt-6 pb-32 scrollbar-none px-5">

        <div class="space-y-6">

            @can('akses_dashboard')
            <a href="/dashboard-utama" class="w-full flex items-center justify-between bg-gradient-to-br from-emerald-600 to-teal-600 p-4 rounded-3xl active:scale-[0.98] transition-all shadow-[0_12px_28px_-10px_rgba(16,185,129,0.5)] group">
                <div class="flex items-center space-x-3.5">
                    <div class="w-10 h-10 rounded-2xl bg-white/20 backdrop-blur-md flex items-center justify-center text-white group-hover:scale-110 transition-transform">
                        <i class="fas fa-desktop text-lg"></i>
                    </div>
                    <div class="min-w-0">
                        <span class="block text-sm font-black text-white">Dashboard Utama</span>
                        <span class="block text-[10px] font-bold text-emerald-100 mt-0.5">Panel Admin &amp; TU</span>
                    </div>
                </div>
                <i class="fas fa-chevron-right text-white/70 text-xs"></i>
            </a>
            @endcan

            <!-- ===== GRUP: FITUR UTAMA ===== -->
            <div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1 mb-2.5">Fitur Utama</p>
                <div class="bg-white rounded-3xl border border-slate-100 shadow-[0_4px_18px_-10px_rgba(2,6,23,0.06)] overflow-hidden divide-y divide-slate-100">
                    <a href="/jadwal-saya" class="flex items-center gap-3.5 px-4 py-3.5 w-full active:bg-slate-50 transition group">
                        <span class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center flex-shrink-0 group-active:scale-95 transition-transform"><i class="fas fa-calendar-days text-base"></i></span>
                        <span class="flex-1 min-w-0">
                            <span class="block text-sm font-bold text-slate-800">Jadwal Saya</span>
                            <span class="block text-[11px] font-semibold text-slate-400 mt-0.5">Jadwal mengajar mingguan &amp; rekap</span>
                        </span>
                        <i class="fas fa-chevron-right text-slate-300 text-xs"></i>
                    </a>
                    <a href="/agenda-kegiatan" class="flex items-center gap-3.5 px-4 py-3.5 w-full active:bg-slate-50 transition group">
                        <span class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center flex-shrink-0 group-active:scale-95 transition-transform"><i class="fas fa-calendar-day text-base"></i></span>
                        <span class="flex-1 min-w-0">
                            <span class="block text-sm font-bold text-slate-800">Agenda Kegiatan</span>
                            <span class="block text-[11px] font-semibold text-slate-400 mt-0.5">Kegiatan &amp; kehadiran agenda</span>
                        </span>
                        <i class="fas fa-chevron-right text-slate-300 text-xs"></i>
                    </a>
                    <a href="/kaldik" class="flex items-center gap-3.5 px-4 py-3.5 w-full active:bg-slate-50 transition group">
                        <span class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center flex-shrink-0 group-active:scale-95 transition-transform"><i class="fas fa-book-open text-base"></i></span>
                        <span class="flex-1 min-w-0">
                            <span class="block text-sm font-bold text-slate-800">Kalender Pendidikan</span>
                            <span class="block text-[11px] font-semibold text-slate-400 mt-0.5">Target kurikulum &amp; peta mengajar</span>
                        </span>
                        <i class="fas fa-chevron-right text-slate-300 text-xs"></i>
                    </a>
                    <button onclick="tampilToast('info', 'Menu Pengumuman segera hadir.')" class="flex items-center gap-3.5 px-4 py-3.5 w-full active:bg-slate-50 transition group text-left">
                        <span class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center flex-shrink-0 group-active:scale-95 transition-transform"><i class="fas fa-bullhorn text-base"></i></span>
                        <span class="flex-1 min-w-0">
                            <span class="block text-sm font-bold text-slate-800">Pengumuman</span>
                            <span class="block text-[11px] font-semibold text-slate-400 mt-0.5">Informasi &amp; berita terbaru</span>
                        </span>
                        <span class="shrink-0 px-2 py-1 rounded-full bg-amber-50 border border-amber-100 text-[9px] font-black text-amber-600 uppercase tracking-wider">Segera</span>
                    </button>
                    <button onclick="tampilToast('info', 'Menu Cuti / Izin segera hadir.')" class="flex items-center gap-3.5 px-4 py-3.5 w-full active:bg-slate-50 transition group text-left">
                        <span class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center flex-shrink-0 group-active:scale-95 transition-transform"><i class="fas fa-file-signature text-base"></i></span>
                        <span class="flex-1 min-w-0">
                            <span class="block text-sm font-bold text-slate-800">Cuti / Izin</span>
                            <span class="block text-[11px] font-semibold text-slate-400 mt-0.5">Permohonan izin &amp; cuti mengajar</span>
                        </span>
                        <span class="shrink-0 px-2 py-1 rounded-full bg-amber-50 border border-amber-100 text-[9px] font-black text-amber-600 uppercase tracking-wider">Segera</span>
                    </button>
                    <button onclick="tampilToast('info', 'Menu Ganti Jam segera hadir.')" class="flex items-center gap-3.5 px-4 py-3.5 w-full active:bg-slate-50 transition group text-left">
                        <span class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center flex-shrink-0 group-active:scale-95 transition-transform"><i class="fas fa-arrow-right-arrow-left text-base"></i></span>
                        <span class="flex-1 min-w-0">
                            <span class="block text-sm font-bold text-slate-800">Ganti Jam</span>
                            <span class="block text-[11px] font-semibold text-slate-400 mt-0.5">Penukaran jam mengajar</span>
                        </span>
                        <span class="shrink-0 px-2 py-1 rounded-full bg-amber-50 border border-amber-100 text-[9px] font-black text-amber-600 uppercase tracking-wider">Segera</span>
                    </button>
                </div>
            </div>

            <!-- ===== GRUP: AKUN ===== -->
            <div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1 mb-2.5">Akun</p>
                <div class="bg-white rounded-3xl border border-slate-100 shadow-[0_4px_18px_-10px_rgba(2,6,23,0.06)] overflow-hidden divide-y divide-slate-100">
                    <a href="{{ route('guru.profil.lengkap') }}" class="flex items-center gap-3.5 px-4 py-3.5 w-full active:bg-slate-50 transition group">
                        <span class="w-10 h-10 rounded-xl bg-slate-50 text-slate-500 flex items-center justify-center flex-shrink-0 group-active:scale-95 transition-transform ring-1 ring-slate-100"><i class="fas fa-user text-base"></i></span>
                        <span class="flex-1 min-w-0">
                            <span class="block text-sm font-bold text-slate-800">Profil Saya</span>
                            <span class="block text-[11px] font-semibold text-slate-400 mt-0.5">Biodata &amp; kelengkapan data</span>
                        </span>
                        <i class="fas fa-chevron-right text-slate-300 text-xs"></i>
                    </a>
                    <a href="/notifikasi/pengaturan" class="flex items-center gap-3.5 px-4 py-3.5 w-full active:bg-slate-50 transition group">
                        <span class="w-10 h-10 rounded-xl bg-slate-50 text-slate-500 flex items-center justify-center flex-shrink-0 group-active:scale-95 transition-transform ring-1 ring-slate-100"><i class="fas fa-bell text-base"></i></span>
                        <span class="flex-1 min-w-0">
                            <span class="block text-sm font-bold text-slate-800">Pengaturan Notifikasi</span>
                            <span class="block text-[11px] font-semibold text-slate-400 mt-0.5">Pengingat jadwal mengajar</span>
                        </span>
                        <span class="shrink-0 px-2 py-1 rounded-full bg-amber-50 border border-amber-100 text-[9px] font-black text-amber-600 uppercase tracking-wider">Segera</span>
                    </a>
                    <button onclick="bukaModalGantiPassword()" class="flex items-center gap-3.5 px-4 py-3.5 w-full active:bg-slate-50 transition group text-left">
                        <span class="w-10 h-10 rounded-xl bg-slate-50 text-slate-500 flex items-center justify-center flex-shrink-0 group-active:scale-95 transition-transform ring-1 ring-slate-100"><i class="fas fa-lock text-base"></i></span>
                        <span class="flex-1 min-w-0">
                            <span class="block text-sm font-bold text-slate-800">Ganti Password</span>
                            <span class="block text-[11px] font-semibold text-slate-400 mt-0.5">Perbarui kata sandi akun</span>
                        </span>
                        <i class="fas fa-chevron-right text-slate-300 text-xs"></i>
                    </button>
                </div>
            </div>

            <!-- ===== GRUP: LAINNYA ===== -->
            <div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1 mb-2.5">Lainnya</p>
                <div class="bg-white rounded-3xl border border-slate-100 shadow-[0_4px_18px_-10px_rgba(2,6,23,0.06)] overflow-hidden divide-y divide-slate-100">
                    <button id="btn-install-aplikasi" onclick="pilihInstallAplikasi()" class="hidden items-center gap-3.5 px-4 py-3.5 w-full active:bg-slate-50 transition group text-left">
                        <span class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center flex-shrink-0 group-active:scale-95 transition-transform"><i class="fas fa-arrow-down-to-bracket text-base"></i></span>
                        <span class="flex-1 min-w-0">
                            <span class="block text-sm font-bold text-slate-800">Install Aplikasi</span>
                            <span class="block text-[11px] font-semibold text-slate-400 mt-0.5">Pasang di layar utama perangkat</span>
                        </span>
                        <i class="fas fa-chevron-right text-slate-300 text-xs"></i>
                    </button>
                    <button onclick="bukaModal('modal-tentang', 'bg-tentang', 'box-tentang')" class="flex items-center gap-3.5 px-4 py-3.5 w-full active:bg-slate-50 transition group text-left">
                        <span class="w-10 h-10 rounded-xl bg-slate-50 text-slate-500 flex items-center justify-center flex-shrink-0 group-active:scale-95 transition-transform ring-1 ring-slate-100"><i class="fas fa-circle-info text-base"></i></span>
                        <span class="flex-1 min-w-0">
                            <span class="block text-sm font-bold text-slate-800">Tentang Aplikasi</span>
                            <span class="block text-[11px] font-semibold text-slate-400 mt-0.5">Informasi versi &amp; pengembang</span>
                        </span>
                        <i class="fas fa-chevron-right text-slate-300 text-xs"></i>
                    </button>
                    <button onclick="hapusCacheReload(this)" class="flex items-center gap-3.5 px-4 py-3.5 w-full active:bg-slate-50 transition group text-left">
                        <span class="w-10 h-10 rounded-xl bg-slate-50 text-slate-500 flex items-center justify-center flex-shrink-0 group-active:scale-95 transition-transform ring-1 ring-slate-100"><i class="fas fa-broom text-base"></i></span>
                        <span class="flex-1 min-w-0">
                            <span class="block text-sm font-bold text-slate-800">Hapus Cache &amp; Muat Ulang</span>
                            <span class="block text-[11px] font-semibold text-slate-400 mt-0.5">Bersihkan data tersimpan aplikasi</span>
                        </span>
                        <i class="fas fa-chevron-right text-slate-300 text-xs"></i>
                    </button>
                </div>
            </div>

            <!-- LOGOUT -->
            <form method="POST" action="/logout" class="mt-2" onsubmit="loadingElegan(event, this)">
                @csrf
                <button id="btn-logout" type="submit" class="relative w-full flex items-center justify-center gap-3 bg-white p-4 rounded-3xl active:scale-[0.98] transition-all duration-300 shadow-[0_4px_18px_-10px_rgba(2,6,23,0.06)] border border-slate-100 group cursor-pointer text-left overflow-hidden min-h-[64px]">
                    <span id="box-ikon-logout" class="w-10 h-10 rounded-xl bg-rose-50 text-rose-500 flex items-center justify-center flex-shrink-0 group-hover:bg-rose-500 group-hover:text-white transition-colors">
                        <i id="ikon-logout" class="fas fa-power-off text-base transition-transform"></i>
                    </span>
                    <span id="teks-logout" class="text-sm font-bold text-slate-700 group-hover:text-rose-600 transition-colors">Keluar Sistem</span>
                </button>
            </form>

            <!-- Script Khusus Bypass Loading Global -->
            <script>
                function loadingElegan(event, form) {
                    event.preventDefault();
                    event.stopPropagation();

                    let btn = document.getElementById('btn-logout');
                    let boxIkon = document.getElementById('box-ikon-logout');
                    let ikon = document.getElementById('ikon-logout');
                    let teks = document.getElementById('teks-logout');

                    btn.classList.add('opacity-80', 'cursor-wait', 'pointer-events-none');
                    boxIkon.classList.replace('bg-rose-50', 'bg-rose-500');
                    boxIkon.classList.replace('text-rose-500', 'text-white');
                    ikon.className = 'fas fa-circle-notch fa-spin text-base';
                    teks.innerText = 'Mengamankan sesi...';
                    teks.classList.replace('text-slate-700', 'text-rose-600');

                    setTimeout(() => { form.submit(); }, 400);
                }
            </script>

        </div>
    </div>

    <!-- NAVIGASI BAWAH -->
    @include('partials.bottom-nav', ['active' => 'menu'])

    <!-- ========================================== -->
    <!-- MODAL TENTANG APLIKASI -->
    <!-- ========================================== -->
    <div id="modal-tentang" class="fixed inset-0 z-[100] hidden">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity opacity-0" id="bg-tentang" onclick="tutupModal('modal-tentang', 'bg-tentang', 'box-tentang')"></div>
        <div class="flex items-center justify-center min-h-screen px-4 pb-10">
            <div class="bg-white w-full max-w-sm rounded-[2rem] p-6 shadow-2xl transform scale-95 opacity-0 transition-all duration-300 flex flex-col items-center text-center" id="box-tentang">
                <div class="w-20 h-20 rounded-3xl bg-gradient-to-br from-emerald-400 to-teal-600 shadow-lg flex items-center justify-center text-white text-4xl mb-4 ring-4 ring-emerald-50">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <h3 class="text-xl font-black text-slate-800">SmartPesantren</h3>
                <p class="text-[10px] font-bold text-emerald-600 uppercase tracking-widest mt-1 mb-4">Versi 2.0.0</p>

                <div class="bg-slate-50 rounded-2xl p-4 text-xs text-slate-500 font-medium mb-6 w-full text-left leading-relaxed border border-slate-100">
                    <p class="mb-2">Sistem Informasi Akademik dan Manajemen Pesantren terpadu.</p>
                    <p class="mt-3 text-center text-[10px] font-bold text-slate-400">&copy; {{ date('Y') }} Sancod Builder.</p>
                </div>
                <button onclick="tutupModal('modal-tentang', 'bg-tentang', 'box-tentang')" class="w-full py-3.5 bg-slate-900 hover:bg-slate-800 text-white text-sm font-bold rounded-2xl transition shadow-md">Tutup Panel</button>
            </div>
        </div>
    </div>

</div>

<!-- SCRIPT PENGENDALI MODAL & TOAST LOKAL -->
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

    function tampilToast(tipe, pesan) {
        let el = document.getElementById('toast-demo');
        if (!el) {
            el = document.createElement('div');
            el.id = 'toast-demo';
            el.className = 'fixed left-4 right-4 bottom-24 z-[120] px-4 py-3 rounded-2xl text-xs font-bold text-center shadow-2xl transition-all duration-300 opacity-0 translate-y-3';
            document.body.appendChild(el);
        }
        el.className = el.className
            .replace(/bg-emerald-500|bg-red-500|bg-sky-500/g, '')
            + (tipe === 'success' ? ' bg-emerald-500 text-white' : tipe === 'error' ? ' bg-red-500 text-white' : ' bg-slate-800 text-white');
        el.textContent = pesan;
        el.style.opacity = '1';
        el.style.transform = 'translateY(0)';
        clearTimeout(el._t);
        el._t = setTimeout(() => { el.style.opacity = '0'; el.style.transform = 'translateY(12px)'; }, 4000);
    }

    // HAPUS CACHE & MUAT ULANG
    function hapusCacheReload(btn) {
        btn.disabled = true;
        tampilToast('info', 'Menghapus cache dan memuat ulang...');
        if ('caches' in window) {
            caches.keys().then(namaCache => {
                return Promise.all(namaCache.map(n => caches.delete(n)));
            });
        }
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.getRegistrations().then(regs => {
                return Promise.all(regs.map(r => r.unregister()));
            });
        }
        setTimeout(() => { location.reload(); }, 1200);
    }

    // ====== INSTALL APLIKASI (PWA) ======
    let deferredInstallPrompt = null;

    function tampilkanTombolInstall() {
        let btn = document.getElementById('btn-install-aplikasi');
        if (btn) {
            btn.classList.remove('hidden');
            btn.classList.add('flex');
        }
    }

    function sembunyikanTombolInstall() {
        let btn = document.getElementById('btn-install-aplikasi');
        if (btn) {
            btn.classList.add('hidden');
            btn.classList.remove('flex');
        }
    }

    window.addEventListener('beforeinstallprompt', (e) => {
        e.preventDefault();
        deferredInstallPrompt = e;
        tampilkanTombolInstall();
    });

    window.addEventListener('appinstalled', () => {
        deferredInstallPrompt = null;
        sembunyikanTombolInstall();
    });

    function pilihInstallAplikasi() {
        if (!deferredInstallPrompt) {
            tampilToast('info', 'Aplikasi belum siap diinstal pada browser ini.');
            return;
        }
        deferredInstallPrompt.prompt();
        deferredInstallPrompt.userChoice.then((hasil) => {
            if (hasil.outcome === 'accepted') {
                tampilToast('success', 'Aplikasi sedang dipasang...');
            }
            deferredInstallPrompt = null;
        });
    }
</script>
@endsection