@extends('layouts.app')

@section('title', 'Pinjaman')

@section('content')
@php
$labelStatus = ['aktif' => 'Aktif', 'lunas' => 'Lunas'];
$warnaStatus = ['aktif' => 'bg-amber-100 text-amber-700 border-amber-200', 'lunas' => 'bg-emerald-100 text-emerald-700 border-emerald-200'];
@endphp

<div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
    <div>
        <h2 class="text-2xl font-black text-slate-800 tracking-tight flex items-center">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600 mr-3 shadow-inner">
                <i class="fas fa-people-carry-box"></i>
            </div>
            Pinjaman Dana
        </h2>
        <p class="text-sm font-bold text-slate-400 mt-1 ml-14">
            Pencairan modal toko otomatis tercatat sebagai pinjaman; dilunasi dari setoran wali kelas.
        </p>
    </div>
    @can('akses_kebendaharaan')
    <a href="{{ route('kebendaharaan.pinjaman.create') }}" class="inline-flex items-center px-5 py-3 bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white font-bold text-sm rounded-xl transition-all shadow-[0_4px_15px_-3px_rgba(16,185,129,0.4)]">
        <i class="fas fa-plus mr-2"></i> Catat Pinjaman
    </a>
    @endcan
</div>

<div class="mb-4 flex flex-wrap items-center gap-2">
    <a href="{{ route('kebendaharaan.pinjaman.index') }}" class="text-xs font-black px-3 py-1.5 rounded-lg border {{ !request('status') ? 'bg-emerald-600 text-white border-emerald-600' : 'bg-white text-slate-500 border-slate-200 hover:border-emerald-300' }}">Semua</a>
    @foreach(['aktif', 'lunas'] as $st)
    <a href="{{ route('kebendaharaan.pinjaman.index', ['status' => $st]) }}" class="text-xs font-black px-3 py-1.5 rounded-lg border {{ request('status') === $st ? 'bg-emerald-600 text-white border-emerald-600' : 'bg-white text-slate-500 border-slate-200 hover:border-emerald-300' }}">{{ $labelStatus[$st] }}</a>
    @endforeach
</div>

<div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="bg-slate-50 border-b border-slate-100 p-4 flex justify-between items-center">
        <h3 class="text-sm font-black text-slate-700 uppercase tracking-widest"><i class="fas fa-list text-emerald-500 mr-2"></i>Daftar Pinjaman</h3>
        <span class="bg-emerald-100 text-emerald-700 text-[10px] font-black px-2 py-1 rounded-md">{{ $list->count() }} Pinjaman</span>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50/80 text-[10px] font-black text-slate-400 uppercase tracking-widest">
                    <th class="text-left px-4 py-3">Kode</th>
                    <th class="text-left px-4 py-3">Peminjam</th>
                    <th class="text-left px-4 py-3">Dari SPP</th>
                    <th class="text-left px-4 py-3">Tanggal</th>
                    <th class="text-right px-4 py-3">Jumlah</th>
                    <th class="text-right px-4 py-3">Sudah Angsur</th>
                    <th class="text-right px-4 py-3">Sisa</th>
                    <th class="text-center px-4 py-3">Status</th>
                    <th class="text-center px-4 py-3">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($list as $pj)
                @php $sisa = $pj->sisa(); @endphp
                <tr class="border-t border-slate-100">
                    <td class="px-4 py-3 font-black text-slate-800">{{ $pj->kode }}</td>
                    <td class="px-4 py-3 font-semibold text-slate-700">{{ $pj->peminjam?->name ?? '—' }}</td>
                    <td class="px-4 py-3 font-semibold text-slate-500">{{ $pj->pencairan?->kode ?? 'Manual' }}</td>
                    <td class="px-4 py-3 font-semibold text-slate-600">{{ $pj->tanggal->format('d/m/Y') }}</td>
                    <td class="px-4 py-3 text-right font-black text-slate-800">Rp {{ number_format($pj->jumlah, 0, ',', '.') }}</td>
                    <td class="px-4 py-3 text-right font-semibold text-slate-600">Rp {{ number_format($pj->totalSetoran(), 0, ',', '.') }}</td>
                    <td class="px-4 py-3 text-right font-black {{ $sisa > 0 ? 'text-rose-600' : 'text-emerald-600' }}">Rp {{ number_format($sisa, 0, ',', '.') }}</td>
                    <td class="px-4 py-3 text-center">
                        <span class="inline-block text-[10px] font-black px-2 py-1 rounded-md border {{ $warnaStatus[$pj->status] }}">
                            <i class="fas {{ $pj->status === 'lunas' ? 'fa-circle-check' : 'fa-hourglass-half' }} mr-1"></i>{{ $labelStatus[$pj->status] }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex flex-col items-center gap-1">
                            @if($pj->status === 'aktif' && $sisa <= 0)
                            <form action="{{ route('kebendaharaan.pinjaman.lunasi', $pj->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-emerald-50 text-emerald-600 hover:bg-emerald-600 hover:text-white border border-emerald-200 font-bold text-[10px] rounded-lg transition-all">
                                    <i class="fas fa-check mr-1"></i> Tandai Lunas
                                </button>
                            </form>
                            @endif
                            @if($pj->status === 'lunas' && $sisa > 0)
                            <form action="{{ route('kebendaharaan.pinjaman.aktifkan', $pj->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-amber-50 text-amber-600 hover:bg-amber-600 hover:text-white border border-amber-200 font-bold text-[10px] rounded-lg transition-all">
                                    <i class="fas fa-rotate-left mr-1"></i> Aktifkan Kembali
                                </button>
                            </form>
                            @endif
                            @if($pj->totalSetoran() <= 0)
                            <form action="{{ route('kebendaharaan.pinjaman.destroy', $pj->id) }}" method="POST" onsubmit="return confirm('Hapus pinjaman ini?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white border border-rose-200 font-bold text-[10px] rounded-lg transition-all">
                                    <i class="fas fa-trash mr-1"></i> Hapus
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="px-4 py-10 text-center">
                        <div class="w-16 h-16 bg-slate-50 text-slate-300 rounded-full flex items-center justify-center mx-auto mb-4 text-3xl shadow-inner"><i class="fas fa-people-carry-box"></i></div>
                        <p class="text-sm font-black text-slate-700">Belum Ada Pinjaman</p>
                        <p class="text-xs font-medium text-slate-400 mt-1">Pinjaman terbentuk otomatis saat pencairan Modal Toko dibayar, atau dicatat manual di sini.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection