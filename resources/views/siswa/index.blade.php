@extends('layouts.app')

@section('title', 'Master Siswa')

@section('content')
<div class="flex flex-col md:flex-row md:justify-between md:items-center gap-3 mb-6">
    <h2 class="text-2xl font-bold text-gray-800"><i class="fas fa-user-graduate mr-2 text-indigo-600"></i> Master Siswa</h2>
    <div class="flex gap-2">
        <a href="{{ route('master-siswa.index') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-semibold shadow hover:bg-indigo-700 transition">
            <i class="fas fa-plus mr-1"></i> Tambah Siswa
        </a>
    </div>
</div>

<!-- Kartu statistik -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-200">
        <p class="text-xs font-bold text-gray-400 uppercase">Total Siswa</p>
        <p class="text-2xl font-extrabold text-gray-700 mt-1">{{ \App\Models\Siswa::count() }}</p>
    </div>
    <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-200">
        <p class="text-xs font-bold text-gray-400 uppercase">Aktif</p>
        <p class="text-2xl font-extrabold text-emerald-600 mt-1">{{ \App\Models\Siswa::where('status','Aktif')->count() }}</p>
    </div>
    <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-200">
        <p class="text-xs font-bold text-gray-400 uppercase">Keluar / Alumni</p>
        <p class="text-2xl font-extrabold text-rose-500 mt-1">{{ \App\Models\Siswa::where('status','!=','Aktif')->count() }}</p>
    </div>
</div>

<!-- Form Tambah (inline) -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 mb-6" id="form-tambah">
    <h3 class="font-bold text-gray-700 mb-4"><i class="fas fa-plus-circle text-indigo-500 mr-2"></i> Tambah Siswa Baru</h3>
    <form method="POST" action="{{ route('master-siswa.store') }}">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">NIS <span class="text-red-500">*</span></label>
                <input type="text" name="nis" value="{{ $nisBaru }}" required class="w-full border border-gray-300 rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">NISN</label>
                <input type="text" name="nisn" class="w-full border border-gray-300 rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
            </div>
            <div class="md:col-span-2">
                <label class="block text-xs font-semibold text-gray-600 mb-1">Nama Lengkap Siswa <span class="text-red-500">*</span></label>
                <input type="text" name="nama_siswa" required placeholder="Nama lengkap" class="w-full border border-gray-300 rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Jenis Kelamin</label>
                <select name="jenis_kelamin" class="w-full border border-gray-300 rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                    <option value="">- Pilih -</option>
                    <option value="L">Laki-laki</option>
                    <option value="P">Perempuan</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Tempat Lahir</label>
                <input type="text" name="tempat_lahir" class="w-full border border-gray-300 rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Tanggal Lahir</label>
                <input type="date" name="tanggal_lahir" class="w-full border border-gray-300 rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Tahun Masuk</label>
                <input type="text" name="tahun_masuk" placeholder="2026" class="w-full border border-gray-300 rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Kelas (langsung)</label>
                <select name="kelas_id" class="w-full border border-gray-300 rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                    <option value="">- Pilih -</option>
                    @foreach($kelas as $k)
                        <option value="{{ $k->id }}">{{ $k->nama_kelas }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Periode</label>
                <select name="periode_id" class="w-full border border-gray-300 rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                    <option value="">- Pilih -</option>
                    @foreach(\App\Models\Periode::orderBy('tahun_ajaran','desc')->get() as $p)
                        <option value="{{ $p->id }}">{{ $p->tahun_ajaran }} ({{ $p->semester }})</option>
                    @endforeach
                </select>
            </div>
            <div class="md:col-span-4 flex items-end">
                <button type="submit" class="bg-indigo-600 text-white px-6 py-2.5 rounded-lg text-sm font-semibold hover:bg-indigo-700 transition shadow-sm">
                    <i class="fas fa-save mr-1"></i> Simpan Siswa
                </button>
            </div>
        </div>
    </form>
</div>

<!-- Pencarian -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-3 mb-4">
    <form method="GET" action="{{ route('master-siswa.index') }}" class="flex flex-col md:flex-row gap-2">
        <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama / NIS / NISN..." class="flex-1 border border-gray-300 rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
        <button type="submit" class="bg-indigo-600 text-white px-5 py-2.5 rounded-lg text-sm font-semibold hover:bg-indigo-700"><i class="fas fa-search mr-1"></i> Cari</button>
    </form>
</div>

<!-- Tabel -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">NIS</th>
                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Nama</th>
                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">JK</th>
                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Kelas</th>
                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Status</th>
                <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase">Aksi</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse($siswas as $s)
            <tr class="hover:bg-gray-50 transition">
                <td class="px-4 py-3 text-sm font-bold text-gray-700">{{ $s->nis }}</td>
                <td class="px-4 py-3 text-sm text-gray-800 font-semibold">{{ $s->nama_siswa }}</td>
                <td class="px-4 py-3 text-sm text-gray-500">{{ $s->jenis_kelamin }}</td>
                <td class="px-4 py-3 text-sm text-gray-500">
                    {{ $s->angkatan()->latest()->first()?->kelas?->nama_kelas ?? '-' }}
                </td>
                <td class="px-4 py-3">
                    @if($s->status === 'Aktif')
                        <span class="px-2 py-1 text-xs font-bold rounded-full bg-emerald-100 text-emerald-700">Aktif</span>
                    @else
                        <span class="px-2 py-1 text-xs font-bold rounded-full bg-rose-100 text-rose-700">{{ $s->status }}</span>
                    @endif
                </td>
                <td class="px-4 py-3 text-center">
                    <div class="flex justify-center gap-1">
                        <a href="{{ route('master-siswa.show', $s->id) }}" title="Detail" class="w-8 h-8 rounded-full bg-sky-50 text-sky-500 hover:bg-sky-500 hover:text-white transition flex items-center justify-center">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="{{ route('siswa.lengkapi', $s->id) }}" title="Lengkapi / Edit" class="w-8 h-8 rounded-full bg-orange-50 text-orange-400 hover:bg-orange-400 hover:text-white transition flex items-center justify-center">
                            <i class="fas fa-edit"></i>
                        </a>
                        <button onclick="bukaHapus({{ $s->id }}, '{{ $s->nama_siswa }}')" title="Hapus" class="w-8 h-8 rounded-full bg-red-50 text-red-500 hover:bg-red-500 hover:text-white transition flex items-center justify-center">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-4 py-10 text-center text-gray-400 text-sm">
                    <i class="fas fa-user-graduate text-3xl mb-3 opacity-50 block"></i>Belum ada data siswa.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">
    {{ $siswas->links() }}
</div>

<!-- Modal Hapus -->
<div id="modal-hapus" class="hidden fixed inset-0 bg-gray-900 bg-opacity-60 flex items-center justify-center backdrop-blur-sm z-50">
    <div class="relative mx-auto p-6 border w-full max-w-sm shadow-2xl rounded-xl bg-white text-center">
        <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100 mb-5"><i class="fas fa-trash-alt text-2xl text-red-600"></i></div>
        <h3 class="text-xl font-bold text-gray-800 mb-2">Konfirmasi Hapus</h3>
        <p class="text-sm text-gray-500 mb-6">Yakin ingin menghapus siswa <strong id="hapus-nama" class="text-gray-800"></strong>?</p>
        <form id="form-hapus" method="POST" action="">
            @csrf
            @method('DELETE')
            <div class="flex justify-center space-x-3">
                <button type="button" onclick="tutupModal()" class="px-5 py-2.5 bg-gray-200 text-gray-800 rounded-lg font-semibold w-full">Batal</button>
                <button type="submit" class="px-5 py-2.5 bg-red-500 text-white rounded-lg font-semibold w-full">Ya, Hapus</button>
            </div>
        </form>
    </div>
</div>

<script>
    function bukaHapus(id, nama) {
        document.getElementById('modal-hapus').classList.remove('hidden');
        document.getElementById('hapus-nama').innerText = nama;
        document.getElementById('form-hapus').action = '/master-siswa/' + id;
    }
    function tutupModal() {
        document.getElementById('modal-hapus').classList.add('hidden');
    }
</script>
@endsection
