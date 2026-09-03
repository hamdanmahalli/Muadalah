@extends('layouts.app')

@section('title', 'Riwayat Siswa')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h2 class="text-2xl font-bold text-gray-800"><i class="fas fa-user-graduate mr-2 text-indigo-600"></i> Riwayat Murid</h2>
    <a href="{{ route('siswa-saya.index') }}" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-semibold hover:bg-gray-300 transition"><i class="fas fa-arrow-left mr-1"></i> Kembali</a>
</div>

<!-- Header murid -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
    <div class="flex items-center gap-4">
        @if($siswa->foto)
            <img src="{{ asset($siswa->foto) }}" class="w-16 h-16 rounded-full object-cover border-4 border-indigo-100">
        @else
            <div class="w-16 h-16 rounded-full bg-gradient-to-br from-indigo-400 to-indigo-600 flex items-center justify-center text-white text-2xl font-bold">{{ strtoupper(substr($siswa->nama_siswa,0,1)) }}</div>
        @endif
        <div>
            <h3 class="text-xl font-extrabold text-gray-800">{{ $siswa->nama_siswa }}</h3>
            <p class="text-sm text-gray-500">NIS: {{ $siswa->nis }} | {{ $siswa->kelasAktif()?->kelas?->nama_kelas ?? '-' }}</p>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <!-- Biodata singkat -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
        <h4 class="font-bold text-gray-700 mb-3"><i class="fas fa-user text-indigo-500 mr-2"></i> Biodata</h4>
        <dl class="space-y-2 text-sm">
            <div class="flex justify-between"><dt class="text-gray-400">TTL</dt><dd class="font-semibold text-gray-700">{{ $siswa->tempat_lahir }}, {{ $siswa->tanggal_lahir?->format('d-m-Y') }}</dd></div>
            <div class="flex justify-between"><dt class="text-gray-400">JK</dt><dd class="font-semibold text-gray-700">{{ $siswa->jenis_kelamin }}</dd></div>
            <div class="flex justify-between"><dt class="text-gray-400">Ayah</dt><dd class="font-semibold text-gray-700">{{ $siswa->nama_ayah }}</dd></div>
            <div class="flex justify-between"><dt class="text-gray-400">Ibu</dt><dd class="font-semibold text-gray-700">{{ $siswa->nama_ibu }}</dd></div>
            <div class="flex justify-between"><dt class="text-gray-400">HP Ortu</dt><dd class="font-semibold text-gray-700">{{ $siswa->no_hp_ortu }}</dd></div>
        </dl>
    </div>

    <!-- Rekap absensi -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
        <h4 class="font-bold text-gray-700 mb-3"><i class="fas fa-clipboard-check text-emerald-500 mr-2"></i> Rekap Absensi</h4>
        @php
            $rekabsen = $kehadiran->groupBy('status')->map->count();
        @endphp
        <div class="grid grid-cols-2 gap-2 text-sm">
            <div class="border border-emerald-100 rounded-lg p-3 bg-emerald-50"><p class="text-xs font-bold text-gray-400">Hadir</p><p class="font-bold text-emerald-600 text-lg">{{ $rekabsen['hadir'] ?? 0 }}</p></div>
            <div class="border border-amber-100 rounded-lg p-3 bg-amber-50"><p class="text-xs font-bold text-gray-400">Sakit</p><p class="font-bold text-amber-600 text-lg">{{ $rekabsen['sakit'] ?? 0 }}</p></div>
            <div class="border border-sky-100 rounded-lg p-3 bg-sky-50"><p class="text-xs font-bold text-gray-400">Izin</p><p class="font-bold text-sky-600 text-lg">{{ $rekabsen['izin'] ?? 0 }}</p></div>
            <div class="border border-rose-100 rounded-lg p-3 bg-rose-50"><p class="text-xs font-bold text-gray-400">Alpha</p><p class="font-bold text-rose-600 text-lg">{{ $rekabsen['alpha'] ?? 0 }}</p></div>
        </div>
    </div>

    <!-- Tagihan -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
        <h4 class="font-bold text-gray-700 mb-3"><i class="fas fa-file-invoice-dollar text-indigo-500 mr-2"></i> Tagihan</h4>
        @php $belum = $tagihans->filter(fn($t) => $t->sisa() > 0); @endphp
        <p class="text-sm text-gray-500 mb-2">Jumlah tagihan: <span class="font-bold text-gray-700">{{ $tagihans->count() }}</span></p>
        <p class="text-sm text-gray-500 mb-2">Tunggakan: <span class="font-bold text-rose-600">{{ $belum->count() }}</span></p>
        <p class="text-sm text-gray-500">Total sisa: <span class="font-bold text-rose-600">Rp {{ number_format($belum->sum(fn($t)=>$t->sisa()),0,',','.') }}</span></p>
    </div>
</div>

<!-- Nilai -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mt-6">
    <h4 class="font-bold text-gray-700 mb-4"><i class="fas fa-clipboard-list text-indigo-500 mr-2"></i> Nilai</h4>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left text-xs font-bold text-gray-500 uppercase">Pelajaran</th>
                    <th class="px-4 py-2 text-left text-xs font-bold text-gray-500 uppercase">Periode</th>
                    <th class="px-4 py-2 text-left text-xs font-bold text-gray-500 uppercase">UTS</th>
                    <th class="px-4 py-2 text-left text-xs font-bold text-gray-500 uppercase">UAS</th>
                    <th class="px-4 py-2 text-left text-xs font-bold text-gray-500 uppercase">Akhir</th>
                    <th class="px-4 py-2 text-left text-xs font-bold text-gray-500 uppercase">Predikat</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($nilai as $n)
                <tr>
                    <td class="px-4 py-2 font-semibold text-gray-700">{{ $n->pelajaran?->nama_pelajaran }}</td>
                    <td class="px-4 py-2 text-gray-500">{{ $n->periode?->tahun_ajaran }}</td>
                    <td class="px-4 py-2 text-gray-600">{{ $n->nilai_uts ?? '-' }}</td>
                    <td class="px-4 py-2 text-gray-600">{{ $n->nilai_uas ?? '-' }}</td>
                    <td class="px-4 py-2 font-bold text-indigo-600">{{ $n->nilai_akhir ?? '-' }}</td>
                    <td class="px-4 py-2"><span class="px-2 py-0.5 text-xs font-bold rounded-full bg-indigo-100 text-indigo-700">{{ $n->predikat ?? '-' }}</span></td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-4 py-6 text-center text-gray-400">Belum ada nilai.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
