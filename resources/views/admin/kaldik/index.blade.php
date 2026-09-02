@extends('layouts.app')

@section('title', 'Kalender Pendidikan & Hari Libur')

@section('content')
<div class="space-y-6">
    <!-- Header Premium ala Hari Libur -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-2xl shadow-xs border border-gray-100">
        <div class="flex items-center space-x-3">
            <div class="p-3 bg-rose-50 text-rose-600 rounded-xl">
                <i class="fas fa-calendar-times text-2xl"></i>
            </div>
            <div>
                <h2 class="text-xl font-bold text-gray-800">Kalender Pendidikan & Hari Libur</h2>
                <p class="text-xs text-gray-500">Tahun Ajaran: {{ $periodeAktif->tahun_ajaran }} ({{ $periodeAktif->semester }}). Kelola agenda libur, UTS, UAS, dan kegiatan parsial.</p>
            </div>
        </div>

        <button onclick="bukaModalTambah()" class="bg-rose-600 hover:bg-rose-700 text-white px-4 py-2.5 rounded-xl text-xs font-bold transition shadow-sm flex items-center space-x-2 cursor-pointer">
            <i class="fas fa-plus"></i>
            <span>Tambah Agenda Kaldik</span>
        </button>
    </div>

    <!-- Alert Notifikasi -->
    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl shadow-sm text-sm font-bold flex items-center">
            <i class="fas fa-check-circle mr-2 text-emerald-500"></i>
            {{ session('success') }}
        </div>
    @endif

    <!-- Tabel Data Komprehensif -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-gray-50 text-gray-500 uppercase tracking-wider font-bold border-b border-gray-200">
                        <th class="py-3.5 px-4 text-center w-12">No</th>
                        <th class="py-3.5 px-4">Nama Agenda</th>
                        <th class="py-3.5 px-4 text-center">Jenis</th>
                        <th class="py-3.5 px-4 text-center">Rentang Tanggal</th>
                        <th class="py-3.5 px-4 text-center">Tipe Agenda</th>
                        <th class="py-3.5 px-4 text-center">Cakupan Kelas</th>
                        <th class="py-3.5 px-4 text-center">Cakupan Jam</th>
                        <th class="py-3.5 px-4 text-center">Keterangan</th>
                        <th class="py-3.5 px-4 text-center w-20">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-gray-700 font-medium">
                    @forelse($agenda as $index => $item)
                    <tr class="hover:bg-rose-50/20 transition">
                        <td class="py-3.5 px-4 text-center font-bold text-gray-400">{{ $index + 1 }}</td>
                        <td class="py-3.5 px-4 font-bold text-gray-800">{{ $item->nama_agenda }}</td>
                        
                        <!-- Kolom Jenis Agenda -->
                        <td class="py-3.5 px-4 text-center">
                            @php
                                $colorJenis = match($item->jenis_agenda) {
                                    'UTS' => 'bg-amber-100 text-amber-800 border-amber-200',
                                    'UAS' => 'bg-rose-100 text-rose-800 border-rose-200',
                                    'Libur' => 'bg-red-100 text-red-800 border-red-200',
                                    default => 'bg-blue-100 text-blue-800 border-blue-200',
                                };
                            @endphp
                            <span class="px-2.5 py-1 rounded-md text-[10px] font-bold border {{ $colorJenis }} uppercase shadow-sm">
                                {{ $item->jenis_agenda }}
                            </span>
                        </td>

                        <td class="py-3.5 px-4 text-center font-semibold text-gray-700">
                            {{ \Carbon\Carbon::parse($item->tanggal_mulai)->translatedFormat('d M Y') }}
                            @if($item->tanggal_mulai != $item->tanggal_selesai)
                                <span class="text-gray-400 font-normal">s/d</span> {{ \Carbon\Carbon::parse($item->tanggal_selesai)->translatedFormat('d M Y') }}
                            @endif
                        </td>
                        
                        <td class="py-3.5 px-4 text-center">
                            @if($item->tipe_agenda == 'Penuh')
                                <span class="px-2.5 py-1 rounded-full bg-rose-50 text-rose-700 border border-rose-200 text-[10px] font-bold">1 Hari Full</span>
                            @else
                                <span class="px-2.5 py-1 rounded-full bg-amber-50 text-amber-700 border border-amber-200 text-[10px] font-bold">Parsial / Jam</span>
                            @endif
                        </td>
                        
                        <td class="py-3.5 px-4 text-center font-bold">
                            @if($item->target_libur == 'semua')
                                <span class="text-emerald-600 text-xs">Semua Kelas</span>
                            @else
                                @php
                                    $kelasArr = is_string($item->kelas_ids) ? json_decode($item->kelas_ids, true) : (is_array($item->kelas_ids) ? $item->kelas_ids : []);
                                    $namaKls = (!empty($kelasArr) && class_exists('\App\Models\Kelas')) ? \App\Models\Kelas::whereIn('id', $kelasArr)->pluck('nama_kelas')->implode(', ') : 'Tertentu';
                                @endphp
                                <span class="text-indigo-600 text-[11px]">Kls: {{ $namaKls }}</span>
                            @endif
                        </td>

                        <td class="py-3.5 px-4 text-center font-bold text-gray-600">
                            @if($item->tipe_agenda == 'Penuh')
                                <span class="text-gray-400 text-[11px]">Semua Jam (Full)</span>
                            @else
                                @php
                                    $jamArr = is_string($item->jam_diliburkan) ? json_decode($item->jam_diliburkan, true) : (is_array($item->jam_diliburkan) ? $item->jam_diliburkan : []);
                                    // Rapikan angka jika berurutan (opsional)
                                    if (is_array($jamArr) && !empty($jamArr)) {
                                        $jamArr = array_map('intval', $jamArr);
                                        sort($jamArr);
                                        $teksJam = count($jamArr) > 1 && max($jamArr) - min($jamArr) + 1 == count($jamArr) ? min($jamArr) . '-' . max($jamArr) : implode(', ', $jamArr);
                                    } else {
                                        $teksJam = '-';
                                    }
                                @endphp
                                <span class="text-amber-700 text-[11px]">Jam {{ $teksJam }}</span>
                            @endif
                        </td>
                        
                        <td class="py-3.5 px-4 text-gray-500 text-center">{{ $item->keterangan ?? '-' }}</td>
                        <td class="py-3.5 px-4 text-center">
                            <!-- Tombol Hapus Ikonik -->
                            <form action="{{ route('agenda-kaldik.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus agenda kaldik ini?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="p-1.5 text-rose-500 hover:bg-rose-50 rounded-lg transition" title="Hapus"><i class="fas fa-trash-alt"></i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="py-12 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <i class="fas fa-folder-open text-gray-300 text-4xl mb-3"></i>
                                <span class="text-gray-400 font-bold">Belum ada agenda kalender pendidikan.</span>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah Agenda Kaldik -->
