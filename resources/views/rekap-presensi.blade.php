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
    
    <div class="shrink-0 bg-gradient-to-br from-emerald-600 to-teal-700 px-6 pt-8 pb-6 rounded-b-[2.5rem] shadow-md flex justify-between items-center relative z-20 overflow-hidden">
        <div class="absolute -right-6 -bottom-6 w-32 h-32 bg-white/10 rounded-full blur-xl pointer-events-none"></div>
        <div class="flex-1 min-w-0 relative z-10">
            <h2 class="text-2xl font-black text-white tracking-tight drop-shadow-md truncate">Rekap Kehadiran</h2>
            <p class="text-emerald-100 text-xs font-medium mt-1">Laporan & Riwayat Mengajar</p>
        </div>
        @if(isset($periodeAktif) && $periodeAktif)
        <div class="shrink-0 bg-white/20 backdrop-blur-md border border-white/20 px-3 py-1.5 rounded-2xl flex flex-col items-center shadow-lg ml-3 relative z-10">
            <span class="text-[9px] uppercase tracking-wider text-emerald-100 font-bold mb-0.5">TA. Aktif</span>
            <span class="text-xs font-black text-white">{{ $periodeAktif->tahun_ajaran }}</span>
        </div>
        @endif
    </div>

    <div class="px-5 pt-5 shrink-0 z-10 relative">
        <form action="/rekap-presensi" method="GET" id="form-filter">
            <div class="bg-white p-3 rounded-2xl shadow-sm border border-gray-100 flex flex-col gap-3">
                
                <div class="relative">
                    <select name="filter_tipe" id="filter_tipe" onchange="toggleKustom(); this.form.submit()" class="w-full bg-slate-50 border border-gray-200 text-slate-700 text-sm font-bold rounded-xl p-3 appearance-none outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition cursor-pointer">
                        <option value="bulan" {{ $filterTipe == 'bulan' ? 'selected' : '' }}>Rekap Bulan Berjalan</option>
                        <option value="tahun" {{ $filterTipe == 'tahun' ? 'selected' : '' }}>Rekap 1 Tahun Ajaran Penuh</option>
                        <option value="kustom" {{ $filterTipe == 'kustom' ? 'selected' : '' }}>Pilih Rentang Tanggal...</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-500">
                        <i class="fas fa-chevron-down text-xs"></i>
                    </div>
                </div>

                <div id="kustom_date" class="flex gap-2 {{ $filterTipe == 'kustom' ? '' : 'hidden' }}">
                    <input type="date" name="tgl_mulai" value="{{ $tglMulai }}" class="w-1/2 bg-white border border-gray-200 text-gray-700 text-xs font-bold rounded-xl p-2.5 outline-none focus:border-emerald-500 shadow-inner">
                    <input type="date" name="tgl_selesai" value="{{ $tglSelesai }}" class="w-1/2 bg-white border border-gray-200 text-gray-700 text-xs font-bold rounded-xl p-2.5 outline-none focus:border-emerald-500 shadow-inner">
                    <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl px-4 transition shadow-sm"><i class="fas fa-search"></i></button>
                </div>
            </div>
        </form>
    </div>

    <div class="px-5 pt-4 shrink-0 z-10 relative">
        <div class="bg-white rounded-2xl p-4 shadow-sm border border-emerald-100 flex justify-between items-center relative overflow-hidden">
            <div class="absolute -right-6 -top-6 w-20 h-20 bg-emerald-50 rounded-full blur-xl z-0 pointer-events-none"></div>
            <div class="absolute -left-6 -bottom-6 w-20 h-20 bg-sky-50 rounded-full blur-xl z-0 pointer-events-none"></div>
            
            <div class="text-center flex-1 relative z-10">
                <p class="text-[9px] uppercase tracking-widest text-slate-400 font-bold mb-0.5">Wajib</p>
                <p class="text-xl font-black text-slate-700">{{ $rekap->wajib }}</p>
            </div>
            <div class="w-px h-8 bg-slate-100 relative z-10"></div>
            <div class="text-center flex-1 relative z-10">
                <p class="text-[9px] uppercase tracking-widest text-emerald-500 font-bold mb-0.5">Hadir</p>
                <p class="text-xl font-black text-emerald-600">{{ $rekap->hadir }}</p>
            </div>
            <div class="w-px h-8 bg-slate-100 relative z-10"></div>
            <div class="text-center flex-1 relative z-10">
                <p class="text-[9px] uppercase tracking-widest text-rose-400 font-bold mb-0.5">Alpa</p>
                <p class="text-xl font-black text-rose-500">{{ $rekap->alpha }}</p>
            </div>
            <div class="w-px h-8 bg-slate-100 relative z-10"></div>
            <div class="text-center flex-1 relative z-10">
                <p class="text-[9px] uppercase tracking-widest text-sky-500 font-bold mb-0.5">Efektif</p>
                <p class="text-xl font-black {{ $rekap->persen >= 80 ? 'text-sky-600' : 'text-amber-500' }}">{{ $rekap->persen }}%</p>
            </div>
        </div>
    </div>

    <div class="flex-1 overflow-y-auto px-5 pt-4 pb-28 scrollbar-none space-y-3 z-0">
        @forelse($riwayat as $item)
            @php
                $wrnBg = 'bg-gray-100 text-gray-600 border-gray-200';
                $ikon = 'fa-clock';
                if($item->status == 'Hadir') { $wrnBg = 'bg-emerald-100 text-emerald-700 border-emerald-200'; $ikon = 'fa-check-circle'; }
                elseif(in_array($item->status, ['Kosong','Alpha','Alpa'])) { $wrnBg = 'bg-rose-100 text-rose-700 border-rose-200'; $ikon = 'fa-times-circle'; }
                elseif($item->status == 'Izin') { $wrnBg = 'bg-blue-100 text-blue-700 border-blue-200'; $ikon = 'fa-info-circle'; }
                elseif($item->status == 'Sakit') { $wrnBg = 'bg-amber-100 text-amber-700 border-amber-200'; $ikon = 'fa-plus-square'; }
                elseif($item->status == 'Piket') { $wrnBg = 'bg-purple-100 text-purple-700 border-purple-200'; $ikon = 'fa-people-arrows'; }
            @endphp
            <div class="bg-white p-3.5 rounded-2xl border border-gray-100 shadow-sm flex justify-between items-center hover:shadow-md transition duration-200">
                <div class="min-w-0 pr-3">
                    <p class="text-[11px] font-extrabold text-slate-800">{{ $item->tanggal_indo }} <span class="text-slate-400 font-medium ml-1">Jam {{ $item->jam_tampil }}</span></p>
                    <p class="text-[10px] text-slate-500 font-semibold mt-1 truncate"><i class="fas fa-book text-slate-300 mr-1.5"></i>{{ $item->nama_pelajaran }} <span class="mx-1 text-slate-300">•</span> Kls {{ $item->nama_kelas }}</p>
                </div>
                <span class="px-2.5 py-1.5 rounded-lg border text-[9px] font-black shrink-0 {{ $wrnBg }} flex items-center">
                    <i class="fas {{ $ikon }} mr-1 hidden sm:inline-block"></i> {{ $item->status }}
                </span>
            </div>
        @empty
            <div class="flex flex-col items-center justify-center text-center py-12 px-4 bg-white rounded-3xl border border-gray-100 border-dashed">
                <div class="w-16 h-16 bg-slate-50 text-slate-300 rounded-full flex items-center justify-center mb-4 text-3xl"><i class="fas fa-folder-open"></i></div>
                <p class="text-sm font-bold text-slate-600">Riwayat Kosong</p>
                <p class="text-xs text-slate-400 mt-1">Tidak ada catatan kehadiran pada rentang tanggal ini.</p>
            </div>
        @endforelse
    </div>

    {{-- NAVIGASI BAWAH (satu template) --}}
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