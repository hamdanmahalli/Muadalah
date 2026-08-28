@extends('layouts.app')
@section('title', 'Scan Kehadiran Kelas - SmartPesantren')

@section('content')
@push('styles')
    {{-- Turbo: jangan simpan snapshot halaman kamera (hindari scanner macet saat Back/Forward) --}}
    <meta name="turbo-cache-control" content="no-cache">
@endpush
<style>
    header, aside { display: none !important; }
    #app main { padding: 0 !important; background-color: #000 !important; overflow: hidden !important; }
    body { overflow: hidden !important; background-color: #000 !important; }
    .scrollbar-none::-webkit-scrollbar { display: none; }
    .scrollbar-none { -ms-overflow-style: none; scrollbar-width: none; }
    @supports (padding-bottom: env(safe-area-inset-bottom)) { .pb-safe { padding-bottom: env(safe-area-inset-bottom); } }

    /* Memastikan elemen video memenuhi area kamera secara presisi */
    #reader video {
        object-fit: cover !important;
        width: 100% !important;
        height: 100% !important;
    }

    /* Animasi Garis Scanner */
    @keyframes scanLine {
        0% { top: 4%; opacity: 0.8; }
        50% { opacity: 1; }
        100% { top: 92%; opacity: 0.8; }
    }
    .scanner-line {
        position: absolute;
        left: 12%;
        right: 12%;
        height: 2px;
        background: #10b981;
        box-shadow: 0 0 12px #10b981, 0 0 24px #10b981;
        animation: scanLine 2s infinite ease-in-out alternate;
    }
</style>

<div data-turbo="true" class="max-w-md mx-auto h-[100dvh] bg-black flex flex-col relative font-sans overflow-hidden">

    <!-- HEADER: kembali + judul + TAB -->
    <div class="shrink-0 bg-white px-4 pt-4 pb-3 z-30">
        <div class="flex items-center">
            <a href="javascript:history.back()" class="w-10 h-10 rounded-2xl bg-slate-50 text-slate-500 flex items-center justify-center hover:bg-slate-100 hover:text-emerald-600 active:scale-95 transition-all">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div class="flex-1 px-4">
                <h2 class="text-base font-black text-slate-800 tracking-tight">Scan Barcode Kelas</h2>
                <p class="text-[9px] font-bold text-emerald-600 uppercase tracking-widest mt-0.5">Kehadiran & QR Pribadi</p>
            </div>
        </div>

        <!-- TAB: Scan QR | QR Code -->
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
    </div>

    <!-- AREA KAMERA FULL SCREEN (di bawah tab) -->
    <div id="panel-scan" class="flex-1 relative overflow-hidden z-0 flex flex-col bg-black">
        <!-- Area video kamera (layar hitam penuh) -->
        <div class="flex-1 relative min-h-0">
            <div id="reader" class="absolute inset-0"></div>

            <!-- Garis laser pemandu -->
            <div id="laser-line" class="scanner-line pointer-events-none z-10 transition-opacity duration-300"></div>

            <!-- Caption di atas layar hitam (antara kotak fokus & pembatas putih) -->
            <p id="teks-bantuan" class="absolute inset-x-0 bottom-3 z-20 text-white text-[12px] font-semibold text-center transition-opacity duration-300">
                Arahkan kamera ke QR Code
            </p>

            <!-- Panel Hasil Sukses (kamera dimatikan) -->
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

    <!-- ============ PANEL 2: QR CODE PRIBADI GURU ============ -->
    <div id="panel-qr" class="hidden flex-1 z-0 bg-gray-50 overflow-y-auto scrollbar-none p-5 flex flex-col items-center justify-center">
        <div class="w-full bg-white p-6 rounded-3xl border border-slate-200 shadow-sm flex flex-col items-center">
            <span class="inline-block px-3 py-1 bg-emerald-50 text-emerald-600 text-[10px] font-black uppercase tracking-widest rounded-full border border-emerald-100 mb-4">
                <i class="fas fa-user-tag text-[10px] mr-1.5"></i> QR Pribadi Guru
            </span>

            @if($guru && $qrPribadi)
            <div class="w-[210px] h-[210px] bg-slate-50 rounded-2xl border border-slate-200 flex items-center justify-center p-2 shadow-inner">
                <img src="data:image/svg+xml,{{ $qrPribadi }}" alt="QR Pribadi {{ $guru->nama_guru }}" class="w-full h-full object-contain rounded-lg">
            </div>
            <div class="text-center mt-4">
                <p class="text-base font-black text-slate-800">{{ $guru->nama_guru }}</p>
                <p class="text-xs font-bold text-emerald-600">NIG: {{ $guru->nig }}</p>
            </div>
            <p class="text-[11px] font-medium text-slate-400 text-center mt-4 leading-relaxed">
                Tunjukkan QR ini kepada TU saat absen kegiatan.<br>
                TU akan memindainya sebagai bukti kehadiran Anda.
            </p>
            @else
            <div class="w-[210px] h-[210px] rounded-2xl bg-slate-50 border border-dashed border-slate-300 flex items-center justify-center">
                <i class="fas fa-user-slash text-4xl text-slate-300"></i>
            </div>
            <p class="text-sm font-bold text-slate-500 mt-4 text-center">Profil guru tidak ditemukan.<br>Hubungi admin untuk data akun Anda.</p>
            @endif
        </div>
    </div>

    <!-- MODAL KONFIRMASI PIKET -->
    <div id="modal-piket" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm px-4">
        <div class="bg-white rounded-3xl w-full max-w-sm overflow-hidden shadow-2xl transform scale-100 transition-transform">
            <div class="p-6 text-center">
                <div class="w-16 h-16 bg-indigo-50 text-indigo-600 rounded-full flex items-center justify-center text-3xl mx-auto mb-4 shadow-inner">
                    <i class="fas fa-exchange-alt"></i>
                </div>
                <h3 class="text-xl font-black text-slate-800 tracking-tight mb-2">Deteksi Jadwal Berbeda</h3>
                <p id="teks-konfirmasi-piket" class="text-sm text-slate-600 leading-relaxed font-medium mb-6"></p>
                <div class="flex gap-3">
                    <button onclick="batalPiket()" class="w-full py-3 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold rounded-xl transition-colors active:scale-95">Bukan</button>
                    <button id="btn-lanjut-piket" onclick="lanjutPiket()" class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-md shadow-indigo-500/30 transition-all active:scale-95">Ya, Saya Inval</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Library Html5Qrcode -->
