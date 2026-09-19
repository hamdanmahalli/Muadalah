@extends('layouts.app')

@section('title', 'Kelola Anggaran')

@section('content')
@php
$final = $anggaran->status === 'final';
$labelWarna = $final ? 'bg-sky-100 text-sky-700 border-sky-200' : 'bg-amber-100 text-amber-700 border-amber-200';
$totalPagu = collect($anggaran->kelompok)->sum(fn($k) => $k->pos->sum('jumlah'));
$totalRencanaPemasukan = $anggaran->pemasukanRencana->sum('jumlah');
$bulanFiskal = bulan_fiskal_list();
$kodeMaxPerKelompok = $anggaran->kelompok->mapWithKeys(fn ($k) => [$k->id => ($k->pos->max('kode') ?? $k->kode) + 1])->all();
$posDetail = [];
$posSeimbang = [];
foreach ($anggaran->kelompok as $k) {
    foreach ($k->pos as $p) {
        $bulan = [];
        foreach (range(1, 12) as $b) {
            $bulan[$b] = 0.0;
            $pb = $p->posBulan->firstWhere('bulan_fiskal', $b);
            if ($pb) {
                $bulan[$b] = (float) $pb->nominal;
            }
        }
        $alokasi = array_sum($bulan);
        $posSeimbang[$p->id] = abs($alokasi - (float) $p->jumlah) <= 0.5;
        $posDetail[$p->id] = [
            'id' => $p->id,
            'kelompok' => $k->nama,
            'kode' => $p->kode,
            'uraian' => $p->uraian,
            'volume' => (float) $p->volume,
            'satuan' => $p->satuan,
            'volume_2' => $p->volume_2 !== null ? (float) $p->volume_2 : null,
            'satuan_2' => $p->satuan_2,
            'harga_satuan' => (float) $p->harga_satuan,
            'jumlah' => (float) $p->jumlah,
            'bulan' => $bulan,
        ];
    }
}
@endphp

<div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
    <div>
        <a href="{{ route('kebendaharaan.anggaran.index') }}" class="text-xs font-black text-slate-500 hover:text-emerald-600 mb-3 inline-flex items-center">
            <i class="fas fa-arrow-left mr-2"></i> Daftar Anggaran
        </a>
        <h2 class="text-2xl font-black text-slate-800 tracking-tight flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600 mr-1 shadow-inner">
                <i class="fas fa-calculator"></i>
            </div>
            {{ $anggaran->nama }}
            <span class="text-[10px] font-black px-2 py-1 rounded-md border {{ $labelWarna }}">
                <i class="fas {{ $final ? 'fa-lock' : 'fa-pen' }} mr-1"></i>{{ $final ? 'FINAL' : 'DRAFT' }}
            </span>
        </h2>
        <p class="text-sm font-bold text-slate-400 mt-1 ml-14">
            {{ $anggaran->tahun_ajaran }} &middot; Pagu Belanja Rp {{ number_format($totalPagu, 0, ',', '.') }} &middot; Rencana Pemasukan Rp {{ number_format($totalRencanaPemasukan, 0, ',', '.') }}
        </p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('kebendaharaan.anggaran.pdf', $anggaran->id) }}" class="inline-flex items-center justify-center px-4 py-2.5 bg-white text-slate-600 hover:bg-slate-600 hover:text-white border border-slate-200 font-bold text-xs rounded-xl transition-all">
            <i class="fas fa-file-pdf mr-2"></i> PDF RAB
        </a>
        @if($final)
        <form action="{{ route('kebendaharaan.anggaran.buka', $anggaran->id) }}" method="POST" onsubmit="return confirm('Buka kembali anggaran ini? Perubahan akan diizinkan.')">
            @csrf
            <button type="submit" class="inline-flex items-center justify-center px-4 py-2.5 bg-amber-50 text-amber-600 hover:bg-amber-500 hover:text-white border border-amber-200 font-bold text-xs rounded-xl transition-all">
                <i class="fas fa-unlock mr-2"></i> Buka Final
            </button>
        </form>
        @else
        <form action="{{ route('kebendaharaan.anggaran.final', $anggaran->id) }}" method="POST" onsubmit="return confirm('Finalkan RAB? Struktur anggaran akan terkunci dan menjadi acuan pencairan/LPJ.')">
            @csrf
            <button type="submit" class="inline-flex items-center justify-center px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white border border-emerald-600 font-bold text-xs rounded-xl transition-all">
                <i class="fas fa-lock mr-2"></i> Finalkan RAB
            </button>
        </form>
        @endif
    </div>
</div>

@if(session('error_detail'))
<div class="mb-6 bg-rose-50 border border-rose-200 rounded-2xl p-4">
    <p class="text-sm font-black text-rose-700 flex items-center gap-2 mb-2"><i class="fas fa-triangle-exclamation text-rose-500"></i> Distribusi alokasi bulanan belum seimbang dengan total RAB:</p>
    <ul class="list-disc list-inside text-xs font-semibold text-rose-600 space-y-1">
        @foreach(session('error_detail') as $detail)
        <li>{{ $detail }}</li>
        @endforeach
    </ul>
    <p class="text-[11px] font-bold text-rose-400 mt-2">Sebar alokasi tiap pos lewat tombol Edit agar total 12 bulan = jumlah pos.</p>
</div>
@endif

@if($final)
<div class="mb-6 bg-sky-50 border border-sky-200 rounded-2xl p-4 flex items-center gap-3 text-sky-800 text-sm font-bold">
    <i class="fas fa-lock text-sky-500 text-lg"></i>
    Anggaran sudah final. Struktur terkunci; pos ini menjadi acuan pencairan &amp; laporan pertanggung jawaban.
</div>
@else
<div class="mb-6 grid grid-cols-1 lg:grid-cols-2 gap-5">
    <!-- TAMBAH KELOMPOK -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
        <h3 class="text-sm font-black text-slate-700 uppercase tracking-widest mb-4"><i class="fas fa-folder-plus text-emerald-500 mr-2"></i>Tambah Kelompok Pos</h3>
        <form action="{{ route('kebendaharaan.anggaran.kelompok.store', $anggaran->id) }}" method="POST">
            @csrf
            <div class="grid grid-cols-[110px_1fr] gap-3 mb-3">
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Kode</label>
                    <input type="number" name="kode" required placeholder="100" class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 block p-3 outline-none transition-all font-medium">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Nama Kelompok</label>
                    <input type="text" name="nama" required placeholder="mis. Honorium dan Tunjangan" class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 block p-3 outline-none transition-all font-medium">
                </div>
            </div>
            <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2.5 px-4 rounded-xl text-sm transition-all flex items-center justify-center">
                <i class="fas fa-plus mr-2"></i> Tambah Kelompok
            </button>
        </form>
    </div>

    <!-- TAMBAH PEMASUKAN RENCANA -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
        <h3 class="text-sm font-black text-slate-700 uppercase tracking-widest mb-4"><i class="fas fa-circle-dollar-to-slot text-emerald-500 mr-2"></i>Tambah Pemasukan Rencana</h3>
        <form action="{{ route('kebendaharaan.anggaran.pemasukan.store', $anggaran->id) }}" method="POST">
            @csrf
            <div class="mb-3">
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Uraian</label>
                <input type="text" name="uraian" required placeholder="mis. Bantuan Operasional Sekolah Putra (BOSP)" class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 block p-3 outline-none transition-all font-medium">
            </div>
            <div class="grid grid-cols-2 gap-3 mb-3">
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Volume</label>
                    <input type="number" step="any" name="volume" placeholder="mis. 12" class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 block p-3 outline-none transition-all font-medium">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Harga Satuan</label>
                    <input type="text" name="harga_satuan" required placeholder="mis. 1.500.000" class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 block p-3 outline-none transition-all font-medium">
                </div>
            </div>
            <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2.5 px-4 rounded-xl text-sm transition-all flex items-center justify-center">
                <i class="fas fa-plus mr-2"></i> Tambah Pemasukan Rencana
            </button>
        </form>
    </div>
</div>
@endif

<!-- FORM TAMBAH POS (muncul setelah ada kelompok) -->
@if($anggaran->kelompok->count() > 0 && !$final)
<div class="mb-6 bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="bg-slate-50 border-b border-slate-100 p-4 flex justify-between items-center">
        <h3 class="text-sm font-black text-slate-700 uppercase tracking-widest"><i class="fas fa-plus-circle text-emerald-500 mr-2"></i>Tambah Item Pos Belanja</h3>
        <span class="text-[10px] font-bold text-slate-400">Hitung: Volume &times; Volume2 &times; Harga Satuan</span>
    </div>
    <form action="{{ route('kebendaharaan.anggaran.pos.store', $anggaran->id) }}" method="POST" class="p-5">
        @csrf
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Kelompok Pos</label>
                <select id="kelompokPosSelect" name="kelompok_id" required class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 block p-3 outline-none transition-all font-medium cursor-pointer">
                    @foreach($anggaran->kelompok as $k)
                    <option value="{{ $k->id }}">{{ $k->kode }} — {{ $k->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Kode Pos <span class="text-[9px] font-medium text-slate-400 normal-case">(otomatis: max+1)</span></label>
                <input id="kodePosBaru" type="number" name="kode" required placeholder="mis. 101" class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 block p-3 outline-none transition-all font-medium">
            </div>
            <div class="sm:col-span-2">
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Uraian Pos</label>
                <input type="text" name="uraian" required placeholder="mis. Tunjangan Kepala Sekolah" class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 block p-3 outline-none transition-all font-medium">
            </div>
            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Volume</label>
                <input type="number" step="any" name="volume" placeholder="mis. 12" class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 block p-3 outline-none transition-all font-medium">
            </div>
            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Satuan</label>
                <input type="text" name="satuan" placeholder="mis. bulan" class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 block p-3 outline-none transition-all font-medium">
            </div>
            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Volume 2</label>
                <input type="number" step="any" name="volume_2" placeholder="mis. 2" class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 block p-3 outline-none transition-all font-medium">
            </div>
            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Satuan 2</label>
                <input type="text" name="satuan_2" placeholder="mis. orang" class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 block p-3 outline-none transition-all font-medium">
            </div>
            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Harga Satuan (Rp)</label>
                <input type="text" name="harga_satuan" required placeholder="mis. 250.000" class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 block p-3 outline-none transition-all font-medium">
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3 px-4 rounded-xl text-sm transition-all flex items-center justify-center">
                    <i class="fas fa-plus mr-2"></i> Tambah Pos
                </button>
            </div>
        </div>
    </form>
</div>
@endif

<!-- TABEL STRUKTUR ANGGARAN -->
@foreach($anggaran->kelompok as $kelompok)
<div class="mb-6 bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="bg-slate-50 border-b border-slate-100 p-4 flex items-center justify-between">
        <h3 class="text-sm font-black text-slate-800 flex items-center gap-2">
            <span class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center text-xs font-black">{{ $kelompok->kode }}</span>
            {{ $kelompok->nama }}
        </h3>
        <div class="flex items-center gap-3">
            <span class="text-[11px] font-black text-slate-500">{{ $kelompok->pos->count() }} Pos</span>
            @if(!$final)
            <form action="{{ route('kebendaharaan.anggaran.kelompok.destroy', $kelompok->id) }}" method="POST" onsubmit="return confirm('Hapus kelompok pos ini?')">
                @csrf @method('DELETE')
                <button type="submit" class="text-[10px] font-black text-rose-500 hover:text-rose-700"><i class="fas fa-trash mr-1"></i>Hapus</button>
            </form>
            @endif
        </div>
    </div>

    <!-- MOBILE: kartu ringkas (Volume 2, Harga Satuan, Jumlah, Aksi) -->
    <div class="md:hidden divide-y divide-slate-100">
        @foreach($kelompok->pos as $pos)
        <div class="p-4 flex items-start justify-between gap-3">
            <div class="min-w-0 flex-1">
                <p class="text-sm font-black truncate {{ ($posSeimbang[$pos->id] ?? true) ? 'text-slate-800' : 'text-rose-600' }}"><span class="text-emerald-600">{{ $pos->kode }}</span> &middot; {{ $pos->uraian }}</p>
                <p class="mt-2 text-sm font-black text-emerald-700">Rp {{ number_format($pos->jumlah, 0, ',', '.') }}</p>
            </div>
            <button type="button" data-pos="{{ $pos->id }}" class="btn-edit-pos shrink-0 w-11 h-11 inline-flex items-center justify-center rounded-xl {{ $final ? 'bg-slate-100 text-slate-500' : 'bg-emerald-50 text-emerald-600 hover:bg-emerald-100' }}" title="{{ $final ? 'Lihat' : 'Edit' }}">
                <i class="fas {{ $final ? 'fa-eye' : 'fa-pen' }}"></i>
            </button>
        </div>
        @endforeach
        <div class="p-4 bg-slate-50 flex items-center justify-between">
            <span class="text-[11px] font-black text-slate-600 uppercase tracking-wider">Subtotal {{ $kelompok->kode }}</span>
            <span class="text-sm font-black text-emerald-700">Rp {{ number_format($kelompok->pos->sum('jumlah'), 0, ',', '.') }}</span>
        </div>
    </div>

    <!-- DESKTOP: tabel excel -->
    <div class="hidden md:block overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50/80 text-[10px] font-black text-slate-400 uppercase tracking-widest">
                    <th class="text-left px-4 py-3 w-16">Kode</th>
                    <th class="text-left px-4 py-3">Uraian</th>
                    <th class="text-right px-4 py-3">Volume</th>
                    <th class="text-right px-4 py-3">Satuan</th>
                    <th class="text-right px-4 py-3">Volume 2</th>
                    <th class="text-right px-4 py-3">Satuan 2</th>
                    <th class="text-right px-4 py-3">Harga Satuan</th>
                    <th class="text-right px-4 py-3">Jumlah (Rp)</th>
                    <th class="text-center px-4 py-3 w-28">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($kelompok->pos as $pos)
                <tr class="border-t border-slate-100 hover:bg-slate-50/50">
                    <td class="px-4 py-3 font-black text-slate-600">{{ $pos->kode }}</td>
                    <td class="px-4 py-3 font-semibold {{ ($posSeimbang[$pos->id] ?? true) ? 'text-slate-800' : 'text-rose-600' }}">{{ $pos->uraian }}</td>
                    <td class="px-4 py-3 text-right font-semibold text-slate-600">{{ rtrim(rtrim(number_format($pos->volume, 2), '0'), '.') }}</td>
                    <td class="px-4 py-3 text-right font-semibold text-slate-500">{{ $pos->satuan }}</td>
                    <td class="px-4 py-3 text-right font-semibold text-slate-600">{{ $pos->volume_2 !== null ? rtrim(rtrim(number_format($pos->volume_2, 2), '0'), '.') : '—' }}</td>
                    <td class="px-4 py-3 text-right font-semibold text-slate-500">{{ $pos->satuan_2 ?? '—' }}</td>
                    <td class="px-4 py-3 text-right font-semibold text-slate-600">{{ number_format($pos->harga_satuan, 0, ',', '.') }}</td>
                    <td class="px-4 py-3 text-right font-black text-slate-800">{{ number_format($pos->jumlah, 0, ',', '.') }}</td>
                    <td class="px-4 py-3">
                        <div class="flex items-center justify-end gap-1.5">
                            <button type="button" data-pos="{{ $pos->id }}" class="btn-edit-pos inline-flex items-center px-2.5 py-1.5 rounded-lg font-black text-[11px] {{ $final ? 'bg-slate-100 text-slate-500' : 'bg-emerald-50 text-emerald-600 hover:bg-emerald-100' }}">
                                <i class="fas {{ $final ? 'fa-eye' : 'fa-pen' }} mr-1"></i>{{ $final ? 'Lihat' : 'Edit' }}
                            </button>
                            @if(!$final)
                            <form action="{{ route('kebendaharaan.anggaran.pos.destroy', $pos->id) }}" method="POST" onsubmit="return confirm('Hapus pos ini?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-rose-500 hover:text-rose-700 p-1.5" title="Hapus pos"><i class="fas fa-trash"></i></button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
                <tr class="border-t-2 border-slate-200 bg-slate-50/60">
                    <td colspan="7" class="px-4 py-3 text-right font-black text-slate-600 uppercase tracking-wider">Subtotal Kelompok {{ $kelompok->kode }}</td>
                    <td class="px-4 py-3 text-right font-black text-emerald-700">{{ number_format($kelompok->pos->sum('jumlah'), 0, ',', '.') }}</td>
                    <td></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endforeach

@if($anggaran->kelompok->isEmpty())
<div class="mb-6 bg-white rounded-2xl shadow-sm border border-slate-200 p-10 text-center">
    <div class="w-16 h-16 bg-slate-50 text-slate-300 rounded-full flex items-center justify-center mx-auto mb-4 text-3xl shadow-inner"><i class="fas fa-folder-open"></i></div>
    <h4 class="text-sm font-black text-slate-700">Belum Ada Kelompok Pos</h4>
    <p class="text-xs font-medium text-slate-400 mt-1">Tambahkan kelompok pos terlebih dahulu di bagian atas halaman ini.</p>
</div>
@endif

<!-- PEMASUKAN RENCANA -->
<div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="bg-slate-50 border-b border-slate-100 p-4 flex items-center justify-between">
        <h3 class="text-sm font-black text-slate-700 uppercase tracking-widest"><i class="fas fa-circle-dollar-to-slot text-emerald-500 mr-2"></i>Rencana Pemasukan</h3>
        <span class="text-[11px] font-black text-emerald-600">Total Rp {{ number_format($totalRencanaPemasukan, 0, ',', '.') }}</span>
    </div>
    <!-- MOBILE: kartu ringkas -->
    <div class="md:hidden divide-y divide-slate-100">
        @forelse($anggaran->pemasukanRencana as $rencana)
        <div class="p-4 flex items-start justify-between gap-3">
            <div class="min-w-0 flex-1">
                <p class="text-sm font-black text-slate-800">{{ $rencana->uraian }}</p>
                <p class="mt-2 text-sm font-black text-emerald-700">Rp {{ number_format($rencana->jumlah, 0, ',', '.') }}</p>
            </div>
            @if(!$final)
            <form action="{{ route('kebendaharaan.anggaran.pemasukan.destroy', $rencana->id) }}" method="POST" onsubmit="return confirm('Hapus pemasukan rencana ini?')" class="shrink-0">
                @csrf @method('DELETE')
                <button type="submit" class="w-11 h-11 inline-flex items-center justify-center rounded-xl bg-rose-50 text-rose-500 hover:bg-rose-100" title="Hapus"><i class="fas fa-trash"></i></button>
            </form>
            @endif
        </div>
        @empty
        <div class="p-6 text-center text-xs font-semibold text-slate-400">Belum ada pemasukan rencana.</div>
        @endforelse
    </div>

    <!-- DESKTOP: tabel excel -->
    <div class="hidden md:block overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50/80 text-[10px] font-black text-slate-400 uppercase tracking-widest">
                    <th class="text-left px-4 py-3">Uraian</th>
                    <th class="text-right px-4 py-3">Volume</th>
                    <th class="text-right px-4 py-3">Satuan</th>
                    <th class="text-right px-4 py-3">Harga Satuan</th>
                    <th class="text-right px-4 py-3">Jumlah (Rp)</th>
                    <th class="text-center px-4 py-3 w-24">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($anggaran->pemasukanRencana as $rencana)
                <tr class="border-t border-slate-100 hover:bg-slate-50/50">
                    <td class="px-4 py-3 font-semibold text-slate-800">{{ $rencana->uraian }}</td>
                    <td class="px-4 py-3 text-right font-semibold text-slate-600">{{ rtrim(rtrim(number_format($rencana->volume, 2), '0'), '.') }}</td>
                    <td class="px-4 py-3 text-right font-semibold text-slate-500">{{ $rencana->satuan ?? '—' }}</td>
                    <td class="px-4 py-3 text-right font-semibold text-slate-600">{{ number_format($rencana->harga_satuan, 0, ',', '.') }}</td>
                    <td class="px-4 py-3 text-right font-black text-slate-800">{{ number_format($rencana->jumlah, 0, ',', '.') }}</td>
                    <td class="px-4 py-3">
                        <div class="flex items-center justify-end gap-1.5">
                            @if(!$final)
                            <form action="{{ route('kebendaharaan.anggaran.pemasukan.destroy', $rencana->id) }}" method="POST" onsubmit="return confirm('Hapus pemasukan rencana ini?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="inline-flex items-center px-2.5 py-1.5 rounded-lg font-black text-[11px] bg-rose-50 text-rose-500 hover:bg-rose-100"><i class="fas fa-trash mr-1"></i>Hapus</button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr class="border-t border-slate-100">
                    <td colspan="6" class="px-4 py-6 text-center text-xs font-semibold text-slate-400">Belum ada pemasukan rencana.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- MODAL EDIT / LIHAT POS -->
<div id="modalEditPos" class="hidden fixed inset-0 z-[70] bg-slate-900/50 backdrop-blur-sm p-4 flex items-end sm:items-center justify-center" role="dialog" aria-modal="true">
    <div class="w-full max-w-2xl bg-white rounded-t-2xl sm:rounded-2xl shadow-2xl overflow-hidden max-h-[92vh] flex flex-col">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between shrink-0 bg-white">
            <div class="min-w-0">
                <h3 class="text-base font-black text-slate-800 truncate" id="mposJudul">Edit Pos</h3>
                <p class="text-xs font-bold text-slate-400 truncate" id="mposKel"></p>
            </div>
            <button type="button" id="mposTutup" class="ml-3 w-10 h-10 shrink-0 inline-flex items-center justify-center rounded-xl bg-slate-100 text-slate-500 hover:bg-rose-50 hover:text-rose-600"><i class="fas fa-xmark"></i></button>
        </div>
        <form id="mposForm" method="POST" action="" class="flex flex-col min-h-0">
            @csrf @method('PUT')
            <input type="hidden" name="kode" id="mposKodeHidden">
            <div class="p-5 space-y-4 overflow-y-auto">
                @if($final)
                <p class="bg-sky-50 border border-sky-200 text-sky-800 rounded-xl px-4 py-2.5 text-xs font-bold"><i class="fas fa-lock mr-2"></i>Anggaran sudah final — mode lihat saja. Klik "Buka Final" untuk mengedit.</p>
                @endif
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Uraian Pos</label>
                    <input type="text" name="uraian" id="mposUraian" required class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 block p-3 outline-none transition-all font-medium" {{ $final ? 'readonly' : '' }}>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Volume</label>
                        <input type="number" step="any" name="volume" id="mposVolume" {{ $final ? 'readonly' : '' }} class="mpos-angka w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 block p-3 outline-none transition-all font-medium">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Satuan</label>
                        <input type="text" name="satuan" id="mposSatuan" {{ $final ? 'readonly' : '' }} class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 block p-3 outline-none transition-all font-medium">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Volume 2</label>
                        <input type="number" step="any" name="volume_2" id="mposVolume2" {{ $final ? 'readonly' : '' }} class="mpos-angka w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 block p-3 outline-none transition-all font-medium">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Satuan 2</label>
                        <input type="text" name="satuan_2" id="mposSatuan2" {{ $final ? 'readonly' : '' }} class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 block p-3 outline-none transition-all font-medium">
                    </div>
                    <div class="col-span-2 sm:col-span-4">
                        <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Harga Satuan (Rp)</label>
                        <input type="text" name="harga_satuan_text" id="mposHarga" required inputmode="numeric" {{ $final ? 'readonly' : '' }} class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 block p-3 outline-none transition-all font-medium">
                        <input type="hidden" name="harga_satuan" id="mposHargaVal">
                    </div>
                </div>
                <div class="pt-1">
                    <p class="text-xs font-black text-slate-600 uppercase tracking-widest mb-2 flex items-center justify-between">
                        <span><i class="fas fa-calendar-week text-emerald-500 mr-2"></i>Alokasi Bulanan</span>
                        <span class="text-[11px] font-black text-emerald-700">Total: <span id="mposTotalBulan">Rp 0</span></span>
                    </p>
                    <div id="mposAlokasiInfo" class="hidden rounded-xl px-3 py-2 text-xs font-black mb-2"></div>
                    <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 lg:grid-cols-12 gap-2">
                        @foreach($bulanFiskal as $idBulan => $namaBulan)
                        <div>
                            <label class="block text-[9px] font-black text-slate-400 uppercase text-center mb-1">{{ $namaBulan }}</label>
                            <input type="text" name="bulan[{{ $idBulan }}]" data-bulan="{{ $idBulan }}" inputmode="numeric" class="mpos-bulan w-full text-right rounded-lg border border-slate-300 bg-white px-2 py-1.5 text-sm font-bold focus:ring-2 focus:ring-emerald-500 focus:outline-none" placeholder="0" {{ $final ? 'readonly' : '' }}>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="p-5 border-t border-slate-100 flex items-center gap-2 shrink-0">
                <button type="button" id="mposBatal" class="flex-1 px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 font-black text-sm rounded-xl">Batal</button>
                @if(!$final)
                <button type="submit" id="mposSimpan" class="flex-1 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-black text-sm rounded-xl"><i class="fas fa-save mr-2"></i>Simpan Perubahan</button>
                @endif
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
(function () {
    const kodeMap = @json($kodeMaxPerKelompok ?? []);
    const POSDATA = @json($posDetail ?? []);
    const ROUTE_DETAIL = "{{ route('kebendaharaan.anggaran.pos.update-detail', '__ID__') }}";
    const IS_FINAL = @json($final);

    const kelSelect = document.getElementById('kelompokPosSelect');
    const kodeInput = document.getElementById('kodePosBaru');
    function isiKode() { if (kodeMap[kelSelect.value] !== undefined) kodeInput.value = kodeMap[kelSelect.value]; }
    if (kelSelect && kodeInput) {
        kelSelect.addEventListener('change', isiKode);
        isiKode();
    }

    const modal = document.getElementById('modalEditPos');
    if (!modal) return;

    const el = {
        form: document.getElementById('mposForm'),
        judul: document.getElementById('mposJudul'),
        kel: document.getElementById('mposKel'),
        uraian: document.getElementById('mposUraian'),
        volume: document.getElementById('mposVolume'),
        satuan: document.getElementById('mposSatuan'),
        volume2: document.getElementById('mposVolume2'),
        satuan2: document.getElementById('mposSatuan2'),
        harga: document.getElementById('mposHarga'),
        hargaVal: document.getElementById('mposHargaVal'),
        total: document.getElementById('mposTotalBulan'),
        info: document.getElementById('mposAlokasiInfo'),
    };
    const bulanInputs = Array.from(document.querySelectorAll('.mpos-bulan'));
    let targetJumlah = 0;

    function fmtRupiah(n) {
        return String(n).replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }
    function hitungTotal() {
        const total = bulanInputs.reduce((acc, i) => acc + (parseInt(String(i.value).replace(/\D/g, ''), 10) || 0), 0);
        el.total.textContent = 'Rp ' + total.toLocaleString('id-ID');
        if (!el.info) return;
        const selisih = total - targetJumlah;
        el.info.classList.remove('hidden', 'bg-emerald-50', 'text-emerald-700', 'border', 'border-emerald-200', 'bg-amber-50', 'text-amber-700', 'border-amber-200', 'bg-rose-50', 'text-rose-700', 'border-rose-200');
        if (total === targetJumlah) {
            el.info.classList.add('bg-emerald-50', 'text-emerald-700', 'border', 'border-emerald-200');
            el.info.textContent = 'Alokasi bulanan sama dengan jumlah pos (Rp ' + targetJumlah.toLocaleString('id-ID') + ').';
        } else if (selisih > 0) {
            el.info.classList.add('bg-rose-50', 'text-rose-700', 'border', 'border-rose-200');
            el.info.textContent = 'Lebih ' + fmtRupiah(selisih) + ' dari jumlah pos (Rp ' + targetJumlah.toLocaleString('id-ID') + ').';
        } else {
            el.info.classList.add('bg-amber-50', 'text-amber-700', 'border', 'border-amber-200');
            el.info.textContent = 'Kurang ' + fmtRupiah(Math.abs(selisih)) + ' dari jumlah pos (Rp ' + targetJumlah.toLocaleString('id-ID') + ').';
        }
    }
    function formatBulanInput(sec) {
        const d = String(sec.value || '').replace(/\D/g, '');
        sec.value = d === '' ? '' : fmtRupiah(d);
    }

    function tutup() {
        modal.classList.add('hidden');
        if (el.info) el.info.classList.add('hidden');
        document.body.style.overflow = '';
    }

    function muat(id) {
        const d = POSDATA[id];
        if (!d) return;
        el.form.action = ROUTE_DETAIL.replace('__ID__', id);
        el.form.elements.kode.value = d.kode;
        el.judul.textContent = 'Pos ' + d.kode + ' \u2014 ' + d.uraian;
        el.kel.textContent = d.kelompok;
        el.uraian.value = d.uraian;
        el.volume.value = d.volume;
        el.satuan.value = d.satuan || '';
        el.volume2.value = d.volume_2 === null ? '' : d.volume_2;
        el.satuan2.value = d.satuan_2 || '';
        el.harga.value = fmtRupiah(d.harga_satuan);
        el.hargaVal.value = d.harga_satuan;
        targetJumlah = d.jumlah;
        bulanInputs.forEach(i => { const v = d.bulan[i.dataset.bulan] || 0; i.value = v ? fmtRupiah(v) : (v === 0 ? '0' : ''); });
        hitungTotal();
        if (IS_FINAL) modal.classList.add('mpos-final');
        else modal.classList.remove('mpos-final');
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    document.querySelectorAll('.btn-edit-pos').forEach(btn => {
        btn.addEventListener('click', function () { muat(this.dataset.pos); });
    });
    document.getElementById('mposTutup').addEventListener('click', tutup);
    document.getElementById('mposBatal').addEventListener('click', tutup);
    modal.addEventListener('click', function (e) { if (e.target === modal) tutup(); });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && !modal.classList.contains('hidden')) tutup();
    });

    if (el.harga) {
        el.harga.addEventListener('input', function () {
            const digits = String(this.value).replace(/\D/g, '');
            this.value = fmtRupiah(digits);
            el.hargaVal.value = digits;
        });
    }
    bulanInputs.forEach(i => i.addEventListener('input', function () { formatBulanInput(this); hitungTotal(); }));

    el.form.addEventListener('submit', function (e) {
        if (IS_FINAL) { e.preventDefault(); return; }
        el.hargaVal.value = String(el.harga.value).replace(/\D/g, '');
        bulanInputs.forEach(i => { i.value = String(i.value).replace(/\D/g, ''); });
    });
})();
</script>
@endpush