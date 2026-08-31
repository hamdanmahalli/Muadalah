@extends('layouts.app')

@section('title', 'Scan QR Guru - ' . $agenda->nama_kegiatan)

@section('content')
@push('styles')
    <meta name="turbo-cache-control" content="no-cache">
@endpush
<style>
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
</style>

<div class="max-w-md mx-auto h-[100dvh] bg-black flex flex-col relative font-sans overflow-hidden">

    <!-- HEADER -->
    <div class="shrink-0 bg-white px-4 py-4 z-30">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <a href="/agenda-kegiatan/{{ $agenda->id }}/laporan" class="w-10 h-10 rounded-xl bg-slate-50 flex items-center justify-center text-slate-500 hover:bg-slate-100 hover:text-emerald-600 transition-colors mr-4">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div>
                    <h2 class="text-base font-black text-slate-800 tracking-tight">Scan QR Guru</h2>
                    <p class="text-[9px] font-bold text-emerald-600 uppercase tracking-widest mt-0.5">{{ $agenda->nama_kegiatan }}</p>
                </div>
            </div>

            @if(!$agenda->is_open)
            <span class="inline-flex items-center gap-2 px-3 py-1.5 bg-rose-50 text-rose-600 border border-rose-200 font-bold text-xs rounded-lg">
                <i class="fas fa-lock"></i> Ditutup
            </span>
            @endif
        </div>
    </div>

    <!-- AREA KAMERA FULL SCREEN -->
    <div class="flex-1 relative overflow-hidden z-0 flex flex-col bg-black">

        <!-- Area video kamera (layar hitam penuh) -->
        <div class="flex-1 relative min-h-0">
            <div id="reader" class="absolute inset-0"></div>
            <div id="laser-line" class="tu-scanner-line pointer-events-none z-10 transition-opacity duration-300"></div>

            <!-- Caption di atas layar hitam (antara kotak fokus & pembatas putih) -->
            <p class="absolute inset-x-0 bottom-3 z-20 text-white text-[12px] font-semibold text-center">
                Arahkan kamera ke QR Code pribadi guru (GURU-NIG)
            </p>

            <!-- Panel Hasil Sukses -->
            <div id="panel-sukses" class="hidden absolute inset-0 z-30 flex flex-col items-center justify-center bg-black/85 backdrop-blur-sm px-6 text-center">
                <div class="w-20 h-20 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center text-4xl mb-5 shadow-[0_0_40px_rgba(16,185,129,0.6)]">
                    <i class="fas fa-check"></i>
                </div>
                <p id="sukses-nama" class="text-white font-black tracking-widest text-xl uppercase drop-shadow-md">Hadir Tercatat</p>
                <p id="sukses-pesan" class="text-slate-200 text-sm mt-2 font-bold leading-snug drop-shadow-sm px-2"></p>
                <button type="button" id="btn-scan-lagi"
                    class="mt-6 px-6 py-3 bg-emerald-500 hover:bg-emerald-600 text-white font-black rounded-xl shadow-md shadow-emerald-500/30 transition-all active:scale-95">
                    <i class="fas fa-camera mr-2"></i> Pindai Lagi
                </button>
            </div>
        </div>

        <!-- Pembatas putih di bagian bawah -->
        <div class="shrink-0 bg-white border-t border-slate-200 relative z-20 h-10"></div>
    </div>
</div>

