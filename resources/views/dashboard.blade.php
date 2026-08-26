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

        <div class="bg-white rounded-2xl shadow-sm border border-sky-100 p-6 flex flex-col hover:shadow-md hover:-translate-y-1 transition-all duration-300 group">
            <div class="flex justify-between items-start mb-2">
                <div class="w-12 h-12 rounded-xl bg-sky-50 text-sky-500 flex items-center justify-center text-xl group-hover:scale-110 transition-transform">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <h2 class="text-3xl font-black text-gray-800">{{ $totalJadwal ?? 0 }}</h2>
            </div>
            <p class="text-gray-400 font-bold text-[11px] uppercase tracking-wider mb-5">Total Jadwal Hari Ini</p>
            <a href="/meja-kontrol" class="text-sky-600 hover:text-sky-800 font-bold mt-auto text-xs border-t pt-4 border-gray-50 flex justify-between items-center transition">
                Buka Meja Kontrol <i class="fas fa-arrow-right"></i>
            </a>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-emerald-100 p-6 flex flex-col hover:shadow-md hover:-translate-y-1 transition-all duration-300 group">
            <div class="flex justify-between items-start mb-2">
                <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-500 flex items-center justify-center text-xl group-hover:scale-110 transition-transform">
                    <i class="fas fa-user-check"></i>
                </div>
                <h2 class="text-3xl font-black text-gray-800">{{ $guruHadir ?? 0 }}</h2>
            </div>
            <p class="text-gray-400 font-bold text-[11px] uppercase tracking-wider mb-5">Guru Terkonfirmasi Hadir</p>
            <a href="/laporan" class="text-emerald-600 hover:text-emerald-800 font-bold mt-auto text-xs border-t pt-4 border-gray-50 flex justify-between items-center transition">
                Lihat Laporan <i class="fas fa-arrow-right"></i>
            </a>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-rose-100 p-6 flex flex-col hover:shadow-md hover:-translate-y-1 transition-all duration-300 group">
            <div class="flex justify-between items-start mb-2">
                <div class="w-12 h-12 rounded-xl bg-rose-50 text-rose-500 flex items-center justify-center text-xl group-hover:scale-110 transition-transform">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <h2 class="text-3xl font-black text-gray-800">{{ $guruIzinKosong ?? 0 }}</h2>
            </div>
            <p class="text-gray-400 font-bold text-[11px] uppercase tracking-wider mb-5">Izin / Kelas Kosong</p>
            <a href="/meja-kontrol" class="text-rose-600 hover:text-rose-800 font-bold mt-auto text-xs border-t pt-4 border-gray-50 flex justify-between items-center transition">
                Tindak Lanjuti <i class="fas fa-arrow-right"></i>
            </a>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-indigo-100 p-6 flex flex-col hover:shadow-md hover:-translate-y-1 transition-all duration-300 group">
            <div class="flex justify-between items-start mb-2">
                <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-500 flex items-center justify-center text-xl group-hover:scale-110 transition-transform">
                    <i class="fas fa-users"></i>
                </div>
                <h2 class="text-3xl font-black text-gray-800">{{ $totalGuru ?? \App\Models\Guru::count() }}</h2>
            </div>
            <p class="text-gray-400 font-bold text-[11px] uppercase tracking-wider mb-5">Total Guru Terdaftar</p>
            <a href="/master-guru" class="text-indigo-600 hover:text-indigo-800 font-bold mt-auto text-xs border-t pt-4 border-gray-50 flex justify-between items-center transition">
                Lihat Data <i class="fas fa-arrow-right"></i>
            </a>
        </div>

    </div>

</div>
@endsection