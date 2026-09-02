@extends('layouts.app')
@section('title', 'Pabrik Barcode Kelas')

@section('content')
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3 mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800"><i class="fas fa-qrcode mr-2 text-indigo-600"></i> Pabrik Barcode Kehadiran</h2>
            <p class="text-sm text-gray-500 mt-1">Sistem Keamanan Anti-Kecurangan (Barcode berganti setiap hari Sabtu)</p>
        </div>
        <div class="bg-indigo-50 text-indigo-700 px-4 py-2 rounded-xl text-sm font-bold border border-indigo-100 shadow-sm flex items-center sm:shrink-0">
            <i class="far fa-calendar-alt mr-2"></i> Berlaku: {{ $periodeBerlaku }}
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden overflow-x-auto mb-8">
        <table class="min-w-full divide-y divide-gray-100">
            <thead class="bg-gray-50/80">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider w-16">No</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Nama Kelas</th>
                    <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider w-40">Status Keamanan</th>
                    <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider w-48">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                @foreach($kelas as $index => $k)
                <tr class="hover:bg-gray-50/50 transition">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 font-bold">{{ $index + 1 }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-lg font-black text-gray-800">{{ $k->nama_kelas }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                        <span class="px-3 py-1 text-[10px] uppercase tracking-wider font-bold rounded-full bg-emerald-100 text-emerald-700"><i class="fas fa-shield-alt mr-1"></i> Terenkripsi</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                        <a href="/pabrik-barcode/cetak/{{ $k->id }}" target="_blank" class="inline-flex items-center justify-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-bold shadow-sm transition">
                            <i class="fas fa-print mr-2"></i> Cetak QR
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection