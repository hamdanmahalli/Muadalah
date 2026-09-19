@extends('layouts.app')

@section('title', 'Pembelian Barang')

@section('content')
@php $fmt = fn($v) => 'Rp ' . number_format($v, 0, ',', '.'); @endphp

<div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
    <div>
        <a href="{{ route('kebendaharaan.toko.index') }}" class="text-xs font-black text-slate-500 hover:text-emerald-600 mb-3 inline-flex items-center">
            <i class="fas fa-arrow-left mr-2"></i> Beranda Toko
        </a>
        <h2 class="text-2xl font-black text-slate-800 tracking-tight flex items-center">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600 mr-3 shadow-inner">
                <i class="fas fa-truck"></i>
            </div>
            Pembelian Barang
        </h2>
    </div>
    @can('akses_toko_buku_kelola')
    <a href="{{ route('kebendaharaan.toko.pembelian.create') }}" class="inline-flex items-center px-5 py-3 bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white font-bold text-sm rounded-xl transition-all shadow-[0_4px_15px_-3px_rgba(16,185,129,0.4)]">
        <i class="fas fa-plus mr-2"></i> Catat Pembelian
    </a>
    @endcan
</div>

<div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="bg-slate-50 border-b border-slate-100 p-4 flex justify-between items-center">
        <h3 class="text-sm font-black text-slate-700 uppercase tracking-widest"><i class="fas fa-list text-emerald-500 mr-2"></i>Riwayat Pembelian</h3>
        <span class="bg-emerald-100 text-emerald-700 text-[10px] font-black px-2 py-1 rounded-md">{{ $list->count() }} Transaksi</span>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50/80 text-[10px] font-black text-slate-400 uppercase tracking-widest">
                    <th class="text-left px-4 py-3">Kode</th>
                    <th class="text-left px-4 py-3">Tanggal</th>
                    <th class="text-left px-4 py-3">Sumber Dana</th>
                    <th class="text-right px-4 py-3">Total</th>
                    <th class="text-left px-4 py-3">Keterangan</th>
                    <th class="text-center px-4 py-3">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($list as $pb)
                <tr class="border-t border-slate-100">
                    <td class="px-4 py-3 font-black text-slate-800">{{ $pb->kode }}</td>
                    <td class="px-4 py-3 font-semibold text-slate-600">{{ $pb->tanggal->format('d/m/Y') }}</td>
                    <td class="px-4 py-3 font-semibold text-slate-500">{{ $pb->pencairan?->kode ?? 'Dana bebas' }}</td>
                    <td class="px-4 py-3 text-right font-black text-slate-800">{{ $fmt($pb->total) }}</td>
                    <td class="px-4 py-3 font-semibold text-slate-500">{{ $pb->keterangan }}</td>
                    <td class="px-4 py-3 text-center">
                        <a href="{{ route('kebendaharaan.toko.pembelian.show', $pb->id) }}" class="inline-flex items-center px-3 py-1.5 bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white border border-indigo-200 font-bold text-[10px] rounded-lg transition-all">
                            <i class="fas fa-eye mr-1"></i> Detail
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 py-10 text-center">
                        <div class="w-16 h-16 bg-slate-50 text-slate-300 rounded-full flex items-center justify-center mx-auto mb-4 text-3xl shadow-inner"><i class="fas fa-truck"></i></div>
                        <p class="text-sm font-black text-slate-700">Belum Ada Pembelian</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection