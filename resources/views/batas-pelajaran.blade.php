@extends('layouts.app')

@section('title', 'Set Batas Pelajaran')

@section('content')
<div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-end gap-4">
    <div>
        <h2 class="text-2xl font-black text-slate-800 tracking-tight flex items-center">
            <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600 mr-3">
                <i class="fas fa-route"></i>
            </div>
            Target Kurikulum
        </h2>
        <p class="text-sm font-bold text-emerald-600 mt-2 flex items-center">
            <i class="fas fa-check-circle mr-1.5"></i> Periode Aktif: {{ $periodeAktif->tahun_ajaran ?? 'Tidak Diketahui' }}
        </p>
    </div>
    
    <!-- Filter Tingkat Kelas -->
    <form method="GET" action="/batas-pelajaran" class="w-full md:w-auto">
        <div class="flex items-center bg-white border border-slate-200 rounded-xl px-4 py-2 hover:border-indigo-400 focus-within:border-indigo-500 focus-within:ring-4 focus-within:ring-indigo-500/10 transition-all shadow-sm">
            <label class="text-slate-400 mr-3 font-bold text-sm">Tingkat:</label>
            <select name="tingkat" onchange="this.form.submit()" class="text-sm font-black text-indigo-700 bg-transparent outline-none cursor-pointer w-full md:w-48">
                <option value="7" {{ $tingkatPilihan == '7' ? 'selected' : '' }}>Kelas 7 / 1 Ulya</option>
                <option value="8" {{ $tingkatPilihan == '8' ? 'selected' : '' }}>Kelas 8 / 2 Ulya</option>
                <option value="9" {{ $tingkatPilihan == '9' ? 'selected' : '' }}>Kelas 9 / 3 Ulya</option>
            </select>
        </div>
    </form>
</div>

<!-- Pesan Sukses -->
@if(session('sukses'))
<div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl mb-6 flex items-center shadow-sm">
    <i class="fas fa-check-circle text-xl mr-3"></i>
    <span class="font-bold text-sm">{{ session('sukses') }}</span>
</div>
@endif

<!-- Form Excel-like (Bulk Edit) -->
<form action="/batas-pelajaran" method="POST" class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden relative">
    @csrf
    <!-- Menyimpan state tingkat agar saat disubmit tidak berubah -->
    <input type="hidden" name="tingkat" value="{{ $tingkatPilihan }}">
    
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 border-b border-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-5 py-4 text-left text-xs font-black text-slate-500 uppercase tracking-widest w-64 border-r border-slate-100">Pelajaran & Kitab</th>
                    <th class="px-3 py-4 text-left text-xs font-black text-slate-500 uppercase tracking-widest border-r border-slate-100">Mulai Dari (Awal)</th>
                    <th class="px-3 py-4 text-left text-xs font-black text-slate-500 uppercase tracking-widest border-r border-slate-100">UTS Ganjil</th>
                    <th class="px-3 py-4 text-left text-xs font-black text-slate-500 uppercase tracking-widest border-r border-slate-100">UAS Ganjil</th>
                    <th class="px-3 py-4 text-left text-xs font-black text-slate-500 uppercase tracking-widest border-r border-slate-100">UTS Genap</th>
                    <th class="px-3 py-4 text-left text-xs font-black text-slate-500 uppercase tracking-widest">UAS Genap</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 bg-white">
                @foreach($pelajarans as $p)
                    @php 
                        // Mengambil kitab khusus untuk tingkat yang dipilih (Cerda dari JSONB yang kita buat sebelumnya)
                        $kitab = $p->kitab_tingkat[$tingkatPilihan] ?? '-';
                        
                        // Menarik data batas yang sudah tersimpan (jika ada)
                        $data = $batasData[$p->id] ?? null;
                    @endphp
                    <tr class="hover:bg-indigo-50/30 transition-colors group">
                        <td class="px-5 py-3 border-r border-slate-100">
                            <p class="text-sm font-black text-slate-800">{{ $p->nama_pelajaran }}</p>
                            <p class="text-[11px] font-bold text-emerald-600 mt-0.5"><i class="fas fa-book-open mr-1 opacity-70"></i>{{ $kitab }}</p>
                        </td>
                        
                        <!-- Input Kolom Batas -->
                        <td class="px-2 py-2 border-r border-slate-100">
                            <input type="text" name="batas[{{ $p->id }}][mulai_dari]" value="{{ $data->mulai_dari ?? '' }}" class="w-full border-none bg-transparent hover:bg-slate-50 focus:bg-white rounded p-2 text-sm font-medium text-slate-700 focus:ring-2 focus:ring-indigo-500 outline-none transition-all placeholder-slate-300" placeholder="Awal bab...">
                        </td>
                        <td class="px-2 py-2 border-r border-slate-100">
                            <input type="text" name="batas[{{ $p->id }}][batas_uts_ganjil]" value="{{ $data->batas_uts_ganjil ?? '' }}" class="w-full border-none bg-transparent hover:bg-slate-50 focus:bg-white rounded p-2 text-sm font-medium text-slate-700 focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                        </td>
                        <td class="px-2 py-2 border-r border-slate-100">
                            <input type="text" name="batas[{{ $p->id }}][batas_uas_ganjil]" value="{{ $data->batas_uas_ganjil ?? '' }}" class="w-full border-none bg-transparent hover:bg-slate-50 focus:bg-white rounded p-2 text-sm font-medium text-slate-700 focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                        </td>
                        <td class="px-2 py-2 border-r border-slate-100">
                            <input type="text" name="batas[{{ $p->id }}][batas_uts_genap]" value="{{ $data->batas_uts_genap ?? '' }}" class="w-full border-none bg-transparent hover:bg-slate-50 focus:bg-white rounded p-2 text-sm font-medium text-slate-700 focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                        </td>
                        <td class="px-2 py-2">
                            <input type="text" name="batas[{{ $p->id }}][batas_uas_genap]" value="{{ $data->batas_uas_genap ?? '' }}" class="w-full border-none bg-transparent hover:bg-slate-50 focus:bg-white rounded p-2 text-sm font-medium text-slate-700 focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    
    <!-- Tombol Simpan Melayang (Sticky Bottom) -->
    <div class="p-5 bg-slate-50 border-t border-slate-200 flex justify-end sticky bottom-0">
        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 active:scale-95 text-white font-bold py-3 px-8 rounded-xl shadow-[0_4px_20px_-4px_rgba(79,70,229,0.4)] transition-all flex items-center">
            <i class="fas fa-save mr-2 text-lg"></i> Simpan Target Kelas {{ $tingkatPilihan }}
        </button>
    </div>
</form>
@endsection