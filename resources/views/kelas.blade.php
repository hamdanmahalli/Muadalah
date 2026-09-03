@extends('layouts.app')

@section('title', 'Master Kelas')

@section('content')
<div class="flex justify-between items-center mb-6">
    <div>
        <h2 class="text-2xl font-bold text-gray-800"><i class="fas fa-school mr-2 text-indigo-600"></i> Master Kelas</h2>
        <p class="text-sm text-gray-500 mt-1">Kelola kelas beserta tingkat dan wali kelas (dipilih dari data pengurus/guru).</p>
    </div>
    <button onclick="bukaModalTambah()" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-semibold shadow hover:bg-indigo-700 transition">
        <i class="fas fa-plus mr-1"></i> Tambah Kelas
    </button>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden overflow-x-auto max-w-5xl">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase w-16">No</th>
                <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase">Nama Kelas</th>
                <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase">Tingkat</th>
                <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase">Wali Kelas</th>
                <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase w-32">Aksi</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @if(isset($kelas) && count($kelas) > 0)
                @foreach($kelas as $index => $k)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $index + 1 }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 font-bold">{{ $k->nama_kelas }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $k->tingkat ?? '-' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                        @if($k->waliKelas)
                            <span class="inline-flex items-center gap-1 text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded-full text-xs font-bold">
                                <i class="fas fa-user-tie text-[10px]"></i> {{ $k->waliKelas->nama_guru }}
                            </span>
                        @else
                            <span class="text-gray-400">-</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium space-x-1 flex justify-center">
                        <button onclick="bukaModalEdit({{ $k->id }}, '{{ $k->nama_kelas }}', '{{ $k->tingkat ?? '' }}', '{{ $k->wali_kelas_id ?? '' }}')" title="Edit Kelas" class="w-8 h-8 rounded-full bg-orange-50 text-orange-400 hover:bg-orange-400 hover:text-white transition flex items-center justify-center">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button onclick="bukaModalHapus({{ $k->id }}, '{{ $k->nama_kelas }}')" title="Hapus Kelas" class="w-8 h-8 rounded-full bg-red-50 text-red-500 hover:bg-red-500 hover:text-white transition flex items-center justify-center">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </td>
                </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="5" class="px-6 py-8 text-center text-gray-400 text-sm">
                        <i class="fas fa-folder-open text-3xl mb-3 opacity-50 block"></i>Belum ada data kelas.
                    </td>
                </tr>
            @endif
        </tbody>
    </table>
</div>

<div id="modal-kelas" class="hidden fixed inset-0 bg-gray-900 bg-opacity-60 overflow-y-auto h-full w-full z-50 flex items-center justify-center backdrop-blur-sm">
    <div class="relative mx-auto p-6 border w-full max-w-md shadow-2xl rounded-xl bg-white">
        <div class="flex justify-between items-center mb-5 border-b pb-3">
            <h3 class="text-lg font-bold text-gray-800" id="modal-judul">Tambah Kelas</h3>
            <button onclick="tutupModal()" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times text-xl"></i></button>
        </div>
        <form id="form-kelas" method="POST" action="/master-kelas">
            @csrf 
            <input type="hidden" name="_method" id="form-method" value="POST">
            <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-700 mb-1">Nama Kelas</label>
                <input type="text" name="nama_kelas" id="input-nama" placeholder="Misal: VII.A" required class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-indigo-500 outline-none uppercase">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-700 mb-1">Tingkat</label>
                <select name="tingkat" id="input-tingkat" class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-indigo-500 outline-none">
                    <option value="">-- Pilih Tingkat --</option>
                    @foreach($galur as $g)
                        <option value="{{ $g }}">{{ $g }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-6">
                <label class="block text-sm font-semibold text-gray-700 mb-1">Wali Kelas</label>
                <select name="wali_kelas_id" id="input-wali" class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-indigo-500 outline-none">
                    <option value="">-- Tidak Ada --</option>
                    @foreach($pengurus as $p)
                        <option value="{{ $p->id }}">{{ $p->nama_guru }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex justify-end space-x-2 border-t pt-4">
                <button type="button" onclick="tutupModal()" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg font-semibold">Batal</button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg font-semibold hover:bg-indigo-700">Simpan</button>
            </div>
        </form>
    </div>
</div>

<div id="modal-hapus" class="hidden fixed inset-0 bg-gray-900 bg-opacity-60 flex items-center justify-center backdrop-blur-sm">
    <div class="relative mx-auto p-6 border w-full max-w-sm shadow-2xl rounded-xl bg-white text-center">
        <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100 mb-5"><i class="fas fa-trash-alt text-2xl text-red-600"></i></div>
        <h3 class="text-xl font-bold text-gray-800 mb-2">Konfirmasi Hapus</h3>
        <p class="text-sm text-gray-500 mb-6">Yakin ingin menghapus kelas <strong id="hapus-nama-kelas" class="text-gray-800"></strong>?</p>
        <form id="form-hapus" method="POST" action="">
            @csrf 
            @method('DELETE')
            <div class="flex justify-center space-x-3">
                <button type="button" onclick="tutupModalHapus()" class="px-5 py-2.5 bg-gray-200 text-gray-800 rounded-lg font-semibold w-full">Batal</button>
                <button type="submit" class="px-5 py-2.5 bg-red-500 text-white rounded-lg font-semibold w-full">Ya, Hapus</button>
            </div>
        </form>
    </div>
</div>

<script>
    function bukaModalTambah() {
        document.getElementById('modal-kelas').classList.remove('hidden');
        document.getElementById('modal-judul').innerText = "Tambah Kelas";
        document.getElementById('form-kelas').action = "/master-kelas";
        document.getElementById('form-method').value = "POST";
        document.getElementById('form-kelas').reset();
    }
    function bukaModalEdit(id, nama, tingkat, wali) {
        document.getElementById('modal-kelas').classList.remove('hidden');
        document.getElementById('modal-judul').innerText = "Edit Kelas";
        document.getElementById('form-kelas').action = "/master-kelas/" + id;
        document.getElementById('form-method').value = "PUT";
        document.getElementById('input-nama').value = nama;
        document.getElementById('input-tingkat').value = tingkat;
        document.getElementById('input-wali').value = wali;
    }
    function tutupModal() { 
        document.getElementById('modal-kelas').classList.add('hidden'); 
    }
    function bukaModalHapus(id, nama) {
        document.getElementById('modal-hapus').classList.remove('hidden');
        document.getElementById('hapus-nama-kelas').innerText = nama;
        document.getElementById('form-hapus').action = "/master-kelas/" + id;
    }
    function tutupModalHapus() { 
        document.getElementById('modal-hapus').classList.add('hidden'); 
    }
</script>
@endsection