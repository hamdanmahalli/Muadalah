@extends('layouts.app')

@section('title', 'Honor Guru')

@section('content')
<div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
    <div>
        <h2 class="text-2xl font-black text-slate-800 tracking-tight flex items-center">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600 mr-3 shadow-inner">
                <i class="fas fa-money-bill-wave"></i>
            </div>
            Honor Guru
        </h2>
        <p class="text-sm font-bold text-slate-400 mt-1 ml-14">
            Rekapitulasi bisyaroh guru per bulan, lengkap dengan pemindaian barcode saat penerimaan.
        </p>
    </div>
    <div class="bg-white px-4 py-2 rounded-xl border border-slate-200 text-sm font-bold shadow-sm text-slate-600">
        <i class="fas fa-clock text-emerald-500 mr-1.5"></i> TA: {{ $periodeAktif->tahun_ajaran }}
    </div>
</div>

@if(session('sukses'))
<div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl mb-6 flex items-center shadow-sm">
    <i class="fas fa-check-circle text-xl mr-3"></i>
    <span class="font-bold text-sm">{{ session('sukses') }}</span>
</div>
@endif

@if(session('error'))
<div class="bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-xl mb-6 flex items-center shadow-sm">
    <i class="fas fa-circle-xmark text-xl mr-3"></i>
    <span class="font-bold text-sm">{{ session('error') }}</span>
</div>
@endif

