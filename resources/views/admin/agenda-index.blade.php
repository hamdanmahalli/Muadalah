@extends('layouts.app') <!-- Sesuaikan dengan nama layout admin Anda -->

@section('title', 'Kelola Agenda Kegiatan')

@section('content')
<div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
    <div>
        <h2 class="text-2xl font-black text-slate-800 tracking-tight flex items-center">
            <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600 mr-3 shadow-inner">
                <i class="fas fa-calendar-check"></i>
            </div>
            Agenda & Kehadiran Acara
        </h2>
        <p class="text-sm font-bold text-slate-400 mt-1 ml-14">
            Buat jadwal rapat, pembinaan, atau acara khusus lainnya.
        </p>
    </div>
    <div class="bg-white px-4 py-2 rounded-xl border border-slate-200 text-sm font-bold shadow-sm text-slate-600">
        <i class="fas fa-clock text-emerald-500 mr-1.5"></i> TA: {{ $periodeAktif->tahun_ajaran }}
    </div>
</div>

@if(session('sukses'))
<div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl mb-6 flex items-center shadow-sm">
    <i class="fas fa-check-circle text-xl mr-3"></i>
    <span class="font-bold text-sm">{{ session('sukses') }}</span>
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    
    <!-- KOLOM KIRI: FORM TAMBAH AGENDA -->
    <div class="lg:col-span-1">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden sticky top-6">
            <div class="bg-slate-50 border-b border-slate-100 p-4">
                <h3 class="text-sm font-black text-slate-700 uppercase tracking-widest"><i class="fas fa-plus-circle text-indigo-500 mr-2"></i>Buat Agenda Baru</h3>
            </div>
            <form action="/agenda-kegiatan" method="POST" class="p-5 space-y-4">
                @csrf
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Nama Kegiatan</label>
                    <input type="text" name="nama_kegiatan" required placeholder="Contoh: Rapat Evaluasi Bulanan" class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 block p-3 outline-none transition-all font-medium">
                </div>
                
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Tanggal Pelaksanaan</label>
                    <input type="date" name="tanggal" required value="{{ date('Y-m-d') }}" class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 block p-3 outline-none transition-all font-medium cursor-pointer">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Jam Mulai</label>
                        <input type="time" name="jam_mulai" required class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 block p-3 outline-none transition-all font-medium cursor-pointer">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Jam Selesai</label>
                        <input type="time" name="jam_selesai" class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 block p-3 outline-none transition-all font-medium cursor-pointer">
                    </div>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Lokasi (Opsional)</label>
                    <input type="text" name="lokasi" placeholder="Contoh: Aula Utama" class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 block p-3 outline-none transition-all font-medium">
                </div>

                <button type="submit" class="w-full mt-2 bg-indigo-600 hover:bg-indigo-700 active:scale-95 text-white font-bold py-3.5 px-4 rounded-xl transition-all shadow-[0_4px_15px_-3px_rgba(79,70,229,0.4)] flex items-center justify-center">
                    <i class="fas fa-save mr-2"></i> Simpan & Buat QR Code
                </button>
            </form>
        </div>
    </div>

    <!-- KOLOM KANAN: DAFTAR AGENDA -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="bg-slate-50 border-b border-slate-100 p-4 flex justify-between items-center">
                <h3 class="text-sm font-black text-slate-700 uppercase tracking-widest"><i class="fas fa-list text-emerald-500 mr-2"></i>Daftar Agenda Semester Ini</h3>
                <span class="bg-emerald-100 text-emerald-700 text-[10px] font-black px-2 py-1 rounded-md">{{ count($agendas) }} Acara</span>
            </div>
            
            <div class="p-0">
                @forelse($agendas as $agenda)
                    <div class="p-5 border-b border-slate-100 hover:bg-slate-50/50 transition-colors flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                        <div class="flex items-start gap-4">
                            <!-- Ikon Kalender -->
                            <div class="w-12 h-12 bg-slate-100 rounded-xl flex flex-col items-center justify-center border border-slate-200 shrink-0">
                                <span class="text-[9px] font-bold text-slate-400 uppercase leading-none mb-1">{{ \Carbon\Carbon::parse($agenda->tanggal)->translatedFormat('M') }}</span>
                                <span class="text-lg font-black text-slate-700 leading-none">{{ \Carbon\Carbon::parse($agenda->tanggal)->format('d') }}</span>
                            </div>
                            
                            <!-- Info Utama -->
                            <div>
                                <h4 class="text-base font-black text-slate-800">{{ $agenda->nama_kegiatan }}</h4>
                                <div class="flex flex-wrap items-center gap-3 mt-1.5">
                                    <span class="text-[11px] font-bold text-slate-500 flex items-center">
                                        <i class="fas fa-clock text-indigo-400 mr-1.5"></i> 
                                        {{ \Carbon\Carbon::parse($agenda->jam_mulai)->format('H:i') }} - {{ $agenda->jam_selesai ? \Carbon\Carbon::parse($agenda->jam_selesai)->format('H:i') : 'Selesai' }}
                                    </span>
                                    @if($agenda->lokasi)
                                    <span class="text-[11px] font-bold text-slate-500 flex items-center">
                                        <i class="fas fa-map-marker-alt text-rose-400 mr-1.5"></i> {{ $agenda->lokasi }}
                                    </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        <!-- Area Tombol Aksi -->
                        <div class="shrink-0 w-full sm:w-auto flex flex-col sm:flex-row gap-2 mt-3 sm:mt-0">
                            <!-- Tombol Laporan -->
                            <a href="/agenda-kegiatan/{{ $agenda->id }}/laporan" class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2.5 bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white border border-indigo-200 hover:border-indigo-600 font-bold text-xs rounded-xl transition-all">
                                <i class="fas fa-chart-pie mr-2"></i> Laporan
                            </a>
                            <!-- Tombol Buka Proyektor -->
                            <a href="/agenda-kegiatan/{{ $agenda->id }}/proyektor" target="_blank" class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2.5 bg-emerald-50 text-emerald-600 hover:bg-emerald-500 hover:text-white border border-emerald-200 hover:border-emerald-500 font-bold text-xs rounded-xl transition-all">
                                <i class="fas fa-expand-arrows-alt mr-2"></i> Layar QR
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="p-10 flex flex-col items-center justify-center text-center">
                        <div class="w-16 h-16 bg-slate-50 text-slate-300 rounded-full flex items-center justify-center mb-4 text-3xl shadow-inner"><i class="fas fa-box-open"></i></div>
                        <h4 class="text-sm font-black text-slate-700">Belum Ada Agenda</h4>
                        <p class="text-xs font-medium text-slate-400 mt-1">Silakan buat agenda acara baru melalui form di sebelah kiri.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
    
</div>
@endsection