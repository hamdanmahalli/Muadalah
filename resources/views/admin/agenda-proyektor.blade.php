<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Absensi - {{ $agenda->nama_kegiatan }}</title>
    <!-- Kita menggunakan CDN Tailwind khusus halaman ini agar layout utamanya tidak bocor -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #0f172a; overflow: hidden; } /* Dark Mode untuk proyektor agar tidak silau */
    </style>
</head>
<body class="flex flex-col items-center justify-center min-h-screen text-slate-100 relative">
    
    <!-- Efek Cahaya Latar -->
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[800px] h-[800px] bg-indigo-500/20 rounded-full blur-[120px] pointer-events-none z-0"></div>

    <div class="relative z-10 w-full max-w-4xl px-8 flex flex-col items-center text-center">
        <!-- Header Info -->
        <div class="mb-10">
            <span class="inline-block px-4 py-1.5 rounded-full bg-emerald-500/20 text-emerald-400 font-black tracking-widest uppercase text-sm border border-emerald-500/30 mb-4 shadow-[0_0_20px_rgba(16,185,129,0.2)]">
                <i class="fas fa-satellite-dish animate-pulse mr-2"></i> Scan Untuk Kehadiran
            </span>
            <h1 class="text-5xl md:text-6xl font-black text-white tracking-tight drop-shadow-lg mb-4">{{ $agenda->nama_kegiatan }}</h1>
            <p class="text-xl md:text-2xl font-bold text-slate-300 flex items-center justify-center gap-6">
                <span><i class="fas fa-calendar-alt text-indigo-400 mr-2"></i> {{ \Carbon\Carbon::parse($agenda->tanggal)->translatedFormat('l, d F Y') }}</span>
                <span><i class="fas fa-clock text-indigo-400 mr-2"></i> {{ \Carbon\Carbon::parse($agenda->jam_mulai)->format('H:i') }} WIB</span>
            </p>
        </div>

        <!-- QR Code Area -->
        <div class="bg-white p-8 rounded-[3rem] shadow-[0_20px_50px_rgba(0,0,0,0.5)] flex flex-col items-center relative transform transition-transform hover:scale-105">
            <!-- Pinggiran Scanner -->
            <div class="absolute top-4 left-4 w-12 h-12 border-t-4 border-l-4 border-indigo-600 rounded-tl-2xl"></div>
            <div class="absolute top-4 right-4 w-12 h-12 border-t-4 border-r-4 border-indigo-600 rounded-tr-2xl"></div>
            <div class="absolute bottom-4 left-4 w-12 h-12 border-b-4 border-l-4 border-indigo-600 rounded-bl-2xl"></div>
            <div class="absolute bottom-4 right-4 w-12 h-12 border-b-4 border-r-4 border-indigo-600 rounded-br-2xl"></div>

            <!-- Menggunakan API pihak ketiga yang cepat & gratis untuk merender QR tanpa perlu install library backend -->
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=400x400&data={{ $agenda->qr_token }}&margin=10" 
                 alt="QR Code" 
                 class="w-72 h-72 md:w-96 md:h-96 object-contain">
            
            <p class="text-slate-400 font-bold text-sm mt-6 uppercase tracking-widest">Gunakan Aplikasi Guru Untuk Memindai</p>
        </div>
    </div>

    <!-- Footer Instruksi -->
    <div class="absolute bottom-8 left-0 right-0 text-center z-10">
        <p class="text-slate-500 font-medium">Tekan <kbd class="px-2 py-1 bg-slate-800 rounded-md border border-slate-700 text-slate-300 mx-1 font-mono">F11</kbd> untuk layar penuh (Full Screen)</p>
    </div>
</body>
</html>