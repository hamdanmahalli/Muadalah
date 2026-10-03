@extends('layouts.app')

@section('title', 'Kas per Pemegang')

@section('content')
@php
$labelBulan = $labelBulan ?? fn($b) => bulan_fiskal_label($b);
$fmt = fn($n) => 'Rp ' . number_format((float) $n, 0, ',', '.');
@endphp

<div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
    <div>
        <h2 class="text-2xl font-black text-slate-800 tracking-tight flex items-center">
            <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600 mr-3 shadow-inner">
                <i class="fas fa-wallet"></i>
            </div>
            Kas per Pemegang
        </h2>
        <p class="text-sm font-bold text-slate-400 mt-1 ml-14">
            Uang yang dipegang tiap orang: masuk dari SPP dibayar &amp; pemasukan, keluar dari belanja.
            Atribusi mengikuti pengaju SPP / pencatat. Periode {{ $periode->tahun_ajaran }} &middot; {{ ucfirst($periode->semester) }}.
        </p>
    </div>

    <form method="GET" action="{{ route('kebendaharaan.kas-pemegang') }}" class="flex items-center gap-2">
        <select name="bulan" class="bg-white border border-slate-200 text-slate-700 text-xs font-bold rounded-xl px-3 py-2.5 outline-none cursor-pointer">
            <option value="">Semua Bulan</option>
            @foreach($bulanList as $b => $lb)
            <option value="{{ $b }}" {{ ($bulan ?? null) === $b ? 'selected' : '' }}>{{ $labelBulan($b) }}</option>
            @endforeach
        </select>
        <input type="number" name="tahun" value="{{ $tahun }}" min="2015" max="2100"
               class="bg-white border border-slate-200 text-slate-700 text-xs font-bold rounded-xl px-3 py-2.5 outline-none w-24 text-center">
        <button type="submit" class="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs transition-all shadow-sm">
            <i class="fas fa-filter mr-1.5"></i> Tampilkan
        </button>
    </form>
</div>

<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-2xl border border-slate-100 p-5 shadow-sm">
        <p class="text-[11px] font-black text-slate-400 uppercase tracking-wider">Total Uang Masuk</p>
        <p class="text-2xl font-black text-emerald-600 mt-1.5">{{ $fmt($total['masuk']) }}</p>
    </div>
    <div class="bg-white rounded-2xl border border-slate-100 p-5 shadow-sm">
        <p class="text-[11px] font-black text-slate-400 uppercase tracking-wider">Total Uang Keluar</p>
        <p class="text-2xl font-black text-rose-600 mt-1.5">{{ $fmt($total['keluar']) }}</p>
    </div>
    <div class="bg-white rounded-2xl border border-slate-100 p-5 shadow-sm">
        <p class="text-[11px] font-black text-slate-400 uppercase tracking-wider">Total Saldo Dipegang</p>
        <p class="text-2xl font-black {{ $total['saldo'] < 0 ? 'text-rose-600' : 'text-slate-800' }} mt-1.5">{{ $fmt($total['saldo']) }}</p>
        <p class="text-[11px] font-bold text-slate-400 mt-1">Harus sama dengan saldo buku kas periode yang dipilih.</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
    @forelse($pemegang as $pg)
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="flex items-center justify-between p-5 pb-4">
            <div class="flex items-center gap-3 min-w-0">
                <div class="w-11 h-11 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600 font-black text-sm flex-shrink-0">
                    {{ strtoupper(substr($pg['user']->name, 0, 2)) }}
                </div>
                <div class="min-w-0">
                    <p class="font-black text-slate-800 text-sm truncate">{{ $pg['user']->name }}</p>
                    <p class="text-[11px] font-bold text-slate-400">{{ $pg['user']->jabatan ?? ($pg['user']->roles->first()->name ?? 'Anggota') }}</p>
                </div>
            </div>
            <span class="inline-flex items-center gap-1.5 text-[11px] font-black px-2.5 py-1.5 rounded-lg border whitespace-nowrap
                {{ $pg['saldo'] < 0 ? 'bg-rose-100 text-rose-700 border-rose-200' : 'bg-emerald-100 text-emerald-700 border-emerald-200' }}">
                <i class="fas {{ $pg['saldo'] < 0 ? 'fa-triangle-exclamation' : 'fa-wallet' }}"></i>
                {{ $fmt($pg['saldo']) }}
            </span>
        </div>
        <div class="grid grid-cols-2 gap-px bg-slate-100 border-y border-slate-100 text-center">
            <div class="bg-white py-2.5">
                <span class="text-[10px] font-black text-slate-400 uppercase tracking-wider">Masuk</span>
                <span class="block text-xs font-black text-emerald-600 mt-0.5">{{ $fmt($pg['masuk']) }}</span>
            </div>
            <div class="bg-white py-2.5">
                <span class="text-[10px] font-black text-slate-400 uppercase tracking-wider">Keluar</span>
                <span class="block text-xs font-black text-rose-600 mt-0.5">{{ $fmt($pg['keluar']) }}</span>
            </div>
        </div>
        @if($pg['rincian']->isNotEmpty())
        <details class="group">
            <summary class="flex items-center gap-2 px-5 py-3 cursor-pointer text-[11px] font-black text-slate-500 hover:text-indigo-600 transition-colors">
                <i class="fas fa-chevron-down group-open:rotate-180 transition-transform"></i>
                Rincian ({{ $pg['rincian']->count() }} transaksi)
            </summary>
            <div class="px-5 pb-4 space-y-2">
                @foreach($pg['rincian'] as $r)
                <div class="flex items-center justify-between gap-3 border-b border-slate-50 pb-2 last:border-0">
                    <div class="min-w-0">
                        <p class="text-xs font-bold text-slate-700 truncate">
                            <span class="text-slate-400 font-black mr-1">{{ $r['tanggal'] }}</span>
                            <span class="inline-flex items-center gap-1 text-[10px] font-black px-1.5 py-0.5 rounded {{ $r['sumber'] === 'SPP' ? 'bg-sky-100 text-sky-700' : ($r['sumber'] === 'Pemasukan' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700') }}">
                                <i class="fas {{ $r['sumber'] === 'SPP' ? 'fa-hand-holding-dollar' : ($r['sumber'] === 'Pemasukan' ? 'fa-cash-register' : 'fa-cart-shopping') }}"></i>
                                {{ $r['sumber'] }}
                            </span>
                        </p>
                        <p class="text-[11px] font-semibold text-slate-400 truncate">{{ $r['uraian'] }}{{ $r['kode'] ? ' · ' . $r['kode'] : '' }}</p>
                    </div>
                    <div class="text-right flex-shrink-0">
                        <p class="text-xs font-black {{ $r['masuk'] ? 'text-emerald-600' : 'text-rose-600' }}">{{ $r['masuk'] ? '+' : '−' }}{{ $fmt($r['masuk'] ?: $r['keluar']) }}</p>
                        <p class="text-[10px] font-bold text-slate-400">{{ $fmt($r['saldo']) }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </details>
        @else
        <p class="px-5 py-3 text-[11px] font-bold text-slate-300">Tidak ada transaksi pada rentang ini.</p>
        @endif
    </div>
    @empty
    <div class="col-span-full bg-white rounded-2xl border border-slate-100 p-10 text-center">
        <i class="fas fa-wallet text-3xl text-slate-200"></i>
        <p class="text-sm font-bold text-slate-400 mt-3">Belum ada pemegang kas terdaftar.</p>
    </div>
    @endforelse
</div>
@endsection