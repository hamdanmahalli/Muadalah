@extends('layouts.app')

@section('title', 'Raport')

@section('content')
<div class="flex flex-col md:flex-row md:justify-between md:items-center gap-3 mb-6">
    <h2 class="text-2xl font-bold text-gray-800"><i class="fas fa-file-alt mr-2 text-indigo-600"></i> Raport Siswa</h2>
</div>

<form method="GET" action="{{ route('raport.index') }}" class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-sm">
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Periode</label>
            <select name="periode_id" onchange="this.form.submit()" class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-indigo-500 outline-none">
                @foreach($periodes as $p)
                    <option value="{{ $p->id }}" {{ $periodeId == $p->id ? 'selected' : '' }}>{{ $p->tahun_ajaran }} ({{ $p->semester }})</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Kelas</label>
            <select name="kelas_id" onchange="this.form.submit()" class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-indigo-500 outline-none">
                <option value="">- Semua Kelas -</option>
                @foreach($kelas as $k)
                    <option value="{{ $k->id }}" {{ $kelasId == $k->id ? 'selected' : '' }}>{{ $k->nama_kelas }}</option>
                @endforeach
            </select>
        </div>
    </div>
</form>

<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">NIS</th>
                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Nama</th>
                    <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase">Cetak Raport</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($siswas as $s)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-gray-500 font-bold">{{ $s->nis }}</td>
                    <td class="px-4 py-3 font-semibold text-gray-700">{{ $s->nama_siswa }}</td>
                    <td class="px-4 py-3 text-center">
                        <a href="{{ route('raport.cetak', $s->id) }}?periode_id={{ $periodeId }}" target="_blank" class="inline-flex items-center gap-1 bg-indigo-600 text-white px-4 py-2 rounded-lg text-xs font-semibold hover:bg-indigo-700 transition">
                            <i class="fas fa-print"></i> Cetak PDF
                        </a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="3" class="px-4 py-10 text-center text-gray-400">Pilih kelas untuk menampilkan siswa.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
