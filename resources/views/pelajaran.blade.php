@extends('layouts.app')

@section('title', 'Master Pelajaran')

@section('content')
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800"><i class="fas fa-book-open mr-2 text-indigo-600"></i> Master Pelajaran</h2>
        <button onclick="bukaModalTambah()" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-semibold shadow hover:bg-indigo-700 transition flex items-center">
            <i class="fas fa-plus mr-2"></i> Tambah Pelajaran
        </button>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider w-16">No</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider w-32">Kode</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Nama Pelajaran</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Kitab / Referensi Per Tingkat</th>
                    <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider w-24">Status</th>
                    <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider w-32">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
                @forelse($pelajarans as $index => $p)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 font-medium">{{ $index + 1 }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-indigo-600">{{ $p->kode_pelajaran }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-800">{{ $p->nama_pelajaran }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">
                        <!-- MENAMPILKAN BADGE KITAB DARI JSON -->
                        @if(is_array($p->kitab_tingkat) && count(array_filter($p->kitab_tingkat)) > 0)
                            <div class="flex flex-wrap gap-1.5">
                                @foreach($p->kitab_tingkat as $tingkat => $kitab)
                                    @if(!empty($kitab))
                                        <span class="inline-flex items-center px-2 py-1 rounded-md text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 shadow-sm">
                                            <span class="opacity-60 mr-1">Kls {{ $tingkat }}:</span> {{ $kitab }}
                                        </span>
                                    @endif
                                @endforeach
                            </div>
                        @else
                            <span class="italic text-gray-400 text-xs"><i class="fas fa-minus-circle mr-1 opacity-50"></i> Tanpa Kitab Spesifik</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                        <i class="fas fa-eye {{ $p->status == 'Aktif' ? 'text-emerald-500' : 'text-gray-300' }} text-lg" title="{{ $p->status ?? 'Aktif' }}"></i>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium space-x-1 flex justify-center">
                        <button onclick='bukaModalEdit(@json($p))' title="Edit Pelajaran" class="w-8 h-8 rounded-full bg-orange-50 text-orange-400 hover:bg-orange-400 hover:text-white transition flex items-center justify-center">
                            <i class="fas fa-edit"></i>
                        </button>
                        <form action="/master-pelajaran/{{ $p->id }}" method="POST" class="inline-block" onsubmit="return confirm('Yakin ingin menghapus pelajaran ini?')">
                            @csrf @method('DELETE')
                            <button type="submit" title="Hapus Pelajaran" class="w-8 h-8 rounded-full bg-red-50 text-red-500 hover:bg-red-500 hover:text-white transition flex items-center justify-center">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-10 text-center text-gray-400 font-medium">Belum ada data pelajaran.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- MODAL TAMBAH/EDIT -->
    <div id="modal-pelajaran" class="hidden fixed inset-0 bg-gray-900 bg-opacity-60 overflow-y-auto h-full w-full z-50 flex items-center justify-center backdrop-blur-sm">
        <div class="relative mx-auto p-6 border w-full max-w-xl shadow-2xl rounded-2xl bg-white">
            <div class="flex justify-between items-center mb-5 border-b pb-3">
                <h3 class="text-lg font-bold text-gray-800" id="modal-judul">Tambah Pelajaran</h3>
                <button onclick="tutupModal()" class="text-gray-400 hover:text-gray-600 transition"><i class="fas fa-times text-xl"></i></button>
            </div>
            
            <form id="form-pelajaran" method="POST" action="/master-pelajaran">
                @csrf
                <input type="hidden" name="_method" id="form-method" value="POST">
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Kode Pelajaran</label>
                        <input type="text" name="kode_pelajaran" id="input-kode" value="{{ $kodeBaru }}" readonly class="w-full border border-gray-300 rounded-lg p-2.5 bg-gray-100 text-gray-500 font-bold outline-none cursor-not-allowed">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Status Pelajaran</label>
                        <select name="status" id="input-status" class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-indigo-500 outline-none cursor-pointer bg-white">
                            <option value="Aktif">Aktif</option>
                            <option value="Nonaktif">Nonaktif</option>
                        </select>
                    </div>
                </div>

                <div class="mb-5">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Nama Pelajaran</label>
                    <input type="text" name="nama_pelajaran" id="input-nama" required class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-indigo-500 outline-none">
                </div>

                <!-- BLOK INPUT KITAB PER TINGKAT (Pengganti Input Lama) -->
                <div class="mb-6 bg-slate-50 p-4 rounded-xl border border-slate-200 shadow-inner">
                    <h4 class="text-sm font-bold text-slate-700 mb-3"><i class="fas fa-book-open text-emerald-600 mr-2"></i> Rincian Kitab Per Tingkat (Opsional)</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1.5 pl-1">Kelas 7</label>
                            <input type="text" name="kitab_tingkat[7]" id="input-kitab-7" class="w-full border border-slate-300 rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-emerald-500 outline-none bg-white" placeholder="Cth: Safinatun Najah">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1.5 pl-1">Kelas 8</label>
                            <input type="text" name="kitab_tingkat[8]" id="input-kitab-8" class="w-full border border-slate-300 rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-emerald-500 outline-none bg-white" placeholder="Cth: Fathul Qarib">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1.5 pl-1">Kelas 9</label>
                            <input type="text" name="kitab_tingkat[9]" id="input-kitab-9" class="w-full border border-slate-300 rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-emerald-500 outline-none bg-white" placeholder="Cth: Fathul Mu'in">
                        </div>
                    </div>
                </div>

                <div class="flex justify-end space-x-3 border-t pt-5">
                    <button type="button" onclick="tutupModal()" class="px-5 py-2.5 bg-gray-100 text-gray-700 rounded-xl font-bold hover:bg-gray-200 transition">Batal</button>
                    <button type="submit" class="px-5 py-2.5 bg-indigo-600 text-white rounded-xl font-bold hover:bg-indigo-700 transition shadow-md">Simpan Data</button>
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
            
            // Set default value
            document.getElementById('input-kode').value = "{{ $kodeBaru }}";
            document.getElementById('input-status').value = "Aktif";
            
            // Kosongkan input kitab
            document.getElementById('input-kitab-7').value = "";
            document.getElementById('input-kitab-8').value = "";
            document.getElementById('input-kitab-9').value = "";
        }

        function bukaModalEdit(pelajaran) {
            document.getElementById('modal-pelajaran').classList.remove('hidden');
            document.getElementById('modal-judul').innerText = "Edit Pelajaran";
            document.getElementById('form-pelajaran').action = "/master-pelajaran/" + pelajaran.id;
            document.getElementById('form-method').value = "PUT";
            
            // Isi data utama
            document.getElementById('input-kode').value = pelajaran.kode_pelajaran;
            document.getElementById('input-nama').value = pelajaran.nama_pelajaran;
            document.getElementById('input-status').value = pelajaran.status || 'Aktif';
            
            // Isi data kitab dinamis secara aman (Mencegah error jika kitab_tingkat null)
            let kitab = pelajaran.kitab_tingkat || {};
            document.getElementById('input-kitab-7').value = kitab['7'] || '';
            document.getElementById('input-kitab-8').value = kitab['8'] || '';
            document.getElementById('input-kitab-9').value = kitab['9'] || '';
        }

        function tutupModal() {
            document.getElementById('modal-pelajaran').classList.add('hidden');
        }
    </script>
@endsection