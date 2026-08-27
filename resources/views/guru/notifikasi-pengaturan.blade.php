@extends('layouts.app')
@section('title', 'Pengaturan Notifikasi')
@section('content')
<style>
    header, aside { display: none !important; }
    main { padding: 0 !important; background-color: #f8fafc !important; overflow: hidden !important; }
    body { overflow: hidden !important; background-color: #f8fafc !important; }
</style>

<div class="max-w-md mx-auto h-[100dvh] bg-slate-50 flex flex-col relative font-sans overflow-hidden">

    <!-- HEADER -->
    <div class="shrink-0 bg-gradient-to-br from-emerald-600 to-teal-700 px-6 pt-8 pb-6 rounded-b-[2.5rem] shadow-md relative z-20">
        <div class="flex items-center gap-3">
            <a href="/menu" class="w-9 h-9 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center text-white">
                <i class="fas fa-arrow-left text-sm"></i>
            </a>
            <div>
                <h2 class="text-2xl font-black text-white tracking-tight drop-shadow-md">Notifikasi</h2>
                <p class="text-emerald-100 text-xs font-medium mt-1">Pengingat Jadwal Mengajar</p>
            </div>
        </div>
    </div>

    <!-- KONTEN -->
    <div class="flex-1 overflow-y-auto scrollbar-none px-6 pt-10 pb-10 flex flex-col items-center justify-center">

        <div class="w-20 h-20 rounded-full bg-emerald-50 border-4 border-emerald-100 flex items-center justify-center text-emerald-500 mb-6">
            <i class="fas fa-tools text-3xl"></i>
        </div>

        <h3 class="text-xl font-black text-slate-800 text-center">Fitur Sedang Diproses</h3>
        <p class="text-sm text-slate-500 text-center mt-3 leading-relaxed max-w-xs">
            Fitur notifikasi sedang dinonaktifkan sementara. Akan dibangun ulang kembali pada versi APK aplikasi.
        </p>

        <a href="/menu"
           class="mt-8 w-full max-w-xs py-3 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-600 text-white font-bold text-sm shadow-lg shadow-emerald-200 active:scale-95 transition text-center">
            Kembali ke Menu
        </a>
    </div>

</div>
@endsection