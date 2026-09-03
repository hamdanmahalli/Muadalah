@extends('layouts.app')

@section('title', 'Tagihan & Pembayaran')

@section('content')
<div class="flex flex-col md:flex-row md:justify-between md:items-center gap-3 mb-6">
    <h2 class="text-2xl font-bold text-gray-800"><i class="fas fa-file-invoice-dollar mr-2 text-emerald-600"></i> Tagihan &amp; Pembayaran</h2>
    <div class="flex gap-2">
        <a href="{{ route('tagihan.buat') }}" class="bg-emerald-600 text-white px-4 py-2 rounded-lg text-sm font-semibold shadow hover:bg-emerald-700 transition">
            <i class="fas fa-plus mr-1"></i> Buat Tagihan
        </a>
    </div>
</div>

<!-- Statistik -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-200">
        <p class="text-xs font-bold text-gray-400 uppercase">Total Belum Lunas</p>
        <p class="text-2xl font-extrabold text-rose-500 mt-1">Rp {{ number_format($totalBelum,0,',','.') }}</p>
    </div>
    <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-200">
        <p class="text-xs font-bold text-gray-400 uppercase">Sudah Lunas</p>
        <p class="text-2xl font-extrabold text-emerald-600 mt-1">Rp {{ number_format($totalLunas,0,',','.') }}</p>
    </div>
    <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-200">
        <p class="text-xs font-bold text-gray-400 uppercase">Jumlah Tagihan</p>
        <p class="text-2xl font-extrabold text-indigo-600 mt-1">{{ $tagihans->total() }}</p>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <!-- Kelola Jenis Tagihan -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
        <h3 class="font-bold text-gray-700 mb-3"><i class="fas fa-tags text-indigo-500 mr-2"></i> Jenis Tagihan</h3>

        <form method="POST" action="{{ route('tagihan.jenis.store') }}" class="mb-4 flex gap-2">
            @csrf
            <input type="text" name="nama_tagihan" placeholder="Nama tagihan (SPP, dll)" required class="flex-1 border border-gray-300 rounded-lg p-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
            <button type="submit" class="bg-indigo-600 text-white px-3 rounded-lg text-sm font-semibold hover:bg-indigo-700"><i class="fas fa-plus"></i></button>
        </form>

        <ul class="space-y-2 text-sm">
            @forelse($jenisTagihan as $j)
            <li class="border border-gray-100 rounded-lg p-3 flex items-center justify-between">
                <div>
                    <p class="font-bold text-gray-700">{{ $j->nama_tagihan }}</p>
                    <p class="text-xs text-gray-400">{{ $j->deskripsi }}</p>
                </div>
                <div class="flex gap-1">
                    <button onclick="editJenis({{ $j->id }}, '{{ $j->nama_tagihan }}', '{{ addslashes($j->deskripsi ?? '') }}')" class="w-7 h-7 rounded-full bg-orange-50 text-orange-400 hover:bg-orange-400 hover:text-white transition"><i class="fas fa-edit"></i></button>
                    <button onclick="hapusJenis({{ $j->id }}, '{{ $j->nama_tagihan }}')" class="w-7 h-7 rounded-full bg-red-50 text-red-500 hover:bg-red-500 hover:text-white transition"><i class="fas fa-trash-alt"></i></button>
                </div>
            </li>
            @empty
            <li class="text-gray-400 text-center py-4">Belum ada jenis tagihan.</li>
            @endforelse
        </ul>
    </div>

    <!-- Daftar tagihan -->
    <div class="md:col-span-2 bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <form method="GET" action="{{ route('tagihan.index') }}" class="p-4 grid grid-cols-1 md:grid-cols-3 gap-2 border-b border-gray-100">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari siswa..." class="border border-gray-300 rounded-lg p-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
            <select name="status" class="border border-gray-300 rounded-lg p-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                <option value="">Semua Status</option>
                <option value="belum" {{ request('status')=='belum'?'selected':'' }}>Belum</option>
                <option value="parsial" {{ request('status')=='parsial'?'selected':'' }}>Parsial</option>
                <option value="lunas" {{ request('status')=='lunas'?'selected':'' }}>Lunas</option>
            </select>
            <select name="jenis_tagihan_id" class="border border-gray-300 rounded-lg p-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                <option value="">Semua Jenis</option>
                @foreach($jenisTagihan as $j)
                    <option value="{{ $j->id }}" {{ request('jenis_tagihan_id')==$j->id?'selected':'' }}>{{ $j->nama_tagihan }}</option>
                @endforeach
            </select>
        </form>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Siswa</th>
                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Jenis</th>
                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Nominal</th>
                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Sisa</th>
                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($tagihans as $t)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-semibold text-gray-700">{{ $t->siswa?->nama_siswa }}<div class="text-xs text-gray-400">{{ $t->siswa?->nis }}</div></td>
                        <td class="px-4 py-3 text-gray-600">{{ $t->jenisTagihan?->nama_tagihan }}<div class="text-xs text-gray-400">{{ $t->periode?->tahun_ajaran }}</div></td>
                        <td class="px-4 py-3 font-bold text-gray-700">Rp {{ number_format($t->nominal,0,',','.') }}</td>
                        <td class="px-4 py-3 {{ $t->sisa() > 0 ? 'text-rose-500' : 'text-emerald-600' }} font-semibold">Rp {{ number_format($t->sisa(),0,',','.') }}</td>
                        <td class="px-4 py-3">
                            @if($t->status === 'lunas')
                                <span class="px-2 py-0.5 text-xs font-bold rounded-full bg-emerald-100 text-emerald-700">Lunas</span>
                            @elseif($t->status === 'parsial')
                                <span class="px-2 py-0.5 text-xs font-bold rounded-full bg-amber-100 text-amber-700">Parsial</span>
                            @else
                                <span class="px-2 py-0.5 text-xs font-bold rounded-full bg-rose-100 text-rose-700">Belum</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            <a href="{{ route('tagihan.detail', $t->id) }}" class="w-8 h-8 rounded-full bg-sky-50 text-sky-500 hover:bg-sky-500 hover:text-white transition inline-flex items-center justify-center" title="Bayar / Detail">
                                <i class="fas fa-coins"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-4 py-10 text-center text-gray-400">Belum ada tagihan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4">{{ $tagihans->links() }}</div>
    </div>
