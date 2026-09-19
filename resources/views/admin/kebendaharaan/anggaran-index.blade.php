@extends('layouts.app')

@section('title', 'Anggaran (RAB)')

@section('content')
<div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
    <div>
        <h2 class="text-2xl font-black text-slate-800 tracking-tight flex items-center">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600 mr-3 shadow-inner">
                <i class="fas fa-calculator"></i>
            </div>
            Anggaran (RAB)
        </h2>
        <p class="text-sm font-bold text-slate-400 mt-1 ml-14">
            Susun Rencana Anggaran &amp; Biaya per periode tahun ajaran sebelum kegiatan berjalan.
        </p>
    </div>
    @can('akses_anggaran')
    <div class="w-full sm:w-auto flex flex-col sm:flex-row gap-2 sm:gap-3">
        <a href="#import" class="inline-flex items-center justify-center px-5 py-3 bg-white text-emerald-700 hover:bg-emerald-50 border border-emerald-300 border-dashed font-bold text-sm rounded-xl transition-all">
            <i class="fas fa-cloud-arrow-up mr-2"></i> Upload RAB (.xlsx)
        </a>
        <a href="{{ route('kebendaharaan.anggaran.create') }}" class="inline-flex items-center justify-center px-5 py-3 bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white font-bold text-sm rounded-xl transition-all shadow-[0_4px_15px_-3px_rgba(16,185,129,0.4)]">
            <i class="fas fa-plus mr-2"></i> Buat Anggaran Baru
        </a>
    </div>
    @endcan
</div>

<div id="import" class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden mb-6">
    <div class="bg-slate-50 border-b border-slate-100 p-4">
        <h3 class="text-sm font-black text-slate-700 uppercase tracking-widest"><i class="fas fa-cloud-arrow-up text-emerald-500 mr-2"></i>Import RAB dari Excel</h3>
        <p class="text-[11px] font-bold text-slate-400 mt-1">
            Unggah file RAB (.xlsx/.xls) dengan kolom POS | URAIAN | Vol | Ket | Vol | Ket | Satuan | Jumlah Harga | ... bulan JULI &hellip; JUNI (+ bagian PEMASUKAN).
        </p>
    </div>
    <form action="{{ route('kebendaharaan.anggaran.import') }}" method="POST" enctype="multipart/form-data" class="p-5 flex flex-col sm:flex-row items-stretch sm:items-center gap-4">
        @csrf
        <div class="flex-1">
            <input type="file" name="file" accept=".xlsx,.xls" required
                   class="block w-full text-sm text-slate-600 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-black file:bg-emerald-600 file:text-white hover:file:bg-emerald-700 cursor-pointer">
            <p class="text-[10px] font-bold text-slate-400 mt-1.5">
                Membuat/mengganti anggaran periode aktif (hanya bila status Draft). Alokasi bulanan per pos ikut terisi.
            </p>
        </div>
        <button type="submit" class="inline-flex items-center justify-center px-6 py-3 bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white font-bold text-sm rounded-xl transition-all shrink-0">
            <i class="fas fa-upload mr-2"></i> Impor Sekarang
        </button>
    </form>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="bg-slate-50 border-b border-slate-100 p-4 flex justify-between items-center">
        <h3 class="text-sm font-black text-slate-700 uppercase tracking-widest"><i class="fas fa-list text-emerald-500 mr-2"></i>Daftar Anggaran</h3>
        <span class="bg-emerald-100 text-emerald-700 text-[10px] font-black px-2 py-1 rounded-md">{{ $list->count() }} Baris</span>
    </div>
    <div class="p-0">
        @forelse($list as $ag)
        @php
            $aktif = $periodeAktif && $ag->periode_id === $periodeAktif->id;
            $status = $ag->status === 'final' ? ['Final', 'bg-sky-100 text-sky-700 border-sky-200', 'fa-lock'] : ['Draft', 'bg-amber-100 text-amber-700 border-amber-200', 'fa-pen'];
        @endphp
        <div class="p-5 border-b border-slate-100 hover:bg-slate-50/50 transition-colors flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 bg-emerald-50 rounded-xl flex flex-col items-center justify-center border border-emerald-100 shrink-0">
                    <i class="fas fa-file-invoice-dollar text-emerald-600 text-xl"></i>
                </div>
                <div>
                    <h4 class="text-base font-black text-slate-800 flex items-center gap-2">
                        {{ $ag->nama }}
                        @if($aktif)
                        <span class="text-[9px] font-black px-2 py-0.5 rounded-md bg-emerald-100 text-emerald-700 border border-emerald-200"><i class="fas fa-star-of-life mr-1"></i>PERIODE AKTIF</span>
                        @endif
                    </h4>
                    <div class="flex flex-wrap items-center gap-3 mt-1.5">
                        <span class="text-[10px] font-black px-2 py-1 rounded-md border {{ $status[1] }}">
                            <i class="fas {{ $status[2] }} mr-1"></i>{{ $status[0] }}
                        </span>
                        @if($ag->periode)
                        <span class="text-[11px] font-bold text-slate-500"><i class="fas fa-calendar-week text-emerald-400 mr-1.5"></i>{{ $ag->periode->tahun_ajaran }}</span>
                        @endif
                        <span class="text-[11px] font-bold text-slate-500"><i class="fas fa-layer-group text-emerald-400 mr-1.5"></i>{{ $ag->kelompok->count() }} Kelompok</span>
                        <span class="text-[11px] font-black text-emerald-600"><i class="fas fa-coins mr-1.5"></i>Rp {{ number_format($ag->totalPagu(), 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
            <div class="shrink-0 w-full sm:w-auto flex flex-col sm:flex-row gap-2">
                <a href="{{ route('kebendaharaan.anggaran.show', $ag->id) }}" class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2.5 bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white border border-indigo-200 hover:border-indigo-600 font-bold text-xs rounded-xl transition-all">
                    <i class="fas fa-edit mr-2"></i> Kelola
                </a>
                <a href="{{ route('kebendaharaan.anggaran.pdf', $ag->id) }}" class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2.5 bg-white text-slate-600 hover:bg-slate-600 hover:text-white border border-slate-200 font-bold text-xs rounded-xl transition-all">
                    <i class="fas fa-file-pdf mr-2"></i> PDF
                </a>
            </div>
        </div>
        @empty
        <div class="p-10 flex flex-col items-center justify-center text-center">
            <div class="w-16 h-16 bg-slate-50 text-slate-300 rounded-full flex items-center justify-center mb-4 text-3xl shadow-inner"><i class="fas fa-calculator"></i></div>
            <h4 class="text-sm font-black text-slate-700">Belum Ada Anggaran</h4>
            <p class="text-xs font-medium text-slate-400 mt-1">Klik "Buat Anggaran Baru" untuk menyusun RAB.</p>
        </div>
        @endforelse
    </div>
</div>
@endsection