<div id="modal-hari-libur" class="hidden fixed inset-0 bg-gray-900 bg-opacity-60 overflow-y-auto h-full w-full z-50 flex items-center justify-center backdrop-blur-sm">
    <div class="relative mx-auto p-6 border w-full max-w-lg shadow-2xl rounded-2xl bg-white">
        <div class="flex justify-between items-center mb-4 border-b pb-3">
            <h3 class="text-base font-bold text-gray-800"><i class="fas fa-calendar-plus text-rose-600 mr-2"></i> Tambah Agenda Kaldik</h3>
            <button onclick="tutupModal()" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times text-lg"></i></button>
        </div>

        <form action="{{ route('agenda-kaldik.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">Nama Agenda / Event</label>
                <input type="text" name="nama_agenda" required placeholder="Contoh: Ujian Tengah Semester / Rapat" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-xs focus:ring-2 focus:ring-rose-500 outline-none">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Jenis Agenda</label>
                    <select name="jenis_agenda" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-xs focus:ring-2 focus:ring-rose-500 outline-none bg-white font-bold cursor-pointer">
                        <option value="Libur">Libur KBM</option>
                        <option value="UTS">UTS</option>
                        <option value="UAS">UAS</option>
                        <option value="Kegiatan">Kegiatan Lainnya</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Cakupan Kelas</label>
                    <select name="target_libur" id="target_libur" onchange="toggleTargetLibur(this.value)" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-xs focus:ring-2 focus:ring-rose-500 outline-none bg-white font-bold cursor-pointer">
                        <option value="semua">Semua Kelas</option>
                        <option value="kelas_tertentu">Hanya Kelas Tertentu</option>
                    </select>
                </div>
            </div>

            <div id="box-pilih-kelas" class="hidden bg-indigo-50/50 p-3 rounded-xl border border-indigo-200">
                <label class="block text-xs font-bold text-indigo-800 mb-2">Pilih Kelas yang Diliburkan:</label>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 max-h-32 overflow-y-auto p-1">
                    @foreach($kelas as $kls)
                    <label class="flex items-center p-1.5 bg-white border border-indigo-200 rounded-lg text-xs cursor-pointer hover:bg-indigo-100">
                        <input type="checkbox" name="kelas_ids[]" value="{{ $kls->id }}" class="text-indigo-600 rounded mr-1.5 focus:ring-indigo-500">
                        <span class="font-bold text-gray-800">{{ $kls->nama_kelas }}</span>
                    </label>
                    @endforeach
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
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
                <label class="block text-xs font-bold text-gray-700 mb-1">Tipe Agenda Libur</label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-1">
                    <label class="flex items-center p-2.5 border rounded-xl cursor-pointer hover:bg-gray-50 border-rose-300">
                        <input type="radio" name="tipe_agenda" value="Penuh" checked onchange="toggleTipeLibur(this.value)" class="text-rose-600 focus:ring-rose-500">
                        <span class="ml-2 text-xs font-bold text-gray-700">Libur Full (1 Hari)</span>
                    </label>
                    <label class="flex items-center p-2.5 border rounded-xl cursor-pointer hover:bg-gray-50 border-gray-200">
                        <input type="radio" name="tipe_agenda" value="Parsial" onchange="toggleTipeLibur(this.value)" class="text-rose-600 focus:ring-rose-500">
                        <span class="ml-2 text-xs font-bold text-gray-700">Libur Parsial (Jam)</span>
                    </label>
                </div>
            </div>

            <div id="box-jam-parsial" class="hidden bg-amber-50/50 p-3 rounded-xl border border-amber-200">
                <label class="block text-xs font-bold text-amber-800 mb-2">Pilih Jam yang Diliburkan:</label>
                <div class="grid grid-cols-3 sm:grid-cols-5 gap-2">
                    @for($i=1; $i<=10; $i++)
                    <label class="flex items-center justify-center p-1.5 bg-white border border-amber-200 rounded-lg text-xs cursor-pointer hover:bg-amber-100">
                        <input type="checkbox" name="jam_diliburkan[]" value="{{ $i }}" class="text-amber-600 rounded mr-1">
                        <span class="font-bold text-amber-900">Jam {{ $i }}</span>
                    </label>
                    @endfor
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

    // Mencegah Tanggal Selesai lebih mundur dari Tanggal Mulai
    document.querySelector('input[name="tanggal_mulai"]').addEventListener('change', function() {
        let tglSelesai = document.querySelector('input[name="tanggal_selesai"]');
        if(tglSelesai.value < this.value) {
            tglSelesai.value = this.value; // Samakan otomatis
        }
        tglSelesai.min = this.value; // Kunci kalender
    });
</script>
@endsection