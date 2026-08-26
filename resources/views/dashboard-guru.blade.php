@extends('layouts.app')

@section('title', 'Beranda Guru - Muadalah Wustha')

@section('content')
<style>
    /* KECERDASAN UI: Murni Mobile App (Satu Layar, Tanpa Scroll Bawaan Web) */
    header, aside { display: none !important; }
    main { padding: 0 !important; background-color: #f8fafc !important; overflow: hidden !important; }
    body { overflow: hidden !important; background-color: #f8fafc !important; }
    .scrollbar-none::-webkit-scrollbar { display: none; }
    .scrollbar-none { -ms-overflow-style: none; scrollbar-width: none; }
    @supports (padding-bottom: env(safe-area-inset-bottom)) {
        .pb-safe { padding-bottom: env(safe-area-inset-bottom); }
    }
</style>

<div class="max-w-md mx-auto h-[100dvh] bg-slate-50 flex flex-col relative font-sans overflow-hidden">
    
    <div class="absolute top-0 w-full h-[45%] bg-gradient-to-br from-emerald-600 to-teal-800 rounded-b-[40px] shadow-lg z-0"></div>
    <div class="absolute -top-20 -right-20 w-64 h-64 bg-white opacity-10 rounded-full blur-3xl z-0"></div>
    <div class="absolute top-10 -left-10 w-40 h-40 bg-emerald-400 opacity-20 rounded-full blur-2xl z-0"></div>

    <div class="shrink-0 px-6 pt-10 pb-6 flex justify-between items-center gap-3 relative z-10">
        <div class="flex-1 min-w-0">
            <div class="flex items-center space-x-1.5 mb-0.5">
                <span class="w-2 h-2 rounded-full bg-emerald-300 animate-pulse"></span>
                <p class="text-emerald-100 text-[11px] font-bold tracking-widest uppercase">Assalamu'alaikum,</p>
            </div>
            <h2 class="text-lg sm:text-xl font-black text-white tracking-tight drop-shadow-md truncate" title="{{ auth()->user()->name }}">
                {{ auth()->user()->name ?? 'Ahmad' }}
            </h2>
        </div>
        
        <div class="shrink-0 bg-white/10 backdrop-blur-md border border-white/20 px-3 py-1.5 rounded-2xl flex flex-col items-center shadow-lg">
            <span class="text-[9px] uppercase tracking-wider text-emerald-100 font-bold mb-0.5">TA. Aktif</span>
            <span class="text-xs font-black text-white">{{ $periodeAktif->tahun_ajaran ?? '2026/2027' }}</span>
        </div>
    </div>

    <div class="shrink-0 px-5 pb-6 relative z-10">
        <div class="bg-white/95 backdrop-blur-xl rounded-3xl p-5 shadow-[0_8px_30px_rgb(0,0,0,0.08)] border border-white/50 flex items-center justify-between overflow-hidden">
            <div class="relative z-10">
                <span class="bg-indigo-50 text-indigo-600 text-[9px] font-black uppercase px-2 py-0.5 rounded-full mb-1.5 inline-block border border-indigo-100 tracking-wider">Info Pendidik</span>
                <h3 class="text-xs font-bold leading-snug text-slate-700">Gunakan Tombol QR Code di bawah untuk memindai kehadiran kelas.</h3>
            </div>
            <div class="w-12 h-12 bg-indigo-50 rounded-2xl flex items-center justify-center flex-shrink-0 text-indigo-500 text-2xl shadow-inner ml-3">
                <i class="fas fa-qrcode"></i>
            </div>
        </div>
    </div>

    <div class="flex-1 min-h-0 flex flex-col px-5 pb-[5.5rem] relative z-10">
        
        <div id="jadwal-carousel" class="flex-1 flex overflow-x-auto snap-x snap-mandatory gap-4 scrollbar-none items-stretch">
            
            @php 
                $hariIniStr = \Carbon\Carbon::now()->translatedFormat('l'); 
                $mapHari = ['Monday'=>'Senin','Tuesday'=>'Selasa','Wednesday'=>'Rabu','Thursday'=>'Kamis','Friday'=>'Jumat','Saturday'=>'Sabtu','Sunday'=>'Ahad']; 
                $hariIniLokal = $mapHari[$hariIniStr] ?? 'Senin'; 
            @endphp
            
            @forelse($jadwals as $hari => $listJadwal)
            @php $isToday = (strtolower($hari) == strtolower($hariIniLokal)); @endphp
            
            <div class="snap-center shrink-0 w-full h-full bg-white rounded-3xl p-5 border border-slate-100 shadow-sm transition-all duration-300 flex flex-col">
                
                <div class="shrink-0 flex justify-between items-center mb-5 border-b border-slate-50 pb-3">
                    <p class="text-sm font-black text-slate-500 uppercase tracking-widest flex items-center">
                        Hari {{ $hari }}
                    </p>
                    <span class="text-[10px] font-bold text-slate-500 bg-slate-50 px-2.5 py-1 rounded-lg border border-slate-100">
                        {{ count($listJadwal) }} Pertemuan
                    </span>
                </div>
                
                <div class="flex-1 overflow-y-auto scrollbar-none space-y-4 pb-2">
                    @foreach($listJadwal as $j)
                    <div class="flex items-center group">
                        <div class="w-14 h-14 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-black text-xl shrink-0 shadow-inner border border-emerald-100 group-hover:scale-105 transition-transform">
                            {{ $j['jam_tampil'] }}
                        </div>
                        
                        <div class="flex-1 min-w-0 ml-4">
                            <h4 class="text-base font-black text-slate-800 truncate mb-1">{{ $j['mata_pelajaran'] ?? 'Pelajaran' }}</h4>
                            <div class="flex flex-col space-y-1">
                                @if($j['nama_kitab'] !== '-')
                                <span class="text-[11px] font-bold text-emerald-600 flex items-center">
                                    <i class="fas fa-book-open mr-1.5 opacity-60"></i> <span class="truncate">{{ $j['nama_kitab'] }}</span>
                                </span>
                                @endif
                                <span class="text-[11px] font-bold text-slate-400 flex items-center">
                                    <i class="fas fa-chalkboard text-slate-300 mr-1.5"></i> Kelas {{ $j['kelas'] ?? '-' }}
                                </span>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @empty
            <div class="snap-center shrink-0 w-full h-full bg-white rounded-3xl p-5 shadow-sm border border-slate-100 flex flex-col items-center justify-center text-center">
                <div class="w-20 h-20 bg-slate-50 text-slate-300 rounded-full flex items-center justify-center mx-auto mb-4 text-4xl shadow-inner">
                    <i class="fas fa-mug-hot"></i>
                </div>
                <p class="text-base font-black text-slate-700">Belum Ada Jadwal</p>
                <p class="text-xs font-medium text-slate-400 mt-1 px-4">Jadwal mengajar Anda masih kosong untuk periode ini.</p>
            </div>
            @endforelse
        </div>

        @if(count($jadwals) > 0)
        <div class="shrink-0 flex justify-center items-center space-x-2 mt-5">
            @foreach($jadwals as $index => $item)
                <div class="dot-indicator rounded-full transition-all duration-300 {{ $loop->first ? 'bg-emerald-600 w-6 h-2' : 'bg-slate-300 w-2 h-2' }}"></div>
            @endforeach
        </div>
        @endif

    </div>

    <!-- NAVIGASI BAWAH (Disinkronkan dengan Konsep Menu) -->
    <div class="absolute bottom-0 left-0 right-0 z-50 w-full bg-white/95 backdrop-blur-xl border-t border-slate-100 shadow-[0_-10px_25px_rgba(0,0,0,0.06)] px-4 py-2 flex justify-between items-end pb-safe">
        
        <a href="/dashboard-guru" class="flex flex-col items-center justify-center w-12 text-emerald-600 pb-1 group">
            <i class="fas fa-home text-xl mb-1 group-active:scale-90 transition-transform"></i>
            <span class="text-[9px] font-black">Beranda</span>
        </a>

        <a href="/kaldik" class="flex flex-col items-center justify-center w-12 text-slate-400 hover:text-emerald-500 transition pb-1 group">
            <i class="fas fa-calendar-alt text-xl mb-1 group-active:scale-90 transition-transform"></i>
            <span class="text-[9px] font-bold">Kaldik</span>
        </a>

        <div class="relative -top-6 flex justify-center items-center">
            <a href="/scan-kelas" class="w-16 h-16 rounded-full bg-gradient-to-b from-emerald-400 to-emerald-600 text-white flex items-center justify-center text-3xl shadow-[0_8px_20px_rgba(16,185,129,0.4)] border-4 border-slate-50 transform hover:scale-105 active:scale-95 transition-all">
                <i class="fas fa-qrcode"></i>
            </a>
        </div>

        <a href="/rekap-presensi" class="flex flex-col items-center justify-center w-12 text-slate-400 hover:text-emerald-500 transition pb-1 group">
            <i class="fas fa-file-invoice text-xl mb-1 group-active:scale-90 transition-transform"></i>
            <span class="text-[9px] font-bold">Rekap</span>
        </a>

        <!-- PERBAIKAN: Tombol Menu (Menggunakan route cerdas Laravel) -->
        <a href="/menu" class="flex flex-col items-center justify-center w-12 text-slate-400 hover:text-emerald-500 transition pb-1 group">
            <i class="fas fa-bars text-xl mb-1 group-active:scale-90 transition-transform"></i>
            <span class="text-[9px] font-bold">Menu</span>
        </a>

    </div>

</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const carousel = document.getElementById('jadwal-carousel');
        const dots = document.querySelectorAll('.dot-indicator');

        if(carousel && dots.length > 0) {
            carousel.addEventListener('scroll', () => {
                const scrollPosition = carousel.scrollLeft;
                const cardWidth = carousel.clientWidth;
                const activeIndex = Math.round(scrollPosition / cardWidth);

                dots.forEach((dot, index) => {
                    if (index === activeIndex) {
                        dot.className = 'dot-indicator rounded-full transition-all duration-300 bg-emerald-600 w-6 h-2';
                    } else {
                        dot.className = 'dot-indicator rounded-full transition-all duration-300 bg-slate-300 w-2 h-2';
                    }
                });
            });
        }
    });
</script>
@endsection