@extends('layouts.app')

@section('title', 'Manajemen Pengumuman')

@section('content')
<div class="space-y-6">
    <!-- Header Premium -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-2xl shadow-xs border border-gray-100">
        <div class="flex items-center space-x-3">
            <div class="p-3 bg-blue-50 text-blue-600 rounded-xl">
                <i class="fas fa-bullhorn text-2xl"></i>
            </div>
            <div>
                <h2 class="text-xl font-bold text-gray-800">Manajemen Pengumuman</h2>
                <p class="text-xs text-gray-500">Kelola pengumuman yang ditampilkan di Dashboard Guru.</p>
            </div>
        </div>

        <button onclick="bukaModalTambah()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 rounded-xl text-xs font-bold transition shadow-sm flex items-center space-x-2 cursor-pointer">
            <i class="fas fa-plus"></i>
            <span>Tambah Pengumuman</span>
        </button>
    </div>

    <!-- Alert Notifikasi -->
    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl shadow-sm text-sm font-bold flex items-center">
            <i class="fas fa-check-circle mr-2 text-emerald-500"></i>
            {{ session('success') }}
        </div>
    @endif

    <!-- Tabel Data -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-gray-50 text-gray-500 uppercase tracking-wider font-bold border-b border-gray-200">
                        <th class="py-3.5 px-4 text-center w-12">No</th>
                        <th class="py-3.5 px-4">Judul</th>
                        <th class="py-3.5 px-4">Isi</th>
                        <th class="py-3.5 px-4 text-center">Warna</th>
                        <th class="py-3.5 px-4 text-center">Gambar BG</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4 text-center">Periode Tampil</th>
                        <th class="py-3.5 px-4 text-center">Dibuat Oleh</th>
                        <th class="py-3.5 px-4 text-center w-32">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-gray-700 font-medium">
                    @forelse($pengumuman as $index => $item)
                    <tr class="hover:bg-blue-50/20 transition">
                        <td class="py-3.5 px-4 text-center font-bold text-gray-400">{{ $index + 1 }}</td>
                        <td class="py-3.5 px-4 font-bold text-gray-800">{{ $item->judul }}</td>
                        <td class="py-3.5 px-4 text-gray-600 max-w-[200px] truncate">{{ $item->isi ?? '-' }}</td>
                        <td class="py-3.5 px-4 text-center">
                            @php
                                $colorMap = [
                                    'emerald' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                                    'blue' => 'bg-blue-100 text-blue-700 border-blue-200',
                                    'amber' => 'bg-amber-100 text-amber-700 border-amber-200',
                                    'rose' => 'bg-rose-100 text-rose-700 border-rose-200',
                                    'violet' => 'bg-violet-100 text-violet-700 border-violet-200',
                                    'cyan' => 'bg-cyan-100 text-cyan-700 border-cyan-200',
                                    'indigo' => 'bg-indigo-100 text-indigo-700 border-indigo-200',
                                ];
                                $colorClass = $colorMap[$item->warna] ?? $colorMap['emerald'];
                            @endphp
                            <span class="px-2.5 py-1 rounded-md text-[10px] font-bold border {{ $colorClass }} uppercase shadow-sm">
                                {{ ucfirst($item->warna) }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            @if($item->gambar)
                                <img src="{{ asset('storage/' . $item->gambar) }}" alt="BG" class="h-10 w-16 object-cover rounded-lg border border-gray-200">
                            @else
                                <span class="text-gray-400 text-[11px]">-</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            @if($item->aktif)
                                <span class="px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 text-[10px] font-bold">Aktif</span>
                            @else
                                <span class="px-2.5 py-1 rounded-full bg-gray-50 text-gray-500 border border-gray-200 text-[10px] font-bold">Nonaktif</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-center font-semibold text-gray-700">
                            @if($item->tanggal_mulai)
                                {{ \Carbon\Carbon::parse($item->tanggal_mulai)->translatedFormat('d M Y') }}
                                @if($item->tanggal_selesai)
                                    <span class="text-gray-400 font-normal">s/d</span> {{ \Carbon\Carbon::parse($item->tanggal_selesai)->translatedFormat('d M Y') }}
                                @endif
                            @else
                                <span class="text-gray-400">Selalu Tampil</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-center text-gray-500">{{ $item->pembuat->name ?? '-' }}</td>
                        <td class="py-3.5 px-4 text-center">
                            <div class="flex items-center justify-center space-x-2">
                                <button type="button" class="js-edit p-1.5 text-blue-500 hover:bg-blue-50 rounded-lg transition" title="Edit"
                        data-id="{{ $item->id }}"
                        data-judul="{{ $item->judul }}"
                        data-isi="{{ $item->isi }}"
                        data-warna="{{ $item->warna }}"
                        data-gambar="{{ $item->gambar }}"
                        data-aktif="{{ $item->aktif ? 1 : 0 }}"
                        data-mulai="{{ $item->tanggal_mulai ? $item->tanggal_mulai->format('Y-m-d') : '' }}"
                        data-selesai="{{ $item->tanggal_selesai ? $item->tanggal_selesai->format('Y-m-d') : '' }}">
                        <i class="fas fa-pen"></i>
                    </button>
                                <form action="{{ route('pengumuman.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pengumuman ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-1.5 text-rose-500 hover:bg-rose-50 rounded-lg transition" title="Hapus"><i class="fas fa-trash-alt"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="py-12 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <i class="fas fa-bullhorn text-gray-300 text-4xl mb-3"></i>
                                <span class="text-gray-400 font-bold">Belum ada pengumuman.</span>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah Pengumuman -->
<div id="modal-tambah" class="hidden fixed inset-0 bg-gray-900 bg-opacity-60 overflow-y-auto h-full w-full z-50 flex items-center justify-center backdrop-blur-sm">
    <div class="relative mx-auto p-6 border w-full max-w-lg shadow-2xl rounded-2xl bg-white">
        <div class="flex justify-between items-center mb-4 border-b pb-3">
            <h3 class="text-base font-bold text-gray-800"><i class="fas fa-bullhorn text-blue-600 mr-2"></i> Tambah Pengumuman</h3>
            <button onclick="tutupModalTambah()" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times text-lg"></i></button>
        </div>

        <form action="{{ route('pengumuman.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">Judul Pengumuman</label>
                <input type="text" name="judul" required placeholder="Contoh: Jadwal UTS Semester Ganjil" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-xs focus:ring-2 focus:ring-blue-500 outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">Isi Pengumuman</label>
                <textarea name="isi" rows="4" placeholder="Tuliskan isi pengumuman di sini..." class="w-full border border-gray-300 rounded-xl p-2 text-xs focus:ring-2 focus:ring-blue-500 outline-none"></textarea>
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">Gambar Background (Opsional)</label>
                <input type="file" name="gambar" accept="image/*" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-xs focus:ring-2 focus:ring-blue-500 outline-none bg-white cursor-pointer">
                <p class="text-[10px] text-gray-400 mt-1">Upload gambar bergaya kartu BYOND (JPG/PNG). Jika kosong, warna tema dipakai.</p>
            </div>

<div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Warna Tema</label>
                    <select name="warna" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-xs focus:ring-2 focus:ring-blue-500 outline-none bg-white font-bold cursor-pointer">
                        <option value="emerald">Hijau (Emerald)</option>
                        <option value="blue">Biru (Blue)</option>
                        <option value="amber">Kuning (Amber)</option>
                        <option value="rose">Merah (Rose)</option>
                        <option value="violet">Ungu (Violet)</option>
                        <option value="cyan">Cyan</option>
                        <option value="indigo">Indigo</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Status</label>
                    <label class="flex items-center p-2.5 border rounded-xl cursor-pointer hover:bg-gray-50 border-gray-200 mt-1">
                        <input type="checkbox" name="aktif" value="1" checked class="text-blue-600 rounded mr-2 focus:ring-blue-500">
                        <span class="text-xs font-bold text-gray-700">Aktifkan Pengumuman</span>
                    </label>
                </div>
            </div>

<div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Tanggal Mulai (Opsional)</label>
                    <input type="date" name="tanggal_mulai" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-xs focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Tanggal Selesai (Opsional)</label>
                    <input type="date" name="tanggal_selesai" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-xs focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
            </div>
            <p class="text-[10px] text-gray-400 -mt-2">Jika kosong, pengumuman akan selalu ditampilkan.</p>

            <div class="flex justify-end space-x-2 pt-3 border-t">
                <button type="button" onclick="tutupModalTambah()" class="px-4 py-2 bg-gray-100 text-gray-700 text-xs font-bold rounded-xl hover:bg-gray-200">Batal</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white text-xs font-bold rounded-xl hover:bg-blue-700 shadow-sm">Simpan Pengumuman</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Pengumuman -->
<div id="modal-edit" class="hidden fixed inset-0 bg-gray-900 bg-opacity-60 overflow-y-auto h-full w-full z-50 flex items-center justify-center backdrop-blur-sm">
    <div class="relative mx-auto p-6 border w-full max-w-lg shadow-2xl rounded-2xl bg-white">
        <div class="flex justify-between items-center mb-4 border-b pb-3">
            <h3 class="text-base font-bold text-gray-800"><i class="fas fa-pen text-blue-600 mr-2"></i> Edit Pengumuman</h3>
            <button onclick="tutupModalEdit()" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times text-lg"></i></button>
        </div>

        <form id="form-edit" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">Judul Pengumuman</label>
                <input type="text" name="judul" id="edit_judul" required class="w-full border border-gray-300 rounded-xl px-3 py-2 text-xs focus:ring-2 focus:ring-blue-500 outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">Isi Pengumuman</label>
                <textarea name="isi" id="edit_isi" rows="4" class="w-full border border-gray-300 rounded-xl p-2 text-xs focus:ring-2 focus:ring-blue-500 outline-none"></textarea>
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">Gambar Background (Opsional)</label>
                <div id="edit_gambar_preview" class="mb-2"></div>
                <input type="file" name="gambar" accept="image/*" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-xs focus:ring-2 focus:ring-blue-500 outline-none bg-white cursor-pointer">
                <p class="text-[10px] text-gray-400 mt-1">Biarkan kosong untuk mempertahankan gambar lama.</p>
            </div>

<div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Warna Tema</label>
                    <select name="warna" id="edit_warna" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-xs focus:ring-2 focus:ring-blue-500 outline-none bg-white font-bold cursor-pointer">
                        <option value="emerald">Hijau (Emerald)</option>
                        <option value="blue">Biru (Blue)</option>
                        <option value="amber">Kuning (Amber)</option>
                        <option value="rose">Merah (Rose)</option>
                        <option value="violet">Ungu (Violet)</option>
                        <option value="cyan">Cyan</option>
                        <option value="indigo">Indigo</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Status</label>
                    <label class="flex items-center p-2.5 border rounded-xl cursor-pointer hover:bg-gray-50 border-gray-200 mt-1">
                        <input type="checkbox" name="aktif" id="edit_aktif" value="1" class="text-blue-600 rounded mr-2 focus:ring-blue-500">
                        <span class="text-xs font-bold text-gray-700">Aktifkan Pengumuman</span>
                    </label>
                </div>
            </div>

<div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Tanggal Mulai (Opsional)</label>
                    <input type="date" name="tanggal_mulai" id="edit_tgl_mulai" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-xs focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Tanggal Selesai (Opsional)</label>
                    <input type="date" name="tanggal_selesai" id="edit_tgl_selesai" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-xs focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
            </div>

            <div class="flex justify-end space-x-2 pt-3 border-t">
                <button type="button" onclick="tutupModalEdit()" class="px-4 py-2 bg-gray-100 text-gray-700 text-xs font-bold rounded-xl hover:bg-gray-200">Batal</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white text-xs font-bold rounded-xl hover:bg-blue-700 shadow-sm">Perbarui Pengumuman</button>
            </div>
        </form>
    </div>
</div>

<script>
    function bukaModalTambah() {
        document.getElementById('modal-tambah').classList.remove('hidden');
    }
    function tutupModalTambah() {
        document.getElementById('modal-tambah').classList.add('hidden');
    }
    function tutupModalEdit() {
        document.getElementById('modal-edit').classList.add('hidden');
    }

    document.addEventListener('click', function(e) {
        var btn = e.target.closest('.js-edit');
        if (!btn) return;
        document.getElementById('form-edit').action = '/pengumuman/' + btn.dataset.id;
        document.getElementById('edit_judul').value = btn.dataset.judul || '';
        document.getElementById('edit_isi').value = btn.dataset.isi || '';
        document.getElementById('edit_warna').value = btn.dataset.warna || 'emerald';
        document.getElementById('edit_aktif').checked = btn.dataset.aktif === '1';
        document.getElementById('edit_tgl_mulai').value = btn.dataset.mulai || '';
        document.getElementById('edit_tgl_selesai').value = btn.dataset.selesai || '';
        var preview = document.getElementById('edit_gambar_preview');
        if (preview) {
            if (btn.dataset.gambar) {
                preview.innerHTML = '<img src="/storage/' + btn.dataset.gambar + '" alt="BG" class="h-16 w-28 object-cover rounded-lg border border-gray-200">';
            } else {
                preview.innerHTML = '';
            }
        }
        document.getElementById('modal-edit').classList.remove('hidden');
    });

    var tglMulaiAdd = document.querySelector('#modal-tambah input[name="tanggal_mulai"]');
    if (tglMulaiAdd) {
        tglMulaiAdd.addEventListener('change', function() {
            var tglSelesai = document.querySelector('#modal-tambah input[name="tanggal_selesai"]');
            if (tglSelesai) {
                if (tglSelesai.value && tglSelesai.value < this.value) {
                    tglSelesai.value = this.value;
                }
                tglSelesai.min = this.value;
            }
        });
    }
</script>
@endsection
