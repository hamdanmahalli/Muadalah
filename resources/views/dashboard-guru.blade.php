@extends('layouts.app')

@section('title', 'Beranda Guru - Muadalah Wustha')

@section('content')
<style>
    header, aside { display: none !important; }
    #btn-buka-sidebar { display: none !important; }
    main { padding: 0 !important; background-color: #f8fafc !important; overflow: hidden !important; }
    body { overflow: hidden !important; background-color: #f8fafc !important; }
    .scrollbar-none::-webkit-scrollbar { display: none; }
    .scrollbar-none { -ms-overflow-style: none; scrollbar-width: none; }
    @supports (padding-bottom: env(safe-area-inset-bottom)) {
        .pb-safe { padding-bottom: env(safe-area-inset-bottom); }
    }
</style>

<div data-turbo="true" class="max-w-md mx-auto h-[100dvh] bg-slate-100 flex flex-col relative font-sans overflow-hidden">

    <!-- ===== HEADER BANK: GRADIENT + AVATAR + BELL ===== -->
    <div class="shrink-0 bg-gradient-to-br from-emerald-600 via-emerald-600 to-teal-700 px-5 pt-6 pb-14 relative z-10 overflow-hidden">
        <div class="absolute -right-16 -top-16 w-56 h-56 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
        <div class="absolute -left-12 -bottom-20 w-48 h-48 bg-teal-400/20 rounded-full blur-2xl pointer-events-none"></div>

<div class="relative z-10 flex items-center justify-between gap-3">
            <div class="flex items-center gap-3 min-w-0">
                <div class="min-w-0">
                    <p class="text-[10px] font-black text-emerald-100 uppercase tracking-widest">Assalamu'alaikum,</p>
                    <h2 class="text-lg font-black text-white tracking-tight truncate" title="{{ auth()->user()->name }}">
                        {{ auth()->user()->name ?? 'Ahmad' }}
                    </h2>
                </div>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <div class="px-3 py-1.5 rounded-full bg-white/15 backdrop-blur border border-white/20">
                    <span class="text-[10px] font-black text-white">TA. {{ $periodeAktif->tahun_ajaran ?? '2026/2027' }}</span>
                </div>
                <a href="/notifikasi/pengaturan" class="relative w-10 h-10 rounded-full bg-white/15 backdrop-blur border border-white/20 flex items-center justify-center text-white active:scale-95 transition">
                    <i class="fas fa-bell text-sm"></i>
                    <span class="absolute top-2 right-2.5 w-2 h-2 rounded-full bg-rose-400 ring-2 ring-emerald-700"></span>
                </a>
            </div>
        </div>

        <p class="relative z-10 text-xs font-semibold text-emerald-100 mt-3 flex items-center gap-1.5">
            <i class="far fa-calendar-alt text-[10px]"></i>
            {{ \Carbon\Carbon::now()->locale('id')->translatedFormat('l, d F Y') }} · {{ $periodeAktif->semester ?? 'Semester' }}
        </p>
    </div>

    <!-- ===== AREA KONTEN (scroll) ===== -->
    <div class="flex-1 overflow-y-auto scrollbar-none px-5 pt-0 pb-32 relative z-20 -mt-8">

        @if(session('pesan'))
        <div class="mb-4 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3.5 flex items-start gap-3 text-xs font-semibold text-amber-800 shadow-sm">
            <div class="w-8 h-8 rounded-xl bg-amber-100 flex items-center justify-center shrink-0 text-amber-600">
                <i class="fas fa-triangle-exclamation text-sm"></i>
            </div>
            <span class="leading-relaxed pt-1">{{ session('pesan') }}</span>
        </div>
        @endif

        @php
            $hariIniLokalStats = map_hari(\Carbon\Carbon::now()->format('l'));
            $jadwalHariIni = $jadwals[$hariIniLokalStats] ?? [];
            $jmlKelasHariIni = count($jadwalHariIni);
            $jmlJamHariIni = 0;
            foreach ($jadwalHariIni as $blok) {
                $mulai = $blok['jam_mulai'] ?? 1;
                $selesai = $blok['jam_selesai'] ?? $mulai;
                $jmlJamHariIni += ($selesai - $mulai) + 1;
            }
            $jmlHariMengajar = is_array($jadwals) ? count($jadwals) : 0;
        @endphp


        <!-- ===== PAPAN PENGUMUMAN (carousel geser otomatis) ===== -->
        @php
            $warnaPengumuman = [
                'emerald' => 'from-emerald-600 to-teal-700',
                'blue' => 'from-blue-600 to-sky-700',
                'amber' => 'from-amber-500 to-orange-600',
                'rose' => 'from-rose-500 to-pink-700',
                'violet' => 'from-violet-600 to-purple-700',
                'cyan' => 'from-cyan-500 to-sky-600',
                'indigo' => 'from-indigo-600 to-blue-700',
            ];
        @endphp

        @if(count($pengumumans) > 0)
        <div id="pengumuman-carousel" class="flex overflow-x-auto snap-x snap-mandatory gap-3 scrollbar-none -mx-5 px-5 pb-1">
            @foreach($pengumumans as $peng)
            <div class="snap-center shrink-0 w-full rounded-3xl p-6 shadow-[0_22px_45px_-18px_rgba(15,23,42,0.5)] relative overflow-hidden bg-gradient-to-br {{ $warnaPengumuman[$peng->warna] ?? $warnaPengumuman['emerald'] }}">
                @if($peng->gambar)
                <div class="absolute inset-0 bg-cover bg-center pointer-events-none" style="background-image:url('{{ asset('storage/' . $peng->gambar) }}');"></div>
                @else
                <div class="absolute inset-0 opacity-20 pointer-events-none" style="background-image:url('data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 viewBox=%270 0 200 200%27%3E%3Cg fill=%27none%27 stroke=%27white%27 stroke-width=%271.5%27%3E%3Cpath d=%27M100 10 L190 190 H10 Z%27/%3E%3Cpath d=%27M100 45 L155 190 H45 Z%27/%3E%3Cpath d=%27M100 80 L120 190 H80 Z%27/%3E%3Ccircle cx=%27100%27 cy=%27100%27 r=%2770%27/%3E%3Ccircle cx=%27100%27 cy=%27100%27 r=%2750%27/%3E%3Ccircle cx=%27100%27 cy=%27100%27 r=%2730%27/%3E%3C/g%3E%3C/svg%3E');background-size:200px 200px;background-repeat:repeat;background-position:center;"></div>
                <div class="absolute -right-10 -top-12 w-44 h-44 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
                <div class="absolute -left-8 -bottom-14 w-40 h-40 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
                @endif
                <div class="relative z-10">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="w-7 h-7 rounded-full bg-amber-400 border border-amber-300 flex items-center justify-center text-amber-900 shadow-sm"><i class="fas fa-bullhorn text-xs"></i></span>
                        <span class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Pengumuman</span>
                    </div>
                    <h3 class="text-xl font-black text-slate-800 tracking-tight leading-snug">{{ $peng->judul }}</h3>
                    @if($peng->isi)
                    <p class="text-sm font-medium text-slate-600 leading-relaxed mt-2">{{ $peng->isi }}</p>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @if(count($pengumumans) > 1)
        <div class="flex justify-center items-center space-x-2 mt-3">
            @foreach($pengumumans as $index => $p)
                <div class="dot-pengumuman rounded-full transition-all duration-300 {{ $loop->first ? 'bg-slate-700 w-6 h-2' : 'bg-slate-300 w-2 h-2' }}"></div>
            @endforeach
        </div>
        @endif
        @else
        <div class="bg-gradient-to-br from-slate-600 to-slate-800 rounded-3xl p-6 shadow-[0_22px_45px_-18px_rgba(15,23,42,0.5)] relative overflow-hidden">
            <div class="flex items-center gap-2 mb-2">
                <span class="w-8 h-8 rounded-xl bg-white/15 backdrop-blur text-white flex items-center justify-center"><i class="fas fa-bullhorn text-sm"></i></span>
                <h3 class="text-sm font-black text-white tracking-tight">Papan Pengumuman</h3>
            </div>
            <p class="text-xs font-medium text-white/80 leading-relaxed">Belum ada pengumuman untuk saat ini.</p>
        </div>
        @endif
        <div class="flex items-center justify-between mt-7 mb-4 mx-4">
            <h3 class="text-[15px] font-black text-slate-900 tracking-tight">Layanan</h3>
            <span class="text-[10px] font-bold text-slate-400">Ketuk untuk akses cepat</span>
        </div>

        
        <div class="grid grid-cols-4 gap-x-3 gap-y-3 mx-4">
            <!-- 4 menu pertama tampil -->
            <a href="/scan-kelas" class="bg-gradient-to-br from-cyan-500 to-cyan-600 rounded-xl aspect-square flex flex-col items-center justify-center gap-1.5 shadow-[0_12px_22px_-10px_rgba(6,182,212,0.7)] active:scale-90 transition-all">
                <i class="fas fa-qrcode text-base text-white"></i>
                <span class="text-[8px] font-black text-white/95 tracking-wide leading-tight text-center">Scan Hadir</span>
            </a>
            <a href="/rekap-presensi" class="bg-gradient-to-br from-violet-500 to-violet-600 rounded-xl aspect-square flex flex-col items-center justify-center gap-1.5 shadow-[0_12px_22px_-10px_rgba(139,92,246,0.7)] active:scale-90 transition-all">
                <i class="fas fa-list-check text-base text-white"></i>
                <span class="text-[8px] font-black text-white/95 tracking-wide leading-tight text-center">Rekap</span>
            </a>
            <a href="/agenda-kegiatan" class="bg-gradient-to-br from-rose-500 to-rose-600 rounded-xl aspect-square flex flex-col items-center justify-center gap-1.5 shadow-[0_12px_22px_-10px_rgba(244,63,94,0.7)] active:scale-90 transition-all">
                <i class="fas fa-calendar-day text-base text-white"></i>
                <span class="text-[8px] font-black text-white/95 tracking-wide leading-tight text-center">Agenda</span>
            </a>
            <button onclick="window.location.href='/scan-kelas'; return false;" class="bg-gradient-to-br from-sky-500 to-sky-600 rounded-xl aspect-square flex flex-col items-center justify-center gap-1.5 shadow-[0_12px_22px_-10px_rgba(14,165,233,0.7)] active:scale-90 transition-all">
                <i class="fas fa-id-badge text-base text-white"></i>
                <span class="text-[8px] font-black text-white/95 tracking-wide leading-tight text-center">QR Pribadi</span>
            </button>

            <!-- sisanya terlipat -->
            <div id="menu-tersembunyi" class="col-span-4 grid grid-cols-4 gap-x-3 gap-y-3 mt-4 hidden">
            <a href="/jadwal-saya" class="bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-xl aspect-square flex flex-col items-center justify-center gap-1.5 shadow-[0_12px_22px_-10px_rgba(99,102,241,0.7)] active:scale-90 transition-all">
                <i class="fas fa-calendar-days text-base text-white"></i>
                <span class="text-[8px] font-black text-white/95 tracking-wide leading-tight text-center">Jadwal</span>
            </a>
            <a href="/kaldik" class="bg-gradient-to-br from-amber-500 to-amber-600 rounded-xl aspect-square flex flex-col items-center justify-center gap-1.5 shadow-[0_12px_22px_-10px_rgba(245,158,11,0.7)] active:scale-90 transition-all">
                <i class="fas fa-book-open text-base text-white"></i>
                <span class="text-[8px] font-black text-white/95 tracking-wide leading-tight text-center">Kaldik</span>
            </a>
            <button onclick="tampilToast('info', 'Ganti Jam segera hadir.'); return false;" class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-xl aspect-square flex flex-col items-center justify-center gap-1.5 shadow-[0_12px_22px_-10px_rgba(249,115,22,0.7)] active:scale-90 transition-all">
                <i class="fas fa-arrows-rotate text-base text-white"></i>
                <span class="text-[8px] font-black text-white/95 tracking-wide leading-tight text-center">Ganti Jam</span>
            </button>
            <button onclick="tampilToast('info', 'Cuti / Izin segera hadir.'); return false;" class="bg-gradient-to-br from-pink-500 to-pink-600 rounded-xl aspect-square flex flex-col items-center justify-center gap-1.5 shadow-[0_12px_22px_-10px_rgba(236,72,153,0.7)] active:scale-90 transition-all">
                <i class="fas fa-paper-plane text-base text-white"></i>
                <span class="text-[8px] font-black text-white/95 tracking-wide leading-tight text-center">Cuti/Izin</span>
            </button>
            <a href="{{ route('guru.profil.lengkap') }}" class="bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-xl aspect-square flex flex-col items-center justify-center gap-1.5 shadow-[0_12px_22px_-10px_rgba(16,185,129,0.7)] active:scale-90 transition-all">
                <i class="fas fa-user text-base text-white"></i>
                <span class="text-[8px] font-black text-white/95 tracking-wide leading-tight text-center">Profil</span>
            </a>
            <button onclick="tampilToast('info', 'Nilai Ujian segera hadir.'); return false;" class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl aspect-square flex flex-col items-center justify-center gap-1.5 shadow-[0_12px_22px_-10px_rgba(59,130,246,0.7)] active:scale-90 transition-all">
                <i class="fas fa-chart-column text-base text-white"></i>
                <span class="text-[8px] font-black text-white/95 tracking-wide leading-tight text-center">Nilai Ujian</span>
            </button>
            <a href="/notifikasi/pengaturan" class="bg-gradient-to-br from-teal-500 to-teal-600 rounded-xl aspect-square flex flex-col items-center justify-center gap-1.5 shadow-[0_12px_22px_-10px_rgba(20,184,166,0.7)] active:scale-90 transition-all">
                <i class="fas fa-bell text-base text-white"></i>
                <span class="text-[8px] font-black text-white/95 tracking-wide leading-tight text-center">Notifikasi</span>
            </a>
            <button onclick="tampilToast('info', 'Wali Kelas segera hadir.'); return false;" class="bg-gradient-to-br from-slate-700 to-slate-800 rounded-xl aspect-square flex flex-col items-center justify-center gap-1.5 shadow-[0_12px_22px_-10px_rgba(51,65,85,0.7)] active:scale-90 transition-all">
                <i class="fas fa-chalkboard-user text-base text-white"></i>
                <span class="text-[8px] font-black text-white/95 tracking-wide leading-tight text-center">Wali Kelas</span>
            </button>
            </div>
        </div>

        <button onclick="toggleMenuLainnya(this)" class="mx-auto mt-4 flex items-center gap-2 text-[10px] font-black text-slate-500 hover:text-emerald-600 transition-colors">
            <span class="menu-toggle-text">Tampilkan Semua</span>
            <i class="fas fa-chevron-down menu-toggle-chev text-[8px] mt-0.5"></i>
        </button>
        <!-- ===== JADWAL HARI INI (list alá riwayat transaksi) ===== -->
        <div class="flex items-center justify-between mt-7 mb-3 mx-4">
            <h3 class="text-[15px] font-black text-slate-900 tracking-tight">Jadwal Hari Ini</h3>
            <span class="text-[11px] font-bold text-slate-400">{{ $hariIniLokalStats }}</span>
        </div>

        @forelse($jadwalHariIni as $blok)
        <div class="bg-white rounded-2xl border border-slate-100 shadow-[0_4px_16px_-10px_rgba(2,6,23,0.08)] px-4 py-3.5 flex items-center gap-4 mb-2.5 mx-4">
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-700 flex items-center justify-center font-black text-base border border-emerald-100 shrink-0">
                {{ $blok['jam_tampil'] ?? ('Jam ' . ($blok['jam_mulai'] ?? '')) }}
            </div>
            <div class="flex-1 min-w-0">
                <h4 class="text-sm font-black text-slate-800 truncate">{{ $blok['mata_pelajaran'] ?? 'Pelajaran' }}</h4>
                <p class="text-[11px] font-bold text-slate-400 mt-0.5 flex items-center"><i class="fas fa-users text-slate-300 mr-1.5 text-[10px]"></i> Kelas {{ $blok['kelas'] ?? '-' }}</p>
            </div>
            <i class="fas fa-chevron-right text-slate-300 text-xs shrink-0"></i>
        </div>
        @empty
        <div class="bg-white rounded-2xl border border-dashed border-slate-200 p-5 text-center mx-4">
            <div class="w-12 h-12 bg-slate-50 text-slate-300 rounded-2xl flex items-center justify-center mx-auto mb-3 text-xl"><i class="fas fa-mug-hot"></i></div>
            <p class="text-sm font-black text-slate-700">Tidak Ada Jadwal Hari Ini</p>
            <p class="text-[11px] font-semibold text-slate-400 mt-1">Nikmati hari libur mengajar Anda.</p>
        </div>
        @endforelse

        <!-- ===== JADWAL MINGGU INI (horizontal carousel) ===== -->
        <div class="flex items-center justify-between mt-7 mb-3 mx-4">
            <h3 class="text-[15px] font-black text-slate-900 tracking-tight">Jadwal Minggu Ini</h3>
            <span class="text-[11px] font-bold text-slate-400 flex items-center gap-1">Geser <i class="fas fa-arrow-right text-[9px]"></i></span>
        </div>

        @php
            $hariIniStr = \Carbon\Carbon::now()->translatedFormat('l');
            $mapHari = ['Monday'=>'Senin','Tuesday'=>'Selasa','Wednesday'=>'Rabu','Thursday'=>'Kamis','Friday'=>'Jumat','Saturday'=>'Sabtu','Sunday'=>'Ahad'];
            $hariIniLokal = $mapHari[$hariIniStr] ?? 'Senin';
        @endphp

        <div id="jadwal-carousel" class="flex overflow-x-auto snap-x snap-mandatory gap-3 scrollbar-none px-4 pb-1">
            @forelse($jadwals as $hari => $listJadwal)
            @php $isToday = (strtolower($hari) == strtolower($hariIniLokal)); @endphp
            <div class="snap-center shrink-0 w-[86%] max-w-[330px] bg-white rounded-3xl p-5 border transition-all duration-300 {{ $isToday ? 'border-emerald-300 shadow-[0_14px_34px_-16px_rgba(16,185,129,0.45)]' : 'border-slate-100 shadow-[0_6px_20px_-12px_rgba(2,6,23,0.12)]' }}">
                <div class="flex justify-between items-center mb-4">
                    <div class="flex items-center gap-2">
                        <p class="text-sm font-black text-slate-700 uppercase tracking-widest">Hari {{ $hari }}</p>
                    </div>
                    <span class="text-[10px] font-bold text-slate-400 bg-slate-50 px-2.5 py-1 rounded-lg border border-slate-100">{{ count($listJadwal) }} Pertemuan</span>
                </div>

                <div class="space-y-2.5">
                    @foreach($listJadwal as $j)
                    <div class="flex items-center p-2 -m-2 rounded-2xl hover:bg-slate-50 transition-colors">
                        <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-700 flex items-center justify-center font-black text-sm border border-emerald-100 shrink-0">{{ $j['jam_tampil'] }}</div>
                        <div class="flex-1 min-w-0 ml-3">
                            <h4 class="text-[13px] font-black text-slate-800 truncate">{{ $j['mata_pelajaran'] ?? 'Pelajaran' }}</h4>
                            <div class="flex flex-col">
                                @if($j['nama_kitab'] !== '-')
                                <span class="text-[10px] font-bold text-emerald-600 flex items-center"><i class="fas fa-book-open mr-1.5 opacity-60"></i><span class="truncate">{{ $j['nama_kitab'] }}</span></span>
                                @endif
                                <span class="text-[10px] font-bold text-slate-400 flex items-center"><i class="fas fa-users text-slate-300 mr-1.5"></i> Kelas {{ $j['kelas'] ?? '-' }}</span>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @empty
            <div class="snap-center shrink-0 w-[86%] max-w-[330px] bg-white rounded-3xl p-6 border border-dashed border-slate-200 flex flex-col items-center justify-center text-center">
                <div class="w-16 h-16 bg-slate-50 text-slate-300 rounded-full flex items-center justify-center mb-3 text-3xl"><i class="fas fa-mug-hot"></i></div>
                <p class="text-base font-black text-slate-700">Belum Ada Jadwal</p>
                <p class="text-xs font-medium text-slate-400 mt-1 leading-relaxed">Jadwal mengajar Anda masih kosong untuk periode ini.</p>
            </div>
            @endforelse
        </div>

        @if(count($jadwals) > 0)
        <div class="flex justify-center items-center space-x-2 mt-3">
            @foreach($jadwals as $index => $item)
                <div class="dot-indicator rounded-full transition-all duration-300 {{ $loop->first ? 'bg-emerald-500 w-6 h-2' : 'bg-slate-300 w-2 h-2' }}"></div>
            @endforeach
        </div>
        @endif

    </div>

    <!-- NAVIGASI BAWAH -->
    @include('partials.bottom-nav', ['active' => 'beranda'])

</div>

<script>
    (function() {
        const carousel = document.getElementById('jadwal-carousel');
        const dots = document.querySelectorAll('.dot-indicator');

        if(carousel && dots.length > 0) {
            carousel.addEventListener('scroll', () => {
                const scrollPosition = carousel.scrollLeft;
                const cardWidth = carousel.clientWidth;
                const activeIndex = Math.min(Math.round(scrollPosition / cardWidth), dots.length - 1);
                dots.forEach((dot, index) => {
                    if (index === activeIndex) {
                        dot.className = 'dot-indicator rounded-full transition-all duration-300 bg-emerald-500 w-6 h-2';
                    } else {
                        dot.className = 'dot-indicator rounded-full transition-all duration-300 bg-slate-300 w-2 h-2';
                    }
                });
            });
        }
    })();

    (function() {
        const pCarousel = document.getElementById('pengumuman-carousel');
        const pDots = document.querySelectorAll('.dot-pengumuman');
        if (pCarousel && pDots.length > 0) {
            let index = 0;
            const total = pDots.length;
            const cardWidth = () => pCarousel.querySelector('.snap-center')?.offsetWidth || pCarousel.clientWidth;
            const updateDots = () => {
                pDots.forEach((dot, i) => {
                    if (i === index) {
                        dot.className = 'dot-pengumuman rounded-full transition-all duration-300 bg-slate-700 w-6 h-2';
                    } else {
                        dot.className = 'dot-pengumuman rounded-full transition-all duration-300 bg-slate-300 w-2 h-2';
                    }
                });
            };
            const goTo = (i) => {
                index = (i + total) % total;
                pCarousel.scrollTo({ left: index * (cardWidth() + 12), behavior: 'smooth' });
                updateDots();
            };
            pCarousel.addEventListener('scroll', () => {
                const current = Math.round(pCarousel.scrollLeft / (cardWidth() + 12));
                index = Math.min(Math.max(current, 0), total - 1);
                updateDots();
            });
            setInterval(() => goTo(index + 1), 4000);
        }
    })();

    function toggleMenuLainnya(btn) {
        const box = document.getElementById('menu-tersembunyi');
        if (!box) return;
        const text = btn.querySelector('.menu-toggle-text');
        const chev = btn.querySelector('.menu-toggle-chev');
        if (box.classList.contains('hidden')) {
            box.classList.remove('hidden');
            if (text) text.textContent = 'Sembunyikan';
            if (chev) chev.classList.add('rotate-180');
        } else {
            box.classList.add('hidden');
            if (text) text.textContent = 'Tampilkan Semua';
            if (chev) chev.classList.remove('rotate-180');
        }
    }

    function tampilToast(tipe, pesan) {
        const warna = {
            info: 'from-slate-800 to-slate-900',
            sukses: 'from-emerald-500 to-emerald-600',
            error: 'from-rose-500 to-rose-600'
        };
        const ikon = tipe === 'sukses' ? 'fa-circle-check' : (tipe === 'error' ? 'fa-circle-xmark' : 'fa-circle-info');
        const body = document.createElement('div');
        body.className = 'fixed top-5 left-1/2 -translate-x-1/2 z-[70] max-w-[86%] w-full flex items-center gap-3 bg-gradient-to-r ' + (warna[tipe] || warna.info) + ' text-white text-sm font-bold px-4 py-3.5 rounded-2xl shadow-2xl transform transition-all duration-300 opacity-0 -translate-y-4';
        body.innerHTML = '<i class="fas ' + ikon + '"></i>' + pesan;
        document.body.appendChild(body);
        requestAnimationFrame(() => {
            body.classList.remove('opacity-0', '-translate-y-4');
        });
        setTimeout(() => {
            body.classList.add('opacity-0', '-translate-y-4');
            setTimeout(() => body.remove(), 350);
        }, 2200);
    }
</script>
@endsection




