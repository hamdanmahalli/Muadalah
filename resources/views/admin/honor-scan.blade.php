@extends('layouts.app')

@section('title', 'Scan Honor - Penerimaan Bisyaroh')

@section('content')
@push('styles')
    <meta name="turbo-cache-control" content="no-cache">
@endpush
<style>
    header, aside { display: none !important; }
    #btn-buka-sidebar { display: none !important; }
    main { padding: 0 !important; background-color: #000 !important; overflow: hidden !important; }
    body { overflow: hidden !important; background-color: #000 !important; }
    #reader video {
        object-fit: cover !important;
        width: 100% !important;
        height: 100% !important;
    }
    @keyframes scanLine {
        0% { top: 4%; opacity: 0.8; }
        50% { opacity: 1; }
        100% { top: 92%; opacity: 0.8; }
    }
    .tu-scanner-line {
        position: absolute;
        left: 12%;
        right: 12%;
        height: 2px;
        background: #10b981;
        box-shadow: 0 0 12px #10b981, 0 0 24px #10b981;
        animation: scanLine 2s ease-in-out infinite alternate;
    }
    .scrollbar-none::-webkit-scrollbar { display: none; }
    .scrollbar-none { -ms-overflow-style: none; scrollbar-width: none; }
</style>

<div class="max-w-md mx-auto h-[100dvh] bg-black flex flex-col relative font-sans overflow-hidden">

    <!-- HEADER + TAB -->
    <div class="shrink-0 bg-white px-4 pt-4 pb-3 z-30">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <a href="{{ route('honor.index') }}" class="w-10 h-10 rounded-xl bg-slate-50 flex items-center justify-center text-slate-500 hover:bg-slate-100 hover:text-emerald-600 transition-colors mr-4">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div>
                    <h2 class="text-base font-black text-slate-800 tracking-tight">Scan Honor Guru</h2>
                    <p class="text-[9px] font-bold text-emerald-600 uppercase tracking-widest mt-0.5">
                        @if($periodeHonor)
                            {{ $bulanIndonesia[$periodeHonor->bulan] ?? $periodeHonor->bulan }} {{ $periodeHonor->tahun }}
                        @else
                            Belum ada periode final
                        @endif
                    </p>
                </div>
            </div>
            @if($periodeHonor)
            <span class="inline-flex items-center gap-2 px-3 py-1.5 bg-sky-50 text-sky-600 border border-sky-200 font-bold text-xs rounded-lg">
                <i class="fas fa-circle-check"></i> {{ $periodeHonor->details->where('butuh_penerimaan', true)->count() > 0 ? $periodeHonor->details->where('is_diterima', true)->count() . '/' . $periodeHonor->details->where('butuh_penerimaan', true)->count() : '—' }}
            </span>
            @endif
        </div>

        @if($periodeHonor)
        <div class="flex mt-3 gap-1 bg-slate-100 p-1 rounded-2xl">
            <button type="button" id="tab-scan"
                class="w-1/2 flex items-center justify-center gap-2 py-2.5 rounded-xl text-sm font-black transition-all
                       text-emerald-700 bg-white shadow-sm">
                <i class="fas fa-qrcode"></i> Scan QR
            </button>
            <button type="button" id="tab-qr"
                class="w-1/2 flex items-center justify-center gap-2 py-2.5 rounded-xl text-sm font-black transition-all
                       text-slate-400">
                <i class="fas fa-user-tag"></i> QR Code
            </button>
        </div>
        @endif
    </div>

    @if($periodeHonor)
    <!-- PANEL 1: SCAN (kamera full screen) -->
    <div id="panel-scan" class="flex-1 relative overflow-hidden z-0 flex flex-col bg-black">

        <div class="flex-1 relative min-h-0">
            <div id="reader" class="absolute inset-0"></div>
            <div id="laser-line" class="tu-scanner-line pointer-events-none z-10 transition-opacity duration-300"></div>

            <p class="absolute inset-x-0 bottom-3 z-20 text-white text-[12px] font-semibold text-center">
                Arahkan kamera ke QR Code honor guru (HONOR-...)
            </p>

            <!-- Overlay saat kamera mati / belum menyala -->
            <div id="overlay-kamera" class="absolute inset-0 z-20 flex flex-col items-center justify-center bg-black/90 backdrop-blur-sm px-6 text-center">
                <div class="w-16 h-16 rounded-full bg-slate-800/80 text-slate-500 flex items-center justify-center text-2xl mb-4">
                    <i class="fas fa-camera"></i>
                </div>
                <p id="status-kamera" class="text-slate-300 text-[13px] font-bold leading-relaxed max-w-[280px]">Kamera belum menyala.</p>
                <button type="button" id="btn-mulai-kamera" onclick=" mulaiKamera()"
                    class="mt-5 px-6 py-3 bg-emerald-500 hover:bg-emerald-600 text-white font-black rounded-xl shadow-md shadow-emerald-500/30 transition-all active:scale-95">
                    <i class="fas fa-qrcode mr-2"></i> Mulai Scan Barcode
                </button>
            </div>

            <!-- Panel Hasil Sukses -->
            <div id="panel-sukses" class="hidden absolute inset-0 z-30 flex flex-col items-center justify-center bg-black/85 backdrop-blur-sm px-6 text-center">
                <div id="sukses-ikon" class="w-20 h-20 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center text-4xl mb-5 shadow-[0_0_40px_rgba(16,185,129,0.6)]">
                    <i class="fas fa-hand-holding-dollar"></i>
                </div>
                <p id="sukses-nama" class="text-white font-black tracking-widest text-xl uppercase drop-shadow-md">Honor Diterima</p>
                <p id="sukses-pesan" class="text-slate-200 text-sm mt-2 font-bold leading-snug drop-shadow-sm px-2"></p>
                <button type="button" id="btn-scan-lagi"
                    class="mt-6 px-6 py-3 bg-emerald-500 hover:bg-emerald-600 text-white font-black rounded-xl shadow-md shadow-emerald-500/30 transition-all active:scale-95">
                    <i class="fas fa-camera mr-2"></i> Pindai Lagi
                </button>
            </div>
        </div>

        <div class="shrink-0 bg-white border-t border-slate-200 relative z-20">
            <button type="button" id="btn-toggle-kamera" onclick=" toggleKamera()"
                class="w-full py-3 text-sm font-black text-emerald-600 flex items-center justify-center gap-2 active:scale-[0.99] transition-all">
                <i id="ikon-toggle-kamera" class="fas fa-video"></i>
                <span id="label-toggle-kamera">Nyalakan Kamera</span>
            </button>
        </div>
    </div>

    <!-- PANEL 2: QR CODE (galeri barcode honor) -->
    <div id="panel-qr" class="hidden flex-1 z-0 bg-slate-100 overflow-y-auto scrollbar-none p-4">
        <div class="grid grid-cols-3 gap-2.5">
            @forelse($qrItems as $item)
            <div class="bg-white rounded-2xl border border-slate-200 p-2 flex flex-col items-center text-center shadow-sm {{ $item['diterima'] ? 'opacity-50' : '' }}">
                <div class="w-full aspect-square bg-slate-50 rounded-xl border border-slate-100 flex items-center justify-center p-1.5 relative">
                    <img src="data:image/svg+xml,{{ $item['qr'] }}" alt="QR {{ $item['nama'] }}" class="w-full h-full object-contain rounded-lg">
                    @if($item['diterima'])
                    <span class="absolute -top-1.5 -right-1.5 w-5 h-5 rounded-full bg-emerald-500 text-white flex items-center justify-center text-[9px] shadow">
                        <i class="fas fa-check"></i>
                    </span>
                    @endif
                </div>
                <p class="w-full text-[9px] font-bold text-slate-600 mt-1.5 truncate" title="{{ $item['nama'] }}">{{ $item['nama'] }}</p>
                <p class="text-[8px] font-black {{ $item['diterima'] ? 'text-emerald-600' : 'text-amber-600' }} mt-0.5 uppercase">
                    {{ $item['diterima'] ? 'Diterima' : 'Menunggu' }}
                </p>
            </div>
            @empty
            <div class="col-span-3 py-12 flex flex-col items-center text-center">
                <div class="w-16 h-16 bg-slate-50 text-slate-300 rounded-full flex items-center justify-center mb-4 text-3xl"><i class="fas fa-qrcode"></i></div>
                <h4 class="text-sm font-black text-slate-700">Belum Ada QR Honor</h4>
                <p class="text-xs font-medium text-slate-400 mt-1">Semua honor periode ini sudah diterima, atau belum ada nominal yang perlu penerimaan.</p>
            </div>
            @endforelse
        </div>
    </div>
    @else
    <div class="flex-1 bg-slate-100 flex flex-col items-center justify-center px-8 text-center">
        <div class="w-16 h-16 bg-slate-50 text-slate-300 rounded-full flex items-center justify-center mb-4 text-3xl"><i class="fas fa-hourglass-half"></i></div>
        <h4 class="text-sm font-black text-slate-700">Belum Ada Periode Final</h4>
        <p class="text-xs font-medium text-slate-400 mt-1 leading-relaxed">Finalkan rekap honor terlebih dahulu agar QR penerimaan aktif.</p>
        <a href="{{ route('honor.index') }}" class="mt-5 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold rounded-xl transition shadow-sm active:scale-95">
            <i class="fas fa-arrow-left mr-2"></i> Kembali
        </a>
    </div>
    @endif
</div>

@push('scripts')
<script src="/js/html5-qrcode.min.js" defer></script>
<script>
(function() {
    let isProcessing = false;
    let html5QrCode = null;
    let kameraBerjalan = false;
    let scanSelesai = false;

    const panelScan = document.getElementById('panel-scan');
    const panelQr = document.getElementById('panel-qr');
    const tabScan = document.getElementById('tab-scan');
    const tabQr = document.getElementById('tab-qr');
    const panelSukses = document.getElementById('panel-sukses');
    const laser = document.getElementById('laser-line');
    const overlayKamera = document.getElementById('overlay-kamera');
    const statusKamera = document.getElementById('status-kamera');
    const btnToggle = document.getElementById('btn-toggle-kamera');
    const labelToggle = document.getElementById('label-toggle-kamera');
    const ikonToggle = document.getElementById('ikon-toggle-kamera');

    function tampilScanToast(tipe, pesan) {
        var lama = document.getElementById('toast-scan-honor');
        if (lama) lama.remove();
        var t = document.createElement('div');
        t.id = 'toast-scan-honor';
        var warna = tipe === 'error' ? '#f43f5e' : (tipe === 'success' ? '#10b981' : '#f59e0b');
        t.style.cssText = 'position:fixed;left:16px;right:16px;bottom:120px;z-index:250;background:' + warna + ';color:#fff;padding:12px 16px;border-radius:14px;font-size:13px;font-weight:700;text-align:center;box-shadow:0 10px 25px rgba(0,0,0,0.25);opacity:0;transform:translateY(10px);transition:opacity .2s ease,transform .2s ease;';
        t.textContent = pesan;
        document.body.appendChild(t);
        requestAnimationFrame(function() {
            t.style.opacity = '1';
            t.style.transform = 'translateY(0)';
        });
        setTimeout(function() { if (t.parentNode) { t.style.opacity = '0'; setTimeout(function() { t.remove(); }, 250); } }, 3800);
    }

    function setStatusKamera(pesan) {
        if (statusKamera) statusKamera.textContent = pesan;
    }

    function aturTombolToggle(jalan) {
        kameraBerjalan = jalan;
        if (btnToggle) {
            if (jalan) {
                labelToggle.textContent = 'Matikan Kamera';
                ikonToggle.className = 'fas fa-video-slash';
            } else {
                labelToggle.textContent = 'Nyalakan Kamera';
                ikonToggle.className = 'fas fa-video';
            }
        }
    }

    function tampilOverlay(terlihat) {
        if (!overlayKamera) return;
        if (terlihat) overlayKamera.classList.remove('hidden');
        else overlayKamera.classList.add('hidden');
    }

    function lokasiTidakAman() {
        return !window.isSecureContext && !['localhost', '127.0.0.1', '::1'].includes(location.hostname);
    }

    function konteksAman() {
        if (window.isSecureContext) return true;
        var host = location.hostname;
        if (host === 'localhost' || host === '127.0.0.1' || host === '::1') return true;
        setStatusKamera('Kamera membutuhkan koneksi aman (HTTPS). Buka aplikasi melalui alamat https, bukan http.');
        tampilOverlay(true);
        aturTombolToggle(false);
        tampilScanToast('error', 'Kamera butuh HTTPS (site bukan localhost).');
        return false;
    }

    function tampilSukses(nama, pesan) {
        document.getElementById('sukses-nama').textContent = nama;
        document.getElementById('sukses-pesan').textContent = pesan;
        panelSukses.classList.remove('hidden');
        laser.style.opacity = '0';
        tampilOverlay(false);
        aturTombolToggle(false);
    }
    function sembunyiSukses() {
        panelSukses.classList.add('hidden');
        laser.style.opacity = '1';
        tampilOverlay(false);
    }

    function onScanSuccess(decodedText, decodedResult) {
        if (isProcessing) return;
        isProcessing = true;

        fetch('{{ route('honor.proses-scan') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ qr_data: decodedText })
        })
        .then(response => response.json().then(data => ({ ok: response.ok, data })))
        .then(({ ok, data }) => {
            if (data.success) {
                scanSelesai = true;
                hentikanKamera();
                if (navigator.vibrate) navigator.vibrate(200);
                if (data.sudah_diterima_sebelumnya) {
                    document.getElementById('sukses-ikon').className = 'w-20 h-20 rounded-full bg-sky-500/20 text-sky-400 flex items-center justify-center text-4xl mb-5 shadow-[0_0_40px_rgba(14,165,233,0.6)]';
                    document.getElementById('sukses-ikon').innerHTML = '<i class="fas fa-circle-check"></i>';
                } else {
                    document.getElementById('sukses-ikon').className = 'w-20 h-20 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center text-4xl mb-5 shadow-[0_0_40px_rgba(16,185,129,0.6)]';
                    document.getElementById('sukses-ikon').innerHTML = '<i class="fas fa-hand-holding-dollar"></i>';
                }
                tampilSukses(data.nama_guru || 'Honor Diterima', data.pesan);
            } else {
                tampilScanToast('error', data.pesan || 'QR tidak dikenali.');
                if (navigator.vibrate) navigator.vibrate([300]);
            }
        })
        .catch(() => {
            const el = document.getElementById('sukses-ikon');
            if (el) el.innerHTML = '<i class="fas fa-circle-xmark"></i>';
            tampilScanToast('error', 'Gagal terhubung ke server.');
        })
        .finally(() => {
            setTimeout(() => { isProcessing = false; }, 400);
        });
    }

    function initKamera() {
        if (typeof Html5Qrcode === 'undefined') {
            setStatusKamera('Library kamera gagal dimuat. Muat ulang halaman.');
            tampilOverlay(true);
            aturTombolToggle(false);
            tampilScanToast('error', 'Library kamera gagal dimuat. Muat ulang halaman.');
            return false;
        }
        if (html5QrCode) return true;
        try {
            html5QrCode = new Html5Qrcode("reader");
            return true;
        } catch (err) {
            console.error("Gagal init kamera:", err);
            setStatusKamera('Terjadi kendala menyiapkan kamera. Coba lagi.');
            tampilOverlay(true);
            aturTombolToggle(false);
            return false;
        }
    }

    function mulaiKamera() {
        if (kameraBerjalan) return;
        if (scanSelesai) return;
        if (!konteksAman()) return;
        if (!html5QrCode && !initKamera()) return;

        tampilOverlay(false);
        tampilScanToast('info', 'Mengakses kamera…');

        function cobaMulai(facingMode) {
            html5QrCode.start(
                facingMode,
                { fps: 10, qrbox: { width: 220, height: 220 } },
                onScanSuccess
            ).then(function() {
                aturTombolToggle(true);
                tampilScanToast('success', 'Kamera menyala. Arahkan ke QR honor guru.');
            }).catch(function(err) {
                console.error("Gagal mengakses kamera:", err);
                if (typeof facingMode === 'object') {
                    cobaMulai(true);
                } else {
                    setStatusKamera('Gagal mengakses kamera. Izinkan akses kamera di browser, lalu coba lagi.');
                    tampilOverlay(true);
                    aturTombolToggle(false);
                    tampilScanToast('error', lokasiTidakAman() ? 'Kamera butuh HTTPS.' : 'Gagal mengakses kamera. Izinkan akses kamera di browser.');
                }
            });
        }
        cobaMulai({ facingMode: 'environment' });
    }

    function toggleKamera() {
        if (kameraBerjalan) {
            hentikanKamera();
            setStatusKamera('Kamera dimatikan.');
            tampilOverlay(true);
            tampilScanToast('info', 'Kamera dimatikan.');
        } else {
            scanSelesai = false;
            sembunyiSukses();
            mulaiKamera();
        }
    }

    function hentikanKamera() {
        if (!kameraBerjalan) return;
        aturTombolToggle(false);
        try {
            if (html5QrCode && typeof html5QrCode.stop === 'function') {
                html5QrCode.stop().catch(function() {});
            }
        } catch (err) {}
    }

    // ===== TAB =====
    function pindahTab(nama) {
        if (!panelScan || !panelQr) return;
        if (nama === 'scan') {
            scanSelesai = false;
            panelScan.classList.remove('hidden');
            panelQr.classList.add('hidden');
            tabScan.className = "w-1/2 flex items-center justify-center gap-2 py-2.5 rounded-xl text-sm font-black transition-all text-emerald-700 bg-white shadow-sm";
            tabQr.className = "w-1/2 flex items-center justify-center gap-2 py-2.5 rounded-xl text-sm font-black transition-all text-slate-400";
            sembunyiSukses();
            mulaiKamera();
        } else {
            panelQr.classList.remove('hidden');
            panelScan.classList.add('hidden');
            tabQr.className = "w-1/2 flex items-center justify-center gap-2 py-2.5 rounded-xl text-sm font-black transition-all text-emerald-700 bg-white shadow-sm";
            tabScan.className = "w-1/2 flex items-center justify-center gap-2 py-2.5 rounded-xl text-sm font-black transition-all text-slate-400";
            hentikanKamera();
        }
    }
    window.pindahTab = pindahTab;

    if (tabScan) tabScan.addEventListener('click', function() { pindahTab('scan'); });
    if (tabQr) tabQr.addEventListener('click', function() { pindahTab('qr'); });

    const btnScanLagi = document.getElementById('btn-scan-lagi');
    if (btnScanLagi) {
        btnScanLagi.addEventListener('click', function() {
            scanSelesai = false;
            sembunyiSukses();
            mulaiKamera();
        });
    }

    // START AMAN (auto-start tetap dicoba; bila gagal overlay + tombol muncul)
    function cobaMulaiAuto() {
        if (!panelScan) return;
        if (panelScan.classList.contains('hidden')) return;
        if (scanSelesai) return;
        if (typeof Html5Qrcode === 'undefined') return;
        if (document.visibilityState === 'visible') {
            if (kameraBerjalan) return;
            if (!konteksAman()) return;
            mulaiKamera();
        }
    }
    window.addEventListener('load', function() { setTimeout(cobaMulaiAuto, 300); });
    document.addEventListener('DOMContentLoaded', function() { setTimeout(cobaMulaiAuto, 300); });
    document.addEventListener('turbo:load', function() { setTimeout(cobaMulaiAuto, 300); });
    document.addEventListener('visibilitychange', function() {
        if (document.visibilityState === 'visible') cobaMulaiAuto();
    });
    window.addEventListener('pageshow', function() { setTimeout(cobaMulaiAuto, 300); });

    document.addEventListener('turbo:before-visit', hentikanKamera);
    window.addEventListener('pagehide', hentikanKamera);

    aturTombolToggle(false);
})();
</script>
@endpush
@endsection