@extends('layouts.app')

@section('title', 'Kalender Pendidikan & Hari Libur')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-2xl shadow-xs border border-gray-100">
        <div class="flex items-center space-x-3">
            <div class="p-3 bg-rose-50 text-rose-600 rounded-xl">
                <i class="fas fa-calendar-times text-2xl"></i>
            </div>
            <div>
                <h2 class="text-xl font-bold text-gray-800">Kalender Pendidikan & Hari Libur</h2>
                <p class="text-xs text-gray-500">Kelola agenda libur nasional, libur pesantren, maupun libur parsial jam pelajaran.</p>
            </div>
        </div>

        <button onclick="bukaModalTambah()" class="bg-rose-600 hover:bg-rose-700 text-white px-4 py-2.5 rounded-xl text-xs font-bold transition shadow-sm flex items-center space-x-2 cursor-pointer">
            <i class="fas fa-plus"></i>
            <span>Tambah Agenda Libur</span>
        </button>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-gray-50 text-gray-500 uppercase tracking-wider font-bold border-b border-gray-200">
                        <th class="py-3.5 px-4 text-center w-12">No</th>
                        <th class="py-3.5 px-4">Nama Agenda / Event</th>
                        <th class="py-3.5 px-4 text-center">Rentang Tanggal</th>
                        <th class="py-3.5 px-4 text-center">Tipe Libur</th>
                        
                        <th class="py-3.5 px-4 text-center">Cakupan Kelas</th>
                        
                        <th class="py-3.5 px-4 text-center">Cakupan Jam</th>
                        <th class="py-3.5 px-4 text-center">Keterangan</th>
                        <th class="py-3.5 px-4 text-center w-20">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-gray-700 font-medium">
                    @forelse($hariLiburs as $index => $libur)
                    <tr class="hover:bg-rose-50/20 transition">
                        <td class="py-3.5 px-4 text-center font-bold text-gray-400">{{ $index + 1 }}</td>
                        <td class="py-3.5 px-4 font-bold text-gray-800">{{ $libur->nama_libur }}</td>
                        <td class="py-3.5 px-4 text-center font-semibold text-gray-700">
                            {{ \Carbon\Carbon::parse($libur->tanggal_mulai)->translatedFormat('d M Y') }}
                            @if($libur->tanggal_mulai != $libur->tanggal_selesai)
                                <span class="text-gray-400 font-normal">s/d</span> {{ \Carbon\Carbon::parse($libur->tanggal_selesai)->translatedFormat('d M Y') }}
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            @if($libur->tipe_libur == 'Penuh')
                                <span class="px-2.5 py-1 rounded-full bg-rose-50 text-rose-700 border border-rose-200 text-[10px] font-bold">1 Hari Full</span>
                            @else
                                <span class="px-2.5 py-1 rounded-full bg-amber-50 text-amber-700 border border-amber-200 text-[10px] font-bold">Parsial / Jam</span>
                            @endif
                        </td>
                        
                        <td class="py-3.5 px-4 text-center font-bold">
                            @if($libur->target_libur == 'semua')
                                <span class="text-emerald-600 text-xs">Semua Kelas</span>
                            @else
                                @php
                                    $kelasArr = is_string($libur->kelas_ids) ? json_decode($libur->kelas_ids, true) : (is_array($libur->kelas_ids) ? $libur->kelas_ids : []);
                                    $namaKls = (!empty($kelasArr) && class_exists('\App\Models\Kelas')) ? \App\Models\Kelas::whereIn('id', $kelasArr)->pluck('nama_kelas')->implode(', ') : 'Tertentu';
                                @endphp
                                <span class="text-indigo-600 text-xs">Kls: {{ $namaKls }}</span>
                            @endif
                        </td>

                        <td class="py-3.5 px-4 text-center font-bold text-gray-600">
                            @if($libur->tipe_libur == 'Penuh')
                                <span class="text-gray-400 text-xs">Semua Jam (Full)</span>
                            @else
                                @php
                                    $jamArr = is_string($libur->jam_diliburkan) ? json_decode($libur->jam_diliburkan, true) : (is_array($libur->jam_diliburkan) ? $libur->jam_diliburkan : []);
                                @endphp
                                <span class="text-amber-700 text-xs">Jam Ke-{{ is_array($jamArr) ? implode(', ', $jamArr) : '-' }}</span>
                            @endif
                        </td>
                        
                        <td class="py-3.5 px-4 text-gray-500 text-center">{{ $libur->keterangan ?? '-' }}</td>
                        <td class="py-3.5 px-4 text-center">
                            <form action="/hari-libur/{{ $libur->id }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus agenda libur ini?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="p-1.5 text-rose-500 hover:bg-rose-50 rounded-lg transition"><i class="fas fa-trash-alt"></i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-10 text-center text-gray-400">Belum ada agenda hari libur.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="modal-hari-libur" class="hidden fixed inset-0 bg-gray-900 bg-opacity-60 overflow-y-auto h-full w-full z-50 flex items-center justify-center backdrop-blur-sm">
    <div class="relative mx-auto p-6 border w-full max-w-lg shadow-2xl rounded-2xl bg-white">
        <div class="flex justify-between items-center mb-4 border-b pb-3">
            <h3 class="text-base font-bold text-gray-800"><i class="fas fa-calendar-plus text-rose-600 mr-2"></i> Tambah Agenda Hari Libur</h3>
            <button onclick="tutupModal()" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times text-lg"></i></button>
        </div>

        <form action="/hari-libur" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">Nama Agenda / Event Libur</label>
                <input type="text" name="nama_libur" required placeholder="Contoh: Upacara Kemerdekaan / Rapat Guru" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-xs focus:ring-2 focus:ring-rose-500 outline-none">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Tanggal Mulai</label>
                    <input type="date" name="tanggal_mulai" required value="{{ date('Y-m-d') }}" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-xs focus:ring-2 focus:ring-rose-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Tanggal Selesai</label>
                    <input type="date" name="tanggal_selesai" required value="{{ date('Y-m-d') }}" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-xs focus:ring-2 focus:ring-rose-500 outline-none">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">Cakupan Wilayah Libur</label>
                <select name="target_libur" id="target_libur" onchange="toggleTargetLibur(this.value)" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-xs focus:ring-2 focus:ring-rose-500 outline-none bg-white font-bold cursor-pointer">
                    <option value="semua">Semua Kelas</option>
                    <option value="kelas_tertentu">Hanya Kelas / Rombel Tertentu</option>
                </select>
            </div>

            <div id="box-pilih-kelas" class="hidden bg-rose-50/50 p-3 rounded-xl border border-rose-200">
                <label class="block text-xs font-bold text-rose-800 mb-2">Pilih Kelas yang Diliburkan:</label>
                <div class="grid grid-cols-3 gap-2 max-h-40 overflow-y-auto p-1">
                    @foreach($semuaKelas as $kls)
                    <label class="flex items-center p-1.5 bg-white border border-rose-200 rounded-lg text-xs cursor-pointer hover:bg-rose-100">
                        <input type="checkbox" name="kelas_ids[]" value="{{ $kls->id }}" class="text-rose-600 rounded mr-1.5">
                        <span class="font-bold text-gray-800">{{ $kls->nama_kelas }}</span>
                    </label>
                    @endforeach
                </div>
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">Tipe Libur</label>
                <div class="grid grid-cols-2 gap-3 mt-1">
                    <label class="flex items-center p-2.5 border rounded-xl cursor-pointer hover:bg-gray-50 border-rose-300">
                        <input type="radio" name="tipe_libur" value="Penuh" checked onchange="toggleTipeLibur(this.value)" class="text-rose-600 focus:ring-rose-500">
                        <span class="ml-2 text-xs font-bold text-gray-700">Libur Full (1 Hari)</span>
                    </label>
                    <label class="flex items-center p-2.5 border rounded-xl cursor-pointer hover:bg-gray-50 border-gray-200">
                        <input type="radio" name="tipe_libur" value="Parsial" onchange="toggleTipeLibur(this.value)" class="text-rose-600 focus:ring-rose-500">
                        <span class="ml-2 text-xs font-bold text-gray-700">Libur Parsial (Jam)</span>
                    </label>
                </div>
            </div>

            <div id="box-jam-parsial" class="hidden bg-amber-50/50 p-3 rounded-xl border border-amber-200">
                <label class="block text-xs font-bold text-amber-800 mb-2">Pilih Jam yang Diliburkan:</label>
                <div class="grid grid-cols-5 gap-2">
                    @foreach($semuaJam as $jam)
                    <label class="flex items-center justify-center p-1.5 bg-white border border-amber-200 rounded-lg text-xs cursor-pointer hover:bg-amber-100">
                        <input type="checkbox" name="jam_diliburkan[]" value="{{ $jam->jam_ke }}" class="text-amber-600 rounded mr-1">
                        <span class="font-bold text-amber-900">Jam {{ $jam->jam_ke }}</span>
                    </label>
                    @endforeach
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">Keterangan (Opsional)</label>
                <textarea name="keterangan" rows="2" placeholder="Catatan tambahan..." class="w-full border border-gray-300 rounded-xl p-2 text-xs focus:ring-2 focus:ring-rose-500 outline-none"></textarea>
            </div>

            <div class="flex justify-end space-x-2 pt-3 border-t">
                <button type="button" onclick="tutupModal()" class="px-4 py-2 bg-gray-100 text-gray-700 text-xs font-bold rounded-xl hover:bg-gray-200">Batal</button>
                <button type="submit" class="px-4 py-2 bg-rose-600 text-white text-xs font-bold rounded-xl hover:bg-rose-700 shadow-sm">Simpan Agenda</button>
            </div>
        </form>
    </div>
</div>

<script>
    function bukaModalTambah() {
        document.getElementById('modal-hari-libur').classList.remove('hidden');
    }
    function tutupModal() {
        document.getElementById('modal-hari-libur').classList.add('hidden');
    }
    function toggleTipeLibur(val) {
        let box = document.getElementById('box-jam-parsial');
        if(val === 'Parsial') {
            box.classList.remove('hidden');
        } else {
            box.classList.add('hidden');
        }
    }
    function toggleTargetLibur(val) {
        let box = document.getElementById('box-pilih-kelas');
        if(val === 'kelas_tertentu') {
            box.classList.remove('hidden');
        } else {
            box.classList.add('hidden');
        }
    }

    // KECERDASAN UI: Mencegah Tanggal Selesai lebih mundur dari Tanggal Mulai
    document.querySelector('input[name="tanggal_mulai"]').addEventListener('change', function() {
        let tglSelesai = document.querySelector('input[name="tanggal_selesai"]');
        if(tglSelesai.value < this.value) {
            tglSelesai.value = this.value; // Samakan otomatis
        }
        tglSelesai.min = this.value; // Kunci kalender agar tidak bisa pilih tanggal mundur
    });

</script>

@endsection