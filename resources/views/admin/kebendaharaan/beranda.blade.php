@extends('layouts.app')

@section('title', 'Kebendaharaan')

@section('content')
<div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
    <div>
        <h2 class="text-2xl font-black text-slate-800 tracking-tight flex items-center">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600 mr-3 shadow-inner">
                <i class="fas fa-landmark"></i>
            </div>
            Kebendaharaan
        </h2>
        <p class="text-sm font-bold text-slate-400 mt-1 ml-14">
            Kelola anggaran (RAB), pencairan, laporan pertanggung jawaban, pemasukan, hingga rekap otomatis.
        </p>
    </div>
    <div class="bg-white px-4 py-2 rounded-xl border border-slate-200 text-sm font-bold shadow-sm text-slate-600">
        <i class="fas fa-clock text-emerald-500 mr-1.5"></i> TA: {{ $periode->tahun_ajaran }}
    </div>
</div>

<!-- KARTU RINGKASAN -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    @php
        $kartu = [
            ['ikon' => 'fa-circle-dollar-to-slot', 'warna' => 'bg-emerald-50 text-emerald-600', 'label' => 'Total Pemasukan', 'nilai' => $pemasukanTotal, 'ket' => 'Semua pemasukan periode ini'],
            ['ikon' => 'fa-receipt', 'warna' => 'bg-rose-50 text-rose-600', 'label' => 'Total Pengeluaran', 'nilai' => $realisasiTotal, 'ket' => 'LPJ disetujui (realisasi)'],
            ['ikon' => 'fa-scale-balanced', 'warna' => 'bg-sky-50 text-sky-600', 'label' => 'Saldo Kas', 'nilai' => $saldo, 'ket' => 'Pemasukan - Pengeluaran'],
            ['ikon' => 'fa-coins', 'warna' => 'bg-violet-50 text-violet-600', 'label' => 'Sisa Pinjaman', 'nilai' => $sisaPinjamanTotal, 'ket' => $pinjamanAktif->count() . ' pinjaman aktif'],
        ];
    @endphp
    @foreach($kartu as $c)
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
        <div class="flex items-center justify-between">
            <div class="w-11 h-11 rounded-xl {{ $c['warna'] }} flex items-center justify-center shadow-inner">
                <i class="fas {{ $c['ikon'] }} text-lg"></i>
            </div>
        </div>
        <p class="text-[11px] font-black text-slate-400 uppercase tracking-widest mt-3">{{ $c['label'] }}</p>
        <p class="text-xl font-black text-slate-800 mt-0.5">Rp {{ number_format($c['nilai'], 0, ',', '.') }}</p>
        <p class="text-[11px] font-semibold text-slate-400 mt-0.5">{{ $c['ket'] }}</p>
    </div>
    @endforeach
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-6">
    <!-- STATUS PENCAIRAN -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="bg-slate-50 border-b border-slate-100 p-4 flex items-center justify-between">
            <h3 class="text-sm font-black text-slate-700 uppercase tracking-widest"><i class="fas fa-hand-holding-dollar text-emerald-500 mr-2"></i>Status Pencairan (SPP)</h3>
            @can('akses_pencairan')
            <a href="{{ route('kebendaharaan.pencairan.index') }}" class="text-xs font-black text-emerald-600 hover:text-emerald-800">Kelola <i class="fas fa-arrow-right ml-1"></i></a>
            @endcan
        </div>
        <div class="grid grid-cols-3 gap-3 p-5">
            @foreach(['diajukan' => ['Menunggu', 'bg-amber-50 text-amber-600'], 'dibayar' => ['Dibayar', 'bg-emerald-50 text-emerald-600'], 'ditolak' => ['Ditolak', 'bg-rose-50 text-rose-600']] as $st => $info)
            <div class="rounded-xl border border-slate-100 p-4 text-center">
                <p class="text-2xl font-black {{ $info[1] }}">{{ $dCair[$st] }}</p>
                <p class="text-[11px] font-bold text-slate-500 mt-1">{{ $info[0] }}</p>
            </div>
            @endforeach
        </div>
        @if($dLaporan['diajukan'] > 0)
        <div class="px-5 pb-4">
            <span class="inline-flex items-center gap-2 bg-amber-50 text-amber-700 border border-amber-200 text-xs font-black px-3 py-1.5 rounded-lg">
                <i class="fas fa-triangle-exclamation"></i> {{ $dLaporan['diajukan'] }} laporan pertanggung jawaban menunggu validasi
            </span>
        </div>
        @endif
    </div>

    <!-- ANGGARAN -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="bg-slate-50 border-b border-slate-100 p-4 flex items-center justify-between">
            <h3 class="text-sm font-black text-slate-700 uppercase tracking-widest"><i class="fas fa-calculator text-emerald-500 mr-2"></i>Anggaran Periode Ini</h3>
            @can('akses_anggaran')
            <a href="{{ route('kebendaharaan.anggaran.index') }}" class="text-xs font-black text-emerald-600 hover:text-emerald-800">Kelola <i class="fas fa-arrow-right ml-1"></i></a>
            @endcan
        </div>
        <div class="p-5">
            @if($anggaran)
            <div class="flex items-center justify-between mb-4">
                <div>
                    <p class="text-base font-black text-slate-800">{{ $anggaran->nama }}</p>
                    <span class="text-[10px] font-black mt-1 inline-block px-2 py-0.5 rounded-md border {{ $anggaran->status === 'final' ? 'bg-sky-100 text-sky-700 border-sky-200' : 'bg-amber-100 text-amber-700 border-amber-200' }}">
                        <i class="fas {{ $anggaran->status === 'final' ? 'fa-lock' : 'fa-pen' }} mr-1"></i>{{ $anggaran->status === 'final' ? 'Final' : 'Draft' }}
                    </span>
                </div>
                <p class="text-lg font-black text-emerald-600">Rp {{ number_format($anggaran->totalPagu(), 0, ',', '.') }}</p>
            </div>
            <div class="space-y-2">
                <div class="flex justify-between text-xs font-bold text-slate-500">
                    <span>Rencana Pemasukan</span>
                    <span>Rp {{ number_format($anggaran->totalPemasukanRencana(), 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-xs font-bold text-slate-500">
                    <span>Total Pagu Belanja</span>
                    <span>Rp {{ number_format($anggaran->totalPagu(), 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-xs font-bold text-slate-500">
                    <span>Realisasi (LPJ)</span>
                    <span class="text-rose-500">Rp {{ number_format($realisasiTotal, 0, ',', '.') }}</span>
                </div>
            </div>
            @else
            <div class="text-center py-6">
                <div class="w-14 h-14 bg-slate-50 text-slate-300 rounded-full flex items-center justify-center mx-auto mb-3 text-2xl shadow-inner"><i class="fas fa-calculator"></i></div>
                <p class="text-sm font-black text-slate-700">Belum Ada Anggaran</p>
                <p class="text-xs font-medium text-slate-400 mt-1">Bendahara perlu menyusun RAB untuk periode ini.</p>
                @can('akses_anggaran')
                <a href="{{ route('kebendaharaan.anggaran.create') }}" class="inline-flex items-center mt-4 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl transition-all">
                    <i class="fas fa-plus mr-2"></i> Susun RAB
                </a>
                @endcan
            </div>
            @endif
        </div>
    </div>
</div>

<!-- NILAI STOK & TOKO -->
<div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="bg-slate-50 border-b border-slate-100 p-4 flex items-center justify-between">
        <h3 class="text-sm font-black text-slate-700 uppercase tracking-widest"><i class="fas fa-book-open text-emerald-500 mr-2"></i>Toko Buku</h3>
        @can('akses_toko_buku')
        <a href="{{ route('kebendaharaan.toko.index') }}" class="text-xs font-black text-emerald-600 hover:text-emerald-800">Kelola <i class="fas fa-arrow-right ml-1"></i></a>
        @endcan
    </div>
    <div class="p-0">
        <div class="p-5 flex items-center justify-between border-b border-slate-100">
            <div class="flex items-center gap-4">
                <div class="w-11 h-11 bg-sky-50 rounded-xl flex items-center justify-center text-sky-600 shadow-inner"><i class="fas fa-boxes-stacked"></i></div>
                <div>
                    <p class="text-sm font-black text-slate-800">Nilai Stok Barang</p>
                    <p class="text-xs font-semibold text-slate-400">{{ $stokBarang->count() }} jenis dengan stok &gt; 0</p>
                </div>
            </div>
            <p class="text-lg font-black text-slate-800">Rp {{ number_format($nilaistok, 0, ',', '.') }}</p>
        </div>
        <div class="p-5 flex items-center justify-between border-b border-slate-100">
            <div class="flex items-center gap-4">
                <div class="w-11 h-11 bg-rose-50 rounded-xl flex items-center justify-center text-rose-600 shadow-inner"><i class="fas fa-hand-holding-heart"></i></div>
                <div>
                    <p class="text-sm font-black text-slate-800">Pinjaman Aktif</p>
                    <p class="text-xs font-semibold text-slate-400">{{ $pinjamanAktif->count() }} pinjaman</p>
                </div>
            </div>
            <p class="text-lg font-black text-rose-600">Rp {{ number_format($sisaPinjamanTotal, 0, ',', '.') }}</p>
        </div>
    </div>
</div>
@endsection