@extends('layouts.app')

@section('title', 'Siswa Saya')

@section('content')
<div class="flex flex-col md:flex-row md:justify-between md:items-center gap-3 mb-6">
    <h2 class="text-2xl font-bold text-gray-800"><i class="fas fa-user-graduate mr-2 text-indigo-600"></i> Siswa Saya</h2>
</div>

@if($kelasWali->isEmpty())
    <div class="bg-white rounded-xl shadow-sm border border-amber-200 p-8 text-center">
        <i class="fas fa-user-cog text-5xl text-amber-400 mb-4"></i>
        <h3 class="font-bold text-gray-700 text-lg mb-1">Belum Ada Kelas yang Diampu</h3>
        <p class="text-sm text-gray-400">Anda belum ditetapkan sebagai wali kelas. Silakan hubungi admin untuk menetapkan kelas wali Anda.</p>
    </div>
@else
    <!-- Pilih kelas wali -->
    <form method="GET" action="{{ route('siswa-saya.index') }}" class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Kelas Wali Saya</label>
                <select name="kelas_id" onchange="this.form.submit()" class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-indigo-500 outline-none">
                    @foreach($kelasWali as $kl)
                        <option value="{{ $kl->id }}" {{ $kelasId == $kl->id ? 'selected' : '' }}>{{ $kl->nama_kelas }} ({{ $kl->tingkat }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Periode</label>
                <select name="periode_id" onchange="this.form.submit()" class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-indigo-500 outline-none">
                    <option value="">- Periode Aktif -</option>
                </select>
            </div>
        </div>
    </form>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden overflow-x-auto">
        <div class="p-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-bold text-gray-700">Daftar Murid <span class="text-gray-400 text-sm font-normal">{{ $kelasWali->firstWhere('id',$kelasId)?->nama_kelas }}</span></h3>
        </div>
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase w-8">No</th>
                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">NIS</th>
                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Nama</th>
                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">JK</th>
                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Tagihan Belum Lunas</th>
                    <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($siswas as $i => $s)
                @php
                    $belum = $s->tagihans->filter(fn($t) => $t->sisa() > 0);
                @endphp
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-gray-500">{{ $i+1 }}</td>
                    <td class="px-4 py-3 font-bold text-gray-700">{{ $s->nis }}</td>
                    <td class="px-4 py-3 font-semibold text-gray-800">{{ $s->nama_siswa }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $s->jenis_kelamin }}</td>
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
                <tr><td colspan="6" class="px-4 py-10 text-center text-gray-400">Belum ada murid di kelas ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endif
@endsection
