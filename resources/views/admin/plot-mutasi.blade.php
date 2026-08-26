@extends('layouts.app')

@section('title', 'Mutasi Massal Guru')

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Header -->
    <div class="mb-6 flex items-center">
        <a href="/plot-jadwal?kelas_id={{ $plot->kelas_id }}" class="w-10 h-10 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-slate-500 hover:bg-slate-50 hover:text-indigo-600 transition-colors mr-4 shadow-sm">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h2 class="text-2xl font-black text-slate-800 tracking-tight flex items-center">
                <i class="fas fa-random text-indigo-500 mr-3"></i> Mutasi Massal Pengajar
            </h2>
            <p class="text-sm font-bold text-slate-400 mt-0.5">Ganti guru untuk seluruh jadwal mata pelajaran ini sekaligus.</p>
        </div>
    </div>

    <form action="/plot-jadwal/{{ $plot->id }}/mutasi" method="POST" class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
        @csrf
        
        <!-- Info Pelajaran -->
        <div class="bg-slate-50 border-b border-slate-100 p-6 flex flex-col sm:flex-row items-center justify-between gap-4 text-center sm:text-left">
            <div>
                <p class="text-[10px] uppercase font-bold tracking-widest text-slate-400 mb-1">Mata Pelajaran & Kelas</p>
                <h3 class="text-xl font-black text-slate-700">{{ $plot->pelajaran->nama_pelajaran ?? 'Pelajaran' }} - Kelas {{ $plot->kelas->nama_kelas ?? '-' }}</h3>
            </div>
            <div class="bg-indigo-50 border border-indigo-100 px-4 py-2 rounded-xl">
                <p class="text-[10px] uppercase font-bold tracking-widest text-indigo-400 mb-0.5">Ruang Lingkup</p>
                <p class="text-sm font-black text-indigo-600"><i class="fas fa-layer-group mr-1"></i> Mutasi Massal ({{ $plot->beban_jam }} Jam)</p>
            </div>
        </div>

        <div class="p-6 md:p-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 relative">
                
                <div class="hidden md:flex absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-12 h-12 bg-white border-2 border-slate-100 rounded-full items-center justify-center text-slate-300 z-10 shadow-sm">
                    <i class="fas fa-arrow-right text-xl"></i>
                </div>

                <!-- KOLOM KIRI: GURU LAMA -->
                <div class="bg-amber-50/50 border border-amber-100 rounded-2xl p-5 relative overflow-hidden">
                    <div class="absolute -right-4 -top-4 w-16 h-16 bg-amber-100 opacity-50 rounded-full blur-xl"></div>
                    <span class="inline-block px-3 py-1 bg-amber-100 text-amber-600 text-[9px] font-black uppercase tracking-widest rounded-md mb-4 border border-amber-200">
                        Pengajar Saat Ini
                    </span>
                    
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 rounded-full bg-white border border-amber-200 text-amber-500 flex items-center justify-center text-lg shadow-sm mr-4 shrink-0">
                            <i class="fas fa-user-minus"></i>
                        </div>
                        <div>
                            <p class="text-[11px] font-bold text-amber-600/70 uppercase tracking-wider mb-0.5">Nama Guru</p>
                            <p class="text-base font-black text-slate-700">{{ $plot->guru->nama_guru ?? 'Tidak diketahui' }}</p>
                        </div>
                    </div>
                    
                    <p class="text-xs font-medium text-amber-700/80 leading-relaxed bg-white/60 p-3 rounded-xl border border-amber-100">
                        <i class="fas fa-info-circle mr-1"></i> Seluruh jadwal harian guru ini akan ditutup pada <strong class="text-amber-800">1 hari sebelum</strong> tanggal efektif. Riwayat absen sebelumnya tetap aman.
                    </p>
                </div>

                <!-- KOLOM KANAN: GURU BARU -->
                <div class="bg-emerald-50/50 border border-emerald-100 rounded-2xl p-5 relative overflow-hidden">
                    <div class="absolute -right-4 -top-4 w-16 h-16 bg-emerald-100 opacity-50 rounded-full blur-xl"></div>
                    <span class="inline-block px-3 py-1 bg-emerald-100 text-emerald-600 text-[9px] font-black uppercase tracking-widest rounded-md mb-4 border border-emerald-200">
                        Pengajar Pengganti
                    </span>
                    
                    <div class="mb-4">
                        <label class="block text-[11px] font-bold text-emerald-600/70 uppercase tracking-wider mb-1.5">Pilih Guru Baru</label>
                        <select name="guru_baru_id" required class="w-full bg-white border border-emerald-200 text-slate-700 text-sm font-bold rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 block p-3 outline-none transition-all shadow-sm cursor-pointer">
                            <option value="">-- Pilih Guru --</option>
                            @foreach($semuaGuru as $g)
                                <option value="{{ $g->id }}">{{ $g->nama_guru }}</option>
                            @endforeach
                        </select>
                    </div>

                    <p class="text-xs font-medium text-emerald-700/80 leading-relaxed bg-white/60 p-3 rounded-xl border border-emerald-100">
                        <i class="fas fa-check-circle mr-1"></i> Setelah klik simpan, seluruh jadwal harian sebelumnya akan dikosongkan. Anda dapat mengatur ulang jadwal untuk guru baru ini.
                    </p>
                </div>

            </div>
        </div>

        <div class="bg-slate-50 border-t border-slate-100 p-6 flex items-center justify-end">
            <button type="button" onclick="history.back()" class="px-6 py-2.5 bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 font-bold text-sm rounded-xl mr-3 transition-colors">
                Batal
            </button>
            <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 active:scale-95 text-white font-bold text-sm rounded-xl transition-all shadow-[0_4px_15px_-3px_rgba(79,70,229,0.4)] flex items-center">
                <i class="fas fa-save mr-2"></i> Mutasi Massal Sekarang
            </button>
        </div>
    </form>
</div>
@endsection