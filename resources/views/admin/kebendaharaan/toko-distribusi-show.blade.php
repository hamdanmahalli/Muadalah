@extends('layouts.app')

@section('title', 'Detail Distribusi')

@section('content')
@php $fmt = fn($v) => 'Rp ' . number_format($v, 0, ',', '.'); @endphp

<div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
    <div>
        <a href="{{ route('kebendaharaan.toko.distribusi.index') }}" class="text-xs font-black text-slate-500 hover:text-emerald-600 mb-3 inline-flex items-center">
            <i class="fas fa-arrow-left mr-2"></i> Daftar Distribusi
        </a>
        <h2 class="text-2xl font-black text-slate-800 tracking-tight flex items-center">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600 mr-3 shadow-inner">
                <i class="fas fa-share"></i>
            </div>
            {{ $dsb->kode }}
        </h2>
        <p class="text-sm font-bold text-slate-400 mt-1 ml-14">
            {{ $dsb->waliKelas?->nama_guru ?? 'Tanpa wali kelas' }} · {{ $dsb->tanggal->format('d/m/Y') }}
        </p>
    </div>
</div>

<!-- RINGKASAN SETORAN -->
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
        <p class="text-[11px] font-black text-slate-400 uppercase tracking-widest">Nilai Pengambilan</p>
        <p class="text-xl font-black text-slate-800 mt-1">{{ $fmt($dsb->nilaiPengambilan()) }}</p>
    </div>
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
        <p class="text-[11px] font-black text-slate-400 uppercase tracking-widest">Sudah Disetor</p>
        <p class="text-xl font-black text-emerald-600 mt-1">{{ $fmt($dsb->totalDisetor()) }}</p>
    </div>
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
        <p class="text-[11px] font-black text-slate-400 uppercase tracking-widest">Sisa Piutang</p>
        <p class="text-xl font-black {{ $dsb->sisaBelumDisetor() > 0 ? 'text-rose-600' : 'text-emerald-600' }} mt-1">{{ $fmt($dsb->sisaBelumDisetor()) }}</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-6">
    <!-- ITEM DISTRIBUSI -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="bg-slate-50 border-b border-slate-100 p-4">
            <h3 class="text-sm font-black text-slate-700 uppercase tracking-widest"><i class="fas fa-boxes-stacked text-emerald-500 mr-2"></i>Item Distribusi</h3>
        </div>
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50/80 text-[10px] font-black text-slate-400 uppercase tracking-widest">
                    <th class="text-left px-4 py-3">Barang</th>
                    <th class="text-right px-4 py-3">Qty</th>
                    <th class="text-right px-4 py-3">Harga Jual</th>
                    <th class="text-right px-4 py-3">Jumlah</th>
                </tr>
            </thead>
            <tbody>
                @foreach($dsb->items as $it)
                <tr class="border-t border-slate-100">
                    <td class="px-4 py-2.5 font-semibold text-slate-700">{{ $it->barang?->nama }}</td>
                    <td class="px-4 py-2.5 text-right font-bold text-slate-800">{{ rtrim(rtrim(number_format($it->qty, 2), '0'), '.') }}</td>
                    <td class="px-4 py-2.5 text-right font-semibold text-slate-600">{{ $fmt($it->harga_jual) }}</td>
                    <td class="px-4 py-2.5 text-right font-black text-slate-800">{{ $fmt($it->qty * $it->harga_jual) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- FORM PENJUALAN -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="bg-slate-50 border-b border-slate-100 p-4">
            <h3 class="text-sm font-black text-slate-700 uppercase tracking-widest"><i class="fas fa-cart-shopping text-emerald-500 mr-2"></i>Catat Penjualan ke Murid</h3>
            <p class="text-[10px] font-bold text-slate-400 mt-1">Wali kelas mencatat buku yang dibeli murid. Uang masih dipegang wali kelas sampai disetor.</p>
        </div>
        @can('akses_toko_buku_penjualan')
        <form action="{{ route('kebendaharaan.toko.penjualan.store') }}" method="POST" class="p-4 grid grid-cols-1 sm:grid-cols-2 gap-2.5">
            @csrf
            <input type="hidden" name="distribusi_barang_id" value="{{ $dsb->id }}">
            <div class="sm:col-span-2">
                <select name="siswa_id" required class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-xs rounded-xl focus:ring-2 focus:ring-emerald-500 p-2.5 outline-none transition-all font-medium">
                    <option value="">— Pilih Murid —</option>
                    @foreach($siswaDiKelas as $s)
                    <option value="{{ $s->id }}">{{ $s->nama_siswa }}</option>
                    @endforeach
                </select>
            </div>
            <div class="sm:col-span-2">
                <select name="barang_id" required class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-xs rounded-xl focus:ring-2 focus:ring-emerald-500 p-2.5 outline-none transition-all font-medium">
                    <option value="">— Pilih Barang —</option>
                    @foreach($barangDistribusi as $b)
                    <option value="{{ $b->id }}">{{ $b->nama }} ({{ $fmt($b->harga_jual) }})</option>
                    @endforeach
                </select>
            </div>
            <input type="number" step="any" name="qty" required value="1" min="1" class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-xs rounded-xl focus:ring-2 focus:ring-emerald-500 p-2.5 outline-none transition-all font-medium">
            <input type="date" name="tanggal" required value="{{ date('Y-m-d') }}" class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-xs rounded-xl focus:ring-2 focus:ring-emerald-500 p-2.5 outline-none transition-all font-medium">
            <div class="sm:col-span-2">
                <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2.5 rounded-xl text-xs transition-all"><i class="fas fa-cart-plus mr-1.5"></i> Catat Penjualan (Belum Lunas)</button>
            </div>
        </form>
        @else
        <p class="p-4 text-center text-xs font-semibold text-slate-400">Anda tidak memiliki hak mencatat penjualan.</p>
        @endcan
    </div>
</div>

<!-- DAFTAR PENJUALAN -->
<div class="mb-6 bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="bg-slate-50 border-b border-slate-100 p-4 flex items-center justify-between">
        <h3 class="text-sm font-black text-slate-700 uppercase tracking-widest"><i class="fas fa-cart-shopping text-emerald-500 mr-2"></i>Rekap Penjualan</h3>
        <span class="text-[10px] font-black text-slate-400">{{ $dsb->penjualan->count() }} catatan</span>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50/80 text-[10px] font-black text-slate-400 uppercase tracking-widest">
                    <th class="text-left px-4 py-3">Murid</th>
                    <th class="text-left px-4 py-3">Barang</th>
                    <th class="text-right px-4 py-3">Qty</th>
                    <th class="text-right px-4 py-3">Harga</th>
                    <th class="text-right px-4 py-3">Jumlah</th>
                    <th class="text-center px-4 py-3">Status</th>
                    @can('akses_toko_buku_penjualan')
                    <th class="text-center px-4 py-3">Aksi</th>
                    @endcan
                </tr>
            </thead>
            <tbody>
                @forelse($dsb->penjualan->sortByDesc('id') as $pj)
                <tr class="border-t border-slate-100">
                    <td class="px-4 py-2.5 font-semibold text-slate-700">{{ $pj->siswa?->nama_siswa }}</td>
                    <td class="px-4 py-2.5 font-semibold text-slate-500">{{ $pj->barang?->nama }}</td>
                    <td class="px-4 py-2.5 text-right font-bold text-slate-800">{{ rtrim(rtrim(number_format($pj->qty, 2), '0'), '.') }}</td>
                    <td class="px-4 py-2.5 text-right font-semibold text-slate-600">{{ $fmt($pj->harga_jual) }}</td>
                    <td class="px-4 py-2.5 text-right font-black text-slate-800">{{ $fmt($pj->qty * $pj->harga_jual) }}</td>
                    <td class="px-4 py-2.5 text-center">
                        <span class="text-[10px] font-black px-2 py-1 rounded-md border {{ $pj->status === 'lunas' ? 'bg-emerald-100 text-emerald-700 border-emerald-200' : 'bg-amber-100 text-amber-700 border-amber-200' }}">
                            {{ $pj->status === 'lunas' ? 'Lunas' : 'Belum' }}
                        </span>
                    </td>
                    @can('akses_toko_buku_penjualan')
                    <td class="px-4 py-2.5 text-center">
                        @if($pj->status === 'belum')
                        <form action="{{ route('kebendaharaan.toko.penjualan.lunas', $pj->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="text-[10px] font-black text-emerald-600 hover:text-emerald-800"><i class="fas fa-check mr-1"></i>Tandai Lunas</button>
                        </form>
                        @endif
                    </td>
                    @endcan
                </tr>
                @empty
                <tr><td colspan="7" class="px-4 py-8 text-center text-xs font-semibold text-slate-400">Belum ada penjualan tercatat.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- SETORAN -->
<div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="bg-slate-50 border-b border-slate-100 p-4 flex items-center justify-between">
        <h3 class="text-sm font-black text-slate-700 uppercase tracking-widest"><i class="fas fa-hand-holding-dollar text-emerald-500 mr-2"></i>Setoran Wali Kelas</h3>
        <span class="text-[10px] font-black {{ $dsb->sisaBelumDisetor() > 0 ? 'text-rose-500' : 'text-emerald-600' }}">Sisa Rp {{ number_format($dsb->sisaBelumDisetor(), 0, ',', '.') }}</span>
    </div>

    @can('akses_toko_buku_setoran')
    <form action="{{ route('kebendaharaan.toko.setoran.store') }}" method="POST" class="p-4 grid grid-cols-1 sm:grid-cols-4 gap-2.5 border-b border-slate-100 bg-slate-50/50">
        @csrf
        <input type="hidden" name="distribusi_barang_id" value="{{ $dsb->id }}">
        <div>
            <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Total Setoran</label>
            <input type="text" name="total" required placeholder="mis. 100000" class="w-full bg-white border border-slate-200 text-slate-800 text-xs rounded-xl focus:ring-2 focus:ring-emerald-500 p-2.5 outline-none transition-all font-medium">
        </div>
        <div>
            <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Pelunasan Pinjaman</label>
            <select name="pinjaman_id" required class="w-full bg-white border border-slate-200 text-slate-800 text-xs rounded-xl focus:ring-2 focus:ring-emerald-500 p-2.5 outline-none transition-all font-medium cursor-pointer">
                <option value="">— Pilih Pinjaman —</option>
                @foreach($pinjamanAktif as $p)
                <option value="{{ $p->id }}">{{ $p->kode }} · sisa Rp {{ number_format($p->sisa(), 0, ',', '.') }} · {{ $p->peminjam?->name ?? '' }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Tanggal</label>
            <input type="date" name="tanggal" required value="{{ date('Y-m-d') }}" class="w-full bg-white border border-slate-200 text-slate-800 text-xs rounded-xl focus:ring-2 focus:ring-emerald-500 p-2.5 outline-none transition-all font-medium">
        </div>
        <div class="flex items-end">
            <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2.5 rounded-xl text-xs transition-all"><i class="fas fa-hand-holding-dollar mr-1.5"></i> Catat Setoran</button>
        </div>
        <div class="sm:col-span-4">
            <input type="text" name="keterangan" placeholder="Keterangan (opsional)" class="w-full bg-white border border-slate-200 text-slate-800 text-xs rounded-xl focus:ring-2 focus:ring-emerald-500 p-2.5 outline-none transition-all font-medium">
        </div>
    </form>
    @else
    <p class="px-4 py-3 text-xs font-semibold text-slate-400">Anda tidak memiliki hak mencatat setoran.</p>
    @endcan

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50/80 text-[10px] font-black text-slate-400 uppercase tracking-widest">
                    <th class="text-left px-4 py-3">Kode</th>
                    <th class="text-left px-4 py-3">Tanggal</th>
                    <th class="text-left px-4 py-3">Lunasi Pinjaman</th>
                    <th class="text-right px-4 py-3">Total</th>
                    <th class="text-center px-4 py-3">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($dsb->setoran->sortByDesc('id') as $std)
                <tr class="border-t border-slate-100">
                    <td class="px-4 py-2.5 font-black text-slate-800">{{ $std->kode }}</td>
                    <td class="px-4 py-2.5 font-semibold text-slate-600">{{ $std->tanggal->format('d/m/Y') }}</td>
                    <td class="px-4 py-2.5 font-semibold text-slate-500">{{ $std->pinjaman?->kode ?? '—' }}</td>
                    <td class="px-4 py-2.5 text-right font-black text-emerald-600">+ {{ $fmt($std->total) }}</td>
                    <td class="px-4 py-2.5 text-center">
                        @can('akses_toko_buku_setoran')
                        <form action="{{ route('kebendaharaan.toko.setoran.destroy', $std->id) }}" method="POST" onsubmit="return confirm('Hapus setoran ini? Pemasukan otomatisnya juga akan terhapus.')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-rose-500 hover:text-rose-700 text-xs"><i class="fas fa-trash"></i></button>
                        </form>
                        @endcan
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-4 py-8 text-center text-xs font-semibold text-slate-400">Belum ada setoran.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection