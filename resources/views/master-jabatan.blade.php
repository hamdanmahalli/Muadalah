@extends('layouts.app')

@section('title', 'Master Jabatan')

@section('content')
<div class="flex justify-between items-center mb-6">
    <div>
        <h2 class="text-2xl font-bold text-gray-800"><i class="fas fa-briefcase mr-2 text-violet-600"></i> Master Jabatan</h2>
        <p class="text-sm text-gray-500 mt-1">Kelola daftar jabatan pengurus/guru. Satu pengurus bisa punya lebih dari satu jabatan.</p>
    </div>
    <button onclick="bukaModalTambah()" class="bg-violet-600 text-white px-5 py-2 rounded-xl text-sm font-semibold shadow hover:bg-violet-700 transition">
        <i class="fas fa-plus mr-1"></i> Tambah Jabatan
    </button>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden max-w-4xl">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase w-16">No</th>
                <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase">Nama Jabatan</th>
                <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase">Deskripsi</th>
                <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase w-24">Status</th>
                <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase w-32">Aksi</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse($jabatans as $index => $j)
            <tr class="hover:bg-gray-50 transition">
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $index + 1 }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 font-bold">{{ $j->nama_jabatan }}</td>
                <td class="px-6 py-4 text-sm text-gray-500">{{ $j->deskripsi ?? '-' }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-center">
                    <span class="px-3 py-1 text-[10px] font-bold uppercase tracking-wider rounded-full {{ $j->status == 'Nonaktif' ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }}">{{ $j->status }}</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                    <div class="flex justify-center space-x-1">
                        <button onclick="bukaModalEdit({{ $j->id }}, '{{ js_q($j->nama_jabatan) }}', '{{ js_q($j->deskripsi ?? '') }}', '{{ $j->status }}')" title="Edit" class="w-8 h-8 rounded-full bg-orange-50 text-orange-400 hover:bg-orange-400 hover:text-white transition flex items-center justify-center">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button onclick="bukaModalHapus({{ $j->id }}, '{{ js_q($j->nama_jabatan) }}')" title="Hapus" class="w-8 h-8 rounded-full bg-red-50 text-red-500 hover:bg-red-500 hover:text-white transition flex items-center justify-center">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="px-6 py-8 text-center text-gray-400 text-sm">
                    <i class="fas fa-briefcase text-3xl mb-3 opacity-50 block"></i>Belum ada data jabatan.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div id="modal-jabatan" class="hidden fixed inset-0 bg-gray-900 bg-opacity-60 overflow-y-auto h-full w-full z-50 flex items-center justify-center backdrop-blur-sm">
    <div class="relative mx-auto p-6 border w-full max-w-sm shadow-2xl rounded-xl bg-white">
        <div class="flex justify-between items-center mb-5 border-b pb-3">
            <h3 class="text-lg font-bold text-gray-800" id="modal-judul">Tambah Jabatan</h3>
            <button onclick="tutupModal()" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times text-xl"></i></button>
        </div>
        <form id="form-jabatan" method="POST" action="/master-jabatan">
            @csrf 
            <input type="hidden" name="_method" id="form-method" value="POST">
            <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-700 mb-1">Nama Jabatan</label>
                <input type="text" name="nama_jabatan" id="input-nama" placeholder="Misal: Wakil Kurikulum" required class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-violet-500 outline-none">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-700 mb-1">Deskripsi (Opsional)</label>
                <input type="text" name="deskripsi" id="input-deskripsi" class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-violet-500 outline-none">
            </div>
            <div class="mb-6">
                <label class="block text-sm font-semibold text-gray-700 mb-1">Status</label>
                <select name="status" id="input-status" class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-violet-500 outline-none">
                    <option value="Aktif">Aktif</option>
                    <option value="Nonaktif">Nonaktif</option>
                </select>
            </div>
            <div class="flex justify-end space-x-2 border-t pt-4">
                <button type="button" onclick="tutupModal()" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg font-semibold">Batal</button>
                <button type="submit" class="px-4 py-2 bg-violet-600 text-white rounded-lg font-semibold hover:bg-violet-700">Simpan</button>
            </div>
        </form>
    </div>
</div>

<div id="modal-hapus" class="hidden fixed inset-0 bg-gray-900 bg-opacity-60 flex items-center justify-center backdrop-blur-sm">
    <div class="relative mx-auto p-6 border w-full max-w-sm shadow-2xl rounded-xl bg-white text-center">
        <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100 mb-5"><i class="fas fa-trash-alt text-2xl text-red-600"></i></div>
        <h3 class="text-xl font-bold text-gray-800 mb-2">Konfirmasi Hapus</h3>
        <p class="text-sm text-gray-500 mb-6">Yakin ingin menghapus jabatan <strong id="hapus-nama" class="text-gray-800"></strong>?</p>
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
        document.getElementById('modal-jabatan').classList.remove('hidden');
        document.getElementById('modal-judul').innerText = "Tambah Jabatan";
        document.getElementById('form-jabatan').action = "/master-jabatan";
        document.getElementById('form-method').value = "POST";
        document.getElementById('form-jabatan').reset();
        document.getElementById('input-status').value = "Aktif";
    }
    function bukaModalEdit(id, nama, deskripsi, status) {
        document.getElementById('modal-jabatan').classList.remove('hidden');
        document.getElementById('modal-judul').innerText = "Edit Jabatan";
        document.getElementById('form-jabatan').action = "/master-jabatan/" + id;
        document.getElementById('form-method').value = "PUT";
        document.getElementById('input-nama').value = nama;
        document.getElementById('input-deskripsi').value = deskripsi;
        document.getElementById('input-status').value = status;
    }
    function tutupModal() { 
        document.getElementById('modal-jabatan').classList.add('hidden'); 
    }
    function bukaModalHapus(id, nama) {
        document.getElementById('modal-hapus').classList.remove('hidden');
        document.getElementById('hapus-nama').innerText = nama;
        document.getElementById('form-hapus').action = "/master-jabatan/" + id;
    }
    function tutupModalHapus() { 
        document.getElementById('modal-hapus').classList.add('hidden'); 
    }
</script>
@endsection
