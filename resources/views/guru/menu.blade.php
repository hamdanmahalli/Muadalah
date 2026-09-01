@extends('layouts.app')
@section('title', 'Menu - SmartPesantren')
@section('content')
<style>
    header, aside { display: none !important; }
    #btn-buka-sidebar { display: none !important; }
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

            @if(auth()->user()->hasAnyRole(['Administrator', 'Pimpinan', 'Tata Usaha']) || auth()->user()->can('akses_dashboard'))
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
            @endif

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
                    <button onclick="toggleIngatIdentitas(this)" class="flex items-center gap-3.5 px-4 py-3.5 w-full active:bg-slate-50 transition group text-left cursor-pointer">
                        <span class="w-10 h-10 rounded-xl bg-slate-50 text-slate-500 flex items-center justify-center flex-shrink-0 group-active:scale-95 transition-transform ring-1 ring-slate-100"><i class="fas fa-user-check text-base"></i></span>
                        <span class="flex-1 min-w-0">
                            <span class="block text-sm font-bold text-slate-800">Ingat Identitas Saya</span>
                            <span class="block text-[11px] font-semibold text-slate-400 mt-0.5">Isi otomatis username &amp; tampilkan jadwal di halaman login</span>
                        </span>
                        <span id="box-switch-ingat" class="relative shrink-0 w-11 h-6 rounded-full transition-colors duration-300 cursor-pointer" style="background-color: #cbd5e1;">
                            <span id="knob-switch-ingat" class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow transition-all duration-300" style="transform: translateX(0);"></span>
                        </span>
                    </button>
                    <button onclick="toggleLoginSidikJari(this)" class="flex items-center gap-3.5 px-4 py-3.5 w-full active:bg-slate-50 transition group text-left cursor-pointer">
                        <span class="w-10 h-10 rounded-xl bg-slate-50 text-slate-500 flex items-center justify-center flex-shrink-0 group-active:scale-95 transition-transform ring-1 ring-slate-100"><i class="fas fa-fingerprint text-base"></i></span>
                        <span class="flex-1 min-w-0">
                            <span class="block text-sm font-bold text-slate-800">Login Sidik Jari</span>
                            <span class="block text-[11px] font-semibold text-slate-400 mt-0.5">Masuk cepat tanpa kata sandi</span>
                        </span>
                        <span id="box-switch-sidik" class="relative shrink-0 w-11 h-6 rounded-full transition-colors duration-300 cursor-pointer" style="background-color: #cbd5e1;">
                            <span id="knob-switch-sidik" class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow transition-all duration-300" style="transform: translateX(0);"></span>
                        </span>
                    </button>
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

    // ====== INGAT IDENTITAS SAYA (Pengaturan di Menu) ======
    var INGAT_KEY = 'sn_ingat';
    var USERNAME_KEY = 'sn_username';
    var usernameGuru = '{{ auth()->user()->username }}';
    var ingatAktif = localStorage.getItem(INGAT_KEY) === '1';

    function renderSwitchIngat() {
        var box = document.getElementById('box-switch-ingat');
        var knob = document.getElementById('knob-switch-ingat');
        if (!box || !knob) return;

        if (ingatAktif) {
            box.style.backgroundColor = '#10b981';
            knob.style.transform = 'translateX(20px)';
        } else {
            box.style.backgroundColor = '#cbd5e1';
            knob.style.transform = 'translateX(0px)';
        }
    }

    function toggleIngatIdentitas(btn) {
        ingatAktif = !ingatAktif;
        localStorage.setItem(INGAT_KEY, ingatAktif ? '1' : '0');
        if (ingatAktif) {
            if (usernameGuru) localStorage.setItem(USERNAME_KEY, usernameGuru);
            tampilToast('success', 'Identitas Anda akan diingat di perangkat ini.');
        } else {
            localStorage.removeItem(USERNAME_KEY);
            tampilToast('info', 'Ingat identitas dinonaktifkan.');
        }
        renderSwitchIngat();
    }

    renderSwitchIngat();

    // ====== LOGIN SIDIK JARI (WebAuthn / Passkey) ======
    var passkeyTerdaftar = {{ $passkeyCount > 0 ? 'true' : 'false' }};
    var passkeyIds = @json($passkeys);

    function renderSwitchSidik() {
        var box = document.getElementById('box-switch-sidik');
        var knob = document.getElementById('knob-switch-sidik');
        if (!box || !knob) return;

        if (passkeyTerdaftar) {
            box.style.backgroundColor = '#10b981';
            knob.style.transform = 'translateX(20px)';
        } else {
            box.style.backgroundColor = '#cbd5e1';
            knob.style.transform = 'translateX(0px)';
        }
    }

    function keBuffer(strB64url) {
        var s = strB64url.replace(/-/g, '+').replace(/_/g, '/');
        while (s.length % 4) { s += '='; }
        var bin = atob(s);
        var out = new Uint8Array(bin.length);
        for (var i = 0; i < bin.length; i++) { out[i] = bin.charCodeAt(i); }
        return out;
    }

    function dariBuffer(buffer) {
        var bins = new Uint8Array(buffer);
        var bin = '';
        for (var i = 0; i < bins.length; i++) { bin += String.fromCharCode(bins[i]); }
        return btoa(bin).replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/, '');
    }

    function konversiOptions(options) {
        options.challenge = keBuffer(options.challenge);
        if (options.user && options.user.id) { options.user.id = keBuffer(options.user.id); }
        ['allowCredentials', 'excludeCredentials'].forEach(function(kunci) {
            if (Array.isArray(options[kunci])) {
                options[kunci].forEach(function(item) { if (item.id) { item.id = keBuffer(item.id); } });
            }
        });
        return options;
    }

    function credentialKeJson(cred) {
        var json = { id: cred.id, rawId: dariBuffer(cred.rawId), type: cred.type, response: {} };
        var r = cred.response;
        json.response.clientDataJSON = dariBuffer(r.clientDataJSON);
        if (r.attestationObject) { json.response.attestationObject = dariBuffer(r.attestationObject); }
        if (r.authenticatorData) { json.response.authenticatorData = dariBuffer(r.authenticatorData); }
        if (r.signature) { json.response.signature = dariBuffer(r.signature); }
        if (r.userHandle) { json.response.userHandle = dariBuffer(r.userHandle); }
        if (typeof r.getTransports === 'function') { json.response.transports = r.getTransports(); }
        return json;
    }

    function footerCsrf(extra) {
        var token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
        return Object.assign({ 'X-CSRF-TOKEN': token, 'Accept': 'application/json' }, extra || {});
    }

    async function daftarSidikJari() {
        if (!window.PublicKeyCredential) { tampilToast('error', 'Browser tidak mendukung sidik jari.'); return false; }
        if (!window.isSecureContext) { tampilToast('error', 'Sidik jari hanya berfungsi di HTTPS.'); return false; }

        try {
            var resp = await fetch('/user/passkeys/options', { headers: footerCsrf(), credentials: 'same-origin' });
            var data = await resp.json();

            var cred = await navigator.credentials.create({ publicKey: konversiOptions(data.options) });

            var kirim = await fetch('/user/passkeys', {
                method: 'POST',
                headers: footerCsrf({ 'Content-Type': 'application/json' }),
                body: JSON.stringify({ name: 'Perangkat ' + new Date().toLocaleDateString('id-ID'), credential: credentialKeJson(cred) }),
                credentials: 'same-origin'
            });

            if (!kirim.ok) { tampilToast('error', 'Gagal menyimpan sidik jari.'); return false; }
            passkeyTerdaftar = true;
            renderSwitchSidik();
            tampilToast('success', 'Alhamdulillah, sidik jari berhasil didaftarkan.');
            return true;
        } catch (err) {
            if (err && err.name === 'NotAllowedError') {
                tampilToast('info', 'Pendaftaran sidik jari dibatalkan.');
            } else {
                tampilToast('error', 'Gagal mendaftarkan sidik jari. Coba lagi.');
            }
            return false;
        }
    }

    async function hapusSidikJari() {
        try {
            var ids = passkeyIds || [];
            if (ids.length === 0) { passkeyTerdaftar = false; renderSwitchSidik(); return; }

            for (var i = 0; i < ids.length; i++) {
                await fetch('/user/passkeys/' + ids[i], {
                    method: 'DELETE',
                    headers: footerCsrf(),
                    credentials: 'same-origin'
                });
            }
            passkeyTerdaftar = false;
            passkeyIds = [];
            renderSwitchSidik();
            tampilToast('info', 'Login sidik jari dinonaktifkan di perangkat ini.');
        } catch (err) {
            tampilToast('error', 'Gagal menghapus sidik jari.');
        }
    }

    function toggleLoginSidikJari(btn) {
        if (passkeyTerdaftar) {
            hapusSidikJari();
        } else {
            daftarSidikJari();
        }
    }

    renderSwitchSidik();

    // ====== INSTALL APLIKASI (PWA) ======
    let deferredInstallPrompt = null;

    function dukungInstallPWA() {
        // Browser mendukung instal PWA bila memiliki event beforeinstallprompt
        // (Chrome/Edge/Android). Safari iOS & beberapa browser tidak.
        return ('onbeforeinstallprompt' in window);
    }

    function tampilkanTombolInstall() {
        let btn = document.getElementById('btn-install-aplikasi');
        if (!btn) return;
        btn.classList.remove('hidden');
        btn.classList.add('flex');
    }

    function sembunyikanTombolInstall() {
        let btn = document.getElementById('btn-install-aplikasi');
        if (!btn) return;
        btn.classList.add('hidden');
        btn.classList.remove('flex');
    }

    // Saat halaman dimuat: tampilkan tombol bila browser mendukung instal PWA
    // dan aplikasi belum terinstal (tidak sedang berjalan dalam mode standalone).
    function cekStatusInstall() {
        let sudahTerinstal = window.matchMedia('(display-mode: standalone)').matches
            || window.matchMedia('(display-mode: fullscreen)').matches
            || window.navigator.standalone === true;

        if (sudahTerinstal) {
            sembunyikanTombolInstall();
        } else if (dukungInstallPWA()) {
            tampilkanTombolInstall();
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

    // Jalankan cek status (tunggu DOM siap)
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', cekStatusInstall);
    } else {
        cekStatusInstall();
    }

    function pilihInstallAplikasi() {
        if (deferredInstallPrompt) {
            deferredInstallPrompt.prompt();
            deferredInstallPrompt.userChoice.then((hasil) => {
                if (hasil.outcome === 'accepted') {
                    tampilToast('success', 'Aplikasi sedang dipasang...');
                }
                deferredInstallPrompt = null;
            });
            return;
        }

        // Belum ada prompt otomatis siap -> beri panduan manual
        if (/Android/i.test(navigator.userAgent)) {
            tampilToast('info', 'Buka menu titik tiga di browser, lalu pilih "Tambahkan ke layar utama".');
        } else {
            tampilToast('info', 'Buka menu browser (titik tiga), lalu pilih "Instal aplikasi" atau "Tambahkan ke layar utama".');
        }
    }
</script>
@endsection