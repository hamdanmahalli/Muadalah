@extends('layouts.app')

@section('title', 'Dashboard - Muadalah Wustha')

@section('content')
<div class="space-y-6">

    <!-- ===== HEADER PAGE ===== -->
    <div class="flex flex-col md:flex-row justify-between items-center gap-4">
        <div>
            <h2 class="text-2xl font-black text-slate-800 tracking-tight">Dashboard</h2>
            <p class="text-xs text-slate-500 mt-1 font-medium">Ringkasan kehadiran asatidz secara real-time.</p>
        </div>
        <div class="flex items-center gap-3">
            <span class="bg-white border border-slate-200 text-slate-600 px-4 py-2 rounded-full text-sm font-bold flex items-center shadow-sm">
                <i class="far fa-clock mr-2 text-cyan-500"></i> <span id="live-clock">--:--:--</span>
            </span>
            <span class="bg-gradient-to-r from-cyan-500 to-blue-600 text-white px-4 py-2 rounded-full text-sm font-bold shadow-md shadow-cyan-500/20">
                <i class="far fa-calendar-alt mr-2"></i> {{ \Carbon\Carbon::now()->format('d M Y') }}
            </span>
        </div>
    </div>

    <!-- ===== 4 KARTU STATISTIK ===== -->
    <div class="grid grid-cols-2 gap-3 sm:gap-6 xl:grid-cols-4">

        <!-- Cyan: Total Jadwal -->
        <div class="bg-gradient-to-br from-cyan-400 to-blue-500 rounded-3xl p-4 sm:p-6 text-white shadow-lg shadow-cyan-500/20 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 relative overflow-hidden">
            <div class="flex justify-between items-start mb-4 relative z-10">
                <div class="w-11 h-11 rounded-2xl bg-white/20 backdrop-blur flex items-center justify-center text-lg"><i class="fas fa-calendar-alt"></i></div>
                <span class="bg-white/20 text-[11px] font-bold px-2.5 py-1 rounded-full backdrop-blur border border-white/20">Hari Ini</span>
            </div>
            <p class="text-[11px] font-bold uppercase tracking-wider truncate text-cyan-50/90 relative z-10 mb-1">Total Jadwal</p>
            <div class="flex items-end justify-between relative z-10">
                <h2 class="text-4xl sm:text-4xl font-black relative z-10">{{ $totalJadwal ?? 0 }}</h2>
                <span class="text-xs font-bold bg-white/20 px-2 py-1 rounded-lg mb-1"><i class="fas {{ $deltaTotalJadwal >= 0 ? 'fa-arrow-up' : 'fa-arrow-down' }} mr-1"></i>{{ $deltaTotalJadwal >= 0 ? '+' : '' }}{{ $deltaTotalJadwal }}%</span>
            </div>
            <canvas data-spark="spark-c1" class="absolute left-0 right-0 bottom-0 opacity-40 pointer-events-none z-0" height="46"></canvas>
            <a href="/meja-kontrol" class="relative z-10 flex justify-between items-center pt-4 border-t border-white/20 text-xs font-bold hover:text-white mt-2">
                Buka Meja Kontrol <i class="fas fa-arrow-right"></i>
            </a>
            @if(isset($belumCatatCount) && $belumCatatCount > 0)
            <p class="relative z-10 text-[10px] font-bold text-cyan-100/90 mt-2"><i class="fas fa-hourglass-half mr-1"></i>{{ $belumCatatCount }} jadwal belum tercatat</p>
            @endif
        </div>

        <!-- Emerald: Guru Hadir -->
        <div class="bg-gradient-to-br from-emerald-400 to-teal-600 rounded-3xl p-4 sm:p-6 text-white shadow-lg shadow-emerald-500/20 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 relative overflow-hidden">
            <div class="flex justify-between items-start mb-4 relative z-10">
                <div class="w-11 h-11 rounded-2xl bg-white/20 backdrop-blur flex items-center justify-center text-lg"><i class="fas fa-user-check"></i></div>
                <span class="bg-white/20 text-[11px] font-bold px-2.5 py-1 rounded-full backdrop-blur border border-white/20">Live</span>
            </div>
            <p class="text-[11px] font-bold uppercase tracking-wider truncate text-emerald-50/90 relative z-10 mb-1">Guru Hadir</p>
            <div class="flex items-end justify-between relative z-10">
                <h2 class="text-4xl sm:text-4xl font-black relative z-10">{{ $guruHadir ?? 0 }}</h2>
                <span class="text-xs font-bold bg-white/20 px-2 py-1 rounded-lg mb-1"><i class="fas {{ $deltaGuruHadir >= 0 ? 'fa-arrow-up' : 'fa-arrow-down' }} mr-1"></i>{{ $deltaGuruHadir >= 0 ? '+' : '' }}{{ $deltaGuruHadir }}%</span>
            </div>
            <canvas data-spark="spark-c2" class="absolute left-0 right-0 bottom-0 opacity-40 pointer-events-none z-0" height="46"></canvas>
            <a href="/laporan" class="relative z-10 flex justify-between items-center pt-4 border-t border-white/20 text-xs font-bold hover:text-white mt-2">
                Lihat Laporan <i class="fas fa-arrow-right"></i>
            </a>
        </div>

        <!-- Indigo: Izin / Kelas Kosong -->
        <div class="bg-gradient-to-br from-indigo-500 to-purple-600 rounded-3xl p-4 sm:p-6 text-white shadow-lg shadow-indigo-500/20 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 relative overflow-hidden">
            <div class="flex justify-between items-start mb-4 relative z-10">
                <div class="w-11 h-11 rounded-2xl bg-white/20 backdrop-blur flex items-center justify-center text-lg"><i class="fas fa-exclamation-triangle"></i></div>
                <span class="bg-white/20 text-[11px] font-bold px-2.5 py-1 rounded-full backdrop-blur border border-white/20">Perlu Tindak</span>
            </div>
            <p class="text-[11px] font-bold uppercase tracking-wider truncate text-indigo-50/90 relative z-10 mb-1">Izin / Kelas Kosong</p>
            <div class="flex items-end justify-between relative z-10">
                <h2 class="text-4xl sm:text-4xl font-black relative z-10">{{ $guruIzinKosong ?? 0 }}</h2>
                <span class="text-xs font-bold bg-white/20 px-2 py-1 rounded-lg mb-1"><i class="fas {{ $deltaIzinKosong >= 0 ? 'fa-arrow-up' : 'fa-arrow-down' }} mr-1"></i>{{ $deltaIzinKosong >= 0 ? '+' : '' }}{{ $deltaIzinKosong }}%</span>
            </div>
            @php $rincian = ($guruIzin ?? 0) + ($guruSakit ?? 0) + ($guruAlpa ?? 0) + ($guruMenunggu ?? 0); @endphp
            @if($rincian > 0)
            <p class="relative z-10 text-[10px] font-bold text-indigo-100/90 mt-2 leading-relaxed">
                Izin {{ $guruIzin ?? 0 }} · Sakit {{ $guruSakit ?? 0 }} · Alpa {{ $guruAlpa ?? 0 }} · Menunggu {{ $guruMenunggu ?? 0 }}
            </p>
            @endif
            <canvas data-spark="spark-c3" class="absolute left-0 right-0 bottom-0 opacity-40 pointer-events-none z-0" height="46"></canvas>
            <a href="/meja-kontrol" class="relative z-10 flex justify-between items-center pt-4 border-t border-white/20 text-xs font-bold hover:text-white mt-2">
                Tindak Lanjuti <i class="fas fa-arrow-right"></i>
            </a>
        </div>

        <!-- Biru Gelap: Total Guru -->
        <div class="bg-gradient-to-br from-slate-700 to-slate-900 rounded-3xl p-4 sm:p-6 text-white shadow-lg shadow-slate-700/20 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 relative overflow-hidden">
            <div class="flex justify-between items-start mb-4 relative z-10">
                <div class="w-11 h-11 rounded-2xl bg-white/10 backdrop-blur flex items-center justify-center text-lg"><i class="fas fa-users"></i></div>
                <span class="bg-white/10 text-[11px] font-bold px-2.5 py-1 rounded-full backdrop-blur border border-white/10">Registrasi</span>
            </div>
            <p class="text-[11px] font-bold uppercase tracking-wider truncate text-slate-300 relative z-10 mb-1">Total Pengurus/Guru Terdaftar</p>
            <div class="flex items-end justify-between relative z-10">
                <h2 class="text-4xl sm:text-4xl font-black relative z-10">{{ $totalGuru ?? 0 }}</h2>
                <span class="text-xs font-bold bg-white/10 px-2 py-1 rounded-lg mb-1"><i class="fas fa-equals mr-1"></i>+0%</span>
            </div>
            <canvas data-spark="spark-c4" class="absolute left-0 right-0 bottom-0 opacity-40 pointer-events-none z-0" height="46"></canvas>
            <a href="/master-guru" class="relative z-10 flex justify-between items-center pt-4 border-t border-white/10 text-xs font-bold hover:text-slate-300 mt-2">
                Lihat Data <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>

    <!-- ===== GRID BAWAH: KIRI 60% (TUGAS) + KANAN 40% (GRAFIK, KALENDER, LIST) ===== -->
    <div class="grid grid-cols-1 xl:grid-cols-5 gap-6">

        <!-- ===== KIRI (3/5): RINGKASAN (Periode+Agenda) + MONITORING AKTIF ===== -->
        <div class="xl:col-span-3">
            <!-- Periode & Agenda (sejajar) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    @foreach($kartuTugas as $kartu)
                    @php
                        $warna = $kartu['warna'];
                        $peta = [
                            'sky' => ['bg-sky-100','text-sky-600'],
                            'emerald' => ['bg-emerald-100','text-emerald-600'],
                            'indigo' => ['bg-indigo-100','text-indigo-600'],
                            'rose' => ['bg-rose-100','text-rose-600'],
                        ];
                        [$bgIkon, $warnaIkon] = $peta[$warna] ?? $peta['sky'];
                        $pct = $kartu['pct'] ?? 0;
                    @endphp
                    <a href="{{ $kartu['link'] }}" class="group block bg-white rounded-3xl p-5 shadow-sm border border-slate-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                        <div class="flex items-center gap-3">
                            <div class="w-11 h-11 rounded-2xl {{ $bgIkon }} {{ $warnaIkon }} flex items-center justify-center text-lg flex-shrink-0 group-hover:scale-110 transition-transform">
                                <i class="fas {{ $kartu['ikon'] }}"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-[11px] font-extrabold text-slate-400 uppercase tracking-widest truncate">{{ $kartu['judul'] }}</p>
                                <p class="text-lg font-black text-slate-800 leading-tight truncate group-hover:text-cyan-700 transition">{{ $kartu['sub'] }}</p>
                                <p class="text-sm font-bold text-cyan-600">{{ $pct }}% capaian</p>
                            </div>
                            <div class="flex items-center gap-1 flex-shrink-0 text-cyan-600 text-xs">
                                <i class="fas fa-arrow-right"></i>
                            </div>
                        </div>
                    </a>
                    @endforeach
                </div>

            <!-- MONITORING GURU AKTIF (lebar penuh) -->
            <div class="mt-6 bg-white rounded-3xl p-6 shadow-sm border border-slate-100 flex flex-col">
                    <div class="flex items-center justify-between mb-1">
                        <h3 class="text-sm font-black text-slate-800 tracking-tight"><i class="fas fa-user-clock mr-2 text-cyan-500"></i>Monitoring Guru Aktif</h3>
                        <a href="/monitoring-kehadiran" class="text-[11px] font-bold text-cyan-600 hover:text-cyan-800 flex-shrink-0">Lihat Semua <i class="fas fa-arrow-right text-[9px]"></i></a>
                    </div>
                    <p class="text-[11px] text-slate-500 font-medium mb-3">
                        @if($blokAktif)
                        <i class="fas fa-hourglass-half mr-1 text-cyan-500"></i> {{ $blokAktif['label'] }} · {{ $blokAktif['waktu'] }}
                        @else
                        Belum ada blok jam aktif.
                        @endif
                    </p>

                    <div class="flex flex-wrap items-center gap-2 mb-4">
                        <span class="text-[11px] font-bold bg-cyan-50 text-cyan-700 px-3 py-1 rounded-full">{{ count($monitorGuruAktif) }} guru</span>
                        <span class="text-[11px] font-bold bg-emerald-50 text-emerald-600 px-3 py-1 rounded-full">{{ collect($monitorGuruAktif)->where('status','Hadir')->count() }} hadir</span>
                        <span class="text-[11px] font-bold bg-amber-50 text-amber-700 px-3 py-1 rounded-full">{{ collect($monitorGuruAktif)->where('status','Izin')->count() }} izin</span>
                        <span class="text-[11px] font-bold bg-purple-50 text-purple-700 px-3 py-1 rounded-full">{{ collect($monitorGuruAktif)->where('status','Sakit')->count() }} sakit</span>
                        <span class="text-[11px] font-bold bg-yellow-50 text-yellow-700 px-3 py-1 rounded-full">{{ collect($monitorGuruAktif)->where('status','Menunggu')->count() }} menunggu</span>
                        <span class="text-[11px] font-bold {{ collect($monitorGuruAktif)->where('status','Alpa')->count() > 0 ? 'bg-red-50 text-red-600' : 'bg-slate-100 text-slate-500' }} px-3 py-1 rounded-full">{{ collect($monitorGuruAktif)->where('status','Alpa')->count() }} alpa</span>
                    </div>

                    <div class="flex flex-wrap items-center gap-x-3 gap-y-1 mb-4 px-1 text-[10px] font-bold text-slate-500">
                        <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> Hadir</span>
                        <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span> Izin</span>
                        <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-purple-500"></span> Sakit</span>
                        <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-yellow-500"></span> Menunggu</span>
                        <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-red-500"></span> Alpa</span>
                    </div>

                    <div class="flex-1 space-y-2 lg:overflow-y-auto lg:max-h-[430px]">
                        @forelse($monitorGuruAktif as $g)
                        @php
                            $st = $g['status'];
                            $warnaStatus = [
                                'Hadir' => 'bg-emerald-100 text-emerald-700',
                                'Izin' => 'bg-amber-100 text-amber-700',
                                'Sakit' => 'bg-purple-100 text-purple-700',
                                'Alpa' => 'bg-red-100 text-red-700',
                                'Menunggu' => 'bg-yellow-100 text-yellow-700',
                            ][$st] ?? 'bg-slate-100 text-slate-600';
                            $warnaAvatar = [
                                'Hadir' => 'from-emerald-400 to-teal-500',
                                'Izin' => 'from-amber-400 to-orange-500',
                                'Sakit' => 'from-purple-400 to-indigo-500',
                                'Alpa' => 'from-red-400 to-rose-500',
                                'Menunggu' => 'from-yellow-400 to-amber-500',
                            ][$st] ?? 'from-slate-400 to-slate-600';
                        @endphp
                        <div class="flex items-center gap-3 p-2.5 rounded-2xl bg-slate-50/70 hover:bg-slate-100 transition">
                            <div class="w-9 h-9 rounded-full bg-gradient-to-br {{ $warnaAvatar }} flex items-center justify-center text-white text-xs font-black flex-shrink-0">
                                {{ strtoupper(substr($g['nama'], 0, 1)) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-bold text-slate-800 truncate" title="{{ $g['nama'] }}">{{ $g['nama'] }}</p>
                                <p class="text-[11px] text-slate-500 font-medium truncate" title="{{ $g['kelas'] }} · Jam Ke-{{ $g['jam_ke'] }}">{{ $g['kelas'] }} · Jam Ke-{{ $g['jam_ke'] }}</p>
                            </div>
                            <span class="text-[10px] font-bold px-2 py-1 rounded-full {{ $warnaStatus }} flex-shrink-0">{{ $st }}</span>
                            <a href="/master-guru{{ $g['nig'] ? '?cari='.$g['nig'] : '' }}" class="text-[10px] font-bold text-cyan-600 bg-cyan-50 hover:bg-cyan-100 px-2.5 py-1 rounded-full flex-shrink-0 transition">Profil</a>
                        </div>
                        @empty
                        <div class="text-center py-8">
                            <div class="w-14 h-14 mx-auto rounded-full bg-emerald-50 text-emerald-500 flex items-center justify-center text-2xl mb-3"><i class="fas fa-check-circle"></i></div>
                            <p class="text-sm font-bold text-slate-700">Tidak ada guru terjadwal</p>
                            <p class="text-xs text-slate-400 mt-1">Belum ada jadwal pada blok jam aktif.</p>
                        </div>
                        @endforelse
                    </div>
                </div>

            </div>

        <!-- ===== KANAN (2/5) ===== -->
        <div class="xl:col-span-2 space-y-4">

            <!-- GRAFIK UTAMA: Area Chart -->
            <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-sm font-black text-slate-800 tracking-tight">Statistik Kehadiran Minggu Ini</h3>
                        <p class="text-[11px] text-slate-400 font-medium mt-0.5">Rekap Hadir / Izin / Alpa harian</p>
                    </div>
                    <div class="flex items-center gap-1">
                        <button class="w-8 h-8 rounded-lg bg-slate-50 text-slate-500 hover:bg-cyan-50 hover:text-cyan-600 flex items-center justify-center text-sm transition" onclick="setChartType('line')" title="Garis"><i class="fas fa-chart-line"></i></button>
                        <button class="w-8 h-8 rounded-lg bg-slate-50 text-slate-500 hover:bg-cyan-50 hover:text-cyan-600 flex items-center justify-center text-sm transition" onclick="setChartType('bar')" title="Batang"><i class="fas fa-chart-bar"></i></button>
                    </div>
                </div>
                <div class="relative h-52">
                    <canvas id="areaChart"></canvas>
                </div>
            </div>

            <!-- STRIP KALENDER MINGGU INI -->
            <div class="bg-white rounded-3xl p-5 shadow-sm border border-slate-100">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-black text-slate-800 tracking-tight"><i class="far fa-calendar-alt mr-2 text-cyan-500"></i>Minggu Ini</h3>
                    <span class="text-[11px] font-bold text-slate-400">{{ \Carbon\Carbon::now()->format('M Y') }}</span>
                </div>
                <div class="grid grid-cols-7 gap-1.5">
                    @foreach($stripMinggu as $hari)
                    <div class="flex flex-col items-center py-2 rounded-2xl {{ $hari['aktif'] ? 'bg-gradient-to-b from-cyan-500 to-blue-600 text-white shadow-md shadow-cyan-500/30' : 'hover:bg-slate-50 text-slate-600' }} transition">
                        <span class="text-[10px] font-bold uppercase {{ $hari['aktif'] ? 'text-white/80' : 'text-slate-400' }}">{{ $hari['nama'] }}</span>
                        <span class="text-lg font-black {{ $hari['aktif'] ? 'text-white' : 'text-slate-700' }}">{{ $hari['tanggal'] }}</span>
                        <span class="text-[9px] font-semibold {{ $hari['aktif'] ? 'text-white/70' : 'text-slate-400' }}">{{ $hari['bulan'] }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function(){
    var labels = @json($labelsGrafik);
    var hadir  = @json($dataHadirGrafik);
    var izin   = @json($dataIzinGrafik);
    var alpa   = @json($dataKosongGrafik);
    var menunggu = @json($dataMenungguGrafik);

    // Area chart utama
    var ctx = document.getElementById('areaChart');
    if (ctx) {
        var mainChart = new Chart(ctx.getContext('2d'), {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Hadir',
                        data: hadir,
                        borderColor: '#0ea5e9',
                        backgroundColor: 'rgba(14,165,233,0.15)',
                        fill: true,
                        tension: 0.4,
                        borderWidth: 2,
                        pointBackgroundColor: '#0ea5e9',
                        pointRadius: 3
                    },
                    {
                        label: 'Izin',
                        data: izin,
                        borderColor: '#f59e0b',
                        backgroundColor: 'rgba(245,158,11,0.10)',
                        fill: true,
                        tension: 0.4,
                        borderWidth: 2,
                        pointBackgroundColor: '#f59e0b',
                        pointRadius: 3
                    },
                    {
                        label: 'Alpa',
                        data: alpa,
                        borderColor: '#ef4444',
                        backgroundColor: 'transparent',
                        fill: false,
                        tension: 0.4,
                        borderWidth: 2,
                        pointBackgroundColor: '#ef4444',
                        pointRadius: 3
                    },
                    {
                        label: 'Menunggu',
                        data: menunggu,
                        borderColor: '#eab308',
                        borderDash: [5, 4],
                        backgroundColor: 'transparent',
                        fill: false,
                        tension: 0.4,
                        borderWidth: 1.5,
                        pointBackgroundColor: '#eab308',
                        pointRadius: 2
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: true, position: 'bottom', labels: { usePointStyle: true, boxWidth: 8, font: { size: 10, weight: 600 } } }
                },
                scales: {
                    y: { beginAtZero: true, grid: { color: '#f1f5f9' }, ticks: { precision: 0, font: { size: 10 } } },
                    x: { grid: { display: false }, ticks: { font: { size: 10, weight: 600 } } }
                }
            }
        });

        window.setChartType = function(type) {
            var showFill = (type === 'line');
            mainChart.data.datasets.forEach(function(ds){
                ds.fill = showFill;
                ds.tension = showFill ? 0.4 : 0;
            });
            mainChart.config.type = type;
            mainChart.update();
        };
    }

    // Sparkline di tiap kartu (dataset per kartu)
    var sparkMap = {
        'spark-c1': @json($sparkJadwal),
        'spark-c2': @json($sparkHadir),
        'spark-c3': @json($sparkIzin),
        'spark-c4': [@php echo count($sparkJadwal) ? implode(',', array_fill(0, count($sparkJadwal), (int)$totalGuru)) : '1,2,1,3,2'; @endphp]
    };
    document.querySelectorAll('canvas[data-spark]').forEach(function(cv){
        var id = cv.getAttribute('data-spark');
        var data = (sparkMap[id] && sparkMap[id].length) ? sparkMap[id] : [1,2,1,3,2];
        new Chart(cv.getContext('2d'), {
            type: 'line',
            data: {
                labels: data.map(function(_,i){ return i+1; }),
                datasets: [{ data: data, borderColor: 'rgba(255,255,255,0.9)', backgroundColor: 'rgba(255,255,255,0.25)', borderWidth: 2, fill: true, tension: 0.4, pointRadius: 0 }]
            },
            options: {
                responsive: false,
                plugins: { legend: { display: false }, tooltip: { enabled: false } },
                scales: { x: { display: false }, y: { display: false } }
            }
        });
    });
})();
</script>
@endpush
