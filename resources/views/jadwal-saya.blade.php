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
    @forelse($jadwalTerstruktur as $hari => $blokJadwal)
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden flex flex-col h-full hover:shadow-md transition-shadow">
            
            <!-- Header Hari (Gaya Sancod Builder) -->
            <div class="bg-indigo-50/50 px-5 py-4 border-b border-indigo-100 flex justify-between items-center">
                <h3 class="font-black text-indigo-700 text-base tracking-widest uppercase">{{ $hari }}</h3>
                <div class="w-8 h-8 rounded-full bg-white text-indigo-400 flex items-center justify-center shadow-sm border border-indigo-50">
                    <i class="fas fa-calendar-day"></i>
                </div>
            </div>

            <!-- List Pelajaran -->
            <div class="flex-col flex-grow">
                @foreach($blokJadwal as $index => $blok)
                    <div class="flex items-center px-4 py-4 {{ !$loop->last ? 'border-b border-slate-100' : '' }} hover:bg-slate-50 transition-colors duration-150">
                        
                        <!-- Blok Jam -->
                        <div class="w-16 flex flex-col items-center justify-center flex-shrink-0">
                            <span class="text-[10px] font-bold text-slate-400 tracking-widest uppercase mb-0.5">Jam Ke</span>
                            <span class="text-xl font-black text-slate-700">
                                <!-- Logika Pemecah Jam -->
                                {{ $blok['jam_mulai'] == $blok['jam_selesai'] ? $blok['jam_mulai'] : $blok['jam_mulai'].'-'.$blok['jam_selesai'] }}
                            </span>
                        </div>
                        
                        <!-- Garis Pemisah (Divider) -->
                        <div class="w-px h-10 bg-slate-200 mx-4"></div>
                        
                        <!-- Info Pelajaran -->
                        <div class="flex flex-col min-w-0">
                            <!-- Mendukung key 'mata_pelajaran' (Dashboard SPA) atau 'pelajaran' (Web) -->
                            <h4 class="text-base font-bold text-slate-800 leading-tight truncate">
                                {{ $blok['mata_pelajaran'] ?? $blok['pelajaran'] }}
                            </h4>
                            
                            <div class="flex items-center mt-1 text-xs font-semibold text-emerald-600 truncate">
                                <i class="fas fa-chalkboard-teacher mr-1.5 opacity-70"></i> Kelas {{ $blok['kelas'] }}
                            </div>
                            
                            <!-- Tambahan: Menampilkan Nama Kitab jika tersedia -->
                            @if(!empty($blok['nama_kitab']) && $blok['nama_kitab'] != '-')
                                <div class="flex items-center mt-0.5 text-[10px] font-bold text-slate-400 truncate">
                                    <i class="fas fa-book-open mr-1.5 opacity-70"></i> {{ $blok['nama_kitab'] }}
                                </div>
                            @endif
                        </div>

                    </div>
                                    @endforeach
                                </div>
                            </div>
                        @empty
                            <!-- State Kosong jika tidak ada jadwal -->
                            <div class="col-span-full bg-white rounded-2xl border border-slate-200 p-8 text-center shadow-sm">
                                <div class="text-slate-300 text-5xl mb-4"><i class="fas fa-mug-hot"></i></div>
                                <h3 class="text-lg font-black text-slate-600 mb-1">Jadwal Kosong</h3>
                                <p class="text-sm font-bold text-slate-400">Anda belum memiliki jadwal mengajar yang aktif di periode ini.</p>
                            </div>
                        @endforelse
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