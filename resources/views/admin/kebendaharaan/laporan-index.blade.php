@extends('layouts.app')

@section('title', 'Laporan Pertanggung Jawaban')

@section('content')
@php
$warnaStatus = [
    'diajukan' => 'bg-amber-100 text-amber-700 border-amber-200',
    'disetujui' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
    'ditolak' => 'bg-rose-100 text-rose-700 border-rose-200',
];
$labelStatus = ['diajukan' => 'Diajukan', 'disetujui' => 'Disetujui', 'ditolak' => 'Ditolak'];
$ikonStatus = ['diajukan' => 'fa-clock', 'disetujui' => 'fa-circle-check', 'ditolak' => 'fa-circle-xmark'];
@endphp

<div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
    <div>
        <h2 class="text-2xl font-black text-slate-800 tracking-tight flex items-center">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600 mr-3 shadow-inner">
                <i class="fas fa-receipt"></i>
            </div>
            Laporan Pertanggung Jawaban (LPJ)
        </h2>
        <p class="text-sm font-bold text-slate-400 mt-1 ml-14">
            Sumber realisasi pengeluaran. LPJ yang divalidasi bendahara otomatis masuk rekap belanja per pos.
        </p>
    </div>
    @can('akses_laporan_kebendaharaan')
    <a href="{{ route('kebendaharaan.laporan.create') }}" class="inline-flex items-center px-5 py-3 bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white font-bold text-sm rounded-xl transition-all shadow-[0_4px_15px_-3px_rgba(16,185,129,0.4)]">
        <i class="fas fa-plus mr-2"></i> Buat Laporan
    </a>
    @endcan
</div>

@if(session('sukses'))
<div class="mb-4 flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl px-4 py-3 text-sm font-bold">
    <i class="fas fa-circle-check"></i> {{ session('sukses') }}
</div>
@endif
@if(session('error'))
<div class="mb-4 flex items-center gap-3 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl px-4 py-3 text-sm font-bold">
    <i class="fas fa-triangle-exclamation"></i> {{ session('error') }}
</div>
@endif
@if(session('warning'))
<div class="mb-4 bg-amber-50 border border-amber-200 text-amber-800 rounded-xl px-4 py-3 text-sm font-bold">
    <div class="flex items-center gap-3"><i class="fas fa-triangle-exclamation"></i> {{ session('warning') }}</div>
    @if(session('warning_detail'))
    <ul class="mt-2 ml-7 list-disc space-y-1 text-xs font-semibold">
        @foreach(session('warning_detail') as $w)
        <li>{{ $w }}</li>
        @endforeach
    </ul>
    @endif
</div>
@endif

<div class="mb-4 flex flex-wrap items-center gap-2">
    <a href="{{ route('kebendaharaan.laporan.index') }}" class="text-xs font-black px-3 py-1.5 rounded-lg border {{ !request('status') ? 'bg-emerald-600 text-white border-emerald-600' : 'bg-white text-slate-500 border-slate-200 hover:border-emerald-300' }}">Semua</a>
    @foreach(['diajukan', 'disetujui', 'ditolak'] as $st)
    <a href="{{ route('kebendaharaan.laporan.index', ['status' => $st]) }}" class="text-xs font-black px-3 py-1.5 rounded-lg border {{ request('status') === $st ? 'bg-emerald-600 text-white border-emerald-600' : 'bg-white text-slate-500 border-slate-200 hover:border-emerald-300' }}">{{ $labelStatus[$st] }}</a>
    @endforeach
</div>