</div>

<!-- Modal Edit Jenis -->
<div id="modal-jenis" class="hidden fixed inset-0 bg-gray-900 bg-opacity-60 flex items-center justify-center backdrop-blur-sm z-50">
    <div class="relative mx-auto p-6 border w-full max-w-sm shadow-2xl rounded-xl bg-white">
        <h3 class="text-lg font-bold text-gray-800 mb-4">Edit Jenis Tagihan</h3>
        <form id="form-jenis" method="POST" action="">
            @csrf
            @method('PUT')
            <div class="space-y-3 text-sm">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Nama</label>
                    <input type="text" name="nama_tagihan" id="jnama" required class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-indigo-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Deskripsi</label>
                    <input type="text" name="deskripsi" id="jdesk" class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-indigo-500 outline-none">
                </div>
            </div>
            <div class="flex justify-end space-x-2 border-t pt-4 mt-4">
                <button type="button" onclick="tutupJenis()" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg font-semibold">Batal</button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg font-semibold">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Hapus Jenis -->
<div id="modal-hapus" class="hidden fixed inset-0 bg-gray-900 bg-opacity-60 flex items-center justify-center backdrop-blur-sm z-50">
    <div class="relative mx-auto p-6 border w-full max-w-sm shadow-2xl rounded-xl bg-white text-center">
        <h3 class="text-xl font-bold text-gray-800 mb-2">Hapus Jenis Tagihan</h3>
        <p class="text-sm text-gray-500 mb-6">Yakin hapus jenis tagihan <strong id="hj-nama" class="text-gray-800"></strong>? Tagihan terkait ikut terhapus.</p>
        <form id="form-hapus" method="POST" action="">
            @csrf
            @method('DELETE')
            <div class="flex justify-center space-x-3">
                <button type="button" onclick="tutupHapus()" class="px-5 py-2.5 bg-gray-200 text-gray-800 rounded-lg font-semibold w-full">Batal</button>
                <button type="submit" class="px-5 py-2.5 bg-red-500 text-white rounded-lg font-semibold w-full">Ya, Hapus</button>
            </div>
        </form>
    </div>
</div>

<script>
    function editJenis(id, nama, desk) {
        document.getElementById('modal-jenis').classList.remove('hidden');
        document.getElementById('form-jenis').action = '/tagihan/jenis/' + id;
        document.getElementById('jnama').value = nama;
        document.getElementById('jdesk').value = desk;
    }
    function tutupJenis(){ document.getElementById('modal-jenis').classList.add('hidden'); }
    function hapusJenis(id, nama) {
        document.getElementById('modal-hapus').classList.remove('hidden');
        document.getElementById('hj-nama').innerText = nama;
        document.getElementById('form-hapus').action = '/tagihan/jenis/' + id;
    }
    function tutupHapus(){ document.getElementById('modal-hapus').classList.add('hidden'); }
</script>
@endsection
