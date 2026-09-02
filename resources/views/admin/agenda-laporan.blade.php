@extends('layouts.app')

@section('title', 'Laporan Kehadiran - ' . $agenda->nama_kegiatan)

@section('content')
<!-- Tombol Kembali & Header -->
<div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
    <div class="flex items-center">
        <a href="/agenda-kegiatan" class="w-10 h-10 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-slate-500 hover:bg-slate-50 hover:text-indigo-600 transition-colors mr-4 shadow-sm">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h2 class="text-2xl font-black text-slate-800 tracking-tight">{{ $agenda->nama_kegiatan }}</h2>
            <p class="text-sm font-bold text-slate-400 mt-0.5">
                <i class="fas fa-calendar-alt mr-1"></i> {{ \Carbon\Carbon::parse($agenda->tanggal)->translatedFormat('d F Y') }} • <i class="fas fa-clock mx-1"></i> {{ \Carbon\Carbon::parse($agenda->jam_mulai)->format('H:i') }} WIB
            </p>
        </div>
    </div>
    
    <div class="flex items-center gap-3">
        <!-- TOMBOL SCAN QR GURU -->
        <a href="/agenda-kegiatan/{{ $agenda->id }}/scan-qr" class="inline-flex items-center justify-center px-5 py-2.5 bg-emerald-50 text-emerald-600 hover:bg-emerald-500 hover:text-white border border-emerald-200 hover:border-emerald-500 font-bold text-sm rounded-xl transition-all shadow-sm">
            <i class="fas fa-qrcode mr-2"></i> Scan QR Guru
        </a>

        <!-- TOMBOL CETAK PDF -->
        <a href="/agenda-kegiatan/{{ $agenda->id }}/pdf" target="_blank" class="inline-flex items-center justify-center px-5 py-2.5 bg-rose-50 text-rose-600 hover:bg-rose-500 hover:text-white border border-rose-200 hover:border-rose-500 font-bold text-sm rounded-xl transition-all shadow-sm">
            <i class="fas fa-file-pdf mr-2"></i> Cetak PDF Laporan
        </a>
    </div>
</div>
@if(session('sukses'))
<div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl mb-6 flex items-center shadow-sm">
    <i class="fas fa-check-circle text-xl mr-3"></i>
    <span class="font-bold text-sm">{{ session('sukses') }}</span>
</div>
@endif

<!-- Kartu Statistik Analitik (Tercatat mencakup Hadir, Izin, & Sakit) -->
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
        <div>
            <p class="text-[10px] uppercase font-bold tracking-widest text-slate-400 mb-1">Total Guru</p>
            <p class="text-2xl font-black text-slate-700">{{ $totalGuru }}</p>
        </div>
        <div class="w-10 h-10 rounded-full bg-slate-50 flex items-center justify-center text-slate-400"><i class="fas fa-users"></i></div>
    </div>
    
    <div class="bg-white p-5 rounded-2xl border border-emerald-100 shadow-sm flex items-center justify-between">
        <div>
            <!-- LABEL DISEMATKAN: Tercatat mencakup Hadir/Izin/Sakit -->
            <p class="text-[10px] uppercase font-bold tracking-widest text-emerald-500 mb-1">Tercatat (Masuk)</p>
            <p id="statistik-hadir" class="text-2xl font-black text-emerald-600">{{ $totalHadir }}</p>
        </div>
        <div class="w-10 h-10 rounded-full bg-emerald-50 text-emerald-500 flex items-center justify-center"><i class="fas fa-clipboard-check"></i></div>
    </div>

    <div class="bg-white p-5 rounded-2xl border border-rose-100 shadow-sm flex items-center justify-between">
        <div>
            <p class="text-[10px] uppercase font-bold tracking-widest text-rose-400 mb-1">Belum Absen</p>
            <p id="statistik-belum" class="text-2xl font-black text-rose-500">{{ $belumHadir }}</p>
        </div>
        <div class="w-10 h-10 rounded-full bg-rose-50 text-rose-400 flex items-center justify-center"><i class="fas fa-times-circle"></i></div>
    </div>

    <div class="bg-indigo-600 p-5 rounded-2xl shadow-md flex items-center justify-between text-white relative overflow-hidden">
        <div class="absolute -right-4 -top-4 w-16 h-16 bg-white opacity-10 rounded-full blur-xl"></div>
        <div class="relative z-10">
            <p class="text-[10px] uppercase font-bold tracking-widest text-indigo-200 mb-1">Partisipasi</p>
            <p class="text-2xl font-black">{{ $persentase }}%</p>
        </div>
    </div>
</div>

<!-- Layout Dua Kolom: Tercatat vs Belum Absen -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

    <!-- TABEL: SUDAH TERCATAT (Hadir/Izin/Sakit) -->
    <div class="bg-white rounded-2xl border border-emerald-100 shadow-sm overflow-hidden flex flex-col h-full max-h-[600px]">
        <div class="px-5 py-4 border-b border-emerald-50 flex justify-between items-center bg-emerald-50/30 shrink-0">
            <h3 class="text-sm font-black text-emerald-700 uppercase tracking-widest"><i class="fas fa-clipboard-check mr-2"></i>Data Masuk</h3>
            <span id="badge-total-hadir" class="bg-emerald-100 text-emerald-600 text-[10px] font-bold px-2 py-1 rounded-md">{{ count($dataHadir) }} Data</span>
        </div>
        
        <div id="container-list-hadir" class="overflow-y-auto scrollbar-none flex-1 p-2">
            @forelse($dataHadir as $index => $hadir)
                @php
                    $statusColor = $hadir->status == 'Hadir' ? 'text-emerald-600 bg-emerald-50 border-emerald-200' : 
                                  ($hadir->status == 'Izin' ? 'text-amber-600 bg-amber-50 border-amber-200' : 'text-rose-600 bg-rose-50 border-rose-200');
                @endphp
                <div class="p-3 border-b border-slate-50 flex items-center justify-between hover:bg-slate-50 transition-colors rounded-xl">
                    <div class="flex items-center min-w-0">
                        <div class="w-8 h-8 rounded-full bg-slate-100 text-slate-500 font-bold text-xs flex items-center justify-center mr-3 shrink-0">
                            {{ $index + 1 }}
                        </div>
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <p class="text-sm font-black text-slate-700 truncate">{{ $hadir->guru->nama_guru }}</p>
                                <span class="px-2 py-0.5 rounded-md border text-[8px] font-black uppercase tracking-wider {{ $statusColor }} shrink-0">{{ $hadir->status }}</span>
                            </div>
                            <p class="text-[10px] font-bold text-slate-400 mt-0.5 truncate">
                                <i class="fas fa-clock mr-1"></i> {{ \Carbon\Carbon::parse($hadir->waktu_hadir)->format('H:i') }} 
                                <span class="mx-1">•</span> <i class="fas fa-fingerprint mr-1 text-indigo-500"></i> {{ $hadir->metode }}
                                @if($hadir->keterangan)
                                    <span class="mx-1">•</span> <i class="fas fa-comment-dots mr-1"></i> {{ $hadir->keterangan }}
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center">
                    <div class="text-slate-300 text-3xl mb-2"><i class="fas fa-user-clock"></i></div>
                    <p class="text-sm font-bold text-slate-500">Belum ada data masuk.</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- TABEL: BELUM HADIR & FORM MANUAL -->
    <div class="bg-white rounded-2xl border border-rose-100 shadow-sm overflow-hidden flex flex-col h-full max-h-[600px]">
        <div class="px-5 py-4 border-b border-rose-50 flex justify-between items-center bg-rose-50/30 shrink-0">
            <h3 class="text-sm font-black text-rose-700 uppercase tracking-widest"><i class="fas fa-user-times mr-2"></i>Belum Absen</h3>
            <!-- Tambahkan id="badge-total-belum" di sini -->
            <span id="badge-total-belum" class="bg-rose-100 text-rose-600 text-[10px] font-bold px-2 py-1 rounded-md">{{ count($dataBelumHadir) }} Guru</span>
        </div>
        
        <div id="container-list-belum" class="overflow-y-auto scrollbar-none flex-1 p-2">
            @forelse($dataBelumHadir as $belum)
                <div class="p-3 border-b border-slate-50 flex flex-col xl:flex-row xl:items-center justify-between hover:bg-slate-50 transition-colors rounded-xl gap-3">
                    <div class="flex items-center">
                        <div class="w-8 h-8 rounded-full bg-slate-100 text-slate-400 font-bold text-xs flex items-center justify-center mr-3 shrink-0"><i class="fas fa-user"></i></div>
                        <p class="text-sm font-bold text-slate-600">{{ $belum->nama_guru }}</p>
                    </div>
                    
                    <!-- FORM INPUT MANUAL DENGAN IZIN/SAKIT -->
                    <form action="/agenda-kegiatan/{{ $agenda->id }}/manual" method="POST" class="flex items-center gap-1.5 shrink-0">
                        @csrf
                        <input type="hidden" name="guru_id" value="{{ $belum->id }}">
                        
                        <select name="status" class="bg-white border border-slate-200 text-slate-600 text-[10px] font-bold rounded-lg px-2 py-1.5 outline-none focus:border-indigo-500 shadow-sm cursor-pointer">
                            <option value="Hadir">Hadir</option>
                            <option value="Izin">Izin</option>
                            <option value="Sakit">Sakit</option>
                        </select>
                        
                        <input type="text" name="keterangan" placeholder="Alasan..." class="bg-white border border-slate-200 text-slate-600 text-[10px] rounded-lg px-2 py-1.5 w-24 outline-none focus:border-indigo-500 shadow-sm">
                        
                        <button type="submit" title="Simpan" class="bg-indigo-50 hover:bg-indigo-500 text-indigo-600 hover:text-white w-7 h-7 rounded-lg text-[10px] flex items-center justify-center transition-colors border border-indigo-100 shadow-sm active:scale-95">
                            <i class="fas fa-save"></i>
                        </button>
                    </form>
                </div>
            @empty
                <div class="p-8 text-center">
                    <div class="text-emerald-300 text-4xl mb-3"><i class="fas fa-party-horn"></i></div>
                    <p class="text-base font-black text-emerald-600">Alhamdulillah!</p>
                    <p class="text-sm font-medium text-slate-500">Seluruh status guru telah tercatat.</p>
                </div>
            @endforelse
        </div>
    </div>

</div>

<!-- SCRIPT POLING REAL-TIME (UPDATE KEDUA TABEL & STATISTIK) -->
<script>
    setInterval(function() {
        fetch('/api/agenda-kegiatan/{{ $agenda->id }}/realtime')
            .then(response => response.json())
            .then(res => {
                // 1. Perbarui Angka Statistik Banner Atas
                document.getElementById('statistik-hadir').innerText = res.total_hadir;
                document.getElementById('statistik-belum').innerText = res.total_belum;
                document.getElementById('badge-total-hadir').innerText = res.total_hadir + ' Data';
                
                // PERBAIKAN: Perbarui juga badge jumlah di header kotak Belum Absen
                let badgeBelum = document.getElementById('badge-total-belum');
                if(badgeBelum) {
                    badgeBelum.innerText = res.total_belum + ' Guru';
                }

                // 2. Render Ulang Tabel "Sudah Tercatat" (Data Masuk)
                let htmlHadir = '';
                if(res.data_hadir.length > 0) {
                    res.data_hadir.forEach((item, index) => {
                        let colorClass = item.status === 'Hadir' ? 'text-emerald-600 bg-emerald-50 border-emerald-200' : (item.status === 'Izin' ? 'text-amber-600 bg-amber-50 border-amber-200' : 'text-rose-600 bg-rose-50 border-rose-200');
                        htmlHadir += `
                            <div class="p-3 border-b border-slate-50 flex items-center justify-between hover:bg-slate-50 transition-colors rounded-xl">
                                <div class="flex items-center min-w-0">
                                    <div class="w-8 h-8 rounded-full bg-slate-100 text-slate-500 font-bold text-xs flex items-center justify-center mr-3 shrink-0">${index + 1}</div>
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2">
                                            <p class="text-sm font-black text-slate-700 truncate">${item.nama_guru}</p>
                                            <span class="px-2 py-0.5 rounded-md border text-[8px] font-black uppercase tracking-wider ${colorClass} shrink-0">${item.status}</span>
                                        </div>
                                        <p class="text-[10px] font-bold text-slate-400 mt-0.5 truncate">
                                            <i class="fas fa-clock mr-1"></i> ${item.waktu} 
                                            <span class="mx-1">•</span> <i class="fas fa-fingerprint mr-1 text-indigo-500"></i> ${item.metode}
                                            ${item.keterangan ? `<span class="mx-1">•</span> <i class="fas fa-comment-dots mr-1"></i> ${item.keterangan}` : ''}
                                        </p>
                                    </div>
                                </div>
                            </div>`;
                    });
                } else {
                    htmlHadir = `<div class="p-8 text-center"><div class="text-slate-300 text-3xl mb-2"><i class="fas fa-user-clock"></i></div><p class="text-sm font-bold text-slate-500">Belum ada data masuk.</p></div>`;
                }
                document.getElementById('container-list-hadir').innerHTML = htmlHadir;

                // 3. Render Ulang Tabel "Belum Absen" beserta Form Manualnya
                let htmlBelum = '';
                if(res.data_belum.length > 0) {
                    res.data_belum.forEach((guru) => {
                        htmlBelum += `
                            <div class="p-3 border-b border-slate-50 flex flex-col xl:flex-row xl:items-center justify-between hover:bg-slate-50 transition-colors rounded-xl gap-3">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 rounded-full bg-slate-100 text-slate-400 font-bold text-xs flex items-center justify-center mr-3 shrink-0"><i class="fas fa-user"></i></div>
                                    <p class="text-sm font-bold text-slate-600">${guru.nama_guru}</p>
                                </div>
                                
                                <form action="/agenda-kegiatan/{{ $agenda->id }}/manual" method="POST" class="flex items-center gap-1.5 shrink-0">
                                    <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}'}">
                                    <input type="hidden" name="guru_id" value="${guru.id}">
                                    
                                    <select name="status" class="bg-white border border-slate-200 text-slate-600 text-[10px] font-bold rounded-lg px-2 py-1.5 outline-none focus:border-indigo-500 shadow-sm cursor-pointer">
                                        <option value="Hadir">Hadir</option>
                                        <option value="Izin">Izin</option>
                                        <option value="Sakit">Sakit</option>
                                    </select>
                                    
                                    <input type="text" name="keterangan" placeholder="Alasan..." class="bg-white border border-slate-200 text-slate-600 text-[10px] rounded-lg px-2 py-1.5 w-24 outline-none focus:border-indigo-500 shadow-sm">
                                    
                                    <button type="submit" title="Simpan" class="bg-indigo-50 hover:bg-indigo-500 text-indigo-600 hover:text-white w-7 h-7 rounded-lg text-[10px] flex items-center justify-center transition-colors border border-indigo-100 shadow-sm active:scale-95">
                                        <i class="fas fa-save"></i>
                                    </button>
                                </form>
                            </div>`;
                    });
                } else {
                    htmlBelum = `
                        <div class="p-8 text-center">
                            <div class="text-emerald-300 text-4xl mb-3"><i class="fas fa-party-horn"></i></div>
                            <p class="text-base font-black text-emerald-600">Alhamdulillah!</p>
                            <p class="text-sm font-medium text-slate-500">Seluruh status guru telah tercatat.</p>
                        </div>`;
                }
                // PERBAIKAN: jika pengguna sedang mengisi form (select/input) di kolom "Belum Absen",
                // jangan render ulang agar pilihan Izin/Sakit tidak berubah kembali ke Hadir saat polling berjalan.
                let listBelumEl = document.getElementById('container-list-belum');
                let sedangMengisi = listBelumEl && listBelumEl.contains(document.activeElement);
                if (!sedangMengisi) {
                    document.getElementById('container-list-belum').innerHTML = htmlBelum;
                }

            })
            .catch(err => console.error("Gagal menyinkronkan data real-time:", err));
    }, 4000);
</script>
@endsection