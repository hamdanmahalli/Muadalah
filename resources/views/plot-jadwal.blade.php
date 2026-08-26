@extends('layouts.app')

@section('title', 'Plotting Target Mengajar')

@section('content')
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
    <style>
        .ts-control { border-radius: 0.5rem !important; border: 1px solid #e5e7eb !important; padding: 0.5rem 0.75rem !important; font-size: 0.875rem !important; background-color: #f9fafb !important; }
        .ts-control.focus { border-color: #6366f1 !important; background-color: #ffffff !important; box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15) !important; }
        .ts-dropdown { border-radius: 0.5rem !important; font-size: 0.875rem !important; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1) !important; }
        .ts-wrapper.single .ts-control:after { right: 0.8rem !important; }
        
        /* MENGHILANGKAN TOMBOL NAIK-TURUN (SPINNER) PADA INPUT NUMBER */
        input[type="number"]::-webkit-inner-spin-button,
        input[type="number"]::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        input[type="number"] {
            -moz-appearance: textfield; /* Untuk Browser Firefox */
        }
    </style>

    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800"><i class="fas fa-sitemap mr-2 text-indigo-600"></i> Plotting Target Mengajar</h2>
            <p class="text-xs text-gray-500 mt-0.5">Pengaturan Alokasi Jam Mengajar Per Kelas</p>
        </div>
        
        <div id="status-autosave" class="hidden items-center px-4 py-2 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-xl text-xs font-bold shadow-sm transition animate-fade-in">
            <i class="fas fa-check-circle mr-2 text-sm text-emerald-500"></i> <span id="text-autosave">Perubahan tersimpan otomatis</span>
        </div>
    </div>

    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 mb-6">
        <form action="/master-plot-jadwal" method="GET" class="flex items-end space-x-4">
            <div class="w-full md:w-1/3">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Pilih Kelas yang Ingin Diatur:</label>
                <select name="kelas_id" class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-indigo-500 outline-none font-medium text-gray-700 bg-white" onchange="this.form.submit()">
                    <option value="">-- Pilih Kelas --</option>
                    @foreach($kelas as $k)
                        <option value="{{ $k->id }}" {{ $kelas_id == $k->id ? 'selected' : '' }}>Kelas {{ $k->nama_kelas }}</option>
                    @endforeach
                </select>
            </div>
            @if(!$kelas_id)
                <div class="text-sm text-gray-400 italic mb-3">Silakan pilih kelas terlebih dahulu untuk memunculkan matriks pelajaran.</div>
            @endif
        </form>
    </div>

    @if($kelas_id)
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white border-b-4 border-indigo-500 rounded-xl shadow-sm p-4 flex flex-col justify-center">
                <span class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Kapasitas Maksimal</span>
                <div class="flex items-center justify-between">
                    <span class="text-2xl font-black text-gray-800">{{ $kapasitasMaksimal }} <span class="text-sm font-semibold text-gray-500">Jam</span></span>
                    <i class="fas fa-building text-indigo-100 text-3xl"></i>
                </div>
            </div>
            
            <div id="card-total-target" class="bg-white border-b-4 {{ $totalTarget > $kapasitasMaksimal ? 'border-red-500 bg-red-50' : ($totalTarget < $kapasitasMaksimal ? 'border-amber-400 bg-amber-50' : 'border-blue-500') }} rounded-xl shadow-sm p-4 flex flex-col justify-center transition-colors duration-300">
                <span class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Total Di-Plot</span>
                <div class="flex items-center justify-between">
                    <span id="stat-total-target" class="text-2xl font-black {{ $totalTarget > $kapasitasMaksimal ? 'text-red-600' : ($totalTarget < $kapasitasMaksimal ? 'text-amber-600' : 'text-gray-800') }}">{{ $totalTarget }} <span class="text-sm font-semibold opacity-70">Jam</span></span>
                    <i class="fas fa-bullseye {{ $totalTarget < $kapasitasMaksimal ? 'text-amber-200' : 'text-blue-100' }} text-3xl" id="icon-total-target"></i>
                </div>
            </div>
            
            <div class="bg-white border-b-4 border-emerald-500 rounded-xl shadow-sm p-4 flex flex-col justify-center">
                <span class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Sudah Terjadwal</span>
                <div class="flex items-center justify-between">
                    <span id="stat-total-terjadwal" class="text-2xl font-black text-gray-800">{{ $totalTerjadwal }} <span class="text-sm font-semibold text-gray-500">Jam</span></span>
                    <i class="fas fa-calendar-check text-emerald-100 text-3xl"></i>
                </div>
            </div>
            
            @php $sisaBelum = $totalTarget - $totalTerjadwal; @endphp
            <div class="bg-white border-b-4 {{ $sisaBelum > 0 ? 'border-amber-500 bg-amber-50' : 'border-gray-300' }} rounded-xl shadow-sm p-4 flex flex-col justify-center">
                <span class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Sisa Target</span>
                <div class="flex items-center justify-between">
                    <span id="stat-sisa-belum" class="text-2xl font-black {{ $sisaBelum > 0 ? 'text-amber-600' : 'text-gray-400' }}">{{ $sisaBelum }} <span class="text-sm font-semibold opacity-70">Jam</span></span>
                    <i class="fas fa-clock text-amber-200 text-3xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden overflow-x-auto mb-6">
            <table class="min-w-full divide-y divide-gray-100">
                <thead class="bg-gray-50/80">
                    <tr>
                        <th class="px-5 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider w-12 border-b border-gray-100">No</th>
                        <th class="px-5 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider border-b border-gray-100">Mata Pelajaran</th>
                        <th class="px-5 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider border-b border-gray-100">Kitab / Referensi</th>
                        <th class="px-5 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider w-24 border-b border-gray-100">Target</th>
                        <th class="px-5 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider w-28 border-b border-gray-100">Terjadwal</th>
                        <th class="px-5 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider w-32 border-b border-gray-100">Status</th>
                        <th class="px-5 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider w-64 border-b border-gray-100">Guru Pengajar</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @foreach($pelajarans as $index => $pelajaran)
                        @php 
                            $plot = $plotAktif->get($pelajaran->id);
                            $targetJam = $plot ? $plot->beban_jam : 2;
                            $terjadwal = $terjadwalPerMapel->get($pelajaran->id, 0);
                            $selisih = $targetJam - $terjadwal;
                        @endphp
                        <tr class="hover:bg-indigo-50/30 transition duration-200 group" data-pelajaran-id="{{ $pelajaran->id }}">
                            <td class="px-5 py-3 whitespace-nowrap text-sm text-gray-400 font-medium">{{ $index + 1 }}</td>
                            
                            <td class="px-5 py-3 whitespace-nowrap">
                                <span class="font-bold text-gray-800 text-sm">{{ $pelajaran->nama_pelajaran }}</span>
                            </td>

                            <td class="px-5 py-3 whitespace-nowrap">
                                <span class="text-xs text-gray-500 font-medium bg-gray-50 px-2.5 py-1 rounded-md border border-gray-100">{{ $pelajaran->nama_kitab ?? '-' }}</span>
                            </td>
                            
                            <td class="px-5 py-3 whitespace-nowrap text-center">
                                <input type="number" id="target-{{ $pelajaran->id }}" value="{{ $targetJam }}" min="0" onchange="autoSimpanRow({{ $pelajaran->id }})" class="w-16 bg-gray-50 border border-gray-200 rounded-lg p-1.5 text-center text-sm font-bold text-gray-700 focus:bg-white focus:ring-2 focus:ring-indigo-500 outline-none transition group-hover:bg-white shadow-sm">
                            </td>
                            
                            <td class="px-5 py-3 whitespace-nowrap text-center">
                                <span id="terjadwal-{{ $pelajaran->id }}" class="text-lg font-black {{ $terjadwal > 0 ? 'text-indigo-600' : 'text-gray-300' }}">{{ $terjadwal }}</span>
                            </td>
                            
                            <td class="px-5 py-3 whitespace-nowrap text-center" id="status-container-{{ $pelajaran->id }}">
                                @if($targetJam == 0)
                                    <span class="px-3 py-1 text-[11px] font-bold rounded-full bg-gray-100 text-gray-500 shadow-sm"><i class="fas fa-minus mr-1"></i> Kosong</span>
                                @elseif($selisih == 0)
                                    <span class="px-3 py-1 text-[11px] font-bold rounded-full bg-emerald-100 text-emerald-700 shadow-sm"><i class="fas fa-check mr-1"></i> Tuntas</span>
                                @elseif($selisih > 0)
                                    <span class="px-3 py-1 text-[11px] font-bold rounded-full bg-amber-100 text-amber-700 shadow-sm"><i class="fas fa-exclamation-circle mr-1"></i> Sisa {{ $selisih }}</span>
                                @else
                                    <span class="px-3 py-1 text-[11px] font-bold rounded-full bg-rose-100 text-rose-700 shadow-sm"><i class="fas fa-arrow-up mr-1"></i> Lebih {{ abs($selisih) }}</span>
                                @endif
                            </td>
                            
                            <td class="px-5 py-3 whitespace-nowrap">
                                <select id="guru-{{ $pelajaran->id }}" onchange="autoSimpanRow({{ $pelajaran->id }})" class="searchable-guru w-full" placeholder="Ketik nama guru...">
                                    <option value="">-- Kosong / Belum Ada --</option>
                                    @foreach($gurus as $guru)
                                        <option value="{{ $guru->id }}" {{ ($plot && $plot->guru_id == $guru->id) ? 'selected' : '' }}>
                                            {{ $guru->nama_guru }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>

                            <!-- TAMBAHAN: KOLOM AKSI (MUTASI) -->
                            <td class="px-5 py-3 whitespace-nowrap text-center">
                                @if($plot && $plot->guru_id)
                                    <a href="/plot-jadwal/{{ $plot->id }}/mutasi" class="inline-flex items-center justify-center text-[10px] bg-indigo-50 text-indigo-600 px-3 py-1.5 rounded-lg font-bold hover:bg-indigo-600 hover:text-white transition-colors border border-indigo-100 shadow-sm">
                                        <i class="fas fa-exchange-alt mr-1.5"></i> Mutasi
                                    </a>
                                @else
                                    <span class="text-[10px] text-gray-400 italic px-2">Menunggu Plotting</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <div id="modal-overload" class="hidden fixed inset-0 bg-gray-900 bg-opacity-70 overflow-y-auto h-full w-full z-50 flex items-center justify-center backdrop-blur-sm animate-fade-in">
        <div class="relative mx-auto p-6 border w-full max-w-md shadow-2xl rounded-2xl bg-white text-center">
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100 mb-4 shadow-inner">
                <i class="fas fa-exclamation-triangle text-2xl text-red-600 animate-bounce"></i>
            </div>
            <h3 class="text-xl font-extrabold text-gray-800 mb-2">Peringatan Kapasitas</h3>
            <div id="pesan-overload" class="text-sm text-gray-700 mb-6 bg-red-50 p-4 rounded-xl border border-red-100 text-left leading-relaxed"></div>
            <button onclick="document.getElementById('modal-overload').classList.add('hidden')" class="w-full py-3 bg-red-600 hover:bg-red-700 text-white font-bold rounded-xl shadow-md transition duration-200">
                <i class="fas fa-check mr-2"></i> Mengerti, Saya Ubah
            </button>
        </div>
    </div>

    <div id="modal-bentrok-ajax" class="hidden fixed inset-0 bg-gray-900 bg-opacity-70 overflow-y-auto h-full w-full z-50 flex items-center justify-center backdrop-blur-sm animate-fade-in">
        <div class="relative mx-auto p-6 border w-full max-w-lg shadow-2xl rounded-3xl bg-white">
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-amber-100 mb-4 shadow-inner">
                <i class="fas fa-exclamation-triangle text-3xl text-amber-600 animate-pulse"></i>
            </div>
            <h3 class="text-xl font-extrabold text-gray-800 mb-2 text-center">Konfirmasi Bentrok Guru</h3>
            <p id="pesan-bentrok-ajax" class="text-sm text-gray-600 mb-4 text-center"></p>
            
            <div class="text-sm text-gray-700 mb-6 bg-amber-50 p-4 rounded-xl border border-amber-100 text-left leading-relaxed max-h-48 overflow-y-auto shadow-inner">
                <ul id="rincian-bentrok-ajax" class="list-disc pl-5"></ul>
            </div>

            <input type="hidden" id="pending-pelajaran-id">
            <input type="hidden" id="pending-guru-id">
            <input type="hidden" id="pending-beban-jam">

            <div class="flex justify-center space-x-3">
                <button type="button" onclick="batalBentrok()" class="px-5 py-3 bg-gray-200 text-gray-800 rounded-xl font-bold hover:bg-gray-300 transition w-full">Batal</button>
                <button type="button" onclick="eksekusiBentrokForce()" class="px-5 py-3 bg-amber-500 hover:bg-amber-600 text-white rounded-xl font-bold shadow-md transition w-full flex items-center justify-center">
                    <i class="fas fa-trash-alt mr-2"></i> Ya, Hapus & Lanjutkan
                </button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            document.querySelectorAll('.searchable-guru').forEach((el) => {
                let ts = new TomSelect(el, {
                    create: false,
                    placeholder: el.getAttribute('placeholder'),
                    maxOptions: null
                });
                ts.on('change', function() {
                    let pelId = el.id.replace('guru-', '');
                    autoSimpanRow(pelId);
                });
            });
        });

        function autoSimpanRow(pelajaranId, forceUpdate = false) {
            let kelasId = "{{ $kelas_id }}";
            let bebanJam = document.getElementById('target-' + pelajaranId).value;
            let guruId = document.getElementById('guru-' + pelajaranId).value;
            let indicator = document.getElementById('status-autosave');
            let textIndicator = document.getElementById('text-autosave');

            indicator.classList.remove('hidden');
            indicator.classList.add('flex');
            textIndicator.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...';

            fetch('/master-plot-jadwal', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    kelas_id: kelasId,
                    pelajaran_id: pelajaranId,
                    beban_jam: bebanJam,
                    guru_id: guruId,
                    force_update: forceUpdate ? 'true' : 'false'
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    textIndicator.innerText = 'Perubahan tersimpan otomatis';
                    setTimeout(() => { indicator.classList.add('hidden'); }, 2000);

                    // UPDATE DASHBOARD REKAPAN SECARA INSTAN & BERUBAH WARNA
                    let capMax = data.kapasitasMaksimal;
                    let totTarget = data.totalTarget;
                    
                    let cardTotal = document.getElementById('card-total-target');
                    let statTotal = document.getElementById('stat-total-target');
                    let iconTotal = document.getElementById('icon-total-target');
                    
                    // Reset class warna
                    cardTotal.className = 'bg-white border-b-4 rounded-xl shadow-sm p-4 flex flex-col justify-center transition-colors duration-300';
                    
                    if (totTarget > capMax) {
                        cardTotal.classList.add('border-red-500', 'bg-red-50');
                        statTotal.className = 'text-2xl font-black text-red-600';
                        iconTotal.className = 'fas fa-bullseye text-red-200 text-3xl';
                    } else if (totTarget < capMax) {
                        cardTotal.classList.add('border-amber-400', 'bg-amber-50');
                        statTotal.className = 'text-2xl font-black text-amber-600';
                        iconTotal.className = 'fas fa-bullseye text-amber-200 text-3xl';
                    } else {
                        cardTotal.classList.add('border-blue-500');
                        statTotal.className = 'text-2xl font-black text-gray-800';
                        iconTotal.className = 'fas fa-bullseye text-blue-100 text-3xl';
                    }
                    
                    statTotal.innerHTML = totTarget + ' <span class="text-sm font-semibold opacity-70">Jam</span>';

                    document.getElementById('stat-total-terjadwal').innerHTML = data.totalTerjadwal + ' <span class="text-sm font-semibold text-gray-500">Jam</span>';
                    
                    let sisaElem = document.getElementById('stat-sisa-belum');
                    sisaElem.innerHTML = data.sisaBelum + ' <span class="text-sm font-semibold opacity-70">Jam</span>';
                    sisaElem.className = 'text-2xl font-black ' + (data.sisaBelum > 0 ? 'text-amber-600' : 'text-gray-400');
                    // UPDATE BADGE STATUS BARIS
                    let selisih = data.beban_jam - data.terjadwalMapel;
                    let container = document.getElementById('status-container-' + pelajaranId);
                    
                    if (data.beban_jam == 0) {
                        container.innerHTML = '<span class="px-3 py-1 text-[11px] font-bold rounded-full bg-gray-100 text-gray-500 shadow-sm"><i class="fas fa-minus mr-1"></i> Kosong</span>';
                    } else if (selisih == 0) {
                        container.innerHTML = '<span class="px-3 py-1 text-[11px] font-bold rounded-full bg-emerald-100 text-emerald-700 shadow-sm"><i class="fas fa-check mr-1"></i> Tuntas</span>';
                    } else if (selisih > 0) {
                        container.innerHTML = '<span class="px-3 py-1 text-[11px] font-bold rounded-full bg-amber-100 text-amber-700 shadow-sm"><i class="fas fa-exclamation-circle mr-1"></i> Sisa ' + selisih + '</span>';
                    } else {
                        container.innerHTML = '<span class="px-3 py-1 text-[11px] font-bold rounded-full bg-rose-100 text-rose-700 shadow-sm"><i class="fas fa-arrow-up mr-1"></i> Lebih ' + Math.abs(selisih) + '</span>';
                    }
                } 
                else if (data.status === 'error_overload') {
                    indicator.classList.add('hidden');
                    document.getElementById('pesan-overload').innerHTML = data.pesan;
                    document.getElementById('modal-overload').classList.remove('hidden');
                }
                else if (data.status === 'error_bentrok') {
                    indicator.classList.add('hidden');
                    document.getElementById('pesan-bentrok-ajax').innerHTML = data.pesan;
                    
                    let ul = document.getElementById('rincian-bentrok-ajax');
                    ul.innerHTML = '';
                    data.rincian.forEach(r => {
                        let li = document.createElement('li');
                        li.className = 'mb-2';
                        li.innerHTML = r;
                        ul.appendChild(li);
                    });

                    document.getElementById('pending-pelajaran-id').value = data.pelajaran_id;
                    document.getElementById('pending-guru-id').value = data.guru_id;
                    document.getElementById('pending-beban-jam').value = data.beban_jam;

                    document.getElementById('modal-bentrok-ajax').classList.remove('hidden');
                }
            })
            .catch(err => {
                textIndicator.innerText = 'Gagal menyimpan';
            });
        }

        function batalBentrok() {
            document.getElementById('modal-bentrok-ajax').classList.add('hidden');
            location.reload(); // Refresh untuk mengembalikan pilihan ke semula
        }

        function eksekusiBentrokForce() {
            let pelId = document.getElementById('pending-pelajaran-id').value;
            document.getElementById('modal-bentrok-ajax').classList.add('hidden');
            autoSimpanRow(pelId, true);
        }
    </script>
@endsection