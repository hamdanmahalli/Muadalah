@extends('layouts.app')

@section('title', 'Konfigurasi Honor')

@section('content')
<div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
    <div>
        <h2 class="text-2xl font-black text-slate-800 tracking-tight flex items-center">
            <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600 mr-3 shadow-inner">
                <i class="fas fa-sliders-h"></i>
            </div>
            Konfigurasi Honor
        </h2>
        <p class="text-sm font-bold text-slate-400 mt-1 ml-14">
            Atur tarif, status guru, dan tunjangan struktural untuk satu bulan tertentu.
        </p>
    </div>
    <a href="{{ route('honor.index') }}" class="inline-flex items-center justify-center px-5 py-2.5 bg-slate-50 text-slate-600 hover:bg-slate-600 hover:text-white border border-slate-200 hover:border-slate-600 font-bold text-sm rounded-xl transition-all shadow-sm">
        <i class="fas fa-arrow-left mr-2"></i> Kembali
    </a>
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

{{-- Salin dari bulan sebelumnya (hanya bila periode belum punya config & ada config lama) --}}
@if(!$config && $lastConfig)
@php
    $lastBulan = $bulanIndonesia[$lastConfig->bulan] ?? $lastConfig->bulan;
@endphp
<div class="mb-6 bg-indigo-50 border-2 border-dashed border-indigo-200 rounded-2xl p-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
    <div class="flex items-start gap-3">
        <div class="w-11 h-11 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center text-lg shrink-0"><i class="fas fa-copy"></i></div>
        <div>
            <h3 class="text-sm font-black text-indigo-800">Belum lama? Salin dari konfigurasi sebelumnya</h3>
            <p class="text-xs font-semibold text-indigo-600 mt-0.5">
                Tarif dasar, status guru, dari luar, override tarif, dan tunjangan jabatan akan disalin dari
                <span class="font-black">{{ $lastBulan }} {{ $lastConfig->tahun }}</span>. Bisa kamu sesuaikan setelahnya.
            </p>
        </div>
    </div>
    <form action="{{ route('honor.konfigurasi.salin') }}" method="POST" onsubmit="return confirm('Salin seluruh konfigurasi dari {{ $lastBulan }} {{ $lastConfig->tahun }} ke periode ini?');" class="shrink-0">
        @csrf
        <input type="hidden" name="bulan" value="{{ $bulan }}">
        <input type="hidden" name="tahun" value="{{ $tahun }}">
        <button type="submit" class="inline-flex items-center justify-center px-6 py-3 bg-indigo-600 hover:bg-indigo-700 active:scale-95 text-white font-bold text-sm rounded-xl transition-all shadow-[0_4px_15px_-3px_rgba(79,70,229,0.5)]">
            <i class="fas fa-copy mr-2"></i> Salin dari Bulan Sebelumnya
        </button>
    </form>
</div>
@endif

{{-- Pilih bulan/tahun --}}
<div class="mb-6 bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="bg-slate-50 border-b border-slate-100 p-4">
        <h3 class="text-sm font-black text-slate-700 uppercase tracking-widest"><i class="fas fa-calendar-alt text-indigo-500 mr-2"></i>Pilih Periode</h3>
    </div>
    <form method="GET" action="{{ route('honor.konfigurasi') }}" class="p-5">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Bulan</label>
                <select name="bulan" onchange="this.form.submit()" class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 block p-3 outline-none transition-all font-medium cursor-pointer">
                    @foreach($bulanIndonesia as $no => $nama)
                    <option value="{{ $no }}" {{ $no == $bulan ? 'selected' : '' }}>{{ $nama }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Tahun</label>
                <input type="number" name="tahun" value="{{ $tahun }}" min="2000" max="2100" onchange="this.form.submit()" class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 block p-3 outline-none transition-all font-medium">
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 active:scale-95 text-white font-bold py-3 px-4 rounded-xl transition-all shadow-[0_4px_15px_-3px_rgba(79,70,229,0.4)]">
                    <i class="fas fa-sync-alt mr-2"></i> Muat Periode
                </button>
            </div>
        </div>
    </form>
</div>

<form action="{{ route('honor.konfigurasi.simpan') }}" method="POST">
    @csrf
    <input type="hidden" name="bulan" value="{{ $bulan }}">
    <input type="hidden" name="tahun" value="{{ $tahun }}">

    {{-- Tarif Dasar --}}
    <div class="mb-6 bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="bg-slate-50 border-b border-slate-100 p-4">
            <h3 class="text-sm font-black text-slate-700 uppercase tracking-widest"><i class="fas fa-coins text-amber-500 mr-2"></i>Tarif Dasar (Rp)</h3>
        </div>
        <div class="p-5 grid grid-cols-2 sm:grid-cols-5 gap-4">
            @php
                $d = $config ?? null;
                $fields = [
                    'tarif_jam_normal' => ['label' => 'Honor / Jam (Tetap)', 'val' => $d?->tarif_jam_normal ?? 5000],
                    'tarif_jam_magang' => ['label' => 'Honor / Jam (Magang)', 'val' => $d?->tarif_jam_magang ?? 4000],
                    'tarif_piket'      => ['label' => 'Piket / Jam', 'val' => $d?->tarif_piket ?? 4000],
                    'tarif_transport'  => ['label' => 'Transport / km (Rp)', 'val' => $d?->tarif_transport ?? 5000],
                    'tarif_wali_kelas' => ['label' => 'Wali Kelas / Bln', 'val' => $d?->tarif_wali_kelas ?? 50000],
                ];
            @endphp
            @foreach($fields as $name => $f)
            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">{{ $f['label'] }}</label>
                <input type="number" name="{{ $name }}" value="{{ $f['val'] }}" min="0" step="500" class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 block p-3 outline-none transition-all font-medium">
            </div>
            @endforeach
        </div>
        <div class="px-5 pb-5">
            <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Catatan (Opsional)</label>
            <input type="text" name="catatan" value="{{ $d?->catatan ?? '' }}" placeholder="Contoh: Berlaku untuk Juli 2026" class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 block p-3 outline-none transition-all font-medium">
        </div>
    </div>

    {{-- Status per Guru --}}
    <div class="mb-6 bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="bg-slate-50 border-b border-slate-100 p-4 flex justify-between items-center">
            <h3 class="text-sm font-black text-slate-700 uppercase tracking-widest"><i class="fas fa-chalkboard-teacher text-sky-500 mr-2"></i>Status & Tunjangan Guru</h3>
            <span class="bg-sky-100 text-sky-700 text-[10px] font-black px-2 py-1 rounded-md">{{ count($gurus) }} Guru</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-[10px] uppercase text-slate-400 font-black">
                    <tr>
                        <th class="px-4 py-3 text-left w-8">No</th>
                        <th class="px-4 py-3 text-left">Nama Guru</th>
                        <th class="px-4 py-3 text-left">Status Honor</th>
                        <th class="px-4 py-3 text-center" title="Jarak rumah ke sekolah (diisi di Data Guru)">Jarak (km)</th>
                        <th class="px-4 py-3 text-left">Override Tarif (Rp)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($gurus as $i => $guru)
                    @php
                        $cfg = $guruConfigsExist[$guru->id] ?? null;
                    @endphp
                    <tr class="hover:bg-slate-50/60">
                        <td class="px-4 py-2.5 text-[11px] font-bold text-slate-400">{{ $i + 1 }}</td>
                        <td class="px-4 py-2.5">
                            <span class="font-bold text-slate-700">{{ $guru->nama_guru }}</span>
                            <span class="block text-[10px] font-semibold text-slate-400">NIG: {{ $guru->nig }}</span>
                        </td>
                        <td class="px-4 py-2.5">
                            <select name="guru_status[{{ $guru->id }}]" class="bg-slate-50 border border-slate-200 text-slate-800 text-xs rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 p-2 outline-none transition-all font-semibold cursor-pointer">
                                <option value="">-- Belum Atur --</option>
                                <option value="Tetap" {{ ($cfg?->status_honor ?? '') === 'Tetap' ? 'selected' : '' }}>Tetap</option>
                                <option value="Magang" {{ ($cfg?->status_honor ?? '') === 'Magang' ? 'selected' : '' }}>Magang</option>
                                <option value="Pengabdian" {{ ($cfg?->status_honor ?? '') === 'Pengabdian' ? 'selected' : '' }}>Pengabdian</option>
                            </select>
                        </td>
                        <td class="px-4 py-2.5 text-center">
                            <span class="text-xs font-bold px-2 py-0.5 rounded-lg {{ ($guru->jarak_km ?? 0) > 0 ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-400' }}">
                                {{ $guru->jarak_km ?? 0 }}
                            </span>
                        </td>
                        <td class="px-4 py-2.5">
                            <input type="number" name="guru_tarif[{{ $guru->id }}]" value="{{ $cfg?->tarif_override ?? '' }}" min="0" placeholder="Auto" class="w-28 bg-slate-50 border border-slate-200 text-slate-800 text-xs rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 p-2 outline-none transition-all font-semibold">
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Tunjangan Jabatan Struktural --}}
    <div class="mb-6 bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="bg-slate-50 border-b border-slate-100 p-4 flex justify-between items-center">
            <h3 class="text-sm font-black text-slate-700 uppercase tracking-widest"><i class="fas fa-briefcase text-violet-500 mr-2"></i>Tunjangan Jabatan Struktural</h3>
            <span class="bg-violet-100 text-violet-700 text-[10px] font-black px-2 py-1 rounded-md">{{ count($jabatans) }} Jabatan</span>
        </div>
        <div class="p-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($jabatans as $jabatan)
            @php
                $nominal = $strukturalExist[$jabatan->id]?->nominal ?? '';
            @endphp
            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">{{ $jabatan->nama_jabatan }}</label>
                <input type="number" name="jabatan_nominal[{{ $jabatan->id }}]" value="{{ $nominal }}" min="0" step="5000" placeholder="0" class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 block p-3 outline-none transition-all font-medium">
            </div>
            @endforeach
        </div>
    </div>

    <div class="sticky bottom-6 flex justify-end">
        <button type="submit" class="inline-flex items-center justify-center px-8 py-4 bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white font-bold text-sm rounded-2xl transition-all shadow-[0_8px_25px_-8px_rgba(16,185,129,0.6)]">
            <i class="fas fa-save mr-2"></i> Simpan Konfigurasi Honor
        </button>
    </div>
</form>
@endsection