<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
<script>
    (function() {
        let isProcessing = false;
        let html5QrCode = null;
        let kameraBerjalan = false;
        let jadwalIdsPiketSementara = [];

        const panelScan = document.getElementById('panel-scan');
        const panelQr = document.getElementById('panel-qr');
        const panelSukses = document.getElementById('panel-sukses');
        const tabScan = document.getElementById('tab-scan');
        const tabQr = document.getElementById('tab-qr');
        const laser = document.getElementById('laser-line');

        // ===== TAMPILKAN / SEMBUNYIKAN PANEL SUKSES (kamera dimatikan) =====
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

            // Saat offline tidak bisa mengirimkan kehadiran
            if (typeof wajibOnline === 'function' && !wajibOnline(null, 'Scan Hadir memerlukan koneksi internet')) {
                if (typeof tampilNotif === 'function') tampilNotif('error', 'Mode Offline', 'Scan kehadiran butuh internet.');
                setTimeout(() => { isProcessing = false; }, 2000);
                return;
            }

            fetch('/scan-proses', {
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
                    hentikanKamera();
                    if (navigator.vibrate) navigator.vibrate(200);
                    tampilSukses('Hadir Tercatat', data.pesan || 'Kehadiran Anda telah tercatat.');
                }
                else if (data.status === 'confirm_piket') {
                    // Kamera tetap aktif, munculkan modal konfirmasi
                    if (navigator.vibrate) navigator.vibrate([100, 50, 100]);
                    document.getElementById('teks-konfirmasi-piket').innerHTML = data.pesan;
                    jadwalIdsPiketSementara = data.data.jadwal_ids;
                    document.getElementById('modal-piket').classList.remove('hidden');
                    isProcessing = false;
                }
                else {
                    // Error: kamera tetap aktif agar bisa scan ulang
                    if (typeof tampilNotif === 'function') tampilNotif('error', 'Gagal', data.pesan);
                    if (navigator.vibrate) navigator.vibrate([300]);
                    isProcessing = false;
                }
            })
            .catch(error => {
                if (typeof tampilNotif === 'function') tampilNotif('error', 'Gagal', 'Gagal terhubung ke server.');
                isProcessing = false;
            });
        }

        // Batal di modal piket
        window.batalPiket = function() {
            document.getElementById('modal-piket').classList.add('hidden');
            isProcessing = false;
        }

        // "Ya, Saya Inval"
        window.lanjutPiket = function() {
            let btnLanjut = document.getElementById('btn-lanjut-piket');
            btnLanjut.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i>';
            btnLanjut.disabled = true;

            fetch('/scan-piket', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ jadwal_ids: jadwalIdsPiketSementara })
            })
            .then(res => res.json())
            .then(data => {
                document.getElementById('modal-piket').classList.add('hidden');
                btnLanjut.innerHTML = 'Ya, Saya Inval';
                btnLanjut.disabled = false;

                hentikanKamera();
                if (navigator.vibrate) navigator.vibrate(200);
                tampilSukses('Hadir Tercatat', data.pesan || 'Kehadiran Anda telah tercatat.');
            });
        }

        // ===== KAMERA =====
        function initKamera() {
            if (typeof Html5Qrcode === 'undefined') {
                if (typeof tampilNotif === 'function') tampilNotif('error', 'Kamera', 'Library kamera gagal dimuat. Muat ulang halaman.');
                return;
            }
            if (html5QrCode) return;
            try {
                html5QrCode = new Html5Qrcode("reader");
                window.__html5QrEl = html5QrCode;
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
                { fps: 10, qrbox: { width: 180, height: 180 } },
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

        // ===== TAB =====
        function pindahTab(nama) {
            if (nama === 'scan') {
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

        tabScan.addEventListener('click', function() { pindahTab('scan'); });
        tabQr.addEventListener('click', function() { pindahTab('qr'); });
        document.getElementById('btn-scan-lagi').addEventListener('click', function() {
            sembunyiSukses();
            mulaiKamera();
        });

        // ===== START AMAN (hanya saat halaman terlihat + retry) =====
        function cobaMulaiKamera() {
            if (document.visibilityState === 'visible') {
                // Mulai hanya jika tab scan aktif
                if (!panelScan.classList.contains('hidden')) {
                    mulaiKamera();
                }
            }
        }

        window.addEventListener('load', function() { setTimeout(cobaMulaiKamera, 300); });
        document.addEventListener('DOMContentLoaded', function() { setTimeout(cobaMulaiKamera, 300); });
        document.addEventListener('turbo:load', function() { setTimeout(cobaMulaiKamera, 300); });
        document.addEventListener('visibilitychange', function() {
            if (document.visibilityState === 'visible') cobaMulaiKamera();
        });
        window.addEventListener('pageshow', function() { setTimeout(cobaMulaiKamera, 300); });

        // Bersihkan saat meninggalkan halaman
        document.addEventListener('turbo:before-visit', hentikanKamera);
        window.addEventListener('pagehide', hentikanKamera);
    })();
</script>
@endsection
