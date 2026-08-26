@extends('layouts.app')

@section('title', 'Master Jadwal Harian')

@section('content')
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
    
    <style>
        /* Desain Kustom Elegan ala Apple/Tailwind */
        .ts-wrapper { width: 100%; }
        .ts-control { border-radius: 0.75rem !important; border: 1px solid #e5e7eb !important; padding: 0.7rem 1rem !important; font-size: 0.875rem !important; font-weight: 700 !important; color: #374151 !important; box-shadow: 0 1px 2px 0 rgba(0,0,0,0.05) !important; background-color: #f9fafb !important; }
        .ts-control.focus { border-color: #10b981 !important; background-color: #ffffff !important; box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15) !important; }
        .ts-dropdown { border-radius: 0.75rem !important; border: 1px solid #e5e7eb !important; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1) !important; font-size: 0.875rem !important; font-weight: 600 !important; padding: 0.3rem; margin-top: 4px; }
        .ts-dropdown .option { padding: 0.6rem 1rem !important; border-radius: 0.5rem; margin-bottom: 2px; cursor: pointer; transition: background-color 0.2s;}
        .ts-dropdown .option:hover, .ts-dropdown .option.active { background-color: #ecfdf5 !important; color: #047857 !important; }
        .ts-wrapper.single .ts-control:after { right: 1rem !important; border-color: #9ca3af transparent transparent transparent !important; }
    </style>

    
    <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800"><i class="fas fa-calendar-alt text-green-600 mr-2"></i> Master Jadwal Harian</h1>
            <p class="text-sm text-gray-500 mt-1">Pengaturan Matriks Roster Kelas & Guru</p>
        </div>
    </div>

    <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 mb-6 flex flex-col xl:flex-row gap-5 items-center justify-between z-20 relative">
        
        <div class="flex flex-col md:flex-row w-full xl:w-auto gap-4 items-center">
            
            <form method="GET" action="/master-jadwal-harian" class="w-full md:w-64">
                <select name="kelas_id" class="searchable-select" onchange="this.form.submit()" placeholder="🔍 Pilih Kelas...">
                    <option value="">🔍 Pilih Kelas...</option>
                    @foreach($kelas as $k)
                        <option value="{{ $k->id }}" {{ $kelas_id == $k->id ? 'selected' : '' }}>Kelas {{ $k->nama_kelas }}</option>
                    @endforeach
                </select>
            </form>
            

            <div class="hidden md:block w-8 border-b-2 border-gray-200 border-dashed"></div>

            <form method="GET" action="/master-jadwal-harian" class="w-full md:w-72">
                <select name="guru_id" class="searchable-select" onchange="this.form.submit()" placeholder="🔍 Ketik Nama Guru...">
                    <option value="">🔍 Ketik Nama Guru...</option>
                    @foreach($gurus as $g)
                        <option value="{{ $g->id }}" {{ $guru_id == $g->id ? 'selected' : '' }}>{{ $g->nama_guru }}</option>
                    @endforeach
                </select>
            </form>
            
        </div>
        
        <div class="w-full xl:w-auto flex justify-end">
            <button type="button" class="w-full md:w-auto bg-red-50 text-red-600 border border-red-100 hover:bg-red-500 hover:text-white px-5 py-2.5 rounded-xl text-sm font-bold transition flex items-center justify-center shadow-sm cursor-pointer">
                <i class="fas fa-file-pdf mr-2 text-lg"></i> Cetak PDF
            </button>
        </div>
        
    </div>

    
    @if(!$mode)
        <div class="bg-white rounded-2xl p-12 text-center border border-gray-200 shadow-sm my-6">
            <div class="w-20 h-20 bg-gray-50 text-gray-400 rounded-full flex items-center justify-center mx-auto mb-4 text-3xl">
                <i class="fas fa-calendar-day"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-800">Pilih Mode Tampilan Jadwal</h3>
            <p class="text-sm text-gray-500 max-w-md mx-auto mt-2">Gunakan salah satu dropdown di atas. Anda bisa mengatur jadwal berfokus pada <b>Kelas</b> tertentu, atau berfokus pada <b>Guru</b> tertentu.</p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
            @foreach($hari_list as $hari)
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden flex flex-col">
                
                <div class="bg-gray-50 px-5 py-3.5 border-b border-gray-100 flex justify-between items-center">
                    <h3 class="font-black text-gray-800 text-lg uppercase tracking-wider">{{ $hari }}</h3>
                    <i class="fas fa-calendar-day text-gray-300 text-xl"></i>
                </div>
                
                <div class="flex-1 flex flex-col divide-y divide-gray-100">
                    @foreach($opsiBlokJam as $blok)
                        @php
                            // Ambil angka jam paling awal dari blok ini (Misal: blok 9-10, maka ambil 9)
                            $jamMulaiBlok = min($blok['jam_list']);
                            // Ambil batas maksimal jam dari database untuk hari yang sedang diputar ini
                            $batasJamHariIni = $max_jam_per_hari[$hari] ?? 10;
                        @endphp
                        
                        @if($jamMulaiBlok <= $batasJamHariIni)
                            @php
                                $j = $jadwal_matriks[$hari][$blok['key']] ?? null;
                            @endphp
                            
                            <!-- ROW JADWAL: Ditambahkan class transisi untuk efek drop -->
                            <div class="p-4 flex items-center transition duration-200" id="row-{{ $hari }}-{{ $blok['key'] }}">
                                <div class="w-16 flex-shrink-0 text-center border-r border-gray-200 pr-3">
                                    <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest">Blok</span>
                                    <span class="block text-lg font-black text-gray-800">{{ $blok['key'] }}</span>
                                </div>
                                
                                <!-- AREA DROP ZONE (Tempat meletakkan jadwal) -->
                                <div class="flex-1 min-w-0 flex justify-between items-center relative group py-2 pl-4 rounded-xl transition-all border-2 border-transparent"
                                     ondragover="allowDrop(event)"
                                     ondragenter="dragEnter(event)"
                                     ondragleave="dragLeave(event)"
                                     ondrop="dropData(event, '{{ $hari }}', '{{ $blok['key'] }}', '{{ $j ? $j->id : '' }}')">
                                    
                                    @if($j)
                                        <!-- ITEM DRAGGABLE (Bisa ditarik) -->
                                        <div class="w-full cursor-grab active:cursor-grabbing p-2 -ml-2 rounded-lg hover:bg-gray-50 transition-all border border-transparent hover:border-gray-200" 
                                             draggable="true" 
                                             ondragstart="dragStart(event, '{{ $j->id }}')" 
                                             ondragend="dragEnd(event)">
                                            <h4 class="text-sm font-bold text-gray-800 truncate pointer-events-none">{{ $j->pelajaran->nama_pelajaran ?? '-' }}</h4>
                                            @if($mode == 'kelas')
                                                <p class="text-xs text-gray-500 mt-1 truncate font-medium text-emerald-600 pointer-events-none"><i class="fas fa-user-tie text-emerald-400 mr-1"></i> {{ $j->guru->nama_guru ?? 'Tanpa Guru' }}</p>
                                            @else
                                                <p class="text-xs text-gray-500 mt-1 truncate font-medium text-indigo-600 pointer-events-none"><i class="fas fa-school text-indigo-400 mr-1"></i> Kelas {{ $j->kelas->nama_kelas ?? '-' }}</p>
                                            @endif
                                        </div>
                                        
                                        <div class="absolute right-0 inset-y-0 pl-12 pr-2 bg-gradient-to-l from-white via-white/80 to-transparent flex items-center justify-end space-x-2 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                            <button type="button" onclick="bukaModalEdit('{{ $hari }}', '{{ $blok['key'] }}', '{{ $j->pelajaran_id }}', '{{ $mode == 'kelas' ? $j->guru_id : $j->kelas_id }}')" class="w-9 h-9 rounded-xl bg-orange-50 text-orange-500 hover:bg-orange-500 hover:text-white transition flex items-center justify-center shadow-sm">
                                                <i class="fas fa-pen text-xs"></i>
                                            </button>
                                            <form action="/master-jadwal-harian/{{ $j->id }}" method="POST" class="inline" onsubmit="return confirm('Kosongkan jadwal Blok {{ $blok['key'] }} hari {{ $hari }}?')">
                                                @csrf @method('DELETE')
                                                <input type="hidden" name="kelas_id" value="{{ $kelas_id }}">
                                                <input type="hidden" name="guru_id" value="{{ $guru_id }}">
                                                <button type="submit" class="w-9 h-9 rounded-xl bg-red-50 text-red-500 hover:bg-red-500 hover:text-white transition flex items-center justify-center shadow-sm">
                                                    <i class="fas fa-trash-alt text-xs"></i>
                                                </button>
                                            </form>
                                        </div>
                                    @else
                                        <!-- TAMPILAN KOSONG -->
                                        <div class="w-full flex items-center pointer-events-none">
                                            <p class="text-xs font-bold text-gray-300 italic flex items-center">
                                                <i class="fas fa-minus-circle mr-1.5 opacity-40"></i> Kosong
                                            </p>
                                        </div>
                                        <button onclick="bukaModalTambah('{{ $hari }}', '{{ $blok['key'] }}')" class="absolute right-2 px-4 py-1.5 rounded-xl border-2 border-dashed border-gray-300 text-gray-500 hover:border-gray-500 hover:text-gray-700 hover:bg-gray-100 transition text-xs font-bold flex items-center cursor-pointer">
                                            <i class="fas fa-plus mr-1.5"></i> Isi
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
    @endif

    <div id="modal-jadwal" class="hidden fixed inset-0 bg-gray-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white w-full max-w-md rounded-2xl shadow-2xl overflow-hidden transform transition-all">
            <div class="{{ $mode == 'guru' ? 'bg-emerald-600' : 'bg-indigo-600' }} px-6 py-4 flex justify-between items-center text-white">
                <h3 class="font-bold text-base"><i class="fas fa-clock mr-2"></i> Set Jadwal Blok Jam</h3>
                <button type="button" onclick="tutupModal()" class="text-white/80 hover:text-white"><i class="fas fa-times text-lg"></i></button>
            </div>
            <form action="/master-jadwal-harian" method="POST" class="p-6">
                @csrf
                <input type="hidden" name="hari" id="form-hari">
                <input type="hidden" name="jam_pilihan" id="form-jam-pilihan">

                @if($mode == 'kelas')
                    <input type="hidden" name="kelas_id" value="{{ $kelas_id }}">
                    <div class="mb-4">
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1.5 pl-1">Mata Pelajaran</label>
                        <select name="pelajaran_id" id="form-pelajaran-id" required class="w-full border border-gray-300 rounded-xl p-3 text-sm font-semibold text-gray-800 bg-white">
                            <option value="">-- Pilih Pelajaran --</option>
                            @foreach($pelajarans as $p)
                                <option value="{{ $p->id }}">{{ $p->nama_pelajaran }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-6">
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1.5 pl-1">Guru Pengajar</label>
                        <select name="guru_id" id="form-guru-id" class="w-full border border-gray-300 rounded-xl p-3 text-sm font-semibold text-gray-800 bg-white">
                            <option value="">-- Tanpa Guru / Kosong --</option>
                            @foreach($gurus as $g)
                                <option value="{{ $g->id }}">{{ $g->nama_guru }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                @if($mode == 'guru')
                    <input type="hidden" name="guru_id" value="{{ $guru_id }}">
                    <div class="mb-4">
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1.5 pl-1">Kelas Target</label>
                        <select name="kelas_id" id="form-kelas-id" required class="w-full border border-gray-300 rounded-xl p-3 text-sm font-semibold text-gray-800 bg-white">
                            <option value="">-- Pilih Kelas --</option>
                            @foreach($kelas_popup as $k)
                                <option value="{{ $k->id }}">Kelas {{ $k->nama_kelas }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-6">
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1.5 pl-1">Mata Pelajaran</label>
                        <select name="pelajaran_id" id="form-pelajaran-id" required class="w-full border border-gray-300 rounded-xl p-3 text-sm font-semibold text-gray-800 bg-white">
                            <option value="">-- Pilih Pelajaran --</option>
                            </select>
                    </div>
                @endif

                <div class="flex justify-end space-x-3 border-t pt-5">
                    <button type="button" onclick="tutupModal()" class="px-5 py-2.5 bg-gray-100 text-gray-700 rounded-xl font-bold w-full">Batal</button>
                    <button type="submit" class="px-5 py-2.5 {{ $mode == 'guru' ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-indigo-600 hover:bg-indigo-700' }} text-white rounded-xl font-bold shadow-md transition w-full">Simpan Blok</button>
                </div>
            </form>
        </div>
    </div>

    @if(session('error_popup'))
    <div id="modal-peringatan" class="fixed inset-0 bg-gray-900 bg-opacity-70 overflow-y-auto h-full w-full z-50 flex items-center justify-center backdrop-blur-sm animate-fade-in">
        <div class="relative mx-auto p-6 border w-full max-w-md shadow-2xl rounded-2xl bg-white text-center">
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100 mb-4 shadow-inner">
                <i class="fas fa-exclamation-triangle text-2xl text-red-600 animate-bounce"></i>
            </div>
            <h3 class="text-xl font-extrabold text-gray-800 mb-2">Perhatian Sistem</h3>
            <div class="text-sm text-gray-700 mb-6 bg-red-50 p-4 rounded-xl border border-red-100 text-left leading-relaxed">
                {!! session('error_popup') !!}
            </div>
            <button onclick="document.getElementById('modal-peringatan').style.display='none'" class="w-full py-3 bg-red-600 hover:bg-red-700 text-white font-bold rounded-xl shadow-md transition duration-200">
                <i class="fas fa-check mr-2"></i> Mengerti, Kembali Atur
            </button>
        </div>
    </div>
    @endif

    @if(session('bentrok_popup'))
    <div id="modal-bentrok" class="fixed inset-0 bg-gray-900 bg-opacity-70 overflow-y-auto h-full w-full z-50 flex items-center justify-center backdrop-blur-sm animate-fade-in">
        <div class="relative mx-auto p-6 border w-full max-w-md shadow-2xl rounded-3xl bg-white">
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-amber-100 mb-4 shadow-inner">
                <i class="fas fa-exclamation-triangle text-3xl text-amber-500"></i>
            </div>
            <h3 class="text-xl font-extrabold text-gray-800 mb-2 text-center">Konfirmasi Perubahan</h3>
            <p class="text-sm text-gray-600 mb-4 text-center px-2">{!! session('bentrok_popup')['pesan'] !!}</p>
            
            <div class="text-sm text-gray-700 mb-6 bg-amber-50 p-4 rounded-xl border border-amber-100 text-left leading-relaxed max-h-48 overflow-y-auto shadow-inner">
                <ul class="list-disc pl-5">
                    @foreach(session('bentrok_popup')['rincian'] as $rincian)
                        <li class="mb-3">{!! $rincian !!}</li>
                    @endforeach
                </ul>
            </div>

            <form action="/master-jadwal-harian" method="POST">
                @csrf
                @foreach(session('timpa_popup')['request_data'] as $key => $val)
                    @if($key !== '_token' && $key !== 'force_timpa')
                        <input type="hidden" name="{{ $key }}" value="{{ $val }}">
                    @endif
                @endforeach
                
                <input type="hidden" name="force_timpa" value="true">

                <div class="flex justify-center space-x-3 mt-4">
                    <button type="button" onclick="document.getElementById('modal-timpa').style.display='none'" class="px-5 py-3 bg-gray-200 text-gray-800 rounded-xl font-bold transition w-full hover:bg-gray-300 shadow-sm">Batal</button>
                    <button type="submit" class="px-5 py-3 bg-orange-500 hover:bg-orange-600 text-white rounded-xl font-bold shadow-md transition w-full flex justify-center items-center">
                        <i class="fas fa-check mr-2"></i> Ya, Timpa Jadwal
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    @if(session('tukar_popup'))
    <div id="modal-tukar" class="fixed inset-0 bg-gray-900 bg-opacity-70 overflow-y-auto h-full w-full z-50 flex items-center justify-center backdrop-blur-sm animate-fade-in">
        <div class="relative mx-auto p-6 border w-full max-w-md shadow-2xl rounded-3xl bg-white">
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-amber-100 mb-4 shadow-inner">
                <i class="fas fa-exchange-alt text-2xl text-amber-600"></i>
            </div>
            <h3 class="text-xl font-extrabold text-gray-800 mb-2 text-center">Beban Jam Sudah Penuh!</h3>
            <p class="text-sm text-gray-600 mb-5 text-center px-2">{!! session('tukar_popup')['pesan'] !!}</p>
            
            <form action="/master-jadwal-harian" method="POST">
                @csrf
                @foreach(session('tukar_popup')['request_data'] as $key => $val)
                    @if($key !== '_token' && $key !== 'force_swap' && $key !== 'swap_target')
                        <input type="hidden" name="{{ $key }}" value="{{ $val }}">
                    @endif
                @endforeach
                
                
                <input type="hidden" name="force_swap" value="true">

                <label class="block text-sm font-bold text-gray-700 mb-3 bg-gray-50 p-2 rounded border border-gray-100">Apakah Anda ingin MENGHAPUS salah satu jadwal lama dan MEMINDAHKANNYA ke sini?</label>
                
                <div class="space-y-2 mb-6 max-h-48 overflow-y-auto pr-2">
                    @foreach(session('tukar_popup')['opsi_tukar'] as $idx => $opsi)
                    <label class="flex items-center p-3.5 border-2 border-gray-100 rounded-xl cursor-pointer hover:bg-amber-50 hover:border-amber-300 transition group">
                        <input type="radio" name="swap_target" value="{{ $opsi['value'] }}" required class="text-amber-600 focus:ring-amber-500 w-5 h-5">
                        <div class="ml-3">
                            <span class="block text-sm font-bold text-gray-800 group-hover:text-amber-800">Hari {{ $opsi['hari'] }}</span>
                            <span class="block text-xs font-semibold text-gray-500 group-hover:text-amber-600">Jam Ke: {{ $opsi['jam_tampil'] }}</span>
                        </div>
                    </label>
                    @endforeach
                </div>

                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="document.getElementById('modal-tukar').style.display='none'" class="px-5 py-2.5 bg-gray-100 text-gray-800 rounded-xl font-bold transition w-full hover:bg-gray-200">Batal Saja</button>
                    <button type="submit" class="px-5 py-2.5 bg-amber-500 hover:bg-amber-600 text-white rounded-xl font-bold shadow-md transition w-full flex justify-center items-center">
                        <i class="fas fa-random mr-2"></i> Ya, Pindahkan!
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    @if(session('timpa_popup'))
    <div id="modal-timpa" class="fixed inset-0 bg-gray-900 bg-opacity-70 overflow-y-auto h-full w-full z-50 flex items-center justify-center backdrop-blur-sm animate-fade-in">
        <div class="relative mx-auto p-6 border w-full max-w-md shadow-2xl rounded-3xl bg-white">
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-orange-100 mb-4 shadow-inner">
                <i class="fas fa-copy text-3xl text-orange-500 animate-pulse"></i>
            </div>
            <h3 class="text-xl font-extrabold text-gray-800 mb-2 text-center">Slot Kelas Terisi!</h3>
            <p class="text-sm text-gray-600 mb-5 text-center px-2">{!! session('timpa_popup')['pesan'] !!}</p>
            
            <form action="/master-jadwal-harian" method="POST">
                @csrf
                @foreach(session('timpa_popup')['request_data'] as $key => $val)
                    @if($key !== '_token' && $key !== 'force_timpa')
                        <input type="hidden" name="{{ $key }}" value="{{ $val }}">
                    @endif
                @endforeach
                
                
                <input type="hidden" name="force_timpa" value="true">

                <div class="flex justify-center space-x-3 mt-4">
                    <button type="button" onclick="document.getElementById('modal-timpa').style.display='none'" class="px-5 py-3 bg-gray-200 text-gray-800 rounded-xl font-bold transition w-full hover:bg-gray-300 shadow-sm">Batal</button>
                    <button type="submit" class="px-5 py-3 bg-orange-500 hover:bg-orange-600 text-white rounded-xl font-bold shadow-md transition w-full flex justify-center items-center">
                        <i class="fas fa-check mr-2"></i> Ya, Timpa Jadwal
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <script>

        // FITUR BARU: MENGAKTIFKAN KOTAK PENCARIAN KETIK (SEARCHABLE SELECT)
        document.addEventListener("DOMContentLoaded", function() {
            // Aktifkan untuk Dropdown di Toolbar Atas
            document.querySelectorAll('.searchable-select').forEach((el) => {
                new TomSelect(el, {
                    create: false,
                    placeholder: el.getAttribute('placeholder'),
                    maxOptions: null // Menampilkan semua data hasil pencarian
                });
            });
        });

        // Auto-select Guru berdasarkan Kelas / Kelas berdasarkan Guru
        @if($mode == 'kelas')
            const plotData = @json($plotAktif ?? []);
            const pelSelect = document.getElementById('form-pelajaran-id');
            if(pelSelect) {
                pelSelect.addEventListener('change', function() {
                    let pelId = this.value;
                    let guruSelect = document.getElementById('form-guru-id');
                    for(let i=0; i<guruSelect.options.length; i++) guruSelect.options[i].style.display = '';

                    if (pelId && plotData[pelId] && plotData[pelId].guru_id) {
                        guruSelect.value = plotData[pelId].guru_id;
                        for(let i=0; i<guruSelect.options.length; i++) {
                            if(guruSelect.options[i].value != "" && guruSelect.options[i].value != plotData[pelId].guru_id) {
                                guruSelect.options[i].style.display = 'none';
                            }
                        }
                    } else {
                        guruSelect.value = '';
                    }
                });
            }
        @elseif($mode == 'guru')
            const plotDataGuru = @json($plotAktif ?? []);
            const klsSelect = document.getElementById('form-kelas-id');
            const pelSelect = document.getElementById('form-pelajaran-id');
            
            if(klsSelect) {
                klsSelect.addEventListener('change', function() {
                    let klsId = this.value;
                    pelSelect.innerHTML = '<option value="">-- Pilih Pelajaran --</option>'; // Reset pelajaran
                    
                    if (klsId && plotDataGuru[klsId]) {
                        // Tambahkan HANYA pelajaran yang diajar guru ini di kelas tersebut
                        plotDataGuru[klsId].forEach(function(pel) {
                            let option = document.createElement('option');
                            option.value = pel.id;
                            option.text = pel.nama_pelajaran;
                            pelSelect.add(option);
                        });
                    }
                });
            }
        @endif

        function bukaModalTambah(hari, jamPilihan) {
            document.getElementById('form-hari').value = hari;
            document.getElementById('form-jam-pilihan').value = jamPilihan;
            
            @if($mode == 'kelas')
                document.getElementById('form-pelajaran-id').value = '';
                document.getElementById('form-pelajaran-id').dispatchEvent(new Event('change'));
            @else
                document.getElementById('form-kelas-id').value = '';
                document.getElementById('form-kelas-id').dispatchEvent(new Event('change'));
            @endif
            
            document.getElementById('modal-jadwal').classList.remove('hidden');
        }

        function bukaModalEdit(hari, jamPilihan, pelajaranId, varB) {
            document.getElementById('form-hari').value = hari;
            document.getElementById('form-jam-pilihan').value = jamPilihan;
            
            @if($mode == 'kelas')
                let pelSelect = document.getElementById('form-pelajaran-id');
                pelSelect.value = pelajaranId;
                pelSelect.dispatchEvent(new Event('change'));
                setTimeout(() => { document.getElementById('form-guru-id').value = varB; }, 50);
            @else
                let klsSelect = document.getElementById('form-kelas-id');
                klsSelect.value = varB;
                klsSelect.dispatchEvent(new Event('change')); // Memicu update dropdown pelajaran
                setTimeout(() => { document.getElementById('form-pelajaran-id').value = pelajaranId; }, 50);
            @endif
            
            document.getElementById('modal-jadwal').classList.remove('hidden');
        }
        function tutupModal() {
            document.getElementById('modal-jadwal').classList.add('hidden');
        }

        // --- MESIN DRAG AND DROP JADWAL ---
        let draggedJadwalId = null;

        // Saat item mulai ditarik
        function dragStart(ev, id) {
            draggedJadwalId = id;
            ev.dataTransfer.effectAllowed = "move";
            // Efek visual item yang ditarik menjadi transparan
            setTimeout(() => { ev.target.classList.add('opacity-40'); }, 0);
        }

        // Saat item dilepas (Selesai tarik)
        function dragEnd(ev) {
            ev.target.classList.remove('opacity-40');
            draggedJadwalId = null;
        }

        // Mengizinkan area untuk dijatuhkan item
        function allowDrop(ev) {
            ev.preventDefault();
        }

        // Efek visual saat item melayang di atas area kosong/target
        function dragEnter(ev) {
            ev.preventDefault();
            ev.currentTarget.classList.add('bg-indigo-50', 'border-indigo-300', 'border-dashed');
        }

        // Menghilangkan efek visual saat item keluar dari area
        function dragLeave(ev) {
            ev.currentTarget.classList.remove('bg-indigo-50', 'border-indigo-300', 'border-dashed');
        }

        // Proses saat item dilepaskan (Drop)
        function dropData(ev, targetHari, targetJam, targetId) {
            ev.preventDefault();
            ev.currentTarget.classList.remove('bg-indigo-50', 'border-indigo-300', 'border-dashed');

            // Abaikan jika tidak ada item yang ditarik, atau dijatuhkan di tempatnya sendiri
            if (!draggedJadwalId || draggedJadwalId === targetId) return;

            // Tampilkan UI Loading Global Anda (Opsional, agar terlihat memproses)
            document.body.style.cursor = 'wait';

            let token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            // Ambil parameter mode yang sedang aktif dari URL (Kelas / Guru)
            let searchParams = new URLSearchParams(window.location.search);
            let kelas_id = searchParams.get('kelas_id');
            let guru_id = searchParams.get('guru_id');

            fetch('/master-jadwal-harian/drag-drop', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token
                },
                body: JSON.stringify({
                    source_id: draggedJadwalId,
                    target_hari: targetHari,
                    target_jam: targetJam,
                    target_id: targetId,
                    kelas_id: kelas_id,
                    guru_id: guru_id
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    // Berhasil! Reload halaman agar matriks merender ulang posisi terbaru
                    window.location.reload();
                } else {
                    alert(data.pesan); // Tampilkan pesan error bentrok
                    document.body.style.cursor = 'default';
                }
            })
            .catch(error => {
                alert('Terjadi kesalahan jaringan.');
                document.body.style.cursor = 'default';
            });
        }
    </script>
@endsection