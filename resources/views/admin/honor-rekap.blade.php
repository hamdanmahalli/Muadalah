@extends('layouts.app')

@section('title', 'Rekap Honor Guru')

@section('content')
@php
    $k = $periodeHonor->konfigurasi;
@endphp
<div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
    <div>
        <h2 class="text-2xl font-black text-slate-800 tracking-tight flex items-center">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600 mr-3 shadow-inner">
                <i class="fas fa-table"></i>
            </div>
            Rekap Honor
        </h2>
        <p class="text-sm font-bold text-slate-400 mt-1 ml-14">
            {{ $bulanIndonesia[$periodeHonor->bulan] ?? $periodeHonor->bulan }} {{ $periodeHonor->tahun }} · TA {{ $k?->periode?->tahun_ajaran ?? '-' }}
        </p>
    </div>
    <div class="flex flex-col sm:flex-row gap-2">
        <a href="{{ route('honor.index') }}" class="inline-flex items-center justify-center px-4 py-2.5 bg-slate-50 text-slate-600 hover:bg-slate-600 hover:text-white border border-slate-200 hover:border-slate-600 font-bold text-xs rounded-xl transition-all shadow-sm">
            <i class="fas fa-arrow-left mr-2"></i> Kembali
        </a>
        @if($periodeHonor->status === 'draft')
        <form action="{{ route('honor.final', $periodeHonor->id) }}" method="POST" onsubmit="return confirm('Finalkan rekap ini? Setelah difinalkan, QR code muncul dan penerimaan bisa dipindai.');">
            @csrf
            <button type="submit" class="inline-flex items-center justify-center px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white font-bold text-xs rounded-xl transition-all shadow-sm">
                <i class="fas fa-lock mr-2"></i> Finalkan Rekap
            </button>
        </form>
        @else
        <a href="{{ route('honor.scan') }}" class="inline-flex items-center justify-center px-4 py-2.5 bg-emerald-50 text-emerald-600 hover:bg-emerald-600 hover:text-white border border-emerald-200 hover:border-emerald-600 font-bold text-xs rounded-xl transition-all shadow-sm">
            <i class="fas fa-qrcode mr-2"></i> Scan Penerimaan
        </a>
        @endif
    </div>
</div>

@if(session('sukses'))
<div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl mb-6 flex items-center shadow-sm">
    <i class="fas fa-check-circle text-xl mr-3"></i>
    <span class="font-bold text-sm">{{ session('sukses') }}</span>
</div>
@endif

@php
    $grandTotal = $periodeHonor->details->sum('total');
    $sudahDiterima = $periodeHonor->details->where('is_diterima', true)->count();
    $totalGuru = $periodeHonor->details->count();
    $warnaStatus = [
        'draft'    => 'bg-amber-100 text-amber-700 border-amber-200',
        'final'    => 'bg-sky-100 text-sky-700 border-sky-200',
        'terbayar' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
    ];
    $labelStatus = ['draft' => 'Draft', 'final' => 'Final', 'terbayar' => 'Terbayar'];
@endphp

{{-- Ringkasan --}}
<div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center"><i class="fas fa-users"></i></div>
            <div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-wider">Total Guru</p>
                <p class="text-xl font-black text-slate-800">{{ $totalGuru }}</p>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center"><i class="fas fa-money-bill-wave"></i></div>
            <div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-wider">Total Honor</p>
                <p class="text-xl font-black text-slate-800">Rp {{ number_format($grandTotal, 0, ',', '.') }}</p>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center"><i class="fas fa-circle-check"></i></div>
            <div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-wider">Sudah Diterima</p>
                <p class="text-xl font-black text-slate-800">{{ $sudahDiterima }}/{{ $totalGuru }}</p>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center"><i class="fas fa-tag"></i></div>
            <div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-wider">Status</p>
                <span class="inline-block mt-1 text-[10px] font-black px-2 py-1 rounded-md border {{ $warnaStatus[$periodeHonor->status] ?? $warnaStatus['draft'] }}">
                    {{ $labelStatus[$periodeHonor->status] ?? $periodeHonor->status }}
                </span>
            </div>
        </div>
    </div>
</div>

