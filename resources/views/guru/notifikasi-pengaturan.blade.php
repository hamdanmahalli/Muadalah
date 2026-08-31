@extends('layouts.app')
@section('title', 'Pengaturan Notifikasi')
@section('content')
<style>
    header, aside { display: none !important; }
    main { padding: 0 !important; background-color: #f8fafc !important; overflow: hidden !important; }
    body { overflow: hidden !important; background-color: #f8fafc !important; }
    .scrollbar-none::-webkit-scrollbar { display: none; }
    .scrollbar-none { -ms-overflow-style: none; scrollbar-width: none; }
</style>

<div data-turbo="true" class="max-w-md mx-auto h-[100dvh] bg-slate-50 flex flex-col relative font-sans overflow-hidden">

    <!-- HEADER MODERN -->
    <div class="shrink-0 bg-white px-4 pt-4 pb-4 border-b border-slate-100 relative z-20">
        <div class="flex items-center gap-3">
            <a href="/menu" class="w-10 h-10 rounded-full bg-slate-100 text-slate-600 flex items-center justify-center hover:bg-slate-200 active:scale-95 transition-all shrink-0">
                <i class="fas fa-arrow-left text-sm"></i>
            </a>
            <div>
                <h2 class="text-base font-black text-slate-900 tracking-tight">Notifikasi</h2>
                <p class="text-[10px] font-bold text-emerald-600 uppercase tracking-widest mt-0.5">Pengingat Jadwal Mengajar</p>
            </div>
        </div>
    </div>

    <!-- KONTEN -->
    <div class="flex-1 overflow-y-auto scrollbar-none px-6 pt-10 pb-10 flex flex-col items-center justify-center">

        <div class="w-24 h-24 rounded-3xl bg-white border border-slate-100 shadow-[0_10px_30px_-12px_rgba(2,6,23,0.12)] flex items-center justify-center text-emerald-500 mb-6">
            <i class="fas fa-tools text-4xl"></i>
        </div>

        <h3 class="text-xl font-black text-slate-900 text-center">Fitur Sedang Diproses</h3>
        <p class="text-sm text-slate-500 text-center mt-3 leading-relaxed max-w-xs">
            Fitur notifikasi sedang dinonaktifkan sementara.
            Akan dibangun ulang pada versi APK aplikasi berikutnya.
        </p>

        <a href="/menu"
           class="mt-8 w-full max-w-xs py-3.5 rounded-2xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-sm active:scale-95 transition text-center shadow-[0_10px_24px_-10px_rgba(15,23,42,0.6)]">
            Kembali ke Menu
        </a>
    </div>

</div>
@endsection