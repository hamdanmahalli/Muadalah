@extends('layouts.app')

@section('title', 'Penempatan Siswa')

@section('content')
<div class="flex flex-col md:flex-row md:justify-between md:items-center gap-3 mb-6">
    <h2 class="text-2xl font-bold text-gray-800"><i class="fas fa-people-arrows mr-2 text-indigo-600"></i> Penempatan Siswa</h2>
</div>

<!-- Filter -->
<form method="GET" action="{{ route('penempatan-siswa.index') }}" class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Periode</label>
            <select name="periode_id" onchange="this.form.submit()" class="w-full border border-gray-300 rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                @foreach($periodes as $p)
                    <option value="{{ $p->id }}" {{ $periodeId == $p->id ? 'selected' : '' }}>{{ $p->tahun_ajaran }} ({{ $p->semester }}) {{ $p->is_active ? '✓ AKTIF' : '' }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Filter Kelas</label>
            <select name="kelas_id" onchange="this.form.submit()" class="w-full border border-gray-300 rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                <option value="">- Semua Kelas -</option>
                @foreach($kelas as $k)
                    <option value="{{ $k->id }}" {{ $kelasId == $k->id ? 'selected' : '' }}>{{ $k->nama_kelas }}</option>
                @endforeach
            </select>
        </div>
    </div>
</form>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <!-- Tambah penempatan -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
        <h3 class="font-bold text-gray-700 mb-3"><i class="fas fa-plus-circle text-emerald-500 mr-2"></i> Tempatkan Siswa</h3>
        <form method="POST" action="{{ route('penempatan-siswa.store') }}">
            @csrf
            <input type="hidden" name="periode_id" value="{{ $periodeId }}">
            <div class="space-y-3 text-sm">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Siswa (belum ditempatkan)</label>
                    <select name="siswa_id" required class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-indigo-500 outline-none">
                        <option value="">- Pilih -</option>
                        @foreach($siswaBelum as $s)
                            <option value="{{ $s->id }}">{{ $s->nama_siswa }} ({{ $s->nis }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Kelas</label>
                    <select name="kelas_id" required class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-indigo-500 outline-none">
                        <option value="">- Pilih -</option>
                        @foreach($kelas as $k)
                            <option value="{{ $k->id }}">{{ $k->nama_kelas }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="w-full bg-emerald-600 text-white px-4 py-2.5 rounded-lg text-sm font-semibold hover:bg-emerald-700 transition">
                    <i class="fas fa-check mr-1"></i> Tempatkan
                </button>
            </div>
        </form>

        <hr class="my-4">
        <h4 class="font-bold text-gray-600 text-sm mb-2"><i class="fas fa-magic text-indigo-500 mr-2"></i> Masuk Otomatis</h4>
        <form method="POST" action="{{ route('penempatan-siswa.auto') }}">
            @csrf
            <input type="hidden" name="periode_id" value="{{ $periodeId }}">
            <div class="space-y-2 text-sm">
                <select name="kelas_id" required class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-indigo-500 outline-none">
                    <option value="">- Pilih Kelas Tujuan -</option>
                    @foreach($kelas as $k)
                        <option value="{{ $k->id }}">{{ $k->nama_kelas }}</option>
                    @endforeach
                </select>
                <button type="submit" class="w-full bg-indigo-600 text-white px-4 py-2.5 rounded-lg text-sm font-semibold hover:bg-indigo-700 transition">
                    <i class="fas fa-fill-drip mr-1"></i> Isi Kelas (Max 50)
                </button>
            </div>
        </form>
    </div>

    <!-- Daftar penempatan -->
    <div class="md:col-span-2 bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Siswa</th>
                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Kelas</th>
                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Periode</th>
                        <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($penempatan as $p)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-semibold text-gray-700">{{ $p->siswa?->nama_siswa }}<div class="text-xs text-gray-400">{{ $p->siswa?->nis }}</div></td>
                        <td class="px-4 py-3"><span class="px-2 py-1 text-xs font-bold rounded-full bg-indigo-100 text-indigo-700">{{ $p->kelas?->nama_kelas }}</span></td>
                        <td class="px-4 py-3 text-gray-500">{{ $p->periode?->tahun_ajaran }} {{ $p->periode?->semester }}</td>
                        <td class="px-4 py-3 text-center">
                            <button onclick="hapusPenempatan({{ $p->id }}, '{{ $p->siswa?->nama_siswa }}')" class="w-8 h-8 rounded-full bg-red-50 text-red-500 hover:bg-red-500 hover:text-white transition inline-flex items-center justify-center">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="px-4 py-10 text-center text-gray-400">Belum ada penempatan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4">{{ $penempatan->links() }}</div>
    </div>
</div>

<div id="modal-hapus" class="hidden fixed inset-0 bg-gray-900 bg-opacity-60 flex items-center justify-center backdrop-blur-sm z-50">
    <div class="relative mx-auto p-6 border w-full max-w-sm shadow-2xl rounded-xl bg-white text-center">
        <h3 class="text-xl font-bold text-gray-800 mb-2">Hapus Penempatan</h3>
        <p class="text-sm text-gray-500 mb-6">Yakin hapus penempatan <strong id="hp-nama" class="text-gray-800"></strong>?</p>
        <form id="form-hapus" method="POST" action="">
            @csrf
            @method('DELETE')
            <div class="flex justify-center space-x-3">
                <button type="button" onclick="tutup()" class="px-5 py-2.5 bg-gray-200 text-gray-800 rounded-lg font-semibold w-full">Batal</button>
                <button type="submit" class="px-5 py-2.5 bg-red-500 text-white rounded-lg font-semibold w-full">Ya</button>
            </div>
        </form>
    </div>
</div>
<script>
    function hapusPenempatan(id, nama) {
        document.getElementById('modal-hapus').classList.remove('hidden');
        document.getElementById('hp-nama').innerText = nama;
        document.getElementById('form-hapus').action = '/penempatan-siswa/' + id;
    }
    function tutup(){ document.getElementById('modal-hapus').classList.add('hidden'); }
</script>
@endsection
