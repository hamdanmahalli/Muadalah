@extends('layouts.app')

@section('title', 'Kas Umum / Buku Besar')

@section('content')
@php
$labelBulan = $labelBulan ?? fn($b) => bulan_fiskal_label($b);
$fmt = fn($n) => 'Rp ' . number_format((float) $n, 0, ',', '.');
@endphp

<div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
    <div>
        <h2 class="text-2xl font-black text-slate-800 tracking-tight flex items-center">
            <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center text-amber-600 mr-3 shadow-inner">
                <i class="fas fa-book-open"></i>
            </div>
            Kas Umum / Buku Besar
        </h2>
        <p class="text-sm font-bold text-slate-400 mt-1 ml-14">
            Buku kas bendahara: seluruh pemasukan yang dilaporkan &amp; dicairkan dalam satu catatan
            berurutan. Masuk = pemasukan dana (termasuk pengembalian sisa panjar); keluar = pencairan
            (SPP &amp; modal toko). Sisa panjar yang tidak terpakai kembali otomatis ke kas bendahara
            saat laporan disahkan pimpinan.
            Periode {{ $periode->tahun_ajaran }} &middot; {{ ucfirst($periode->semester) }}.
        </p>
    </div>

    <form method="GET" action="{{ route('kebendaharaan.kas-umum') }}" class="flex items-center gap-2">
        <select name="bulan" class="bg-white border border-slate-200 text-slate-700 text-xs font-bold rounded-xl px-3 py-2.5 outline-none cursor-pointer">
            <option value="">Semua Bulan</option>
            @foreach($bulanList as $b => $lb)
            <option value="{{ $b }}" {{ ($bulan ?? null) === $b ? 'selected' : '' }}>{{ $labelBulan($b) }}</option>
            @endforeach
        </select>
        <input type="number" name="tahun" value="{{ $tahun }}" min="2015" max="2100"
               class="bg-white border border-slate-200 text-slate-700 text-xs font-bold rounded-xl px-3 py-2.5 outline-none w-24 text-center">
        <button type="submit" class="px-4 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs transition-all shadow-sm">
            <i class="fas fa-filter mr-1.5"></i> Tampilkan
        </button>
    </form>
</div>

<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-2xl border border-slate-100 p-5 shadow-sm">
        <p class="text-[11px] font-black text-slate-400 uppercase tracking-wider">Total Pemasukan</p>
        <p class="text-2xl font-black text-emerald-600 mt-1.5">{{ $fmt($total['masuk']) }}</p>
    </div>
    <div class="bg-white rounded-2xl border border-slate-100 p-5 shadow-sm">
        <p class="text-[11px] font-black text-slate-400 uppercase tracking-wider">Total Pencairan</p>
        <p class="text-2xl font-black text-rose-600 mt-1.5">{{ $fmt($total['keluar']) }}</p>
    </div>
    <div class="bg-white rounded-2xl border border-slate-100 p-5 shadow-sm">
        <p class="text-[11px] font-black text-slate-400 uppercase tracking-wider">Total Saldo</p>
        <p class="text-2xl font-black {{ $total['saldo'] < 0 ? 'text-rose-600' : 'text-slate-800' }} mt-1.5">{{ $fmt($total['saldo']) }}</p>
        <p class="text-[11px] font-bold text-slate-400 mt-1">Posisi kas bendahara saat ini (pemasukan − pencairan).</p>
    </div>
</div>

@if($baris->isEmpty())
<div class="bg-white rounded-2xl border border-slate-100 p-10 text-center shadow-sm">
    <i class="fas fa-book-open text-3xl text-slate-200"></i>
    <p class="text-sm font-bold text-slate-400 mt-3">Belum ada transaksi pada rentang ini.</p>
</div>
@else
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50 text-[10px] font-black uppercase tracking-wider text-slate-400 text-left">
                    <th class="px-4 py-3">No</th>
                    <th class="px-4 py-3">Tanggal</th>
                    <th class="px-4 py-3">Sumber</th>
                    <th class="px-4 py-3">Kode</th>
                    <th class="px-4 py-3">Uraian</th>
                    <th class="px-4 py-3">Pihak</th>
                    <th class="px-4 py-3 text-right">Masuk</th>
                    <th class="px-4 py-3 text-right">Keluar</th>
                    <th class="px-4 py-3 text-right">Saldo Berjalan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($baris as $i => $r)
                <tr class="hover:bg-slate-50/60 transition-colors">
                    <td class="px-4 py-3 text-[11px] font-bold text-slate-300">{{ $i + 1 }}</td>
                    <td class="px-4 py-3 text-xs font-black text-slate-600 whitespace-nowrap">{{ $r['tanggal'] }}</td>
                    <td class="px-4 py-3">
                        <span class="inline-flex items-center gap-1 text-[10px] font-black px-2 py-1 rounded-lg
                            {{ $r['sumber'] === 'Pemasukan' ? 'bg-emerald-100 text-emerald-700' : ($r['sumber'] === 'Pengembalian' ? 'bg-indigo-100 text-indigo-700' : 'bg-sky-100 text-sky-700') }}">
                            <i class="fas {{ $r['sumber'] === 'Pemasukan' ? 'fa-cash-register' : ($r['sumber'] === 'Pengembalian' ? 'fa-rotate-left' : 'fa-hand-holding-dollar') }}"></i>
                            {{ $r['sumber'] }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-[11px] font-bold text-slate-500 whitespace-nowrap">{{ $r['kode'] ?? '—' }}</td>
                    <td class="px-4 py-3 text-xs font-semibold text-slate-600">{{ $r['uraian'] }}</td>
                    <td class="px-4 py-3 text-[11px] font-bold text-slate-500 whitespace-nowrap">{{ $r['pihak_nama'] }}</td>
                    <td class="px-4 py-3 text-right text-xs font-black text-emerald-600 whitespace-nowrap">{{ $r['masuk'] ? '+' . $fmt($r['masuk']) : '—' }}</td>
                    <td class="px-4 py-3 text-right text-xs font-black text-rose-600 whitespace-nowrap">{{ $r['keluar'] ? '−' . $fmt($r['keluar']) : '—' }}</td>
                    <td class="px-4 py-3 text-right text-xs font-black text-slate-800 whitespace-nowrap">{{ $fmt($r['saldo']) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="bg-slate-50 text-[11px] font-black text-slate-700">
                    <td colspan="5" class="px-4 py-3 text-right uppercase tracking-wider">Total</td>
                    <td class="px-4 py-3 text-right text-emerald-600 whitespace-nowrap">+{{ $fmt($total['masuk']) }}</td>
                    <td class="px-4 py-3 text-right text-rose-600 whitespace-nowrap">−{{ $fmt($total['keluar']) }}</td>
                    <td class="px-4 py-3 text-right text-slate-800 whitespace-nowrap">{{ $fmt($total['saldo']) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
    <p class="px-4 py-3 border-t border-slate-100 text-[11px] font-bold text-slate-400">
        Saldo berjalan = posisi kas bendahara kumulatif setelah tiap baris (masuk ditambah, keluar dikurangi),
        diurutkan per tanggal. Bisa negatif saat total pencairan melebihi total pemasukan sampai titik itu
        (uang keluar lebih dulu dari dana pemasukan yang menyusul).
    </p>
</div>
@endif
@endsection