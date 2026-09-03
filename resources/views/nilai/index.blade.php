@extends('layouts.app')

@section('title', 'Input Nilai Siswa')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h2 class="text-2xl font-bold text-gray-800"><i class="fas fa-clipboard-list mr-2 text-indigo-600"></i> Input Nilai</h2>
</div>

<form method="GET" action="{{ route('nilai.index') }}" class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-3 text-sm">
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Periode</label>
            <select name="periode_id" onchange="this.form.submit()" class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-indigo-500 outline-none">
                @foreach($periodes as $p)
                    <option value="{{ $p->id }}" {{ $periodeId == $p->id ? 'selected' : '' }}>{{ $p->tahun_ajaran }} ({{ $p->semester }})</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Kelas <span class="text-red-500">*</span></label>
            <select name="kelas_id" onchange="this.form.submit()" class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-indigo-500 outline-none">
                <option value="">- Pilih Kelas -</option>
                @foreach($kelas as $k)
                    <option value="{{ $k->id }}" {{ $kelasId == $k->id ? 'selected' : '' }}>{{ $k->nama_kelas }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Pelajaran <span class="text-red-500">*</span></label>
            <select name="pelajaran_id" onchange="this.form.submit()" class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-indigo-500 outline-none">
                <option value="">- Pilih Pelajaran -</option>
                @foreach($pelajarans as $pl)
                    <option value="{{ $pl->id }}" {{ $pelajaranId == $pl->id ? 'selected' : '' }}>{{ $pl->nama_pelajaran }}</option>
                @endforeach
            </select>
        </div>
    </div>
</form>

@if($kelasId && $pelajaranId)
<form method="POST" action="{{ route('nilai.simpanMassal') }}">
    @csrf
    <input type="hidden" name="kelas_id" value="{{ $kelasId }}">
    <input type="hidden" name="pelajaran_id" value="{{ $pelajaranId }}">
    <input type="hidden" name="periode_id" value="{{ $periodeId }}">

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase w-8">No</th>
                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Nama Siswa</th>
                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase w-24">NIS</th>
                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase w-32">Nilai UTS</th>
                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase w-32">Nilai UAS</th>
                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase w-32">Nilai Akhir</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($siswas as $i => $s)
                @php $n = $nilaiMap[$s->id] ?? null; @endphp
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-2 text-gray-500">{{ $i+1 }}</td>
                    <td class="px-4 py-2 font-semibold text-gray-700">{{ $s->nama_siswa }}</td>
                    <td class="px-4 py-2 text-gray-500">{{ $s->nis }}</td>
                    <td class="px-2 py-2">
                        <input type="number" step="0.01" min="0" max="100" name="siswa[{{ $s->id }}][uts]" value="{{ $n?->nilai_uts }}" class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-indigo-500 outline-none">
                    </td>
                    <td class="px-2 py-2">
                        <input type="number" step="0.01" min="0" max="100" name="siswa[{{ $s->id }}][uas]" value="{{ $n?->nilai_uas }}" class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-indigo-500 outline-none">
                    </td>
                    <td class="px-2 py-2">
                        <span class="font-bold text-indigo-600">{{ $n?->nilai_akhir ?? '-' }}</span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-4 py-10 text-center text-gray-400">Pilih kelas dan pelajaran terlebih dahulu.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($siswas->isNotEmpty())
    <div class="mt-4 flex justify-end">
        <button type="submit" class="bg-indigo-600 text-white px-6 py-2.5 rounded-lg text-sm font-semibold hover:bg-indigo-700 transition shadow-sm">
            <i class="fas fa-save mr-1"></i> Simpan Semua Nilai (UTS+UAS → Akhir)
        </button>
    </div>
    <p class="text-xs text-gray-400 mt-2">Nilai Akhir dihitung otomatis: rata-rata UTS dan UAS.</p>
    @endif
</form>
@endif
@endsection
