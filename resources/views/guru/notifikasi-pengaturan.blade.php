@extends('layouts.app')
@section('title', 'Pengaturan Notifikasi')
@section('content')
<style>
    header, aside { display: none !important; }
    main { padding: 0 !important; background-color: #f8fafc !important; overflow: hidden !important; }
    body { overflow: hidden !important; background-color: #f8fafc !important; }
    @supports (padding-bottom: env(safe-area-inset-bottom)) { .pb-safe { padding-bottom: env(safe-area-inset-bottom); } }

    .notif-toggle { position: relative; width: 52px; height: 28px; border-radius: 14px; background: #cbd5e1; transition: background 0.3s; cursor: pointer; }
    .notif-toggle.active { background: #10b981; }
    .notif-toggle::after {
        content: ''; position: absolute; top: 2px; left: 2px;
        width: 24px; height: 24px; border-radius: 50%;
        background: white; box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        transition: transform 0.3s;
    }
    .notif-toggle.active::after { transform: translateX(24px); }

    .mode-card {
        border: 2px solid #e2e8f0; border-radius: 16px; padding: 14px 16px;
        display: flex; align-items: center; gap: 12px; cursor: pointer;
        transition: all 0.25s; background: white;
    }
    .mode-card.selected { border-color: #10b981; background: #f0fdf4; }
    .mode-card .mode-icon {
        width: 40px; height: 40px; border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 18px; flex-shrink: 0;
    }
</style>

<div class="max-w-md mx-auto h-[100dvh] bg-slate-50 flex flex-col relative font-sans overflow-hidden">

    <!-- HEADER -->
    <div class="shrink-0 bg-gradient-to-br from-emerald-600 to-teal-700 px-6 pt-8 pb-6 rounded-b-[2.5rem] shadow-md relative z-20">
        <div class="flex items-center gap-3">
            <a href="/menu" class="w-9 h-9 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center text-white">
                <i class="fas fa-arrow-left text-sm"></i>
            </a>
            <div>
                <h2 class="text-2xl font-black text-white tracking-tight drop-shadow-md">Notifikasi</h2>
                <p class="text-emerald-100 text-xs font-medium mt-1">Pengingat Jadwal Mengajar</p>
            </div>
        </div>
    </div>

    <!-- KONTEN -->
    <div class="flex-1 overflow-y-auto scrollbar-none px-5 pt-5 pb-24">

        @if(session('success'))
        <div class="mb-4 px-4 py-3 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-700 text-sm font-semibold flex items-center gap-2">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
        @endif

        <!-- Toggle Aktifkan -->
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 mb-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center">
                        <i class="fas fa-bell"></i>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-slate-800">Aktifkan Notifikasi</p>
                        <p class="text-xs text-slate-400 mt-0.5">Pengingat sebelum mengajar</p>
                    </div>
                </div>
                <form id="toggleForm" action="/notifikasi/simpan" method="POST">
                    @csrf
                    <input type="hidden" name="is_enabled" id="isEnabledInput" value="{{ $setting->is_enabled ? '1' : '0' }}">
                    <input type="hidden" name="mode" id="modeInput" value="{{ $setting->mode }}">
                    <input type="hidden" name="reminder_minutes" id="reminderInput" value="{{ $setting->reminder_minutes ?? 30 }}">
                    <div id="toggleBtn" class="notif-toggle {{ $setting->is_enabled ? 'active' : '' }}" onclick="toggleNotif()"></div>
                </form>
            </div>
        </div>

        <!-- Pilihan Mode -->
        <div id="modeSection" class="{{ $setting->is_enabled ? '' : 'opacity-40 pointer-events-none' }}">
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Mode Notifikasi</p>

            <div class="space-y-3 mb-4">
                <div class="mode-card {{ $setting->mode === 'sound' ? 'selected' : '' }}" onclick="selectMode('sound', this)">
                    <div class="mode-icon bg-blue-100 text-blue-600"><i class="fas fa-volume-up"></i></div>
                    <div>
                        <p class="text-sm font-bold text-slate-800">Suara & Getar</p>
                        <p class="text-xs text-slate-400">Notifikasi berbunyi dan bergetar</p>
                    </div>
                    @if($setting->mode === 'sound')<i class="fas fa-check-circle text-emerald-500 ml-auto"></i>@endif
                </div>

                <div class="mode-card {{ $setting->mode === 'vibrate' ? 'selected' : '' }}" onclick="selectMode('vibrate', this)">
                    <div class="mode-icon bg-purple-100 text-purple-600"><i class="fas fa-mobile-alt"></i></div>
                    <div>
                        <p class="text-sm font-bold text-slate-800">Hanya Getar</p>
                        <p class="text-xs text-slate-400">Hanya bergetar tanpa suara</p>
                    </div>
                    @if($setting->mode === 'vibrate')<i class="fas fa-check-circle text-emerald-500 ml-auto"></i>@endif
                </div>

                <div class="mode-card {{ $setting->mode === 'silent' ? 'selected' : '' }}" onclick="selectMode('silent', this)">
                    <div class="mode-icon bg-slate-100 text-slate-600"><i class="fas fa-moon"></i></div>
                    <div>
                        <p class="text-sm font-bold text-slate-800">Silent</p>
                        <p class="text-xs text-slate-400">Hanya muncul di panel notifikasi</p>
                    </div>
                    @if($setting->mode === 'silent')<i class="fas fa-check-circle text-emerald-500 ml-auto"></i>@endif
                </div>
            </div>

            <!-- Pilihan Waktu Pengingat -->
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3 mt-5">Waktu Pengingat</p>
            <div class="flex gap-2 mb-5">
                @php $current = $setting->reminder_minutes ?? 30; @endphp
                @foreach([10 => '10m', 15 => '15m', 30 => '30m', 45 => '45m', 60 => '1j'] as $val => $label)
                <button type="button" onclick="selectReminder({{ $val }}, this)"
                    class="reminder-btn flex-1 py-2.5 rounded-xl text-xs font-bold border-2 transition-all {{ $current == $val ? 'bg-emerald-500 border-emerald-500 text-white shadow-md shadow-emerald-200' : 'bg-white border-slate-200 text-slate-500' }}">
                    {{ $label }}
                </button>
                @endforeach
            </div>

            <!-- Tombol Test -->
            <button id="btnTest" onclick="kirimTest()" class="w-full py-3 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-600 text-white font-bold text-sm shadow-lg shadow-emerald-200 active:scale-95 transition flex items-center justify-center gap-2">
                <i class="fas fa-paper-plane"></i> Kirim Notifikasi Test
            </button>

            <button id="btnLokal" onclick="ujiLokal()" class="mt-2 w-full py-3 rounded-xl bg-gradient-to-r from-slate-600 to-slate-700 text-white font-bold text-sm shadow-lg shadow-slate-200 active:scale-95 transition flex items-center justify-center gap-2">
                <i class="fas fa-bolt"></i> Uji Notifikasi Lokal (tanpa server)
            </button>

            <button id="btnTestPulse" onclick="kirimTestPulse()" class="mt-2 w-full py-3 rounded-xl bg-gradient-to-r from-amber-500 to-orange-600 text-white font-bold text-sm shadow-lg shadow-amber-200 active:scale-95 transition flex items-center justify-center gap-2">
                <i class="fas fa-radio"></i> Tes Pengiriman Murni (tanpa isi pesan)
            </button>

            <div id="testResult" class="hidden mt-3 px-4 py-2.5 rounded-xl text-sm font-semibold text-center"></div>
            <div id="testDetail" class="hidden mt-2 space-y-1.5 rounded-xl border border-slate-200 bg-white p-3"></div>

            <!-- Status Perangkat -->
            <div id="subStatus" class="hidden mt-3 px-4 py-2.5 rounded-xl text-sm font-semibold flex items-center gap-2"></div>
            <button id="btnHubungkan" onclick="hubungkanNotifikasi()" class="hidden mt-2 w-full py-3 rounded-xl bg-gradient-to-r from-blue-500 to-indigo-600 text-white font-bold text-sm shadow-lg shadow-blue-200 active:scale-95 transition">
                <i class="fas fa-link mr-2"></i> Hubungkan Perangkat Ini
            </button>

            <!-- Diagnosa Teknis -->
            <div class="mt-4 bg-slate-50 border border-slate-200 rounded-xl p-3 space-y-2">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-wider">Diagnosa Teknis</p>
                <div class="flex justify-between text-xs"><span class="text-slate-500 font-medium">Perangkat terdaftar di server</span><span id="diagServer" class="font-bold text-slate-700">{{ $deviceCount }} perangkat</span></div>
                <div class="flex justify-between text-xs"><span class="text-slate-500 font-medium">Push diterima perangkat (10 mnt)</span><span id="diagPulse" class="font-bold {{ $pulseCount > 0 ? 'text-emerald-600' : 'text-slate-700' }}">{{ $pulseCount }} kali</span></div>
                <div class="flex justify-between text-xs"><span class="text-slate-500 font-medium">Subscribsi di browser ini</span><span id="diagBrowser" class="font-bold text-slate-700">Memeriksa...</span></div>
                <div class="flex justify-between text-xs"><span class="text-slate-500 font-medium">Service Worker aktif</span><span id="diagSW" class="font-bold text-slate-700">Memeriksa...</span></div>
                <div class="flex justify-between text-xs"><span class="text-slate-500 font-medium">Kunci tersimpan (p256dh/auth)</span><span id="diagKunci" class="font-bold text-slate-700">{{ $storedKeyInfo ? $storedKeyInfo['p256dh'] . '/' . $storedKeyInfo['auth'] . ' karakter' : 'tidak ada' }}</span></div>
                <div class="flex justify-between text-xs"><span class="text-slate-500 font-medium">Kunci di browser ini</span><span id="diagKunciBrowser" class="font-bold text-slate-700">Memeriksa...</span></div>
            </div>
        </div>

        <!-- Keterangan iOS -->
        <div class="mt-6 bg-amber-50 border border-amber-200 rounded-xl p-4 flex gap-3">
            <i class="fas fa-exclamation-triangle text-amber-500 mt-0.5"></i>
            <div>
                <p class="text-xs font-bold text-amber-800">Catatan untuk iOS</p>
                <p class="text-xs text-amber-600 mt-1 leading-relaxed">Notifikasi push hanya berjalan di <strong>Android</strong>. iOS belum mendukung Web Push untuk PWA.</p>
            </div>
        </div>

    </div>

    <!-- BOTTOM NAV -->
    <div class="shrink-0 z-40 w-full bg-white/95 backdrop-blur-xl border-t border-slate-100 shadow-[0_-10px_25px_rgba(0,0,0,0.06)] px-4 py-2 flex justify-between items-end pb-safe pt-2">
        <a href="/dashboard-guru" class="flex flex-col items-center justify-center w-12 text-slate-400 hover:text-emerald-500 transition pb-1"><i class="fas fa-home text-xl mb-1"></i><span class="text-[9px] font-bold">Beranda</span></a>
        <a href="/kaldik" class="flex flex-col items-center justify-center w-12 text-slate-400 hover:text-emerald-500 transition pb-1"><i class="fas fa-calendar-alt text-xl mb-1"></i><span class="text-[9px] font-bold">Kaldik</span></a>
        <div class="relative -top-6 flex justify-center items-center"><a href="/scan-kelas" class="w-16 h-16 rounded-full bg-gradient-to-b from-emerald-400 to-emerald-600 text-white flex items-center justify-center text-3xl shadow-[0_8px_20px_rgba(16,185,129,0.4)] border-4 border-slate-50 transform hover:scale-105 active:scale-95 transition-all"><i class="fas fa-qrcode"></i></a></div>
        <a href="/rekap-presensi" class="flex flex-col items-center justify-center w-12 text-slate-400 hover:text-emerald-500 transition pb-1"><i class="fas fa-file-invoice text-xl mb-1"></i><span class="text-[9px] font-bold">Rekap</span></a>
        <a href="/menu" class="flex flex-col items-center justify-center w-12 text-slate-400 hover:text-emerald-500 transition pb-1"><i class="fas fa-bars text-xl mb-1"></i><span class="text-[9px] font-bold">Menu</span></a>
    </div>

</div>

<script>
let currentMode = '{{ $setting->mode }}';
let isEnabled = {{ $setting->is_enabled ? 'true' : 'false' }};

function toggleNotif() {
    isEnabled = !isEnabled;
    const toggle = document.getElementById('toggleBtn');
    const input = document.getElementById('isEnabledInput');
    const section = document.getElementById('modeSection');

    toggle.classList.toggle('active', isEnabled);
    input.value = isEnabled ? '1' : '0';
    section.classList.toggle('opacity-40', !isEnabled);
    section.classList.toggle('pointer-events-none', !isEnabled);

    document.getElementById('toggleForm').submit();
}

function selectMode(mode, el) {
    currentMode = mode;
    document.getElementById('modeInput').value = mode;

    document.querySelectorAll('.mode-card').forEach(c => {
        c.classList.remove('selected');
        c.querySelector('.fa-check-circle')?.remove();
    });
    el.classList.add('selected');
    const check = document.createElement('i');
    check.className = 'fas fa-check-circle text-emerald-500 ml-auto';
    el.appendChild(check);

    document.getElementById('toggleForm').submit();
}

function selectReminder(minutes, el) {
    document.getElementById('reminderInput').value = minutes;
    document.querySelectorAll('.reminder-btn').forEach(b => {
        b.className = 'reminder-btn flex-1 py-2.5 rounded-xl text-xs font-bold border-2 transition-all bg-white border-slate-200 text-slate-500';
    });
    el.className = 'reminder-btn flex-1 py-2.5 rounded-xl text-xs font-bold border-2 transition-all bg-emerald-500 border-emerald-500 text-white shadow-md shadow-emerald-200';
    document.getElementById('toggleForm').submit();
}

function kirimTest() {
    const btn = document.getElementById('btnTest');
    const result = document.getElementById('testResult');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mengirim...';

    fetch('/notifikasi/test', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
        },
    })
    .then(r => r.json())
    .then(data => {
        result.classList.remove('hidden');
        const detailEl = document.getElementById('testDetail');
        if (data.success) {
            result.className = 'mt-3 px-4 py-2.5 rounded-xl text-sm font-semibold text-center bg-emerald-50 text-emerald-700 border border-emerald-200';
        } else {
            result.className = 'mt-3 px-4 py-2.5 rounded-xl text-sm font-semibold text-center bg-red-50 text-red-700 border border-red-200';
        }
        result.textContent = data.message;

        if (detailEl && data.detail && data.detail.length) {
            detailEl.classList.remove('hidden');
            detailEl.innerHTML = data.detail.map(d =>
                '<div class="flex items-center justify-between text-xs">' +
                '<span class="text-slate-500 font-medium truncate mr-2"><i class="fas ' + (d.success ? 'fa-check-circle text-emerald-500' : 'fa-times-circle text-red-500') + ' mr-1"></i>' + d.host + '</span>' +
                '<span class="font-bold ' + (d.success ? 'text-emerald-600' : 'text-red-600') + '">HTTP ' + (d.status_code || '-') + '</span></div>'
            ).join('');
        }
    })
    .catch(() => {
        result.classList.remove('hidden');
        result.className = 'mt-3 px-4 py-2.5 rounded-xl text-sm font-semibold text-center bg-red-50 text-red-700 border border-red-200';
        result.textContent = 'Gagal mengirim. Periksa koneksi internet.';
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-paper-plane"></i> Kirim Notifikasi Test';
    });
}

// Tes Pengiriman Murni: push tanpa payload/enkripsi. Membuktikan apakah pesan benar2 tiba di browser.
function kirimTestPulse() {
    const btn = document.getElementById('btnTestPulse');
    const result = document.getElementById('testResult');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mengirim...';

    fetch('/notifikasi/test-pulse', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
        },
    })
    .then(r => r.json())
    .then(data => {
        result.classList.remove('hidden');
        const detailEl = document.getElementById('testDetail');
        if (data.success) {
            result.className = 'mt-3 px-4 py-2.5 rounded-xl text-sm font-semibold text-center bg-emerald-50 text-emerald-700 border border-emerald-200';
        } else {
            result.className = 'mt-3 px-4 py-2.5 rounded-xl text-sm font-semibold text-center bg-red-50 text-red-700 border border-red-200';
        }
        result.textContent = data.message;
        result.textContent += ' — Cek "Push diterima perangkat" di Diagnosa, lalu refresh.';

        if (detailEl && data.detail && data.detail.length) {
            detailEl.classList.remove('hidden');
            detailEl.innerHTML = data.detail.map(d =>
                '<div class="flex items-center justify-between text-xs">' +
                '<span class="text-slate-500 font-medium truncate mr-2"><i class="fas ' + (d.success ? 'fa-check-circle text-emerald-500' : 'fa-times-circle text-red-500') + ' mr-1"></i>' + d.host + '</span>' +
                '<span class="font-bold ' + (d.success ? 'text-emerald-600' : 'text-red-600') + '">HTTP ' + (d.status_code || '-') + '</span></div>'
            ).join('');
        }
    })
    .catch(() => {
        result.classList.remove('hidden');
        result.className = 'mt-3 px-4 py-2.5 rounded-xl text-sm font-semibold text-center bg-red-50 text-red-700 border border-red-200';
        result.textContent = 'Gagal mengirim. Periksa koneksi internet.';
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-radio"></i> Tes Pengiriman Murni (tanpa isi pesan)';
    });
}

// Uji Notifikasi Lokal (tanpa server/FCM) — membuktikan channel suara/getar di perangkat
function ujiLokal() {
    const btn = document.getElementById('btnLokal');
    const asli = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Menampilkan...';

    const optionsLokal = {
        body: 'Ini notifikasi dari perangkat ini sendiri (tanpa server). Bunyi & getar mengikuti pengaturan sistem.',
        icon: '/icons/icon-192x192.png',
        badge: '/icons/icon-192x192.png',
        tag: 'lokal-test',
        renotify: true,
        vibrate: [250, 100, 250, 100, 250],
        data: { url: '/notifikasi/pengaturan' },
    };

    const tampilkan = () =>
        navigator.serviceWorker.ready
            .then(reg => reg.showNotification('Uji Lokal — Bunyi & Getar', optionsLokal))
            .then(() => setSubStatus('success', '<i class="fas fa-check-circle"></i> Notifikasi lokal dimunculkan. Apakah berbunyi & bergetar?'))
            .catch(err => setSubStatus('error', '<i class="fas fa-times-circle"></i> Gagal: ' + err.message))
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = asli;
            });

    if (Notification.permission === 'granted') {
        tampilkan();
    } else if (Notification.permission === 'default') {
        Notification.requestPermission().then(permission => {
            if (permission === 'granted') tampilkan();
            else setSubStatus('warning', '<i class="fas fa-exclamation-circle"></i> Izin ditolak. Aktifkan izin notifikasi dulu.');
        });
    } else {
        setSubStatus('error', '<i class="fas fa-times-circle"></i> Izin notifikasi ditolak di browser.');
    }
}

// Diagnosa teknis: status SW & subskripsi di browser ini
(function() {
    const diagBrowser = document.getElementById('diagBrowser');
    const diagSW = document.getElementById('diagSW');
    const diagKunciBrowser = document.getElementById('diagKunciBrowser');
    if (!diagBrowser) return;

    if ('serviceWorker' in navigator && 'PushManager' in window) {
        navigator.serviceWorker.ready.then(reg => {
            const active = reg.active;
            let swTeks = 'Tidak aktif';
            if (active) {
                swTeks = (active.scriptURL || '').replace(/^.*\/(sw\.js).*$/, '$1') + (active.scriptURL.includes('?v=6') ? ' (v6)' : ' (lama)');
            }

            if (diagSW) {
                diagSW.textContent = swTeks;
                diagSW.className = 'font-bold ' + (swTeks.includes('v6') ? 'text-emerald-600' : 'text-red-600');
            }

            return reg.pushManager.getSubscription();
        }).then(sub => {
            if (sub) {
                const host = (sub.endpoint || '').replace(/^https?:\/\//, '').split('/')[0] || '-';
                diagBrowser.textContent = 'Aktif (' + host + ')';
                diagBrowser.className = 'font-bold text-emerald-600';

                if (diagKunciBrowser && sub.getKey) {
                    const b64 = key => {
                        const p = [];
                        key.forEach(byte => p.push(String.fromCharCode(byte)));
                        return btoa(p.join(''));
                    };
                    const p256dh = sub.getKey('p256dh') ? b64(new Uint8Array(sub.getKey('p256dh'))) : '';
                    const auth = sub.getKey('auth') ? b64(new Uint8Array(sub.getKey('auth'))) : '';
                    diagKunciBrowser.textContent = p256dh.length + '/' + auth.length + ' karakter';
                    diagKunciBrowser.className = 'font-bold text-slate-700';
                }
            } else {
                diagBrowser.textContent = 'Belum ada';
                diagBrowser.className = 'font-bold text-red-600';
            }
        }).catch(err => {
            diagBrowser.textContent = 'Error: ' + err.message;
        });
    } else {
        diagBrowser.textContent = 'Browser tidak mendukung';
        diagSW.textContent = 'Tidak didukung';
    }
})();

// Push subscription
let subStatusEl = document.getElementById('subStatus');
let btnHubungkan = document.getElementById('btnHubungkan');

function urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - base64String.length % 4) % 4);
    const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
    const rawData = window.atob(base64);
    const outputArray = new Uint8Array(rawData.length);
    for (let i = 0; i < rawData.length; ++i) {
        outputArray[i] = rawData.charCodeAt(i);
    }
    return outputArray;
}

