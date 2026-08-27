@extends('layouts.app')
@section('title', 'Kaldik & Target Mengajar')
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

<div class="max-w-md mx-auto h-[100dvh] bg-slate-50 flex flex-col relative font-sans overflow-hidden">
    
    <!-- HEADER -->
    <div class="shrink-0 bg-gradient-to-br from-emerald-600 to-teal-700 px-6 pt-8 pb-6 rounded-b-[2.5rem] shadow-md flex justify-between items-center relative z-20">
        <div class="flex-1 min-w-0 relative z-10">
            <h2 class="text-2xl font-black text-white tracking-tight drop-shadow-md truncate">Peta Mengajar</h2>
            <p class="text-emerald-100 text-xs font-medium mt-1">Target Kurikulum & Sisa Jam</p>
        </div>
        @if(isset($periodeAktif) && $periodeAktif)
        <div class="shrink-0 bg-white/20 backdrop-blur-md border border-white/20 px-3 py-1.5 rounded-2xl flex flex-col items-center shadow-lg ml-3 relative z-10">
            <span class="text-[9px] uppercase tracking-wider text-emerald-100 font-bold mb-0.5">{{ $periodeAktif->semester }}</span>
            <span class="text-xs font-black text-white">{{ $periodeAktif->tahun_ajaran }}</span>
        </div>
        @endif
    </div>

    <!-- KONTEN -->
    <div class="flex-1 overflow-y-auto px-5 pt-6 scrollbar-none space-y-6">
        @forelse($targetMengajar as $item)
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden transition-all hover:shadow-md">
                <div class="p-5">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h4 class="text-base font-black text-slate-800 leading-tight">{{ $item->nama_pelajaran }}</h4>
                            <p class="text-[11px] font-bold text-slate-400 mt-1"><i class="fas fa-chalkboard text-slate-300 mr-1.5"></i> Diterapkan di Kelas {{ $item->nama_kelas }}</p>
                        </div>
                        <div class="text-right shrink-0 ml-3">
                            <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wide">Progress</span>
                            <p class="text-lg font-black text-emerald-600 leading-none mt-0.5">{{ $item->persentase_waktu }}%</p>
                        </div>
                    </div>

                    <!-- Progress Bar Waktu Berlalu -->
                    <div class="w-full bg-slate-100 rounded-full h-2.5 mb-5 shadow-inner">
                        <div class="bg-emerald-500 h-2.5 rounded-full transition-all duration-1000 shadow-sm" style="width: {{ $item->persentase_waktu }}%"></div>
                    </div>

                    <!-- Statistik Angka (Lebih Rapi 3 Kolom) -->
                    <div class="flex justify-between items-center bg-slate-50 rounded-2xl p-3 border border-slate-100 mb-2">
                        <div class="text-center flex-1">
                            <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wider mb-0.5">Telah Berlalu</p>
                            <p class="text-lg font-black text-slate-700 leading-tight">{{ $item->telah_berlalu }} <span class="text-[10px] font-semibold text-slate-400">/ {{ $item->target_total }}</span></p>
                        </div>
                        
                        @if($agendaUts && $item->sisa_pertemuan_pra_uts > 0)
                            <div class="w-px h-8 bg-slate-200 mx-2"></div>
                            <div class="text-center flex-1">
                                <p class="text-[9px] font-bold text-amber-500 uppercase tracking-wider mb-0.5">Sisa Pra-UTS</p>
                                <p class="text-lg font-black text-amber-600 leading-tight">{{ $item->sisa_pertemuan_pra_uts }} <span class="text-[9px] font-semibold opacity-70">Jadwal</span></p>
                            </div>
                        @endif
                        
                        <div class="w-px h-8 bg-slate-200 mx-2"></div>
                        
                        <div class="text-center flex-1 bg-sky-50 rounded-xl py-1.5 border border-white shadow-sm">
                            <p class="text-[9px] font-bold text-sky-600 uppercase tracking-wider mb-0.5">Total Sisa</p>
                            <p class="text-lg font-black text-sky-600 leading-tight">{{ $item->sisa_pertemuan_total }} <span class="text-[9px] font-semibold opacity-70">Jadwal</span></p>
                        </div>
                    </div>
                </div>

                <!-- ACCORDION DETAIL TARGET KURIKULUM (Premium Timeline) -->
                <details class="group border-t border-slate-100 bg-slate-50/50">
                    <summary class="px-5 py-4 text-[11px] font-bold text-slate-600 flex justify-between items-center cursor-pointer hover:bg-indigo-50/50 transition-colors duration-200 outline-none select-none">
                        <div class="flex items-center gap-3">
                            <div class="w-7 h-7 rounded-full bg-white shadow-sm border border-slate-200 flex items-center justify-center text-slate-400 group-open:text-indigo-500 group-open:border-indigo-200 transition-colors">
                                <i class="fas fa-bullseye text-[10px]"></i>
                            </div>
                            <span class="group-open:text-indigo-700 text-xs font-black tracking-wide transition-colors uppercase">Target Kurikulum</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-[9px] font-bold text-slate-400 group-open:hidden tracking-wider uppercase">Buka Peta</span>
                            <div class="w-6 h-6 rounded-full bg-slate-100 flex items-center justify-center group-open:bg-indigo-100 transition-colors">
                                <i class="fas fa-chevron-down text-[10px] text-slate-400 group-open:text-indigo-500 transition-transform duration-300 group-open:rotate-180"></i>
                            </div>
                        </div>
                    </summary>
                    
                    <div class="px-6 pb-6 pt-4 bg-white border-t border-slate-100">
                        @if($item->batas)
                            <!-- Garis Timeline Putus-Putus (Minimalis / Teks Saja) -->
                            <div class="pl-5 border-l-2 border-dashed border-slate-200 space-y-4 ml-1 relative">
                                
                                <!-- Mulai Dari -->
                                <div class="relative py-0.5">
                                    <div class="absolute -left-[25px] top-1.5 w-2.5 h-2.5 rounded-full border-2 border-white bg-slate-400 shadow-sm z-10"></div>
                                    <p class="text-[9px] uppercase font-bold tracking-widest text-slate-400 mb-0.5">Mulai Dari (Awal)</p>
                                    <p class="text-sm font-black text-slate-700 leading-tight">{{ $item->batas->mulai_dari ?? 'Belum ditentukan' }}</p>
                                </div>
                                
                                <!-- UTS Ganjil -->
                                <div class="relative py-0.5">
                                    <div class="absolute -left-[25px] top-1.5 w-2.5 h-2.5 rounded-full border-2 border-white bg-indigo-500 shadow-sm z-10"></div>
                                    <p class="text-[9px] uppercase font-bold tracking-widest text-indigo-400 mb-0.5">Batas UTS Ganjil</p>
                                    <p class="text-sm font-black text-slate-700 leading-tight">{{ $item->batas->batas_uts_ganjil ?? 'Belum ditentukan' }}</p>
                                </div>
                                
                                <!-- UAS Ganjil -->
                                <div class="relative py-0.5">
                                    <div class="absolute -left-[25px] top-1.5 w-2.5 h-2.5 rounded-full border-2 border-white bg-blue-500 shadow-sm z-10"></div>
                                    <p class="text-[9px] uppercase font-bold tracking-widest text-blue-500 mb-0.5">Batas UAS Ganjil</p>
                                    <p class="text-sm font-black text-slate-700 leading-tight">{{ $item->batas->batas_uas_ganjil ?? 'Belum ditentukan' }}</p>
                                </div>
                                
                                <!-- UTS Genap -->
                                <div class="relative py-0.5">
                                    <div class="absolute -left-[25px] top-1.5 w-2.5 h-2.5 rounded-full border-2 border-white bg-amber-500 shadow-sm z-10"></div>
                                    <p class="text-[9px] uppercase font-bold tracking-widest text-amber-500 mb-0.5">Batas UTS Genap</p>
                                    <p class="text-sm font-black text-slate-700 leading-tight">{{ $item->batas->batas_uts_genap ?? 'Belum ditentukan' }}</p>
                                </div>
                                
                                <!-- UAS Genap -->
                                <div class="relative py-0.5">
                                    <div class="absolute -left-[25px] top-1.5 w-2.5 h-2.5 rounded-full border-2 border-white bg-emerald-500 shadow-sm z-10"></div>
                                    <p class="text-[9px] uppercase font-bold tracking-widest text-emerald-500 mb-0.5">Batas UAS Genap</p>
                                    <p class="text-sm font-black text-slate-700 leading-tight">{{ $item->batas->batas_uas_genap ?? 'Belum ditentukan' }}</p>
                                </div>
                                
                            </div>
                        @else
                            <!-- Empty State -->
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
            <div class="flex flex-col items-center justify-center text-center py-12 px-4 bg-white rounded-3xl border border-gray-100 border-dashed">
                <div class="w-16 h-16 bg-slate-50 text-slate-300 rounded-full flex items-center justify-center mb-4 text-3xl"><i class="fas fa-calendar-times"></i></div>
                <p class="text-sm font-bold text-slate-600">Peta Kosong</p>
                <p class="text-xs text-slate-400 mt-1">Sistem tidak menemukan beban mengajar Anda di periode ini.</p>
            </div>
        @endforelse
    </div>

    <!-- NAVIGASI BAWAH -->
    <div class="shrink-0 z-40 w-full bg-white/95 backdrop-blur-xl border-t border-slate-100 shadow-[0_-10px_25px_rgba(0,0,0,0.06)] px-4 py-2 flex justify-between items-end pb-safe pt-2">
        <a href="/dashboard-guru" class="flex flex-col items-center justify-center w-12 text-slate-400 hover:text-emerald-500 transition pb-1"><i class="fas fa-home text-xl mb-1"></i><span class="text-[9px] font-bold">Beranda</span></a>
        <a href="/kaldik" class="flex flex-col items-center justify-center w-12 text-emerald-600 pb-1"><i class="fas fa-calendar-alt text-xl mb-1"></i><span class="text-[9px] font-black">Kaldik</span></a>
        <div class="relative -top-6 flex justify-center items-center"><a href="/scan-kelas" class="w-16 h-16 rounded-full bg-gradient-to-b from-emerald-400 to-emerald-600 text-white flex items-center justify-center text-3xl shadow-[0_8px_20px_rgba(16,185,129,0.4)] border-4 border-slate-50 transform hover:scale-105 active:scale-95 transition-all"><i class="fas fa-qrcode"></i></a></div>
        <a href="/rekap-presensi" class="flex flex-col items-center justify-center w-12 text-slate-400 hover:text-emerald-500 transition pb-1"><i class="fas fa-file-invoice text-xl mb-1"></i><span class="text-[9px] font-bold">Rekap</span></a>
        <a href="/menu" class="flex flex-col items-center justify-center w-12 text-slate-400 hover:text-emerald-500 transition pb-1 group"><i class="fas fa-bars text-xl mb-1"></i><span class="text-[9px] font-bold">Menu</span></a>
    </div>
</div>
@endsection