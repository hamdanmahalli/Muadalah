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
        border-radius: 1rem;
    }
</style>

<div class="mb-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center">
            <a href="/agenda-kegiatan/{{ $agenda->id }}/laporan" class="w-10 h-10 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-slate-500 hover:bg-slate-50 hover:text-indigo-600 transition-colors mr-4 shadow-sm">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h2 class="text-2xl font-black text-slate-800 tracking-tight">Scan QR Guru</h2>
                <p class="text-sm font-bold text-slate-400 mt-0.5">
                    <i class="fas fa-calendar-alt mr-1"></i> {{ $agenda->nama_kegiatan }} • {{ \Carbon\Carbon::parse($agenda->tanggal)->translatedFormat('d F Y') }}
                </p>
            </div>
        </div>

        @if(!$agenda->is_open)
        <span class="inline-flex items-center gap-2 px-4 py-2 bg-rose-50 text-rose-600 border border-rose-200 font-bold text-sm rounded-xl">
            <i class="fas fa-lock"></i> Absensi Ditutup
        </span>
        @endif
    </div>
</div>

@if(session('sukses'))
<div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl mb-6 flex items-center shadow-sm">
    <i class="fas fa-check-circle text-xl mr-3"></i>
    <span class="font-bold text-sm">{{ session('sukses') }}</span>
</div>
@endif

<!-- Kartu Kamera Scan QR Guru -->
<div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm max-w-lg mx-auto relative overflow-hidden">
    <div class="text-center mb-4">
        <span class="inline-block px-3 py-1 bg-emerald-50 text-emerald-600 text-[10px] font-black uppercase tracking-widest rounded-full border border-emerald-100">
            <i class="fas fa-circle text-[6px] mr-1.5 animate-pulse"></i> Kamera Aktif
        </span>
        <p class="text-sm font-bold text-slate-500 mt-3">
            Arahkan kamera ke QR Code pribadi guru (GURU-&lt;NIG&gt;) untuk absen hadir di kegiatan ini.
        </p>
    </div>

    <!-- Area Kamera -->
    <div class="w-full bg-slate-900 rounded-2xl overflow-hidden relative h-[300px] shadow-inner flex items-center justify-center">
        <div id="reader" class="w-full h-full absolute inset-0"></div>
        <div class="absolute inset-x-6 top-0 h-0.5 bg-emerald-400 shadow-[0_0_10px_#10b981] animate-[scanLine_2s_ease-in-out_infinite_alternate] pointer-events-none"></div>
    </div>

    <p class="text-[11px] font-medium text-slate-400 text-center mt-3">
        Pastikan QR Code berada dalam jangkauan kamera.
    </p>
</div>

<style>
    @keyframes scanLine {
        0% { top: 0%; }
        50% { opacity: 1; }
        100% { top: 95%; }
    }
</style>
@endsection

@push('scripts')
<!-- Library Html5Qrcode -->
<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
<script>
(function() {
    const agendaId = '{{ $agenda->id }}';
    let isProcessing = false;
    let html5QrCode;
    let kameraBerjalan = false;

    function onScanSuccess(decodedText, decodedResult) {
        if (isProcessing) return;
        isProcessing = true;

        // Saat offline tidak bisa mengirim absen
        if (typeof wajibOnline === 'function' && !wajibOnline(null, 'Scan absen butuh koneksi internet')) {
            if (typeof tampilNotif === 'function') tampilNotif('error', 'Mode Offline', 'Absen butuh internet.');
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
                if (typeof tampilNotif === 'function') tampilNotif('success', 'Hadir Tercatat', data.pesan);
                if (navigator.vibrate) navigator.vibrate(200);
            } else if (data.status === 'info') {
                if (typeof tampilNotif === 'function') tampilNotif('info', 'Sudah Tercatat', data.pesan);
            } else {
                if (typeof tampilNotif === 'function') tampilNotif('error', 'Ditolak', data.pesan);
                if (navigator.vibrate) navigator.vibrate([300]);
            }
        })
        .catch(() => {
            if (typeof tampilNotif === 'function') tampilNotif('error', 'Gagal', 'Gagal terhubung ke server.');
        })
        .finally(() => {
            // Siap untuk scan berikutnya
            setTimeout(() => { isProcessing = false; }, 400);
        });
    }

    html5QrCode = new Html5Qrcode("reader");
    window.__html5QrTu = html5QrCode;

    function mulaiKamera() {
        if (kameraBerjalan) return;
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

    mulaiKamera();

    document.addEventListener('turbo:before-visit', hentikanKamera);
    window.addEventListener('pagehide', hentikanKamera);
})();
</script>
@endpush
