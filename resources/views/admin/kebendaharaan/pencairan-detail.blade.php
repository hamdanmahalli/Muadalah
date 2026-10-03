@extends('layouts.app')

@section('title', 'Detail SPP ' . $pencairan->kode)

@section('content')
@php
    $labelStatus = ['diajukan' => 'Diajukan', 'disetujui' => 'Disetujui', 'dibayar' => 'Dibayar', 'ditolak' => 'Ditolak'];
    $bgStatus = ['diajukan' => 'bg-amber-100 text-amber-700 border-amber-200', 'disetujui' => 'bg-sky-100 text-sky-700 border-sky-200', 'dibayar' => 'bg-emerald-100 text-emerald-700 border-emerald-200', 'ditolak' => 'bg-rose-100 text-rose-700 border-rose-200'];
    $ikonStatus = ['diajukan' => 'fa-clock', 'disetujui' => 'fa-pen-to-square', 'dibayar' => 'fa-circle-check', 'ditolak' => 'fa-circle-xmark'];
@endphp
<div class="mb-6">
    <a href="{{ route('kebendaharaan.pencairan.index') }}" class="text-xs font-black text-slate-500 hover:text-emerald-600 mb-3 inline-flex items-center">
        <i class="fas fa-arrow-left mr-2"></i> Kembali ke Pencairan (SPP)
    </a>
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black text-slate-800 tracking-tight flex items-center">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600 mr-3 shadow-inner">
                    <i class="fas fa-file-invoice-dollar"></i>
                </div>
                Detail SPP
                <span class="ml-2 px-3 py-1 rounded-lg border bg-white text-slate-700 text-sm font-black">{{ $pencairan->kode }}</span>
            </h2>
            <p class="text-sm font-bold text-slate-400 mt-1 ml-14">
                {{ $pencairan->jenis === 'modal_toko' ? 'Modal Toko' : 'SPP Rutin' }} &middot; diajukan {{ $pencairan->tanggal_aju->format('d M Y') }} &middot;
                Periode {{ $pencairan->periode?->tahun_ajaran ?? '-' }} {{ ucfirst($pencairan->periode?->semester ?? '') }}
            </p>
        </div>
        <span class="inline-flex items-center gap-1.5 text-xs font-black px-3 py-1.5 rounded-xl border {{ $bgStatus[$pencairan->status] }}">
            <i class="fas {{ $ikonStatus[$pencairan->status] }}"></i> {{ $labelStatus[$pencairan->status] }}
        </span>
    </div>
</div>