function setSubStatus(status, text) {
    if (!subStatusEl) return;
    subStatusEl.classList.remove('hidden');
    const styles = {
        success: 'bg-emerald-50 text-emerald-700 border border-emerald-200',
        warning: 'bg-amber-50 text-amber-700 border border-amber-200',
        error: 'bg-red-50 text-red-700 border border-red-200',
    };
    subStatusEl.className = 'mt-3 px-4 py-2.5 rounded-xl text-sm font-semibold flex items-center gap-2 ' + (styles[status] || styles.warning);
    subStatusEl.innerHTML = text;
}

function simpanSubscription(sub) {
    return fetch('/notifikasi/subscribe', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(sub.toJSON()),
    }).then(r => r.json());
}

function hubungkanNotifikasi() {
    if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
        setSubStatus('error', '<i class="fas fa-times-circle"></i> Browser tidak mendukung notifikasi push.');
        return;
    }
    navigator.serviceWorker.ready.then(reg => {
        if (Notification.permission === 'default') {
            return Notification.requestPermission().then(permission => {
                if (permission !== 'granted') {
                    setSubStatus('warning', '<i class="fas fa-exclamation-circle"></i> Izin notifikasi ditolak. Aktifkan di pengaturan browser.');
                    return;
                }
                return reg.pushManager.subscribe({
                    userVisibleOnly: true,
                    applicationServerKey: urlBase64ToUint8Array('{{ config("webpush.vapid.public_key") }}')
                });
            });
        }
        return reg.pushManager.getSubscription().then(existing => {
            if (existing) return existing;
            return reg.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: urlBase64ToUint8Array('{{ config("webpush.vapid.public_key") }}')
            });
        });
    }).then(sub => {
        if (!sub) return;
        return simpanSubscription(sub).then(() => {
            setSubStatus('success', '<i class="fas fa-check-circle"></i> Perangkat terhubung. Notifikasi akan dikirim ke perangkat ini.');
            btnHubungkan.classList.add('hidden');
        });
    }).catch(err => {
        setSubStatus('error', '<i class="fas fa-times-circle"></i> Gagal menghubungkan: ' + err.message);
    });
}

if ('serviceWorker' in navigator && 'PushManager' in window) {
    navigator.serviceWorker.ready.then(reg => {
        return reg.pushManager.getSubscription();
    }).then(sub => {
        if (sub) {
            setSubStatus('success', '<i class="fas fa-check-circle"></i> Perangkat sudah terhubung & notifikasi aktif.');
        } else if (Notification.permission === 'granted') {
            return hubungkanNotifikasi();
        } else if (Notification.permission === 'default') {
            setSubStatus('warning', '<i class="fas fa-link"></i> Ketuk "Hubungkan Perangkat Ini" untuk mengaktifkan notifikasi.');
            btnHubungkan.classList.remove('hidden');
        } else {
            setSubStatus('warning', '<i class="fas fa-exclamation-circle"></i> Izin notifikasi ditolak di browser.');
        }
    }).catch(err => {
        console.log('Push status error:', err);
    });
}
</script>
@endsection
