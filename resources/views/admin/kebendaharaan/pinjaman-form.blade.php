@extends('layouts.app')

@section('title', 'Catat Pinjaman')

@section('content')
<div class="mb-6">
    <a href="{{ route('kebendaharaan.pinjaman.index') }}" class="text-xs font-black text-slate-500 hover:text-emerald-600 mb-3 inline-flex items-center">
        <i class="fas fa-arrow-left mr-2"></i> Kembali ke Daftar Pinjaman
    </a>
    <h2 class="text-2xl font-black text-slate-800 tracking-tight flex items-center">
        <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600 mr-3 shadow-inner">
            <i class="fas fa-plus"></i>
        </div>
        Catat Pinjaman Manual
    </h2>
    <p class="text-sm font-bold text-slate-400 mt-1 ml-14">Pinjaman dari pencairan modal toko tercatat otomatis. Gunakan ini untuk pinjaman di luar alur SPP.</p>
</div>

<div class="max-w-2xl">
    <form action="{{ route('kebendaharaan.pinjaman.store') }}" method="POST" class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
        @csrf
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Peminjam</label>
                <select name="peminjam_user_id" class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 block p-3 outline-none transition-all font-medium cursor-pointer">
                    <option value="">— Diri sendiri —</option>
                    @foreach($users as $u)
                    <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->username }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Tanggal</label>
                <input type="date" name="tanggal" required value="{{ date('Y-m-d') }}" class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 block p-3 outline-none transition-all font-medium">
            </div>
            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Jumlah (Rp)</label>
                <input type="text" name="jumlah" required placeholder="mis. 1000000" class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 block p-3 outline-none transition-all font-medium">
            </div>
            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Keperluan</label>
                <input type="text" name="keperluan" required placeholder="mis. Modal belanja buku" class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 block p-3 outline-none transition-all font-medium">
            </div>
        </div>
        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('kebendaharaan.pinjaman.index') }}" class="px-5 py-3 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold text-sm rounded-xl transition-all">Batal</a>
            <button type="submit" class="px-6 py-3 bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white font-bold text-sm rounded-xl transition-all shadow-[0_4px_15px_-3px_rgba(16,185,129,0.4)]">
                <i class="fas fa-check mr-2"></i> Simpan Pinjaman
            </button>
        </div>
    </form>
</div>
@endsection