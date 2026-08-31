@extends('layouts.app')

@section('title', 'Kelola Tanggal Masa Berlaku Jadwal')

@section('content')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
<style>
    .ts-control { border-radius: 0.5rem !important; border: 1px solid #e5e7eb !important; padding: 0.5rem 0.75rem !important; font-size: 0.875rem !important; background-color: #f9fafb !important; }
    .ts-control.focus { border-color: #00c0c7 !important; box-shadow: 0 0 0 3px rgba(0,192,199,0.15) !important; }
</style>

<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
    <div>
        <h2 class="text-2xl font-bold text-gray-800"><i class="fas fa-calendar-alt mr-2 text-[#00c0c7]"></i> Kelola Tanggal Masa Berlaku Jadwal</h2>
        <p class="text-xs text-gray-500 mt-0.5">Perbaiki / atur tanggal efektif (<code class="bg-gray-100 px-1 rounded">berlaku_mulai</code> & <code class="bg-gray-100 px-1 rounded">berlaku_sampai</code>) tiap slot jadwal sesuai kebutuhan.</p>
    </div>
    <a href="/riwayat-mutasi" class="inline-flex items-center px-4 py-2.5 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 text-sm font-bold rounded-xl shadow-sm transition-all">
        <i class="fas fa-arrow-left mr-2"></i> Kembali ke Riwayat
    </a>
</div>

@if(session('sukses'))
<div class="mb-6 px-4 py-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-sm font-bold shadow-sm flex items-center">
    <i class="fas fa-check-circle mr-2 text-emerald-500"></i> {{ session('sukses') }}
</div>
@endif

<!-- Filter -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6">
    <form method="GET" action="/riwayat-mutasi/kelola-tanggal" class="grid grid-cols-1 md:grid-cols-3 gap-3 items-end">
        <div>
            <label class="block text-xs font-bold text-gray-500 mb-1.5 uppercase tracking-wider">Kelas</label>
            <select name="kelas_id" class="searchable w-full">
                <option value="">Semua Kelas</option>
                @foreach($kelasList as $k)
                    <option value="{{ $k->id }}" {{ $kelasFilter==$k->id?'selected':'' }}>Kelas {{ $k->nama_kelas }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-bold text-gray-500 mb-1.5 uppercase tracking-wider">Guru</label>
            <select name="guru_id" class="searchable w-full">
                <option value="">Semua Guru</option>
                @foreach($guruList as $g)
                    <option value="{{ $g->id }}" {{ $guruFilter==$g->id?'selected':'' }}>{{ $g->nama_guru }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-end gap-2">
            <label class="flex items-center text-sm font-semibold text-gray-600 bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 h-[42px] cursor-pointer">
                <input type="checkbox" name="semua" value="1" {{ request('semua')?'checked':'' }} class="mr-2 text-[#00c0c7]"> Tampilkan semua tahun
            </label>
            <button type="submit" class="px-4 py-2 bg-[#00c0c7] hover:bg-[#00a8b8] text-white text-sm font-bold rounded-xl shadow-sm transition">
                <i class="fas fa-filter mr-1"></i> Filter
            </button>
        </div>
    </form>
</div>

@if($tahunAjaran && !request('semua'))
    <p class="text-xs text-gray-500 mb-4"><i class="fas fa-info-circle text-[#00c0c7] mr-1"></i> Menampilkan jadwal periode aktif: <strong>{{ $tahunAjaran }}</strong></p>
@endif

<!-- Daftar Jadwal -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-100">
        <thead class="bg-gray-50/80">
            <tr>
                <th class="px-5 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider w-10">#</th>
                <th class="px-5 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Kelas</th>
                <th class="px-5 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Pelajaran</th>
                <th class="px-5 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Hari / Jam</th>
                <th class="px-5 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Guru</th>
                <th class="px-5 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Berlaku Mulai</th>
                <th class="px-5 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Berlaku Sampai</th>
                <th class="px-5 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Aksi</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-100">
            @forelse($jadwal as $i => $j)
            <tr class="hover:bg-[#00c0c7]/5 transition align-top">
                <td class="px-5 py-3 text-sm text-gray-400 font-medium">{{ $i + 1 }}</td>
                <td class="px-5 py-3 text-sm font-bold text-gray-800">Kelas {{ $j->kelas?->nama_kelas ?? '-' }}</td>
                <td class="px-5 py-3 text-sm font-medium text-gray-700">{{ $j->pelajaran?->nama_pelajaran ?? '-' }}</td>
                <td class="px-5 py-3 text-sm text-gray-600">{{ $j->hari ?? '-' }} · Jam {{ $j->jam_ke ?? '-' }}</td>
                <td class="px-5 py-3 text-sm">{{ $j->guru?->nama_guru ?? '-' }}</td>
                <form method="POST" action="/riwayat-mutasi/kelola-tanggal">
                    @csrf
                    <input type="hidden" name="jadwal_id" value="{{ $j->id }}">
                    <td class="px-2 py-3">
                        <input type="date" name="berlaku_mulai" value="{{ $j->berlaku_mulai ? \Carbon\Carbon::parse($j->berlaku_mulai)->format('Y-m-d') : '' }}" class="w-full border border-gray-200 rounded-lg p-1.5 text-sm text-gray-700 bg-white focus:ring-2 focus:ring-[#00c0c7] focus:border-[#00c0c7] outline-none">
                    </td>
                    <td class="px-2 py-3">
                        <input type="date" name="berlaku_sampai" value="{{ $j->berlaku_sampai ? \Carbon\Carbon::parse($j->berlaku_sampai)->format('Y-m-d') : '' }}" class="w-full border border-gray-200 rounded-lg p-1.5 text-sm text-gray-700 bg-white focus:ring-2 focus:ring-[#00c0c7] focus:border-[#00c0c7] outline-none">
                    </td>
                    <td class="px-2 py-3">
                        <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-[#00c0c7] hover:bg-[#00a8b8] text-white text-xs font-bold rounded-lg transition">
                            <i class="fas fa-save mr-1"></i> Simpan
                        </button>
                    </td>
                </form>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="px-5 py-12 text-center">
                    <div class="flex flex-col items-center text-gray-300">
                        <i class="fas fa-calendar-times text-5xl mb-3"></i>
                        <p class="text-sm font-bold text-gray-400">Tidak ada jadwal yang cocok.</p>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        document.querySelectorAll('.searchable').forEach(el => new TomSelect(el, { create: false }));
    });
</script>
@endsection
