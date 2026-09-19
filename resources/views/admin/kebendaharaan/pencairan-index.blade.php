@extends('layouts.app')

@section('title', 'Pencairan (SPP)')

@section('content')
@php
    $labelStatus = ['diajukan' => 'Diajukan', 'dibayar' => 'Dibayar', 'ditolak' => 'Ditolak'];
    $bgStatus = ['diajukan' => 'bg-amber-100 text-amber-700 border-amber-200', 'dibayar' => 'bg-emerald-100 text-emerald-700 border-emerald-200', 'ditolak' => 'bg-rose-100 text-rose-700 border-rose-200'];
    $ikonStatus = ['diajukan' => 'fa-clock', 'dibayar' => 'fa-circle-check', 'ditolak' => 'fa-circle-xmark'];
    $bulanFiskal = bulan_fiskal_list();
@endphp
<div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
    <div>
        <h2 class="text-2xl font-black text-slate-800 tracking-tight flex items-center">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600 mr-3 shadow-inner">
                <i class="fas fa-hand-holding-dollar"></i>
            </div>
            Pencairan (SPP)
        </h2>
        <p class="text-sm font-bold text-slate-400 mt-1 ml-14">
            Surat Permintaan Pembayaran: 1 SPP = 1 bulan anggaran, boleh memuat banyak pos. Diajukan &rarr; dibayar bendahara.
        </p>
    </div>
</div>

@if(session('sukses'))
<div class="mb-4 flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl px-4 py-3 text-sm font-bold">
    <i class="fas fa-circle-check"></i> {{ session('sukses') }}
</div>
@endif
@if(session('error'))
<div class="mb-4 flex items-center gap-3 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl px-4 py-3 text-sm font-bold">
    <i class="fas fa-triangle-exclamation"></i> {{ session('error') }}
</div>
@endif
@if(session('warning'))
<div class="mb-4 bg-amber-50 border border-amber-200 text-amber-800 rounded-xl px-4 py-3 text-sm font-bold">
    <div class="flex items-center gap-3"><i class="fas fa-triangle-exclamation"></i> {{ session('warning') }}</div>
    @if(session('warning_detail'))
    <ul class="mt-2 ml-7 list-disc space-y-1 text-xs font-semibold">
        @foreach(session('warning_detail') as $w)
        <li>{{ $w }}</li>
        @endforeach
    </ul>
    @endif
</div>
@endif

