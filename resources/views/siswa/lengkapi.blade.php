@extends('layouts.app')

@section('title', 'Lengkapi Data Siswa')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h2 class="text-2xl font-bold text-gray-800"><i class="fas fa-user-edit mr-2 text-indigo-600"></i> Lengkapi Data Siswa</h2>
    <a href="{{ route('master-siswa.show', $siswa->id) }}" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-semibold hover:bg-gray-300 transition"><i class="fas fa-arrow-left mr-1"></i> Kembali</a>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 max-w-4xl">
    <form method="POST" action="{{ route('siswa.lengkapi', $siswa->id) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm mb-6">
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">NIS</label>
                <input type="text" name="nis" value="{{ $siswa->nis }}" class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-indigo-500 outline-none">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">NISN</label>
                <input type="text" name="nisn" value="{{ $siswa->nisn }}" class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-indigo-500 outline-none">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Nama Lengkap</label>
                <input type="text" name="nama_siswa" value="{{ $siswa->nama_siswa }}" required class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-indigo-500 outline-none">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Jenis Kelamin</label>
                <select name="jenis_kelamin" class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-indigo-500 outline-none">
                    <option value="">- Pilih -</option>
                    <option value="L" {{ $siswa->jenis_kelamin == 'L' ? 'selected' : '' }}>Laki-laki</option>
                    <option value="P" {{ $siswa->jenis_kelamin == 'P' ? 'selected' : '' }}>Perempuan</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Tempat Lahir</label>
                <input type="text" name="tempat_lahir" value="{{ $siswa->tempat_lahir }}" class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-indigo-500 outline-none">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Tanggal Lahir</label>
                <input type="date" name="tanggal_lahir" value="{{ $siswa->tanggal_lahir?->format('Y-m-d') }}" class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-indigo-500 outline-none">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Agama</label>
                <input type="text" name="agama" value="{{ $siswa->agama }}" class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-indigo-500 outline-none">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Tahun Masuk</label>
                <input type="text" name="tahun_masuk" value="{{ $siswa->tahun_masuk }}" class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-indigo-500 outline-none">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Status</label>
                <select name="status" class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-indigo-500 outline-none">
                    <option value="Aktif" {{ $siswa->status == 'Aktif' ? 'selected' : '' }}>Aktif</option>
                    <option value="Keluar" {{ $siswa->status == 'Keluar' ? 'selected' : '' }}>Keluar</option>
                    <option value="Alumni" {{ $siswa->status == 'Alumni' ? 'selected' : '' }}>Alumni</option>
                </select>
            </div>
        </div>

        <div class="mb-6">
            <label class="block text-xs font-semibold text-gray-600 mb-1">Alamat</label>
            <textarea name="alamat" rows="2" class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-indigo-500 outline-none">{{ $siswa->alamat }}</textarea>
        </div>

        <p class="font-bold text-gray-700 mb-3"><i class="fas fa-users text-indigo-500 mr-2"></i> Data Orang Tua</p>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm mb-6">
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Nama Ayah</label>
                <input type="text" name="nama_ayah" value="{{ $siswa->nama_ayah }}" class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-indigo-500 outline-none">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Nama Ibu</label>
                <input type="text" name="nama_ibu" value="{{ $siswa->nama_ibu }}" class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-indigo-500 outline-none">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Pekerjaan Ortu</label>
                <input type="text" name="pekerjaan_ortu" value="{{ $siswa->pekerjaan_ortu }}" class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-indigo-500 outline-none">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">No. HP Ortu</label>
                <input type="text" name="no_hp_ortu" value="{{ $siswa->no_hp_ortu }}" class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-indigo-500 outline-none">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Foto</label>
                <input type="file" name="foto" accept="image/*" class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-indigo-500 outline-none">
                @if($siswa->foto)
                    <p class="text-xs text-emerald-600 mt-1">Foto ada. Upload baru untuk mengganti.</p>
                @endif
            </div>
        </div>

        <p class="font-bold text-gray-700 mb-3"><i class="fas fa-school text-indigo-500 mr-2"></i> Penempatan Aktif</p>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm mb-6">
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Kelas</label>
                <select name="kelas_id" class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-indigo-500 outline-none">
                    <option value="">- Pilih -</option>
                    @foreach($kelas as $k)
                        <option value="{{ $k->id }}" {{ $siswa->angkatan()->latest()->first()?->kelas_id == $k->id ? 'selected' : '' }}>{{ $k->nama_kelas }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Periode</label>
                <select name="periode_id" class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-indigo-500 outline-none">
                    <option value="">- Pilih -</option>
                    @foreach($periodes as $p)
                        <option value="{{ $p->id }}" {{ $siswa->angkatan()->latest()->first()?->periode_id == $p->id ? 'selected' : '' }}>{{ $p->tahun_ajaran }} ({{ $p->semester }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Tanggal Masuk</label>
                <input type="date" name="tanggal_masuk" value="{{ $siswa->angkatan()->latest()->first()?->tanggal_masuk?->format('Y-m-d') }}" class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-indigo-500 outline-none">
            </div>
        </div>

        <div class="flex justify-end border-t pt-4">
            <button type="submit" class="bg-indigo-600 text-white px-6 py-2.5 rounded-lg text-sm font-semibold hover:bg-indigo-700 transition shadow-sm">
                <i class="fas fa-save mr-1"></i> Simpan Perubahan
            </button>
        </div>
    </form>
</div>
@endsection
