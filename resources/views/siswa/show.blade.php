@extends('layouts.app')

@section('title', 'Detail Siswa')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h2 class="text-2xl font-bold text-gray-800"><i class="fas fa-user-graduate mr-2 text-indigo-600"></i> Detail Siswa</h2>
    <div class="flex gap-2">
        <a href="{{ route('siswa.lengkapi', $siswa->id) }}" class="bg-orange-500 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-orange-600 transition"><i class="fas fa-edit mr-1"></i> Lengkapi Data</a>
        <a href="{{ route('master-siswa.index') }}" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-semibold hover:bg-gray-300 transition"><i class="fas fa-arrow-left mr-1"></i> Kembali</a>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <!-- Biodata -->
    <div class="md:col-span-2 bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center gap-4 mb-6">
            @if($siswa->foto)
                <img src="{{ asset($siswa->foto) }}" class="w-20 h-20 rounded-full object-cover border-4 border-indigo-100">
            @else
                <div class="w-20 h-20 rounded-full bg-gradient-to-br from-indigo-400 to-indigo-600 flex items-center justify-center text-white text-3xl font-bold">
                    {{ strtoupper(substr($siswa->nama_siswa,0,1)) }}
                </div>
            @endif
            <div>
                <h3 class="text-xl font-extrabold text-gray-800">{{ $siswa->nama_siswa }}</h3>
                <p class="text-sm text-gray-500">NIS: {{ $siswa->nis }} @if($siswa->nisn) | NISN: {{ $siswa->nisn }} @endif</p>
                <span class="inline-block mt-1 px-2 py-0.5 text-xs font-bold rounded-full {{ $siswa->status === 'Aktif' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">{{ $siswa->status }}</span>
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 gap-4 text-sm">
            <div><p class="text-xs font-bold text-gray-400 uppercase">Jenis Kelamin</p><p class="font-semibold text-gray-700">{{ $siswa->jenis_kelamin == 'L' ? 'Laki-laki' : ($siswa->jenis_kelamin == 'P' ? 'Perempuan' : '-') }}</p></div>
            <div><p class="text-xs font-bold text-gray-400 uppercase">Tempat, Tgl Lahir</p><p class="font-semibold text-gray-700">{{ $siswa->tempat_lahir ? $siswa->tempat_lahir . ',' : '' }} {{ $siswa->tanggal_lahir ? $siswa->tanggal_lahir->format('d-m-Y') : '-' }}</p></div>
            <div><p class="text-xs font-bold text-gray-400 uppercase">Agama</p><p class="font-semibold text-gray-700">{{ $siswa->agama ?? '-' }}</p></div>
            <div><p class="text-xs font-bold text-gray-400 uppercase">Tahun Masuk</p><p class="font-semibold text-gray-700">{{ $siswa->tahun_masuk ?? '-' }}</p></div>
            <div class="col-span-2"><p class="text-xs font-bold text-gray-400 uppercase">Alamat</p><p class="font-semibold text-gray-700">{{ $siswa->alamat ?? '-' }}</p></div>
        </div>

        <div class="border-t border-gray-100 mt-5 pt-4">
            <p class="text-xs font-bold text-gray-400 uppercase mb-3">Data Orang Tua</p>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4 text-sm">
                <div><p class="text-xs font-bold text-gray-400 uppercase">Ayah</p><p class="font-semibold text-gray-700">{{ $siswa->nama_ayah ?? '-' }}</p></div>
                <div><p class="text-xs font-bold text-gray-400 uppercase">Ibu</p><p class="font-semibold text-gray-700">{{ $siswa->nama_ibu ?? '-' }}</p></div>
                <div><p class="text-xs font-bold text-gray-400 uppercase">Pekerjaan Ortu</p><p class="font-semibold text-gray-700">{{ $siswa->pekerjaan_ortu ?? '-' }}</p></div>
                <div><p class="text-xs font-bold text-gray-400 uppercase">No. HP Ortu</p><p class="font-semibold text-gray-700">{{ $siswa->no_hp_ortu ?? '-' }}</p></div>
            </div>
        </div>
    </div>

    <!-- Riwayat penempatan -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h4 class="font-bold text-gray-700 mb-4"><i class="fas fa-school text-indigo-500 mr-2"></i> Riwayat Kelas / Periode</h4>
        @forelse($siswa->angkatan->sortByDesc('periode.tahun_ajaran') as $a)
            <div class="flex items-center justify-between border border-gray-100 rounded-lg p-3 mb-2">
                <div>
                    <p class="font-bold text-gray-700 text-sm">{{ $a->kelas?->nama_kelas }}</p>
                    <p class="text-xs text-gray-400">{{ $a->periode?->tahun_ajaran }} {{ $a->periode?->semester }}</p>
                </div>
                <span class="text-xs font-bold {{ $a->status === 'Aktif' ? 'text-emerald-600' : 'text-rose-500' }}">{{ $a->status }}</span>
            </div>
        @empty
            <p class="text-sm text-gray-400">Belum ada penempatan kelas.</p>
        @endforelse
    </div>
</div>

<!-- Ringkasan tagihan -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mt-6">
    <h4 class="font-bold text-gray-700 mb-4"><i class="fas fa-file-invoice text-indigo-500 mr-2"></i> Tagihan</h4>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left text-xs font-bold text-gray-500 uppercase">Jenis</th>
                    <th class="px-4 py-2 text-left text-xs font-bold text-gray-500 uppercase">Nominal</th>
                    <th class="px-4 py-2 text-left text-xs font-bold text-gray-500 uppercase">Dibayar</th>
                    <th class="px-4 py-2 text-left text-xs font-bold text-gray-500 uppercase">Sisa</th>
                    <th class="px-4 py-2 text-left text-xs font-bold text-gray-500 uppercase">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($siswa->tagihans->sortByDesc('created_at')->take(10) as $t)
                <tr>
                    <td class="px-4 py-2 font-semibold text-gray-700">{{ $t->jenisTagihan?->nama_tagihan }}</td>
                    <td class="px-4 py-2 text-gray-600">{{ number_format($t->nominal,0,',','.') }}</td>
                    <td class="px-4 py-2 text-emerald-600 font-semibold">{{ number_format($t->totalDibayar(),0,',','.') }}</td>
                    <td class="px-4 py-2 text-rose-500 font-semibold">{{ number_format($t->sisa(),0,',','.') }}</td>
                    <td class="px-4 py-2">
                        @if($t->status === 'lunas')
                            <span class="px-2 py-0.5 text-xs font-bold rounded-full bg-emerald-100 text-emerald-700">Lunas</span>
                        @elseif($t->status === 'parsial')
                            <span class="px-2 py-0.5 text-xs font-bold rounded-full bg-amber-100 text-amber-700">Parsial</span>
                        @else
                            <span class="px-2 py-0.5 text-xs font-bold rounded-full bg-rose-100 text-rose-700">Belum</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-4 py-6 text-center text-gray-400">Tidak ada tagihan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
