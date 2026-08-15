@extends('layouts.app')

@section('title', 'Plotting Target Mengajar')

@section('content')
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800"><i class="fas fa-sitemap mr-2 text-indigo-600"></i> Plotting Target Mengajar</h2>
    </div>

    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 mb-6">
        <form action="/master-plot-jadwal" method="GET" class="flex items-end space-x-4">
            <div class="w-1/3">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Pilih Kelas yang Ingin Diatur:</label>
                <select name="kelas_id" class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-indigo-500 outline-none font-medium text-gray-700" onchange="this.form.submit()">
                    <option value="">-- Pilih Kelas --</option>
                    @foreach($kelas as $k)
                        <option value="{{ $k->id }}" {{ $kelas_id == $k->id ? 'selected' : '' }}>{{ $k->nama_kelas }}</option>
                    @endforeach
                </select>
            </div>
            @if(!$kelas_id)
                <div class="text-sm text-gray-400 italic mb-3">Silakan pilih kelas terlebih dahulu untuk memunculkan matriks pelajaran.</div>
            @endif
        </form>
    </div>

    @if($kelas_id)
    <form action="/master-plot-jadwal" method="POST">
        @csrf
        <input type="hidden" name="kelas_id" value="{{ $kelas_id }}">
        
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden overflow-x-auto mb-6">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase w-16">No</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase w-1/4">Mata Pelajaran / Kitab</th>
                        <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase w-32">Beban Jam</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase">Guru Pengajar</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($pelajarans as $index => $pelajaran)
                        @php 
                            // Cek apakah pelajaran ini sudah ada di database plot
                            $plot = $plotAktif->get($pelajaran->id);
                        @endphp
                        <tr class="hover:bg-indigo-50 transition">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $index + 1 }}</td>
                            <td class="px-6 py-4 whitespace-normal">
                                <div class="font-bold text-gray-800">{{ $pelajaran->nama_pelajaran }}</div>
                                <div class="text-xs text-gray-500">{{ $pelajaran->nama_kitab ?? '-' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <input type="number" name="plots[{{ $pelajaran->id }}][beban_jam]" value="{{ $plot ? $plot->beban_jam : 2 }}" min="0" class="w-20 border border-gray-300 rounded-lg p-2 text-center focus:ring-2 focus:ring-indigo-500 outline-none">
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <select name="plots[{{ $pelajaran->id }}][guru_id]" class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-indigo-500 outline-none">
                                    <option value="">-- Kosong / Belum Ada Guru --</option>
                                    @foreach($gurus as $guru)
                                        <option value="{{ $guru->id }}" {{ ($plot && $plot->guru_id == $guru->id) ? 'selected' : '' }}>
                                            {{ $guru->nama_guru }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            
            <div class="bg-gray-50 px-6 py-4 border-t border-gray-200 flex justify-end">
                <button type="submit" class="bg-indigo-600 text-white px-6 py-2.5 rounded-lg font-bold shadow hover:bg-indigo-700 transition flex items-center">
                    <i class="fas fa-save mr-2"></i> Simpan Matriks Kelas Ini
                </button>
            </div>
        </div>
    </form>
    @endif
@endsection