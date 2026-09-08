@extends('layouts.app')

@section('title', 'Kelengkapan Data Guru - SmartPesantren')

@section('content')

    {{-- HEADER --}}
    <div class="flex flex-col lg:flex-row justify-between items-stretch lg:items-center mb-4 md:mb-6 gap-4">
        <div class="flex items-center gap-3">
            <a href="/master-guru" class="w-10 h-10 rounded-xl bg-white border border-gray-100 shadow-sm text-gray-500 hover:bg-sky-500 hover:text-white transition flex items-center justify-center shrink-0 active:scale-95">
                <i class="fas fa-arrow-left text-sm"></i>
            </a>
            <div class="min-w-0">
                <h1 class="text-xl md:text-2xl font-bold text-gray-800 leading-tight">Kelengkapan Data</h1>
                <p class="text-xs md:text-sm text-gray-500 mt-0.5 truncate">{{ $guru->nama_guru }} &bull; NIG {{ $guru->nig }}</p>
            </div>
        </div>

        <div class="flex flex-col sm:flex-row sm:flex-wrap items-stretch sm:items-center gap-2">
            <div class="flex flex-wrap items-center gap-2">
                <span class="inline-flex items-center px-3 py-2 rounded-xl text-xs font-bold {{ $guru->status == 'Aktif' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                    <i class="fas fa-circle text-[8px] mr-1.5"></i> {{ $guru->status ?? 'Aktif' }}
                </span>

                <span class="inline-flex items-center px-3 py-2 rounded-xl text-xs font-bold {{ $editMode ? 'bg-sky-100 text-sky-700' : 'bg-gray-100 text-gray-600' }}">
                    <i class="fas fa-lock-open mr-1.5 text-[10px]"></i> Guru boleh mengubah: {{ $editMode ? 'Ya' : 'Tidak' }}
                </span>
            </div>

            @if($bolehToggle)
                <form method="POST" action="/master-guru/{{ $guru->id }}/kelengkapan/toggle" class="sm:w-auto">
                    @csrf
                    <button type="submit"
                        class="{{ $editMode
                            ? 'bg-amber-50 text-amber-700 border border-amber-200 hover:bg-amber-600 hover:text-white'
                            : 'bg-green-600 text-white hover:bg-green-700' }} w-full justify-center px-4 py-2.5 rounded-xl text-sm font-bold transition flex items-center shadow-sm active:scale-[0.98]">
                        <i class="fas {{ $editMode ? 'fa-lock' : 'fa-unlock' }} mr-2"></i>
                        {{ $editMode ? 'Nonaktifkan Edit' : 'Aktifkan Edit' }}
                    </button>
                </form>
            @endif
        </div>
    </div>

    @if(!$editable)
        <div class="mb-4 bg-sky-50 border border-sky-200 text-sky-800 p-4 rounded-2xl text-sm font-semibold flex items-center shadow-sm">
            <div class="w-8 h-8 rounded-xl bg-sky-100 flex items-center justify-center mr-3 shrink-0">
                <i class="fas fa-info-circle text-sky-600"></i>
            </div>
            Data masih terkunci. Administrator/Pimpinan dapat menekan tombol <b>&laquo;Aktifkan Edit&raquo;</b> agar guru bisa mengubah dan menyimpan datanya sendiri.
        </div>
    @endif

    {{-- FORM PROFIL UTAMA (tanpa form lain di dalamnya agar tidak nested) --}}
    <form method="POST" action="/master-guru/{{ $guru->id }}/kelengkapan" id="form-kelengkapan-admin">
        @csrf

        @include('partials.guru-kelengkapan-form', [
            'guru' => $guru,
            'editable' => $editable,
            'isAdmin' => $isAdmin,
            'jabatans' => $jabatans,
            'guruPage' => false,
            'bolehDokumen' => $editable,
        ])

        @if($editable)
            <div class="sticky bottom-0 flex justify-end bg-white/95 backdrop-blur-sm border-t border-gray-100 p-3 md:p-4 rounded-t-2xl">
                <button type="submit" class="w-full sm:w-auto justify-center px-6 py-2.5 bg-sky-600 text-white rounded-lg font-semibold hover:bg-sky-700 transition shadow-md flex items-center active:scale-[0.98]">
                    <i class="fas fa-save mr-2"></i> Simpan
                </button>
            </div>
        @endif
    </form>

    {{-- DOKUMEN: di luar form utama (masing-masing punya form sendiri) --}}
    @include('partials.guru-kelengkapan-dokumen', [
        'guru' => $guru,
        'bolehDokumen' => $editable,
        'guruPage' => false,
        'dokumenAction' => '/master-guru/' . $guru->id . '/dokumen',
    ])

@endsection