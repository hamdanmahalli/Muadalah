@extends('layouts.app')

@section('title', 'Pemasukan')

@section('content')
<div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
    <div>
        <h2 class="text-2xl font-black text-slate-800 tracking-tight flex items-center">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600 mr-3 shadow-inner">
                <i class="fas fa-cash-register"></i>
            </div>
            Pemasukan Dana
        </h2>
        <p class="text-sm font-bold text-slate-400 mt-1 ml-14">
            Pencatatan dana masuk (BOS, SPP, dll). Setoran toko buku tercatat otomatis di sini.
        </p>
    </div>
    @can('akses_pemasukan')
    <a href="{{ route('kebendaharaan.pemasukan.create') }}" class="inline-flex items-center px-5 py-3 bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white font-bold text-sm rounded-xl transition-all shadow-[0_4px_15px_-3px_rgba(16,185,129,0.4)]">
        <i class="fas fa-plus mr-2"></i> Catat Pemasukan
    </a>
    @endcan
</div>

<div class="mb-5 bg-white rounded-2xl shadow-sm border border-slate-200 p-5 flex items-center justify-between">
    <div>
        <p class="text-[11px] font-black text-slate-400 uppercase tracking-widest">Total Pemasukan Periode Ini</p>
        <p class="text-2xl font-black text-emerald-600 mt-0.5">Rp {{ number_format($total, 0, ',', '.') }}</p>
    </div>
    <i class="fas fa-circle-arrow-up text-3xl text-emerald-200"></i>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="bg-slate-50 border-b border-slate-100 p-4 flex justify-between items-center">
        <h3 class="text-sm font-black text-slate-700 uppercase tracking-widest"><i class="fas fa-list text-emerald-500 mr-2"></i>Daftar Pemasukan</h3>
        <span class="bg-emerald-100 text-emerald-700 text-[10px] font-black px-2 py-1 rounded-md">{{ $list->count() }} Catatan</span>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50/80 text-[10px] font-black text-slate-400 uppercase tracking-widest">
                    <th class="text-left px-4 py-3">Tanggal</th>
                    <th class="text-left px-4 py-3">Uraian</th>
                    <th class="text-left px-4 py-3">Sumber Rencana</th>
                    <th class="text-right px-4 py-3">Jumlah</th>
                    @can('akses_pemasukan')
                    <th class="text-center px-4 py-3 w-16">Aksi</th>
                    @endcan
                </tr>
            </thead>
            <tbody>
                @forelse($list as $pm)
                <tr class="border-t border-slate-100">
                    <td class="px-4 py-3 font-semibold text-slate-600">{{ $pm->tanggal->format('d/m/Y') }}</td>
                    <td class="px-4 py-3">
                        <p class="font-semibold text-slate-800">{{ $pm->uraian }}</p>
                        @if($pm->setoran_barang_id)
                        <span class="text-[10px] font-black text-violet-500"><i class="fas fa-store mr-1"></i>Dari setoran toko buku</span>
                        @endif
                        @if($pm->keterangan)
                        <p class="text-[10px] font-semibold text-slate-400 mt-0.5">{{ $pm->keterangan }}</p>
                        @endif
                    </td>
                    <td class="px-4 py-3 font-semibold text-slate-500">{{ $pm->rencana?->uraian ?? '—' }}</td>
                    <td class="px-4 py-3 text-right font-black text-emerald-600">+ Rp {{ number_format($pm->jumlah, 0, ',', '.') }}</td>
                    @can('akses_pemasukan')
                    <td class="px-4 py-3 text-center">
                        <form action="{{ route('kebendaharaan.pemasukan.destroy', $pm->id) }}" method="POST" onsubmit="return confirm('Hapus catatan pemasukan ini?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-rose-500 hover:text-rose-700" {{ $pm->setoran_barang_id ? 'disabled title="Hapus lewat menu Setoran Toko Buku"' : '' }}><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                    @endcan
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-4 py-10 text-center">
                        <div class="w-16 h-16 bg-slate-50 text-slate-300 rounded-full flex items-center justify-center mx-auto mb-4 text-3xl shadow-inner"><i class="fas fa-circle-arrow-down"></i></div>
                        <p class="text-sm font-black text-slate-700">Belum Ada Pemasukan</p>
                        <p class="text-xs font-medium text-slate-400 mt-1">Catat pemasukan dari tombol di atas.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection