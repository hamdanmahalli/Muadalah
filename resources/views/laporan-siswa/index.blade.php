@extends('layouts.app')

@section('title', 'Laporan Siswa')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h2 class="text-2xl font-bold text-gray-800"><i class="fas fa-print mr-2 text-indigo-600"></i> Laporan Siswa</h2>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <!-- Buku Induk -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-center w-16 h-16 rounded-2xl bg-indigo-50 text-indigo-500 mb-4 mx-auto">
            <i class="fas fa-address-book text-2xl"></i>
        </div>
        <h3 class="font-bold text-gray-800 text-center mb-1">Buku Induk Murid</h3>
        <p class="text-xs text-gray-400 text-center mb-4">Cetak biodata lengkap seluruh siswa (per kelas / semua)</p>
        <form method="GET" action="{{ route('laporan-siswa.buku-induk') }}" class="space-y-2">
            @csrf
            <select name="kelas_id" class="w-full border border-gray-300 rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                <option value="">- Semua Kelas -</option>
                @foreach($kelas as $k)
                    <option value="{{ $k->id }}">{{ $k->nama_kelas }}</option>
                @endforeach
            </select>
            <select name="periode_id" class="w-full border border-gray-300 rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                <option value="">- Semua Periode -</option>
                @foreach($periodes as $p)
                    <option value="{{ $p->id }}">{{ $p->tahun_ajaran }}</option>
                @endforeach
            </select>
            <button type="submit" class="w-full bg-indigo-600 text-white px-4 py-2.5 rounded-lg text-sm font-semibold hover:bg-indigo-700 transition">
                <i class="fas fa-print mr-1"></i> Cetak Buku Induk
            </button>
        </form>
    </div>

    <!-- Buku Induk per siswa -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-center w-16 h-16 rounded-2xl bg-sky-50 text-sky-500 mb-4 mx-auto">
            <i class="fas fa-id-card text-2xl"></i>
        </div>
        <h3 class="font-bold text-gray-800 text-center mb-1">Buku Induk per Murid</h3>
        <p class="text-xs text-gray-400 text-center mb-4">Cetak biodata lengkap satu murid + riwayat</p>
        <form method="GET" action="{{ route('laporan-siswa.buku-induk') }}" class="space-y-2">
            <input type="text" name="nis_search" placeholder="Cari... (isi di laporan di bawah)" class="w-full border border-gray-300 rounded-lg p-2.5 text-sm outline-none bg-gray-50" disabled>
            <a href="{{ route('master-siswa.index') }}" class="block w-full text-center bg-sky-600 text-white px-4 py-2.5 rounded-lg text-sm font-semibold hover:bg-sky-700 transition">
                Pilih dari Master Siswa
            </a>
        </form>
    </div>

    <!-- Rekap Pembayaran -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-center w-16 h-16 rounded-2xl bg-emerald-50 text-emerald-500 mb-4 mx-auto">
            <i class="fas fa-file-invoice-dollar text-2xl"></i>
        </div>
        <h3 class="font-bold text-gray-800 text-center mb-1">Rekap Pembayaran</h3>
        <p class="text-xs text-gray-400 text-center mb-4">Cetak ringkasan tagihan & status pembayaran</p>
        <form method="GET" action="{{ route('laporan-siswa.rekap-pembayaran') }}" class="space-y-2">
            <select name="periode_id" class="w-full border border-gray-300 rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-emerald-500 outline-none">
                <option value="">- Semua Periode -</option>
                @foreach($periodes as $p)
                    <option value="{{ $p->id }}">{{ $p->tahun_ajaran }}</option>
                @endforeach
            </select>
            <button type="submit" class="w-full bg-emerald-600 text-white px-4 py-2.5 rounded-lg text-sm font-semibold hover:bg-emerald-700 transition">
                <i class="fas fa-print mr-1"></i> Cetak Rekap
            </button>
        </form>
    </div>
</div>

<!-- Pilih murid untuk buku induk per murid -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mt-6">
    <h3 class="font-bold text-gray-700 mb-3"><i class="fas fa-user-graduate text-sky-500 mr-2"></i> Pilih Murid (Buku Induk per murid)</h3>
    <div class="relative">
        <select id="pilih-murid" class="w-full border border-gray-300 rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-sky-500 outline-none">
            <option value="">- Pilih Murid -</option>
            @foreach(\App\Models\Siswa::aktif()->orderBy('nama_siswa')->get() as $s)
                <option value="{{ $s->id }}">{{ $s->nama_siswa }} ({{ $s->nis }})</option>
            @endforeach
        </select>
    </div>
    <button onclick="cetakBukuInduk()" class="mt-3 bg-sky-600 text-white px-5 py-2.5 rounded-lg text-sm font-semibold hover:bg-sky-700 transition">
        <i class="fas fa-print mr-1"></i> Cetak Buku Induk Murid Ini
    </button>
</div>

<script>
    function cetakBukuInduk() {
        const id = document.getElementById('pilih-murid').value;
        if (!id) { alert('Pilih murid dulu'); return; }
        window.open('/laporan-siswa/buku-induk/' + id, '_blank');
    }
</script>
@endsection
