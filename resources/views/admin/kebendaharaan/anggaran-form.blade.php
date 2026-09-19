@extends('layouts.app')

@section('title', 'Buat Anggaran')

@section('content')
<div class="mb-6">
    <a href="{{ route('kebendaharaan.anggaran.index') }}" class="text-xs font-black text-slate-500 hover:text-emerald-600 mb-3 inline-flex items-center">
        <i class="fas fa-arrow-left mr-2"></i> Kembali ke Daftar Anggaran
    </a>
    <h2 class="text-2xl font-black text-slate-800 tracking-tight flex items-center">
        <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600 mr-3 shadow-inner">
            <i class="fas fa-plus"></i>
        </div>
        Buat Anggaran (RAB)
    </h2>
</div>

<div class="max-w-2xl">
    <form action="{{ route('kebendaharaan.anggaran.store') }}" method="POST" class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
        @csrf
        <div class="mb-5">
            <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Nama Anggaran</label>
            <input type="text" name="nama" required value="{{ old('nama', $periodeAktif ? 'RAB ' . $periodeAktif->tahun_ajaran : '') }}" placeholder="mis. RAB SPMMU 2026-2027" class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 block p-3 outline-none transition-all font-medium">
            <p class="text-[11px] font-semibold text-slate-400 mt-1.5"><i class="fas fa-info-circle text-sky-400 mr-1"></i>Waktu penyusunan RAB: sebelum tahun ajaran baru dimulai.</p>
        </div>

        <div class="mb-5">
            <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Periode Tahun Ajaran</label>
            <select name="periode_id" required class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 block p-3 outline-none transition-all font-medium cursor-pointer">
                @foreach($periodes as $p)
                <option value="{{ $p->id }}" {{ $periodeAktif && $p->id === $periodeAktif->id ? 'selected' : '' }}>
                    {{ $p->tahun_ajaran }} {{ $periodeAktif && $p->id === $periodeAktif->id ? '(Aktif)' : '' }}
                </option>
                @endforeach
            </select>
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('kebendaharaan.anggaran.index') }}" class="px-5 py-3 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold text-sm rounded-xl transition-all">Batal</a>
            <button type="submit" class="px-6 py-3 bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white font-bold text-sm rounded-xl transition-all shadow-[0_4px_15px_-3px_rgba(16,185,129,0.4)]">
                <i class="fas fa-check mr-2"></i> Simpan &amp; Lanjut Isi Pos
            </button>
        </div>
    </form>
</div>
@endsection