@if(auth()->user()->can('akses_honor_proses') || auth()->user()->can('akses_honor_konfigurasi'))
<div class="mb-6 bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
    <button type="button" onclick="lipatHonorConfig()" class="w-full bg-slate-50 border-b border-slate-100 p-4 flex justify-between items-center text-left">
        <h3 class="text-sm font-black text-slate-700 uppercase tracking-widest"><i class="fas fa-cog text-emerald-500 mr-2"></i>Mulai / Konfigurasi Periode Honor</h3>
        <i id="ikon-config-honor" class="fas fa-chevron-down text-slate-400 transition-transform duration-300"></i>
    </button>
    <div id="isi-config-honor" class="hidden">
        @can('akses_honor_proses')
        <form action="{{ route('honor.hitung') }}" method="POST" class="p-5">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Bulan</label>
                    <select name="bulan" class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 block p-3 outline-none transition-all font-medium cursor-pointer">
                        @foreach($bulanIndonesia as $no => $nama)
                        <option value="{{ $no }}" {{ ($no == now()->month) ? 'selected' : '' }}>{{ $nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Tahun</label>
                    <input type="number" name="tahun" value="{{ now()->year }}" min="2000" max="2100" class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 block p-3 outline-none transition-all font-medium">
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white font-bold py-3 px-4 rounded-xl transition-all shadow-[0_4px_15px_-3px_rgba(16,185,129,0.4)] flex items-center justify-center">
                        <i class="fas fa-calculator mr-2"></i> Hitung Honor Bulan Ini
                    </button>
                </div>
            </div>
            <p class="text-[11px] font-semibold text-slate-400 mt-3">
                <i class="fas fa-info-circle text-sky-400 mr-1"></i>
                Pastikan konfigurasi (tarif, status guru, tunjangan struktural) sudah diatur di halaman Konfigurasi. Jika belum, hitung akan ditolak.
            </p>
        </form>
        @endcan
        <div class="px-5 pb-5 flex justify-end">
            @can('akses_honor_konfigurasi')
            <a href="{{ route('honor.konfigurasi') }}" class="inline-flex items-center justify-center px-5 py-2.5 bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white border border-indigo-200 hover:border-indigo-600 font-bold text-sm rounded-xl transition-all shadow-sm">
                <i class="fas fa-sliders-h mr-2"></i> Atur Konfigurasi Honor
            </a>
            @endcan
        </div>
    </div>
</div>
<script>
    function lipatHonorConfig() {
        var isi = document.getElementById('isi-config-honor');
        var ikon = document.getElementById('ikon-config-honor');
        if (!isi) return;
        var membuka = isi.classList.contains('hidden');
        isi.classList.toggle('hidden');
        if (ikon) ikon.style.transform = membuka ? 'rotate(180deg)' : 'rotate(0deg)';
    }
</script>
@endif

<div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="bg-slate-50 border-b border-slate-100 p-4 flex justify-between items-center">
        <h3 class="text-sm font-black text-slate-700 uppercase tracking-widest"><i class="fas fa-list text-emerald-500 mr-2"></i>Riwayat Periode Honor</h3>
        <span class="bg-emerald-100 text-emerald-700 text-[10px] font-black px-2 py-1 rounded-md">{{ count($honorPeriodeList) }} Periode</span>
    </div>

    <div class="p-0">
        @forelse($honorPeriodeList as $hp)
            @php
                $totalBayar = $hp->details->sum('total');
                $sudahDiterima = $hp->details->where('is_diterima', true)->count();
                $butuhPenerimaan = $hp->details->where('butuh_penerimaan', true)->count();
                $totalGuru = $hp->details->count();
            @endphp
            <div class="p-5 border-b border-slate-100 hover:bg-slate-50/50 transition-colors flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 bg-emerald-50 rounded-xl flex flex-col items-center justify-center border border-emerald-100 shrink-0">
                        <span class="text-[9px] font-bold text-emerald-500 uppercase leading-none mb-1">{{ $hp->bulan }}</span>
                        <span class="text-lg font-black text-emerald-700 leading-none">{{ $hp->tahun }}</span>
                    </div>
                    <div>
                        <h4 class="text-base font-black text-slate-800">
                            {{ $bulanIndonesia[$hp->bulan] ?? $hp->bulan }} {{ $hp->tahun }}
                        </h4>
                        <div class="flex flex-wrap items-center gap-3 mt-1.5">
                            @php
                                $warnaStatus = [
                                    'draft'    => 'bg-amber-100 text-amber-700 border-amber-200',
                                    'final'    => 'bg-sky-100 text-sky-700 border-sky-200',
                                    'terbayar' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                                ];
                                $labelStatus = ['draft' => 'Draft', 'final' => 'Final', 'terbayar' => 'Terbayar'];
                            @endphp
                            <span class="text-[10px] font-black px-2 py-1 rounded-md border {{ $warnaStatus[$hp->status] ?? $warnaStatus['draft'] }}">
                                <i class="fas {{ $hp->status === 'final' ? 'fa-lock' : ($hp->status === 'terbayar' ? 'fa-circle-check' : 'fa-pen') }} mr-1"></i>
                                {{ $labelStatus[$hp->status] ?? $hp->status }}
                            </span>
                            <span class="text-[11px] font-bold text-slate-500 flex items-center">
                                <i class="fas fa-users text-emerald-400 mr-1.5"></i> {{ $totalGuru }} Guru
                            </span>
                            <span class="text-[11px] font-bold text-slate-500 flex items-center">
                                <i class="fas fa-circle-check text-emerald-400 mr-1.5"></i> {{ $butuhPenerimaan > 0 ? $sudahDiterima . '/' . $butuhPenerimaan : '—' }} Diterima
                            </span>
                            <span class="text-[11px] font-black text-emerald-600 flex items-center">
                                <i class="fas fa-coins mr-1.5"></i> Rp {{ number_format($totalBayar, 0, ',', '.') }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="shrink-0 w-full sm:w-auto flex flex-col sm:flex-row gap-2 mt-3 sm:mt-0">
                    @can('akses_honor_proses')
                    <a href="{{ route('honor.rekap', $hp->id) }}" class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2.5 bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white border border-indigo-200 hover:border-indigo-600 font-bold text-xs rounded-xl transition-all">
                        <i class="fas fa-table mr-2"></i> Rekap
                    </a>
                    @endcan
                    @if($hp->status === 'final' && auth()->user()->can('akses_honor_scan'))
                    <a href="{{ route('honor.scan') }}" class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2.5 bg-emerald-50 text-emerald-600 hover:bg-emerald-500 hover:text-white border border-emerald-200 hover:border-emerald-500 font-bold text-xs rounded-xl transition-all">
                        <i class="fas fa-qrcode mr-2"></i> Scan Penerimaan
                    </a>
                    @endif
                </div>
            </div>
        @empty
            <div class="p-10 flex flex-col items-center justify-center text-center">
                <div class="w-16 h-16 bg-slate-50 text-slate-300 rounded-full flex items-center justify-center mb-4 text-3xl shadow-inner"><i class="fas fa-hand-holding-dollar"></i></div>
                <h4 class="text-sm font-black text-slate-700">Belum Ada Periode Honor</h4>
                <p class="text-xs font-medium text-slate-400 mt-1">Atur konfigurasi lalu hitung honor di menu Konfigurasi Honor.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection