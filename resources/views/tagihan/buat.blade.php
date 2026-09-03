@extends('layouts.app')

@section('title', 'Buat Tagihan')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h2 class="text-2xl font-bold text-gray-800"><i class="fas fa-plus-circle mr-2 text-emerald-600"></i> Buat Tagihan</h2>
    <a href="{{ route('tagihan.index') }}" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-semibold hover:bg-gray-300 transition"><i class="fas fa-arrow-left mr-1"></i> Kembali</a>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 max-w-2xl">
    <form method="POST" action="{{ route('tagihan.store') }}">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm mb-6">
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Jenis Tagihan <span class="text-red-500">*</span></label>
                <select name="jenis_tagihan_id" required class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-emerald-500 outline-none">
                    <option value="">- Pilih -</option>
                    @foreach($jenisTagihan as $j)
                        <option value="{{ $j->id }}" {{ old('jenis_tagihan_id') == $j->id ? 'selected' : '' }}>{{ $j->nama_tagihan }}</option>
                    @endforeach
                </select>
                <a href="{{ route('tagihan.index') }}" class="text-xs text-indigo-500 hover:underline">+ Kelola jenis tagihan</a>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Periode</label>
                <select name="periode_id" class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-emerald-500 outline-none">
                    <option value="">- Pilih -</option>
                    @foreach($periodes as $p)
                        <option value="{{ $p->id }}" {{ $aktif && $aktif->id == $p->id ? 'selected' : '' }}>{{ $p->tahun_ajaran }} ({{ $p->semester }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Nominal (Rp) <span class="text-red-500">*</span></label>
                <input type="number" name="nominal" value="{{ old('nominal') }}" required class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-emerald-500 outline-none">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Tanggal Jatuh Tempo</label>
                <input type="date" name="tanggal_jatuh_tempo" value="{{ old('tanggal_jatuh_tempo') }}" class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-emerald-500 outline-none">
            </div>
        </div>

        <div class="mb-6">
            <label class="block text-xs font-semibold text-gray-600 mb-1">Keterangan</label>
            <input type="text" name="keterangan" value="{{ old('keterangan') }}" placeholder="Misal: SPP bulan Januari" class="w-full border border-gray-300 rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-emerald-500 outline-none">
        </div>

        <p class="font-bold text-gray-700 mb-3"><i class="fas fa-bullseye text-indigo-500 mr-2"></i> Target Tagihan</p>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-sm mb-4">
            <label class="flex items-center gap-2 border border-gray-200 rounded-lg p-3 cursor-pointer hover:bg-indigo-50">
                <input type="radio" name="target" value="semua_kelas" checked onchange="toggleTarget()" class="accent-indigo-600"> Semua Murid
            </label>
            <label class="flex items-center gap-2 border border-gray-200 rounded-lg p-3 cursor-pointer hover:bg-indigo-50">
                <input type="radio" name="target" value="kelas_tertentu" onchange="toggleTarget()" class="accent-indigo-600"> Kelas Tertentu
            </label>
            <label class="flex items-center gap-2 border border-gray-200 rounded-lg p-3 cursor-pointer hover:bg-indigo-50">
                <input type="radio" name="target" value="murid_tertentu" onchange="toggleTarget()" class="accent-indigo-600"> Murid Tertentu
            </label>
        </div>

        <!-- Target kelas -->
        <div id="blok-kelas" class="hidden mb-4">
            <label class="block text-xs font-semibold text-gray-600 mb-1">Pilih Kelas</label>
            <select name="kelas_id" class="w-full border border-gray-300 rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-emerald-500 outline-none">
                <option value="">- Pilih -</option>
                @foreach($kelas as $k)
                    <option value="{{ $k->id }}">{{ $k->nama_kelas }}</option>
                @endforeach
            </select>
        </div>

        <!-- Target murid -->
        <div id="blok-murid" class="hidden mb-4">
            <label class="block text-xs font-semibold text-gray-600 mb-1">Pilih Murid (bisa banyak)</label>
            <select name="siswa_ids[]" multiple size="6" class="w-full border border-gray-300 rounded-lg p-2 text-sm focus:ring-2 focus:ring-emerald-500 outline-none">
                @foreach($siswas as $s)
                    <option value="{{ $s->id }}">{{ $s->nama_siswa }} ({{ $s->nis }})</option>
                @endforeach
            </select>
            <p class="text-xs text-gray-400 mt-1">Tahan Ctrl untuk pilih banyak murid.</p>
        </div>

        <div class="flex justify-end border-t pt-4">
            <button type="submit" class="bg-emerald-600 text-white px-6 py-2.5 rounded-lg text-sm font-semibold hover:bg-emerald-700 transition shadow-sm">
                <i class="fas fa-file-invoice mr-1"></i> Buat Tagihan
            </button>
        </div>
    </form>
</div>

<script>
    function toggleTarget() {
        const v = document.querySelector('input[name=target]:checked').value;
        document.getElementById('blok-kelas').classList.toggle('hidden', v !== 'kelas_tertentu');
        document.getElementById('blok-murid').classList.toggle('hidden', v !== 'murid_tertentu');
    }
</script>
@endsection
