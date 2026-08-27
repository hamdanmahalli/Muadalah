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

            <div id="testResult" class="hidden mt-3 px-4 py-2.5 rounded-xl text-sm font-semibold text-center"></div>
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
        if (data.success) {
            result.className = 'mt-3 px-4 py-2.5 rounded-xl text-sm font-semibold text-center bg-emerald-50 text-emerald-700 border border-emerald-200';
            result.textContent = data.message;
        } else {
            result.className = 'mt-3 px-4 py-2.5 rounded-xl text-sm font-semibold text-center bg-red-50 text-red-700 border border-red-200';
            result.textContent = data.message;
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

// Push subscription
if ('serviceWorker' in navigator && 'PushManager' in window) {
    navigator.serviceWorker.ready.then(reg => {
        reg.pushManager.subscribe({
            userVisibleOnly: true,
            applicationServerKey: '{{ config("webpush.vapid.public_key") }}'
        }).then(sub => {
            fetch('/notifikasi/subscribe', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(sub.toJSON()),
            });
        }).catch(err => console.log('Push subscribe error:', err));
    });
}
</script>
@endsection
