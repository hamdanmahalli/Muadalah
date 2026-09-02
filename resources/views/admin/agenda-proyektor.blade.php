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
        /* Animasi pop untuk kartu guru terbaru */
        @keyframes popIn {
            0%   { transform: scale(0.85); opacity: 0; }
            100% { transform: scale(1); opacity: 1; }
        }
        .pop-in { animation: popIn .45s cubic-bezier(.2,.8,.3,1.2) both; }
        /* Animasi pulse kecil pada badge live */
        @keyframes softPulse {
            0%, 100% { box-shadow: 0 0 0 0 rgba(16,185,129,.45); }
            50%      { box-shadow: 0 0 0 14px rgba(16,185,129,0); }
        }
        .live-dot { animation: softPulse 1.8s ease-out infinite; }
    </style>
</head>
<body class="flex flex-col items-center justify-center min-h-screen text-slate-100 relative">

    <!-- Efek Cahaya Latar -->
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[900px] h-[700px] bg-indigo-500/20 rounded-full blur-[120px] pointer-events-none z-0"></div>

    <div class="relative z-10 w-full max-w-7xl px-8 py-6 flex flex-col items-center">
        <!-- Header Info -->
        <div class="mb-8 text-center">
            <span class="inline-block px-4 py-1.5 rounded-full bg-emerald-500/20 text-emerald-400 font-black tracking-widest uppercase text-sm border border-emerald-500/30 mb-4 shadow-[0_0_20px_rgba(16,185,129,0.2)]">
                <i class="fas fa-satellite-dish animate-pulse mr-2"></i> Scan Untuk Kehadiran
            </span>
            <h1 class="text-4xl md:text-5xl font-black text-white tracking-tight drop-shadow-lg mb-3">{{ $agenda->nama_kegiatan }}</h1>
            <p class="text-lg md:text-xl font-bold text-slate-300 flex items-center justify-center gap-6">
                <span><i class="fas fa-calendar-alt text-indigo-400 mr-2"></i> {{ \Carbon\Carbon::parse($agenda->tanggal)->translatedFormat('l, d F Y') }}</span>
                <span><i class="fas fa-clock text-indigo-400 mr-2"></i> {{ \Carbon\Carbon::parse($agenda->jam_mulai)->format('H:i') }} WIB</span>
            </p>
        </div>

        <!-- Layout Utama: Kiri = QR Code, Kanan = Data Guru Terbaru Masuk -->
        <div class="grid w-full grid-cols-1 lg:grid-cols-5 gap-8 items-stretch">

            <!-- ============ KIRI: QR CODE ============ -->
            <div class="lg:col-span-2 flex flex-col items-center justify-center">
                <div class="bg-white p-6 rounded-[3rem] shadow-[0_20px_50px_rgba(0,0,0,0.5)] flex flex-col items-center relative transform transition-transform hover:scale-105">
                    <!-- Pinggiran Scanner -->
                    <div class="absolute top-4 left-4 w-12 h-12 border-t-4 border-l-4 border-indigo-600 rounded-tl-2xl"></div>
                    <div class="absolute top-4 right-4 w-12 h-12 border-t-4 border-r-4 border-indigo-600 rounded-tr-2xl"></div>
                    <div class="absolute bottom-4 left-4 w-12 h-12 border-b-4 border-l-4 border-indigo-600 rounded-bl-2xl"></div>
                    <div class="absolute bottom-4 right-4 w-12 h-12 border-b-4 border-r-4 border-indigo-600 rounded-br-2xl"></div>

                    <!-- Menggunakan API pihak ketiga yang cepat & gratis untuk merender QR tanpa perlu install library backend -->
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=400x400&data={{ $agenda->qr_token }}&margin=10"
                         alt="QR Code"
                         class="w-64 h-64 md:w-80 md:h-80 object-contain">

                    <p class="text-slate-400 font-bold text-sm mt-5 uppercase tracking-widest">Gunakan Aplikasi Guru Untuk Memindai</p>
                </div>
            </div>

            <!-- ============ KANAN: DATA GURU BARU MASUK ============ -->
            <div class="lg:col-span-3 flex flex-col">
                <div class="flex-1 bg-slate-900/60 backdrop-blur rounded-[2.5rem] border border-white/10 p-6 flex flex-col shadow-[0_20px_50px_rgba(0,0,0,0.45)] overflow-hidden">
                    <!-- Header Panel -->
                    <div class="flex items-center justify-between mb-5 shrink-0">
                        <h2 class="text-xl md:text-2xl font-black text-white tracking-tight flex items-center gap-3">
                            <span class="live-dot w-3 h-3 rounded-full bg-emerald-400 inline-block"></span>
                            <i class="fas fa-user-check text-emerald-400"></i> Guru Baru Masuk
                        </h2>
                        <span id="badge-total-hadir" class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 font-black text-sm">
                            <i class="fas fa-users"></i> <span id="total-hadir">0</span> Tercatat
                        </span>
                    </div>

                    <div class="flex-1 flex flex-col min-h-0">
                        <!-- KARTU GURU PALING BARU -->
                        <div id="card-terbaru" class="pop-in shrink-0 mb-5 bg-gradient-to-br from-emerald-500/15 to-indigo-500/15 border border-emerald-400/25 rounded-3xl p-6 flex items-center gap-5 shadow-[0_10px_30px_-10px_rgba(16,185,129,0.35)]">
                            <!-- Foto / Avatar placeholder (ganti <div> ini dengan <img> bila foto guru tersedia) -->
                            <div id="avatar-terbaru" class="w-28 h-28 md:w-32 md:h-32 rounded-full bg-gradient-to-br from-indigo-500 to-emerald-500 flex items-center justify-center text-white text-5xl shadow-lg shrink-0 ring-4 ring-indigo-900/50">
                                <i class="fas fa-user"></i>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p id="nama-terbaru" class="text-2xl md:text-3xl font-black text-white tracking-tight truncate">Belum ada yang hadir</p>
                                <div class="flex flex-wrap items-center gap-3 mt-2">
                                    <span id="wrapper-status-terbaru"><span id="status-terbaru" class="px-3 py-1 rounded-lg border text-xs font-black uppercase tracking-wider hidden"></span></span>
                                    <span id="waktu-terbaru" class="text-lg font-bold text-slate-300 flex items-center gap-2">
                                        <i class="fas fa-clock text-indigo-400"></i> --
                                    </span>
                                </div>
                                <p id="metode-terbaru" class="text-xs font-bold text-slate-500 uppercase tracking-widest mt-2"></p>
                            </div>
                        </div>

                        <!-- DAFTAR BEBERAPA GURU TERAKHIR -->
                        <div class="flex-1 min-h-[200px] flex flex-col">
                            <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-3 shrink-0">
                                <i class="fas fa-history mr-2"></i>Beberapa Terakhir Masuk
                            </h3>
                            <div id="list-terakhir" class="flex-1 overflow-y-auto scrollbar-none space-y-2 pr-1">
                                <!-- Rows diisi oleh JavaScript -->
                            </div>
                        </div>
                    </div>

                    <!-- Pesan menunggu -->
                    <div id="empty-state" class="hidden flex-1 flex-col items-center justify-center text-center py-8">
                        <div class="w-16 h-16 rounded-full bg-slate-800 text-slate-500 flex items-center justify-center mb-4 text-3xl animate-pulse">
                            <i class="fas fa-qrcode"></i>
                        </div>
                        <p class="text-lg font-black text-slate-300">Menunggu Scan Pertama...</p>
                        <p class="text-sm font-medium text-slate-500 mt-1">Guru yang baru memindai QR akan tampil di sini.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer Instruksi -->
    <div class="absolute bottom-6 left-0 right-0 text-center z-10">
        <p class="text-slate-500 font-medium">Tekan <kbd class="px-2 py-1 bg-slate-800 rounded-md border border-slate-700 text-slate-300 mx-1 font-mono">F11</kbd> untuk layar penuh (Full Screen)</p>
    </div>

    <!-- SCRIPT POLING REAL-TIME: tampilkan guru terbaru + beberapa terakhir -->
    <script>
        const AGENDA_ID = {{ $agenda->id }};
        let guruTerakhirId = null;

        // Helper pembuatan avatar. Saat ini memakai ikon placeholder (belum ada kolom foto).
        // Nanti tinggal ganti kembaliannya menjadi: `<img src="/uploads/guru/${guru_id}.jpg" ...>`
        function avatarHtml(guruId, sizeClass) {
            return `<div class="${sizeClass} rounded-full bg-gradient-to-br from-indigo-500 to-emerald-500 flex items-center justify-center text-slate-100 shrink-0">
                        <i class="fas fa-user"></i>
                    </div>`;
        }

        function statusChip(status) {
            if (status === 'Hadir') return '<span class="px-3 py-1 rounded-lg bg-emerald-500/20 text-emerald-300 border border-emerald-500/40 text-xs font-black uppercase tracking-wider">Hadir</span>';
            if (status === 'Izin') return '<span class="px-3 py-1 rounded-lg bg-amber-500/20 text-amber-300 border border-amber-500/40 text-xs font-black uppercase tracking-wider">Izin</span>';
            if (status === 'Sakit') return '<span class="px-3 py-1 rounded-lg bg-rose-500/20 text-rose-300 border border-rose-500/40 text-xs font-black uppercase tracking-wider">Sakit</span>';
            return '';
        }

        function render(res) {
            const list = res.data_hadir || [];
            const total = res.total_hadir || 0;

            document.getElementById('total-hadir').innerText = total;

            if (list.length === 0) {
                document.getElementById('empty-state').classList.remove('hidden');
                document.getElementById('empty-state').classList.add('flex');
                document.getElementById('card-terbaru').classList.add('hidden');
                document.getElementById('list-terakhir').innerHTML = '';
                guruTerakhirId = null;
                return;
            }

            document.getElementById('empty-state').classList.add('hidden');
            document.getElementById('empty-state').classList.remove('flex');
            document.getElementById('card-terbaru').classList.remove('hidden');

            // Data terakhir (paling baru) -> kartu besar
            const terbaru = list[list.length - 1];

            document.getElementById('nama-terbaru').innerText = terbaru.nama_guru;
            document.getElementById('waktu-terbaru').innerHTML = `<i class="fas fa-clock text-indigo-400"></i> ${terbaru.waktu} WIB`;
            document.getElementById('metode-terbaru').innerText = `via ${terbaru.metode}`;
            document.getElementById('wrapper-status-terbaru').innerHTML = statusChip(terbaru.status);

            if (guruTerakhirId !== terbaru.guru_id) {
                const card = document.getElementById('card-terbaru');
                card.classList.remove('pop-in');
                void card.offsetWidth; // restart animasi
                card.classList.add('pop-in');
                guruTerakhirId = terbaru.guru_id;
            }

            // Beberapa guru terakhir (maksimal 5, urut paling baru di atas)
            const terakhir = list.slice(-5).reverse();
            let html = '';
            terakhir.forEach(item => {
                html += `
                    <div class="flex items-center gap-3 bg-white/5 border border-white/10 rounded-2xl px-4 py-3 transition-colors">
                        ${avatarHtml(item.guru_id, 'w-11 h-11 text-xl')}
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-black text-slate-100 truncate">${item.nama_guru}</p>
                            <p class="text-[11px] font-bold text-slate-400"><i class="fas fa-clock mr-1 text-indigo-400"></i>${item.waktu} WIB</p>
                        </div>
                        ${statusChip(item.status)}
                    </div>`;
            });
            document.getElementById('list-terakhir').innerHTML = html;
        }

        function muatData() {
            fetch('/api/agenda-kegiatan/' + AGENDA_ID + '/realtime')
                .then(response => response.json())
                .then(render)
                .catch(err => console.error('Gagal menyinkronkan data real-time:', err));
        }

        // Muat pertama kali + polling rutin
        muatData();
        setInterval(muatData, 4000);
    </script>
</body>
</html>