@extends('layouts.app')
@section('title', 'Manajemen Hak Akses')
@section('content')
<div class="bg-white p-6 rounded-xl shadow-sm mb-6 border border-gray-100">
    <h2 class="text-xl font-bold text-gray-700"><i class="fas fa-key text-green-600 mr-2"></i> Matriks Hak Akses Dinamis</h2>
    <p class="text-xs text-gray-500 mt-1">Centang kotak untuk memberikan izin akses menu kepada masing-masing jabatan. Perubahan berlaku seketika.</p>
</div>

<form action="/manajemen-akses" method="POST">
    @csrf
    @method('PUT')
    
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden overflow-x-auto mb-6">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-green-50">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-black text-green-800 uppercase tracking-wider border-b w-1/3">NAMA MENU (AKSES)</th>
                    @foreach($roles as $role)
                        <th class="px-6 py-4 text-center text-xs font-black text-green-800 uppercase tracking-wider border-b">{{ $role->name }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
                @foreach($permissions as $perm)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-3 whitespace-nowrap text-sm font-bold text-gray-700">
                        {{ str_replace('_', ' ', strtoupper($perm->name)) }}
                    </td>
                    @foreach($roles as $role)
                        <td class="px-6 py-3 text-center border-l border-gray-50">
                            <label class="inline-flex items-center cursor-pointer p-2 hover:bg-green-50 rounded-lg transition">
                                <input type="checkbox" 
                                       name="permissions[{{ str_replace(' ', '_', $role->name) }}][]" 
                                       value="{{ $perm->name }}"
                                       {{ $role->hasPermissionTo($perm->name) ? 'checked' : '' }}
                                       class="w-5 h-5 text-green-600 bg-gray-100 border-gray-300 rounded focus:ring-green-500 focus:ring-2 transition duration-200 cursor-pointer">
                            </label>
                        </td>
                    @endforeach
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="flex justify-end">
        <button type="submit" class="px-6 py-3 bg-green-600 hover:bg-green-700 text-white rounded-xl font-bold shadow-md transition flex items-center cursor-pointer">
            <i class="fas fa-save mr-2"></i> Simpan Matriks Akses
        </button>
    </div>
</form>
@endsection