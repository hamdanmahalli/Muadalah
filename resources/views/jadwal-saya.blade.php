@extends('layouts.app')
@section('title', 'Jadwal Mengajar Saya')
@section('content')

<div class="space-y-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6">
        <div>
            <h2 class="text-2xl font-black text-gray-800 tracking-tight">Jadwal Mengajar</h2>
            <p class="text-sm text-gray-500 mt-1">Selamat bertugas, <span class="font-bold text-emerald-600">{{ auth()->user()->name }}</span></p>
        </div>
        @if(isset($periodeAktif) && $periodeAktif)
        <div class="mt-4 md:mt-0 px-4 py-2 bg-white shadow-sm border border-gray-100 rounded-lg text-sm font-bold text-gray-700">
            TA. {{ $periodeAktif->tahun_ajaran }} ({{ $periodeAktif->semester }})
        </div>
        @endif
    </div>

    @if(isset($pesan))
        <div class="bg-amber-50 border border-amber-200 text-amber-800 p-4 rounded-xl text-sm font-medium shadow-sm">
            <i class="fas fa-exclamation-circle mr-2 text-amber-600"></i> {{ $pesan }}
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach($jadwalTerstruktur as $hari => $blokJadwal)
            <div class="bg-white rounded-xl shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] border border-gray-100 overflow-hidden flex flex-col h-full transition hover:shadow-md">
                
                <div class="bg-gray-50/50 px-5 py-4 border-b border-gray-100 flex justify-between items-center">
                    <h3 class="font-black text-gray-800 text-base tracking-widest uppercase">{{ $hari }}</h3>
                    <i class="fas fa-calendar-alt text-gray-300 text-lg"></i>
                </div>

                <div class="flex-col flex-grow">
                    @foreach($blokJadwal as $index => $blok)
                    <div class="flex items-center px-4 py-4 {{ !$loop->last ? 'border-b border-gray-50' : '' }} hover:bg-gray-50 transition duration-150">
                        
                        <div class="w-16 flex flex-col items-center justify-center flex-shrink-0">
                            <span class="text-[10px] font-bold text-gray-400 tracking-widest uppercase mb-0.5">Blok</span>
                            <span class="text-xl font-black text-gray-800">
                                {{ $blok['jam_mulai'] }}{{ $blok['jam_mulai'] != $blok['jam_selesai'] ? '-'.$blok['jam_selesai'] : '' }}
                            </span>
                        </div>
                        
                        <div class="w-px h-10 bg-gray-200 mx-4"></div>
                        
                        <div class="flex flex-col">
                            <h4 class="text-base font-bold text-gray-800 leading-tight">{{ $blok['pelajaran'] }}</h4>
                            <div class="flex items-center mt-1 text-sm font-semibold text-emerald-600">
                                <i class="fas fa-chalkboard-teacher mr-1.5 text-xs"></i> Kelas {{ $blok['kelas'] }}
                            </div>
                        </div>

                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>

        <div class="mt-10">
            <h2 class="text-lg font-black text-gray-800 tracking-tight mb-4 border-b pb-2"><i class="fas fa-chart-pie text-emerald-600 mr-2"></i> Rekapitulasi Kehadiran Pribadi</h2>
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white rounded-xl shadow-sm border border-emerald-100 overflow-hidden h-fit">
                    <div class="bg-emerald-50 px-5 py-3 border-b border-emerald-100 flex justify-between items-center">
                        <h3 class="font-bold text-emerald-800">Bulan Ini ({{ \Carbon\Carbon::now()->translatedFormat('F Y') }})</h3>
                        <span class="bg-white px-2 py-1 rounded text-[10px] font-bold text-emerald-600 shadow-sm border border-emerald-100 uppercase">Aktif</span>
                    </div>
                    <div class="p-5 grid grid-cols-2 md:grid-cols-4 gap-4 items-center">
                        <div class="text-center border-b md:border-b-0 md:border-r border-gray-100 pb-3 md:pb-0">
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Wajib</p>
                            <p class="text-2xl font-black text-gray-700 mt-1">{{ $rekapBulan->wajib }}</p>
                        </div>
                        <div class="text-center border-b md:border-b-0 md:border-r border-gray-100 pb-3 md:pb-0">
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Hadir</p>
                            <p class="text-2xl font-black text-emerald-600 mt-1">{{ $rekapBulan->hadir }}</p>
                        </div>
                        <div class="text-center md:border-r border-gray-100 pt-1 md:pt-0">
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Alpa</p>
                            <p class="text-2xl font-black text-rose-500 mt-1">{{ $rekapBulan->alpha }}</p>
                        </div>
                        <div class="text-center pt-1 md:pt-0">
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Persentase</p>
                            <p class="text-2xl font-black mt-1 {{ $rekapBulan->persen >= 80 ? 'text-emerald-600' : 'text-amber-500' }}">{{ $rekapBulan->persen }}%</p>
                        </div>
                    </div>
                </div>

                <details class="group bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden h-fit">
                    <summary class="bg-gray-50 px-5 py-3.5 border-b border-gray-200 flex justify-between items-center cursor-pointer hover:bg-gray-100 transition outline-none">
                        <h3 class="font-bold text-gray-700"><i class="fas fa-history text-gray-400 mr-2"></i> Rekap Total 1 Tahun Ajaran</h3>
                        <span class="transition duration-300 group-open:rotate-180 text-gray-500">
                            <i class="fas fa-chevron-down"></i>
                        </span>
                    </summary>
                    <div class="p-5 bg-white grid grid-cols-2 md:grid-cols-4 gap-4 items-center">
                        <div class="text-center border-b md:border-b-0 md:border-r border-gray-100 pb-3 md:pb-0">
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Wajib</p>
                            <p class="text-2xl font-black text-gray-700 mt-1">{{ $rekapTahun->wajib }}</p>
                        </div>
                        <div class="text-center border-b md:border-b-0 md:border-r border-gray-100 pb-3 md:pb-0">
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Hadir</p>
                            <p class="text-2xl font-black text-emerald-600 mt-1">{{ $rekapTahun->hadir }}</p>
                        </div>
                        <div class="text-center md:border-r border-gray-100 pt-1 md:pt-0">
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Izin/Sakit</p>
                            <p class="text-2xl font-black text-amber-500 mt-1">{{ $rekapTahun->izin + $rekapTahun->sakit }}</p>
                        </div>
                        <div class="text-center pt-1 md:pt-0">
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Persentase</p>
                            <p class="text-2xl font-black mt-1 {{ $rekapTahun->persen >= 80 ? 'text-emerald-600' : 'text-amber-500' }}">{{ $rekapTahun->persen }}%</p>
                        </div>
                    </div>
                </details>
            </div>
        </div>
    @endif
</div>

@endsection