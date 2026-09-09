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
    <div class="flex flex-wrap items-center gap-2">
        <a href="{{ route('honor.index') }}" class="inline-flex items-center justify-center px-4 py-2.5 bg-slate-50 text-slate-600 hover:bg-slate-600 hover:text-white border border-slate-200 hover:border-slate-600 font-bold text-xs rounded-xl transition-all shadow-sm">
            <i class="fas fa-arrow-left mr-2"></i> Kembali
        </a>
        @if($periodeHonor->status !== 'draft' && auth()->user()->can('akses_honor'))
        <a href="{{ route('honor.slip', $periodeHonor->id) }}" class="inline-flex items-center justify-center px-4 py-2.5 bg-violet-50 text-violet-600 hover:bg-violet-600 hover:text-white border border-violet-200 hover:border-violet-600 font-bold text-xs rounded-xl transition-all shadow-sm">
            <i class="fas fa-file-pdf mr-2"></i> Download Slip PDF
        </a>
        @endif
        @if($periodeHonor->status === 'draft')
        @can('akses_honor_final')
        <form action="{{ route('honor.final', $periodeHonor->id) }}" method="POST" onsubmit="return confirm('Finalkan rekap ini? Setelah difinalkan, QR code muncul dan penerimaan bisa dipindai.');">
            @csrf
            <button type="submit" class="inline-flex items-center justify-center px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white font-bold text-xs rounded-xl transition-all shadow-sm">
                <i class="fas fa-lock mr-2"></i> Finalkan Rekap
            </button>
        </form>
        @endcan
        @else
        @can('akses_honor_final')
        <form action="{{ route('honor.buka', $periodeHonor->id) }}" method="POST" onsubmit="return confirm('Buka kembali rekap ini menjadi Draft? Perhitungan bisa diubah lalu difinalkan lagi.');">
            @csrf
            <button type="submit" class="inline-flex items-center justify-center px-4 py-2.5 bg-orange-50 text-orange-600 hover:bg-orange-600 hover:text-white border border-orange-200 hover:border-orange-600 font-bold text-xs rounded-xl transition-all shadow-sm">
                <i class="fas fa-lock-open mr-2"></i> Buka Kembali
            </button>
        </form>
        @endcan
        @can('akses_honor_scan')
        <a href="{{ route('honor.scan') }}" class="inline-flex items-center justify-center px-4 py-2.5 bg-emerald-50 text-emerald-600 hover:bg-emerald-600 hover:text-white border border-emerald-200 hover:border-emerald-600 font-bold text-xs rounded-xl transition-all shadow-sm">
            <i class="fas fa-qrcode mr-2"></i> Scan Penerimaan
        </a>
        @endcan
        @endif
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
    <i class="fas fa-exclamation-circle text-xl mr-3"></i>
    <span class="font-bold text-sm">{{ session('error') }}</span>
</div>
@endif

@php
    $grandTotal = $periodeHonor->details->sum('total');
    $sudahDiterima = $periodeHonor->details->where('is_diterima', true)->count();
    $butuhPenerimaan = $periodeHonor->details->where('butuh_penerimaan', true)->count();
    $totalGuru = $periodeHonor->details->count();
    $warnaStatus = [
        'draft'    => 'bg-amber-100 text-amber-700 border-amber-200',
        'final'    => 'bg-sky-100 text-sky-700 border-sky-200',
        'terbayar' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
    ];
    $labelStatus = ['draft' => 'Draft', 'final' => 'Final', 'terbayar' => 'Terbayar'];
@endphp

