@extends('layouts.app')

@section('title', 'Siswa Saya')

@section('content')
<style>
    header, aside { display: none !important; }
    #btn-buka-sidebar { display: none !important; }
    main { padding: 0 !important; background-color: #f8fafc !important; overflow: hidden !important; }
    body { overflow: hidden !important; background-color: #f8fafc !important; }
    .scrollbar-none::-webkit-scrollbar { display: none; }
    .scrollbar-none { -ms-overflow-style: none; scrollbar-width: none; }
    @supports (padding-bottom: env(safe-area-inset-bottom)) { .pb-safe { padding-bottom: env(safe-area-inset-bottom); } }
</style>

<div data-turbo="true" class="max-w-md mx-auto h-[100dvh] bg-slate-50 flex flex-col relative font-sans overflow-hidden">

    <!-- HEADER MODERN -->
    <div class="shrink-0 bg-white px-4 pt-4 pb-4 border-b border-slate-100 relative z-20">
        <div class="flex items-center gap-3">
            <a href="javascript:history.back()" class="w-10 h-10 rounded-full bg-slate-100 text-slate-600 flex items-center justify-center active:scale-95 transition-all hover:bg-slate-200 shrink-0">
                <i class="fas fa-arrow-left text-sm"></i>
            </a>
            <div class="flex-1 min-w-0">
                <h2 class="text-base font-black text-slate-900 tracking-tight truncate">Wali Kelas</h2>
                <p class="text-[10px] font-bold text-indigo-600 uppercase tracking-widest mt-0.5">Siswa Saya</p>
            </div>
        </div>
    </div>

    <!-- AREA KONTEN -->
    <div class="flex-1 overflow-y-auto bg-slate-50 relative z-10 pt-5 pb-32 scrollbar-none px-5">

        @if($kelasWali->isEmpty())
            <div class="bg-white rounded-3xl border border-dashed border-slate-200 p-8 text-center">
                <div class="w-16 h-16 bg-slate-50 text-slate-300 rounded-full flex items-center justify-center mx-auto mb-4 text-3xl"><i class="fas fa-user-cog"></i></div>
                <h3 class="font-bold text-slate-700 text-lg mb-1">Belum Ada Kelas yang Diampu</h3>
                <p class="text-sm text-slate-400">Anda belum ditetapkan sebagai wali kelas. Silakan hubungi admin untuk menetapkan kelas wali Anda.</p>
            </div>
        @else
            <!-- Pilih kelas wali -->
            <form method="GET" action="{{ route('siswa-saya.index') }}" class="bg-white rounded-3xl shadow-[0_4px_18px_-10px_rgba(2,6,23,0.06)] border border-slate-100 p-4 mb-5">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Kelas Wali Saya</label>
                        <select name="kelas_id" onchange="this.form.submit()" class="w-full border border-slate-200 rounded-xl p-2.5 focus:ring-2 focus:ring-indigo-500 outline-none">
                            @foreach($kelasWali as $kl)
                                <option value="{{ $kl->id }}" {{ $kelasId == $kl->id ? 'selected' : '' }}>{{ $kl->nama_kelas }} ({{ $kl->tingkat }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Periode</label>
                        <select name="periode_id" onchange="this.form.submit()" class="w-full border border-slate-200 rounded-xl p-2.5 focus:ring-2 focus:ring-indigo-500 outline-none">
                            <option value="">- Periode Aktif -</option>
                        </select>
                    </div>
                </div>
            </form>

            <div class="bg-white rounded-3xl shadow-[0_4px_18px_-10px_rgba(2,6,23,0.06)] border border-slate-100 overflow-hidden">
                <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="font-bold text-slate-700">Daftar Murid <span class="text-slate-400 text-sm font-normal">{{ $kelasWali->firstWhere('id',$kelasId)?->nama_kelas }}</span></h3>
                </div>
                <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 uppercase w-8">No.Absen</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 uppercase">NIS</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 uppercase">Nama</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 uppercase">JK</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 uppercase">Tagihan Belum Lunas</th>
                            <th class="px-4 py-3 text-center text-xs font-bold text-slate-500 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse($siswas->sortBy('angkatan.nomor_absen') as $s)
                        @php
                            $belum = $s->tagihans->filter(fn($t) => $t->sisa() > 0);
                        @endphp
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 text-slate-500">{{ $s->angkatan->where('kelas_id', $kelasId)->sortByDesc('periode.is_active')->first()?->nomor_absen ?? $loop->iteration }}</td>
                            <td class="px-4 py-3 font-bold text-slate-700">{{ $s->nis }}</td>
                            <td class="px-4 py-3 font-semibold text-slate-800">{{ $s->nama_siswa }}</td>
                            <td class="px-4 py-3 text-slate-500">{{ $s->jenis_kelamin }}</td>
                            <td class="px-4 py-3">
                                @if($belum->isNotEmpty())
                                    <span class="text-xs font-bold text-rose-600">{{ $belum->count() }} tagihan · Rp {{ number_format($belum->sum(fn($t)=>$t->sisa()),0,',','.') }}</span>
                                @else
                                    <span class="text-xs font-bold text-emerald-600">Tidak ada tunggakan</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                <a href="{{ route('siswa-saya.detail', $s->id) }}" class="inline-flex items-center gap-1 bg-indigo-600 text-white px-3 py-1.5 rounded-lg text-xs font-semibold hover:bg-indigo-700 transition">
                                    <i class="fas fa-eye"></i> Riwayat
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="px-4 py-10 text-center text-slate-400">Belum ada murid di kelas ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
                </div>
            </div>
        @endif
    </div>

    <!-- NAVIGASI BAWAH -->
    @include('partials.bottom-nav', ['active' => ''])

</div>
@endsection