<div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="bg-slate-50 border-b border-slate-100 p-4 flex justify-between items-center">
        <h3 class="text-sm font-black text-slate-700 uppercase tracking-widest"><i class="fas fa-list text-emerald-500 mr-2"></i>Daftar Laporan Pertanggung Jawaban</h3>
        <span class="bg-emerald-100 text-emerald-700 text-[10px] font-black px-2 py-1 rounded-md">{{ $list->count() }} Laporan</span>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50/80 text-[10px] font-black text-slate-400 uppercase tracking-widest">
                    <th class="text-left px-4 py-3">Kode</th>
                    <th class="text-left px-4 py-3">Keterangan / Pos</th>
                    <th class="text-left px-4 py-3">Panjar (SPP)</th>
                    <th class="text-left px-4 py-3">Tanggal</th>
                    <th class="text-right px-4 py-3">Nominal</th>
                    <th class="text-center px-4 py-3">Status</th>
                    <th class="text-center px-4 py-3">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($list as $lp)
                <tr class="border-t border-slate-100 align-top">
                    <td class="px-4 py-3 font-black text-slate-800">{{ $lp->kode }}</td>
                    <td class="px-4 py-3">
                        @if($lp->keterangan)
                        <p class="text-xs font-semibold text-slate-400 mb-1">{{ $lp->keterangan }}</p>
                        @endif
                        @forelse($lp->items as $it)
                        <p class="text-sm font-semibold text-slate-700"><span class="text-[10px] font-black text-emerald-600 bg-emerald-50 border border-emerald-100 rounded px-1.5 py-0.5 mr-1">{{ $it->pos?->kode ?? '?' }}</span>{{ $it->uraian ?? $it->pos?->uraian }} <span class="text-xs font-bold text-slate-400">Rp {{ number_format($it->nominal, 0, ',', '.') }}</span></p>
                        @empty
                        <p class="text-sm font-semibold text-slate-400">—</p>
                        @endforelse
                    </td>
                    <td class="px-4 py-3 font-semibold text-slate-600">{{ $lp->pencairan?->kode ?? '—' }}</td>
                    <td class="px-4 py-3 font-semibold text-slate-600">{{ $lp->tanggal->format('d/m/Y') }}</td>
                    <td class="px-4 py-3 text-right font-black text-slate-800">Rp {{ number_format($lp->nominal, 0, ',', '.') }}</td>
                    <td class="px-4 py-3 text-center">
                        <span class="inline-block text-[10px] font-black px-2 py-1 rounded-md border {{ $warnaStatus[$lp->status] }}">
                            <i class="fas {{ $ikonStatus[$lp->status] }} mr-1"></i>{{ $labelStatus[$lp->status] }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex flex-col items-center gap-1.5">
                            @can('akses_validasi_laporan')
                            @if($lp->status === 'diajukan')
                            <form action="{{ route('kebendaharaan.laporan.validasi', $lp->id) }}" method="POST" onsubmit="return confirm('Setujui LPJ ini? Jumlahnya akan menjadi realisasi pengeluaran.')">
                                @csrf
                                <button type="submit" class="inline-flex items-center justify-center px-3 py-1.5 bg-emerald-50 text-emerald-600 hover:bg-emerald-600 hover:text-white border border-emerald-200 font-bold text-[10px] rounded-lg transition-all">
                                    <i class="fas fa-check mr-1"></i> Validasi
                                </button>
                            </form>
                            <form action="{{ route('kebendaharaan.laporan.tolak', $lp->id) }}" method="POST" onsubmit="var a=prompt('Alasan penolakan:'); if(!a||!a.trim()){alert('Tulis alasan penolakan terlebih dahulu');return false;} this.elements['keterangan'].value=a;">
                                @csrf
                                <input type="hidden" name="keterangan">
                                <button type="submit" class="inline-flex items-center justify-center px-3 py-1.5 bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white border border-rose-200 font-bold text-[10px] rounded-lg transition-all">
                                    <i class="fas fa-xmark mr-1"></i> Tolak
                                </button>
                            </form>
                            @endif
                            @endcan
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-4 py-10 text-center">
                        <div class="w-16 h-16 bg-slate-50 text-slate-300 rounded-full flex items-center justify-center mx-auto mb-4 text-3xl shadow-inner"><i class="fas fa-receipt"></i></div>
                        <p class="text-sm font-black text-slate-700">Belum Ada Laporan Pertanggung Jawaban</p>
                        <p class="text-xs font-medium text-slate-400 mt-1">Staf bendahara melaporkan pengeluaran; bendahara memvalidasinya.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection