@extends('layouts.app')

@section('title', 'Rekap Kebendaharaan')

@section('content')
@php
$fmt = fn($v) => 'Rp ' . number_format($v, 0, ',', '.');
@endphp

<div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
    <div>
        <h2 class="text-2xl font-black text-slate-800 tracking-tight flex items-center">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600 mr-3 shadow-inner">
                <i class="fas fa-chart-pie"></i>
            </div>
            Rekap Kebendaharaan
        </h2>
        <p class="text-sm font-bold text-slate-400 mt-1 ml-14">
            Neraca kas, rekap belanja per pos, dan posisi toko buku — untuk periode {{ $periode->tahun_ajaran }}.
        </p>
    </div>
    <a href="{{ route('kebendaharaan.rekap.pdf') }}" class="inline-flex items-center px-5 py-3 bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white font-bold text-sm rounded-xl transition-all shadow-[0_4px_15px_-3px_rgba(16,185,129,0.4)]">
        <i class="fas fa-file-pdf mr-2"></i> Cetak PDF
    </a>
</div>

<!-- KARTU NERACA -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
        <p class="text-[11px] font-black text-slate-400 uppercase tracking-widest">Total Pemasukan</p>
        <p class="text-xl font-black text-emerald-600 mt-1">{{ $fmt($data['pemasukanTotal']) }}</p>
        <p class="text-[11px] font-semibold text-slate-400 mt-0.5">Termasuk setoran toko buku</p>
    </div>
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
        <p class="text-[11px] font-black text-slate-400 uppercase tracking-widest">Total Pengeluaran</p>
        <p class="text-xl font-black text-rose-600 mt-1">{{ $fmt($data['realisasiTotal']) }}</p>
        <p class="text-[11px] font-semibold text-slate-400 mt-0.5">LPJ terverifikasi</p>
    </div>
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
        <p class="text-[11px] font-black text-slate-400 uppercase tracking-widest">Saldo Kas</p>
        <p class="text-xl font-black text-slate-800 mt-1">{{ $fmt($data['saldo']) }}</p>
        <p class="text-[11px] font-semibold text-slate-400 mt-0.5">Pemasukan − Pengeluaran</p>
    </div>
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
        <p class="text-[11px] font-black text-slate-400 uppercase tracking-widest">Dana Dicairkan</p>
        <p class="text-xl font-black text-sky-600 mt-1">{{ $fmt($data['pencairanDibayar']) }}</p>
        <p class="text-[11px] font-semibold text-slate-400 mt-0.5">Panjar dibayar (belum tentu terealisasi)</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-6">
    <!-- REKAP BELANJA -->
    <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="bg-slate-50 border-b border-slate-100 p-4 flex items-center justify-between">
            <h3 class="text-sm font-black text-slate-700 uppercase tracking-widest"><i class="fas fa-receipt text-emerald-500 mr-2"></i>Rekap Belanja per Pos Anggaran</h3>
            <span class="text-[10px] font-black text-slate-400">{{ $data['anggaran'] ? $data['anggaran']->nama : '—' }}</span>
        </div>
        @if($data['anggaran'])
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50/80 text-[10px] font-black text-slate-400 uppercase tracking-widest">
                        <th class="text-left px-4 py-3">Kelompok / Pos</th>
                        <th class="text-right px-4 py-3">Pagu</th>
                        <th class="text-right px-4 py-3">Realisasi</th>
                        <th class="text-right px-4 py-3">Sisa</th>
                        <th class="text-center px-4 py-3">%</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data['rekapBelanja'] as $kel)
                    <tr class="bg-emerald-50/50">
                        <td class="px-4 py-2.5 font-black text-slate-800">{{ $kel->kode }}. {{ $kel->nama }}</td>
                        <td class="px-4 py-2.5 text-right font-black text-slate-800">{{ $fmt($kel->pagu) }}</td>
                        <td class="px-4 py-2.5 text-right font-bold text-rose-600">{{ $fmt($kel->realisasi) }}</td>
                        <td class="px-4 py-2.5 text-right font-bold text-emerald-600">{{ $fmt(max(0, $kel->pagu - $kel->realisasi)) }}</td>
                        <td class="px-4 py-2.5 text-center font-bold text-slate-500">{{ $kel->pagu > 0 ? round($kel->realisasi / $kel->pagu * 100, 1) . '%' : '—' }}</td>
                    </tr>
                    @foreach($kel->rows as $row)
                    <tr class="border-t border-slate-100">
                        <td class="px-4 py-2.5 pl-8 font-semibold text-slate-600">{{ $row->kode }} — {{ $row->uraian }}</td>
                        <td class="px-4 py-2.5 text-right font-semibold text-slate-600">{{ $fmt($row->pagu) }}</td>
                        <td class="px-4 py-2.5 text-right font-bold {{ $row->realisasi > $row->pagu ? 'text-rose-600' : 'text-slate-700' }}">{{ $fmt($row->realisasi) }}</td>
                        <td class="px-4 py-2.5 text-right font-semibold text-slate-600">{{ $fmt($row->sisa) }}</td>
                        <td class="px-4 py-2.5 text-center font-semibold text-slate-500">{{ $row->persen }}%</td>
                    </tr>
                    @endforeach
                    @endforeach
                    <tr class="border-t-2 border-slate-200 bg-slate-50">
                        <td class="px-4 py-3 font-black text-slate-800 uppercase">Total Anggaran</td>
                        <td class="px-4 py-3 text-right font-black text-slate-800">{{ $fmt(collect($data['rekapBelanja'])->sum('pagu')) }}</td>
                        <td class="px-4 py-3 text-right font-black text-rose-600">{{ $fmt(collect($data['rekapBelanja'])->sum('realisasi')) }}</td>
                        <td class="px-4 py-3 text-right font-black text-emerald-600">{{ $fmt(max(0, collect($data['rekapBelanja'])->sum('pagu') - collect($data['rekapBelanja'])->sum('realisasi'))) }}</td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
        </div>
        @else
        <div class="p-10 text-center text-xs font-semibold text-slate-400">Belum ada anggaran untuk periode ini.</div>
        @endif
    </div>

    <!-- REKAP PEMASUKAN -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="bg-slate-50 border-b border-slate-100 p-4">
            <h3 class="text-sm font-black text-slate-700 uppercase tracking-widest"><i class="fas fa-circle-arrow-down text-emerald-500 mr-2"></i>Rekap Pemasukan</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50/80 text-[10px] font-black text-slate-400 uppercase tracking-widest">
                        <th class="text-left px-4 py-3">Uraian</th>
                        <th class="text-right px-4 py-3">Rencana</th>
                        <th class="text-right px-4 py-3">Realisasi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data['rekapPemasukan'] as $rp)
                    <tr class="border-t border-slate-100">
                        <td class="px-4 py-2.5 font-semibold text-slate-700">{{ $rp->uraian }}</td>
                        <td class="px-4 py-2.5 text-right font-semibold text-slate-600">{{ $fmt($rp->rencana) }}</td>
                        <td class="px-4 py-2.5 text-right font-bold text-emerald-600">{{ $fmt($rp->realisasi) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="3" class="px-4 py-6 text-center text-xs font-semibold text-slate-400">Belum ada rencana pemasukan.</td></tr>
                    @endforelse
                    @if($data['pemasukanBarang'] > 0)
                    <tr class="border-t border-slate-100">
                        <td class="px-4 py-2.5 font-semibold text-slate-700">Setoran Toko Buku <span class="text-[9px] font-black text-violet-500">(otomatis)</span></td>
                        <td class="px-4 py-2.5 text-right font-semibold text-slate-400">—</td>
                        <td class="px-4 py-2.5 text-right font-bold text-violet-600">{{ $fmt($data['pemasukanBarang']) }}</td>
                    </tr>
                    @endif
                    @foreach($data['sisaPemasukan'] as $sp)
                    <tr class="border-t border-slate-100">
                        <td class="px-4 py-2.5 font-semibold text-slate-700">{{ $sp->uraian }}</td>
                        <td class="px-4 py-2.5 text-right font-semibold text-slate-400">—</td>
                        <td class="px-4 py-2.5 text-right font-bold text-emerald-600">{{ $fmt($sp->jumlah) }}</td>
                    </tr>
                    @endforeach
                    <tr class="border-t-2 border-slate-200 bg-slate-50">
                        <td class="px-4 py-3 font-black text-slate-800 uppercase">Total</td>
                        <td class="px-4 py-3 text-right font-black text-slate-600">{{ $fmt(collect($data['rekapPemasukan'])->sum('rencana')) }}</td>
                        <td class="px-4 py-3 text-right font-black text-emerald-600">{{ $fmt($data['pemasukanTotal']) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- TOKO BUKU -->
<div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="bg-slate-50 border-b border-slate-100 p-4">
        <h3 class="text-sm font-black text-slate-700 uppercase tracking-widest"><i class="fas fa-book-open text-emerald-500 mr-2"></i>Posisi Toko Buku</h3>
    </div>
    <div class="p-0 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 divide-y sm:divide-y-0 sm:divide-x divide-slate-100">
        <div class="p-5">
            <p class="text-[11px] font-black text-slate-400 uppercase tracking-widest">Nilai Stok</p>
            <p class="text-lg font-black text-sky-600 mt-1">{{ $fmt($data['nilaiStok']) }}</p>
            <p class="text-[10px] font-semibold text-slate-400">Σ stok × harga beli</p>
        </div>
        <div class="p-5">
            <p class="text-[11px] font-black text-slate-400 uppercase tracking-widest">Piutang Wali Kelas</p>
            <p class="text-lg font-black text-rose-600 mt-1">{{ $fmt($data['piutangWaliKelas']) }}</p>
            <p class="text-[10px] font-semibold text-slate-400">Belum disetor</p>
        </div>
        <div class="p-5">
            <p class="text-[11px] font-black text-slate-400 uppercase tracking-widest">Total Setoran</p>
            <p class="text-lg font-black text-emerald-600 mt-1">{{ $fmt($data['totalSetoran']) }}</p>
            <p class="text-[10px] font-semibold text-slate-400">Menjadi pemasukan</p>
        </div>
        <div class="p-5">
            <p class="text-[11px] font-black text-slate-400 uppercase tracking-widest">Sisa Pinjaman</p>
            <p class="text-lg font-black text-violet-600 mt-1">{{ $fmt($data['sisaPinjaman']) }}</p>
            <p class="text-[10px] font-semibold text-slate-400">Modal yang belum kembali</p>
        </div>
        <div class="p-5">
            <p class="text-[11px] font-black text-slate-400 uppercase tracking-widest">Total Penjualan</p>
            <p class="text-lg font-black text-slate-800 mt-1">{{ $fmt($data['totalPenjualan']) }}</p>
            <p class="text-[10px] font-semibold text-slate-400">qty × harga jual (lunas)</p>
        </div>
        <div class="p-5">
            <p class="text-[11px] font-black text-slate-400 uppercase tracking-widest">Laba Kotor</p>
            <p class="text-lg font-black text-emerald-600 mt-1">{{ $fmt($data['labaKotor']) }}</p>
            <p class="text-[10px] font-semibold text-slate-400">Jual − beli</p>
        </div>
    </div>
</div>
@endsection