<!-- FORM PENGAJUAN -->
@can('akses_pencairan')
<div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden mb-6">
    <div class="bg-slate-50 border-b border-slate-100 p-4 flex items-center justify-between">
        <h3 class="text-sm font-black text-slate-700 uppercase tracking-widest"><i class="fas fa-paper-plane text-emerald-500 mr-2"></i>Ajukan SPP</h3>
        <span class="bg-emerald-100 text-emerald-700 text-[10px] font-black px-2 py-1 rounded-md">{{ bulan_fiskal_label(1) }} &ndash; {{ bulan_fiskal_label(12) }}</span>
    </div>
    <form action="{{ route('kebendaharaan.pencairan.store') }}" method="POST" id="formSpp">
        @csrf
        <input type="hidden" name="jenis" id="jenisSpp" value="rutin">

        <!-- Tabs -->
        <div class="p-5 pb-0 grid grid-cols-2 gap-2 max-w-md">
            <button type="button" data-jenis="rutin" class="jenisTab px-4 py-2.5 bg-emerald-600 text-white font-bold text-xs rounded-xl">SPP RUTIN (POS)</button>
            <button type="button" data-jenis="modal_toko" class="jenisTab px-4 py-2.5 bg-slate-100 text-slate-500 hover:bg-emerald-50 font-bold text-xs rounded-xl">MODAL TOKO</button>
        </div>

        <div class="p-5 space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-[11px] font-black text-slate-500 uppercase tracking-wider mb-1.5">Tanggal Pengajuan</label>
                    <input type="date" name="tanggal_aju" required value="{{ now()->format('Y-m-d') }}" class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm font-semibold focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>
                <div id="blkBulan">
                    <label class="block text-[11px] font-black text-slate-500 uppercase tracking-wider mb-1.5">Bulan Anggaran</label>
                    <select name="bulan_fiskal" id="bulan_fiskal" class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm font-semibold focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        <option value="">-- Pilih Bulan --</option>
                        @foreach($bulanFiskal as $id => $nama)
                        <option value="{{ $id }}" {{ old('bulan_fiskal') == $id ? 'selected' : '' }}>{{ $nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[11px] font-black text-slate-500 uppercase tracking-wider mb-1.5">Keperluan</label>
                    <input type="text" name="keperluan" required value="{{ old('keperluan') }}" placeholder="mis. Operasional bulan Juli" class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm font-semibold focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>
            </div>

            <!-- RUTIN: kasir dua panel -->
            <div id="blkRutin" class="mt-4">
                <div class="grid grid-cols-1 lg:grid-cols-5 gap-4">

                    <!-- Panel kiri: pilih kelompok -> pos -->
                    <div class="lg:col-span-2 bg-slate-50 rounded-2xl border border-slate-200 overflow-hidden flex flex-col">
                        <div class="p-4 border-b border-slate-200">
                            <div class="flex items-center justify-between mb-2">
                                <h4 class="text-sm font-black text-slate-700 uppercase tracking-widest"><i class="fas fa-basket-shopping text-emerald-500 mr-2"></i>Pilih Pos</h4>
                                <span class="text-[10px] font-black text-slate-400">{{ $kasir->sum(fn ($k) => $k['pos']->count()) }} pos</span>
                            </div>
                            <input type="text" id="kasirCari" placeholder="Cari kode / uraian pos…" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm font-semibold focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        </div>
                        <div id="kasirKelompok" class="flex flex-wrap gap-2 px-4 pt-4"></div>
                        <div id="kasirPos" class="p-4 space-y-2 overflow-y-auto max-h-[340px]"></div>
                    </div>

                    <!-- Panel kanan: keranjang -->
                    <div class="lg:col-span-3 bg-white rounded-2xl border border-slate-200 overflow-hidden flex flex-col">
                        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                            <h4 class="text-sm font-black text-slate-700 uppercase tracking-widest"><i class="fas fa-cart-shopping text-emerald-500 mr-2"></i>Keranjang SPP</h4>
                            <span id="kasirJumlah" class="bg-emerald-100 text-emerald-700 text-[10px] font-black px-2 py-1 rounded-md">0 item</span>
                        </div>
                        <div id="kasirKeranjang" class="p-4 space-y-2 flex-1 max-h-[340px] overflow-y-auto"></div>
                        <div class="px-4 pb-4">
                            <div id="totalRow" class="flex items-center justify-between bg-emerald-50 border border-emerald-200 rounded-xl px-4 py-3">
                                <span class="text-xs font-black text-emerald-800 uppercase tracking-wider">Total SPP</span>
                                <span id="totalSpp" class="text-lg font-black text-emerald-800">Rp 0</span>
                            </div>
                        </div>
                    </div>
                </div>

                <p id="kasirPesan" class="text-[11px] font-bold text-rose-500 mt-3 hidden"><i class="fas fa-triangle-exclamation mr-1"></i><span></span></p>
            </div>

            <!-- Modal toko -->
            <div id="blkModal" class="hidden mt-4">
                <label class="block text-[11px] font-black text-slate-500 uppercase tracking-wider mb-1.5">Nominal Modal Toko</label>
                <input type="text" name="nominal" id="nominalModal" placeholder="0" class="w-full max-w-xs rounded-xl border border-slate-300 px-3 py-2.5 text-sm font-black focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                <p class="text-[10px] font-bold text-slate-400 mt-1.5">Tanpa pos/bulan. Otomatis menjadi pinjaman (buku belanja toko) saat dibayar.</p>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="inline-flex items-center px-6 py-3 bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white font-bold text-sm rounded-xl transition-all">
                    <i class="fas fa-paper-plane mr-2"></i> Ajukan SPP
                </button>
                <span class="text-[10px] font-bold text-slate-400">Melebihi alokasi bulan diperbolehkan, tetapi ditandai peringatan.</span>
            </div>
        </div>
    </form>
</div>
@endcan

<!-- DAFTAR SPP -->
<div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="bg-slate-50 border-b border-slate-100 p-4 flex flex-wrap items-center justify-between gap-3">
        <h3 class="text-sm font-black text-slate-700 uppercase tracking-widest"><i class="fas fa-list text-emerald-500 mr-2"></i>Daftar SPP</h3>
        <form method="GET" class="flex items-center gap-2">
            <select name="status" onchange="this.form.submit()" class="rounded-xl border border-slate-300 px-3 py-1.5 text-xs font-bold text-slate-600 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                <option value="">Semua Status</option>
                @foreach($labelStatus as $k => $v)
                <option value="{{ $k }}" {{ request('status') == $k ? 'selected' : '' }}>{{ $v }}</option>
                @endforeach
            </select>
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-[10px] font-black uppercase tracking-widest text-slate-400 border-b border-slate-100">
                    <th class="px-5 py-3">Kode / Tanggal</th>
                    <th class="px-3 py-3">Keperluan &amp; Pos</th>
                    <th class="px-3 py-3">Bulan</th>
                    <th class="px-3 py-3 text-right">Total</th>
                    <th class="px-3 py-3">Status</th>
                    <th class="px-5 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($list as $pc)
                <tr class="border-b border-slate-50 hover:bg-slate-50/50 align-top">
                    <td class="px-5 py-4">
                        <p class="font-black text-slate-800 text-xs">{{ $pc->kode }}</p>
                        <p class="text-[11px] font-bold text-slate-400 mt-0.5">{{ $pc->tanggal_aju->format('d M Y') }}</p>
                        <p class="text-[10px] font-black {{ $pc->jenis === 'modal_toko' ? 'text-indigo-500' : 'text-emerald-600' }} mt-0.5 uppercase">{{ $pc->jenis === 'modal_toko' ? 'Modal Toko' : 'Rutin' }}</p>
                    </td>
                    <td class="px-3 py-4 max-w-md">
                        <p class="font-bold text-slate-700 text-xs">{{ $pc->keperluan }}</p>
                        @if($pc->jenis === 'rutin' && $pc->items->isNotEmpty())
                        <ul class="mt-1.5 space-y-1">
                            @foreach($pc->items as $it)
                            <li class="text-[11px] font-semibold text-slate-500 flex items-center gap-1.5">
                                <i class="fas fa-bullseye text-emerald-300 text-[8px]"></i>
                                {{ $it->pos?->kode }} &middot; {{ $it->pos?->uraian }}
                                <span class="text-slate-400 ml-auto">Rp {{ number_format($it->nominal, 0, ',', '.') }}</span>
                            </li>
                            @endforeach
                        </ul>
                        @endif
                        @if($pc->jenis === 'modal_toko')
                        <p class="text-[11px] font-semibold text-slate-500 mt-1">Modal toko &mdash; menjadi pinjaman saat dibayar.</p>
                        @endif
                        @if($pc->status === 'ditolak')
                        <p class="text-[11px] font-semibold text-rose-500 mt-1.5">Alasan: {{ $pc->tolak_alasan }}</p>
                        @endif
                    </td>
                    <td class="px-3 py-4">
                        <span class="inline-flex px-2 py-1 rounded-md bg-slate-100 text-slate-600 text-[10px] font-black">{{ $pc->bulan_fiskal_label }}</span>
                    </td>
                    <td class="px-3 py-4 text-right font-black text-slate-800 text-xs whitespace-nowrap">Rp {{ number_format($pc->jumlah, 0, ',', '.') }}</td>
                    <td class="px-3 py-4">
                        <span class="inline-flex items-center gap-1.5 text-[10px] font-black px-2.5 py-1.5 rounded-lg border {{ $bgStatus[$pc->status] }}">
                            <i class="fas {{ $ikonStatus[$pc->status] }}"></i> {{ $labelStatus[$pc->status] }}
                        </span>
                        @if($pc->jenis === 'modal_toko' && $pc->status === 'dibayar')
                        <span class="block mt-1.5 text-[10px] font-black {{ $pc->isLunasPanjar() ? 'text-emerald-600' : 'text-indigo-600' }}">
                            Panjar {{ $pc->isLunasPanjar() ? 'Lunas' : 'Aktif' }}
                        </span>
                        @endif
                    </td>
                    <td class="px-5 py-4">
                        @can('akses_validasi_pencairan')
                        @if($pc->status === 'diajukan')
                        <div class="flex items-center justify-end gap-2">
                            <form action="{{ route('kebendaharaan.pencairan.bayar', $pc->id) }}" method="POST" onsubmit="return confirm('Bayar SPP {{ $pc->kode }}? Uang langsung dicatat keluar kas.')">
                                @csrf
                                <button class="inline-flex items-center px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[10px] rounded-lg">
                                    <i class="fas fa-circle-check mr-1.5"></i> Bayar
                                </button>
                            </form>
                            <form action="{{ route('kebendaharaan.pencairan.tolak', $pc->id) }}" method="POST">
                                @csrf
                                <input type="hidden" name="tolak_alasan" value="Ditolak oleh bendahara.">
                                <button class="inline-flex items-center px-3 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 font-bold text-[10px] rounded-lg" onclick="return confirmTolak(this.form, '{{ $pc->kode }}')">
                                    <i class="fas fa-circle-xmark mr-1.5"></i> Tolak
                                </button>
                            </form>
                        </div>
                        @else
                        <span class="text-[10px] font-bold text-slate-300">-</span>
                        @endif
                        @else
                        <span class="text-[10px] font-bold text-slate-300">-</span>
                        @endcan
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-5 py-10 text-center">
                        <i class="fas fa-inbox text-3xl text-slate-200"></i>
                        <p class="text-sm font-bold text-slate-400 mt-3">Belum ada SPP pada periode ini.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div id="modalTolak" class="hidden fixed inset-0 z-[70] bg-slate-900/50 backdrop-blur-sm p-4 flex items-end sm:items-center justify-center" role="dialog" aria-modal="true">
    <div class="w-full max-w-md bg-white rounded-t-2xl sm:rounded-2xl shadow-2xl overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between shrink-0 bg-white">
            <div class="min-w-0">
                <h3 class="text-base font-black text-slate-800">Tolak SPP <span id="tolakKode" class="text-rose-600"></span>?</h3>
                <p class="text-xs font-bold text-slate-400 mt-0.5">Alasan wajib diisi (minimal 5 karakter) dan akan disimpan.</p>
            </div>
            <button type="button" id="tolakTutup" class="ml-3 w-10 h-10 shrink-0 inline-flex items-center justify-center rounded-xl bg-slate-100 text-slate-500 hover:bg-rose-50 hover:text-rose-600"><i class="fas fa-xmark"></i></button>
        </div>
        <div class="p-5">
            <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Alasan Penolakan</label>
            <textarea id="tolakAlasan" rows="3" class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm rounded-xl focus:ring-2 focus:ring-rose-500 focus:border-rose-500 block p-3 outline-none transition-all font-medium resize-none">Ditolak oleh bendahara.</textarea>
            <p id="tolakError" class="hidden text-[11px] font-bold text-rose-600 mt-1.5"></p>
        </div>
        <div class="p-5 pt-0 flex gap-2 justify-end">
            <button type="button" id="tolakBatal" class="inline-flex items-center px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold text-xs rounded-xl">Batal</button>
            <button type="button" id="tolakKonfirmasi" class="inline-flex items-center px-4 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs rounded-xl"><i class="fas fa-circle-xmark mr-1.5"></i> Ya, Tolak SPP</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const KASIR = @json($kasir->all());
    const rupiah = (n) => 'Rp ' + Number(n || 0).toLocaleString('id-ID');
    const esc = (s) => String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    const Numb = (s) => parseInt(String(s).replace(/[^\d]/g, '') || '0', 10);

    let formTolak = null;
    const modalTolak = document.getElementById('modalTolak');
    const tolakKodeEl = document.getElementById('tolakKode');
    const tolakAlasanEl = document.getElementById('tolakAlasan');
    const tolakErrorEl = document.getElementById('tolakError');
    const tolakKonfirmasi = document.getElementById('tolakKonfirmasi');
    const tolakBatal = document.getElementById('tolakBatal');
    const tolakTutup = document.getElementById('tolakTutup');

    function bukaModalTolak(form, kode) {
        formTolak = form;
        tolakKodeEl.textContent = kode;
        tolakAlasanEl.value = 'Ditolak oleh bendahara.';
        tolakErrorEl.classList.add('hidden');
        modalTolak.classList.remove('hidden');
        tolakAlasanEl.focus();
        tolakAlasanEl.setSelectionRange(tolakAlasanEl.value.length, tolakAlasanEl.value.length);
    }

    function tutupModalTolak() {
        modalTolak.classList.add('hidden');
        formTolak = null;
    }

    window.confirmTolak = function (form, kode) {
        bukaModalTolak(form, kode);
        return false; // submit dilakukan dari tombol di modal
    };

    tolakTutup.addEventListener('click', tutupModalTolak);
    tolakBatal.addEventListener('click', tutupModalTolak);
    modalTolak.addEventListener('click', function (e) { if (e.target === modalTolak) tutupModalTolak(); });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && !modalTolak.classList.contains('hidden')) tutupModalTolak();
    });
    tolakKonfirmasi.addEventListener('click', function () {
        const alasan = tolakAlasanEl.value.trim();
        if (alasan.length < 5) {
            tolakErrorEl.textContent = 'Alasan penolakan minimal 5 karakter.';
            tolakErrorEl.classList.remove('hidden');
            tolakAlasanEl.focus();
            return;
        }
        if (formTolak) {
            formTolak.elements['tolak_alasan'].value = alasan;
            tutupModalTolak();
            formTolak.submit();
        }
    });

    const formEl = document.getElementById('formSpp');
    if (!formEl) return; // user tanpa akses_pencairan: hanya perlu confirmTolak

    const blkBulan = document.getElementById('blkBulan');
    const blkRutin = document.getElementById('blkRutin');
    const blkModal = document.getElementById('blkModal');
    const nominalModal = document.getElementById('nominalModal');
    const jenisInput = document.getElementById('jenisSpp');
    const bulanFiskalSelect = document.getElementById('bulan_fiskal');
    const kasirKelompok = document.getElementById('kasirKelompok');
    const kasirPos = document.getElementById('kasirPos');
    const kasirCari = document.getElementById('kasirCari');
    const kasirKeranjang = document.getElementById('kasirKeranjang');
    const kasirJumlah = document.getElementById('kasirJumlah');
    const totalSpp = document.getElementById('totalSpp');
    const kasirPesanEl = document.getElementById('kasirPesan');

    let kelompokAktif = (KASIR && KASIR.length) ? KASIR[0].kode : null;
    const cari = { kata: '' };
    let keranjang = []; // array {pos, nominal}

    const posInfo = {}; // id -> {kode, uraian, kelompokNama, alokasi, terpakai, kel}
    KASIR.forEach(k => k.pos.forEach(p => { posInfo[p.id] = Object.assign({}, p, { kelompokNama: k.nama, kel: k.kode }); }));

    function bulan() { return parseInt(bulanFiskalSelect.value || '0', 10); }
    function sisaPos(pos) {
        const b = bulan();
        if (!b) return null;
        const alokasi = (pos.alokasi && pos.alokasi[b]) || 0;
        const terpakai = (pos.terpakai && pos.terpakai[b]) || 0;
        return { alokasi, terpakai, sisa: alokasi - terpakai };
    }
    function kapPosSisa(pos) {
        const s = sisaPos(pos);
        if (!s) return 'Alokasi &middot; Terpakai &middot; Sisa bulan terpilih';
        return 'Alokasi ' + rupiah(s.alokasi) + ' &middot; Terpakai ' + rupiah(s.terpakai)
            + (s.sisa < 0 ? ' &middot; Sisa <b class="text-rose-500">-' + rupiah(Math.abs(s.sisa)) + '</b>' : ' &middot; Sisa ' + rupiah(s.sisa));
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
        const k = KASIR.find(x => x.kode === kelompokAktif);
        if (!k) { kasirPos.innerHTML = ''; return; }
        const kata = cari.kata.toLowerCase();
        const daftar = k.pos.filter(p => !kata || p.kode.includes(kata) || p.uraian.toLowerCase().includes(kata));
        if (!daftar.length) { kasirPos.innerHTML = '<div class="text-center text-xs font-bold text-slate-400 py-6">Tidak ada pos cocok.</div>'; return; }
        const pilihBulan = bulan() > 0;
        kasirPos.innerHTML = daftar.map(p => {
            const sudah = posIdDiKeranjang(p.id) >= 0;
            const s = sisaPos(p);
            const info = s ? kapPosSisa(p) : '<span class="text-slate-300">Alokasi &middot; Terpakai &middot; Sisa</span>';
            return '<div class="flex items-center gap-3 bg-white rounded-xl border border-slate-200 p-3' + (sudah ? ' border-emerald-300 ring-1 ring-emerald-200' : '') + '">' +
                '<div class="flex-1 min-w-0">' +
                '<p class="text-xs font-black text-slate-800">' + esc(p.kode) + ' &middot; ' + esc(p.uraian) + '</p>' +
                '<p class="text-[10px] font-bold text-slate-400 mt-0.5">' + info + '</p>' +
                '</div>' +
                '<button type="button" data-tambah="' + p.id + '" class="shrink-0 inline-flex items-center justify-center w-9 h-9 rounded-xl font-black text-lg ' +
                (pilihBulan ? 'bg-emerald-600 hover:bg-emerald-700 text-white cursor-pointer' : 'bg-slate-200 text-slate-400 cursor-not-allowed') +
                '">+</button></div>';
        }).join('');
    }

    function kartHtml(row) {
        const p = row.pos;
        const idx = keranjang.indexOf(row);
        return '<div class="flex items-center gap-3 bg-slate-50 rounded-xl border border-slate-200 p-3" data-posker="' + p.id + '">' +
            '<div class="flex-1 min-w-0">' +
            '<p class="text-xs font-black text-slate-800">' + esc(p.kode) + ' &middot; ' + esc(p.uraian) + '</p>' +
            '<p class="text-[10px] font-bold text-slate-400 mt-0.5">' + esc(p.kelompokNama) + ' &middot; <span class="sisaInfo"></span></p>' +
            '</div>' +
            '<div class="shrink-0 flex items-center gap-2">' +
            '<input type="hidden" name="items[' + idx + '][pos_id]" value="' + p.id + '">' +
            '<input type="text" name="items[' + idx + '][nominal]" class="nominalInput text-right w-36 rounded-xl border border-slate-300 px-3 py-2 text-sm font-black focus:ring-2 focus:ring-emerald-500 focus:outline-none" placeholder="0" value="' + esc(row.detik || '') + '">' +
            '<button type="button" class="hapusBaris w-8 h-8 inline-flex items-center justify-center rounded-lg bg-rose-50 text-rose-500 hover:bg-rose-100 font-black"><i class="fas fa-trash-can"></i></button>' +
            '</div></div>';
    }

    function renderKeranjang() {
        if (!keranjang.length) {
            kasirKeranjang.innerHTML = '<div class="text-center text-xs font-bold text-slate-400 py-10 border border-dashed border-slate-200 rounded-xl">Keranjang kosong.<br>Pilih kelompok & klik <b>+</b> pada pos yang diinginkan.</div>';
        } else {
            kasirKeranjang.innerHTML = keranjang.map(kartHtml).join('');
        }
        kasirJumlah.textContent = keranjang.length + ' item';
        totalSpp.textContent = rupiah(jumlahKeranjang());

        kasirKeranjang.querySelectorAll('[data-posker]').forEach((el) => {
            const id = parseInt(el.dataset.posker, 10);
            const row = keranjang.find(r => r.pos.id === id);
            if (row) row.elSisa = el.querySelector('.sisaInfo');
        });
        perbaruiSemuaSisa();
    }

    function sisaTextRow(row) {
        const s = sisaPos(row.pos);
        const nom = row.nominal;
        if (!s) {
            row.elSisa.textContent = '';
            row.elSisa.innerHTML = 'Pilih bulan untuk lihat sisa';
            return;
        }
        let html = 'Sisa ' + (s.sisa < 0 ? '-'+rupiah(Math.abs(s.sisa)) : rupiah(s.sisa));
        if (nom > 0 && nom > s.sisa) {
            html = '<b class="text-rose-600">Melebihi sisa!</b> ' + html + ' (alokasi ' + rupiah(s.alokasi) + ', terpakai ' + rupiah(s.terpakai) + ')';
        }
        row.elSisa.innerHTML = html;
    }

    function perbaruiSemuaSisa() {
        keranjang.forEach(r => sisaTextRow(r));
        renderPos();
    }

    function tambahPos(id) {
        const b = bulan();
        if (!b) {
            tampilkanPesan('Pilih bulan anggaran dulu sebelum menambah pos.');
            return;
        }
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
        keranjang.push({ pos: posInfo[id], nominal: 0, detik: '' });
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

    // Delegasi peristiwa
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
        if (!e.target.classList.contains('nominalInput')) return;
        const rowDiv = e.target.closest('[data-posker]');
        const id = parseInt(rowDiv.dataset.posker, 10);
        const row = keranjang.find(r => r.pos.id === id);
        row.detik = e.target.value;
        row.nominal = Numb(e.target.value);
        row.elSisa = rowDiv.querySelector('.sisaInfo');
        sisaTextRow(row);
        totalSpp.textContent = rupiah(jumlahKeranjang());
    });
    kasirKeranjang.addEventListener('click', (e) => {
        const btn = e.target.closest('.hapusBaris');
        if (!btn) return;
        const rowDiv = btn.closest('[data-posker]');
        hapusPos(parseInt(rowDiv.dataset.posker, 10));
    });
    kasirCari.addEventListener('input', (e) => { cari.kata = e.target.value; renderPos(); });
    bulanFiskalSelect.addEventListener('change', () => { perbaruiSemuaSisa(); });

    document.querySelectorAll('.jenisTab').forEach(btn => {
        btn.addEventListener('click', () => {
            const jenis = btn.dataset.jenis;
            jenisInput.value = jenis;
            document.querySelectorAll('.jenisTab').forEach(b => {
                if (b === btn) { b.classList.remove('bg-slate-100', 'text-slate-500'); b.classList.add('bg-emerald-600', 'text-white'); }
                else { b.classList.remove('bg-emerald-600', 'text-white'); b.classList.add('bg-slate-100', 'text-slate-500'); }
            });
            blkRutin.classList.toggle('hidden', jenis !== 'rutin');
            blkModal.classList.toggle('hidden', jenis !== 'modal_toko');
            blkBulan.classList.toggle('opacity-40', jenis !== 'rutin');
            blkBulan.querySelector('select').disabled = jenis !== 'rutin';
            if (jenis === 'rutin') nominalModal.value = '';
        });
    });

    formEl.addEventListener('submit', (e) => {
        if (jenisInput.value === 'modal_toko') return;
        if (!bulan()) { e.preventDefault(); tampilkanPesan('Pilih bulan anggaran untuk SPP rutin.'); return; }
        if (!keranjang.length) { e.preventDefault(); tampilkanPesan('Keranjang masih kosong — tambahkan minimal satu pos.'); return; }
        if (keranjang.some(r => r.nominal <= 0)) {
            e.preventDefault(); tampilkanPesan('Setiap pos di keranjang wajib diisi nominal lebih dari 0.'); return;
        }
    });

    renderKelompok();
    renderPos();
    renderKeranjang();
    tampilkanPesan('');
})();
</script>
@endpush