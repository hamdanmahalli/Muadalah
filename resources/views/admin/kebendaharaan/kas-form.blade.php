@extends('layouts.app')

@section('title', 'Catat Transaksi')

@section('content')
<div class="mb-6">
    <a href="{{ route('kebendaharaan.laporan.index') }}" class="text-xs font-black text-slate-500 hover:text-emerald-600 mb-3 inline-flex items-center">
        <i class="fas fa-arrow-left mr-2"></i> Kembali ke Transaksi
    </a>
    <h2 class="text-2xl font-black text-slate-800 tracking-tight flex items-center">
        <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600 mr-3 shadow-inner">
            <i class="fas fa-plus"></i>
        </div>
        <span id="judulForm">Catat Pengeluaran</span>
    </h2>
    <p class="text-sm font-bold text-slate-400 mt-1 ml-14">
        Buku kas <span class="text-emerald-700">SPP {{ $sppAktif->kode }}</span> &middot; {{ $sppAktif->keperluan }} &middot;
        Pengeluaran wajib mengikuti pos yang sudah dicairkan (SPP dibayar). Bukti nota otomatis dikompres di bawah 1&nbsp;MB.
    </p>
</div>

@if($errors->any())
<div class="mb-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl px-4 py-3 text-sm font-bold">
    <div class="flex items-center gap-3"><i class="fas fa-triangle-exclamation"></i> Periksa kembali isian Anda:</div>
    <ul class="mt-2 ml-7 list-disc space-y-1 text-xs font-semibold">
        @foreach($errors->all() as $e)
        <li>{{ $e }}</li>
        @endforeach
    </ul>
</div>
@endif

