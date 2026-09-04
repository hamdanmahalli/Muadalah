@extends('layouts.app')
@section('title', 'Manajemen Hak Akses')
@section('content')
<div class="bg-white p-6 rounded-xl shadow-sm mb-6 border border-gray-100">
    <h2 class="text-xl font-bold text-gray-700"><i class="fas fa-key text-green-600 mr-2"></i> Matriks Hak Akses Dinamis</h2>
    <p class="text-xs text-gray-500 mt-1">Centang kotak untuk memberikan izin akses menu kepada masing-masing jabatan. Hak akses dikelompokkan sesuai menu pada sidebar. Perubahan berlaku seketika.</p>
</div>

<form action="/manajemen-akses" method="POST">
    @csrf
    @method('PUT')

    @php
        $permByName = $permissions->keyBy('name');
        $ikonGrup = [
            'Beranda & Monitoring' => 'fa-th-large',
            'Master Data' => 'fa-database',
            'Jadwal & Kaldik' => 'fa-calendar-alt',
            'Guru' => 'fa-chalkboard-teacher',
            'Siswa' => 'fa-user-graduate',
            'Pengaturan Sistem' => 'fa-cog',
        ];
        $warnaGrup = [
            'Beranda & Monitoring' => 'bg-teal-50',
            'Master Data' => 'bg-blue-50',
            'Jadwal & Kaldik' => 'bg-indigo-50',
            'Guru' => 'bg-violet-50',
            'Siswa' => 'bg-amber-50',
            'Pengaturan Sistem' => 'bg-rose-50',
        ];
        $warnaNamaGrup = [
            'Beranda & Monitoring' => 'text-teal-700',
            'Master Data' => 'text-blue-700',
            'Jadwal & Kaldik' => 'text-indigo-700',
            'Guru' => 'text-violet-700',
            'Siswa' => 'text-amber-700',
            'Pengaturan Sistem' => 'text-rose-700',
        ];
    @endphp

    @foreach($grupMenu as $namaGrup => $daftarPerm)
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">
        <div class="px-6 py-4 {{ $warnaGrup[$namaGrup] ?? 'bg-gray-50' }} border-b border-gray-200 flex items-center justify-between">
            <h3 class="text-sm font-black {{ $warnaNamaGrup[$namaGrup] ?? 'text-gray-700' }} uppercase tracking-wider">
                <i class="fas {{ $ikonGrup[$namaGrup] ?? 'fa-th' }} mr-2"></i>{{ $namaGrup }}
            </h3>
            <span class="text-[11px] font-semibold text-gray-400">{{ count($daftarPerm) }} menu</span>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-black text-gray-500 uppercase tracking-wider border-b w-1/3">NAMA MENU (AKSES)</th>
                        @foreach($roles as $role)
                            <th class="px-6 py-3 text-center text-xs font-black text-gray-500 uppercase tracking-wider border-b">{{ $role->name }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @foreach($daftarPerm as $namaPerm)
                        @if($perm = $permByName->get($namaPerm))
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-3 whitespace-nowrap text-sm font-semibold text-gray-700">
                                {{ $labelMenu[$namaPerm] ?? str_replace('_', ' ', strtoupper($namaPerm)) }}
                                @if($namaPerm === 'akses_jadwal_saya')
                                <span class="ml-2 text-[10px] font-bold text-teal-600 bg-teal-50 px-2 py-0.5 rounded-full">Cetak Barcode juga untuk Tata Usaha</span>
                                @endif
                            </td>
                            @foreach($roles as $role)
                                <td class="px-6 py-3 text-center border-l border-gray-50">
                                    <label class="inline-flex items-center cursor-pointer p-2 hover:bg-green-50 rounded-lg transition">
                                        <input type="checkbox"
                                               name="permissions[{{ str_replace(' ', '_', $role->name) }}][]"
                                               value="{{ $perm->id }}"
                                               {{ $role->hasPermissionTo($perm->name) ? 'checked' : '' }}
                                               class="w-5 h-5 text-green-600 bg-gray-100 border-gray-300 rounded focus:ring-green-500 focus:ring-2 transition duration-200 cursor-pointer">
                                    </label>
                                </td>
                            @endforeach
                        </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endforeach

    <div class="flex justify-end">
        <button type="submit" class="px-6 py-3 bg-green-600 hover:bg-green-700 text-white rounded-xl font-bold shadow-md transition flex items-center cursor-pointer">
            <i class="fas fa-save mr-2"></i> Simpan Matriks Akses
        </button>
    </div>
</form>
@endsection