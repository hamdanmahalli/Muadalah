@extends('layouts.app')

@section('title', 'Master Import Excel')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800"><i class="fas fa-file-excel mr-2 text-green-600"></i> Master Import Data</h1>
        <p class="text-sm text-gray-500 mt-1">Pusat pengunggahan data masal terpusat berbasis file Excel (.xlsx / .csv)</p>
    </div>

    
    @if(session('error'))
    <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl text-red-800 font-semibold flex items-center">
        <i class="fas fa-exclamation-triangle text-xl mr-3 text-red-600"></i>
        {{ session('error') }}
    </div>
    @endif

    <div class="bg-indigo-50 border border-indigo-100 rounded-2xl p-5 mb-8 shadow-sm relative overflow-hidden">
        <div class="absolute -right-10 -top-10 text-indigo-100 text-9xl opacity-30"><i class="fas fa-info-circle"></i></div>
        <h3 class="font-bold text-indigo-800 text-lg mb-3 relative z-10"><i class="fas fa-clipboard-list mr-2"></i> SOP Urutan Import (Wajib Diikuti)</h3>
        <p class="text-sm text-indigo-700 mb-3 relative z-10">Untuk menghindari error <i>Database Constraint</i> (Data ditolak karena tidak sinkron), mohon unggah file Excel Anda tepat sesuai urutan berikut:</p>
        
        <div class="flex flex-wrap gap-2 relative z-10">
            <span class="bg-indigo-600 text-white text-xs font-bold px-3 py-1.5 rounded-lg shadow-sm">1. Kelas</span>
            <i class="fas fa-arrow-right text-indigo-300 self-center"></i>
            <span class="bg-indigo-600 text-white text-xs font-bold px-3 py-1.5 rounded-lg shadow-sm">2. Pelajaran</span>
            <i class="fas fa-arrow-right text-indigo-300 self-center"></i>
            <span class="bg-indigo-600 text-white text-xs font-bold px-3 py-1.5 rounded-lg shadow-sm">3. Guru</span>
            <i class="fas fa-arrow-right text-indigo-300 self-center"></i>
            <span class="bg-amber-500 text-white text-xs font-bold px-3 py-1.5 rounded-lg shadow-sm">4. Target Mengajar (Plot)</span>
            <i class="fas fa-arrow-right text-indigo-300 self-center"></i>
            <span class="bg-cyan-500 text-white text-xs font-bold px-3 py-1.5 rounded-lg shadow-sm">5. Jadwal Harian (Roster)</span>
        </div>
        
        <div class="mt-4 inline-block bg-white px-4 py-2 rounded-lg text-xs font-bold text-gray-700 border border-indigo-100 shadow-sm relative z-10">
            Target Periode Jadwal Saat Ini: 
            <span class="{{ $periodeAktif ? 'text-green-600' : 'text-red-500' }}">
                {{ $periodeAktif ? $periodeAktif->tahun_ajaran . ' (' . $periodeAktif->semester . ')' : '⚠ PERIODE BELUM DIAKTIFKAN!' }}
            </span>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">

        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex flex-col justify-between">
            <div>
                <div class="flex items-center space-x-3 mb-3">
                    <div class="w-10 h-10 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center font-bold"><i class="fas fa-school"></i></div>
                    <h3 class="font-bold text-gray-800 text-lg">1. Import Kelas</h3>
                </div>
                <div class="text-xs text-gray-600 mb-4 bg-gray-50 p-3 rounded-lg border border-gray-100 font-mono overflow-x-auto whitespace-nowrap">
                    <b>Format Kolom Excel:</b><br>
                    A: Nama Kelas (Misal: VII.A)<br>
                    B: Tingkat (Misal: 7)
                </div>
            </div>
            <form action="/master-import/kelas" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="file" name="file" required class="w-full text-xs border border-gray-200 rounded-xl p-2 bg-gray-50 mb-3">
                <button type="submit" class="w-full py-2 bg-purple-600 hover:bg-purple-700 text-white font-bold rounded-xl text-sm transition shadow-sm flex justify-center items-center"><i class="fas fa-upload mr-2"></i> Unggah Kelas</button>
            </form>
        </div>

        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex flex-col justify-between">
            <div>
                <div class="flex items-center space-x-3 mb-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center font-bold"><i class="fas fa-book-open"></i></div>
                    <h3 class="font-bold text-gray-800 text-lg">2. Import Pelajaran</h3>
                </div>
                <div class="text-xs text-gray-600 mb-4 bg-gray-50 p-3 rounded-lg border border-gray-100 font-mono overflow-x-auto whitespace-nowrap">
                    <b>Format Kolom Excel:</b><br>
                    A: Kode (Boleh Kosong)<br>
                    B: Nama Pelajaran (Misal: Nahwu)<br>
                    C: Nama Kitab (Misal: Jurumiyah)
                </div>
            </div>
            <form action="/master-import/pelajaran" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="file" name="file" required class="w-full text-xs border border-gray-200 rounded-xl p-2 bg-gray-50 mb-3">
                <button type="submit" class="w-full py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl text-sm transition shadow-sm flex justify-center items-center"><i class="fas fa-upload mr-2"></i> Unggah Pelajaran</button>
            </form>
        </div>

        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex flex-col justify-between">
            <div>
                <div class="flex items-center space-x-3 mb-3">
                    <div class="w-10 h-10 rounded-xl bg-green-100 text-green-700 flex items-center justify-center font-bold"><i class="fas fa-chalkboard-teacher"></i></div>
                    <h3 class="font-bold text-gray-800 text-lg">3. Import Guru</h3>
                </div>
                <div class="text-xs text-gray-600 mb-4 bg-gray-50 p-3 rounded-lg border border-gray-100 font-mono overflow-x-auto whitespace-nowrap">
                    <b>Format Kolom Excel:</b><br>
                    A: NIG (Wajib, Misal: 1001)<br>
                    B: Nama Lengkap Guru<br>
                    C: L/P | D: Alamat | E: No.HP
                </div>
            </div>
            <form action="/master-import/guru" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="file" name="file" required class="w-full text-xs border border-gray-200 rounded-xl p-2 bg-gray-50 mb-3">
                <button type="submit" class="w-full py-2 bg-green-600 hover:bg-green-700 text-white font-bold rounded-xl text-sm transition shadow-sm flex justify-center items-center"><i class="fas fa-upload mr-2"></i> Unggah Guru</button>
            </form>
        </div>

        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex flex-col justify-between md:col-span-2 xl:col-span-1">
            <div>
                <div class="flex items-center space-x-3 mb-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center font-bold"><i class="fas fa-sitemap"></i></div>
                    <h3 class="font-bold text-gray-800 text-lg">4. Target Mengajar (Plot)</h3>
                </div>
                <div class="text-xs text-gray-600 mb-4 bg-gray-50 p-3 rounded-lg border border-gray-100 font-mono overflow-x-auto whitespace-nowrap">
                    <b>Format Kolom Excel:</b><br>
                    A: Nama Kelas (Harus Sesuai Step 1)<br>
                    B: Nama Pelajaran (Sesuai Step 2)<br>
                    C: NIG atau Nama Guru<br>
                    D: Beban Jam (Misal: 2)
                </div>
            </div>
            <form action="/master-import/plot-jadwal" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="file" name="file" required class="w-full text-xs border border-gray-200 rounded-xl p-2 bg-gray-50 mb-3">
                <button type="submit" class="w-full py-2 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-xl text-sm transition shadow-sm flex justify-center items-center"><i class="fas fa-upload mr-2"></i> Unggah Plot Jadwal</button>
            </form>
        </div>

        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex flex-col justify-between md:col-span-2 xl:col-span-2">
            <div>
                <div class="flex items-center space-x-3 mb-3">
                    <div class="w-10 h-10 rounded-xl bg-cyan-100 text-cyan-700 flex items-center justify-center font-bold"><i class="fas fa-calendar-check"></i></div>
                    <h3 class="font-bold text-gray-800 text-lg">5. Jadwal Harian (Roster)</h3>
                </div>
                <div class="text-xs text-gray-600 mb-4 bg-gray-50 p-3 rounded-lg border border-gray-100 font-mono overflow-x-auto whitespace-nowrap">
                    <b>Format Kolom Excel:</b><br>
                    A: Nama Kelas &nbsp;&nbsp;&nbsp;| B: Hari (Senin-Minggu)<br>
                    C: Jam Ke (Angka) | D: NIG / Nama Guru | E: Nama Pelajaran
                </div>
            </div>
            <form action="/master-import/jadwal-harian" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="file" name="file" required class="w-full text-xs border border-gray-200 rounded-xl p-2 bg-gray-50 mb-3">
                <button type="submit" class="w-full py-3 bg-cyan-600 hover:bg-cyan-700 text-white font-bold rounded-xl text-sm transition shadow-md flex justify-center items-center"><i class="fas fa-rocket mr-2"></i> Unggah Jadwal Harian & Tampilkan di Meja Kontrol!</button>
            </form>
        </div>

    </div>
@endsection