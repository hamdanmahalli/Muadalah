@extends('layouts.app')

@section('title', 'Catat Pembelian')

@section('content')
<div class="mb-6">
    <a href="{{ route('kebendaharaan.toko.pembelian.index') }}" class="text-xs font-black text-slate-500 hover:text-emerald-600 mb-3 inline-flex items-center">
        <i class="fas fa-arrow-left mr-2"></i> Kembali ke Riwayat Pembelian
    </a>
    <h2 class="text-2xl font-black text-slate-800 tracking-tight flex items-center">
        <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600 mr-3 shadow-inner">
            <i class="fas fa-plus"></i>
        </div>
        Catat Pembelian (Stok Masuk)
    </h2>
    <p class="text-sm font-bold text-slate-400 mt-1 ml-14">Pembelian menambah stok. Gunakan dana dari pencairan Modal Toko (pinjaman).</p>
</div>

<form action="{{ route('kebendaharaan.toko.pembelian.store') }}" method="POST" class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
    @csrf
    <div class="p-5 grid grid-cols-1 sm:grid-cols-3 gap-3">
        <div>
            <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Sumber Dana (Pencairan Modal)</label>
            <select name="pencairan_id" class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 block p-3 outline-none transition-all font-medium cursor-pointer">
                <option value="">— Dana bebas —</option>
                @foreach($pencairanModal as $pc)
                <option value="{{ $pc->id }}">{{ $pc->kode }} · Rp {{ number_format($pc->nominal, 0, ',', '.') }} · {{ $pc->keperluan }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Tanggal</label>
            <input type="date" name="tanggal" required value="{{ date('Y-m-d') }}" class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 block p-3 outline-none transition-all font-medium">
        </div>
        <div>
            <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Keterangan</label>
            <input type="text" name="keterangan" placeholder="mis. Beli buku paket kelas 7" class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 block p-3 outline-none transition-all font-medium">
        </div>
    </div>

    <div class="bg-slate-50 border-y border-slate-100 p-4 flex items-center justify-between">
        <h3 class="text-sm font-black text-slate-700 uppercase tracking-widest"><i class="fas fa-cart-plus text-emerald-500 mr-2"></i>Item Barang Dibeli</h3>
        <button type="button" onclick="tambahBarisPembelian()" class="inline-flex items-center px-3 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-lg transition-all">
            <i class="fas fa-plus mr-1.5"></i> Tambah Baris
        </button>
    </div>

    <div id="daftar-pembelian" class="p-5 space-y-3">
        <div class="grid grid-cols-1 sm:grid-cols-12 gap-2 items-center baris-item">
            <div class="sm:col-span-4">
                <select name="items[0][barang_id]" required class="w-full bg-white border border-slate-200 text-slate-800 text-xs rounded-xl focus:ring-2 focus:ring-emerald-500 p-2.5 outline-none transition-all font-medium cursor-pointer">
                    <option value="">— Pilih Barang —</option>
                    @foreach($barangList as $b)
                    <option value="{{ $b->id }}" data-jual="{{ $b->harga_jual }}">{{ $b->nama }} (jual: {{ number_format($b->harga_jual, 0, ',', '.') }})</option>
                    @endforeach
                </select>
            </div>
            <div class="sm:col-span-2">
                <input type="number" step="any" name="items[0][qty]" required placeholder="Qty" min="1" value="1" class="w-full bg-white border border-slate-200 text-slate-800 text-xs rounded-xl focus:ring-2 focus:ring-emerald-500 p-2.5 outline-none transition-all font-medium">
            </div>
            <div class="sm:col-span-2">
                <input type="text" name="items[0][harga_beli]" required placeholder="Harga beli" class="w-full bg-white border border-slate-200 text-slate-800 text-xs rounded-xl focus:ring-2 focus:ring-emerald-500 p-2.5 outline-none transition-all font-medium">
            </div>
            <div class="sm:col-span-3">
                <input type="text" name="items[0][harga_jual]" placeholder="Harga jual (kosong = master)" class="w-full bg-white border border-slate-200 text-slate-800 text-xs rounded-xl focus:ring-2 focus:ring-emerald-500 p-2.5 outline-none transition-all font-medium">
            </div>
            <div class="sm:col-span-1 text-right">
                <button type="button" onclick="hapusBaris(this)" class="text-rose-500 hover:text-rose-700 text-sm px-1"><i class="fas fa-trash"></i></button>
            </div>
        </div>
    </div>

    <div class="p-5 bg-slate-50/60 border-t border-slate-100 flex flex-col sm:flex-row justify-between items-end gap-3">
        <p class="text-xs font-bold text-slate-400"><i class="fas fa-info-circle text-sky-400 mr-1"></i>Harga beli diisi angka penuh, mis. 15000.</p>
        <div class="flex items-center gap-3">
            <a href="{{ route('kebendaharaan.toko.pembelian.index') }}" class="px-5 py-3 bg-white hover:bg-slate-100 text-slate-600 border border-slate-200 font-bold text-sm rounded-xl transition-all">Batal</a>
            <button type="submit" class="px-6 py-3 bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white font-bold text-sm rounded-xl transition-all shadow-[0_4px_15px_-3px_rgba(16,185,129,0.4)]">
                <i class="fas fa-check mr-2"></i> Simpan Pembelian
            </button>
        </div>
    </div>
</form>

<script>
    var indeksPembelian = 1;
    function tambahBarisPembelian() {
        var wadah = document.getElementById('daftar-pembelian');
        var todo = document.createElement('div');
        todo.className = 'grid grid-cols-1 sm:grid-cols-12 gap-2 items-center baris-item';
        todo.innerHTML = daftarPeta({{ Js::from($barangList->map(fn($b) => ['id' => $b->id, 'nama' => $b->nama, 'jual' => (int)$b->harga_jual])->values()) }});
        wadah.appendChild(todo);
        indeksPembelian++;
    }
    function hapusBaris(btn) {
        var semua = document.querySelectorAll('.baris-item');
        if (semua.length <= 1) return;
        btn.closest('.baris-item').remove();
    }
    function daftarPeta(barang) {
        var opt = '<option value="">— Pilih Barang —</option>';
        barang.forEach(function(b){ opt += '<option value="' + b.id + '" data-jual="' + b.jual + '">' + b.nama + ' (jual: ' + b.jual.toLocaleString('id-ID') + ')</option>'; });
        return '<div class="sm:col-span-4"><select name="items[' + indeksPembelian + '][barang_id]" required class="w-full bg-white border border-slate-200 text-slate-800 text-xs rounded-xl focus:ring-2 focus:ring-emerald-500 p-2.5 outline-none transition-all font-medium cursor-pointer">' + opt + '</select></div>'
             + '<div class="sm:col-span-2"><input type="number" step="any" name="items[' + indeksPembelian + '][qty]" required placeholder="Qty" min="1" value="1" class="w-full bg-white border border-slate-200 text-slate-800 text-xs rounded-xl focus:ring-2 focus:ring-emerald-500 p-2.5 outline-none transition-all font-medium"></div>'
             + '<div class="sm:col-span-2"><input type="text" name="items[' + indeksPembelian + '][harga_beli]" required placeholder="Harga beli" class="w-full bg-white border border-slate-200 text-slate-800 text-xs rounded-xl focus:ring-2 focus:ring-emerald-500 p-2.5 outline-none transition-all font-medium"></div>'
             + '<div class="sm:col-span-3"><input type="text" name="items[' + indeksPembelian + '][harga_jual]" placeholder="Harga jual (kosong = master)" class="w-full bg-white border border-slate-200 text-slate-800 text-xs rounded-xl focus:ring-2 focus:ring-emerald-500 p-2.5 outline-none transition-all font-medium"></div>'
             + '<div class="sm:col-span-1 text-right"><button type="button" onclick="hapusBaris(this)" class="text-rose-500 hover:text-rose-700 text-sm px-1"><i class="fas fa-trash"></i></button></div>';
    }
</script>
@endsection