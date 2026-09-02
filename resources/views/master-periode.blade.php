@extends('layouts.app')
@section('title', 'Master Periode Akademik')

@section('content')
<div class="bg-white p-6 rounded-xl shadow-sm mb-6 border border-gray-100 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
    <div>
        <h2 class="text-xl font-bold text-gray-700">Master Periode Akademik</h2>
        <p class="text-xs text-gray-500 mt-0.5">Kelola Tahun Ajaran, Semester, dan rentang tanggal efektif kalender pendidikan.</p>
    </div>
    <button onclick="bukaModalTambah()" class="bg-emerald-500 hover:bg-emerald-600 text-white px-5 py-2.5 rounded-lg text-sm font-semibold transition flex items-center shadow-sm cursor-pointer self-start sm:self-auto">
        <i class="fas fa-plus mr-2"></i> Tambah Periode
    </button>
</div>


@if ($errors->any())
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded shadow-sm">
        <ul class="list-disc ml-5 mt-1 text-sm font-medium">
            @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
        </ul>
    </div>
@endif

<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider border-b w-12">NO</th>
                <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider border-b">TAHUN AJARAN</th>
                <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider border-b">SEMESTER</th>
                <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider border-b">RENTANG TANGGAL EFEKTIF</th>
                <th class="px-6 py-4 text-center text-xs font-bold text-gray-600 uppercase tracking-wider border-b">STATUS</th>
                <th class="px-6 py-4 text-center text-xs font-bold text-gray-600 uppercase tracking-wider border-b">AKSI</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-100">
            @forelse($periodes as $index => $p)
            <tr class="hover:bg-gray-50 transition">
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 font-bold">{{ $index + 1 }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-bold">{{ $p->tahun_ajaran }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold {{ $p->semester == 'Ganjil' ? 'text-amber-600' : 'text-blue-600' }}">{{ $p->semester }}</td>
                
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 font-medium">
                    @if($p->tanggal_mulai && $p->tanggal_selesai)
                        <i class="far fa-calendar-alt text-emerald-500 mr-1"></i> 
                        {{ \Carbon\Carbon::parse($p->tanggal_mulai)->translatedFormat('d M Y') }} 
                        <span class="text-gray-400 mx-1">s/d</span> 
                        {{ \Carbon\Carbon::parse($p->tanggal_selesai)->translatedFormat('d M Y') }}
                    @else
                        <span class="text-rose-500 text-xs italic"><i class="fas fa-exclamation-triangle"></i> Tanggal belum diatur</span>
                    @endif
                </td>

                <td class="px-6 py-4 whitespace-nowrap text-center">
                    @if($p->is_active)
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700 border border-emerald-200">
                            <i class="fas fa-check-circle mr-1"></i> Aktif
                        </span>
                    @else
                        <form action="/master-periode/set-aktif/{{ $p->id }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="px-3 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-500 hover:bg-emerald-500 hover:text-white transition shadow-sm border border-gray-200 hover:border-emerald-500 cursor-pointer">
                                Set Aktif
                            </button>
                        </form>
                    @endif
                </td>

                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium space-x-1 flex justify-center">
                    <button onclick='bukaModalEdit(@json($p))' class="w-8 h-8 rounded-full bg-orange-50 text-orange-500 hover:bg-orange-500 hover:text-white transition flex items-center justify-center cursor-pointer" title="Edit Periode">
                        <i class="fas fa-edit"></i>
                    </button>
                    <form action="/master-periode/{{ $p->id }}" method="POST" class="inline" onsubmit="return confirm('Yakin ingin menghapus periode ini?');">
                        @csrf @method('DELETE')
                        <button type="submit" class="w-8 h-8 rounded-full bg-red-50 text-red-500 hover:bg-red-500 hover:text-white transition flex items-center justify-center cursor-pointer" title="Hapus">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-6 py-10 text-center text-gray-400 text-sm italic">
                    <i class="fas fa-folder-open text-3xl mb-3 block text-gray-300"></i>
                    Belum ada data periode akademik.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div id="modal-periode" class="hidden fixed inset-0 bg-gray-900 bg-opacity-60 overflow-y-auto h-full w-full z-50 flex items-center justify-center backdrop-blur-sm">
    <div class="relative mx-auto p-6 border w-full max-w-lg shadow-2xl rounded-xl bg-white">
        <div class="flex justify-between items-center mb-5 border-b pb-3">
            <h3 class="text-lg font-bold text-gray-800" id="modal-judul">Tambah Periode</h3>
            <button onclick="tutupModal()" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times text-xl"></i></button>
        </div>
        
        <form id="form-periode" method="POST" action="/master-periode">
            @csrf
            <input type="hidden" name="_method" id="form-method" value="POST">
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Tahun Ajaran</label>
                    <input type="text" name="tahun_ajaran" id="input-tahun" placeholder="Contoh: 2026/2027" required class="w-full border border-gray-300 rounded-lg p-2.5 outline-none focus:border-emerald-500 font-medium">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Semester</label>
                    <select name="semester" id="input-semester" required class="w-full border border-gray-300 rounded-lg p-2.5 outline-none focus:border-emerald-500 font-medium bg-white">
                        <option value="Ganjil">Ganjil</option>
                        <option value="Genap">Genap</option>
                    </select>
                </div>
            </div>

            <div class="bg-emerald-50 p-4 rounded-xl border border-emerald-100 mb-6">
                <p class="text-xs font-bold text-emerald-800 mb-3 uppercase tracking-wider"><i class="far fa-calendar-alt mr-1"></i> Rentang Kalender Pendidikan</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Tanggal Mulai Efektif</label>
                        <input type="date" name="tanggal_mulai" id="input-mulai" required class="w-full border border-gray-300 rounded-lg p-2 outline-none focus:border-emerald-500 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Tanggal Selesai (UAS/Libur)</label>
                        <input type="date" name="tanggal_selesai" id="input-selesai" required class="w-full border border-gray-300 rounded-lg p-2 outline-none focus:border-emerald-500 text-sm">
                    </div>
                </div>
            </div>

            <div class="flex justify-end space-x-2 border-t pt-4">
                <button type="button" onclick="tutupModal()" class="px-5 py-2.5 bg-gray-200 text-gray-800 rounded-lg font-semibold hover:bg-gray-300 transition">Batal</button>
                <button type="submit" class="px-5 py-2.5 bg-emerald-500 text-white rounded-lg font-semibold hover:bg-emerald-600 transition shadow-sm">Simpan Data</button>
            </div>
        </form>
    </div>
</div>

<script>
    // Membuka modal tambah dengan rute khusus jika diperlukan rute terpisah
    function bukaModalTambah() {
        document.getElementById('modal-periode').classList.remove('hidden');
        document.getElementById('modal-judul').innerText = "Tambah Periode Baru";
        document.getElementById('form-periode').action = "/master-periode";
        document.getElementById('form-method').value = "POST";
        document.getElementById('form-periode').reset();
    }

    function bukaModalEdit(periode) {
        document.getElementById('modal-periode').classList.remove('hidden');
        document.getElementById('modal-judul').innerText = "Edit Rentang Periode";
        
        // Sesuaikan jika Bapak punya route PUT untuk update (opsional)
        // Jika belum ada route khusus update periode, simpan menggunakan POST update kustom Bapak.
        // Asumsi standar resource/update:
        document.getElementById('form-periode').action = "/master-periode/" + periode.id;
        document.getElementById('form-method').value = "PUT"; // Butuh route PUT
        
        document.getElementById('input-tahun').value = periode.tahun_ajaran;
        document.getElementById('input-semester').value = periode.semester;
        document.getElementById('input-mulai').value = periode.tanggal_mulai;
        document.getElementById('input-selesai').value = periode.tanggal_selesai;
    }

    function tutupModal() {
        document.getElementById('modal-periode').classList.add('hidden');
    }
</script>
@endsection