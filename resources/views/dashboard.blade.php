@extends('layouts.app')

@section('title', 'Dashboard - Muadalah Wustha')

@section('content')
    <div class="flex flex-col md:flex-row justify-between items-center mb-6">
        <div>
            <h2 class="text-xl font-bold text-gray-700">Dashboard | Muadalah Wustha</h2>
            <p class="text-xs text-gray-500 mt-0.5">Ringkasan aktivitas dan kehadiran guru hari ini.</p>
        </div>

        <div class="flex items-center space-x-3 mt-3 md:mt-0">
            <span class="bg-green-50 text-green-700 px-4 py-1.5 rounded-full text-sm font-bold border border-green-200 flex items-center shadow-sm">
                <i class="far fa-clock mr-2"></i> <span id="live-clock">--:--:--</span>
            </span>
            <button class="bg-red-50 text-red-600 border border-red-200 px-4 py-1.5 rounded text-sm font-semibold hover:bg-red-100 transition">
                <i class="far fa-question-circle mr-1"></i> Panduan
            </button>
        </div>
    </div>

    <div class="bg-gradient-to-r from-green-700 to-green-500 rounded-xl p-8 text-white mb-6 shadow-md flex items-center justify-between relative overflow-hidden">
        <div class="z-10">
            <p class="text-green-100 font-semibold mb-2 flex items-center"><i class="fas fa-mosque mr-2"></i> Madrasah Mua'dalah Wustha Maqna'ul Ulum</p>
            <h2 class="text-3xl font-black mb-2 leading-tight">MAKSIMALKAN PENGGUNAAN<br>SISTEM SMART MUADALAH</h2>
            <p class="text-sm text-green-100 font-medium">Pantau aktivitas kehadiran guru, jadwal kelas, dan rekap laporan secara real-time.</p>
        </div>
        <div class="hidden md:block z-10 opacity-20 text-9xl absolute right-10">
            <i class="fas fa-laptop-code"></i>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-5">
        
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex flex-col hover:shadow-md transition">
            <div class="flex justify-between items-start mb-2">
                <span class="text-blue-500 bg-blue-50 p-2 rounded-lg text-xl"><i class="fas fa-calendar-alt"></i></span>
                <h2 class="text-3xl font-bold text-gray-800">{{ $totalJadwal }}</h2>
            </div>
            <p class="text-gray-500 font-medium mb-4 text-sm">Total Jadwal Hari Ini</p>
            <a href="/meja-kontrol" class="text-blue-500 hover:text-blue-700 font-semibold mt-auto text-xs border-t pt-3 border-gray-50">Buka Meja Kontrol <i class="fas fa-arrow-circle-right ml-1"></i></a>
        </div>
        
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex flex-col hover:shadow-md transition">
            <div class="flex justify-between items-start mb-2">
                <span class="text-green-500 bg-green-50 p-2 rounded-lg text-xl"><i class="fas fa-user-check"></i></span>
                <h2 class="text-3xl font-bold text-gray-800">{{ $guruHadir }}</h2>
            </div>
            <p class="text-gray-500 font-medium mb-4 text-sm">Guru Terkonfirmasi Hadir</p>
            <a href="/laporan" class="text-green-500 hover:text-green-700 font-semibold mt-auto text-xs border-t pt-3 border-gray-50">Lihat Laporan <i class="fas fa-arrow-circle-right ml-1"></i></a>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex flex-col hover:shadow-md transition">
            <div class="flex justify-between items-start mb-2">
                <span class="text-red-500 bg-red-50 p-2 rounded-lg text-xl"><i class="fas fa-exclamation-triangle"></i></span>
                <h2 class="text-3xl font-bold text-gray-800">{{ $guruIzinKosong }}</h2>
            </div>
            <p class="text-gray-500 font-medium mb-4 text-sm">Izin / Kelas Kosong</p>
            <a href="/meja-kontrol" class="text-red-500 hover:text-red-700 font-semibold mt-auto text-xs border-t pt-3 border-gray-50">Tindak Lanjuti <i class="fas fa-arrow-circle-right ml-1"></i></a>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex flex-col hover:shadow-md transition">
            <div class="flex justify-between items-start mb-2">
                <span class="text-purple-500 bg-purple-50 p-2 rounded-lg text-xl"><i class="fas fa-users"></i></span>
                <h2 class="text-3xl font-bold text-gray-800">53</h2>
            </div>
            <p class="text-gray-500 font-medium mb-4 text-sm">Total Guru Terdaftar</p>
            <a href="#" class="text-purple-500 hover:text-purple-700 font-semibold mt-auto text-xs border-t pt-3 border-gray-50">Lihat Data <i class="fas fa-arrow-circle-right ml-1"></i></a>
        </div>

    </div>
@endsection