<div class="max-w-2xl">
    <form action="{{ route('kebendaharaan.laporan.store') }}" method="POST" enctype="multipart/form-data" id="formKas" class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
        @csrf
        <input type="hidden" name="jenis" id="jenisInput" value="keluar">

        {{-- Toggle jenis --}}
        <div class="grid grid-cols-2 gap-2 p-1 bg-slate-100 rounded-2xl mb-5">
            <button type="button" data-jenis="keluar" class="toggleJenis py-3 rounded-xl text-sm font-black transition-all bg-rose-600 text-white shadow">
                <i class="fas fa-arrow-up mr-1.5"></i> Pengeluaran
            </button>
            <button type="button" data-jenis="masuk" class="toggleJenis py-3 rounded-xl text-sm font-black transition-all text-slate-500">
                <i class="fas fa-arrow-down mr-1.5"></i> Pemasukan
            </button>
        </div>

        {{-- ============ KELUAR ============ --}}
        <div id="secKeluar" class="space-y-4">
            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Sumber Dana — SPP Dibayar (Buku Aktif)</label>
                <select name="pencairan_id" id="sppSelect" required class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 block p-3 outline-none transition-all font-medium cursor-pointer">
                    @foreach($sppJson as $pc)
                    <option value="{{ $pc['id'] }}">{{ $pc['kode'] }} · {{ $pc['tanggal'] }} · Rp {{ number_format($pc['nominal'], 0, ',', '.') }}</option>
                    @endforeach
                </select>
                <p class="text-[10px] font-bold text-slate-400 mt-1.5">Buku aktif: SPP rutin terakhir yang dibayar dan belum ditutup. Pos yang tidak diajukan tidak akan tersedia.</p>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Pos Anggaran (dari SPP)</label>
                <select name="pencairan_item_id" id="posSelect" required class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 block p-3 outline-none transition-all font-medium cursor-pointer">
                    <option value="">— Pilih SPP lebih dulu —</option>
                </select>
                <p class="text-[10px] font-bold text-slate-400 mt-1.5" id="sisaInfo">Pilih pos untuk melihat sisa dana.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Tanggal</label>
                    <input type="date" name="tanggal" value="{{ date('Y-m-d') }}" required class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 block p-3 outline-none transition-all font-medium">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Nominal (Rp)</label>
                    <input type="text" name="nominal" id="nominalInput" placeholder="mis. 250.000" class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 block p-3 outline-none transition-all font-medium">
                </div>
            </div>

            <div id="peringatan" class="hidden flex items-center gap-2 bg-amber-50 border border-amber-200 text-amber-800 rounded-xl px-4 py-2.5 text-xs font-bold">
                <i class="fas fa-triangle-exclamation"></i><span></span>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Uraian Belanja</label>
                <input type="text" name="uraian" placeholder="mis. Pembelian ATK kegiatan" class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 block p-3 outline-none transition-all font-medium">
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Keterangan (opsional)</label>
                <input type="text" name="keterangan" placeholder="Catatan tambahan..." class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 block p-3 outline-none transition-all font-medium">
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Foto Nota (opsional)</label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    <label class="flex items-center justify-center gap-2 bg-slate-50 border border-slate-200 text-slate-600 text-sm font-bold rounded-xl p-3 cursor-pointer hover:border-emerald-400 hover:text-emerald-600 transition-all">
                        <i class="fas fa-image"></i> Pilih dari Galeri
                        <input type="file" name="nota" id="notaInput" accept="image/*" class="hidden">
                    </label>
                    <label class="flex items-center justify-center gap-2 bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm font-bold rounded-xl p-3 cursor-pointer hover:border-emerald-500 hover:text-emerald-800 transition-all">
                        <i class="fas fa-camera"></i> Ambil Foto
                        <input type="file" name="nota" id="notaCameraInput" accept="image/*" capture="environment" class="hidden">
                    </label>
                </div>
                <p class="text-[10px] font-bold text-slate-400 mt-1.5">Bisa pilih dari galeri atau langsung memotret nota. JPG/PNG/WebP &mdash; otomatis dikompres ke &lt; 1 MB saat disimpan.</p>
                <img id="notaPreview" src="" alt="Pratinjau" class="hidden mt-3 max-h-48 rounded-xl border border-slate-200">
            </div>
        </div>

        {{-- ============ MASUK ============ --}}
        <div id="secMasuk" class="space-y-4 hidden">
            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Uraian Pemasukan</label>
                <input type="text" name="uraian" disabled placeholder="mis. Setoran hasil penjualan buku" class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 block p-3 outline-none transition-all font-medium">
            </div>
            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Sesuai Rencana Pemasukan (opsional)</label>
                <select name="anggaran_pemasukan_id" disabled class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 block p-3 outline-none transition-all font-medium cursor-pointer">
                    <option value="">— Tanpa rencana —</option>
                    @foreach($rencana as $r)
                    <option value="{{ $r->id }}">{{ $r->uraian }}</option>
                    @endforeach
                </select>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Tanggal</label>
                    <input type="date" name="tanggal" value="{{ date('Y-m-d') }}" disabled class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 block p-3 outline-none transition-all font-medium">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Jumlah (Rp)</label>
                    <input type="text" name="jumlah" disabled placeholder="mis. 150.000" class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 block p-3 outline-none transition-all font-medium">
                </div>
            </div>
            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Keterangan (opsional)</label>
                <input type="text" name="keterangan" disabled placeholder="Catatan tambahan..." class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 block p-3 outline-none transition-all font-medium">
            </div>
            <p class="text-[10px] font-bold text-slate-400">Nominal pemasukan manual dicatat terpisah dari dana SPP (masuk ke rekap keuangan bendahara).</p>
        </div>

        <div class="flex items-center justify-end gap-3 mt-6">
            <a href="{{ route('kebendaharaan.laporan.index') }}" class="px-5 py-3 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold text-sm rounded-xl transition-all">Batal</a>
            <button type="submit" id="btnSimpan" class="px-6 py-3 bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white font-bold text-sm rounded-xl transition-all shadow-[0_4px_15px_-3px_rgba(16,185,129,0.4)]">
                <i class="fas fa-check mr-2"></i> Simpan
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const SPP = @json($sppJson->all());
    const rupiah = (n) => 'Rp ' + Number(n || 0).toLocaleString('id-ID');
    const Numb = (s) => parseInt(String(s).replace(/[^\d]/g, '') || '0', 10);
    const esc = (s) => String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');

    const jenisInput = document.getElementById('jenisInput');
    const secKeluar = document.getElementById('secKeluar');
    const secMasuk = document.getElementById('secMasuk');
    const judulForm = document.getElementById('judulForm');
    const sppSelect = document.getElementById('sppSelect');
    const posSelect = document.getElementById('posSelect');
    const nominalInput = document.getElementById('nominalInput');
    const sisaInfo = document.getElementById('sisaInfo');
    const peringatan = document.getElementById('peringatan');
    const notaInput = document.getElementById('notaInput');
    const notaCameraInput = document.getElementById('notaCameraInput');
    const notaPreview = document.getElementById('notaPreview');
    const btnSimpan = document.getElementById('btnSimpan');

    let sisaTerpilih = null;

    function aktifkan(sec, on) {
        sec.querySelectorAll('input, select').forEach((el) => { el.disabled = !on; });
    }

    function setJenis(jenis) {
        jenisInput.value = jenis;
        const keluar = jenis === 'keluar';
        secKeluar.classList.toggle('hidden', !keluar);
        secMasuk.classList.toggle('hidden', keluar);
        aktifkan(secKeluar, keluar);
        aktifkan(secMasuk, !keluar);
        judulForm.textContent = keluar ? 'Catat Pengeluaran' : 'Catat Pemasukan';
        btnSimpan.className = 'px-6 py-3 active:scale-95 text-white font-bold text-sm rounded-xl transition-all ' +
            (keluar ? 'bg-rose-600 hover:bg-rose-700 shadow-[0_4px_15px_-3px_rgba(225,29,72,0.4)]'
                    : 'bg-emerald-600 hover:bg-emerald-700 shadow-[0_4px_15px_-3px_rgba(16,185,129,0.4)]');
        document.querySelectorAll('.toggleJenis').forEach((b) => {
            const aktif = b.dataset.jenis === jenis;
            b.className = 'toggleJenis py-3 rounded-xl text-sm font-black transition-all ' +
                (aktif ? (keluar ? 'bg-rose-600 text-white shadow' : 'bg-emerald-600 text-white shadow') : 'text-slate-500');
        });
    }

    function isiPos() {
        const id = parseInt(sppSelect.value || '0', 10);
        const pc = SPP.find((x) => x.id === id);
        sisaTerpilih = null;
        if (!pc) {
            posSelect.innerHTML = '<option value="">— Pilih SPP lebih dulu —</option>';
            sisaInfo.textContent = 'Pilih pos untuk melihat sisa dana.';
            sembunyikanPeringatan();
            return;
        }
        posSelect.innerHTML = '<option value="">— Pilih pos —</option>' + pc.items.map((it) =>
            '<option value="' + it.pencairan_item_id + '">' + esc(it.kode) + ' · ' + esc(it.uraian)
            + ' (sisa ' + rupiah(it.sisa) + ')</option>').join('');
        sisaInfo.textContent = 'SPP ' + pc.kode + ' · ' + (pc.keperluan || '') + ' · dana ' + rupiah(pc.nominal);
        sembunyikanPeringatan();
    }

    function pilihPos() {
        const pc = SPP.find((x) => x.id === parseInt(sppSelect.value || '0', 10));
        const item = pc ? pc.items.find((it) => it.pencairan_item_id === parseInt(posSelect.value || '0', 10)) : null;
        sisaTerpilih = item ? item.sisa : null;
        if (item) {
            sisaInfo.textContent = 'Sisa dana pos ' + item.kode + ': ' + rupiah(item.sisa) + ' (dana ' + rupiah(item.nominal) + ')';
        }
        cekPeringatan();
    }

    function cekPeringatan() {
        const nom = Numb(nominalInput.value);
        if (sisaTerpilih !== null && nom > sisaTerpilih) {
            peringatan.querySelector('span').textContent = 'Nominal melebihi sisa SPP pos ini (' + rupiah(sisaTerpilih) + '). Tetap bisa disimpan sebagai catatan realita belanja.';
            peringatan.classList.remove('hidden');
        } else {
            sembunyikanPeringatan();
        }
    }

    function sembunyikanPeringatan() { peringatan.classList.add('hidden'); }

    document.querySelectorAll('.toggleJenis').forEach((b) => {
        b.addEventListener('click', () => setJenis(b.dataset.jenis));
    });
    sppSelect.addEventListener('change', isiPos);
    posSelect.addEventListener('change', pilihPos);
    nominalInput.addEventListener('input', cekPeringatan);

    function resetPreview() {
        notaPreview.classList.add('hidden');
        notaPreview.src = '';
    }

    function setPreview(input) {
        const f = input.files && input.files[0];
        if (!f) { resetPreview(); return; }
        notaPreview.src = URL.createObjectURL(f);
        notaPreview.classList.remove('hidden');
    }

    let sinkronNota = false;
    function tandaiNotaDipilih(input, lain) {
        if (sinkronNota) return;
        sinkronNota = true;
        lain.value = '';
        setPreview(input);
        sinkronNota = false;
    }

    notaInput.addEventListener('change', () => tandaiNotaDipilih(notaInput, notaCameraInput));
    notaCameraInput.addEventListener('change', () => tandaiNotaDipilih(notaCameraInput, notaInput));

    setJenis('keluar');

    // Buku aktif selalu merupakan SPP tunggal; isi daftar pos langsung saat halaman terbuka.
    if (sppSelect.options.length > 0) {
        sppSelect.value = sppSelect.options[0].value;
        isiPos();
        const pc = SPP.find((x) => x.id === parseInt(sppSelect.value || '0', 10));
        if (pc && pc.items.length === 1) {
            posSelect.value = String(pc.items[0].pencairan_item_id);
            pilihPos();
        }
    }
})();
</script>
@endpush