{{-- Ringkasan --}}
<div class="grid grid-cols-2 gap-3 md:grid-cols-4 md:gap-4 mb-6">
    <div class="bg-white rounded-2xl border border-slate-200 p-3 md:p-4 shadow-sm">
        <div class="flex items-center gap-2.5 md:gap-3">
            <div class="w-8 h-8 md:w-10 md:h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0"><i class="fas fa-users text-sm md:text-base"></i></div>
            <div class="min-w-0">
                <p class="text-[9px] md:text-[10px] font-black text-slate-400 uppercase tracking-wider">Guru</p>
                <p class="text-base md:text-xl font-black text-slate-800">{{ $totalGuru }}</p>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-2xl border border-slate-200 p-3 md:p-4 shadow-sm">
        <div class="flex items-center gap-2.5 md:gap-3">
            <div class="w-8 h-8 md:w-10 md:h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0"><i class="fas fa-money-bill-wave text-sm md:text-base"></i></div>
            <div class="min-w-0">
                <p class="text-[9px] md:text-[10px] font-black text-slate-400 uppercase tracking-wider">Total Honor</p>
                <p class="text-base md:text-xl font-black text-slate-800">Rp {{ number_format($grandTotal, 0, ',', '.') }}</p>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-2xl border border-slate-200 p-3 md:p-4 shadow-sm">
        <div class="flex items-center gap-2.5 md:gap-3">
            <div class="w-8 h-8 md:w-10 md:h-10 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center shrink-0"><i class="fas fa-circle-check text-sm md:text-base"></i></div>
            <div class="min-w-0">
                <p class="text-[9px] md:text-[10px] font-black text-slate-400 uppercase tracking-wider">Diterima</p>
                <p class="text-base md:text-xl font-black text-slate-800">{{ $butuhPenerimaan > 0 ? $sudahDiterima . '/' . $butuhPenerimaan : '—' }}</p>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-2xl border border-slate-200 p-3 md:p-4 shadow-sm">
        <div class="flex items-center gap-2.5 md:gap-3">
            <div class="w-8 h-8 md:w-10 md:h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0"><i class="fas fa-tag text-sm md:text-base"></i></div>
            <div class="min-w-0">
                <p class="text-[9px] md:text-[10px] font-black text-slate-400 uppercase tracking-wider">Status</p>
                <span class="inline-block mt-0.5 md:mt-1 text-[10px] font-black px-2 py-1 rounded-md border {{ $warnaStatus[$periodeHonor->status] ?? $warnaStatus['draft'] }}">
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
        @if($periodeHonor->status === 'draft')
        <span class="text-[10px] font-black px-2 py-1 rounded-md border bg-amber-100 text-amber-700 border-amber-200">Draft — nominal bisa diedit</span>
        @endif
    </div>
    <div class="hidden md:block overflow-x-auto">
        <table class="w-full text-sm whitespace-nowrap">
            <thead class="bg-slate-50 text-[10px] uppercase text-slate-400 font-black">
                <tr>
                    <th class="px-3 py-3 text-left">No</th>
                    <th class="px-3 py-3 text-left">Nama Guru</th>
                    <th class="px-3 py-3 text-center" title="Piket">Piket</th>
                    <th class="px-3 py-3 text-center" title="Realita Jam">Realita</th>
                    <th class="px-3 py-3 text-right">Honor Pokok</th>
                    <th class="px-3 py-3 text-right">Struktural</th>
                    <th class="px-3 py-3 text-right">Wali Kelas</th>
                    <th class="px-3 py-3 text-right">Transport</th>
                    <th class="px-3 py-3 text-right">Honor Piket</th>
                    <th class="px-3 py-3 text-right">Total</th>
                    <th class="px-3 py-3 text-center">Status</th>
                    <th class="px-3 py-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($periodeHonor->details as $i => $d)
                <tr class="hover:bg-slate-50/60">
                    <td class="px-3 py-2.5 text-[11px] font-bold text-slate-400">{{ $i + 1 }}</td>
                    <td class="px-3 py-2.5">
                        <span class="font-bold text-slate-700">{{ $d->guru->nama_guru }}</span>
                    </td>
                    <td class="px-3 py-2.5 text-center font-semibold text-slate-600">{{ $d->piket_jam }}</td>
                    <td class="px-3 py-2.5 text-center font-black text-emerald-600">{{ $d->realita_jam }}</td>
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
                        @elseif(!$d->butuh_penerimaan)
                            <span class="text-[10px] font-black px-2 py-1 rounded-md bg-slate-100 text-slate-500">
                                <i class="fas fa-ban mr-1"></i> Tanpa Penerimaan
                            </span>
                        @else
                            <span class="text-[10px] font-black px-2 py-1 rounded-md bg-slate-100 text-slate-500">
                                <i class="fas fa-hourglass-half mr-1"></i> Menunggu
                            </span>
                        @endif
                    </td>
                    <td class="px-3 py-2.5 text-center">
                        @if($periodeHonor->status === 'draft' && auth()->user()->can('akses_honor_proses'))
                        <button type="button"
                            onclick="bukaModalEdit({{ $d->id }}, '{{ js_q($d->guru->nama_guru) }}', {{ $d->piket_jam }}, {{ $d->realita_jam }}, {{ $d->honor_pokok }}, {{ $d->tunjangan_struktural }}, {{ $d->tunjangan_wali_kelas }}, {{ $d->transport }}, {{ $d->honor_piket }}, {{ $d->total }}, {{ $d->is_diterima ? 'true' : 'false' }})"
                            class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 hover:bg-amber-500 hover:text-white transition flex items-center justify-center border border-amber-200 shadow-sm" title="Edit Honor">
                            <i class="fas fa-pen text-xs"></i>
                        </button>
                        @else
                        <span class="inline-flex w-8 h-8 rounded-lg bg-slate-50 text-slate-300 items-center justify-center border border-slate-100" title="Terkunci — buka kembali ke Draft untuk mengedit">
                            <i class="fas fa-lock text-xs"></i>
                        </span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="12" class="px-4 py-10 text-center">
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
                    <td colspan="9" class="px-3 py-3 text-right text-[10px] font-black text-slate-400 uppercase tracking-wider">Total Keseluruhan</td>
                    <td class="px-3 py-3 text-right font-black text-emerald-700">Rp {{ number_format($grandTotal, 0, ',', '.') }}</td>
                    <td class="px-3 py-3" colspan="2"></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>

    <div class="md:hidden overflow-x-auto">
        <table class="w-full text-sm whitespace-nowrap">
            <thead class="bg-slate-50 text-[10px] uppercase text-slate-400 font-black">
                <tr>
                    <th class="px-3 py-3 text-left">No</th>
                    <th class="px-3 py-3 text-left">Nama Guru</th>
                    <th class="px-3 py-3 text-right">Total</th>
                    <th class="px-3 py-3 text-center">Status</th>
                    <th class="px-3 py-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($periodeHonor->details as $i => $d)
                <tr class="hover:bg-slate-50/60">
                    <td class="px-3 py-2.5 text-[11px] font-bold text-slate-400">{{ $i + 1 }}</td>
                    <td class="px-3 py-2.5">
                        <span class="font-bold text-slate-700">{{ $d->guru->nama_guru }}</span>
                    </td>
                    <td class="px-3 py-2.5 text-right font-black text-emerald-700 whitespace-nowrap">Rp {{ number_format($d->total, 0, ',', '.') }}</td>
                    <td class="px-3 py-2.5 text-center">
                        @if($d->is_diterima)
                            <span class="text-[10px] font-black px-2 py-1 rounded-md bg-emerald-100 text-emerald-700 whitespace-nowrap">
                                <i class="fas fa-circle-check mr-1"></i> Diterima
                            </span>
                        @elseif(!$d->butuh_penerimaan)
                            <span class="text-[10px] font-black px-2 py-1 rounded-md bg-slate-100 text-slate-500 whitespace-nowrap">
                                <i class="fas fa-ban mr-1"></i> Tanpa
                            </span>
                        @else
                            <span class="text-[10px] font-black px-2 py-1 rounded-md bg-slate-100 text-slate-500 whitespace-nowrap">
                                <i class="fas fa-hourglass-half mr-1"></i> Menunggu
                            </span>
                        @endif
                    </td>
                    <td class="px-3 py-2.5 text-center">
                        @if($periodeHonor->status === 'draft' && auth()->user()->can('akses_honor_proses'))
                        <button type="button"
                            onclick="bukaModalEdit({{ $d->id }}, '{{ js_q($d->guru->nama_guru) }}', {{ $d->piket_jam }}, {{ $d->realita_jam }}, {{ $d->honor_pokok }}, {{ $d->tunjangan_struktural }}, {{ $d->tunjangan_wali_kelas }}, {{ $d->transport }}, {{ $d->honor_piket }}, {{ $d->total }}, {{ $d->is_diterima ? 'true' : 'false' }})"
                            class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 hover:bg-amber-500 hover:text-white transition flex items-center justify-center border border-amber-200 shadow-sm" title="Edit Honor">
                            <i class="fas fa-pen text-xs"></i>
                        </button>
                        @else
                        <span class="inline-flex w-8 h-8 rounded-lg bg-slate-50 text-slate-300 items-center justify-center border border-slate-100" title="Terkunci — buka kembali ke Draft untuk mengedit">
                            <i class="fas fa-lock text-xs"></i>
                        </span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-4 py-10 text-center">
                        <div class="flex flex-col items-center">
                            <div class="w-16 h-16 bg-slate-50 text-slate-300 rounded-full flex items-center justify-center mb-4 text-3xl shadow-inner"><i class="fas fa-inbox"></i></div>
                            <h4 class="text-sm font-black text-slate-700">Belum Ada Data Honor</h4>
                            <p class="text-xs font-medium text-slate-400 mt-1">Periode ini belum dihitung.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
            @if($periodeHonor->details->count() > 0)
            <tfoot class="bg-slate-50 border-t-2 border-slate-100">
                <tr>
                    <td colspan="3" class="px-3 py-3 text-right text-[10px] font-black text-slate-400 uppercase tracking-wider">Total Keseluruhan</td>
                    <td class="px-3 py-3 text-right font-black text-emerald-700 whitespace-nowrap">Rp {{ number_format($grandTotal, 0, ',', '.') }}</td>
                    <td class="px-3 py-3"></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>

{{-- Modal Edit Honor --}}
@if($periodeHonor->status === 'draft' && $periodeHonor->details->count() > 0 && auth()->user()->can('akses_honor_proses'))
<div id="modal-edit-honor" class="hidden fixed inset-0 bg-gray-900 bg-opacity-60 overflow-y-auto h-full w-full z-50 flex items-center justify-center backdrop-blur-sm transition-opacity">
    <div class="relative mx-auto p-5 border w-11/12 max-w-sm shadow-2xl rounded-2xl bg-white transform transition-all">
        <div class="absolute top-0 right-0 pt-4 pr-4">
            <button type="button" onclick="tutupModalEdit()" class="text-gray-400 hover:text-gray-600 transition">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <div class="mt-2 text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-emerald-100 mb-3 shadow-inner">
                <i class="fas fa-user-tie text-xl text-emerald-600"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-800 leading-tight mb-1" id="edit-nama-guru">Nama Guru</h3>
            <p class="text-xs text-gray-500 mb-4 border-b pb-4" id="edit-subtitle">Edit Nominal Honor</p>

            <form id="form-edit-honor" method="POST" action="{{ route('honor.detail.update', ['id' => 0]) }}" onsubmit="return stripRupiahForm(this)">
                @csrf
                <div class="grid grid-cols-2 gap-3 text-center mb-5">
                    <div class="bg-slate-50 p-3 rounded-xl border border-slate-100 shadow-sm">
                        <span class="block text-[10px] font-bold text-slate-400 uppercase">Piket</span>
                        <span class="block text-xl font-black text-slate-800 mt-1" id="edit-piket">0</span>
                    </div>
                    <div class="bg-emerald-50 p-3 rounded-xl border border-emerald-100 shadow-sm">
                        <span class="block text-[10px] font-bold text-emerald-600 uppercase">Realita</span>
                        <span class="block text-xl font-black text-emerald-700 mt-1" id="edit-realita">0</span>
                    </div>
                </div>

                <div class="space-y-3 text-left">
                    @php
                        $fieldHonor = [
                            'honor_pokok'          => 'Honor Pokok',
                            'tunjangan_struktural' => 'Tunjangan Struktural',
                            'tunjangan_wali_kelas' => 'Tunjangan Wali Kelas',
                            'transport'            => 'Transport',
                            'honor_piket'          => 'Honor Piket',
                        ];
                    @endphp
                    @foreach($fieldHonor as $nama => $label)
                    <div>
                        <label for="edit-{{ $nama }}" class="block text-[10px] font-black text-slate-400 uppercase tracking-wider mb-1">{{ $label }}</label>
                        <input type="text" inputmode="numeric" id="edit-{{ $nama }}" name="{{ $nama }}" value="0" maxlength="15" class="js-rupiah w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 block p-2.5 outline-none transition-all font-semibold" required>
                    </div>
                    @endforeach

                    <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-3 flex items-center justify-between mt-1">
                        <span class="text-[10px] font-black text-emerald-700 uppercase tracking-wider">Total</span>
                        <span class="text-lg font-black text-emerald-700">Rp <span id="edit-total">0</span></span>
                    </div>

                    <div class="flex items-center justify-between bg-slate-50 border border-slate-100 rounded-xl p-3">
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-wider">Status</span>
                        <span id="edit-status"></span>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2 mt-5">
                    <button type="button" onclick="tutupModalEdit()" class="py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-xl shadow-sm transition duration-200 text-sm">
                        Batal
                    </button>
                    <button type="submit" class="py-2.5 bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white font-bold rounded-xl shadow-sm transition duration-200 text-sm">
                        <i class="fas fa-save mr-1"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    var baseEdit = "{{ route('honor.detail.update', ['id' => 0]) }}";

    function formatRibuan(val) {
        if (val === '' || val === null || val === undefined) return '0';
        var angka = String(val).replace(/[^\d]/g, '');
        if (angka === '') return '0';
        return parseInt(angka, 10).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    function hitungTotalEdit() {
        var ids = ['edit-honor_pokok', 'edit-tunjangan_struktural', 'edit-tunjangan_wali_kelas', 'edit-transport', 'edit-honor_piket'];
        var t = 0;
        ids.forEach(function (id) {
            var el = document.getElementById(id);
            t += parseInt(el.value.replace(/[^\d]/g, ''), 10) || 0;
        });
        document.getElementById('edit-total').innerText = formatRibuan(t);
    }

    function bukaModalEdit(id, nama, piket, realita, pokok, struktural, wali, transport, hp, total, diterima) {
        document.getElementById('modal-edit-honor').classList.remove('hidden');
        document.getElementById('edit-nama-guru').innerText = nama;
        document.getElementById('edit-subtitle').innerText = 'Edit Nominal Honor';
        document.getElementById('edit-piket').innerText = piket;
        document.getElementById('edit-realita').innerText = realita;

        document.getElementById('edit-honor_pokok').value = formatRibuan(pokok);
        document.getElementById('edit-tunjangan_struktural').value = formatRibuan(struktural);
        document.getElementById('edit-tunjangan_wali_kelas').value = formatRibuan(wali);
        document.getElementById('edit-transport').value = formatRibuan(transport);
        document.getElementById('edit-honor_piket').value = formatRibuan(hp);
        hitungTotalEdit();

        var st = document.getElementById('edit-status');
        st.innerHTML = diterima
            ? '<span class="text-[10px] font-black px-2 py-1 rounded-md bg-emerald-100 text-emerald-700"><i class="fas fa-circle-check mr-1"></i> Diterima</span>'
            : '<span class="text-[10px] font-black px-2 py-1 rounded-md bg-slate-100 text-slate-500"><i class="fas fa-hourglass-half mr-1"></i> Menunggu</span>';

        document.getElementById('form-edit-honor').action = baseEdit.slice(0, -1) + id;
    }

    function tutupModalEdit() {
        document.getElementById('modal-edit-honor').classList.add('hidden');
    }

    function stripRupiahForm(form) {
        form.querySelectorAll('.js-rupiah').forEach(function (el) {
            el.value = el.value.replace(/\./g, '');
        });
        return true;
    }

    var fieldEdit = ['honor_pokok', 'tunjangan_struktural', 'tunjangan_wali_kelas', 'transport', 'honor_piket'];
    fieldEdit.forEach(function (nama) {
        var el = document.getElementById('edit-' + nama);
        el.addEventListener('input', function () {
            this.value = formatRibuan(this.value);
            hitungTotalEdit();
        });
    });
</script>
@endif
@endsection