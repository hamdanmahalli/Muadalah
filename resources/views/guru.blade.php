@extends('layouts.app')

@section('title', 'Master Data Guru')

@section('content')

    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Master Data Guru</h1>
            <p class="text-sm text-gray-500 mt-1">Kelola data pengajar Pondok Pesantren.</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a href="/master-guru/export" class="bg-emerald-50 text-emerald-700 border border-emerald-200 px-4 py-2 rounded-xl text-sm font-bold hover:bg-emerald-600 hover:text-white transition flex items-center shadow-sm">
                <i class="fas fa-file-excel mr-2"></i> Export
            </a>
            <button onclick="bukaModalImport()" class="bg-blue-50 text-blue-700 border border-blue-200 px-4 py-2 rounded-xl text-sm font-bold hover:bg-blue-600 hover:text-white transition flex items-center shadow-sm">
                <i class="fas fa-file-upload mr-2"></i> Import
            </button>
            <button onclick="bukaModalTambah()" class="bg-green-600 text-white px-5 py-2 rounded-xl text-sm font-bold hover:bg-green-700 transition flex items-center shadow-md">
                <i class="fas fa-plus mr-2"></i> Tambah Guru
            </button>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-8">
        
        <div class="flex flex-col md:flex-row justify-between items-center p-5 border-b border-gray-100 gap-4 bg-gray-50/50">
            <div class="flex items-center text-sm text-gray-600 font-medium">
                <span class="mr-2">Tampilkan</span>
                <select id="select-per-page" onchange="doLiveSearch()" class="border border-gray-200 rounded-lg p-2 bg-white focus:ring-2 focus:ring-green-500 outline-none transition cursor-pointer shadow-sm text-gray-700 font-bold">
                    <option value="10" {{ ($perPage ?? 10) == 10 ? 'selected' : '' }}>10</option>
                    <option value="25" {{ ($perPage ?? 10) == 25 ? 'selected' : '' }}>25</option>
                    <option value="50" {{ ($perPage ?? 10) == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ ($perPage ?? 10) == 100 ? 'selected' : '' }}>100</option>
                </select>
                <span class="ml-2">data</span>
            </div>

            <form id="form-pencarian" onsubmit="event.preventDefault(); doLiveSearch();" class="w-full md:w-72 relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-search text-gray-400"></i>
                </div>
                <input type="text" 
                       id="input-search"
                       value="{{ $search ?? '' }}" 
                       placeholder="Ketik nama atau NIG..." 
                       autocomplete="off"
                       oninput="doLiveSearch()"
                       class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-green-500 outline-none transition bg-white shadow-sm font-medium text-gray-700">
            </form>
        </div>

        <div id="area-tabel-guru">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-white">
                        <tr>
                            <th class="px-5 py-4 text-left text-[11px] font-extrabold text-gray-400 uppercase tracking-widest w-12">No</th>
                            <th class="px-5 py-4 text-left text-[11px] font-extrabold text-gray-400 uppercase tracking-widest w-24">NIG</th>
                            <th class="px-5 py-4 text-left text-[11px] font-extrabold text-gray-400 uppercase tracking-widest">Nama Guru</th>
                            <th class="px-5 py-4 text-left text-[11px] font-extrabold text-gray-400 uppercase tracking-widest w-28">L/P</th>
                            <th class="px-5 py-4 text-left text-[11px] font-extrabold text-gray-400 uppercase tracking-widest">Alamat</th>
                            <th class="px-5 py-4 text-left text-[11px] font-extrabold text-gray-400 uppercase tracking-widest w-32">No. HP</th>
                            <th class="px-5 py-4 text-center text-[11px] font-extrabold text-gray-400 uppercase tracking-widest w-24">Status</th>
                            <th class="px-5 py-4 text-right text-[11px] font-extrabold text-gray-400 uppercase tracking-widest w-28">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 bg-white">
                        @forelse($gurus as $index => $guru)
                        <tr class="hover:bg-gray-50/50 transition duration-150">
                            <td class="px-5 py-4 whitespace-nowrap text-sm text-gray-400 font-semibold">{{ $gurus->firstItem() + $index }}</td>
                            <td class="px-5 py-4 whitespace-nowrap text-sm font-bold text-gray-700">{{ $guru->nig }}</td>
                            <td class="px-5 py-4 whitespace-nowrap text-sm font-bold text-gray-900">{{ $guru->nama_guru }}</td>
                            <td class="px-5 py-4 whitespace-nowrap text-sm text-gray-500">{{ $guru->gender ?? '-' }}</td>
                            <td class="px-5 py-4 whitespace-nowrap text-sm text-gray-500">{{ $guru->alamat ?? '-' }}</td>
                            <td class="px-5 py-4 whitespace-nowrap text-sm text-gray-500">{{ $guru->no_hp ?? '-' }}</td>
                            <td class="px-5 py-4 whitespace-nowrap text-center">
                                <span class="px-3 py-1 inline-flex text-[10px] uppercase tracking-wider font-bold rounded-full {{ ($guru->status == 'Nonaktif') ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }}">
                                    {{ $guru->status ?? 'Aktif' }}
                                </span>
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex justify-end space-x-2">
                                    <button onclick="bukaModalEdit('{{ $guru->id }}', '{{ $guru->nig }}', '{{ addslashes($guru->nama_guru) }}', '{{ $guru->no_hp }}', '{{ $guru->gender }}', '{{ addslashes($guru->alamat) }}', '{{ $guru->status }}')" class="w-8 h-8 rounded-lg bg-gray-50 text-gray-500 hover:bg-amber-500 hover:text-white transition flex items-center justify-center border border-gray-100 shadow-sm" title="Edit">
                                        <i class="fas fa-pen text-[10px]"></i>
                                    </button>
                                    
                                    <button type="button" onclick="konfirmasiHapus('{{ $guru->id }}', '{{ addslashes($guru->nama_guru) }}')" class="w-8 h-8 rounded-lg bg-gray-50 text-gray-500 hover:bg-red-500 hover:text-white transition flex items-center justify-center border border-gray-100 shadow-sm" title="Hapus">
                                        <i class="fas fa-trash text-[10px]"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="px-6 py-16 text-center">
                                <div class="mx-auto w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-4">
                                    <i class="fas fa-user-slash text-2xl text-gray-400"></i>
                                </div>
                                <p class="text-gray-500 font-medium">Belum ada data guru yang cocok.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($gurus->hasPages())
            <div class="px-5 py-4 border-t border-gray-100 bg-gray-50/50">
                {{ $gurus->links() }}
            </div>
            @endif
        </div>
    </div>

    <div id="modal-import" class="hidden fixed inset-0 bg-gray-900 bg-opacity-60 overflow-y-auto h-full w-full z-50 flex items-center justify-center backdrop-blur-sm">
        <div class="relative mx-auto p-6 border w-full max-w-sm shadow-2xl rounded-xl bg-white">
            <div class="flex justify-between items-center mb-4 border-b pb-3">
                <h3 class="text-lg font-bold text-gray-800">Import Data Guru</h3>
                <button type="button" onclick="tutupModalImport()" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times text-xl"></i></button>
            </div>
            <form action="/master-guru/import" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Pilih File Excel (.xlsx / .xls)</label>
                    <input type="file" name="file" required class="w-full border border-gray-300 rounded-lg p-2 text-sm bg-gray-50">
                </div>
                <div class="flex justify-end space-x-2 border-t pt-4">
                    <button type="button" onclick="tutupModalImport()" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg font-semibold text-sm">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg font-semibold text-sm hover:bg-blue-700">Unggah & Import</button>
                </div>
            </form>
        </div>
    </div>

    <div id="modal-guru" class="hidden fixed inset-0 bg-gray-900 bg-opacity-60 overflow-y-auto h-full w-full z-50 flex items-center justify-center backdrop-blur-sm">
        <div class="relative mx-auto p-6 border w-full max-w-lg shadow-2xl rounded-xl bg-white">
            <div class="flex justify-between items-center mb-5 border-b pb-3">
                <h3 class="text-lg font-bold text-gray-800" id="modal-judul">Tambah Guru Baru</h3>
                <button type="button" onclick="tutupModal()" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times text-xl"></i></button>
            </div>
            
            <form id="form-guru" method="POST" action="/master-guru">
                @csrf
                <input type="hidden" name="_method" id="form-method" value="POST">
                
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-1">NIG (Otomatis)</label>
                        <input type="text" name="nig" id="input-nig" value="{{ $nigBaru }}" readonly class="w-full border border-gray-300 rounded-lg p-2 bg-gray-100 text-gray-500 font-bold outline-none cursor-not-allowed">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Status Mengajar</label>
                        <select name="status" id="input-status" class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-green-500 outline-none">
                            <option value="Aktif">Aktif</option>
                            <option value="Nonaktif">Nonaktif</option>
                        </select>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Nama Lengkap Guru</label>
                    <input type="text" name="nama_guru" id="input-nama" required class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-green-500 outline-none">
                </div>

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">No. HP / WhatsApp</label>
                        <input type="text" name="no_hp" id="input-nohp" placeholder="08123xxxx" class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-green-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">gender</label>
                        <select name="gender" id="input-gender" class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-green-500 outline-none">
                            <option value="">-- Pilih --</option>
                            <option value="Laki-laki">Laki-laki</option>
                            <option value="Perempuan">Perempuan</option>
                        </select>
                    </div>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Alamat Lengkap</label>
                    <textarea name="alamat" id="input-alamat" rows="2" class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-green-500 outline-none"></textarea>
                </div>

                <div class="flex justify-end space-x-2 border-t pt-4">
                    <button type="button" onclick="tutupModal()" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg font-semibold hover:bg-gray-300 transition">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg font-semibold hover:bg-green-700 transition shadow-md">Simpan Data</button>
                </div>
            </form>
        </div>
    </div>

    <div id="modal-hapus" class="hidden fixed inset-0 bg-gray-900 bg-opacity-60 overflow-y-auto h-full w-full z-50 flex items-center justify-center backdrop-blur-sm">
        <div class="relative mx-auto p-6 border w-full max-w-sm shadow-2xl rounded-2xl bg-white text-center">
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100 mb-4 shadow-inner">
                <i class="fas fa-trash-alt text-2xl text-red-600"></i>
            </div>
            <h3 class="text-xl font-extrabold text-gray-800 mb-2">Hapus Data Guru?</h3>
            <p class="text-sm text-gray-600 mb-6">Yakin ingin menghapus <b id="teks-nama-hapus" class="text-gray-900"></b>? Data ini akan dihapus secara permanen dari sistem.</p>
            
            <div class="flex justify-center space-x-3">
                <button type="button" onclick="tutupModalHapus()" class="px-5 py-2.5 bg-gray-200 text-gray-800 rounded-xl font-semibold hover:bg-gray-300 transition">Batal</button>
                <button type="button" onclick="eksekusiHapus()" class="px-5 py-2.5 bg-red-600 text-white rounded-xl font-semibold hover:bg-red-700 transition shadow-md">Ya, Hapus Data</button>
            </div>
            
            <form id="form-hapus-global" method="POST" class="hidden">
                @csrf @method('DELETE')
            </form>
        </div>
    </div>

    <script>
        // 1. SKRIP PENCARIAN AJAX LIVE SEARCH
        let searchTimer;
        function doLiveSearch() {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(() => {
                const query = document.getElementById('input-search').value;
                const perPage = document.getElementById('select-per-page').value;
                const url = `/master-guru?search=${encodeURIComponent(query)}&per_page=${perPage}`;

                fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(response => response.text())
                .then(html => {
                    const doc = new DOMParser().parseFromString(html, 'text/html');
                    const newTableArea = doc.getElementById('area-tabel-guru');
                    if (newTableArea) {
                        document.getElementById('area-tabel-guru').innerHTML = newTableArea.innerHTML;
                    }
                    window.history.pushState({}, '', url);
                }).catch(error => console.error('Error Live Search:', error));
            }, 300);
        }

        // 2. SKRIP KENDALI MODAL IMPORT
        function bukaModalImport() {
            document.getElementById('modal-import').classList.remove('hidden');
        }
        function tutupModalImport() {
            document.getElementById('modal-import').classList.add('hidden');
        }

        // 3. SKRIP KENDALI MODAL TAMBAH & EDIT GURU
        function bukaModalTambah() {
            document.getElementById('modal-guru').classList.remove('hidden');
            document.getElementById('modal-judul').innerText = "Tambah Guru Baru";
            document.getElementById('form-guru').action = "/master-guru";
            document.getElementById('form-method').value = "POST";
            
            // Reset isi form
            document.getElementById('form-guru').reset();
            document.getElementById('input-nig').value = "{{ $nigBaru }}";
            document.getElementById('input-status').value = "Aktif";
        }

        function bukaModalEdit(id, nig, nama, hp, gender, alamat, status) {
            document.getElementById('modal-guru').classList.remove('hidden');
            document.getElementById('modal-judul').innerText = "Edit Data Guru";
            document.getElementById('form-guru').action = "/master-guru/" + id;
            document.getElementById('form-method').value = "PUT";
            
            // Isi form dengan data lama
            document.getElementById('input-nig').value = nig;
            document.getElementById('input-nama').value = nama;
            document.getElementById('input-nohp').value = hp;
            document.getElementById('input-gender').value = gender;
            document.getElementById('input-alamat').value = alamat;
            document.getElementById('input-status').value = status;
        }

        function tutupModal() {
            document.getElementById('modal-guru').classList.add('hidden');
        }

        // 4. SKRIP KENDALI MODAL HAPUS ELEGAN
        function konfirmasiHapus(id, namaGuru) {
            document.getElementById('modal-hapus').classList.remove('hidden');
            document.getElementById('teks-nama-hapus').innerText = namaGuru;
            document.getElementById('form-hapus-global').action = "/master-guru/" + id;
        }

        function tutupModalHapus() {
            document.getElementById('modal-hapus').classList.add('hidden');
        }

        function eksekusiHapus() {
            document.getElementById('form-hapus-global').submit();
        }
    </script>
@endsection