@extends('layouts.app')

@section('title', 'Realisasi ' . $pencairan->kode)

@section('content')
<div class="mb-6">
    <a href="{{ route('kebendaharaan.laporan.index') }}" class="text-xs font-black text-slate-500 hover:text-emerald-600 mb-3 inline-flex items-center">
        <i class="fas fa-arrow-left mr-2"></i> Kembali ke Pelaporan
    </a>
    <h2 class="text-2xl font-black text-slate-800 tracking-tight uppercase">Form Realisasi Anggaran</h2>
    <p class="text-sm font-bold text-slate-400 mt-1">Masukkan nominal realisasi per rincian untuk <span class="text-emerald-600">{{ $pencairan->kode }}</span>. Simpan &rarr; langsung menjadi realisasi anggaran.</p>
</div>

@if(session('error'))
<div class="mb-4 flex items-center gap-3 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl px-4 py-3 text-sm font-bold">
    <i class="fas fa-triangle-exclamation"></i> {{ session('error') }}
</div>
@endif

@if($laporan)
<div class="mb-4 flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl px-4 py-3 text-sm font-bold">
    <i class="fas fa-circle-check"></i> Sudah ada realisasi <span class="bg-white border border-emerald-200 rounded px-1.5 py-0.5">{{ $laporan->kode }}</span>
    ({{ $laporan->tanggal ? $laporan->tanggal->format('d M Y') : '—' }}). Simpan kembali untuk memperbarui nilainya.
</div>
@endif

<div class="max-w-4xl">
    <form action="{{ route('kebendaharaan.laporan.realisasi.store', $pencairan->id) }}" method="POST" class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        @csrf

        <div class="p-5 pb-0 grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Kode Kegiatan</label>
                <input type="text" value="{{ $pencairan->kode }}" readonly disabled class="w-full bg-[#F0F0F0] border border-slate-200 text-slate-700 text-sm rounded-xl px-3 py-2.5 font-black outline-none">
            </div>
            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Nominal Pencairan</label>
                <input type="text" value="Rp {{ number_format($pencairan->jumlah, 0, ',', '.') }}" readonly disabled class="w-full bg-[#F0F0F0] border border-slate-200 text-slate-700 text-sm rounded-xl px-3 py-2.5 font-black outline-none">
            </div>
            <div class="sm:col-span-2">
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Kegiatan</label>
                <textarea rows="2" readonly disabled class="w-full bg-[#F0F0F0] border border-slate-200 text-slate-700 text-sm rounded-xl px-3 py-2.5 font-semibold outline-none resize-none">{{ $pencairan->keperluan }}</textarea>
            </div>
        </div>

        <div class="p-5">
            <h3 class="text-sm font-black text-slate-700 uppercase tracking-widest mb-3"><i class="fas fa-list-alt text-emerald-500 mr-2"></i>Rincian</h3>

            <div class="hidden sm:block">
                <div class="overflow-x-auto rounded-xl border border-slate-200">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-[#F2F2F2] text-[10px] font-black uppercase tracking-widest text-slate-500 border-b border-slate-200">
                                <th class="px-4 py-3 text-left">Rincian Anggaran</th>
                                <th class="px-4 py-3 text-right w-44">Nilai Pencairan</th>
                                <th class="px-4 py-3 text-right w-44">Realisasi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($items as $it)
                            <tr class="border-b border-slate-100 align-top">
                                <td class="px-4 py-3">
                                    <div class="bg-slate-50 border border-slate-200 text-slate-700 text-xs font-semibold rounded-lg px-3 py-2 min-h-[40px] flex flex-col justify-center">
                                        <span class="text-[10px] font-black text-emerald-600">{{ $it->kode }}</span>
                                        {{ $it->uraian }}
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="bg-slate-50 border border-slate-200 text-slate-600 text-right text-xs font-black rounded-lg px-3 py-2 min-h-[40px] flex flex-col justify-center">
                                        Rp {{ number_format($it->nominal, 0, ',', '.') }}
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <input type="text" name="realisasi[{{ $it->id }}]" data-rupiah data-item-id="{{ $it->id }}" value="{{ old('realisasi.' . $it->id, $it->realisasi > 0 ? number_format($it->realisasi, 0, ',', '.') : '') }}"
                                        placeholder="0" inputmode="numeric"
                                        class="w-full bg-white border border-slate-300 text-slate-800 text-right text-sm rounded-lg px-3 py-2 font-black focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-all">
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="sm:hidden space-y-3">
                @foreach($items as $it)
                <div class="rounded-xl border border-slate-200 p-3 space-y-2">
                    <p class="text-xs font-semibold text-slate-700">
                        <span class="text-[10px] font-black text-emerald-600 bg-emerald-50 border border-emerald-100 rounded px-1.5 py-0.5 mr-1">{{ $it->kode }}</span>{{ $it->uraian }}
                    </p>
                    <div class="grid grid-cols-2 gap-2 items-center">
                        <input type="text" value="Rp {{ number_format($it->nominal, 0, ',', '.') }}" readonly disabled class="w-full bg-[#F0F0F0] border border-slate-200 text-slate-500 text-xs rounded-lg px-3 py-2 font-bold outline-none">
                        <input type="text" data-rupiah data-item-id="{{ $it->id }}" value="{{ old('realisasi.' . $it->id, $it->realisasi > 0 ? number_format($it->realisasi, 0, ',', '.') : '') }}"
                            placeholder="Realisasi 0" inputmode="numeric"
                            class="w-full bg-white border border-slate-300 text-slate-800 text-right text-sm rounded-lg px-3 py-2 font-black focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-all">
                    </div>
                </div>
                @endforeach
            </div>

            <div class="mt-4 flex items-center justify-end gap-3">
                <label class="text-[11px] font-black text-slate-500 uppercase tracking-wider mr-auto py-2">Realisasi diisi dalam Rupiah (kosongkan = 0)</label>
                <div class="flex items-center gap-3">
                    <span class="text-xs font-black text-slate-600 uppercase tracking-wider">Total Realisasi</span>
                    <input type="text" id="totalRealisasi" value="0" readonly class="w-48 bg-[#F0F0F0] border border-slate-200 text-slate-800 text-right text-base rounded-xl px-3 py-2.5 font-black outline-none">
                </div>
            </div>
        </div>

        <div class="px-5 pb-5 border-t border-slate-100 pt-4 bg-slate-50/50">
            <p id="totalHint" class="hidden mb-3 flex items-center gap-2 bg-rose-50 border border-rose-200 text-rose-700 text-xs font-bold rounded-xl px-4 py-2.5">
                <i class="fas fa-triangle-exclamation"></i> Isi minimal satu nilai Realisasi lebih dari 0 di bagian Rincian untuk bisa menyimpan (isi 0 / kosongkan = baris tidak direalisasikan).
            </p>
            <div class="flex flex-wrap items-center gap-3 justify-between">
                @if($laporan)
                <button type="button" data-reset class="inline-flex items-center px-4 py-3 bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 font-bold text-xs rounded-xl transition-all">
                    <i class="fas fa-eraser mr-2"></i> Kosongkan Realisasi (hapus {{ $laporan->kode }})
                </button>
                @else
                <span></span>
                @endif
                <div class="flex items-center gap-3">
                    <a href="{{ route('kebendaharaan.laporan.index') }}" class="inline-flex items-center px-5 py-3 bg-slate-200 hover:bg-slate-300 text-slate-600 font-bold text-sm rounded-xl transition-all">
                        <i class="fas fa-times mr-2"></i> Cancel
                    </a>
                    <button type="submit" id="btnSimpan" disabled class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 active:scale-95 text-white font-bold text-sm rounded-xl transition-all shadow-[0_4px_15px_-3px_rgba(37,99,235,0.4)] opacity-40 cursor-not-allowed">
                        <i class="fas fa-save mr-2"></i> Simpan
                    </button>
                </div>
            </div>
        </div>
    </form>

    @if($laporan)
    <form id="formReset" action="{{ route('kebendaharaan.laporan.realisasi.store', $pencairan->id) }}" method="POST">
        @csrf
        @foreach($items as $it)
        <input type="hidden" name="realisasi[{{ $it->id }}]" value="">
        @endforeach
    </form>
    @endif
</div>
@endsection

@push('scripts')
<script>
(function () {
    var inputs = Array.prototype.slice.call(document.querySelectorAll('[data-rupiah]'));
    var totalEl = document.getElementById('totalRealisasi');
    var btn = document.getElementById('btnSimpan');
    var hint = document.getElementById('totalHint');

    function Numb(s) {
        return parseInt(String(s || '').replace(/[^\d]/g, '') || '0', 10);
    }
    function fmt(n) {
        return new Intl.NumberFormat('id-ID').format(n);
    }
    function named() {
        return inputs.filter(function (i) { return i.name && i.name.indexOf('realisasi[') === 0; });
    }
    function total() {
        return named().reduce(function (acc, inp) { return acc + Numb(inp.value); }, 0);
    }
    function syncItem(inp) {
        var id = inp.dataset.itemId;
        inputs.forEach(function (i) {
            if (i !== inp && i.dataset.itemId === id) i.value = inp.value;
        });
    }
    function tambah(inp) {
        inp.addEventListener('input', function () {
            var raw = inp.value.replace(/[^\d]/g, '');
            inp.value = raw ? fmt(parseInt(raw, 10)) : '';
            syncItem(inp);
            perbarui();
        });
        inp.addEventListener('blur', function () {
            var v = Numb(inp.value);
            inp.value = v ? fmt(v) : '';
            syncItem(inp);
        });
    }
    function perbarui() {
        var t = total();
        totalEl.value = fmt(t);
        if (btn) {
            var zero = t === 0;
            btn.disabled = zero;
            btn.classList.toggle('opacity-40', zero);
            btn.classList.toggle('cursor-not-allowed', zero);
            btn.classList.toggle('hover:bg-blue-700', !zero);
        }
        if (hint) hint.classList.toggle('hidden', t > 0);
    }

    inputs.forEach(tambah);

    if (btn && btn.form) {
        btn.form.addEventListener('submit', function (e) {
            if (total() === 0) {
                e.preventDefault();
                if (hint) hint.classList.remove('hidden');
                var first = named().find(function (i) { return Numb(i.value) === 0; });
                if (first) first.focus();
            }
        });
    }

    var resetBtn = document.querySelector('[data-reset]');
    if (resetBtn) {
        resetBtn.addEventListener('click', function () {
            if (confirm('Kosongkan realisasi ini? Rekaman ' + (this.textContent.match(/\S+-\S+/g) || ['LPJ'])[0] + ' beserta rinciannya akan dihapus.')) {
                document.getElementById('formReset').submit();
            }
        });
    }

    perbarui();
})();
</script>
@endpush