@if($pencairan->status === 'ditolak')
<div class="mb-4 flex items-center gap-3 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl px-4 py-3 text-sm font-bold">
    <i class="fas fa-circle-xmark"></i> Alasan penolakan: {{ $pencairan->tolak_alasan }}
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        {{-- Lembar pos --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="bg-slate-50 border-b border-slate-100 p-4">
                <h3 class="text-sm font-black text-slate-700 uppercase tracking-widest"><i class="fas fa-table-list text-emerald-500 mr-2"></i>Rincian Pos</h3>
            </div>
            @if($baris->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-[10px] font-black uppercase tracking-widest text-slate-400 border-b border-slate-100 bg-slate-50">
                            <th class="px-5 py-3 w-10">No</th>
                            <th class="px-3 py-3">Kode</th>
                            <th class="px-3 py-3">Uraian Pos</th>
                            <th class="px-3 py-3 text-right">Alokasi</th>
                            <th class="px-3 py-3 text-right">Terpakai</th>
                            <th class="px-3 py-3 text-right">Sisa</th>
                            <th class="px-3 py-3 text-right">Nominal (Rp)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($baris as $i => $b)
                        <tr class="border-b border-slate-50 hover:bg-slate-50/50">
                            <td class="px-5 py-3 text-xs font-bold text-slate-400">{{ $i + 1 }}</td>
                            <td class="px-3 py-3 text-xs font-black text-slate-700 whitespace-nowrap">{{ $b['kode'] }}</td>
                            <td class="px-3 py-3 text-xs font-semibold text-slate-600">{{ $b['uraian'] }}</td>
                            <td class="px-3 py-3 text-right text-xs font-bold text-slate-500 whitespace-nowrap">Rp {{ number_format($b['alokasi'], 0, ',', '.') }}</td>
                            <td class="px-3 py-3 text-right text-xs font-bold text-slate-500 whitespace-nowrap">Rp {{ number_format($b['terpakai'], 0, ',', '.') }}</td>
                            <td class="px-3 py-3 text-right text-xs font-black whitespace-nowrap {{ $b['sisa'] < 0 ? 'text-rose-600' : 'text-emerald-600' }}">Rp {{ number_format(max($b['sisa'], 0), 0, ',', '.') }}</td>
                            <td class="px-3 py-3 text-right text-xs font-black text-slate-800 whitespace-nowrap">Rp {{ number_format($b['nominal'], 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="bg-emerald-50">
                            <td colspan="6" class="px-5 py-3 text-right text-xs font-black text-emerald-800 uppercase tracking-wider">Total SPP</td>
                            <td class="px-3 py-3 text-right text-base font-black text-emerald-800 whitespace-nowrap">Rp {{ number_format($pencairan->jumlah, 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            @else
            <p class="px-5 py-10 text-center text-xs font-bold text-slate-400">
                <i class="fas fa-table-list text-2xl text-slate-200"></i><br>
                <span class="mt-2 inline-block">Tanpa pos — {{ $pencairan->bulan_fiskal_label }}</span>
            </p>
            @endif
        </div>

        {{-- Riwayat status --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="bg-slate-50 border-b border-slate-100 p-4">
                <h3 class="text-sm font-black text-slate-700 uppercase tracking-widest"><i class="fas fa-timeline text-emerald-500 mr-2"></i>Riwayat Status</h3>
            </div>
            <div class="p-5 space-y-4">
                <div class="flex items-start gap-3">
                    <span class="w-8 h-8 shrink-0 rounded-full bg-amber-100 text-amber-700 flex items-center justify-center"><i class="fas fa-paper-plane text-xs"></i></span>
                    <div>
                        <p class="text-xs font-black text-slate-700">Diajukan</p>
                        <p class="text-[11px] font-semibold text-slate-400 mt-0.5">{{ $pencairan->pengaju?->name ?? '-' }} &middot; {{ $pencairan->tanggal_aju->format('d M Y') }}</p>
                    </div>
                </div>
                @if($pencairan->disetujui_at)
                <div class="flex items-start gap-3">
                    <span class="w-8 h-8 shrink-0 rounded-full bg-sky-100 text-sky-700 flex items-center justify-center"><i class="fas fa-pen-to-square text-xs"></i></span>
                    <div>
                        <p class="text-xs font-black text-slate-700">Disetujui</p>
                        <p class="text-[11px] font-semibold text-slate-400 mt-0.5">{{ $pencairan->disetujui_at->format('d M Y H:i') }}</p>
                    </div>
                </div>
                @endif
                @if($pencairan->dibayar_at)
                <div class="flex items-start gap-3">
                    <span class="w-8 h-8 shrink-0 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center"><i class="fas fa-circle-check text-xs"></i></span>
                    <div>
                        <p class="text-xs font-black text-slate-700">Dibayar</p>
                        <p class="text-[11px] font-semibold text-slate-400 mt-0.5">{{ $pencairan->dibayar_at->format('d M Y H:i') }}</p>
                    </div>
                </div>
                @endif
                @if($pencairan->status === 'ditolak')
                <div class="flex items-start gap-3">
                    <span class="w-8 h-8 shrink-0 rounded-full bg-rose-100 text-rose-700 flex items-center justify-center"><i class="fas fa-circle-xmark text-xs"></i></span>
                    <div class="min-w-0">
                        <p class="text-xs font-black text-slate-700">Ditolak</p>
                        <p class="text-[11px] font-semibold text-rose-500 mt-0.5">{{ $pencairan->tolak_alasan }}</p>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Kartu info --}}
    <div class="space-y-6">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="bg-slate-50 border-b border-slate-100 p-4">
                <h3 class="text-sm font-black text-slate-700 uppercase tracking-widest"><i class="fas fa-circle-info text-emerald-500 mr-2"></i>Informasi SPP</h3>
            </div>
            <dl class="p-5 space-y-3 text-sm">
                <div>
                    <dt class="text-[10px] font-black uppercase tracking-widest text-slate-400">Keperluan</dt>
                    <dd class="mt-0.5 text-xs font-bold text-slate-700">{{ $pencairan->keperluan }}</dd>
                </div>
                <div>
                    <dt class="text-[10px] font-black uppercase tracking-widest text-slate-400">Jenis</dt>
                    <dd class="mt-0.5 text-xs font-black {{ $pencairan->jenis === 'modal_toko' ? 'text-indigo-600' : 'text-emerald-600' }} uppercase">{{ $pencairan->jenis === 'modal_toko' ? 'Modal Toko' : 'Rutin' }}</dd>
                </div>
                <div>
                    <dt class="text-[10px] font-black uppercase tracking-widest text-slate-400">Bulan Anggaran</dt>
                    <dd class="mt-0.5 text-xs font-bold text-slate-700">{{ $pencairan->bulan_fiskal_label }}</dd>
                </div>
                <div>
                    <dt class="text-[10px] font-black uppercase tracking-widest text-slate-400">Total</dt>
                    <dd class="mt-0.5 text-base font-black text-emerald-700">Rp {{ number_format($pencairan->jumlah, 0, ',', '.') }}</dd>
                </div>
                <div>
                    <dt class="text-[10px] font-black uppercase tracking-widest text-slate-400">Pengaju</dt>
                    <dd class="mt-0.5 text-xs font-bold text-slate-700">{{ $pencairan->pengaju?->name ?? '-' }}</dd>
                </div>
                @if($pencairan->jenis === 'modal_toko')
                <div class="border-t border-slate-100 pt-3">
                    <dt class="text-[10px] font-black uppercase tracking-widest text-slate-400">Panjar / Pinjaman</dt>
                    <dd class="mt-0.5 text-xs font-black {{ $pencairan->isLunasPanjar() ? 'text-emerald-600' : 'text-indigo-600' }}">
                        {{ $pencairan->status === 'dibayar' ? ($pencairan->isLunasPanjar() ? 'Lunas' : 'Aktif') : '—' }}
                    </dd>
                    <p class="text-[10px] font-bold text-slate-400 mt-1">Menjadi pinjaman (buku belanja toko) saat dibayar.</p>
                </div>
                @endif
            </dl>
        </div>
    </div>
</div>
@endsection