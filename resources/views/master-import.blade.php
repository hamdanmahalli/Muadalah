@extends('layouts.app')

@section('title', 'Master Import Excel')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800"><i class="fas fa-file-excel mr-2 text-green-600"></i> Master Import Excel</h1>
        <p class="text-sm text-gray-500 mt-1">Pusat pengunggahan data masal berbasis file Excel (.xlsx / .csv)</p>
    </div>

    @if(session('sukses'))
    <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-xl text-green-800 font-semibold flex items-center">
        <i class="fas fa-check-circle text-xl mr-3 text-green-600"></i>
        {{ session('sukses') }}
    </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex flex-col justify-between">
            <div>
                <div class="flex items-center space-x-3 mb-3">
                    <div class="w-10 h-10 rounded-xl bg-green-100 text-green-700 flex items-center justify-center font-bold">
                        <i class="fas fa-chalkboard-teacher"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-800 text-lg">Import Master Guru</h3>
                        <p class="text-xs text-gray-400">Format: NIG | Nama Guru | L/P | Alamat | No. HP | Status</p>
                    </div>
                </div>
                <p class="text-sm text-gray-600 mb-4 bg-gray-50 p-3 rounded-lg border border-gray-100">
                    Pastikan kolom pertama berisi <b>NIG</b> unik dan kolom kedua berisi <b>Nama Lengkap Guru</b>.
                </p>
            </div>

            <form action="/master-import/guru" method="POST" enctype="multipart/form-data" class="mt-2">
                @csrf
                <div class="mb-3">
                    <input type="file" name="file" required class="w-full text-xs text-gray-500 border border-gray-200 rounded-xl p-2 bg-gray-50">
                </div>
                <button type="submit" class="w-full py-2.5 bg-green-600 hover:bg-green-700 text-white font-bold rounded-xl text-sm transition shadow-md flex items-center justify-center">
                    <i class="fas fa-upload mr-2"></i> Unggah Data Guru
                </button>
            </form>
        </div>

        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex flex-col justify-between">
            <div>
                <div class="flex items-center space-x-3 mb-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center font-bold">
                        <i class="fas fa-book-open"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-800 text-lg">Import Master Pelajaran</h3>
                        <p class="text-xs text-gray-400">Format: Kode Pelajaran | Nama Pelajaran | Nama Kitab</p>
                    </div>
                </div>
                <p class="text-sm text-gray-600 mb-4 bg-gray-50 p-3 rounded-lg border border-gray-100">
                    Unggah daftar mata pelajaran beserta referensi kitab yang diajarkan di pesantren.
                </p>
            </div>

            <form action="/master-import/pelajaran" method="POST" enctype="multipart/form-data" class="mt-2">
                @csrf
                <div class="mb-3">
                    <input type="file" name="file" required class="w-full text-xs text-gray-500 border border-gray-200 rounded-xl p-2 bg-gray-50">
                </div>
                <button type="submit" class="w-full py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl text-sm transition shadow-md flex items-center justify-center">
                    <i class="fas fa-upload mr-2"></i> Unggah Data Pelajaran
                </button>
            </form>
        </div>

        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex flex-col justify-between">
            <div>
                <div class="flex items-center space-x-3 mb-3">
                    <div class="w-10 h-10 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center font-bold">
                        <i class="fas fa-school"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-800 text-lg">Import Master Kelas</h3>
                        <p class="text-xs text-gray-400">Format: Nama Kelas | Tingkat</p>
                    </div>
                </div>
                <p class="text-sm text-gray-600 mb-4 bg-gray-50 p-3 rounded-lg border border-gray-100">
                    Contoh isian: <b>SPM-7A</b> (Kolom 1) dan <b>7</b> (Kolom 2).
                </p>
            </div>

            <form action="/master-import/kelas" method="POST" enctype="multipart/form-data" class="mt-2">
                @csrf
                <div class="mb-3">
                    <input type="file" name="file" required class="w-full text-xs text-gray-500 border border-gray-200 rounded-xl p-2 bg-gray-50">
                </div>
                <button type="submit" class="w-full py-2.5 bg-purple-600 hover:bg-purple-700 text-white font-bold rounded-xl text-sm transition shadow-md flex items-center justify-center">
                    <i class="fas fa-upload mr-2"></i> Unggah Data Kelas
                </button>
            </form>
        </div>

        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex flex-col justify-between">
            <div>
                <div class="flex items-center space-x-3 mb-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center font-bold">
                        <i class="fas fa-sitemap"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-800 text-lg">Import Target Mengajar</h3>
                        <p class="text-xs text-gray-400">Format: Nama Kelas | Pelajaran | NIG/Guru | Beban Jam</p>
                    </div>
                </div>
                <p class="text-sm text-gray-600 mb-4 bg-gray-50 p-3 rounded-lg border border-gray-100">
                    Sistem akan mencocokkan nama kelas, pelajaran, dan guru secara otomatis.
                </p>
            </div>

            <form action="/master-import/plot-jadwal" method="POST" enctype="multipart/form-data" class="mt-2">
                @csrf
                <div class="mb-3">
                    <input type="file" name="file" required class="w-full text-xs text-gray-500 border border-gray-200 rounded-xl p-2 bg-gray-50">
                </div>
                <button type="submit" class="w-full py-2.5 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-xl text-sm transition shadow-md flex items-center justify-center">
                    <i class="fas fa-upload mr-2"></i> Unggah Target Mengajar
                </button>
            </form>
        </div>

        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex flex-col justify-between">
            <div>
                <div class="flex items-center space-x-3 mb-3">
                    <div class="w-10 h-10 rounded-xl bg-cyan-100 text-cyan-700 flex items-center justify-center font-bold">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-800 text-lg">Import Jadwal Harian</h3>
                        <p class="text-xs text-gray-400">Format: Nama Kelas | Hari | Jam Ke | NIG/Guru | Pelajaran</p>
                    </div>
                </div>
                <p class="text-sm text-gray-600 mb-4 bg-gray-50 p-3 rounded-lg border border-gray-100">
                    Ini adalah Jadwal Pelaksanaan (Roster). Pastikan format hari valid (misal: Senin, Selasa).
                </p>
            </div>

            <form action="/master-import/jadwal-harian" method="POST" enctype="multipart/form-data" class="mt-2">
                @csrf
                <div class="mb-3">
                    <input type="file" name="file" required class="w-full text-xs text-gray-500 border border-gray-200 rounded-xl p-2 bg-gray-50">
                </div>
                <button type="submit" class="w-full py-2.5 bg-cyan-600 hover:bg-cyan-700 text-white font-bold rounded-xl text-sm transition shadow-md flex items-center justify-center">
                    <i class="fas fa-upload mr-2"></i> Unggah Jadwal Harian
                </button>
            </form>
        </div>

    </div>
@endsection