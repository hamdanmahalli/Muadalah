@extends('layouts.app')

@section('title', 'Transaksi')

@section('content')
@php
$bulanList = bulan_fiskal_list();
$bulanId = [1=>'Jan',2=>'Feb',3=>'Mar',4=>'Apr',5=>'Mei',6=>'Jun',7=>'Jul',8=>'Agu',9=>'Sep',10=>'Okt',11=>'Nov',12=>'Des'];
$labelBulan = fn ($b) => ucwords(strtolower($bulanList[$b] ?? ('Bulan ' . $b)));
$uangMasuk = $ringkas['uangMasuk'] ?? 0;
$uangKeluar = $ringkas['uangKeluar'] ?? 0;
$pemasukanTercatat = $ringkas['pemasukanTercatat'] ?? 0;
$sisa = $ringkas['sisa'] ?? 0;
$harian = $ringkas['harian'] ?? collect();
@endphp

<div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
    <div>
        <h2 class="text-2xl font-black text-slate-800 tracking-tight flex items-center">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600 mr-3 shadow-inner">
                <i class="fas fa-right-left"></i>
            </div>
            Transaksi
        </h2>
        <p class="text-sm font-bold text-slate-400 mt-1 ml-14">
            @if($sppAktif)
            Buku kas <span class="text-emerald-700">SPP {{ $sppAktif->kode }}</span> &middot; {{ $sppAktif->keperluan }} &middot;
            bulan {{ $labelBulan($sppAktif->bulan_fiskal) }}. Uang masuk dari SPP dibayar, uang keluar dari belanja per pos.
            @else
            Belum ada buku kas aktif untuk Anda &middot; SPP rutin yang Anda ajukan &amp; sudah dibayar akan membuka buku secara otomatis.
            @endif
            Periode {{ $periode->tahun_ajaran }} &middot; {{ ucfirst($periode->semester) }}.
        </p>
    </div>

    <div class="flex items-center gap-2 flex-wrap">
        @if($sppAktif)
        <span class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-[11px] font-black">
            <i class="fas fa-book-open"></i> SPP {{ $sppAktif->kode }} &middot; bulan {{ $labelBulan($sppAktif->bulan_fiskal) }}
        </span>
        <form action="{{ route('kebendaharaan.laporan.laporkan') }}" method="POST" class="flex flex-col sm:flex-row items-stretch sm:items-end gap-2">
            @csrf
            <div class="bg-white border border-slate-200 rounded-xl px-3 py-2">
                <textarea name="catatan" rows="1" placeholder="Catatan untuk bendahara (opsional): ringkasan penggunaan dana SPP..." class="w-full sm:w-72 bg-transparent text-slate-700 text-xs font-medium placeholder:text-slate-400 focus:outline-none resize-none"></textarea>
            </div>
            <button type="submit" class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-900 text-white font-bold text-xs transition-all shadow-sm"
                onclick="return confirm('Laporkan pengeluaran SPP {{ $sppAktif->kode }} ke bendahara? Setelah dilaporkan, buku terkunci dan menunggu validasi bendahara / pengesahan pimpinan.')">
                <i class="fas fa-paper-plane mr-1.5"></i> Laporkan ke Bendahara
            </button>
        </form>
        @endif
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

@if(!$sppAktif)
<div class="mb-6 bg-white rounded-2xl shadow-sm border border-slate-200 px-4 py-16 text-center">
    <div class="w-16 h-16 bg-slate-50 text-slate-300 rounded-full flex items-center justify-center mx-auto mb-4 text-3xl shadow-inner"><i class="fas fa-book-open"></i></div>
    <p class="text-sm font-black text-slate-700">Belum ada buku kas aktif untuk Anda</p>
    <p class="text-xs font-medium text-slate-400 mt-1">Halaman ini berisi buku kas untuk SPP yang <b>Anda ajukan</b> (pengaju). Ajukan SPP rutin di menu <b>Pencairan</b> &mdash; begitu dibayar buku langsung terbuka. Buku yang sudah dilaporkan tampil di <b>Riwayat &amp; Validasi Laporan</b> di bawah.</p>
</div>
@else
{{-- Kartu ringkas --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1"><i class="fas fa-arrow-down text-emerald-500 mr-1"></i> Uang Masuk</p>
        <p class="text-2xl font-black text-emerald-600">Rp {{ number_format($uangMasuk, 0, ',', '.') }}</p>
        <p class="text-[10px] font-bold text-slate-400 mt-1">Dari SPP yang dibayar</p>
    </div>
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1"><i class="fas fa-arrow-up text-rose-500 mr-1"></i> Uang Keluar</p>
        <p class="text-2xl font-black text-rose-600">Rp {{ number_format($uangKeluar, 0, ',', '.') }}</p>
        <p class="text-[10px] font-bold text-slate-400 mt-1">Realisasi belanja per pos</p>
    </div>
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1"><i class="fas fa-scale-balanced text-slate-500 mr-1"></i> Sisa Kas</p>
        <p class="text-2xl font-black {{ $sisa < 0 ? 'text-rose-600' : 'text-slate-800' }}">Rp {{ number_format($sisa, 0, ',', '.') }}</p>
        <p class="text-[10px] font-bold text-slate-400 mt-1">Masuk &minus; Keluar</p>
    </div>
</div>

@if($pemasukanTercatat > 0)
<div class="mb-4 flex items-center gap-2 text-[11px] font-bold text-slate-500 bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5">
    <i class="fas fa-circle-info text-slate-400"></i>
    Pemasukan manual tercatat (terpisah dari dana SPP): <span class="text-emerald-600 font-black">Rp {{ number_format($pemasukanTercatat, 0, ',', '.') }}</span>
</div>
@endif

{{-- Catatan buku kas aktif --}}
<div class="space-y-4">
    @forelse($harian as $tanggal => $rows)
    @php
    $tgl = $tanggal ? \Illuminate\Support\Carbon::parse($tanggal) : null;
    $label = $tgl ? ($tgl->format('d') . ' ' . $bulanId[(int) $tgl->format('n')] . ' ' . $tgl->format('Y')) : 'Tanpa tanggal';
    $dayMasuk = $rows->where('arah', 'masuk')->sum('nominal');
    $dayKeluar = $rows->where('arah', 'keluar')->sum('nominal');
    @endphp
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="bg-slate-50 border-b border-slate-100 px-4 py-2.5 flex items-center justify-between flex-wrap gap-2">
            <p class="text-[11px] font-black text-slate-600">{{ $label }}</p>
            <p class="text-[10px] font-bold text-slate-400">
                @if($dayMasuk > 0)<span class="text-emerald-600">Masuk Rp {{ number_format($dayMasuk, 0, ',', '.') }}</span>@endif
                @if($dayMasuk > 0 && $dayKeluar > 0) &middot; @endif
                @if($dayKeluar > 0)<span class="text-rose-600">Keluar Rp {{ number_format($dayKeluar, 0, ',', '.') }}</span>@endif
            </p>
        </div>
        <div class="divide-y divide-slate-100">
            @foreach($rows as $r)
            <div class="px-4 py-3 flex items-start gap-3">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 {{ $r['arah'] === 'masuk' ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-500' }}">
                    <i class="fas {{ $r['arah'] === 'masuk' ? 'fa-arrow-down' : 'fa-arrow-up' }}"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-black text-slate-800 truncate">{{ $r['judul'] }}</p>
                    <p class="text-[10px] font-bold text-slate-400 mt-0.5">
                        @if($r['kode'])<span class="text-slate-500">{{ $r['kode'] }}</span> &middot; @endif
                        <span class="{{ $r['arah'] === 'masuk' ? 'text-emerald-600' : 'text-rose-500' }}">{{ $r['sumber'] }}</span>
                        @if($r['pos']) &middot; <span class="text-slate-500">{{ $r['pos'] }}</span>@endif
                    </p>
                    @if($r['uraian'])
                    <p class="text-xs font-semibold text-slate-500 mt-1">{{ $r['uraian'] }}</p>
                    @endif
                </div>
                <div class="shrink-0 flex items-center gap-2">
                    @if($r['foto'])
                    <button type="button" class="lihatNota w-9 h-9 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center" data-src="{{ asset('uploads/' . $r['foto']) }}">
                        <i class="fas fa-image"></i>
                    </button>
                    @endif
                    <p class="text-sm font-black whitespace-nowrap {{ $r['arah'] === 'masuk' ? 'text-emerald-600' : 'text-rose-600' }}">
                        {{ $r['arah'] === 'masuk' ? '+' : '-' }} Rp {{ number_format($r['nominal'], 0, ',', '.') }}
                    </p>
                    @if($r['id'])
                    <form action="{{ route('kebendaharaan.laporan.destroy', $r['id']) }}" method="POST" onsubmit="return confirm('Hapus catatan belanja ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-9 h-9 rounded-lg bg-white border border-slate-200 text-slate-400 hover:text-rose-500 hover:border-rose-200 flex items-center justify-center">
                            <i class="fas fa-trash-can"></i>
                        </button>
                    </form>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @empty
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 px-4 py-16 text-center">
        <div class="w-16 h-16 bg-slate-50 text-slate-300 rounded-full flex items-center justify-center mx-auto mb-4 text-3xl shadow-inner"><i class="fas fa-book-open"></i></div>
        <p class="text-sm font-black text-slate-700">Belum ada catatan pada buku ini</p>
        <p class="text-xs font-medium text-slate-400 mt-1">Tekan <b>+ Catat Transaksi</b> untuk mencatat uang masuk/keluar.</p>
    </div>
    @endforelse
</div>
@endif

{{-- Validasi & pengesahan laporan mengarah ke halaman terpisah --}}
@if($bisaBendahara || $bisaPimpinan)
<a href="{{ route('kebendaharaan.laporan.validasi') }}" class="mt-8 flex items-center justify-between gap-3 bg-white rounded-2xl shadow-sm border border-slate-200 px-4 py-3 hover:border-emerald-300 transition-all">
    <div class="flex items-center gap-3 min-w-0">
        <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center text-amber-600 shrink-0"><i class="fas fa-file-signature"></i></div>
        <div class="min-w-0">
            <p class="text-sm font-black text-slate-800 truncate">Validasi &amp; Pengesahan Laporan</p>
            <p class="text-[10px] font-bold text-slate-400 truncate">Tinjau laporan SPP dari pengaju: terima, kembalikan, atau sahkan final.</p>
        </div>
    </div>
    <div class="flex items-center gap-2 shrink-0 flex-wrap">
        @if($bisaBendahara)
        <span class="px-2.5 py-1 rounded-full bg-amber-100 text-amber-700 text-[10px] font-black">{{ $jmlMenungguBendahara }} menunggu validasi</span>
        @endif
        @if($bisaPimpinan)
        <span class="px-2.5 py-1 rounded-full bg-sky-100 text-sky-700 text-[10px] font-black">{{ $jmlMenungguPengesahan }} menunggu pengesahan</span>
        @endif
        <i class="fas fa-arrow-right text-slate-300"></i>
    </div>
</a>
@endif

{{-- Riwayat laporan pengaju --}}
<div class="mt-8 space-y-8">
    <div>
        <h3 class="text-sm font-black text-slate-700 mb-3 flex items-center">
            <i class="fas fa-clock-rotate-left text-slate-400 mr-2"></i> Riwayat Laporan Saya
        </h3>

        @if($riwayat->isEmpty())
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 px-4 py-8 text-center">
            <p class="text-xs font-bold text-slate-400">Belum ada laporan yang dikirim pada periode ini.</p>
        </div>
        @else
        <div class="space-y-3">
            @foreach($riwayat as $r)
            @include('admin.kebendaharaan._laporan-kartu', ['r' => $r])
            @endforeach
        </div>
        @endif
    </div>
</div>

@if($sppAktif)
<a href="{{ route('kebendaharaan.laporan.create') }}"
   class="fixed bottom-6 right-6 z-40 inline-flex items-center gap-2 px-5 py-4 rounded-2xl bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white font-black text-sm shadow-[0_10px_30px_-6px_rgba(16,185,129,0.6)] transition-all">
    <i class="fas fa-plus"></i> Catat Transaksi
</a>
@endif

{{-- Lightbox nota --}}
<div id="notaModal" class="hidden fixed inset-0 z-50 bg-slate-900/70 backdrop-blur-sm items-center justify-center p-4">
    <div class="relative max-w-3xl w-full">
        <button type="button" id="notaTutup" class="absolute -top-10 right-0 text-white/80 hover:text-white text-2xl"><i class="fas fa-xmark"></i></button>
        <img id="notaImg" src="" alt="Nota" class="w-full max-h-[80vh] object-contain rounded-2xl bg-white shadow-2xl">
    </div>
</div>

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
@endsection

@push('scripts')
<script>
(function () {
    var modal = document.getElementById('notaModal');
    var img = document.getElementById('notaImg');
    document.querySelectorAll('.lihatNota').forEach(function (b) {
        b.addEventListener('click', function () {
            img.src = b.dataset.src;
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        });
    });
    function tutup() { modal.classList.add('hidden'); modal.classList.remove('flex'); img.src = ''; }
    document.getElementById('notaTutup').addEventListener('click', tutup);
    modal.addEventListener('click', function (e) { if (e.target === modal) tutup(); });

    var kModal = document.getElementById('kembaliModal');
    var kForm = document.getElementById('kembaliForm');
    var kAlasan = document.getElementById('kembaliAlasan');
    var kLabel = document.getElementById('kembaliLabel');
    document.querySelectorAll('.btnKembali').forEach(function (b) {
        b.addEventListener('click', function () {
            kForm.action = b.dataset.route;
            kLabel.textContent = b.dataset.label;
            kAlasan.value = '';
            kModal.classList.remove('hidden');
            kModal.classList.add('flex');
            kAlasan.focus();
        });
    });
    function tutupK() { kModal.classList.add('hidden'); kModal.classList.remove('flex'); kForm.action = ''; }
    document.getElementById('kembaliBatal').addEventListener('click', tutupK);
    kModal.addEventListener('click', function (e) { if (e.target === kModal) tutupK(); });
})();
</script>
@endpush
