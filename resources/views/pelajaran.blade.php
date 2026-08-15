@extends('layouts.app')

@section('title', 'Master Mata Pelajaran')

@section('content')
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800"><i class="fas fa-book-open mr-2 text-indigo-600"></i> Master Mata Pelajaran</h2>
        <button onclick="bukaModalTambah()" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-semibold shadow hover:bg-indigo-700 transition">
            <i class="fas fa-plus mr-1"></i> Tambah Pelajaran
        </button>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase w-12">No</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase w-24">Kode</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase">Mata Pelajaran</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase">Nama Kitab / Referensi</th>
                    <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase w-32">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($pelajarans ?? [] as $index => $pelajaran)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $index + 1 }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-indigo-600">{{ $pelajaran->kode_pelajaran }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 font-bold">{{ $pelajaran->nama_pelajaran }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $pelajaran->nama_kitab ?? '-' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium space-x-1 flex justify-center">
                        <button onclick='bukaModalEdit(@json($pelajaran))' title="Edit Pelajaran" class="w-8 h-8 rounded-full bg-orange-50 text-orange-400 hover:bg-orange-400 hover:text-white transition flex items-center justify-center">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button onclick="bukaModalHapus({{ $pelajaran->id }}, '{{ $pelajaran->nama_pelajaran }}')" title="Hapus Pelajaran" class="w-8 h-8 rounded-full bg-red-50 text-red-500 hover:bg-red-500 hover:text-white transition flex items-center justify-center">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-8 text-center text-gray-400 text-sm">
                        <i class="fas fa-folder-open text-3xl mb-3 opacity-50 block"></i>
                        Belum ada data mata pelajaran.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div id="modal-pelajaran" class="hidden fixed inset-0 bg-gray-900 bg-opacity-60 overflow-y-auto h-full w-full z-50 flex items-center justify-center backdrop-blur-sm">
        <div class="relative mx-auto p-6 border w-full max-w-md shadow-2xl rounded-xl bg-white">
            <div class="flex justify-between items-center mb-5 border-b pb-3">
                <h3 class="text-lg font-bold text-gray-800" id="modal-judul">Tambah Pelajaran</h3>
                <button onclick="tutupModal()" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times text-xl"></i></button>
            </div>
            
            <form id="form-pelajaran" method="POST" action="/master-pelajaran">
                @csrf
                <input type="hidden" name="_method" id="form-method" value="POST">
                
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Kode Pelajaran (Otomatis)</label>
                    <input type="text" name="kode_pelajaran" id="input-kode" value="{{ $kodeBaru }}" readonly class="w-full border border-gray-300 rounded-lg p-2 bg-gray-100 text-gray-500 font-bold outline-none cursor-not-allowed">
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Nama Mata Pelajaran</label>
                    <input type="text" name="nama_pelajaran" id="input-nama" placeholder="Misal: Nahwu" required class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-indigo-500 outline-none">
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Nama Kitab (Opsional)</label>
                    <input type="text" name="nama_kitab" id="input-kitab" placeholder="Misal: Mukhtashor Jiddan" class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-indigo-500 outline-none">
                </div>

                <div class="flex justify-end space-x-2 border-t pt-4">
                    <button type="button" onclick="tutupModal()" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg font-semibold hover:bg-gray-300 transition">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg font-semibold hover:bg-indigo-700 transition">Simpan Data</button>
                </div>
            </form>
        </div>
    </div>

    <div id="modal-hapus" class="hidden fixed inset-0 bg-gray-900 bg-opacity-60 overflow-y-auto h-full w-full z-50 flex items-center justify-center backdrop-blur-sm transition-opacity">
        <div class="relative mx-auto p-6 border w-full max-w-sm shadow-2xl rounded-xl bg-white text-center transform transition-all">
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100 mb-5">
                <i class="fas fa-trash-alt text-2xl text-red-600"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-800 mb-2">Konfirmasi Hapus</h3>
            <p class="text-sm text-gray-500 mb-6">Yakin ingin menghapus pelajaran <br><strong id="hapus-nama-pelajaran" class="text-gray-800 text-base"></strong>?</p>
            
            <form id="form-hapus" method="POST" action="">
                @csrf @method('DELETE')
                <div class="flex justify-center space-x-3">
                    <button type="button" onclick="tutupModalHapus()" class="px-5 py-2.5 bg-gray-200 text-gray-800 rounded-lg font-semibold hover:bg-gray-300 transition w-full">Batal</button>
                    <button type="submit" class="px-5 py-2.5 bg-red-500 text-white rounded-lg font-semibold hover:bg-red-600 transition shadow-sm w-full">Ya, Hapus</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function bukaModalTambah() {
            document.getElementById('modal-pelajaran').classList.remove('hidden');
            document.getElementById('modal-judul').innerText = "Tambah Pelajaran";
            document.getElementById('form-pelajaran').action = "/master-pelajaran";
            document.getElementById('form-method').value = "POST";
            document.getElementById('form-pelajaran').reset();
            
            // Kembalikan ke kode otomatis baru saat menambah
            document.getElementById('input-kode').value = "{{ $kodeBaru }}";
        }

        function bukaModalEdit(pelajaran) {
            document.getElementById('modal-pelajaran').classList.remove('hidden');
            document.getElementById('modal-judul').innerText = "Edit Pelajaran";
            document.getElementById('form-pelajaran').action = "/master-pelajaran/" + pelajaran.id;
            document.getElementById('form-method').value = "PUT";
            
            // Kunci kode lama agar tidak bisa diubah saat edit
            document.getElementById('input-kode').value = pelajaran.kode_pelajaran;
            document.getElementById('input-nama').value = pelajaran.nama_pelajaran;
            document.getElementById('input-kitab').value = pelajaran.nama_kitab || '';
        }

        function tutupModal() {
            document.getElementById('modal-pelajaran').classList.add('hidden');
        }

        function bukaModalHapus(id, nama) {
            document.getElementById('modal-hapus').classList.remove('hidden');
            document.getElementById('hapus-nama-pelajaran').innerText = nama;
            document.getElementById('form-hapus').action = "/master-pelajaran/" + id;
        }

        function tutupModalHapus() {
            document.getElementById('modal-hapus').classList.add('hidden');
        }
    </script>
@endsection