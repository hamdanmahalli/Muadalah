@extends('layouts.app')

@section('title', 'Hari & Jam Operasional')

@section('content')
<style>
    /* Menghilangkan panah spinner pada angka */
    input[type="number"]::-webkit-inner-spin-button,
    input[type="number"]::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
    input[type="number"] { -moz-appearance: textfield; }
</style>

<div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-800"><i class="fas fa-calendar-week text-indigo-600 mr-2"></i> Hari & Jam Operasional</h1>
        <p class="text-sm text-gray-500 mt-1">Konfigurasi kapasitas waktu belajar aktif lembaga dalam satu minggu</p>
    </div>
</div>

<div class="bg-gradient-to-r from-indigo-600 to-blue-600 rounded-2xl shadow-lg p-6 mb-8 text-white flex items-center justify-between relative overflow-hidden">
    <div class="relative z-10">
        <h3 class="text-indigo-100 font-bold text-sm uppercase tracking-wider mb-2">Total Kapasitas Lembaga</h3>
        <div class="flex items-baseline space-x-2">
            <span class="text-5xl font-black tracking-tight">{{ $total_kapasitas }}</span>
            <span class="text-xl font-semibold text-indigo-100">Jam / Minggu</span>
        </div>
        <p class="text-xs text-indigo-200 mt-2"><i class="fas fa-info-circle mr-1"></i> Angka ini menjadi patokan batas maksimal di Plotting Target Mengajar.</p>
    </div>
    <i class="fas fa-chart-pie text-8xl text-white opacity-10 absolute -right-4 -bottom-4 transform rotate-12"></i>
</div>

<form action="/master-hari-operasional" method="POST" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    @csrf
    
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-100">
            <thead class="bg-gray-50/80">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider w-32 border-b border-gray-100">Hari</th>
                    <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider w-32 border-b border-gray-100">Status Aktif</th>
                    <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider w-40 border-b border-gray-100">Max Jam Belajar</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider border-b border-gray-100">Keterangan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                @foreach($data as $hari)
                <tr class="hover:bg-gray-50/50 transition duration-200">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="text-sm font-black text-gray-800 uppercase">{{ $hari->hari }}</span>
                    </td>
                    
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="hari_data[{{ $hari->id }}][is_active]" value="1" class="sr-only peer" {{ $hari->is_active ? 'checked' : '' }} onchange="toggleRow(this, {{ $hari->id }})">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                        </label>
                    </td>
                    
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                        <div class="flex items-center justify-center">
                            <input type="number" id="max_jam_{{ $hari->id }}" name="hari_data[{{ $hari->id }}][max_jam]" value="{{ $hari->max_jam }}" min="0" max="24" class="w-20 bg-gray-50 border border-gray-200 rounded-lg p-2 text-center text-lg font-bold text-gray-700 focus:bg-white focus:ring-2 focus:ring-indigo-500 outline-none transition {{ !$hari->is_active ? 'opacity-50' : '' }}" {{ !$hari->is_active ? 'readonly' : '' }}>
                        </div>
                    </td>
                    
                    <td class="px-6 py-4 whitespace-nowrap">
                        <input type="text" id="ket_{{ $hari->id }}" name="hari_data[{{ $hari->id }}][keterangan]" value="{{ $hari->keterangan }}" placeholder="Misal: Hari Normal / Libur Total" class="w-full bg-gray-50 border border-gray-200 rounded-lg p-2.5 text-sm font-medium text-gray-700 focus:bg-white focus:ring-2 focus:ring-indigo-500 outline-none transition {{ !$hari->is_active ? 'opacity-50' : '' }}" {{ !$hari->is_active ? 'readonly' : '' }}>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    
    <div class="bg-gray-50 px-6 py-5 border-t border-gray-100 flex justify-end">
        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-8 py-3 rounded-xl font-bold shadow-md transition flex items-center">
            <i class="fas fa-save mr-2"></i> Simpan Konfigurasi Waktu
        </button>
    </div>
</form>

<script>
    // KECERDASAN UI: Meredupkan dan me-Nol-kan jam secara otomatis jika hari diliburkan
    function toggleRow(checkbox, id) {
        let maxJamInput = document.getElementById('max_jam_' + id);
        let ketInput = document.getElementById('ket_' + id);
        
        if (checkbox.checked) {
            maxJamInput.classList.remove('opacity-50');
            ketInput.classList.remove('opacity-50');
            maxJamInput.removeAttribute('readonly');
            ketInput.removeAttribute('readonly');
        } else {
            maxJamInput.classList.add('opacity-50');
            ketInput.classList.add('opacity-50');
            maxJamInput.value = 0; // Langsung di-Nol-kan
            maxJamInput.setAttribute('readonly', 'true');
            ketInput.setAttribute('readonly', 'true');
        }
    }
</script>
@endsection