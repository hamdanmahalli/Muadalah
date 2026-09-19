@extends('layouts.app')

@section('title', 'Toko Buku')

@section('content')
@php
$fmt = fn($v) => 'Rp ' . number_format($v, 0, ',', '.');
@endphp

<div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
    <div>
        <h2 class="text-2xl font-black text-slate-800 tracking-tight flex items-center">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600 mr-3 shadow-inner">
                <i class="fas fa-book-open"></i>
            </div>
            Toko Buku
        </h2>
        <p class="text-sm font-bold text-slate-400 mt-1 ml-14">
            Beli stok dari dana pinjaman, distribusikan ke wali kelas, jual per murid, dan catat setoran.
        </p>
    </div>
    <div class="flex gap-2">
        @can('akses_toko_buku_kelola')
        <a href="{{ route('kebendaharaan.toko.barang.index') }}" class="inline-flex items-center justify-center px-4 py-2.5 bg-white text-slate-600 hover:bg-slate-600 hover:text-white border border-slate-200 font-bold text-xs rounded-xl transition-all">
            <i class="fas fa-boxes-stacked mr-2"></i> Barang
        </a>
        <a href="{{ route('kebendaharaan.toko.pembelian.index') }}" class="inline-flex items-center justify-center px-4 py-2.5 bg-white text-slate-600 hover:bg-slate-600 hover:text-white border border-slate-200 font-bold text-xs rounded-xl transition-all">
            <i class="fas fa-truck mr-2"></i> Pembelian
        </a>
        @endcan
        @canany(['akses_toko_buku_distribusi','akses_toko_buku_penjualan','akses_toko_buku_setoran'])
        <a href="{{ route('kebendaharaan.toko.distribusi.index') }}" class="inline-flex items-center justify-center px-4 py-2.5 bg-white text-slate-600 hover:bg-slate-600 hover:text-white border border-slate-200 font-bold text-xs rounded-xl transition-all">
            <i class="fas fa-share mr-2"></i> Distribusi
        </a>
        @endcanany
    </div>
</div>

