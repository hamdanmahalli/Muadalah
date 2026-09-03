@extends('layouts.app')

@section('title', 'Absensi Siswa')

@section('content')
<div class="flex flex-col md:flex-row md:justify-between md:items-center gap-3 mb-6">
    <h2 class="text-2xl font-bold text-gray-800"><i class="fas fa-clipboard-check mr-2 text-emerald-600"></i> Absensi Siswa</h2>
</div>

<!-- Filter -->
<form method="GET" action="{{ route('absen-siswa.index') }}" class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-3 text-sm">
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Periode</label>
            <select name="periode_id" onchange="this.form.submit()" class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-emerald-500 outline-none">
                @foreach($periodes as $p)
                    <option value="{{ $p->id }}" {{ $periodeId == $p->id ? 'selected' : '' }}>{{ $p->tahun_ajaran }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Kelas <span class="text-red-500">*</span></label>
            <select name="kelas_id" onchange="this.form.submit()" class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-emerald-500 outline-none">
                <option value="">- Pilih Kelas -</option>
                @foreach($kelas as $k)
                    <option value="{{ $k->id }}" {{ $kelasId == $k->id ? 'selected' : '' }}>{{ $k->nama_kelas }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Tanggal</label>
            <input type="date" name="tanggal" value="{{ $tanggal }}" class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-emerald-500 outline-none">
        </div>
        <div class="flex items-end">
            <button type="submit" class="bg-emerald-600 text-white px-5 py-2.5 rounded-lg text-sm font-semibold hover:bg-emerald-700"><i class="fas fa-search mr-1"></i> Tampilkan</button>
        </div>
    </div>
</form>

@if($kelasId)
<form method="POST" action="{{ route('absen-siswa.store') }}">
    @csrf
    <input type="hidden" name="kelas_id" value="{{ $kelasId }}">
    <input type="hidden" name="periode_id" value="{{ $periodeId }}">
    <input type="hidden" name="tanggal" value="{{ $tanggal }}">

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden overflow-x-auto">
        <div class="p-4 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h3 class="font-bold text-gray-700">Absensi {{ $tanggal }}</h3>
                <p class="text-xs text-gray-400">Status: <span class="text-emerald-600 font-bold">H</span>=Hadir, <span class="text-amber-600 font-bold">S</span>=Sakit, <span class="text-sky-600 font-bold">I</span>=Izin, <span class="text-rose-600 font-bold">A</span>=Alpha</p>
            </div>
            @if($siswas->isNotEmpty())
            <a href="{{ route('absen-siswa.cetak', ['kelas_id' => $kelasId, 'tanggal_awal' => \Carbon\Carbon::parse($tanggal)->startOfWeek()->format('Y-m-d'), 'tanggal_akhir' => $tanggal, 'periode_id' => $periodeId]) }}" target="_blank" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-indigo-700 transition">
                <i class="fas fa-print mr-1"></i> Cetak Absen Mingguan
            </a>
            @endif
        </div>
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase w-8">No.Absen</th>
                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Nama Siswa</th>
                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase w-24">NIS</th>
                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Keterangan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($siswas as $i => $s)
                @php $k = $kehadiranMap[$s->id] ?? null; $status = $k?->status ?? 'hadir'; @endphp
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-2 text-gray-500">{{ $s->angkatan->first()?->nomor_absen ?? $i+1 }}</td>
                    <td class="px-4 py-2 font-semibold text-gray-700">{{ $s->nama_siswa }}</td>
                    <td class="px-4 py-2 text-gray-500">{{ $s->nis }}</td>
                    <td class="px-4 py-2">
                        <select name="status[{{ $s->id }}]" class="border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-emerald-500 outline-none">
                            <option value="hadir" {{ $status=='hadir'?'selected':'' }}>Hadir</option>
                            <option value="sakit" {{ $status=='sakit'?'selected':'' }}>Sakit</option>
                            <option value="izin" {{ $status=='izin'?'selected':'' }}>Izin</option>
                            <option value="alpha" {{ $status=='alpha'?'selected':'' }}>Alpha</option>
                        </select>
                    </td>
                    <td class="px-4 py-2">
                        <input type="text" name="keterangan[{{ $s->id }}]" value="{{ $k?->keterangan }}" class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-emerald-500 outline-none">
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-4 py-10 text-center text-gray-400">Pilih kelas terlebih dahulu.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($siswas->isNotEmpty())
    <div class="mt-4 flex justify-end">
        <button type="submit" class="bg-emerald-600 text-white px-6 py-2.5 rounded-lg text-sm font-semibold hover:bg-emerald-700 transition shadow-sm">
            <i class="fas fa-save mr-1"></i> Simpan Absensi
        </button>
    </div>
    @endif
</form>
@endif
@endsection
