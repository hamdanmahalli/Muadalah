@extends('layouts.app')

@section('title', 'Buat Laporan Pertanggung Jawaban')

@section('content')
<div class="mb-6">
    <a href="{{ route('kebendaharaan.laporan.index') }}" class="text-xs font-black text-slate-500 hover:text-emerald-600 mb-3 inline-flex items-center">
        <i class="fas fa-arrow-left mr-2"></i> Kembali ke Daftar LPJ
    </a>
    <h2 class="text-2xl font-black text-slate-800 tracking-tight flex items-center">
        <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600 mr-3 shadow-inner">
            <i class="fas fa-plus"></i>
        </div>
        Buat Laporan Pertanggung Jawaban (LPJ)
    </h2>
    <p class="text-sm font-bold text-slate-400 mt-1 ml-14">1 LPJ boleh memuat banyak baris. Setiap baris: pilih pos, tulis apa yang dibeli (uraian), lalu nominal. Diajukan &rarr; divalidasi bendahara menjadi realisasi belanja.</p>
</div>

<div class="max-w-4xl">
    <form action="{{ route('kebendaharaan.laporan.store') }}" method="POST" id="formLpj" class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
        @csrf
        <div id="kasirPesan" class="hidden mb-4 flex items-center gap-3 bg-amber-50 border border-amber-200 text-amber-800 rounded-xl px-4 py-3 text-sm font-bold">
            <i class="fas fa-triangle-exclamation"></i><span></span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-5">
            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Panjar / Pencairan (opsional)</label>
                <select id="panjarSelect" class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 block p-3 outline-none transition-all font-medium cursor-pointer">
                    <option value="">— Tanpa panjar (pilih pos manual) —</option>
                    @foreach($pencairanDibayar as $pc)
                    <option value="{{ $pc['id'] }}">{{ $pc['kode'] }} · Rp {{ number_format($pc['nominal'], 0, ',', '.') }} ({{ $pc['items']->count() }} item)</option>
                    @endforeach
                </select>
                <p class="text-[10px] font-bold text-slate-400 mt-1.5">Memilih panjar otomatis mengisi keranjang sesuai item SPP yang dibayar. Semua pengeluaran wajib dibukukan per pos RAB, makanya setap baris tetap bisa diubah pos/uraian/nominalnya.</p>
            </div>
            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Tanggal</label>
                <input type="date" name="tanggal" required value="{{ date('Y-m-d') }}" class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 block p-3 outline-none transition-all font-medium">
            </div>
            <div class="sm:col-span-2">
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Keterangan (opsional)</label>
                <input type="text" name="keterangan" placeholder="Catatan singkat LPJ ini..." class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 block p-3 outline-none transition-all font-medium">
            </div>
        </div>

        <div class="border-t border-slate-100 pt-5 mb-5">
            <h3 class="text-sm font-black text-slate-700 uppercase tracking-widest mb-3"><i class="fas fa-cart-plus text-emerald-500 mr-2"></i>Tambah Baris LPJ</h3>
            <input id="kasirCari" type="text" placeholder="Cari pos..." class="mb-3 w-full sm:w-72 bg-slate-50 border border-slate-200 text-slate-800 text-sm rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 block p-3 outline-none transition-all font-medium">
            <div id="kasirKelompok" class="flex flex-wrap gap-2 mb-3"></div>
            <div id="kasirPos" class="space-y-2"></div>
        </div>

        <div class="border-t border-slate-100 pt-5">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-black text-slate-700 uppercase tracking-widest"><i class="fas fa-cart-shopping text-emerald-500 mr-2"></i>Keranjang LPJ <span id="kasirJumlah" class="text-slate-400 normal-case tracking-normal text-xs"></span></h3>
                <p class="text-sm"><span class="text-[10px] font-black text-slate-400 uppercase tracking-wider mr-2">Total</span><span id="totalRp" class="font-black text-emerald-600 text-lg">Rp 0</span></p>
            </div>
            <div id="kasirKeranjang" class="space-y-2"></div>
        </div>

        <div class="flex items-center justify-end gap-3 mt-6">
            <a href="{{ route('kebendaharaan.laporan.index') }}" class="px-5 py-3 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold text-sm rounded-xl transition-all">Batal</a>
            <button type="submit" class="px-6 py-3 bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white font-bold text-sm rounded-xl transition-all shadow-[0_4px_15px_-3px_rgba(16,185,129,0.4)]">
                <i class="fas fa-paper-plane mr-2"></i> Kirim untuk Validasi
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const KASIR = @json($kasir->all());
    const PANJAR = @json($pencairanDibayar->all());
    const rupiah = (n) => 'Rp ' + Number(n || 0).toLocaleString('id-ID');
    const esc = (s) => String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    const Numb = (s) => parseInt(String(s).replace(/[^\d]/g, '') || '0', 10);

    const formEl = document.getElementById('formLpj');
    const kasirCari = document.getElementById('kasirCari');
    const kasirKelompok = document.getElementById('kasirKelompok');
    const kasirPos = document.getElementById('kasirPos');
    const kasirKeranjang = document.getElementById('kasirKeranjang');
    const kasirJumlah = document.getElementById('kasirJumlah');
    const totalRp = document.getElementById('totalRp');
    const kasirPesanEl = document.getElementById('kasirPesan');
    const panjarSelect = document.getElementById('panjarSelect');

    let kelompokAktif = (KASIR && KASIR.length) ? KASIR[0].kode : null;
    const cari = { kata: '' };
    let keranjang = []; // {pos, nominal, detik, uraian, pencairan_item_id, sumberNominal}

    const posInfo = {}; // id -> {kode, uraian, pagu, realisasi, kelompokNama, kel}
    KASIR.forEach(k => k.pos.forEach(p => { posInfo[p.id] = Object.assign({}, p, { kelompokNama: k.nama, kel: k.kode }); }));

    function sisaPos(p) { return (p.pagu || 0) - (p.realisasi || 0); }
    function kapPosSisa(p) {
        const s = sisaPos(p);
        return 'Pagu ' + rupiah(p.pagu) + ' &middot; Realisasi LPJ ' + rupiah(p.realisasi)
            + (s < 0 ? ' &middot; Sisa <b class="text-rose-500">-' + rupiah(Math.abs(s)) + '</b>' : ' &middot; Sisa ' + rupiah(s));
    }
    function posIdDiKeranjang(id) { return keranjang.findIndex(r => r.pos.id === id); }
    function jumlahKeranjang() { return keranjang.reduce((a, r) => a + r.nominal, 0); }

    function renderKelompok() {
        if (!KASIR || !KASIR.length) {
            kasirKelompok.innerHTML = '';
            kasirPos.innerHTML = '<div class="text-center text-xs font-bold text-slate-400 py-8">Belum ada anggaran Final pada periode aktif.<br>Finalkan RAB lebih dulu pada menu Anggaran.</div>';
            return;
        }
        kasirKelompok.innerHTML = KASIR.map(k => {
            const aktif = k.kode === kelompokAktif;
            const jml = k.pos.length;
            return '<button type="button" data-kel="' + k.kode + '" class="' +
                (aktif ? 'bg-emerald-600 text-white border-emerald-600 shadow' : 'bg-white text-slate-600 border-slate-200 hover:border-emerald-300') +
                ' rounded-xl border px-3 py-2 text-[11px] font-black transition-all">' +
                esc(k.kode) + ' &middot; ' + esc(k.nama) + ' <span class="' + (aktif ? 'text-emerald-200' : 'text-slate-400') + ' ml-1">(' + jml + ')</span></button>';
        }).join('');
    }

    function renderPos() {
        const k = (KASIR || []).find(x => x.kode === kelompokAktif);
        if (!k) { kasirPos.innerHTML = ''; return; }
        const kata = cari.kata.toLowerCase();
        const daftar = k.pos.filter(p => !kata || p.kode.includes(kata) || p.uraian.toLowerCase().includes(kata));
        if (!daftar.length) { kasirPos.innerHTML = '<div class="text-center text-xs font-bold text-slate-400 py-6">Tidak ada pos cocok.</div>'; return; }
        kasirPos.innerHTML = daftar.map(p => {
            const sudah = posIdDiKeranjang(p.id) >= 0;
            return '<div class="flex items-center gap-3 bg-white rounded-xl border border-slate-200 p-3' + (sudah ? ' border-emerald-300 ring-1 ring-emerald-200' : '') + '">' +
                '<div class="flex-1 min-w-0">' +
                '<p class="text-xs font-black text-slate-800">' + esc(p.kode) + ' &middot; ' + esc(p.uraian) + '</p>' +
                '<p class="text-[10px] font-bold text-slate-400 mt-0.5">' + kapPosSisa(p) + '</p>' +
                '</div>' +
                '<button type="button" data-tambah="' + p.id + '" class="shrink-0 inline-flex items-center justify-center w-9 h-9 rounded-xl font-black text-lg bg-emerald-600 hover:bg-emerald-700 text-white cursor-pointer">+</button></div>';
        }).join('');
    }

    function barisHtml(row) {
        const p = row.pos;
        const idx = keranjang.indexOf(row);
        const dariPanjar = row.pencairan_item_id ? '<span class="text-emerald-600 ml-2"><i class="fas fa-link mr-0.5"></i>panjar SPP</span>' : '';
        return '<div class="flex items-start gap-3 bg-slate-50 rounded-xl border border-slate-200 p-3" data-posker="' + p.id + '">' +
            '<div class="flex-1 min-w-0">' +
            '<p class="text-xs font-black text-slate-800">' + esc(p.kode) + ' &middot; ' + esc(p.uraian) + dariPanjar + '</p>' +
            '<p class="text-[10px] font-bold text-slate-400 mt-0.5 sisaInfo"></p>' +
            '</div>' +
            '<div class="shrink-0 flex flex-col sm:flex-row items-stretch sm:items-center gap-2">' +
            '<input type="hidden" name="items[' + idx + '][pos_id]" value="' + p.id + '">' +
            '<input type="hidden" name="items[' + idx + '][pencairan_item_id]" value="' + (row.pencairan_item_id || '') + '">' +
            '<input type="text" placeholder="Apa yang dibeli..." value="' + esc(row.uraian || '') + '" class="uraianInput w-full sm:w-56 rounded-xl border border-slate-300 px-3 py-2 text-sm font-medium focus:ring-2 focus:ring-emerald-500 focus:outline-none">' +
            '<input type="text" name="items[' + idx + '][nominal]" class="nominalInput text-right w-36 rounded-xl border border-slate-300 px-3 py-2 text-sm font-black focus:ring-2 focus:ring-emerald-500 focus:outline-none" placeholder="0" value="' + esc(row.detik || '') + '">' +
            '<button type="button" class="hapusBaris w-9 h-9 shrink-0 inline-flex items-center justify-center rounded-lg bg-rose-50 text-rose-500 hover:bg-rose-100 font-black"><i class="fas fa-trash-can"></i></button>' +
            '</div></div>';
    }

    function renderKeranjang() {
        if (!keranjang.length) {
            kasirKeranjang.innerHTML = '<div class="text-center text-xs font-bold text-slate-400 py-10 border border-dashed border-slate-200 rounded-xl">Keranjang kosong.<br>Pilih panjar otomatis, atau pilih kelompok & klik <b>+</b> pada pos yang ingin dipertanggungjawabkan.</div>';
        } else {
            kasirKeranjang.innerHTML = keranjang.map(barisHtml).join('');
        }
        kasirJumlah.textContent = keranjang.length + ' item';
        totalRp.textContent = rupiah(jumlahKeranjang());

        kasirKeranjang.querySelectorAll('[data-posker]').forEach((el) => {
            const id = parseInt(el.dataset.posker, 10);
            const row = keranjang.find(r => r.pos.id === id);
            if (row) row.elSisa = el.querySelector('.sisaInfo');
        });
        perbaruiSemuaSisa();
    }

    function sisaTextRow(row) {
        const p = row.pos;
        const s = sisaPos(p);
        const nom = row.nominal;
        let html = kapPosSisa(p);
        if (nom > 0 && nom > s) {
            html = '<b class="text-rose-600">Melebihi pagu pos!</b> ' + html + ' (sisanya diambilkan dari pos lain / biaya lain-lain)';
        }
        if (row.sumberNominal > 0 && nom > row.sumberNominal) {
            html = rupiah(nom) + ' &gt; panjar ' + rupiah(row.sumberNominal) + ' &middot; <b class="text-rose-600">Melebihi panjar!</b> ' + html;
        }
        row.elSisa.innerHTML = html;
    }

    function perbaruiSemuaSisa() {
        keranjang.forEach(r => { if (r.elSisa) sisaTextRow(r); });
        renderPos();
    }

    function tambahPos(id) {
        const sudah = posIdDiKeranjang(id);
        if (sudah >= 0) {
            tampilkanPesan('Pos sudah ada di keranjang.');
            const elRow = kasirKeranjang.querySelector('[data-posker="' + id + '"]');
            if (elRow) {
                elRow.scrollIntoView({ behavior: 'smooth', block: 'center' });
                elRow.style.boxShadow = '0 0 0 3px rgba(245,158,11,0.45)';
                setTimeout(() => { elRow.style.boxShadow = ''; }, 1400);
            }
            return;
        }
        keranjang.push({ pos: posInfo[id], nominal: 0, detik: '', uraian: '', pencairan_item_id: null, sumberNominal: 0 });
        renderKeranjang();
        cari.kata = '';
        kasirCari.value = '';
        tampilkanPesan('');
    }

    function hapusPos(id) {
        keranjang = keranjang.filter(r => r.pos.id !== id);
        renderKeranjang();
        renderPos();
    }

    function tampilkanPesan(teks) {
        if (!teks) { kasirPesanEl.classList.add('hidden'); return; }
        kasirPesanEl.classList.remove('hidden');
        kasirPesanEl.querySelector('span').textContent = teks;
    }

    kasirKelompok.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-kel]');
        if (!btn) return;
        kelompokAktif = parseInt(btn.dataset.kel, 10);
        renderKelompok();
        renderPos();
    });
    kasirPos.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-tambah]');
        if (!btn) return;
        tambahPos(parseInt(btn.dataset.tambah, 10));
    });
    kasirKeranjang.addEventListener('input', (e) => {
        const rowDiv = e.target.closest('[data-posker]');
        if (!rowDiv) return;
        const id = parseInt(rowDiv.dataset.posker, 10);
        const row = keranjang.find(r => r.pos.id === id);
        if (!row) return;
        if (e.target.classList.contains('nominalInput')) {
            row.detik = e.target.value;
            row.nominal = Numb(e.target.value);
            row.elSisa = rowDiv.querySelector('.sisaInfo');
            sisaTextRow(row);
            totalRp.textContent = rupiah(jumlahKeranjang());
        } else if (e.target.classList.contains('uraianInput')) {
            row.uraian = e.target.value;
        }
    });
    kasirKeranjang.addEventListener('click', (e) => {
        const btn = e.target.closest('.hapusBaris');
        if (!btn) return;
        const rowDiv = btn.closest('[data-posker]');
        hapusPos(parseInt(rowDiv.dataset.posker, 10));
    });
    kasirCari.addEventListener('input', (e) => { cari.kata = e.target.value; renderPos(); });

    panjarSelect.addEventListener('change', () => {
        const id = parseInt(panjarSelect.value || '0', 10);
        keranjang = [];
        if (id) {
            const pc = PANJAR.find(x => x.id === id);
            if (pc) {
                pc.items.forEach(it => {
                    const p = posInfo[it.pos_id];
                    if (!p) return;
                    keranjang.push({
                        pos: p,
                        nominal: it.nominal,
                        detik: String(it.nominal),
                        uraian: it.uraian,
                        pencairan_item_id: it.pencairan_item_id,
                        sumberNominal: it.nominal,
                    });
                });
            }
        }
        renderKeranjang();
        tampilkanPesan('');
    });

    formEl.addEventListener('submit', (e) => {
        if (!keranjang.length) { e.preventDefault(); tampilkanPesan('Keranjang masih kosong — tambahkan minimal satu baris.'); return; }
        if (keranjang.some(r => r.nominal <= 0)) { e.preventDefault(); tampilkanPesan('Nominal tiap baris wajib lebih dari 0.'); return; }
        if (keranjang.some(r => !String(r.uraian || '').trim())) { e.preventDefault(); tampilkanPesan('Tulis uraian (apa yang dibeli) untuk setiap baris.'); return; }
    });

    renderKelompok();
    renderPos();
    renderKeranjang();
    tampilkanPesan('');
})();
</script>
@endpush