@push('scripts')
<!-- Library Html5Qrcode -->
<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
<script>
(function() {
    const agendaId = '{{ $agenda->id }}';
    let isProcessing = false;
    let html5QrCode = null;
    let kameraBerjalan = false;
    let scanSelesai = false;

    const panelSukses = document.getElementById('panel-sukses');
    const laser = document.getElementById('laser-line');

    function tampilSukses(nama, pesan) {
        document.getElementById('sukses-nama').textContent = nama;
        document.getElementById('sukses-pesan').textContent = pesan;
        panelSukses.classList.remove('hidden');
        laser.style.opacity = '0';
    }
    function sembunyiSukses() {
        panelSukses.classList.add('hidden');
        laser.style.opacity = '1';
    }

    function onScanSuccess(decodedText, decodedResult) {
        if (isProcessing) return;
        isProcessing = true;

        // Saat offline tidak bisa mengirim absen
        if (typeof wajibOnline === 'function' && !wajibOnline(null, 'Scan absen butuh koneksi internet')) {
            if (typeof tampilNotif === 'function') tampilNotif('error', 'Mode Offline', 'Absen butuh internet.');
            isProcessing = false;
            return;
        }

        fetch('/agenda-kegiatan/' + agendaId + '/scan-proses-guru', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ qr_data: decodedText })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                // Sukses: matikan kamera + tampilkan panel sukses
                scanSelesai = true;
                hentikanKamera();
                if (navigator.vibrate) navigator.vibrate(200);
                tampilSukses('Hadir Tercatat', data.pesan);
            } else if (data.status === 'info') {
                // Sudah tercatat: kamera tetap aktif (info)
                if (typeof tampilNotif === 'function') tampilNotif('info', 'Sudah Tercatat', data.pesan);
            } else {
                // Error: kamera tetap aktif
                if (typeof tampilNotif === 'function') tampilNotif('error', 'Ditolak', data.pesan);
                if (navigator.vibrate) navigator.vibrate([300]);
            }
        })
        .catch(() => {
            if (typeof tampilNotif === 'function') tampilNotif('error', 'Gagal', 'Gagal terhubung ke server.');
        })
        .finally(() => {
            setTimeout(() => { isProcessing = false; }, 400);
        });
    }

    function initKamera() {
        if (typeof Html5Qrcode === 'undefined') {
            if (typeof tampilNotif === 'function') tampilNotif('error', 'Kamera', 'Library kamera gagal dimuat. Muat ulang halaman.');
            return;
        }
        if (html5QrCode) return;
        try {
            html5QrCode = new Html5Qrcode("reader");
            window.__html5QrTu = html5QrCode;
        } catch (err) {
            console.error("Gagal init kamera:", err);
        }
    }

    function mulaiKamera() {
        if (kameraBerjalan) return;
        if (!html5QrCode) { initKamera(); }
        if (!html5QrCode) return;

        html5QrCode.start(
            { facingMode: "environment" },
            { fps: 10, qrbox: { width: 220, height: 220 } },
            onScanSuccess
        ).then(function() {
            kameraBerjalan = true;
        }).catch(function(err) {
            console.error("Gagal mengakses kamera:", err);
            if (typeof tampilNotif === 'function') tampilNotif('error', 'Kamera', 'Gagal mengakses kamera. Aktifkan izin kamera di browser.');
        });
    }

    function hentikanKamera() {
        if (!kameraBerjalan) return;
        kameraBerjalan = false;
        try {
            if (html5QrCode && typeof html5QrCode.stop === 'function') {
                html5QrCode.stop().catch(function() {});
            }
        } catch (err) {}
    }

    document.getElementById('btn-scan-lagi').addEventListener('click', function() {
        scanSelesai = false;
        sembunyiSukses();
        mulaiKamera();
    });

    // START AMAN
    function cobaMulaiKamera() {
        if (scanSelesai) return;
        if (document.visibilityState === 'visible') mulaiKamera();
    }
    window.addEventListener('load', function() { setTimeout(cobaMulaiKamera, 300); });
    document.addEventListener('DOMContentLoaded', function() { setTimeout(cobaMulaiKamera, 300); });
    document.addEventListener('turbo:load', function() { setTimeout(cobaMulaiKamera, 300); });
    document.addEventListener('visibilitychange', function() {
        if (document.visibilityState === 'visible') cobaMulaiKamera();
    });
    window.addEventListener('pageshow', function() { setTimeout(cobaMulaiKamera, 300); });

    document.addEventListener('turbo:before-visit', hentikanKamera);
    window.addEventListener('pagehide', hentikanKamera);
})();
</script>
@endpush
@endsection
