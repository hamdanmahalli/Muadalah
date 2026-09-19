@extends('layouts.app')

@section('title', 'Detail Pembelian')

@section('content')
@php $fmt = fn($v) => 'Rp ' . number_format($v, 0, ',', '.'); @endphp

<div class="mb-6">
    <a href="{{ route('kebendaharaan.toko.pembelian.index') }}" class="text-xs font-black text-slate-500 hover:text-emerald-600 mb-3 inline-flex items-center">
        <i class="fas fa-arrow-left mr-2"></i> Riwayat Pembelian
    </a>
    <h2 class="text-2xl font-black text-slate-800 tracking-tight flex items-center">
        <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600 mr-3 shadow-inner">
            <i class="fas fa-truck"></i>
        </div>
        {{ $pb->kode }}
    </h2>
    <p class="text-sm font-bold text-slate-400 mt-1 ml-14">{{ $pb->tanggal->format('d/m/Y') }} · Sumber: {{ $pb->pencairan?->kode ?? 'Dana bebas' }}</p>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="bg-slate-50 border-b border-slate-100 p-4">
        <h3 class="text-sm font-black text-slate-700 uppercase tracking-widest"><i class="fas fa-cart-plus text-emerald-500 mr-2"></i>Item Pembelian</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50/80 text-[10px] font-black text-slate-400 uppercase tracking-widest">
                    <th class="text-left px-4 py-3">Barang</th>
                    <th class="text-right px-4 py-3">Qty</th>
                    <th class="text-right px-4 py-3">Harga Beli</th>
                    <th class="text-right px-4 py-3">Harga Jual Acuan</th>
                    <th class="text-right px-4 py-3">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pb->items as $it)
                <tr class="border-t border-slate-100">
                    <td class="px-4 py-3 font-semibold text-slate-700">{{ $it->barang?->nama }} <span class="text-[10px] text-slate-400">({{ $it->barang?->kode }})</span></td>
                    <td class="px-4 py-3 text-right font-bold text-slate-800">{{ rtrim(rtrim(number_format($it->qty, 2), '0'), '.') }}</td>
                    <td class="px-4 py-3 text-right font-semibold text-slate-600">{{ $fmt($it->harga_beli) }}</td>
                    <td class="px-4 py-3 text-right font-semibold text-emerald-600">{{ $fmt($it->harga_jual) }}</td>
                    <td class="px-4 py-3 text-right font-black text-slate-800">{{ $fmt($it->qty * $it->harga_beli) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="border-t-2 border-slate-200 bg-slate-50">
                    <td colspan="4" class="px-4 py-3 text-right font-black text-slate-800 uppercase">Total</td>
                    <td class="px-4 py-3 text-right font-black text-emerald-700">{{ $fmt($pb->total) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection