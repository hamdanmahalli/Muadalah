@extends('layouts.app')

@section('title', 'Pencairan (SPP)')

@section('content')
@php
    $labelStatus = ['diajukan' => 'Diajukan', 'disetujui' => 'Disetujui', 'dibayar' => 'Dibayar', 'ditolak' => 'Ditolak'];
    $bgStatus = ['diajukan' => 'bg-amber-100 text-amber-700 border-amber-200', 'disetujui' => 'bg-sky-100 text-sky-700 border-sky-200', 'dibayar' => 'bg-emerald-100 text-emerald-700 border-emerald-200', 'ditolak' => 'bg-rose-100 text-rose-700 border-rose-200'];
    $ikonStatus = ['diajukan' => 'fa-clock', 'disetujui' => 'fa-pen-to-square', 'dibayar' => 'fa-circle-check', 'ditolak' => 'fa-circle-xmark'];
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
            Surat Permintaan Pembayaran: 1 SPP = 1 bulan anggaran, boleh memuat banyak pos. Diajukan &rarr; disetujui oleh bendahara &rarr; dibayar ke pengaju.
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
        <div id="itemsDinamis"></div>

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

            <!-- RUTIN: lembar SPP ala excel -->
            <div id="blkRutin" class="mt-4">
                <div class="bg-white rounded-2xl border border-slate-200">
                    <div class="p-4 border-b border-slate-100 rounded-t-2xl flex items-center justify-between gap-3">
                        <h4 class="text-sm font-black text-slate-700 uppercase tracking-widest"><i class="fas fa-table-list text-emerald-500 mr-2"></i>Lembar SPP</h4>
                        <span id="sheetJumlah" class="bg-emerald-100 text-emerald-700 text-[10px] font-black px-2 py-1 rounded-md">0 baris</span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm min-w-[780px]">
                            <thead>
                                <tr class="text-left text-[10px] font-black uppercase tracking-widest text-slate-400 border-b border-slate-100 bg-slate-50">
                                    <th class="px-5 py-3 w-10">No</th>
                                    <th class="px-3 py-3">Kode</th>
                                    <th class="px-3 py-3">Uraian Pos</th>
                                    <th class="px-3 py-3 text-right">Alokasi</th>
                                    <th class="px-3 py-3 text-right">Terpakai</th>
                                    <th class="px-3 py-3 text-right">Sisa</th>
                                    <th class="px-3 py-3 text-right">Nominal (Rp)</th>
                                    <th class="px-3 py-3"></th>
                                </tr>
                            </thead>
                            <tbody id="sheetBody">
                                <tr>
                                    <td colspan="8" class="px-5 py-10 text-center text-xs font-bold text-slate-400">
                                        <i class="fas fa-table-list text-2xl text-slate-200"></i>
                                        <p class="mt-2">Lembar masih kosong.<br>Klik <b>+ Tambah Baris Pos</b> di bawah.</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="relative px-4 py-3 border-t border-slate-100 flex flex-wrap items-center justify-between gap-3">
                        <button type="button" id="tombolTambah" class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white font-bold text-xs rounded-xl transition-all">
                            <i class="fas fa-plus"></i> Tambah Baris Pos
                        </button>
                        <div class="flex items-center justify-between bg-emerald-50 border border-emerald-200 rounded-xl px-4 py-2.5 min-w-[240px] gap-4">
                            <span class="text-xs font-black text-emerald-800 uppercase tracking-wider">Total SPP</span>
                            <span id="totalSpp" class="text-base font-black text-emerald-800 whitespace-nowrap">Rp 0</span>
                        </div>

                        <div id="popPos" class="hidden absolute right-0 bottom-full mb-2 w-[440px] max-w-[calc(100vw-2rem)] z-50 bg-white border border-slate-200 rounded-2xl shadow-2xl overflow-hidden">
                            <div class="p-3 border-b border-slate-100">
                                <input type="text" id="popCari" placeholder="Cari kode / uraian pos…" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm font-semibold focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                            </div>
                            <div id="popDaftar" class="p-3 space-y-3 max-h-[320px] overflow-y-auto"></div>
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
                <span class="text-[10px] font-bold text-slate-400">Melebihi alokasi periode diperbolehkan, tetapi ditandai peringatan.</span>
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
                    <th class="px-3 py-3">Keperluan</th>
                    <th class="px-3 py-3">Bulan</th>
                    <th class="px-3 py-3 text-right">Total</th>
                    <th class="px-3 py-3">Status</th>
                    <th class="px-3 py-3">Detail</th>
                    <th class="px-5 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($list as $pc)
                <tr class="border-b border-slate-50 hover:bg-slate-50/50">
                    <td class="px-5 py-4">
                        <p class="font-black text-slate-800 text-xs">{{ $pc->kode }}</p>
                        <p class="text-[11px] font-bold text-slate-400 mt-0.5">{{ $pc->tanggal_aju->format('d M Y') }}</p>
                        <p class="text-[10px] font-black {{ $pc->jenis === 'modal_toko' ? 'text-indigo-500' : 'text-emerald-600' }} mt-0.5 uppercase">{{ $pc->jenis === 'modal_toko' ? 'Modal Toko' : 'Rutin' }}</p>
                    </td>
                    <td class="px-3 py-4 max-w-sm">
                        <p class="font-bold text-slate-700 text-xs truncate">{{ $pc->keperluan }}</p>
                        @if($pc->jenis === 'rutin')
                        <p class="text-[10px] font-semibold text-slate-400 mt-0.5">{{ $pc->items->count() }} pos &middot; Rp {{ number_format($pc->jumlah, 0, ',', '.') }}</p>
                        @else
                        <p class="text-[10px] font-semibold text-slate-400 mt-0.5">Modal toko &mdash; menjadi pinjaman saat dibayar.</p>
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
                    <td class="px-3 py-4">
                        <a href="{{ route('kebendaharaan.pencairan.show', $pc->id) }}" class="inline-flex items-center px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold text-[10px] rounded-lg">
                            <i class="fas fa-eye mr-1.5"></i> Rincian
                        </a>
                    </td>
                    <td class="px-5 py-4">
                        @can('akses_validasi_pencairan')
                        @if($pc->status === 'diajukan')
                        <div class="flex items-center justify-end gap-2">
                            <form action="{{ route('kebendaharaan.pencairan.setujui', $pc->id) }}" method="POST" onsubmit="return confirm('Setujui SPP {{ $pc->kode }}? Dana akan siap dibayarkan ke pengaju.')">
                                @csrf
                                <button class="inline-flex items-center px-3 py-1.5 bg-sky-600 hover:bg-sky-700 text-white font-bold text-[10px] rounded-lg">
                                    <i class="fas fa-pen-to-square mr-1.5"></i> Setujui
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
                        @elseif($pc->status === 'disetujui')
                        <div class="flex items-center justify-end gap-2">
                            <form action="{{ route('kebendaharaan.pencairan.bayar', $pc->id) }}" method="POST" onsubmit="return confirm('Bayar SPP {{ $pc->kode }}? Uang masuk ke tangan {{ $pc->pengaju?->name ?? 'pengaju' }} dan keluar kas.')">
                                @csrf
                                <button class="inline-flex items-center px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[10px] rounded-lg">
                                    <i class="fas fa-circle-check mr-1.5"></i> Bayar
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
                    <td colspan="7" class="px-5 py-10 text-center">
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
            const form = formTolak;
            form.elements['tolak_alasan'].value = alasan;
            form.submit();
            tutupModalTolak();
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

    const sheetBody = document.getElementById('sheetBody');
    const sheetJumlah = document.getElementById('sheetJumlah');
    const totalSpp = document.getElementById('totalSpp');
    const tombolTambah = document.getElementById('tombolTambah');
    const popPos = document.getElementById('popPos');
    const popCari = document.getElementById('popCari');
    const popDaftar = document.getElementById('popDaftar');
    const itemsDinamis = document.getElementById('itemsDinamis');
    const kasirPesanEl = document.getElementById('kasirPesan');

    const posInfo = {}; // id -> {kode, uraian, kelKode, kelNama, alokasi, terpakai}
    KASIR.forEach(k => k.pos.forEach(p => { posInfo[p.id] = Object.assign({}, p, { kelKode: k.kode, kelNama: k.nama }); }));

    let rows = []; // {pos, nominal, detik}
    let popTampil = false;

    function bulan() { return parseInt(bulanFiskalSelect.value || '0', 10); }
    function angka(n) { return Number(n || 0).toLocaleString('id-ID'); }
    function fmtRp(n) { return 'Rp ' + angka(n); }
    // Alokasi & terpakai TIDAK mengikuti bulan: total selama periode.
    function sisaPos(pos) {
        const total = (m) => Object.entries(m || {}).reduce((a, [, v]) => a + (Number(v) || 0), 0);
        return { alokasi: total(pos.alokasi), terpakai: total(pos.terpakai) };
    }
    function totalNominal() { return rows.reduce((a, r) => a + (r.nominal || 0), 0); }
    function barisDari(input) {
        const id = parseInt(input.dataset.nim, 10);
        return rows.find(r => r.pos.id === id);
    }

    function tampilkanPesan(teks) {
        if (!teks) { kasirPesanEl.classList.add('hidden'); return; }
        kasirPesanEl.classList.remove('hidden');
        kasirPesanEl.querySelector('span').textContent = teks;
    }

    // ==== Pemilih pos (popover) ====
    function renderPop() {
        const kata = popCari.value.trim().toLowerCase();
        const ada = new Set(rows.map(r => r.pos.id));
        const sections = KASIR.map(k => {
            const pos = k.pos.filter(p => !ada.has(p.id) && (!kata || p.kode.toLowerCase().includes(kata) || p.uraian.toLowerCase().includes(kata)));
            if (!pos.length) return '';
            const itemsHtml = pos.map(p => {
                const s = sisaPos(p);
                const sisaBersih = s.alokasi - s.terpakai;
                const info = 'Alokasi ' + angka(s.alokasi) + ' &middot; Terpakai ' + angka(s.terpakai)
                    + ' &middot; Sisa <b class="' + (sisaBersih < 0 ? 'text-rose-500' : 'text-emerald-600') + '">' + angka(sisaBersih) + '</b>';
                return '<div class="flex items-center gap-3 bg-white rounded-xl border border-slate-200 p-3 hover:border-emerald-300">' +
                    '<div class="flex-1 min-w-0">' +
                    '<p class="text-xs font-black text-slate-800">' + esc(p.kode) + ' &middot; ' + esc(p.uraian) + '</p>' +
                    '<p class="text-[10px] font-bold text-slate-400 mt-0.5">' + info + '</p>' +
                    '</div>' +
                    '<button type="button" data-add="' + p.id + '" class="shrink-0 inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[11px]"><i class="fas fa-plus"></i> Pilih</button></div>';
            }).join('');
            return '<div><p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2">' + esc(k.kode) + ' &middot; ' + esc(k.nama) + '</p><div class="space-y-2">' + itemsHtml + '</div></div>';
        }).join('');
        popDaftar.innerHTML = sections || '<p class="text-center text-xs font-bold text-slate-400 py-6">Tidak ada pos cocok.</p>';
    }

    function bukaPop() {
        if (!KASIR || !KASIR.length) {
            tampilkanPesan('Belum ada anggaran Final pada periode aktif. Finalkan RAB lebih dulu pada menu Anggaran.');
            return;
        }
        popTampil = true;
        popPos.classList.remove('hidden');
        popCari.value = '';
        renderPop();
        popCari.focus();
    }
    function tutupPop() { popTampil = false; popPos.classList.add('hidden'); }

    tombolTambah.addEventListener('click', (e) => {
        e.stopPropagation();
        if (popTampil) { tutupPop(); return; }
        bukaPop();
    });
    document.addEventListener('click', (e) => {
        if (!popTampil) return;
        if (popPos.contains(e.target) || tombolTambah.contains(e.target)) return;
        tutupPop();
    });
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && popTampil) tutupPop();
    });
    popCari.addEventListener('input', renderPop);
    popDaftar.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-add]');
        if (btn) tambahBaris(parseInt(btn.dataset.add, 10));
    });

    // ==== Lembar SPP ala excel ====
    function tambahBaris(id) {
        const b = bulan();
        if (!b) { tampilkanPesan('Pilih bulan anggaran dulu sebelum menambah pos.'); return; }
        const sudah = rows.findIndex(r => r.pos.id === id);
        if (sudah >= 0) {
            tampilkanPesan('Pos sudah ada di lembar.');
            const tr = sheetBody.querySelector('tr[data-bar="' + id + '"]');
            if (tr) {
                tr.scrollIntoView({ behavior: 'smooth', block: 'center' });
                tr.style.boxShadow = '0 0 0 3px rgba(245,158,11,0.45)';
                setTimeout(() => { tr.style.boxShadow = ''; }, 1400);
            }
            return;
        }
        rows.push({ pos: posInfo[id], nominal: 0, detik: '' });
        renderSheet();
        tampilkanPesan('');
        focusNominal(rows.length - 1);
    }

    function hapusBaris(id) {
        rows = rows.filter(r => r.pos.id !== id);
        renderSheet();
    }

    function renderSheet() {
        if (!rows.length) {
            sheetBody.innerHTML = '<tr><td colspan="8" class="px-5 py-10 text-center text-xs font-bold text-slate-400">' +
                '<i class="fas fa-table-list text-2xl text-slate-200"></i><p class="mt-2">Lembar masih kosong.<br>Klik <b>+ Tambah Baris Pos</b> di bawah.</p></td></tr>';
            sheetJumlah.textContent = '0 baris';
            totalSpp.textContent = 'Rp 0';
            return;
        }

        const grup = {};
        rows.forEach(r => {
            if (!grup[r.pos.kelKode]) grup[r.pos.kelKode] = { kode: r.pos.kelKode, nama: r.pos.kelNama, rows: [] };
            grup[r.pos.kelKode].rows.push(r);
        });
        const urutan = KASIR.map(k => k.kode).filter(k => grup[k]);

        let no = 0;
        const html = [];
        urutan.forEach(k => {
            const g = grup[k];
            html.push('<tr class="bg-slate-100"><td colspan="8" class="px-5 py-2 text-[10px] font-black uppercase tracking-widest text-slate-500">' + esc(g.kode) + ' &middot; ' + esc(g.nama) + '</td></tr>');
            g.rows.forEach(r => {
                no++;
                const s = sisaPos(r.pos);
                const alokasi = s.alokasi;
                const terpakai = s.terpakai;
                const sisaBersih = alokasi - terpakai;
                const lebih = r.nominal > sisaBersih;
                html.push('<tr data-bar="' + r.pos.id + '" class="border-b border-slate-50 hover:bg-slate-50/50' + (lebih ? ' bg-rose-50/70' : '') + '">' +
                    '<td class="px-5 py-3 text-xs font-bold text-slate-400">' + no + '</td>' +
                    '<td class="px-3 py-3 text-xs font-black text-slate-700 whitespace-nowrap">' + esc(r.pos.kode) + '</td>' +
                    '<td class="px-3 py-3 text-xs font-semibold text-slate-600 max-w-xs">' + esc(r.pos.uraian) + ' <span class="text-[10px] font-bold text-slate-400">' + esc(r.pos.kelNama) + '</span></td>' +
                    '<td class="px-3 py-3 text-right text-xs font-bold text-slate-500 whitespace-nowrap">' + angka(alokasi) + '</td>' +
                    '<td class="px-3 py-3 text-right text-xs font-bold text-slate-500 whitespace-nowrap">' + angka(terpakai) + '</td>' +
                    '<td class="px-3 py-3 text-right text-xs font-black whitespace-nowrap ' + (lebih ? 'text-rose-600' : 'text-emerald-600') + '">' + angka(sisaBersih) + '</td>' +
                    '<td class="px-3 py-3"><input type="text" inputmode="numeric" data-nim="' + r.pos.id + '" value="' + esc(r.detik) + '" class="w-full text-right rounded-lg border border-slate-300 px-3 py-2 text-sm font-black focus:ring-2 focus:ring-emerald-500 focus:outline-none" placeholder="0"></td>' +
                    '<td class="px-3 py-3"><button type="button" data-hapus="' + r.pos.id + '" class="w-8 h-8 inline-flex items-center justify-center rounded-lg bg-rose-50 text-rose-500 hover:bg-rose-100 font-black"><i class="fas fa-trash-can"></i></button></td></tr>');
            });
        });
        sheetBody.innerHTML = html.join('');
        sheetJumlah.textContent = rows.length + ' baris';
        totalSpp.textContent = fmtRp(totalNominal());
    }

    function focusNominal(idx) {
        const inputs = Array.from(sheetBody.querySelectorAll('input[data-nim]'));
        if (inputs[idx]) { inputs[idx].focus(); inputs[idx].select(); }
    }

    function perbaruiBaris(row, tr) {
        const s = sisaPos(row.pos);
        const sisaBersih = s.alokasi - s.terpakai;
        const lebih = row.nominal > sisaBersih;
        const selSisa = tr.children[5];
        selSisa.className = 'px-3 py-3 text-right text-xs font-black whitespace-nowrap ' + (lebih ? 'text-rose-600' : 'text-emerald-600');
        selSisa.innerHTML = angka(sisaBersih);
        tr.classList.toggle('bg-rose-50/70', lebih);
    }

    sheetBody.addEventListener('input', (e) => {
        const inp = e.target.closest('input[data-nim]');
        if (!inp) return;
        const row = barisDari(inp);
        if (!row) return;
        const n = Numb(inp.value);
        row.nominal = n;
        row.detik = n > 0 ? angka(n) : '';
        inp.value = row.detik;
        perbaruiBaris(row, inp.closest('tr'));
        totalSpp.textContent = fmtRp(totalNominal());
    });

    sheetBody.addEventListener('focusout', (e) => {
        const inp = e.target.closest('input[data-nim]');
        if (!inp) return;
        const row = barisDari(inp);
        if (row) inp.value = row.detik;
    });

    sheetBody.addEventListener('keydown', (e) => {
        const inp = e.target.closest('input[data-nim]');
        if (!inp) return;
        const inputs = Array.from(sheetBody.querySelectorAll('input[data-nim]'));
        const i = inputs.indexOf(inp);
        if (e.key === 'Enter' || e.key === 'ArrowDown') { e.preventDefault(); focusNominal(i + 1); }
        else if (e.key === 'ArrowUp') { e.preventDefault(); focusNominal(i - 1); }
    });

    sheetBody.addEventListener('paste', (e) => {
        const inp = e.target.closest('input[data-nim]');
        if (!inp) return;
        const teks = (e.clipboardData || window.clipboardData).getData('text');
        const bagian = teks.split(/\r?\n/).map(l => l.trim()).filter(Boolean);
        const banyak = bagian.length > 1 || bagian.some(l => l.includes('\t'));
        if (!banyak) return;
        e.preventDefault();
        const inputs = Array.from(sheetBody.querySelectorAll('input[data-nim]'));
        const mulai = inputs.indexOf(inp);
        let i = mulai;
        for (const b of bagian) {
            if (i >= inputs.length) break;
            const row = barisDari(inputs[i]);
            const n = Numb(b.split('\t')[0]);
            if (row) { row.nominal = n; row.detik = n > 0 ? angka(n) : ''; }
            i++;
        }
        renderSheet();
        focusNominal(mulai);
    });

    sheetBody.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-hapus]');
        if (btn) hapusBaris(parseInt(btn.dataset.hapus, 10));
    });
    bulanFiskalSelect.addEventListener('change', () => { renderSheet(); renderPop(); });

    // Tab jenis
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

    // Submit: kirim hanya baris ber-nominal > 0
    formEl.addEventListener('submit', (e) => {
        if (jenisInput.value === 'modal_toko') return;
        if (!bulan()) { e.preventDefault(); tampilkanPesan('Pilih bulan anggaran untuk SPP rutin.'); return; }
        if (!rows.length) { e.preventDefault(); tampilkanPesan('Lembar masih kosong — tambahkan minimal satu pos.'); return; }
        itemsDinamis.innerHTML = '';
        const inputs = Array.from(sheetBody.querySelectorAll('input[data-nim]'));
        let i = 0;
        for (const inp of inputs) {
            const row = barisDari(inp);
            if (!row) continue;
            const n = Numb(inp.value);
            row.nominal = n;
            row.detik = n > 0 ? angka(n) : '';
            if (n <= 0) {
                e.preventDefault();
                tampilkanPesan('Setiap baris di lembar wajib diisi nominal lebih dari 0.');
                itemsDinamis.innerHTML = '';
                return;
            }
            itemsDinamis.insertAdjacentHTML('beforeend',
                '<input type="hidden" name="items[' + i + '][pos_id]" value="' + row.pos.id + '">' +
                '<input type="hidden" name="items[' + i + '][nominal]" value="' + n + '">');
            i++;
        }
    });

    renderPop();
    renderSheet();
    tampilkanPesan('');
})();
</script>
@endpush