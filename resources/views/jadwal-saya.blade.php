@extends('layouts.app')
@section('title', 'Jadwal Mengajar Saya')
@section('content')
<style>
    header, aside { display: none !important; }
    main { padding: 0 !important; background-color: #f8fafc !important; overflow: hidden !important; }
    body { overflow: hidden !important; background-color: #f8fafc !important; }
    .scrollbar-none::-webkit-scrollbar { display: none; }
    .scrollbar-none { -ms-overflow-style: none; scrollbar-width: none; }
    @supports (padding-bottom: env(safe-area-inset-bottom)) { .pb-safe { padding-bottom: env(safe-area-inset-bottom); } }

    details > summary { list-style: none; }
    details > summary::-webkit-details-marker { display: none; }
    details[open] summary ~ * { animation: sweep .3s ease-in-out; }
    @keyframes sweep { 0% { opacity: 0; transform: translateY(-10px) } 100% { opacity: 1; transform: translateY(0) } }
</style>

<div data-turbo="true" class="max-w-md mx-auto h-[100dvh] bg-slate-50 flex flex-col relative font-sans overflow-hidden">

    <!-- HEADER MODERN -->
    <div class="shrink-0 bg-white px-4 pt-4 pb-4 border-b border-slate-100 relative z-20">
        <div class="flex items-center gap-3">
            <a href="/menu" class="w-10 h-10 rounded-full bg-slate-100 text-slate-600 flex items-center justify-center active:scale-95 transition-all hover:bg-slate-200 shrink-0">
                <i class="fas fa-arrow-left text-sm"></i>
            </a>
            <div class="flex-1 min-w-0">
                <h2 class="text-base font-black text-slate-900 tracking-tight truncate">Jadwal Saya</h2>
                <p class="text-[10px] font-bold text-emerald-600 uppercase tracking-widest mt-0.5">Selamat bertugas, {{ auth()->user()->name }}</p>
            </div>
            @if(isset($periodeAktif) && $periodeAktif)
            <div class="shrink-0 px-3 py-1.5 rounded-full bg-emerald-50 border border-emerald-100 flex flex-col items-center">
                <span class="text-[8px] font-black text-emerald-600 uppercase tracking-wider">{{ $periodeAktif->semester }}</span>
                <span class="text-[11px] font-black text-slate-800 leading-tight">TA. {{ $periodeAktif->tahun_ajaran }}</span>
            </div>
            @endif
        </div>
    </div>

    <!-- AREA KONTEN -->
    <div class="flex-1 overflow-y-auto bg-slate-50 relative z-10 pt-5 pb-32 scrollbar-none px-5">

        @if(isset($pesan))
            <div class="bg-amber-50 border border-amber-200 text-amber-800 p-4 rounded-2xl text-xs font-bold flex items-start gap-3">
                <div class="w-8 h-8 rounded-xl bg-amber-100 flex items-center justify-center shrink-0 text-amber-600"><i class="fas fa-triangle-exclamation text-sm"></i></div>
                <span class="leading-relaxed pt-1">{{ $pesan }}</span>
            </div>
        @else
            <!-- REKAP KEHADIRAN -->
            <div class="bg-white rounded-3xl border border-slate-100 shadow-[0_4px_18px_-10px_rgba(2,6,23,0.06)] p-5 mb-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-black text-slate-900 tracking-tight">Rekap Kehadiran</h3>
                    <span class="px-2.5 py-1 rounded-full bg-emerald-50 border border-emerald-100 text-[10px] font-black text-emerald-600 uppercase">{{ \Carbon\Carbon::now()->locale('id')->translatedFormat('F Y') }}</span>
                </div>

                <div class="grid grid-cols-4 divide-x divide-slate-100">
                    <div class="pr-2">
                        <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wider mb-1">Wajib</p>
                        <p class="text-xl font-black text-slate-800 leading-none">{{ $rekapBulan->wajib }}</p>
                    </div>
                    <div class="px-2">
                        <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wider mb-1">Hadir</p>
                        <p class="text-xl font-black text-emerald-600 leading-none">{{ $rekapBulan->hadir }}</p>
                    </div>
                    <div class="px-2">
                        <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wider mb-1">Alpa</p>
                        <p class="text-xl font-black text-rose-500 leading-none">{{ $rekapBulan->alpha }}</p>
                    </div>
                    <div class="pl-2">
                        <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wider mb-1">Persen</p>
                        <p class="text-xl font-black leading-none {{ $rekapBulan->persen >= 80 ? 'text-emerald-600' : 'text-amber-500' }}">{{ $rekapBulan->persen }}%</p>
                    </div>
                </div>
            </div>

            <!-- SEKSI JADWAL MINGGUAN -->
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-base font-black text-slate-900 tracking-tight">Jadwal Mingguan</h3>
                <span class="text-[10px] font-bold text-slate-400">{{ count($jadwalTerstruktur) }} Hari Aktif</span>
            </div>

            <div class="space-y-4">
            @forelse($jadwalTerstruktur as $hari => $blokJadwal)
                @php
                    $isToday = (strtolower($hari) == strtolower(map_hari(\Carbon\Carbon::now()->format('l'))));
                @endphp
                <div class="bg-white rounded-3xl border overflow-hidden {{ $isToday ? 'border-emerald-200 shadow-[0_10px_28px_-14px_rgba(16,185,129,0.3)]' : 'border-slate-100 shadow-[0_4px_18px_-10px_rgba(2,6,23,0.06)]' }}">
                    <div class="px-5 pt-4 pb-3 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            @if($isToday)
                            <span class="px-2 py-1 rounded-lg bg-emerald-500 text-white text-[9px] font-black uppercase tracking-wider">Hari Ini</span>
                            @endif
                            <h4 class="font-black text-slate-700 uppercase tracking-widest text-sm">{{ $hari }}</h4>
                        </div>
                        <span class="text-[10px] font-bold text-slate-400">{{ count($blokJadwal) }} Pertemuan</span>
                    </div>
                    <div class="divide-y divide-slate-50 border-t border-slate-50">
                        @foreach($blokJadwal as $blok)
                        <div class="flex items-center px-5 py-3.5">
                            <div class="w-16 shrink-0 flex flex-col items-center">
                                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider mb-0.5">Jam Ke</span>
                                <span class="text-base font-black text-slate-800">{{ $blok['jam_mulai'] == $blok['jam_selesai'] ? $blok['jam_mulai'] : $blok['jam_mulai'].'-'.$blok['jam_selesai'] }}</span>
                            </div>
                            <div class="w-px h-10 bg-slate-100 mx-4 shrink-0"></div>
                            <div class="flex flex-col min-w-0">
                                <h5 class="text-sm font-black text-slate-800 leading-tight truncate">{{ $blok['mata_pelajaran'] ?? $blok['pelajaran'] }}</h5>
                                <span class="flex items-center mt-1 text-[11px] font-bold text-emerald-600 truncate"><i class="fas fa-users mr-1.5 opacity-70"></i> Kelas {{ $blok['kelas'] }}</span>
                                @if(!empty($blok['nama_kitab']) && $blok['nama_kitab'] != '-')
                                <span class="flex items-center mt-0.5 text-[10px] font-bold text-slate-400 truncate"><i class="fas fa-book-open mr-1.5 opacity-70"></i> {{ $blok['nama_kitab'] }}</span>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-3xl border border-dashed border-slate-200 p-8 text-center">
                    <div class="text-slate-300 text-4xl mb-4"><i class="fas fa-mug-hot"></i></div>
                    <h3 class="text-base font-black text-slate-700 mb-1">Jadwal Kosong</h3>
                    <p class="text-xs font-semibold text-slate-400">Anda belum memiliki jadwal mengajar yang aktif di periode ini.</p>
                </div>
            @endforelse
            </div>

            <!-- REKAP TOTAL 1 TAHUN -->
            <details class="group bg-white rounded-3xl border border-slate-100 shadow-[0_4px_18px_-10px_rgba(2,6,23,0.06)] overflow-hidden mt-6">
                <summary class="px-5 py-4 flex justify-between items-center cursor-pointer select-none">
                    <h3 class="text-sm font-black text-slate-800 flex items-center gap-2.5"><i class="fas fa-chart-simple text-emerald-500"></i> Rekap 1 Tahun Ajaran</h3>
                    <span class="w-6 h-6 rounded-full bg-slate-100 flex items-center justify-center group-open:bg-emerald-100 transition-colors">
                        <i class="fas fa-chevron-down text-[10px] text-slate-400 group-open:text-emerald-600 group-open:rotate-180 transition-transform duration-300"></i>
                    </span>
                </summary>
                <div class="px-5 pb-5 pt-2 border-t border-slate-50">
                    <div class="grid grid-cols-4 divide-x divide-slate-100 pt-3">
                        <div class="pr-2">
                            <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wider mb-1">Wajib</p>
                            <p class="text-xl font-black text-slate-800 leading-none">{{ $rekapTahun->wajib }}</p>
                        </div>
                        <div class="px-2">
                            <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wider mb-1">Hadir</p>
                            <p class="text-xl font-black text-emerald-600 leading-none">{{ $rekapTahun->hadir }}</p>
                        </div>
                        <div class="px-2">
                            <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wider mb-1">Izin/Sakit</p>
                            <p class="text-xl font-black text-amber-500 leading-none">{{ $rekapTahun->izin + $rekapTahun->sakit }}</p>
                        </div>
                        <div class="pl-2">
                            <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wider mb-1">Persen</p>
                            <p class="text-xl font-black leading-none {{ $rekapTahun->persen >= 80 ? 'text-emerald-600' : 'text-amber-500' }}">{{ $rekapTahun->persen }}%</p>
                        </div>
                    </div>
                </div>
            </details>
        @endif
    </div>

    <!-- NAVIGASI BAWAH -->
    @include('partials.bottom-nav', ['active' => ''])
</div>
@endsection