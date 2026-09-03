@extends('layouts.app')

@section('title', 'Detail Tagihan')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h2 class="text-2xl font-bold text-gray-800"><i class="fas fa-file-invoice mr-2 text-emerald-600"></i> Detail Tagihan</h2>
    <a href="{{ route('tagihan.index') }}" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-semibold hover:bg-gray-300 transition"><i class="fas fa-arrow-left mr-1"></i> Kembali</a>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <!-- Info tagihan -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <p class="text-xs font-bold text-gray-400 uppercase mb-1">Siswa</p>
        <p class="text-xl font-extrabold text-gray-800">{{ $tagihan->siswa?->nama_siswa }} <span class="text-sm font-semibold text-gray-400">({{ $tagihan->siswa?->nis }})</span></p>
        <div class="grid grid-cols-2 gap-4 mt-4 text-sm">
            <div class="border border-gray-100 rounded-lg p-3 bg-gray-50"><p class="text-xs font-bold text-gray-400 uppercase">Jenis</p><p class="font-bold text-gray-700">{{ $tagihan->jenisTagihan?->nama_tagihan }}</p></div>
            <div class="border border-gray-100 rounded-lg p-3 bg-gray-50"><p class="text-xs font-bold text-gray-400 uppercase">Periode</p><p class="font-bold text-gray-700">{{ $tagihan->periode?->tahun_ajaran }}</p></div>
            <div class="border border-gray-100 rounded-lg p-3 bg-emerald-50"><p class="text-xs font-bold text-gray-400 uppercase">Nominal</p><p class="font-bold text-emerald-700">Rp {{ number_format($tagihan->nominal,0,',','.') }}</p></div>
            <div class="border border-gray-100 rounded-lg p-3 bg-amber-50"><p class="text-xs font-bold text-gray-400 uppercase">Sisa</p><p class="font-bold text-amber-600">Rp {{ number_format($tagihan->sisa(),0,',','.') }}</p></div>
        </div>
    </div>

    <!-- Form pembayaran -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="font-bold text-gray-700 mb-4"><i class="fas fa-hand-holding-usd text-emerald-500 mr-2"></i> Catat Pembayaran</h3>
        <form method="POST" action="{{ route('tagihan.bayar', $tagihan->id) }}">
            @csrf
            <div class="space-y-3 text-sm">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Nominal Dibayar (maks Rp {{ number_format($tagihan->sisa(),0,',','.') }}) <span class="text-red-500">*</span></label>
                    <input type="number" name="nominal_dibayar" required max="{{ $tagihan->sisa() }}" class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-emerald-500 outline-none">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Tanggal Bayar</label>
                        <input type="date" name="tanggal_bayar" value="{{ now()->format('Y-m-d') }}" required class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-emerald-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Metode</label>
                        <select name="metode" class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-emerald-500 outline-none">
                            <option value="Tunai">Tunai</option>
                            <option value="Transfer">Transfer</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Keterangan</label>
                    <input type="text" name="keterangan" class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-emerald-500 outline-none">
                </div>
                <button type="submit" class="w-full bg-emerald-600 text-white px-4 py-2.5 rounded-lg text-sm font-semibold hover:bg-emerald-700 transition">
                    <i class="fas fa-check mr-1"></i> Simpan Pembayaran
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Riwayat pembayaran -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mt-6">
    <h3 class="font-bold text-gray-700 mb-4"><i class="fas fa-history text-indigo-500 mr-2"></i> Riwayat Pembayaran</h3>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left text-xs font-bold text-gray-500 uppercase">Tanggal</th>
                    <th class="px-4 py-2 text-left text-xs font-bold text-gray-500 uppercase">Nominal</th>
                    <th class="px-4 py-2 text-left text-xs font-bold text-gray-500 uppercase">Metode</th>
                    <th class="px-4 py-2 text-left text-xs font-bold text-gray-500 uppercase">Ket</th>
                    <th class="px-4 py-2 text-center text-xs font-bold text-gray-500 uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($tagihan->pembayarans as $b)
                <tr>
                    <td class="px-4 py-2 text-gray-600">{{ $b->tanggal_bayar?->format('d-m-Y') }}</td>
                    <td class="px-4 py-2 font-bold text-emerald-600">Rp {{ number_format($b->nominal_dibayar,0,',','.') }}</td>
                    <td class="px-4 py-2 text-gray-600">{{ $b->metode }}</td>
                    <td class="px-4 py-2 text-gray-500">{{ $b->keterangan }}</td>
                    <td class="px-4 py-2 text-center">
                        <form method="POST" action="{{ route('tagihan.hapusBayar', [$tagihan->id, $b->id]) }}" onsubmit="return confirm('Hapus pembayaran ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-7 h-7 rounded-full bg-red-50 text-red-500 hover:bg-red-500 hover:text-white transition"><i class="fas fa-trash-alt"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-4 py-6 text-center text-gray-400">Belum ada pembayaran.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