{{-- Tabel Rekap --}}
<div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="bg-slate-50 border-b border-slate-100 p-4 flex justify-between items-center">
        <h3 class="text-sm font-black text-slate-700 uppercase tracking-widest"><i class="fas fa-list text-emerald-500 mr-2"></i>Rekap Bisyaroh Guru</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm whitespace-nowrap">
            <thead class="bg-slate-50 text-[10px] uppercase text-slate-400 font-black">
                <tr>
                    <th class="px-3 py-3 text-left">No</th>
                    <th class="px-3 py-3 text-left">Nama Guru</th>
                    <th class="px-3 py-3 text-center" title="Jam Wajib">Jam Wajib</th>
                    <th class="px-3 py-3 text-center" title="Alpa">A</th>
                    <th class="px-3 py-3 text-center" title="Izin">I</th>
                    <th class="px-3 py-3 text-center" title="Sakit">S</th>
                    <th class="px-3 py-3 text-center" title="Piket">Piket</th>
                    <th class="px-3 py-3 text-center" title="Realita Jam">Realita</th>
                    <th class="px-3 py-3 text-center" title="Persentase">%</th>
                    <th class="px-3 py-3 text-left">Keterangan</th>
                    <th class="px-3 py-3 text-right">Honor Pokok</th>
                    <th class="px-3 py-3 text-right">Struktural</th>
                    <th class="px-3 py-3 text-right">Wali</th>
                    <th class="px-3 py-3 text-right">Transport</th>
                    <th class="px-3 py-3 text-right">Honor Piket</th>
                    <th class="px-3 py-3 text-right">Total</th>
                    <th class="px-3 py-3 text-center">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($periodeHonor->details as $i => $d)
                @php
                    $warnaKet = [
                        'Sangat Baik' => 'bg-emerald-100 text-emerald-700',
                        'Baik' => 'bg-sky-100 text-sky-700',
                        'Cukup' => 'bg-amber-100 text-amber-700',
                        'Kurang' => 'bg-orange-100 text-orange-700',
                        'Sangat Kurang' => 'bg-rose-100 text-rose-700',
                    ];
                @endphp
                <tr class="hover:bg-slate-50/60">
                    <td class="px-3 py-2.5 text-[11px] font-bold text-slate-400">{{ $i + 1 }}</td>
                    <td class="px-3 py-2.5">
                        <span class="font-bold text-slate-700">{{ $d->guru->nama_guru }}</span>
                    </td>
                    <td class="px-3 py-2.5 text-center font-semibold text-slate-600">{{ $d->jam_wajib }}</td>
                    <td class="px-3 py-2.5 text-center font-semibold {{ $d->alpa > 0 ? 'text-rose-600' : 'text-slate-400' }}">{{ $d->alpa }}</td>
                    <td class="px-3 py-2.5 text-center font-semibold {{ $d->izin > 0 ? 'text-amber-600' : 'text-slate-400' }}">{{ $d->izin }}</td>
                    <td class="px-3 py-2.5 text-center font-semibold {{ $d->sakit > 0 ? 'text-orange-600' : 'text-slate-400' }}">{{ $d->sakit }}</td>
                    <td class="px-3 py-2.5 text-center font-semibold text-slate-600">{{ $d->piket_jam }}</td>
                    <td class="px-3 py-2.5 text-center font-black text-emerald-600">{{ $d->realita_jam }}</td>
                    <td class="px-3 py-2.5 text-center font-black text-slate-700">{{ number_format($d->persentase, 2, ',', '.') }}%</td>
                    <td class="px-3 py-2.5">
                        <span class="text-[10px] font-black px-2 py-1 rounded-md {{ $warnaKet[$d->keterangan] ?? 'bg-slate-100 text-slate-600' }}">{{ $d->keterangan }}</span>
                    </td>
                    <td class="px-3 py-2.5 text-right font-semibold text-slate-600">{{ number_format($d->honor_pokok, 0, ',', '.') }}</td>
                    <td class="px-3 py-2.5 text-right font-semibold {{ $d->tunjangan_struktural > 0 ? 'text-slate-700' : 'text-slate-300' }}">{{ number_format($d->tunjangan_struktural, 0, ',', '.') }}</td>
                    <td class="px-3 py-2.5 text-right font-semibold {{ $d->tunjangan_wali_kelas > 0 ? 'text-slate-700' : 'text-slate-300' }}">{{ number_format($d->tunjangan_wali_kelas, 0, ',', '.') }}</td>
                    <td class="px-3 py-2.5 text-right font-semibold {{ $d->transport > 0 ? 'text-slate-700' : 'text-slate-300' }}">{{ number_format($d->transport, 0, ',', '.') }}</td>
                    <td class="px-3 py-2.5 text-right font-semibold {{ $d->honor_piket > 0 ? 'text-slate-700' : 'text-slate-300' }}">{{ number_format($d->honor_piket, 0, ',', '.') }}</td>
                    <td class="px-3 py-2.5 text-right font-black text-emerald-700">Rp {{ number_format($d->total, 0, ',', '.') }}</td>
                    <td class="px-3 py-2.5 text-center">
                        @if($d->is_diterima)
                            <span class="text-[10px] font-black px-2 py-1 rounded-md bg-emerald-100 text-emerald-700">
                                <i class="fas fa-circle-check mr-1"></i> Diterima
                            </span>
                        @else
                            <span class="text-[10px] font-black px-2 py-1 rounded-md bg-slate-100 text-slate-500">
                                <i class="fas fa-hourglass-half mr-1"></i> Menunggu
                            </span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="17" class="px-4 py-10 text-center">
                        <div class="flex flex-col items-center">
                            <div class="w-16 h-16 bg-slate-50 text-slate-300 rounded-full flex items-center justify-center mb-4 text-3xl shadow-inner"><i class="fas fa-inbox"></i></div>
                            <h4 class="text-sm font-black text-slate-700">Belum Ada Data Honor</h4>
                            <p class="text-xs font-medium text-slate-400 mt-1">Periode ini belum dihitung. Tekan tombol Hitung Honor pada halaman utama.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
            @if($periodeHonor->details->count() > 0)
            <tfoot class="bg-slate-50 border-t-2 border-slate-100">
                <tr>
                    <td colspan="15" class="px-3 py-3 text-right text-[10px] font-black text-slate-400 uppercase tracking-wider">Total Keseluruhan</td>
                    <td class="px-3 py-3 text-right font-black text-emerald-700">Rp {{ number_format($grandTotal, 0, ',', '.') }}</td>
                    <td class="px-3 py-3"></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>
@endsection