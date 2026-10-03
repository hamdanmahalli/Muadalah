@extends('layouts.app')

@section('title', 'Validasi Laporan')

@section('content')
<div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
    <div>
        <h2 class="text-2xl font-black text-slate-800 tracking-tight flex items-center">
            <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center text-amber-600 mr-3 shadow-inner">
                <i class="fas fa-file-signature"></i>
            </div>
            Validasi Laporan
        </h2>
        <p class="text-sm font-bold text-slate-400 mt-1 ml-14">
            Laporan pengeluaran SPP dari para pengaju &middot; {{ $periode->tahun_ajaran }} &middot; {{ ucfirst($periode->semester) }}.
        </p>
    </div>
    <a href="{{ route('kebendaharaan.laporan.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-900 text-white font-bold text-xs transition-all shadow-sm">
        <i class="fas fa-right-left mr-1"></i> Transaksi
    </a>
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

@if($bisaBendahara)
<div class="mb-8">
    <h3 class="text-sm font-black text-slate-700 mb-3 flex items-center">
        <i class="fas fa-file-signature text-amber-500 mr-2"></i> Menunggu Validasi Bendahara
        <span class="ml-2 px-2 py-0.5 rounded-full bg-amber-100 text-amber-700 text-[10px] font-black">{{ $bukuMenungguBendahara->count() }}</span>
    </h3>
    @if($bukuMenungguBendahara->isEmpty())
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 px-4 py-8 text-center">
        <p class="text-xs font-bold text-slate-400">Tidak ada laporan SPP yang menunggu validasi bendahara.</p>
    </div>
    @else
    <div class="space-y-3">
        @foreach($bukuMenungguBendahara as $r)
        @include('admin.kebendaharaan._laporan-kartu', ['r' => $r])
        @endforeach
    </div>
    @endif
</div>
@endif

@if($bisaPimpinan)
<div>
    <h3 class="text-sm font-black text-slate-700 mb-3 flex items-center">
        <i class="fas fa-stamp text-sky-500 mr-2"></i> Menunggu Pengesahan Pimpinan
        <span class="ml-2 px-2 py-0.5 rounded-full bg-sky-100 text-sky-700 text-[10px] font-black">{{ $bukuMenungguPengesahan->count() }}</span>
    </h3>
    @if($bukuMenungguPengesahan->isEmpty())
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 px-4 py-8 text-center">
        <p class="text-xs font-bold text-slate-400">Tidak ada laporan yang menunggu pengesahan pimpinan.</p>
    </div>
    @else
    <div class="space-y-3">
        @foreach($bukuMenungguPengesahan as $r)
        @include('admin.kebendaharaan._laporan-kartu', ['r' => $r])
        @endforeach
    </div>
    @endif
</div>
@endif
@endsection

{{-- Modal alasan pengembalian laporan --}}
<div id="kembaliModal" class="hidden fixed inset-0 z-50 bg-slate-900/70 backdrop-blur-sm items-center justify-center p-4">
    <div class="relative max-w-md w-full bg-white rounded-2xl shadow-2xl p-6">
        <h4 class="text-sm font-black text-slate-800 mb-1"><i class="fas fa-rotate-left text-rose-500 mr-2"></i>Kembalikan Laporan</h4>
        <p class="text-[11px] font-semibold text-slate-400 mb-4">Tulis alasan pengembalian laporan <span id="kembaliLabel" class="text-slate-600"></span> ke pengaju. Pengaju bisa memperbaiki lalu melaporkan ulang.</p>
        <form id="kembaliForm" method="POST">
            @csrf
            <textarea name="alasan" id="kembaliAlasan" rows="4" required placeholder="Contoh: foto/nota bukti belanja kurang jelas, uraian tidak lengkap, dsb." class="w-full bg-slate-50 border border-slate-200 text-slate-700 text-sm rounded-xl focus:ring-2 focus:ring-rose-500 focus:border-rose-500 block p-3 outline-none transition-all font-medium resize-none"></textarea>
            <div class="flex items-center justify-end gap-2 mt-4">
                <button type="button" id="kembaliBatal" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold text-xs transition-all">Batal</button>
                <button type="submit" class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs transition-all"><i class="fas fa-rotate-left mr-1.5"></i> Kembalikan</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
(function () {
    var modal = document.getElementById('kembaliModal');
    var form = document.getElementById('kembaliForm');
    var alasan = document.getElementById('kembaliAlasan');
    var label = document.getElementById('kembaliLabel');
    document.querySelectorAll('.btnKembali').forEach(function (b) {
        b.addEventListener('click', function () {
            form.action = b.dataset.route;
            label.textContent = b.dataset.label;
            alasan.value = '';
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            alasan.focus();
        });
    });
    function tutup() { modal.classList.add('hidden'); modal.classList.remove('flex'); form.action = ''; }
    document.getElementById('kembaliBatal').addEventListener('click', tutup);
    modal.addEventListener('click', function (e) { if (e.target === modal) tutup(); });
})();
</script>
@endpush