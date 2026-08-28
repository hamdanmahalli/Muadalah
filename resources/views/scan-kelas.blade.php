@extends('layouts.app')
@section('title', 'Scan Kehadiran Kelas - SmartPesantren')

@section('content')
@push('styles')
    {{-- Turbo: jangan simpan snapshot halaman kamera (hindari scanner macet saat Back/Forward) --}}
    <meta name="turbo-cache-control" content="no-cache">
@endpush
<style>
    header, aside { display: none !important; }
    main { padding: 0 !important; background-color: #f8fafc !important; overflow: hidden !important; }
    body { overflow: hidden !important; background-color: #f8fafc !important; }
    .scrollbar-none::-webkit-scrollbar { display: none; }
    .scrollbar-none { -ms-overflow-style: none; scrollbar-width: none; }
    @supports (padding-bottom: env(safe-area-inset-bottom)) { .pb-safe { padding-bottom: env(safe-area-inset-bottom); } }
    
    /* Animasi Garis Scanner Elegan */
    @keyframes scanLine {
        0% { top: 0%; opacity: 0.8; }
        50% { opacity: 1; }
        100% { top: 95%; opacity: 0.8; }
    }
    .scanner-line {
        position: absolute;
        left: 10%;
        right: 10%;
        height: 2px;
        background: #10b981;
        box-shadow: 0 0 10px #10b981, 0 0 20px #10b981;
        animation: scanLine 2s infinite ease-in-out alternate;
    }
    
    /* Memastikan elemen video di dalam reader memenuhi area secara presisi */
    #reader video {
        object-fit: cover !important;
        width: 100% !important;
        height: 100% !important;
        border-radius: 1rem;
    }
</style>

<div data-turbo="true" class="max-w-md mx-auto h-[100dvh] bg-slate-50 flex flex-col relative font-sans overflow-hidden">
    
    <!-- HEADER STICKY (Kembali ke Halaman Sebelumnya Secara Dinamis) -->
    <div class="shrink-0 bg-white border-b border-slate-100 px-4 py-4 flex items-center shadow-[0_2px_10px_rgba(0,0,0,0.02)] relative z-20">
        <a href="javascript:history.back()" class="w-10 h-10 rounded-2xl bg-slate-50 text-slate-500 flex items-center justify-center hover:bg-slate-100 hover:text-emerald-600 active:scale-95 transition-all">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div class="flex-1 px-4">
            <h2 class="text-base font-black text-slate-800 tracking-tight">Scan Barcode Kelas</h2>
            <p class="text-[9px] font-bold text-emerald-600 uppercase tracking-widest mt-0.5">Arahkan kamera ke QR Code</p>
        </div>
    </div>

    <!-- AREA KONTEN UTAMA -->
    <div class="flex-1 overflow-y-auto bg-slate-50 relative z-10 p-5 scrollbar-none flex flex-col justify-center items-center">
        
        <!-- KOTAK NOTIFIKASI -->
        <div id="pesan-area" class="hidden w-full mb-4 p-4 rounded-2xl text-center font-bold text-sm shadow-sm transition-all animate-[sweep_0.3s_ease-in-out]">
            <span id="pesan-teks"></span>
        </div>

        <!-- FRAME KAMERA ELEGAN (Sancod Builder Premium UI) -->
        <div class="w-full bg-white p-5 rounded-3xl border border-slate-200 shadow-sm relative flex flex-col items-center overflow-hidden transition-all duration-300" id="kamera-card">
            
            <div class="w-full text-center mb-3">
                <span id="badge-status" class="inline-block px-3 py-1 bg-emerald-50 text-emerald-600 text-[10px] font-black uppercase tracking-widest rounded-full border border-emerald-100 transition-colors duration-300">
                    <i class="fas fa-circle text-[6px] mr-1.5 animate-pulse"></i> Kamera Aktif
                </span>
            </div>

            <!-- KOTAK KAMERA -->
            <div class="w-full bg-slate-900 rounded-2xl overflow-hidden relative h-[250px] shadow-inner flex items-center justify-center">
                
                <!-- Layer Kamera Asli -->
                <div id="reader" class="w-full h-full absolute inset-0"></div>
                
                <!-- LAYER OVERLAY KACA BURAM (Ukuran Font Ramah Lansia & UI Premium) -->
                <div id="kamera-overlay" class="absolute inset-0 z-20 flex flex-col items-center justify-center bg-slate-900/85 backdrop-blur-lg opacity-0 pointer-events-none transition-all duration-500 transform scale-105 p-4 text-center">
                    
                    <!-- Ikon diperbesar dari w-16/text-3xl menjadi w-20/text-4xl -->
                    <div id="overlay-icon" class="w-20 h-20 rounded-full bg-indigo-500/20 text-indigo-400 flex items-center justify-center text-4xl mb-4 shadow-[0_0_40px_rgba(99,102,241,0.6)] transition-all duration-300">
                        <i class="fas fa-circle-notch fa-spin"></i>
                    </div>
                    
                    <!-- Teks Utama: Diperbesar ke text-lg/xl dengan efek bayangan menyala -->
                    <p id="overlay-text" class="text-white font-black tracking-widest text-lg md:text-xl uppercase drop-shadow-md">MEMPROSES</p>
                    
                    <!-- Subteks: Dari 10px menjadi 14px (text-sm), dibuat lebih tebal (font-bold) -->
                    <p id="overlay-subtext" class="text-slate-200 text-sm mt-2 font-bold leading-snug drop-shadow-sm px-2">Sinkronisasi ke server...</p>
                </div>

                <!-- GARIS LASER SCANNER PEMANDU -->
                <div id="laser-line" class="scanner-line pointer-events-none z-10 transition-opacity duration-300"></div>
                
                <!-- KOTAK PANDUAN FOCUS -->
                <div id="focus-box" class="absolute w-[180px] h-[180px] border-2 border-emerald-400/60 rounded-xl pointer-events-none z-10 shadow-[0_0_15px_rgba(16,185,129,0.2)] transition-opacity duration-300"></div>
            </div>

            <p id="teks-bantuan" class="text-[11px] font-medium text-slate-400 text-center mt-3 transition-opacity duration-300">
                Pastikan posisi QR Code berada di dalam kotak bingkai.
            </p>
        </div>

    </div>

    <!-- MODAL KONFIRMASI PIKET (Tersembunyi secara default) -->
    <div id="modal-piket" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm px-4">
        <div class="bg-white rounded-3xl w-full max-w-sm overflow-hidden shadow-2xl transform scale-100 transition-transform">
            <div class="p-6 text-center">
                <div class="w-16 h-16 bg-indigo-50 text-indigo-600 rounded-full flex items-center justify-center text-3xl mx-auto mb-4 shadow-inner">
                    <i class="fas fa-exchange-alt"></i>
                </div>
                <h3 class="text-xl font-black text-slate-800 tracking-tight mb-2">Deteksi Jadwal Berbeda</h3>
                <p id="teks-konfirmasi-piket" class="text-sm text-slate-600 leading-relaxed font-medium mb-6">
                    <!-- Teks dari server akan masuk ke sini -->
                </p>
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
        const pesanArea = document.getElementById('pesan-area');
        const pesanTeks = document.getElementById('pesan-teks');
        let isProcessing = false;
        let html5QrCode;

        function tampilkanPesan(teks, bgClass, textClass) {
            pesanArea.className = `w-full mb-4 p-4 rounded-2xl text-center font-bold text-sm shadow-sm ${bgClass} ${textClass}`;
            pesanTeks.innerHTML = teks;
            pesanArea.classList.remove('hidden');
        }

        let jadwalIdsPiketSementara = []; // Memori sementara untuk menyimpan ID

        // FUNGSI PENGENDALI ANTARMUKA (Sancod Builder UI Engine - Aksesibilitas Lansia)
        function setKameraUI(state, pesanUtama = '', pesanSub = '') {
            const overlay = document.getElementById('kamera-overlay');
            const icon = document.getElementById('overlay-icon');
            const text = document.getElementById('overlay-text');
            const subtext = document.getElementById('overlay-subtext');
            const laser = document.getElementById('laser-line');
            const focusBox = document.getElementById('focus-box');
            const badge = document.getElementById('badge-status');
            const card = document.getElementById('kamera-card');

            if (state !== 'reset') {
                overlay.classList.remove('opacity-0', 'pointer-events-none', 'scale-105');
                overlay.classList.add('opacity-100', 'scale-100');
                laser.style.opacity = '0';
                focusBox.style.opacity = '0';
            }

            if (state === 'processing') {
                card.className = "w-full bg-white p-5 rounded-3xl border border-indigo-200 shadow-[0_0_20px_rgba(99,102,241,0.1)] relative flex flex-col items-center overflow-hidden transition-all duration-300";
                badge.className = "inline-block px-3 py-1 bg-indigo-50 text-indigo-600 text-[10px] font-black uppercase tracking-widest rounded-full border border-indigo-100 transition-colors duration-300";
                badge.innerHTML = '<i class="fas fa-spinner fa-spin text-[10px] mr-1.5"></i> Memproses';
                
                // Ikon dan shadow diperbesar
                icon.className = "w-20 h-20 rounded-full bg-indigo-500/20 text-indigo-400 flex items-center justify-center text-4xl mb-4 shadow-[0_0_40px_rgba(99,102,241,0.6)]";
                icon.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i>';
                text.innerText = 'MEMPROSES';
                subtext.innerText = 'Menyandikan data kehadiran...';
            } 
            else if (state === 'success') {
                card.className = "w-full bg-white p-5 rounded-3xl border border-emerald-200 shadow-[0_0_20px_rgba(16,185,129,0.1)] relative flex flex-col items-center overflow-hidden transition-all duration-300";
                badge.className = "inline-block px-3 py-1 bg-emerald-50 text-emerald-600 text-[10px] font-black uppercase tracking-widest rounded-full border border-emerald-100 transition-colors duration-300";
                badge.innerHTML = '<i class="fas fa-check text-[10px] mr-1.5"></i> Selesai';

                icon.className = "w-20 h-20 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center text-4xl mb-4 shadow-[0_0_40px_rgba(16,185,129,0.6)]";
                icon.innerHTML = '<i class="fas fa-check"></i>';
                text.innerText = 'BERHASIL';
                text.classList.replace('text-white', 'text-emerald-400');
                subtext.innerHTML = pesanUtama; 
            }
            else if (state === 'error') {
                card.className = "w-full bg-white p-5 rounded-3xl border border-rose-200 shadow-[0_0_20px_rgba(225,29,72,0.1)] relative flex flex-col items-center overflow-hidden transition-all duration-300";
                badge.className = "inline-block px-3 py-1 bg-rose-50 text-rose-600 text-[10px] font-black uppercase tracking-widest rounded-full border border-rose-100 transition-colors duration-300";
                badge.innerHTML = '<i class="fas fa-times text-[10px] mr-1.5"></i> Error';

                icon.className = "w-20 h-20 rounded-full bg-rose-500/20 text-rose-400 flex items-center justify-center text-4xl mb-4 shadow-[0_0_40px_rgba(225,29,72,0.6)]";
                icon.innerHTML = '<i class="fas fa-exclamation-triangle"></i>';
                text.innerText = 'DITOLAK';
                text.classList.replace('text-white', 'text-rose-400');
                subtext.innerHTML = pesanUtama;
            }
            else if (state === 'piket') {
                card.className = "w-full bg-white p-5 rounded-3xl border border-blue-200 shadow-[0_0_20px_rgba(59,130,246,0.1)] relative flex flex-col items-center overflow-hidden transition-all duration-300";
                badge.className = "inline-block px-3 py-1 bg-blue-50 text-blue-600 text-[10px] font-black uppercase tracking-widest rounded-full border border-blue-100 transition-colors duration-300 animate-pulse";
                badge.innerHTML = '<i class="fas fa-user-shield text-[10px] mr-1.5"></i> Mode Inval';

                icon.className = "w-20 h-20 rounded-full bg-blue-500/20 text-blue-400 flex items-center justify-center text-4xl mb-4 shadow-[0_0_40px_rgba(59,130,246,0.6)]";
                icon.innerHTML = '<i class="fas fa-exchange-alt"></i>';
                text.innerText = 'BEDA JADWAL';
                text.classList.replace('text-white', 'text-blue-400');
                subtext.innerHTML = 'Menunggu konfirmasi Anda...';
            }
            else if (state === 'reset') {
                card.className = "w-full bg-white p-5 rounded-3xl border border-slate-200 shadow-sm relative flex flex-col items-center overflow-hidden transition-all duration-300";
                badge.className = "inline-block px-3 py-1 bg-emerald-50 text-emerald-600 text-[10px] font-black uppercase tracking-widest rounded-full border border-emerald-100 transition-colors duration-300";
                badge.innerHTML = '<i class="fas fa-circle text-[6px] mr-1.5 animate-pulse"></i> Kamera Aktif';
                
                // Kembalikan ukuran dan warna teks utama saat reset
                text.className = 'text-white font-black tracking-widest text-lg md:text-xl uppercase drop-shadow-md';

                overlay.classList.remove('opacity-100', 'scale-100');
                overlay.classList.add('opacity-0', 'pointer-events-none', 'scale-105');
                laser.style.opacity = '1';
                focusBox.style.opacity = '1';
            }
        }

        function onScanSuccess(decodedText, decodedResult) {
            if (isProcessing) return; 
            isProcessing = true;
            
            // 0. Saat offline tidak bisa mengirimkan kehadiran
            if (typeof wajibOnline === 'function' && !wajibOnline(null, 'Scan Hadir memerlukan koneksi internet')) {
                setKameraUI('error', 'Anda sedang offline. Sambungkan internet untuk scan kehadiran.');
                setTimeout(() => {
                    setKameraUI('reset');
                    isProcessing = false;
                }, 3000);
                return;
            }
            
            // 1. Matikan kamera secara visual, munculkan animasi memproses
            setKameraUI('processing');
            
            // Hapus pesanArea karena fungsinya sudah digantikan oleh Overlay Kamera
            if(pesanArea) pesanArea.classList.add('hidden');

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
                    // Skenario Normal (Jadwal Sendiri)
                    setKameraUI('success', data.pesan);
                    if (navigator.vibrate) navigator.vibrate(200);
                    
                    setTimeout(() => {
                        setKameraUI('reset');
                        isProcessing = false;
                    }, 3500);
                } 
                else if (data.status === 'confirm_piket') {
                    // Skenario Inval (Jadwal Orang Lain)
                    setKameraUI('piket');
                    if (navigator.vibrate) navigator.vibrate([100, 50, 100]);
                    
                    document.getElementById('teks-konfirmasi-piket').innerHTML = data.pesan;
                    jadwalIdsPiketSementara = data.data.jadwal_ids;
                    document.getElementById('modal-piket').classList.remove('hidden');
                }
                else {
                    // Skenario Error
                    setKameraUI('error', data.pesan);
                    if (navigator.vibrate) navigator.vibrate([300]);
                    
                    setTimeout(() => { 
                        setKameraUI('reset');
                        isProcessing = false; 
                    }, 4000);
                }
            })
            .catch(error => {
                setKameraUI('error', 'Gagal terhubung ke server.');
                setTimeout(() => { 
                    setKameraUI('reset');
                    isProcessing = false; 
                }, 3000);
            });
        }

        // Fungsi Jika Guru Menekan Batal di Modal Piket
        window.batalPiket = function() {
            document.getElementById('modal-piket').classList.add('hidden');
            setKameraUI('reset'); // Kembalikan kamera
            isProcessing = false; // Buka kunci scanner
        }

        // Fungsi Jika Guru Menekan "Ya, Saya Inval"
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
                
                // Ubah overlay jadi sukses warna hijau
                setKameraUI('success', data.pesan);
                if (navigator.vibrate) navigator.vibrate(200);

                setTimeout(() => {
                    setKameraUI('reset');
                    isProcessing = false;
                }, 4000);
            });
        }

        html5QrCode = new Html5Qrcode("reader");
        
        const config = { 
            fps: 10, 
            qrbox: { width: 180, height: 180 } 
        };

        html5QrCode.start(
            { facingMode: "environment" }, 
            config, 
            onScanSuccess
        ).catch(err => {
            console.error("Gagal mengakses kamera:", err);
            tampilkanPesan('Gagal mengakses kamera. Pastikan izin kamera diaktifkan di browser Anda.', 'bg-rose-50 text-rose-700 border border-rose-200');
        });

        // Ekspos instance untuk dibersihkan saat meninggalkan halaman (navigasi Turbo / SPA)
        window.__html5QrEl = html5QrCode;
        function hentikanKamera() {
            try {
                if (window.__html5QrEl && typeof window.__html5QrEl.stop === 'function') {
                    window.__html5QrEl.stop().catch(function() {});
                }
            } catch (err) {}
            window.__html5QrEl = null;
        }
        document.addEventListener('turbo:before-visit', hentikanKamera);
        window.addEventListener('pagehide', hentikanKamera);
    })();
</script>
@endsection