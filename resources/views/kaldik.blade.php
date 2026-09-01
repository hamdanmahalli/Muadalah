@extends('layouts.app')
@section('title', 'Kaldik & Target Mengajar')
@section('content')
<style>
    header, aside { display: none !important; }
    #btn-buka-sidebar { display: none !important; }
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
    <div class="shrink-0 bg-white px-5 pt-7 pb-5 border-b border-slate-100 relative z-20">
        <div class="flex items-center justify-between gap-3">
            <div class="min-w-0">
                <h2 class="text-2xl font-black text-slate-900 tracking-tight truncate">Peta Mengajar</h2>
                <p class="text-xs font-semibold text-slate-400 mt-1">Target Kurikulum &amp; Sisa Jam</p>
            </div>
            @if(isset($periodeAktif) && $periodeAktif)
            <div class="shrink-0 px-3 py-1.5 rounded-full bg-emerald-50 border border-emerald-100 flex flex-col items-center">
                <span class="text-[8px] font-black text-emerald-600 uppercase tracking-wider">{{ $periodeAktif->semester }}</span>
                <span class="text-[11px] font-black text-slate-800 leading-tight">TA. {{ $periodeAktif->tahun_ajaran }}</span>
            </div>
            @endif
        </div>
    </div>

    <!-- KONTEN -->
    <div class="flex-1 overflow-y-auto px-5 pt-5 pb-32 scrollbar-none space-y-4">
        @forelse($targetMengajar as $item)
            <div class="bg-white rounded-3xl border border-slate-100 shadow-[0_4px_18px_-10px_rgba(2,6,23,0.06)] overflow-hidden transition-all">
                <div class="p-5">
                    <div class="flex justify-between items-start mb-4">
                        <div class="min-w-0">
                            <h4 class="text-[15px] font-black text-slate-800 leading-tight truncate">{{ $item->nama_pelajaran }}</h4>
                            <p class="text-[11px] font-bold text-slate-400 mt-1"><i class="fas fa-users text-slate-300 mr-1.5"></i> Diterapkan di Kelas {{ $item->nama_kelas }}</p>
                        </div>
                        <div class="text-right shrink-0 ml-3">
                            <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wide">Progress</span>
                            <p class="text-xl font-black text-emerald-600 leading-none mt-1">{{ $item->persentase_waktu }}%</p>
                        </div>
                    </div>

                    <!-- Progress Bar -->
                    <div class="w-full bg-slate-100 rounded-full h-2 mb-5 overflow-hidden">
                        <div class="bg-emerald-500 h-2 rounded-full transition-all duration-1000" style="width: {{ $item->persentase_waktu }}%"></div>
                    </div>

                    <!-- Statistik Angka (3 Kolom) -->
                    <div class="flex justify-between items-center bg-slate-50 rounded-2xl p-3 border border-slate-100">
                        <div class="text-center flex-1">
                            <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wider mb-1">Telah Berlalu</p>
                            <p class="text-lg font-black text-slate-800 leading-tight">{{ $item->telah_berlalu }} <span class="text-[10px] font-semibold text-slate-400">/ {{ $item->target_total }}</span></p>
                        </div>

                        @if($agendaUts)
                            <div class="w-px h-8 bg-slate-200 mx-2"></div>
                            <div class="text-center flex-1">
                                <p class="text-[9px] font-bold text-amber-500 uppercase tracking-wider mb-1">Sisa Pra-UTS</p>
                                <p class="text-lg font-black text-amber-600 leading-tight">{{ $item->sisa_pertemuan_pra_uts }} <span class="text-[9px] font-semibold opacity-70">Jadwal</span></p>
                            </div>
                        @endif

                        <div class="w-px h-8 bg-slate-200 mx-2"></div>

                        <div class="text-center flex-1">
                            <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wider mb-1">Total Sisa</p>
                            <p class="text-lg font-black text-slate-800 leading-tight">{{ $item->sisa_pertemuan_total }} <span class="text-[9px] font-semibold opacity-70">Jadwal</span></p>
                        </div>
                    </div>
                </div>

                <!-- ACCORDION DETAIL TARGET KURIKULUM -->
                <details class="group border-t border-slate-100">
                    <summary class="px-5 py-4 text-[11px] font-bold text-slate-600 flex justify-between items-center cursor-pointer hover:bg-slate-50 transition-colors duration-200 outline-none select-none">
                        <div class="flex items-center gap-3">
                            <div class="w-7 h-7 rounded-full bg-white shadow-sm border border-slate-200 flex items-center justify-center text-slate-400 group-open:text-emerald-600 group-open:border-emerald-200 transition-colors">
                                <i class="fas fa-bullseye text-[10px]"></i>
                            </div>
                            <span class="group-open:text-emerald-700 text-xs font-black tracking-wide transition-colors uppercase">Target Kurikulum</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-[9px] font-bold text-slate-400 group-open:hidden tracking-wider uppercase">Buka Peta</span>
                            <div class="w-6 h-6 rounded-full bg-slate-100 flex items-center justify-center group-open:bg-emerald-100 transition-colors">
                                <i class="fas fa-chevron-down text-[10px] text-slate-400 group-open:text-emerald-600 transition-transform duration-300 group-open:rotate-180"></i>
                            </div>
                        </div>
                    </summary>

                    <div class="px-6 pb-6 pt-5 bg-white border-t border-slate-100">
                        @if($item->batas)
                            <div class="pl-5 border-l-2 border-dashed border-slate-200 space-y-4 ml-1 relative">

                                <div class="relative py-0.5">
                                    <div class="absolute -left-[25px] top-1.5 w-2.5 h-2.5 rounded-full border-2 border-white bg-slate-400 shadow-sm z-10"></div>
                                    <p class="text-[9px] uppercase font-bold tracking-widest text-slate-400 mb-1">Mulai Dari (Awal)</p>
                                    <p class="text-sm font-black text-slate-700 leading-tight">{{ $item->batas->mulai_dari ?? 'Belum ditentukan' }}</p>
                                </div>

                                <div class="relative py-0.5">
                                    <div class="absolute -left-[25px] top-1.5 w-2.5 h-2.5 rounded-full border-2 border-white bg-indigo-500 shadow-sm z-10"></div>
                                    <p class="text-[9px] uppercase font-bold tracking-widest text-indigo-400 mb-1">Batas UTS Ganjil</p>
                                    <p class="text-sm font-black text-slate-700 leading-tight">{{ $item->batas->batas_uts_ganjil ?? 'Belum ditentukan' }}</p>
                                </div>

                                <div class="relative py-0.5">
                                    <div class="absolute -left-[25px] top-1.5 w-2.5 h-2.5 rounded-full border-2 border-white bg-blue-500 shadow-sm z-10"></div>
                                    <p class="text-[9px] uppercase font-bold tracking-widest text-blue-500 mb-1">Batas UAS Ganjil</p>
                                    <p class="text-sm font-black text-slate-700 leading-tight">{{ $item->batas->batas_uas_ganjil ?? 'Belum ditentukan' }}</p>
                                </div>

                                <div class="relative py-0.5">
                                    <div class="absolute -left-[25px] top-1.5 w-2.5 h-2.5 rounded-full border-2 border-white bg-amber-500 shadow-sm z-10"></div>
                                    <p class="text-[9px] uppercase font-bold tracking-widest text-amber-500 mb-1">Batas UTS Genap</p>
                                    <p class="text-sm font-black text-slate-700 leading-tight">{{ $item->batas->batas_uts_genap ?? 'Belum ditentukan' }}</p>
                                </div>

                                <div class="relative py-0.5">
                                    <div class="absolute -left-[25px] top-1.5 w-2.5 h-2.5 rounded-full border-2 border-white bg-emerald-500 shadow-sm z-10"></div>
                                    <p class="text-[9px] uppercase font-bold tracking-widest text-emerald-500 mb-1">Batas UAS Genap</p>
                                    <p class="text-sm font-black text-slate-700 leading-tight">{{ $item->batas->batas_uas_genap ?? 'Belum ditentukan' }}</p>
                                </div>

                            </div>
                        @else
                            <div class="flex flex-col items-center justify-center py-6 bg-slate-50 rounded-2xl border border-dashed border-slate-200">
                                <div class="w-10 h-10 bg-white rounded-full flex items-center justify-center mb-2 shadow-sm border border-slate-100">
                                    <i class="fas fa-folder-open text-slate-300 text-sm"></i>
                                </div>
                                <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Belum Ada Target</p>
                                <p class="text-[10px] font-medium text-slate-400 mt-1 text-center leading-relaxed">Staf Tata Usaha belum menyusun batas materi<br>untuk pelajaran ini.</p>
                            </div>
                        @endif
                    </div>
                </details>
            </div>
        @empty
            <div class="flex flex-col items-center justify-center text-center py-12 px-4 bg-white rounded-3xl border border-dashed border-slate-200">
                <div class="w-16 h-16 bg-slate-50 text-slate-300 rounded-full flex items-center justify-center mb-4 text-3xl"><i class="fas fa-calendar-xmark"></i></div>
                <p class="text-sm font-black text-slate-600">Peta Kosong</p>
                <p class="text-xs text-slate-400 mt-1">Sistem tidak menemukan beban mengajar Anda di periode ini.</p>
            </div>
        @endforelse
    </div>

    <!-- NAVIGASI BAWAH -->
    @include('partials.bottom-nav', ['active' => 'kaldik'])
</div>
@endsection
