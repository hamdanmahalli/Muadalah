@extends('layouts.app')

@section('title', 'Honor Saya - Muadalah Wustha')

@section('content')
<style>
    header, aside { display: none !important; }
    #btn-buka-sidebar { display: none !important; }
    main { padding: 0 !important; background-color: #f8fafc !important; overflow: hidden !important; }
    body { overflow: hidden !important; background-color: #f8fafc !important; }
    .scrollbar-none::-webkit-scrollbar { display: none; }
    .scrollbar-none { -ms-overflow-style: none; scrollbar-width: none; }
    #reader video { object-fit: cover !important; width: 100% !important; height: 100% !important; }
    @keyframes scanLine {
        0% { top: 4%; opacity: 0.8; }
        50% { opacity: 1; }
        100% { top: 92%; opacity: 0.8; }
    }
    .scanner-line {
        position: absolute;
        left: 14%;
        right: 14%;
        height: 2px;
        background: #10b981;
        box-shadow: 0 0 12px #10b981, 0 0 24px #10b981;
        animation: scanLine 2s ease-in-out infinite alternate;
    }
</style>

<div data-turbo="true" class="max-w-md mx-auto h-[100dvh] bg-slate-100 flex flex-col relative font-sans overflow-hidden">

    <!-- HEADER -->
    <div class="shrink-0 bg-gradient-to-br from-emerald-600 via-emerald-600 to-teal-700 px-5 pt-6 pb-14 relative z-10 overflow-hidden">
        <div class="absolute -right-16 -top-16 w-56 h-56 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
        <div class="absolute -left-12 -bottom-20 w-48 h-48 bg-teal-400/20 rounded-full blur-2xl pointer-events-none"></div>
        <div class="relative z-10 flex items-center justify-between gap-3">
            <a href="/dashboard-guru" class="w-10 h-10 rounded-full bg-white/15 backdrop-blur border border-white/20 flex items-center justify-center text-white active:scale-95 transition">
                <i class="fas fa-arrow-left text-sm"></i>
            </a>
            <div class="flex-1 min-w-0 text-center">
                <p class="text-[10px] font-black text-emerald-100 uppercase tracking-widest">Honor Bisyaroh</p>
                <h2 class="text-lg font-black text-white tracking-tight truncate">{{ $guru->nama_guru }}</h2>
            </div>
            <span class="px-3 py-1.5 rounded-full bg-white/15 backdrop-blur border border-white/20 text-[10px] font-black text-white">NIG: {{ $guru->nig }}</span>
        </div>
    </div>

    <!-- KONTEN -->
    <div class="flex-1 overflow-y-auto scrollbar-none px-5 pt-0 pb-32 relative z-20 -mt-8">

        <div class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3.5 flex items-start gap-3 text-xs font-semibold text-emerald-800 shadow-sm">
            <div class="w-8 h-8 rounded-xl bg-emerald-100 flex items-center justify-center shrink-0 text-emerald-600">
                <i class="fas fa-circle-info text-sm"></i>
            </div>
            <span class="leading-relaxed pt-1">Slip honor tampil di sini setiap bulan. Saat sudah menerima honor, status berubah menjadi "Sudah Diterima".</span>
        </div>

        @forelse($honors as $h)
        @php
            $bulanNama = $bulanIndonesia[$h->periode->bulan] ?? $h->periode->bulan;
        @endphp
        <div class="bg-white rounded-3xl shadow-[0_14px_34px_-16px_rgba(2,6,23,0.2)] border border-slate-100 overflow-hidden mb-5">
            <!-- Kepala Slip -->
            <div class="bg-gradient-to-r from-emerald-600 to-teal-700 px-5 py-4 flex items-center justify-between">
                <div>
                    <p class="text-[9px] font-black text-emerald-200 uppercase tracking-widest">Slip Honor</p>
                    <h3 class="text-lg font-black text-white tracking-tight">{{ $bulanNama }} {{ $h->periode->tahun }}</h3>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-white/15 border border-white/20 flex items-center justify-center text-white backdrop-blur">
                    <i class="fas fa-money-bill-wave text-xl"></i>
                </div>
            </div>

            <!-- Rincian -->
            <div class="px-5 py-4 divide-y divide-slate-100 text-sm">
                <div class="flex justify-between items-center py-2">
                    <span class="text-[12px] font-bold text-slate-500 flex items-center"><i class="fas fa-book-open text-slate-300 w-6"></i> Honor Mengajar</span>
                    <span class="font-black text-slate-800">Rp {{ number_format($h->honor_pokok, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between items-center py-2">
                    <span class="text-[12px] font-bold text-slate-500 flex items-center"><i class="fas fa-clock text-slate-300 w-6"></i> Honor Piket</span>
                    <span class="font-black text-slate-800">Rp {{ number_format($h->honor_piket, 0, ',', '.') }}</span>
                </div>
                @if($h->tunjangan_struktural > 0)
                <div class="flex justify-between items-center py-2">
                    <span class="text-[12px] font-bold text-slate-500 flex items-center"><i class="fas fa-briefcase text-slate-300 w-6"></i> Tunjangan Struktural</span>
                    <span class="font-bold text-slate-700">Rp {{ number_format($h->tunjangan_struktural, 0, ',', '.') }}</span>
                </div>
                @endif
                @if($h->tunjangan_wali_kelas > 0)
                <div class="flex justify-between items-center py-2">
                    <span class="text-[12px] font-bold text-slate-500 flex items-center"><i class="fas fa-users text-slate-300 w-6"></i> Wali Kelas</span>
                    <span class="font-bold text-slate-700">Rp {{ number_format($h->tunjangan_wali_kelas, 0, ',', '.') }}</span>
                </div>
                @endif
                @if($h->transport > 0)
                <div class="flex justify-between items-center py-2">
                    <span class="text-[12px] font-bold text-slate-500 flex items-center"><i class="fas fa-bus text-slate-300 w-6"></i> Transport</span>
                    <span class="font-bold text-slate-700">Rp {{ number_format($h->transport, 0, ',', '.') }}</span>
                </div>
                @endif
                <div class="flex justify-between items-center py-3 mt-1 border-t-2 border-dashed border-slate-200">
                    <span class="text-[13px] font-black text-slate-700 uppercase tracking-wider">Total</span>
                    <span class="text-xl font-black text-emerald-600">Rp {{ number_format($h->total, 0, ',', '.') }}</span>
                </div>
            </div>

            <!-- Status -->
            <div class="px-5 pb-5">
                @if($h->is_diterima)
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3.5 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-emerald-500 text-white flex items-center justify-center shrink-0 shadow-sm">
                        <i class="fas fa-check text-lg"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-black text-emerald-700">SUDAH DITERIMA</p>
                        <p class="text-[11px] font-bold text-emerald-600/80 mt-0.5">
                            {{ $h->waktu_diterima ? $h->waktu_diterima->translatedFormat('l, d F Y · H:i') : 'Tercatat' }}
                            @if($h->metode_penerimaan) · via {{ $h->metode_penerimaan }} @endif
                        </p>
                    </div>
                </div>
                @else
                <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3.5 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center shrink-0 shadow-sm">
                        <i class="fas fa-hourglass-half text-lg"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-black text-amber-700">MENUNGGU PENERIMAAN</p>
                        <p class="text-[11px] font-bold text-amber-600/80 mt-0.5">Tunjukkan QR honor Anda kepada TU saat menerima.</p>
                    </div>
                    <button onclick="bukaModalQr('{{ $h->qr_token }}', '{{ addslashes($guru->nama_guru) }}', '{{ $bulanNama }} {{ $h->periode->tahun }}')" class="w-10 h-10 rounded-xl bg-amber-100 text-amber-700 hover:bg-amber-500 hover:text-white flex items-center justify-center transition active:scale-95 shrink-0">
                        <i class="fas fa-qrcode text-lg"></i>
                    </button>
                </div>
                @endif
            </div>
        </div>
        @empty
        <div class="bg-white rounded-3xl p-8 border border-dashed border-slate-200 flex flex-col items-center text-center shadow-sm">
            <div class="w-16 h-16 bg-slate-50 text-slate-300 rounded-full flex items-center justify-center mb-4 text-3xl"><i class="fas fa-hand-holding-dollar"></i></div>
            <h3 class="text-base font-black text-slate-700">Belum Ada Slip Honor</h3>
            <p class="text-xs font-medium text-slate-400 mt-1 leading-relaxed">Setelah admin menghitung honor bulan ini, slip akan tampil di sini.</p>
            <a href="/dashboard-guru" class="mt-5 px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold rounded-2xl transition shadow-md active:scale-95">
                <i class="fas fa-arrow-left mr-2"></i> Kembali ke Beranda
            </a>
        </div>
        @endforelse

        <!-- Tombol scan konfirmasi oleh guru sendiri -->
        @if($honors->where('is_diterima', false)->count() > 0)
        <button onclick="bukaModalScan()" class="w-full mb-4 inline-flex items-center justify-center gap-2 px-5 py-4 bg-slate-900 hover:bg-slate-800 text-white text-sm font-black rounded-2xl shadow-lg active:scale-95 transition-all">
            <i class="fas fa-camera text-base"></i> Konfirmasi Terima via Scan QR
        </button>
        @endif
    </div>

    @include('partials.bottom-nav', ['active' => 'beranda'])
</div>

<!-- MODAL QR HONOR -->
<div id="modal-qr-honor" class="fixed inset-0 z-[100] hidden">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity opacity-0" id="bg-qr-honor"></div>
    <div class="flex items-center justify-center min-h-screen px-4 pb-10">
        <div class="bg-white w-full max-w-sm rounded-[2rem] p-6 shadow-2xl transform scale-95 opacity-0 transition-all duration-300 flex flex-col items-center text-center" id="box-qr-honor">
            <span class="inline-block px-3 py-1.5 bg-emerald-50 text-emerald-600 text-[10px] font-black uppercase tracking-widest rounded-full border border-emerald-100 mb-5">
                <i class="fas fa-money-bill-wave text-[10px] mr-1.5"></i> QR Honor — <span id="qr-bulan"></span>
            </span>
            <div class="w-[210px] h-[210px] bg-slate-50 rounded-2xl border border-slate-100 flex items-center justify-center p-2">
                <img id="qr-img" src="" alt="QR Honor" class="w-full h-full object-contain rounded-lg">
            </div>
            <div class="text-center mt-5">
                <p id="qr-nama" class="text-base font-black text-slate-900"></p>
            </div>
            <p class="text-[11px] font-medium text-slate-400 text-center mt-4 leading-relaxed">
                Tunjukkan QR ini kepada TU saat menerima honor.<br>Petugas akan memindainya sebagai bukti penerimaan.
            </p>
            <button onclick="tutupModalQr()" class="w-full py-3.5 bg-slate-900 hover:bg-slate-800 text-white text-sm font-bold rounded-2xl transition shadow-md mt-6">Tutup</button>
        </div>
    </div>
</div>

<!-- MODAL SCAN KONFIRMASI HONOR -->
<div id="modal-scan-honor" class="fixed inset-0 z-[100] hidden">
    <div class="absolute inset-0 bg-slate-900/90 backdrop-blur-sm transition-opacity opacity-0" id="bg-scan-honor"></div>
    <div class="flex flex-col h-[100dvh]">
        <div class="shrink-0 bg-white px-4 py-4 z-30">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <button onclick="tutupModalScan()" class="w-10 h-10 rounded-xl bg-slate-50 flex items-center justify-center text-slate-500 hover:bg-slate-100 transition-colors mr-4">
                        <i class="fas fa-arrow-left"></i>
                    </button>
                    <div>
                        <h2 class="text-base font-black text-slate-800 tracking-tight">Scan Konfirmasi Honor</h2>
                        <p class="text-[9px] font-bold text-emerald-600 uppercase tracking-widest mt-0.5">Scan QR slip honor Anda</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="flex-1 relative overflow-hidden z-0 bg-black min-h-0">
            <div id="reader-honor" class="absolute inset-0"></div>
            <div id="laser-line-honor" class="scanner-line pointer-events-none z-10 transition-opacity duration-300"></div>
            <p class="absolute inset-x-0 bottom-3 z-20 text-white text-[12px] font-semibold text-center px-4">
                Arahkan kamera ke QR honor (HONOR-...) yang ditampilkan atau dari layar TU.
            </p>
            <div id="panel-sukses-honor" class="hidden absolute inset-0 z-30 flex flex-col items-center justify-center bg-black/85 backdrop-blur-sm px-6 text-center">
                <div class="w-20 h-20 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center text-4xl mb-5 shadow-[0_0_40px_rgba(16,185,129,0.6)]">
                    <i class="fas fa-check"></i>
                </div>
                <p id="sukses-nama-honor" class="text-white font-black tracking-widest text-xl uppercase drop-shadow-md">Honor Diterima</p>
                <p id="sukses-pesan-honor" class="text-slate-200 text-sm mt-2 font-bold leading-snug drop-shadow-sm px-2"></p>
                <button type="button" id="btn-scan-lagi-honor"
                    class="mt-6 px-6 py-3 bg-emerald-500 hover:bg-emerald-600 text-white font-black rounded-xl shadow-md shadow-emerald-500/30 transition-all active:scale-95">
                    <i class="fas fa-camera mr-2"></i> Pindai Lagi
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function bukaModalQr(token, nama, bulan) {
        document.getElementById('qr-img').src = 'https://api.qrserver.com/v1/create-qr-code/?size=280x280&data=' + encodeURIComponent(token) + '&margin=8';
        document.getElementById('qr-nama').textContent = nama;
        document.getElementById('qr-bulan').textContent = bulan;
        const modal = document.getElementById('modal-qr-honor');
        modal.classList.remove('hidden');
        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                document.getElementById('bg-qr-honor').classList.remove('opacity-0');
                const box = document.getElementById('box-qr-honor');
                box.classList.remove('scale-95', 'opacity-0');
                box.classList.add('scale-100', 'opacity-100');
            });
        });
    }
    function tutupModalQr() {
        const modal = document.getElementById('modal-qr-honor');
        document.getElementById('bg-qr-honor').classList.add('opacity-0');
        const box = document.getElementById('box-qr-honor');
        box.classList.remove('scale-100', 'opacity-100');
        box.classList.add('scale-95', 'opacity-0');
        setTimeout(() => modal.classList.add('hidden'), 300);
    }

    // ===== MODAL SCAN =====
    let html5QrHonor = null;
    let kameraHonorBerjalan = false;
    let isProcessingHonor = false;
    let scanHonorSelesai = false;

    function bukaModalScan() {
        scanHonorSelesai = false;
        document.getElementById('panel-sukses-honor').classList.add('hidden');
        document.getElementById('laser-line-honor').style.opacity = '1';
        const modal = document.getElementById('modal-scan-honor');
        modal.classList.remove('hidden');
        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                document.getElementById('bg-scan-honor').classList.remove('opacity-0');
            });
        });
        setTimeout(mulaiKameraHonor, 400);
    }
    function tutupModalScan() {
        hentikanKameraHonor();
        const modal = document.getElementById('modal-scan-honor');
        document.getElementById('bg-scan-honor').classList.add('opacity-0');
        setTimeout(() => modal.classList.add('hidden'), 300);
    }

    function onScanHonorSuccess(decodedText) {
        if (isProcessingHonor) return;
        isProcessingHonor = true;
        fetch('/guru/honor/scan', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ qr_data: decodedText })
        })
        .then(r => r.json().then(d => ({ ok: r.ok, d })))
        .then(({ d }) => {
            if (d.success) {
                scanHonorSelesai = true;
                hentikanKameraHonor();
                if (navigator.vibrate) navigator.vibrate(200);
                document.getElementById('sukses-nama-honor').textContent = 'SUDAH DITERIMA';
                document.getElementById('sukses-pesan-honor').textContent = d.pesan;
                document.getElementById('panel-sukses-honor').classList.remove('hidden');
                document.getElementById('laser-line-honor').style.opacity = '0';
            } else {
                if (typeof tampilToast === 'function') tampilToast('error', d.pesan || 'QR tidak dikenali.');
            }
        })
        .catch(() => {
            if (typeof tampilToast === 'function') tampilToast('error', 'Gagal terhubung ke server.');
        })
        .finally(() => setTimeout(() => { isProcessingHonor = false; }, 500));
    }

    function mulaiKameraHonor() {
        if (kameraHonorBerjalan) return;
        if (typeof Html5Qrcode === 'undefined') return;
        if (!html5QrHonor) {
            try { html5QrHonor = new Html5Qrcode("reader-honor"); } catch (e) { return; }
        }
        html5QrHonor.start(
            { facingMode: "environment" },
            { fps: 10, qrbox: { width: 220, height: 220 } },
            onScanHonorSuccess
        ).then(() => { kameraHonorBerjalan = true; })
        .catch(err => {
            console.error("Gagal kamera:", err);
            if (typeof tampilToast === 'function') tampilToast('error', 'Gagal mengakses kamera.');
        });
    }
    function hentikanKameraHonor() {
        if (!kameraHonorBerjalan) return;
        kameraHonorBerjalan = false;
        try { if (html5QrHonor && typeof html5QrHonor.stop === 'function') html5QrHonor.stop().catch(function() {}); } catch (e) {}
    }

    document.getElementById('btn-scan-lagi-honor').addEventListener('click', function() {
        scanHonorSelesai = false;
        document.getElementById('panel-sukses-honor').classList.add('hidden');
        document.getElementById('laser-line-honor').style.opacity = '1';
        mulaiKameraHonor();
    });
</script>
<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
@endsection