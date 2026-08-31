@extends('layouts.app')

@section('title', 'Rekap Kehadiran - SmartPesantren')

@section('content')
<style>
    header, aside { display: none !important; }
    main { padding: 0 !important; background-color: #f8fafc !important; overflow: hidden !important; }
    body { overflow: hidden !important; background-color: #f8fafc !important; }
    .scrollbar-none::-webkit-scrollbar { display: none; }
    .scrollbar-none { -ms-overflow-style: none; scrollbar-width: none; }
    @supports (padding-bottom: env(safe-area-inset-bottom)) {
        .pb-safe { padding-bottom: env(safe-area-inset-bottom); }
    }
</style>

<div data-turbo="true" class="max-w-md mx-auto h-[100dvh] bg-slate-50 flex flex-col relative font-sans overflow-hidden">

    <!-- HEADER MODERN -->
    <div class="shrink-0 bg-white px-5 pt-7 pb-5 border-b border-slate-100 relative z-20">
        <div class="flex items-center justify-between gap-3">
            <div class="min-w-0">
                <h2 class="text-2xl font-black text-slate-900 tracking-tight truncate">Rekap Kehadiran</h2>
                <p class="text-xs font-semibold text-slate-400 mt-1">Laporan &amp; Riwayat Mengajar</p>
            </div>
            @if(isset($periodeAktif) && $periodeAktif)
            <div class="shrink-0 px-3 py-1.5 rounded-full bg-emerald-50 border border-emerald-100 flex flex-col items-center">
                <span class="text-[8px] font-black text-emerald-600 uppercase tracking-wider">{{ $periodeAktif->semester }}</span>
                <span class="text-[11px] font-black text-slate-800 leading-tight">TA. {{ $periodeAktif->tahun_ajaran }}</span>
            </div>
            @endif
        </div>
    </div>

    <!-- FILTER -->
    <div class="px-5 pt-4 shrink-0 z-10 relative">
        <form action="/rekap-presensi" method="GET" id="form-filter">
            <div class="flex flex-col gap-2.5">
                <div class="relative">
                    <select name="filter_tipe" id="filter_tipe" onchange="toggleKustom(); this.form.submit()" class="w-full bg-white border border-slate-200 text-slate-700 text-sm font-bold rounded-2xl p-3.5 appearance-none outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition cursor-pointer shadow-sm pl-11">
                        <option value="bulan" {{ $filterTipe == 'bulan' ? 'selected' : '' }}>Rekap Bulan Berjalan</option>
                        <option value="tahun" {{ $filterTipe == 'tahun' ? 'selected' : '' }}>Rekap 1 Tahun Ajaran Penuh</option>
                        <option value="kustom" {{ $filterTipe == 'kustom' ? 'selected' : '' }}>Pilih Rentang Tanggal...</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 left-0 pl-4 flex items-center text-slate-400">
                        <i class="fas fa-calendar-days text-sm"></i>
                    </div>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-slate-400">
                        <i class="fas fa-chevron-down text-xs"></i>
                    </div>
                </div>

                <div id="kustom_date" class="flex gap-2 {{ $filterTipe == 'kustom' ? '' : 'hidden' }}">
                    <input type="date" name="tgl_mulai" value="{{ $tglMulai }}" class="w-1/2 bg-white border border-slate-200 text-slate-700 text-xs font-bold rounded-2xl p-3 outline-none focus:border-emerald-500 shadow-sm">
                    <input type="date" name="tgl_selesai" value="{{ $tglSelesai }}" class="w-1/2 bg-white border border-slate-200 text-slate-700 text-xs font-bold rounded-2xl p-3 outline-none focus:border-emerald-500 shadow-sm">
                    <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white rounded-2xl px-5 transition shadow-sm"><i class="fas fa-search"></i></button>
                </div>
            </div>
        </form>
    </div>

    <!-- RINGKASAN -->
    <div class="px-5 pt-4 shrink-0 z-10 relative">
        <div class="bg-gradient-to-br from-emerald-600 to-teal-600 rounded-3xl px-5 py-4 flex justify-between items-center relative overflow-hidden shadow-[0_12px_28px_-12px_rgba(16,185,129,0.5)]">
            <div class="absolute -right-6 -top-6 w-24 h-24 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
            <div class="text-center flex-1 relative z-10">
                <p class="text-[9px] uppercase tracking-widest text-emerald-100 font-bold mb-1">Wajib</p>
                <p class="text-2xl font-black text-white">{{ $rekap->wajib }}</p>
            </div>
            <div class="w-px h-9 bg-white/20 relative z-10"></div>
            <div class="text-center flex-1 relative z-10">
                <p class="text-[9px] uppercase tracking-widest text-emerald-100 font-bold mb-1">Hadir</p>
                <p class="text-2xl font-black text-white">{{ $rekap->hadir }}</p>
            </div>
            <div class="w-px h-9 bg-white/20 relative z-10"></div>
            <div class="text-center flex-1 relative z-10">
                <p class="text-[9px] uppercase tracking-widest text-emerald-100 font-bold mb-1">Alpa</p>
                <p class="text-2xl font-black text-white">{{ $rekap->alpha }}</p>
            </div>
            <div class="w-px h-9 bg-white/20 relative z-10"></div>
            <div class="text-center flex-1 relative z-10">
                <p class="text-[9px] uppercase tracking-widest text-emerald-100 font-bold mb-1">Efektif</p>
                <p class="text-2xl font-black {{ $rekap->persen >= 80 ? 'text-emerald-200' : 'text-amber-300' }}">{{ $rekap->persen }}%</p>
            </div>
        </div>
    </div>

    <!-- RIWAYAT -->
    <div class="flex-1 overflow-y-auto px-5 pt-4 pb-32 scrollbar-none space-y-2.5 z-0">
        @forelse($riwayat as $item)
            @php
                $wrnBg = 'bg-slate-100 text-slate-600';
                $ikon = 'fa-clock';
                if($item->status == 'Hadir') { $wrnBg = 'bg-emerald-50 text-emerald-700 border-emerald-200'; $ikon = 'fa-check'; }
                elseif(in_array($item->status, ['Kosong','Alpha','Alpa'])) { $wrnBg = 'bg-rose-50 text-rose-600 border-rose-200'; $ikon = 'fa-xmark'; }
                elseif($item->status == 'Izin') { $wrnBg = 'bg-sky-50 text-sky-600 border-sky-200'; $ikon = 'fa-info'; }
                elseif($item->status == 'Sakit') { $wrnBg = 'bg-amber-50 text-amber-600 border-amber-200'; $ikon = 'fa-briefcase-medical'; }
                elseif($item->status == 'Piket') { $wrnBg = 'bg-violet-50 text-violet-600 border-violet-200'; $ikon = 'fa-people-arrows'; }
            @endphp
            <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-[0_2px_10px_-6px_rgba(2,6,23,0.05)] flex justify-between items-center gap-3">
                <div class="min-w-0 pr-2">
                    <p class="text-[12px] font-extrabold text-slate-800">{{ $item->tanggal_indo }} <span class="text-slate-400 font-medium ml-1">Jam {{ $item->jam_tampil }}</span></p>
                    <p class="text-[11px] text-slate-500 font-semibold mt-1 truncate"><i class="fas fa-book text-slate-300 mr-1.5"></i>{{ $item->nama_pelajaran }} <span class="mx-1 text-slate-300">•</span> Kls {{ $item->nama_kelas }}</p>
                </div>
                <span class="px-2.5 py-1.5 rounded-xl border text-[10px] font-black shrink-0 {{ $wrnBg }} flex items-center gap-1.5">
                    <i class="fas {{ $ikon }}"></i> {{ $item->status }}
                </span>
            </div>
        @empty
            <div class="flex flex-col items-center justify-center text-center py-12 px-4 bg-white rounded-3xl border border-dashed border-slate-200">
                <div class="w-16 h-16 bg-slate-50 text-slate-300 rounded-full flex items-center justify-center mb-4 text-3xl"><i class="fas fa-folder-open"></i></div>
                <p class="text-sm font-black text-slate-600">Riwayat Kosong</p>
                <p class="text-xs text-slate-400 mt-1">Tidak ada catatan kehadiran pada rentang tanggal ini.</p>
            </div>
        @endforelse
    </div>

    {{-- NAVIGASI BAWAH --}}
    @include('partials.bottom-nav', ['active' => 'rekap'])

</div>

<script>
    function toggleKustom() {
        const tipe = document.getElementById('filter_tipe').value;
        const kotakKustom = document.getElementById('kustom_date');
        if (tipe === 'kustom') {
            kotakKustom.classList.remove('hidden');
        } else {
            kotakKustom.classList.add('hidden');
        }
    }
</script>
@endsection