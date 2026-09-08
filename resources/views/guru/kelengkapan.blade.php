@extends('layouts.app')
@section('title', 'Biodata & Kelengkapan Guru - SmartPesantren')
@section('content')
<style>
    @media (max-width: 767px) {
        header { display: none !important; }
        #btn-buka-sidebar { display: none !important; }
        main { padding: 0 !important; background-color: #f8fafc !important; overflow: hidden !important; }
        body { overflow: hidden !important; background-color: #f8fafc !important; }
        .scrollbar-none::-webkit-scrollbar { display: none; }
        .scrollbar-none { -ms-overflow-style: none; scrollbar-width: none; }
        @supports (padding-bottom: env(safe-area-inset-bottom)) { .pb-safe { padding-bottom: env(safe-area-inset-bottom); } }
    }
    @keyframes sweep { 0% { opacity: 0; transform: translateY(-10px) } 100% { opacity: 1; transform: translateY(0) } }
</style>

@php
    $editable       = (bool) ($isAdmin || $editMode);
    $bolehDokumen   = $editable;
@endphp

{{-- SATU LAYOUT RESPONSIF: mobile = layar penuh ala aplikasi (tombol kembali saja),
     desktop = tampilan default shell lebar. Tanpa duplikasi form. --}}
<div class="max-w-md md:max-w-none mx-auto h-[100dvh] md:h-auto bg-slate-50 md:bg-transparent flex flex-col md:block font-sans overflow-hidden md:overflow-visible">

    <!-- HEADER (kembali + judul + badge) -->
    <div class="shrink-0 md:shrink-none bg-white md:bg-transparent border-b md:border-0 border-slate-100 px-4 md:px-0 py-4 flex items-center gap-3 md:mb-6">
        <a href="{{ url()->previous() }}" class="w-10 h-10 rounded-full md:rounded-xl bg-slate-100 md:bg-white text-slate-600 border md:border-gray-100 md:shadow-sm flex items-center justify-center hover:bg-slate-200 active:scale-95 transition-all shrink-0" title="Kembali">
            <i class="fas fa-arrow-left text-sm"></i>
        </a>
        <div class="min-w-0 flex-1">
            <h2 class="text-base font-black text-slate-900 tracking-tight truncate">Biodata Guru</h2>
            <p class="text-[10px] font-bold text-emerald-600 uppercase tracking-widest mt-0.5">Profil &amp; Kelengkapan Data &bull; {{ $guru->nama_guru }}</p>
        </div>
        @if($isAdmin || $editMode)
            <span class="inline-flex items-center px-3 py-1.5 rounded-full text-[10px] font-black uppercase tracking-wide {{ $editable ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500' }}">
                <i class="fas fa-lock-open mr-1"></i> Boleh Edit
            </span>
        @endif
    </div>

    <!-- AREA KONTEN -->
    <div class="flex-1 overflow-y-auto md:overflow-visible md:flex-none scrollbar-none px-5 md:px-0 pb-28 md:pb-0 pt-5 md:pt-0">

        @if(session('status'))
            <div class="mb-5 bg-emerald-50 text-emerald-700 p-4 rounded-2xl text-xs font-bold flex items-center border border-emerald-100 shadow-sm animate-[sweep_0.3s_ease-in-out]">
                <div class="w-8 h-8 rounded-xl bg-emerald-100 flex items-center justify-center mr-3 shrink-0">
                    <i class="fas fa-check text-emerald-600"></i>
                </div>
                {{ session('status') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-5 bg-rose-50 text-rose-700 p-4 rounded-2xl text-xs font-bold flex items-center border border-rose-100 shadow-sm animate-[sweep_0.3s_ease-in-out]">
                <div class="w-8 h-8 rounded-xl bg-rose-100 flex items-center justify-center mr-3 shrink-0">
                    <i class="fas fa-exclamation-triangle text-rose-600"></i>
                </div>
                {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-5 bg-rose-50 text-rose-700 p-4 rounded-2xl text-xs font-bold border border-rose-100 shadow-sm">
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if(!$editable)
            <div class="mb-5 bg-sky-50 border border-sky-200 text-sky-800 p-4 rounded-2xl text-xs font-bold flex items-center animate-[sweep_0.3s_ease-in-out]">
                <div class="w-8 h-8 rounded-xl bg-sky-100 flex items-center justify-center mr-3 shrink-0">
                    <i class="fas fa-lock text-sky-600"></i>
                </div>
                Data Anda masih terkunci. Silakan hubungi Administrator/Pimpinan untuk mengaktifkan pengeditan melalui menu <b>Master Data &rarr; Master Pengurus/Guru</b>.
            </div>
        @endif

        {{-- FORM PROFIL UTAMA (tidak mengandung form lain agar tidak nested) --}}
        <form action="{{ route('guru.profil.update') }}" method="POST" id="form-profil" data-turbo="false">
            @csrf
            @method('PUT')

            @include('partials.guru-kelengkapan-form', [
                'guru' => $guru,
                'editable' => $editable,
                'isAdmin' => $isAdmin,
                'jabatans' => null,
                'guruPage' => true,
                'bolehDokumen' => $bolehDokumen,
            ])

            <div class="mb-4 rounded-2xl bg-white border border-slate-100 p-4 shadow-sm">
                @if($editable)
                    <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 active:scale-[0.99] text-white font-black text-sm py-4 rounded-2xl transition-all shadow-[0_10px_24px_-8px_rgba(16,185,129,0.5)] flex items-center justify-center group">
                        <i class="fas fa-save mr-2.5 text-lg group-hover:scale-110 transition-transform"></i> Simpan Perubahan Biodata
                    </button>
                @else
                    <div class="w-full bg-slate-100 text-slate-500 font-bold text-xs text-center py-4 rounded-2xl flex items-center justify-center">
                        <i class="fas fa-lock mr-2"></i> Data Terkunci
                    </div>
                @endif
            </div>
        </form>

        {{-- DOKUMEN: di luar form utama (masing-masing punya form sendiri) --}}
        @include('partials.guru-kelengkapan-dokumen', [
            'guru' => $guru,
            'bolehDokumen' => $bolehDokumen,
            'guruPage' => true,
            'dokumenAction' => route('guru.profil.dokumen'),
        ])

    </div>

</div>
@endsection