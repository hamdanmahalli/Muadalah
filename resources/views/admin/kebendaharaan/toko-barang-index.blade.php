@extends('layouts.app')

@section('title', 'Master Barang')

@section('content')
@php $fmt = fn($v) => 'Rp ' . number_format($v, 0, ',', '.'); @endphp

<div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
    <div>
        <a href="{{ route('kebendaharaan.toko.index') }}" class="text-xs font-black text-slate-500 hover:text-emerald-600 mb-3 inline-flex items-center">
            <i class="fas fa-arrow-left mr-2"></i> Beranda Toko
        </a>
        <h2 class="text-2xl font-black text-slate-800 tracking-tight flex items-center">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600 mr-3 shadow-inner">
                <i class="fas fa-boxes-stacked"></i>
            </div>
            Master Barang / Buku
        </h2>
    </div>
</div>

<div class="mb-6 bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
    <h3 class="text-sm font-black text-slate-700 uppercase tracking-widest mb-4"><i class="fas fa-plus-circle text-emerald-500 mr-2"></i>Tambah Barang</h3>
    <form action="{{ route('kebendaharaan.toko.barang.store') }}" method="POST" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
        @csrf
        <div class="lg:col-span-2">
            <input type="text" name="nama" required placeholder="Nama barang/buku" class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 block p-3 outline-none transition-all font-medium">
        </div>
        <div>
            <input type="text" name="satuan" placeholder="Satuan (Pcs)" class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 block p-3 outline-none transition-all font-medium">
        </div>
        <div>
            <input type="text" name="harga_beli" required placeholder="Harga beli (Rp)" class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 block p-3 outline-none transition-all font-medium">
        </div>
        <div>
            <input type="text" name="harga_jual" required placeholder="Harga jual (Rp)" class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 block p-3 outline-none transition-all font-medium">
        </div>
        <div class="lg:col-span-5">
            <input type="text" name="keterangan" placeholder="Keterangan (opsional)" class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 block p-3 outline-none transition-all font-medium">
        </div>
        <div class="lg:col-span-5 flex justify-end">
            <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm rounded-xl transition-all">
                <i class="fas fa-plus mr-2"></i> Tambah Barang
            </button>
        </div>
    </form>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="bg-slate-50 border-b border-slate-100 p-4 flex justify-between items-center">
        <h3 class="text-sm font-black text-slate-700 uppercase tracking-widest"><i class="fas fa-list text-emerald-500 mr-2"></i>Daftar Barang</h3>
        <span class="bg-emerald-100 text-emerald-700 text-[10px] font-black px-2 py-1 rounded-md">{{ $list->count() }} Barang</span>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50/80 text-[10px] font-black text-slate-400 uppercase tracking-widest">
                    <th class="text-left px-4 py-3">Kode</th>
                    <th class="text-left px-4 py-3">Nama</th>
                    <th class="text-right px-4 py-3">Stok</th>
                    <th class="text-right px-4 py-3">Harga Beli</th>
                    <th class="text-right px-4 py-3">Harga Jual</th>
                    <th class="text-center px-4 py-3">Status</th>
                    <th class="text-left px-4 py-3">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($list as $b)
                <tr class="border-t border-slate-100">
                    <td class="px-4 py-3 font-black text-slate-700">{{ $b->kode }}</td>
                    <td class="px-4 py-3">
                        <p class="font-semibold text-slate-800">{{ $b->nama }}</p>
                        @if($b->keterangan)<p class="text-[10px] font-semibold text-slate-400">{{ $b->keterangan }}</p>@endif
                    </td>
                    <td class="px-4 py-3 text-right font-black text-slate-800">{{ rtrim(rtrim(number_format($b->stok(), 2), '0'), '.') }} {{ $b->satuan }}</td>
                    <td class="px-4 py-3 text-right font-semibold text-slate-600">{{ $fmt($b->harga_beli) }}</td>
                    <td class="px-4 py-3 text-right font-semibold text-emerald-600">{{ $fmt($b->harga_jual) }}</td>
                    <td class="px-4 py-3 text-center">
                        <span class="text-[10px] font-black px-2 py-1 rounded-md border {{ $b->is_active ? 'bg-emerald-100 text-emerald-700 border-emerald-200' : 'bg-slate-100 text-slate-500 border-slate-200' }}">{{ $b->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2">
                            <details class="relative">
                                <summary class="text-[10px] font-black text-indigo-600 cursor-pointer inline-flex items-center"><i class="fas fa-pen mr-1"></i>Edit</summary>
                                <form action="{{ route('kebendaharaan.toko.barang.update', $b->id) }}" method="POST" class="absolute right-0 mt-1 z-10 w-64 bg-white border border-slate-200 rounded-xl shadow-lg p-4 grid grid-cols-1 gap-2">
                                    @csrf @method('PUT')
                                    <input type="text" name="nama" value="{{ $b->nama }}" required class="bg-slate-50 border border-slate-200 text-xs rounded-lg p-2">
                                    <input type="text" name="satuan" value="{{ $b->satuan }}" class="bg-slate-50 border border-slate-200 text-xs rounded-lg p-2">
                                    <input type="text" name="harga_beli" value="{{ $b->harga_beli }}" required class="bg-slate-50 border border-slate-200 text-xs rounded-lg p-2">
                                    <input type="text" name="harga_jual" value="{{ $b->harga_jual }}" required class="bg-slate-50 border border-slate-200 text-xs rounded-lg p-2">
                                    <textarea name="keterangan" rows="2" class="bg-slate-50 border border-slate-200 text-xs rounded-lg p-2">{{ $b->keterangan }}</textarea>
                                    <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg p-2">Simpan</button>
                                </form>
                            </details>
                            <form action="{{ route('kebendaharaan.toko.barang.toggle', $b->id) }}" method="POST">
                                @csrf
                                <button type="submit" title="Ubah status" class="text-[10px] font-black text-amber-500 hover:text-amber-700"><i class="fas fa-toggle-on mr-1"></i>{{ $b->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                            </form>
                            @if($b->stok() <= 0)
                            <form action="{{ route('kebendaharaan.toko.barang.destroy', $b->id) }}" method="POST" onsubmit="return confirm('Hapus barang ini?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-[10px] font-black text-rose-500 hover:text-rose-700"><i class="fas fa-trash"></i></button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-4 py-10 text-center">
                        <div class="w-16 h-16 bg-slate-50 text-slate-300 rounded-full flex items-center justify-center mx-auto mb-4 text-3xl shadow-inner"><i class="fas fa-box"></i></div>
                        <p class="text-sm font-black text-slate-700">Belum Ada Barang</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection