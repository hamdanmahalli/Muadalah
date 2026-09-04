@extends('layouts.app')

@section('title', 'Input Nilai Siswa')

@section('content')
@if(in_array($mode, ['guru', 'panitia']))
<style>
    header, aside { display: none !important; }
    #btn-buka-sidebar { display: none !important; }
    main { padding: 0 !important; background-color: #f1f5f9 !important; }
    body { background-color: #f1f5f9 !important; }
</style>
<div class="max-w-md mx-auto bg-slate-100 min-h-[100dvh] flex flex-col relative font-sans">
    <div class="shrink-0 bg-gradient-to-br from-emerald-600 via-emerald-600 to-teal-700 px-4 pt-6 pb-5 relative z-10 overflow-hidden shadow-md">
        <div class="absolute -right-16 -top-16 w-56 h-56 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
        <div class="absolute -left-12 -bottom-20 w-48 h-48 bg-teal-400/20 rounded-full blur-2xl pointer-events-none"></div>
        <div class="relative z-10 flex items-center gap-3">
            <a href="{{ $mode === 'guru' ? '/dashboard-guru' : '/menu' }}" class="w-10 h-10 flex items-center justify-center bg-white/20 hover:bg-white/30 rounded-xl text-white transition active:scale-90">
                <i class="fas fa-arrow-left text-lg"></i>
            </a>
            <div class="text-white">
                <h1 class="font-extrabold text-lg leading-tight">Nilai</h1>
                <p class="text-[11px] text-emerald-100 font-medium">{{ $mode === 'guru' ? 'Input Nilai Harian' : 'Input Skor UTS/UAS' }}</p>
            </div>
        </div>
    </div>

    <div class="flex-1 p-4 overflow-y-auto pb-8" style="max-height: calc(100dvh - 92px);">
@else
<div class="flex justify-between items-center mb-6">
    <h2 class="text-2xl font-bold text-gray-800"><i class="fas fa-clipboard-list mr-2 text-indigo-600"></i> Input Nilai</h2>
</div>
@endif

@if($mode === 'guru')
<div class="bg-cyan-50 border border-cyan-200 text-cyan-800 px-4 py-3 rounded-xl mb-4 flex items-center shadow-sm">
    <i class="fas fa-user-graduate text-lg mr-3"></i>
    <div>
        <p class="font-bold text-sm">Mode: Nilai Harian</p>
        <p class="text-xs">Anda hanya dapat menginput <b>Nilai Harian</b> untuk kelas & mata pelajaran yang Anda ampu. Skor UTS/UAS diinput oleh panitia ujian.</p>
    </div>
</div>
@elseif($mode === 'panitia')
<div class="bg-indigo-50 border border-indigo-200 text-indigo-800 px-4 py-3 rounded-xl mb-4 flex items-center shadow-sm">
    <i class="fas fa-clipboard-check text-lg mr-3"></i>
    <div>
        <p class="font-bold text-sm">Mode: Skor UTS / UAS</p>
        <p class="text-xs">Anda hanya dapat menginput <b>Skor UTS & UAS</b>. Nilai Harian diinput oleh guru (Dewan Guru).</p>
    </div>
</div>
@else
<div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl mb-4 flex items-center shadow-sm">
    <i class="fas fa-user-shield text-lg mr-3"></i>
    <div>
        <p class="font-bold text-sm">Mode: Administrator / Tata Usaha</p>
        <p class="text-xs">Anda dapat menginput Nilai Harian, Skor UTS, dan Skor UAS sekaligus.</p>
    </div>
</div>
@endif

{{-- Toggle mode utk user dengan role ganda (Dewan Guru + Kepanitiaan) --}}
@if($bolehPilihMode)
<div class="bg-white border border-gray-200 rounded-xl shadow-sm p-1.5 mb-4 flex gap-1">
    <a href="{{ route('nilai.index', ['mode' => 'guru'] + request()->except('mode')) }}"
       class="flex-1 text-center px-3 py-2 rounded-lg text-sm font-semibold transition {{ $mode === 'guru' ? 'bg-emerald-600 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-100' }}">
        <i class="fas fa-user-graduate mr-1"></i> Nilai Harian
    </a>
    <a href="{{ route('nilai.index', ['mode' => 'panitia'] + request()->except('mode')) }}"
       class="flex-1 text-center px-3 py-2 rounded-lg text-sm font-semibold transition {{ $mode === 'panitia' ? 'bg-indigo-600 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-100' }}">
        <i class="fas fa-clipboard-check mr-1"></i> Skor UTS/UAS
    </a>
</div>
@endif

{{-- Panel pilih kolom: HANYA untuk Administrator/Pimpinan --}}
@if($bolehKontrol)
<div class="bg-white border border-gray-200 shadow-sm rounded-2xl overflow-hidden mb-6">
    <div class="bg-gradient-to-r from-violet-600 to-indigo-600 px-5 py-3 flex items-center">
        <i class="fas fa-sliders-h text-white mr-2"></i>
        <p class="text-white font-semibold text-sm">Tampilkan Kolom</p>
        <span class="ml-auto text-white/80 text-xs">Khusus Administrator / Pimpinan</span>
    </div>
    <form method="POST" action="{{ route('nilai.updateKolom') }}">
        @csrf
        <div class="p-5 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
            @foreach([
                'harian_uts' => 'Harian UTS',
                'skor_uts' => 'Skor UTS',
                'uts_akhir' => 'UTS Akhir',
                'harian_uas' => 'Harian UAS',
                'skor_uas' => 'Skor UAS',
                'uas_akhir' => 'UAS Akhir',
                'nilai_akhir' => 'Nilai Rapot',
                'predikat' => 'Predikat',
            ] as $key => $label)
            <label class="nilai-toggle flex items-center justify-between gap-3 rounded-xl border border-gray-200 bg-gray-50 px-3 py-2.5 cursor-pointer">
                <span class="text-sm font-medium text-gray-700">{{ $label }}</span>
                <span class="relative inline-flex items-center">
                    <input type="checkbox" name="{{ $key }}" value="1" {{ $kolomConfig->$key ? 'checked' : '' }}>
                    <i></i>
                </span>
            </label>
            @endforeach
        </div>
        <div class="px-5 pb-5 flex justify-end">
            <button type="submit" class="inline-flex items-center bg-violet-600 hover:bg-violet-700 text-white px-6 py-2.5 rounded-xl text-sm font-semibold shadow-sm transition">
                <i class="fas fa-check-circle mr-2"></i> Simpan Pengaturan
            </button>
        </div>
    </form>
    <style>
        .nilai-toggle input { position: absolute; opacity: 0; width: 0; height: 0; }
        .nilai-toggle i {
            display: inline-block; width: 40px; height: 24px; border-radius: 9999px;
            background: #d1d5db; position: relative; transition: background .2s; flex: none;
        }
        .nilai-toggle i::after {
            content: ''; position: absolute; left: 2px; top: 2px; width: 20px; height: 20px;
            background: #fff; border-radius: 9999px; box-shadow: 0 1px 3px rgba(0,0,0,.3);
            transition: transform .2s;
        }
        .nilai-toggle input:checked + i { background: #7c3aed; }
        .nilai-toggle input:checked + i::after { transform: translateX(16px); }
    </style>
</div>
@endif

<form method="GET" action="{{ route('nilai.index') }}" class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6">
    <input type="hidden" name="mode" value="{{ $mode }}">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-3 text-sm">
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Periode</label>
            <select name="periode_id" onchange="this.form.submit()" class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-indigo-500 outline-none">
                @foreach($periodes as $p)
                    <option value="{{ $p->id }}" {{ $periodeId == $p->id ? 'selected' : '' }}>{{ $p->tahun_ajaran }} ({{ $p->semester }})</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Kelas</label>
            <select name="kelas_id" onchange="this.form.submit()" class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-indigo-500 outline-none">
                <option value="">- Pilih Kelas -</option>
                @foreach($kelas as $k)
                    <option value="{{ $k->id }}" {{ $kelasId == $k->id ? 'selected' : '' }}>{{ $k->nama_kelas }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Pelajaran</label>
            <select name="pelajaran_id" onchange="this.form.submit()" class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-indigo-500 outline-none">
                <option value="">- Pilih Pelajaran -</option>
                @foreach($pelajarans as $pl)
                    <option value="{{ $pl->id }}" {{ $pelajaranId == $pl->id ? 'selected' : '' }}>{{ $pl->nama_pelajaran }}</option>
                @endforeach
            </select>
        </div>
    </div>
</form>

@if($kelasId && $pelajaranId)
<form method="POST" action="{{ route('nilai.simpanMassal') }}">
    @csrf
    <input type="hidden" name="kelas_id" value="{{ $kelasId }}">
    <input type="hidden" name="pelajaran_id" value="{{ $pelajaranId }}">
    <input type="hidden" name="periode_id" value="{{ $periodeId }}">
    <input type="hidden" name="mode" value="{{ $mode }}">

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase w-14">Absen</th>
                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Nama Siswa</th>
                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase w-24">NIS</th>
                    @if($kolomConfig->harian_uts && $mode !== 'panitia')
                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase w-28">Harian UTS</th>
                    @endif
                    @if($kolomConfig->skor_uts && $mode !== 'guru')
                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase w-24">Skor UTS</th>
                    @endif
                    @if($kolomConfig->uts_akhir)
                    <th class="px-4 py-3 text-left text-xs font-bold text-indigo-500 uppercase w-28">UTS Akhir</th>
                    @endif
                    @if($kolomConfig->harian_uas && $mode !== 'panitia')
                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase w-28">Harian UAS</th>
                    @endif
                    @if($kolomConfig->skor_uas && $mode !== 'guru')
                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase w-24">Skor UAS</th>
                    @endif
                    @if($kolomConfig->uas_akhir)
                    <th class="px-4 py-3 text-left text-xs font-bold text-indigo-500 uppercase w-28">UAS Akhir</th>
                    @endif
                    @if($kolomConfig->nilai_akhir)
                    <th class="px-4 py-3 text-left text-xs font-bold text-emerald-600 uppercase w-28">Nilai Rapot</th>
                    @endif
                    @if($kolomConfig->predikat)
                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase w-20">Predikat</th>
                    @endif
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($siswas as $i => $s)
                @php $n = $nilaiMap[$s->id] ?? null; @endphp
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-2 text-gray-500 font-semibold">{{ $absenMap[$s->id] ?? $i+1 }}</td>
                    <td class="px-4 py-2 font-semibold text-gray-700">{{ $s->nama_siswa }}</td>
                    <td class="px-4 py-2 text-gray-500">{{ $s->nis }}</td>
                    @if($kolomConfig->harian_uts && $mode !== 'panitia')
                    <td class="px-2 py-2">
                        <input type="number" step="0.01" min="0" max="100" name="siswa[{{ $s->id }}][harian_uts]" value="{{ $n?->nilai_harian_uts }}" class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-indigo-500 outline-none">
                    </td>
                    @endif
                    @if($kolomConfig->skor_uts && $mode !== 'guru')
                    <td class="px-2 py-2">
                        <input type="number" step="0.01" min="0" max="100" name="siswa[{{ $s->id }}][skor_uts]" value="{{ $n?->skor_uts }}" class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-indigo-500 outline-none">
                    </td>
                    @endif
                    @if($kolomConfig->uts_akhir)
                    <td class="px-2 py-2 text-center">
                        <span class="font-bold text-indigo-600">{{ $n?->nilai_uts_akhir ?? '-' }}</span>
                    </td>
                    @endif
                    @if($kolomConfig->harian_uas)
                    <td class="px-2 py-2">
                        @if($mode !== 'panitia')
                        <input type="number" step="0.01" min="0" max="100" name="siswa[{{ $s->id }}][harian_uas]" value="{{ $n?->nilai_harian_uas }}" class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-indigo-500 outline-none">
                        @else
                        <span class="inline-block w-full text-center font-semibold text-gray-700">{{ $n?->nilai_harian_uas ?? '-' }}</span>
                        @endif
                    </td>
                    @endif
                    @if($kolomConfig->skor_uas)
                    <td class="px-2 py-2">
                        @if($mode !== 'guru')
                        <input type="number" step="0.01" min="0" max="100" name="siswa[{{ $s->id }}][skor_uas]" value="{{ $n?->skor_uas }}" class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-indigo-500 outline-none">
                        @else
                        <span class="inline-block w-full text-center font-semibold text-gray-700">{{ $n?->skor_uas ?? '-' }}</span>
                        @endif
                    </td>
                    @endif
                    @if($kolomConfig->uas_akhir)
                    <td class="px-2 py-2 text-center">
                        <span class="font-bold text-indigo-600">{{ $n?->nilai_uas_akhir ?? '-' }}</span>
                    </td>
                    @endif
                    @if($kolomConfig->nilai_akhir)
                    <td class="px-2 py-2 text-center">
                        <span class="font-bold text-emerald-600">{{ $n?->nilai_akhir ?? '-' }}</span>
                    </td>
                    @endif
                    @if($kolomConfig->predikat)
                    <td class="px-2 py-2 text-center">
                        <span class="font-bold text-gray-700">{{ $n?->predikat ?? '-' }}</span>
                    </td>
                    @endif
                </tr>
                @empty
                <tr><td colspan="20" class="px-4 py-10 text-center text-gray-400">Pilih kelas dan pelajaran terlebih dahulu.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($siswas->isNotEmpty())
    <div class="mt-4 flex justify-end">
        <button type="submit" class="bg-indigo-600 text-white px-6 py-2.5 rounded-lg text-sm font-semibold hover:bg-indigo-700 transition shadow-sm">
            <i class="fas fa-save mr-1"></i> 
            @if($mode === 'guru') Simpan Nilai Harian
            @elseif($mode === 'panitia') Simpan Skor UTS/UAS
            @else Simpan Semua Nilai
            @endif
        </button>
    </div>
    <p class="text-xs text-gray-400 mt-2">
        UTS Akhir = (Harian UTS + Skor UTS) ÷ 2 &nbsp;·&nbsp; UAS Akhir = (Harian UAS + Skor UAS) ÷ 2 &nbsp;·&nbsp; Nilai Rapot = (UTS Akhir + UAS Akhir) ÷ 2
    </p>
    @endif
</form>
@endif

@if(in_array($mode, ['guru', 'panitia']))
    </div>
</div>
@endif
@endsection
