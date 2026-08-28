@extends('layouts.app')

@section('title', 'Dashboard - Muadalah Wustha')

@section('content')
<div class="space-y-6">
    
    <div class="flex flex-col md:flex-row justify-between items-center bg-white p-5 rounded-2xl shadow-sm border border-gray-100 gap-4">
        <div class="text-center md:text-left">
            <h2 class="text-xl font-bold text-gray-800">Dashboard | Muadalah Wustha</h2>
            <p class="text-xs text-gray-500 mt-1 font-medium">Ringkasan aktivitas dan kehadiran asatidz hari ini.</p>
        </div>

        <div class="flex items-center space-x-3">
            <span class="bg-emerald-50 text-emerald-700 px-4 py-2 rounded-xl text-sm font-bold border border-emerald-100 flex items-center shadow-sm">
                <i class="far fa-clock mr-2"></i> <span id="live-clock">--:--:--</span>
            </span>
            <button class="bg-rose-50 text-rose-600 border border-rose-100 px-4 py-2 rounded-xl text-sm font-bold hover:bg-rose-100 transition shadow-sm cursor-pointer">
                <i class="far fa-question-circle mr-1"></i> Panduan
            </button>
        </div>
    </div>

    <div class="bg-gradient-to-r from-emerald-600 to-teal-500 rounded-3xl p-8 text-white shadow-lg relative overflow-hidden flex items-center justify-between">
        <div class="relative z-10">
            <p class="bg-white/20 inline-block px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-widest backdrop-blur-sm mb-4 border border-white/20">
                <i class="fas fa-mosque mr-1"></i> Madrasah Mu'adalah Wustha Maqna'ul Ulum
            </p>
            <h2 class="text-3xl font-black mb-2 leading-tight tracking-tight">MAKSIMALKAN PENGGUNAAN<br>SISTEM SMART MUADALAH</h2>
            <p class="text-emerald-50 text-sm font-medium">Pantau aktivitas kehadiran guru, jadwal kelas, dan rekap laporan secara real-time.</p>
        </div>
        <div class="hidden md:block z-10 opacity-10 text-9xl absolute -right-6 -bottom-10 transform -rotate-6">
            <i class="fas fa-laptop-code"></i>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">

        <div class="bg-gradient-to-br from-emerald-400 to-teal-600 rounded-3xl p-6 text-white shadow-lg shadow-emerald-500/20 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 group relative overflow-hidden">
            <div class="absolute -right-6 -bottom-8 opacity-10 text-[110px] group-hover:scale-110 transition-transform"><i class="fas fa-calendar-alt"></i></div>
            <div class="flex justify-between items-start mb-5 relative z-10">
                <div class="w-12 h-12 rounded-2xl bg-white/20 backdrop-blur-md flex items-center justify-center text-xl group-hover:scale-110 transition-transform">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <span class="bg-white/20 text-xs font-bold px-2.5 py-1 rounded-full backdrop-blur-md border border-white/20">Hari Ini</span>
            </div>
            <p class="text-[11px] font-bold uppercase tracking-wider text-emerald-50/90 relative z-10 mb-1">Total Jadwal</p>
            <h2 class="text-4xl font-black text-white relative z-10 mb-5">{{ $totalJadwal ?? 0 }}</h2>
            <a href="/meja-kontrol" class="relative z-10 flex justify-between items-center pt-4 border-t border-white/20 text-xs font-bold hover:text-emerald-50 transition">
                Buka Meja Kontrol <i class="fas fa-arrow-right"></i>
            </a>
        </div>

        <div class="bg-gradient-to-br from-teal-400 to-cyan-600 rounded-3xl p-6 text-white shadow-lg shadow-teal-500/20 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 group relative overflow-hidden">
            <div class="absolute -right-6 -bottom-8 opacity-10 text-[110px] group-hover:scale-110 transition-transform"><i class="fas fa-user-check"></i></div>
            <div class="flex justify-between items-start mb-5 relative z-10">
                <div class="w-12 h-12 rounded-2xl bg-white/20 backdrop-blur-md flex items-center justify-center text-xl group-hover:scale-110 transition-transform">
                    <i class="fas fa-user-check"></i>
                </div>
                <span class="bg-white/20 text-xs font-bold px-2.5 py-1 rounded-full backdrop-blur-md border border-white/20">Live</span>
            </div>
            <p class="text-[11px] font-bold uppercase tracking-wider text-teal-50/90 relative z-10 mb-1">Guru Terkonfirmasi Hadir</p>
            <h2 class="text-4xl font-black text-white relative z-10 mb-5">{{ $guruHadir ?? 0 }}</h2>
            <a href="/laporan" class="relative z-10 flex justify-between items-center pt-4 border-t border-white/20 text-xs font-bold hover:text-teal-50 transition">
                Lihat Laporan <i class="fas fa-arrow-right"></i>
            </a>
        </div>

        <div class="bg-gradient-to-br from-rose-400 to-red-500 rounded-3xl p-6 text-white shadow-lg shadow-rose-500/20 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 group relative overflow-hidden">
            <div class="absolute -right-6 -bottom-8 opacity-10 text-[110px] group-hover:scale-110 transition-transform"><i class="fas fa-exclamation-triangle"></i></div>
            <div class="flex justify-between items-start mb-5 relative z-10">
                <div class="w-12 h-12 rounded-2xl bg-white/20 backdrop-blur-md flex items-center justify-center text-xl group-hover:scale-110 transition-transform">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <span class="bg-white/20 text-xs font-bold px-2.5 py-1 rounded-full backdrop-blur-md border border-white/20">Perlu Tindak</span>
            </div>
            <p class="text-[11px] font-bold uppercase tracking-wider text-rose-50/90 relative z-10 mb-1">Izin / Kelas Kosong</p>
            <h2 class="text-4xl font-black text-white relative z-10 mb-5">{{ $guruIzinKosong ?? 0 }}</h2>
            <a href="/meja-kontrol" class="relative z-10 flex justify-between items-center pt-4 border-t border-white/20 text-xs font-bold hover:text-rose-50 transition">
                Tindak Lanjuti <i class="fas fa-arrow-right"></i>
            </a>
        </div>

        <div class="bg-gradient-to-br from-slate-700 to-slate-900 rounded-3xl p-6 text-white shadow-lg shadow-slate-700/20 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 group relative overflow-hidden">
            <div class="absolute -right-6 -bottom-8 opacity-10 text-[110px] group-hover:scale-110 transition-transform"><i class="fas fa-users"></i></div>
            <div class="flex justify-between items-start mb-5 relative z-10">
                <div class="w-12 h-12 rounded-2xl bg-white/10 backdrop-blur-md flex items-center justify-center text-xl group-hover:scale-110 transition-transform">
                    <i class="fas fa-users"></i>
                </div>
                <span class="bg-white/10 text-xs font-bold px-2.5 py-1 rounded-full backdrop-blur-md border border-white/10">Registrasi</span>
            </div>
            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-300 relative z-10 mb-1">Total Guru Terdaftar</p>
            <h2 class="text-4xl font-black text-white relative z-10 mb-5">{{ $totalGuru ?? \App\Models\Guru::count() }}</h2>
            <a href="/master-guru" class="relative z-10 flex justify-between items-center pt-4 border-t border-white/10 text-xs font-bold hover:text-slate-300 transition">
                Lihat Data <i class="fas fa-arrow-right"></i>
            </a>
        </div>

    </div>

</div>
@endsection