<!-- KARTU RINGKASAN -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    @php
        $kartu = [
            ['ikon' => 'fa-boxes-stacked', 'warna' => 'bg-sky-50 text-sky-600', 'label' => 'Nilai Stok', 'nilai' => $totalNilaiStok, 'ket' => $stokValues->count() . ' jenis barang'],
            ['ikon' => 'fa-hand-holding-heart', 'warna' => 'bg-rose-50 text-rose-600', 'label' => 'Piutang Wali Kelas', 'nilai' => $totalPiutang, 'ket' => $distribusiOut->count() . ' distribusi belum lunas'],
            ['ikon' => 'fa-people-carry-box', 'warna' => 'bg-violet-50 text-violet-600', 'label' => 'Sisa Pinjaman', 'nilai' => $sisaPinjaman, 'ket' => $pinjaman->count() . ' pinjaman aktif'],
            ['ikon' => 'fa-circle-dollar-to-slot', 'warna' => 'bg-emerald-50 text-emerald-600', 'label' => 'Total Setoran', 'nilai' => $totalSetoran, 'ket' => 'Menjadi pemasukan bendahara'],
        ];
    @endphp
    @foreach($kartu as $c)
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
        <div class="w-11 h-11 rounded-xl {{ $c['warna'] }} flex items-center justify-center shadow-inner">
            <i class="fas {{ $c['ikon'] }} text-lg"></i>
        </div>
        <p class="text-[11px] font-black text-slate-400 uppercase tracking-widest mt-3">{{ $c['label'] }}</p>
        <p class="text-xl font-black text-slate-800 mt-0.5">{{ $fmt($c['nilai']) }}</p>
        <p class="text-[11px] font-semibold text-slate-400 mt-0.5">{{ $c['ket'] }}</p>
    </div>
    @endforeach
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-6">
    <!-- STOK RENDAH / RINGKASAN STOK -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="bg-slate-50 border-b border-slate-100 p-4 flex items-center justify-between">
            <h3 class="text-sm font-black text-slate-700 uppercase tracking-widest"><i class="fas fa-boxes-stacked text-emerald-500 mr-2"></i>Stok Barang</h3>
            @can('akses_toko_buku_kelola')
            <a href="{{ route('kebendaharaan.toko.barang.index') }}" class="text-xs font-black text-emerald-600 hover:text-emerald-800">Kelola <i class="fas fa-arrow-right ml-1"></i></a>
            @endcan
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50/80 text-[10px] font-black text-slate-400 uppercase tracking-widest">
                        <th class="text-left px-4 py-3">Barang</th>
                        <th class="text-right px-4 py-3">Stok</th>
                        <th class="text-right px-4 py-3">Harga Jual</th>
                        <th class="text-right px-4 py-3">Nilai Stok</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stokValues->sortBy('nama') as $b)
                    <tr class="border-t border-slate-100">
                        <td class="px-4 py-2.5 font-semibold text-slate-700">{{ $b->nama }}</td>
                        <td class="px-4 py-2.5 text-right font-black {{ $b->stok() <= 0 ? 'text-rose-500' : 'text-slate-800' }}">{{ rtrim(rtrim(number_format($b->stok(), 2), '0'), '.') }} {{ $b->satuan }}</td>
                        <td class="px-4 py-2.5 text-right font-semibold text-slate-600">{{ number_format($b->harga_jual, 0, ',', '.') }}</td>
                        <td class="px-4 py-2.5 text-right font-bold text-slate-600">{{ number_format($b->stok() * $b->harga_beli, 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="px-4 py-6 text-center text-xs font-semibold text-slate-400">Belum ada barang dengan stok.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- PIUTANG WALI KELAS -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="bg-slate-50 border-b border-slate-100 p-4 flex items-center justify-between">
            <h3 class="text-sm font-black text-slate-700 uppercase tracking-widest"><i class="fas fa-hand-holding-heart text-emerald-500 mr-2"></i>Menunggu Setoran</h3>
            @canany(['akses_toko_buku_distribusi','akses_toko_buku_penjualan','akses_toko_buku_setoran'])
            <a href="{{ route('kebendaharaan.toko.distribusi.index') }}" class="text-xs font-black text-emerald-600 hover:text-emerald-800">Kelola <i class="fas fa-arrow-right ml-1"></i></a>
            @endcanany
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50/80 text-[10px] font-black text-slate-400 uppercase tracking-widest">
                        <th class="text-left px-4 py-3">Kode</th>
                        <th class="text-left px-4 py-3">Wali Kelas</th>
                        <th class="text-right px-4 py-3">Nilai Ambil</th>
                        <th class="text-right px-4 py-3">Belum Disetor</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($distribusiOut->sortByDesc('id') as $d)
                    <tr class="border-t border-slate-100">
                        <td class="px-4 py-2.5">
                            <a href="{{ route('kebendaharaan.toko.distribusi.show', $d->id) }}" class="font-black text-emerald-600 hover:underline">{{ $d->kode }}</a>
                        </td>
                        <td class="px-4 py-2.5 font-semibold text-slate-700">{{ $d->waliKelas?->nama_guru ?? '—' }}</td>
                        <td class="px-4 py-2.5 text-right font-semibold text-slate-600">{{ number_format($d->nilaiPengambilan(), 0, ',', '.') }}</td>
                        <td class="px-4 py-2.5 text-right font-black text-rose-600">{{ number_format($d->sisaBelumDisetor(), 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="px-4 py-6 text-center text-xs font-semibold text-slate-400">Semua setoran sudah lunas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- PENJUALAN & LABA -->
<div class="grid grid-cols-3 gap-4">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
        <p class="text-[11px] font-black text-slate-400 uppercase tracking-widest">Total Penjualan</p>
        <p class="text-xl font-black text-slate-800 mt-1">{{ $fmt($totalPenjualan) }}</p>
        <p class="text-[10px] font-semibold text-slate-400">Penjualan yang sudah ditandai lunas</p>
    </div>
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
        <p class="text-[11px] font-black text-slate-400 uppercase tracking-widest">Pokok Penjualan</p>
        <p class="text-xl font-black text-slate-800 mt-1">{{ $fmt($bebanPokok) }}</p>
        <p class="text-[10px] font-semibold text-slate-400">qty terjual × harga beli</p>
    </div>
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
        <p class="text-[11px] font-black text-slate-400 uppercase tracking-widest">Laba Kotor</p>
        <p class="text-xl font-black text-emerald-600 mt-1">{{ $fmt($totalPenjualan - $bebanPokok) }}</p>
        <p class="text-[10px] font-semibold text-slate-400">Jual − pokok</p>
    </div>
</div>
@endsection