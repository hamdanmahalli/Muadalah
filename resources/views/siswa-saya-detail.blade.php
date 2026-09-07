@extends('layouts.app')

@section('title', 'Riwayat Siswa')

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
                <h2 class="text-base font-black text-slate-900 tracking-tight truncate">Riwayat Murid</h2>
                <p class="text-[10px] font-bold text-indigo-600 uppercase tracking-widest mt-0.5">Wali Kelas</p>
            </div>
        </div>
    </div>

    <!-- AREA KONTEN -->
    <div class="flex-1 overflow-y-auto bg-slate-50 relative z-10 pt-5 pb-32 scrollbar-none px-5">

        <!-- Header murid -->
        <div class="bg-white rounded-3xl shadow-[0_4px_18px_-10px_rgba(2,6,23,0.06)] border border-slate-100 p-5 mb-5">
            <div class="flex items-center gap-4">
                @if($siswa->foto)
                    <img src="{{ asset($siswa->foto) }}" class="w-16 h-16 rounded-full object-cover border-4 border-indigo-100">
                @else
                    <div class="w-16 h-16 rounded-full bg-gradient-to-br from-indigo-400 to-indigo-600 flex items-center justify-center text-white text-2xl font-bold">{{ strtoupper(substr($siswa->nama_siswa,0,1)) }}</div>
                @endif
                <div class="min-w-0">
                    <h3 class="text-lg font-extrabold text-slate-800 truncate">{{ $siswa->nama_siswa }}</h3>
                    <p class="text-sm text-slate-500">NIS: {{ $siswa->nis }} | {{ $siswa->kelasAktif()?->kelas?->nama_kelas ?? '-' }}</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <!-- Biodata singkat -->
            <div class="bg-white rounded-3xl shadow-[0_4px_18px_-10px_rgba(2,6,23,0.06)] border border-slate-100 p-5">
                <h4 class="font-bold text-slate-700 mb-3"><i class="fas fa-user text-indigo-500 mr-2"></i> Biodata</h4>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between gap-3"><dt class="text-slate-400">TTL</dt><dd class="font-semibold text-slate-700 text-right">{{ $siswa->tempat_lahir }}, {{ $siswa->tanggal_lahir?->format('d-m-Y') }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-400">JK</dt><dd class="font-semibold text-slate-700">{{ $siswa->jenis_kelamin }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-400">Ayah</dt><dd class="font-semibold text-slate-700">{{ $siswa->nama_ayah }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-400">Ibu</dt><dd class="font-semibold text-slate-700">{{ $siswa->nama_ibu }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-400">HP Ortu</dt><dd class="font-semibold text-slate-700">{{ $siswa->no_hp_ortu }}</dd></div>
                </dl>
            </div>

            <!-- Rekap absensi -->
            <div class="bg-white rounded-3xl shadow-[0_4px_18px_-10px_rgba(2,6,23,0.06)] border border-slate-100 p-5">
                <h4 class="font-bold text-slate-700 mb-3"><i class="fas fa-clipboard-check text-emerald-500 mr-2"></i> Rekap Absensi</h4>
                @php
                    $rekabsen = $kehadiran->groupBy('status')->map->count();
                @endphp
                <div class="grid grid-cols-2 gap-2 text-sm">
                    <div class="border border-emerald-100 rounded-xl p-3 bg-emerald-50"><p class="text-xs font-bold text-slate-400">Hadir</p><p class="font-bold text-emerald-600 text-lg">{{ $rekabsen['hadir'] ?? 0 }}</p></div>
                    <div class="border border-amber-100 rounded-xl p-3 bg-amber-50"><p class="text-xs font-bold text-slate-400">Sakit</p><p class="font-bold text-amber-600 text-lg">{{ $rekabsen['sakit'] ?? 0 }}</p></div>
                    <div class="border border-sky-100 rounded-xl p-3 bg-sky-50"><p class="text-xs font-bold text-slate-400">Izin</p><p class="font-bold text-sky-600 text-lg">{{ $rekabsen['izin'] ?? 0 }}</p></div>
                    <div class="border border-rose-100 rounded-xl p-3 bg-rose-50"><p class="text-xs font-bold text-slate-400">Alpha</p><p class="font-bold text-rose-600 text-lg">{{ $rekabsen['alpha'] ?? 0 }}</p></div>
                </div>
            </div>

            <!-- Tagihan -->
            <div class="bg-white rounded-3xl shadow-[0_4px_18px_-10px_rgba(2,6,23,0.06)] border border-slate-100 p-5">
                <h4 class="font-bold text-slate-700 mb-3"><i class="fas fa-file-invoice-dollar text-indigo-500 mr-2"></i> Tagihan</h4>
                @php $belum = $tagihans->filter(fn($t) => $t->sisa() > 0); @endphp
                <p class="text-sm text-slate-500 mb-2">Jumlah tagihan: <span class="font-bold text-slate-700">{{ $tagihans->count() }}</span></p>
                <p class="text-sm text-slate-500 mb-2">Tunggakan: <span class="font-bold text-rose-600">{{ $belum->count() }}</span></p>
                <p class="text-sm text-slate-500">Total sisa: <span class="font-bold text-rose-600">Rp {{ number_format($belum->sum(fn($t)=>$t->sisa()),0,',','.') }}</span></p>
            </div>
        </div>

        <!-- Nilai -->
        <div class="bg-white rounded-3xl shadow-[0_4px_18px_-10px_rgba(2,6,23,0.06)] border border-slate-100 p-5 mt-5">
            <h4 class="font-bold text-slate-700 mb-4"><i class="fas fa-clipboard-list text-indigo-500 mr-2"></i> Nilai</h4>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-bold text-slate-500 uppercase">Pelajaran</th>
                            <th class="px-4 py-2 text-left text-xs font-bold text-slate-500 uppercase">Periode</th>
                            <th class="px-4 py-2 text-left text-xs font-bold text-slate-500 uppercase">UTS</th>
                            <th class="px-4 py-2 text-left text-xs font-bold text-slate-500 uppercase">UAS</th>
                            <th class="px-4 py-2 text-left text-xs font-bold text-slate-500 uppercase">Akhir</th>
                            <th class="px-4 py-2 text-left text-xs font-bold text-slate-500 uppercase">Predikat</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse($nilai as $n)
                        <tr>
                            <td class="px-4 py-2 font-semibold text-slate-700">{{ $n->pelajaran?->nama_pelajaran }}</td>
                            <td class="px-4 py-2 text-slate-500">{{ $n->periode?->tahun_ajaran }}</td>
                            <td class="px-4 py-2 text-slate-600">{{ $n->nilai_uts ?? '-' }}</td>
                            <td class="px-4 py-2 text-slate-600">{{ $n->nilai_uas ?? '-' }}</td>
                            <td class="px-4 py-2 font-bold text-indigo-600">{{ $n->nilai_akhir ?? '-' }}</td>
                            <td class="px-4 py-2"><span class="px-2 py-0.5 text-xs font-bold rounded-full bg-indigo-100 text-indigo-700">{{ $n->predikat ?? '-' }}</span></td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="px-4 py-6 text-center text-slate-400">Belum ada nilai.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- NAVIGASI BAWAH -->
    @include('partials.bottom-nav', ['active' => ''])

</div>
@endsection
