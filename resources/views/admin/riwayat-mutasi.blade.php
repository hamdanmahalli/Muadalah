@extends('layouts.app')

@section('title', 'Riwayat Mutasi Jadwal')

@section('content')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
<style>
    .ts-control { border-radius: 0.5rem !important; border: 1px solid #e5e7eb !important; padding: 0.5rem 0.75rem !important; font-size: 0.875rem !important; background-color: #f9fafb !important; }
    .ts-control.focus { border-color: #00c0c7 !important; box-shadow: 0 0 0 3px rgba(0,192,199,0.15) !important; }
</style>

<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
    <div>
        <h2 class="text-2xl font-bold text-gray-800"><i class="fas fa-history mr-2 text-[#00c0c7]"></i> Riwayat Mutasi / Perubahan Jadwal</h2>
        <p class="text-xs text-gray-500 mt-0.5">Catatan otomatis setiap pergantian guru, tukar jam, dan perubahan jadwal.</p>
    </div>
    <a href="/riwayat-mutasi/kelola-tanggal" class="inline-flex items-center px-4 py-2.5 bg-[#00c0c7] hover:bg-[#00a8b8] text-white text-sm font-bold rounded-xl shadow-[0_8px_20px_rgba(0,192,199,0.3)] transition-all">
        <i class="fas fa-calendar-alt mr-2"></i> Kelola Tanggal Masa Berlaku
    </a>
</div>

@if(session('sukses'))
<div class="mb-6 px-4 py-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-sm font-bold shadow-sm flex items-center">
    <i class="fas fa-check-circle mr-2 text-emerald-500"></i> {{ session('sukses') }}
</div>
@endif

<!-- Kartu Statistik -->
<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-4 mb-6">
    <div class="bg-white border-b-4 border-[#00c0c7] rounded-xl shadow-sm p-4">
        <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Total Perubahan</span>
        <div class="text-2xl font-black text-gray-800 mt-1">{{ $statistik['total'] }}</div>
    </div>
    <div class="bg-white border-b-4 border-indigo-500 rounded-xl shadow-sm p-4">
        <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Ganti Guru</span>
        <div class="text-2xl font-black text-indigo-600 mt-1">{{ $statistik['ganti_guru'] }}</div>
    </div>
    <div class="bg-white border-b-4 border-amber-500 rounded-xl shadow-sm p-4">
        <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Tukar / Pindah</span>
        <div class="text-2xl font-black text-amber-600 mt-1">{{ $statistik['tukar_jam'] }}</div>
    </div>
    <div class="bg-white border-b-4 border-rose-500 rounded-xl shadow-sm p-4">
        <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Hapus Slot</span>
        <div class="text-2xl font-black text-rose-600 mt-1">{{ $statistik['hapus_slot'] }}</div>
    </div>
    <div class="bg-white border-b-4 border-sky-500 rounded-xl shadow-sm p-4">
        <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Perubahan Plot</span>
        <div class="text-2xl font-black text-sky-600 mt-1">{{ $statistik['plot_sync'] }}</div>
    </div>
</div>

<!-- Filter -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6">
    <form method="GET" action="/riwayat-mutasi" class="grid grid-cols-1 md:grid-cols-5 gap-3 items-end">
        <div>
            <label class="block text-xs font-bold text-gray-500 mb-1.5 uppercase tracking-wider">Tipe</label>
            <select name="tipe" class="w-full border border-gray-300 rounded-lg p-2 text-sm font-medium text-gray-700 bg-white">
                <option value="">Semua Tipe</option>
                <option value="ganti_guru" {{ $tipeFilter=='ganti_guru'?'selected':'' }}>Ganti Guru</option>
                <option value="tukar_jam" {{ $tipeFilter=='tukar_jam'?'selected':'' }}>Tukar Jam</option>
                <option value="pindah_blok" {{ $tipeFilter=='pindah_blok'?'selected':'' }}>Pindah Blok</option>
                <option value="hapus_slot" {{ $tipeFilter=='hapus_slot'?'selected':'' }}>Hapus Slot</option>
                <option value="plot_sync" {{ $tipeFilter=='plot_sync'?'selected':'' }}>Perubahan Plot</option>
            </select>
        </div>
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
        <div>
            <label class="block text-xs font-bold text-gray-500 mb-1.5 uppercase tracking-wider">Periode</label>
            <select name="periode_id" class="w-full border border-gray-300 rounded-lg p-2 text-sm font-medium text-gray-700 bg-white">
                <option value="">Periode Aktif</option>
                @foreach($periodeList as $p)
                    <option value="{{ $p->id }}" {{ $periodeFilter==$p->id?'selected':'' }}>{{ $p->tahun_ajaran }} ({{ $p->semester }})</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-bold text-gray-500 mb-1.5 uppercase tracking-wider">Cari</label>
            <input type="text" name="cari" value="{{ $cari }}" placeholder="Mapel / guru / kelas..." class="w-full border border-gray-300 rounded-lg p-2 text-sm font-medium text-gray-700 bg-white">
        </div>
        <div class="md:col-span-5 flex justify-end gap-2">
            <button type="submit" class="px-5 py-2 bg-[#00c0c7] hover:bg-[#00a8b8] text-white text-sm font-bold rounded-xl shadow-sm transition">
                <i class="fas fa-filter mr-1"></i> Terapkan
            </button>
            <a href="/riwayat-mutasi" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-bold rounded-xl transition">Reset</a>
        </div>
    </form>
</div>

<!-- Daftar Riwayat -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-100">
        <thead class="bg-gray-50/80">
            <tr>
                <th class="px-5 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Tanggal</th>
                <th class="px-5 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Tipe</th>
                <th class="px-5 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Pelajaran / Kelas</th>
                <th class="px-5 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Perubahan Guru</th>
                <th class="px-5 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Tanggal Efektif</th>
                <th class="px-5 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Keterangan</th>
                <th class="px-5 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Oleh</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-100">
            @forelse($riwayat as $r)
            <tr class="hover:bg-[#00c0c7]/5 transition">
                <td class="px-5 py-3 whitespace-nowrap text-sm font-semibold text-gray-700">
                    {{ $r->tanggal_kejadian ? $r->tanggal_kejadian->translatedFormat('d M Y') : '-' }}
                </td>
                <td class="px-5 py-3 whitespace-nowrap">
                    @php
                        $badge = match($r->tipe) {
                            'ganti_guru' => 'bg-indigo-100 text-indigo-700',
                            'tukar_jam' => 'bg-amber-100 text-amber-700',
                            'pindah_blok' => 'bg-amber-100 text-amber-700',
                            'hapus_slot' => 'bg-rose-100 text-rose-700',
                            'plot_sync' => 'bg-sky-100 text-sky-700',
                            default => 'bg-gray-100 text-gray-600',
                        };
                    @endphp
                    <span class="px-3 py-1 text-[11px] font-bold rounded-full {{ $badge }}">{{ \App\Models\MutasiJadwal::labelTipe($r->tipe) }}</span>
                </td>
                <td class="px-5 py-3">
                    <span class="text-sm font-bold text-gray-800">{{ $r->pelajaran?->nama_pelajaran ?? '-' }}</span>
                    <span class="block text-xs text-gray-400 font-medium">Kelas {{ $r->kelas?->nama_kelas ?? '-' }} @if($r->hari) · {{ $r->hari }} @if($r->jam_ke) Jam {{ $r->jam_ke }} @endif @endif</span>
                </td>
                <td class="px-5 py-3">
                    @if($r->tipe == 'hapus_slot')
                        <span class="inline-flex items-center text-sm font-semibold text-rose-600"><i class="fas fa-trash-alt mr-1.5 text-xs"></i> Slot dikosongkan</span>
                    @elseif($r->guru_lama_id == $r->guru_baru_id)
                        <span class="inline-flex items-center text-sm font-semibold text-gray-700"><i class="fas fa-arrows-alt-h mr-1.5 text-xs text-amber-500"></i>{{ $r->guruBaru?->nama_guru ?? '-' }}</span>
                    @else
                        <span class="inline-flex flex-col">
                            <span class="text-sm text-gray-400 line-through font-medium">{{ $r->guruLama?->nama_guru ?? '-' }}</span>
                            <span class="text-sm font-bold text-gray-800"><i class="fas fa-arrow-right text-[#00c0c7] mr-1"></i>{{ $r->guruBaru?->nama_guru ?? '-' }}</span>
                        </span>
                    @endif
                </td>
                <td class="px-5 py-3 whitespace-nowrap text-sm font-medium text-gray-600">
                    {{ $r->tanggal_efektif ? $r->tanggal_efektif->format('d M Y') : '-' }}
                </td>
                <td class="px-5 py-3 text-xs text-gray-500 font-medium max-w-[220px]">{{ $r->keterangan ?? '-' }}</td>
                <td class="px-5 py-3 whitespace-nowrap text-xs text-gray-500">{{ $r->user?->name ?? '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="px-5 py-12 text-center">
                    <div class="flex flex-col items-center text-gray-300">
                        <i class="fas fa-inbox text-5xl mb-3"></i>
                        <p class="text-sm font-bold text-gray-400">Belum ada riwayat mutasi/perubahan jadwal.</p>
                        <p class="text-xs text-gray-400 mt-1">Riwayat akan tercatat otomatis saat guru diganti, jam ditukar, atau slot dihapus.</p>
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
