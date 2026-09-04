@extends('layouts.app')

@section('title', 'Meja Kontrol - SmartPesantren')

@section('content')
    <!-- ==========================================
         HEADER & MENU ELEGAN (REDESAIN)
         ========================================== -->
    <div class="bg-white p-5 md:p-6 rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100 mb-8 flex flex-col xl:flex-row justify-between items-start xl:items-center gap-6 relative overflow-hidden">
        
        <!-- Aksen Garis Dekoratif -->
        <div class="absolute top-0 left-0 w-1.5 h-full bg-indigo-500 rounded-l-3xl"></div>

        <div>
            <h2 class="text-2xl font-black text-slate-800 tracking-tight flex items-center">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600 mr-3">
                    <i class="fas fa-tv"></i>
                </div>
                Meja Kontrol Kehadiran
            </h2>
        </div>
        
        <div class="flex flex-wrap items-center gap-3 w-full xl:w-auto ml-0 xl:ml-auto">
            <form method="GET" action="/meja-kontrol" class="flex flex-wrap items-center gap-3 w-full sm:w-auto">
                    
                <div class="bg-indigo-600 text-white px-4 py-2.5 rounded-xl text-sm font-bold shadow-sm flex items-center">
                    <i class="fas fa-calendar-day mr-2 opacity-80"></i> {{ $hariIni }}
                </div>

                <!-- Pemilih Tanggal -->
                <div class="flex items-center bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 hover:border-emerald-400 hover:bg-white transition-all duration-300">
                    <label class="text-slate-400 mr-2"><i class="fas fa-calendar-alt text-emerald-600"></i></label>
                    
                    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
                    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

                    <input type="text" id="kalender-dinamis" name="tanggal" value="{{ $tanggalPilihan }}" class="text-sm bg-transparent outline-none font-bold text-emerald-700 cursor-pointer w-24 bg-white" readonly>
                </div>

                <!-- Pemilih Jam -->
                <div class="flex items-center bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 hover:border-indigo-400 hover:bg-white transition-all duration-300 focus-within:border-indigo-500 focus-within:bg-white focus-within:ring-4 focus-within:ring-indigo-500/10">
                    <label for="jam" class="text-slate-400 mr-2"><i class="fas fa-clock text-indigo-600"></i></label>
                    <select name="jam" onchange="this.form.submit()" class="text-sm bg-transparent outline-none font-bold text-indigo-700 cursor-pointer pr-2">
                        @foreach($opsiBlokJam as $opsi)
                            <option value="{{ $opsi['nilai'] }}" {{ $jamPilihan == $opsi['nilai'] ? 'selected' : '' }}>
                                {{ $opsi['label'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </form>

            <!-- Teks WhatsApp Broadcast -->
            @php
                $tglFormat = \Carbon\Carbon::parse($tanggalPilihan)->translatedFormat('d F Y');
                $waText = "ASSALAMUALAIKUM WR. WB.\nSekedar MENGINGATKAN bahwa Jadwal Mengajar hari ini, {$hariIni}, {$tglFormat}.\n\n";

                $jadwalAktif = collect($jadwals)->filter(function($j) { return !isset($j['is_libur']) || !$j['is_libur']; });
                $groupedByJam = $jadwalAktif->groupBy('jam_tampil');
                
                foreach($groupedByJam as $jamTampil => $items) {
                    $waText .= "JAM " . $jamTampil . "\n";
                    foreach($items as $item) {
                        $waText .= "Kelas " . $item['kelas'] . " " . $item['mata_pelajaran'] . " (" . $item['nama_guru'] . ")\n";
                    }
                    $waText .= "\n";
                }
                $waText .= "Terima kasih.\nWASSALAMUALAIKUM WR. WB.";
            @endphp

            <textarea id="teks-wa-hidden" class="hidden">{{ $waText }}</textarea>
            <button type="button" onclick="salinTeksWA()" class="bg-emerald-50 text-emerald-600 border border-emerald-200 hover:bg-emerald-600 hover:text-white px-5 py-2.5 rounded-xl text-sm font-bold shadow-sm transition-all duration-300 flex items-center cursor-pointer flex-shrink-0 active:scale-95">
                <i class="fab fa-whatsapp mr-2 text-lg"></i> <span id="teks-btn-wa">Copy WA</span>
            </button>
        </div>
    </div>

    <!-- ==========================================
         BAGIAN PENGUMUMAN HARI LIBUR
         ========================================== -->
    @if(isset($daftarLibur) && $daftarLibur->count() > 0)
        <div class="bg-rose-50 border border-rose-200 p-4 rounded-2xl mb-6 shadow-sm">
            @foreach($daftarLibur as $libur)
            <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-2 {{ !$loop->last ? 'mb-3 border-b border-rose-100 pb-3' : '' }}">
                <div class="flex items-center space-x-3">
                    <div class="p-2.5 bg-rose-100 text-rose-600 rounded-xl flex-shrink-0">
                        <i class="fas fa-calendar-day text-xl"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-rose-800">🎉 HARI LIBUR: {{ $libur->nama_agenda }}</h4>
                        <p class="text-xs text-rose-600 mt-0.5 font-medium leading-relaxed">
                            Libur untuk 
                            @if($libur->target_libur == 'semua')
                                <b>Seluruh Kelas</b>.
                            @else
                                @php
                                    $kelasArr = is_string($libur->kelas_ids) ? json_decode($libur->kelas_ids, true) : (is_array($libur->kelas_ids) ? $libur->kelas_ids : []);
                                    $namaKelasStr = (!empty($kelasArr) && is_array($kelasArr) && class_exists('\App\Models\Kelas')) 
                                        ? \App\Models\Kelas::whereIn('id', $kelasArr)->pluck('nama_kelas')->implode(', ') : 'Tertentu';
                                @endphp
                                <b>Kelas {{ $namaKelasStr }}</b>.
                            @endif
                            
                            @if($libur->tipe_agenda == 'Penuh')
                                <b>Semua Jam</b>.
                            @else
                                @php
                                    $jamArr = is_string($libur->jam_diliburkan) ? json_decode($libur->jam_diliburkan, true) : (is_array($libur->jam_diliburkan) ? $libur->jam_diliburkan : []);
                                    if(is_array($jamArr) && count($jamArr) > 0) {
                                        $jamArr = array_map('intval', $jamArr);
                                        $jamTeks = count($jamArr) > 1 ? min($jamArr) . '-' . max($jamArr) : implode(', ', $jamArr);
                                    } else {
                                        $jamTeks = '-';
                                    }
                                @endphp
                                <b>Jam {{ $jamTeks }}</b>.
                            @endif
                            {{ $libur->keterangan ? ' ' . $libur->keterangan : '' }}
                        </p>
                    </div>
                </div>
                <span class="px-3 py-1 bg-rose-600 text-white rounded-xl text-xs font-bold uppercase shadow-sm hidden md:block">LIBUR KBM</span>
            </div>
            @endforeach
        </div>
    @endif

    <!-- ==========================================
         GRID KOTAK JADWAL (LAYAR CATUR)
         ========================================== -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 pb-20">
        @php $adaJadwalTampil = false; @endphp

        @foreach($jadwals as $jadwal)
        
            @if(isset($jadwal['is_libur']) && $jadwal['is_libur'])
                @continue
            @endif

            @php
                $adaJadwalTampil = true;
                $idUtama = $jadwal['id_list'][0];
                $rekam = $kehadiranHariIni[$idUtama] ?? null;
                $statusSaatIni = $rekam ? $rekam->status : 'Menunggu';
                $keteranganSaatIni = $rekam ? $rekam->keterangan : '';
                $nigPenggantiSaatIni = $rekam ? $rekam->nig_pengganti : '';
                
                $namaPenggantiSaatIni = null;
                if($nigPenggantiSaatIni) {
                    $guruInval = collect($daftarGuru)->firstWhere('nig', $nigPenggantiSaatIni);
                    $namaPenggantiSaatIni = $guruInval ? $guruInval->nama_guru : $nigPenggantiSaatIni;
                }
                
                // LOGIKA WARNA STATUS
                $warnaStatus = 'bg-slate-50 text-slate-600 border-slate-200';
                $dotColor = 'bg-slate-400';
                $borderGlow = 'border-slate-200 hover:border-slate-300';
                
                if($statusSaatIni == 'Hadir') { 
                    $warnaStatus = 'bg-emerald-50 text-emerald-700 border-emerald-200'; 
                    $dotColor = 'bg-emerald-500';
                    $borderGlow = 'border-emerald-200 shadow-[0_4px_20px_-4px_rgba(16,185,129,0.15)]';
                }
                if($statusSaatIni == 'Izin') { 
                    $warnaStatus = 'bg-blue-50 text-blue-700 border-blue-200'; 
                    $dotColor = 'bg-blue-500';
                    $borderGlow = 'border-blue-200 shadow-[0_4px_20px_-4px_rgba(59,130,246,0.15)]';
                }
                if($statusSaatIni == 'Sakit') { 
                    $warnaStatus = 'bg-amber-50 text-amber-700 border-amber-200'; 
                    $dotColor = 'bg-amber-500';
                    $borderGlow = 'border-amber-200 shadow-[0_4px_20px_-4px_rgba(245,158,11,0.15)]';
                }
                if($statusSaatIni == 'Kosong') { 
                    $warnaStatus = 'bg-rose-50 text-rose-700 border-rose-200'; 
                    $dotColor = 'bg-rose-500';
                    $borderGlow = 'border-rose-200 shadow-[0_4px_20px_-4px_rgba(225,29,72,0.15)]';
                }
            @endphp

            <div class="bg-white rounded-3xl p-5 shadow-sm border {{ $borderGlow }} hover:-translate-y-1 transition-all duration-300 relative flex flex-col h-full" id="card-{{ $idUtama }}">
                
                <!-- HEADER: KELAS & JAM -->
                <div class="flex justify-between items-center mb-4">
                    <span class="bg-slate-800 text-white text-[10px] font-black uppercase tracking-widest px-3 py-1.5 rounded-lg shadow-sm">KLS {{ $jadwal['kelas'] }}</span>
                    <span class="text-[10px] font-black text-indigo-700 bg-indigo-50 border border-indigo-100 px-3 py-1.5 rounded-lg tracking-wider">JAM {{ $jadwal['jam_tampil'] }}</span>
                </div>

                <!-- BODY: MATA PELAJARAN, KITAB & GURU -->
                <div class="mb-4 flex-grow">
                    <h3 class="text-xl font-black text-slate-800 tracking-tight leading-tight mb-1">{{ $jadwal['mata_pelajaran'] }}</h3>
                    <p class="text-sm font-bold text-emerald-600 mb-4"><i class="fas fa-book-open text-[10px] mr-1.5 opacity-70"></i> {{ $jadwal['nama_kitab'] ?? 'Tanpa Kitab' }}</p>
                    
                    <div class="flex items-center pt-3 border-t border-slate-100">
                        <div class="w-8 h-8 rounded-full bg-slate-50 flex items-center justify-center text-slate-400 mr-3 border border-slate-200 shrink-0">
                            <i class="fas fa-user-tie text-xs"></i>
                        </div>
                        <div>
                            <p class="text-sm text-slate-700 font-extrabold leading-none mb-1">{{ $jadwal['nama_guru'] }}</p>
                            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest leading-none">NIG: {{ $jadwal['nig_guru'] }}</p>
                        </div>
                    </div>
                </div>

                <!-- INFO EXTRA (KETERANGAN & INVAL) -->
                <div id="info-extra-{{ $idUtama }}" data-ket="{{ $keteranganSaatIni }}" data-inv="{{ $nigPenggantiSaatIni }}" class="mb-4 {{ (!$keteranganSaatIni && !$namaPenggantiSaatIni) ? 'hidden' : '' }}">
                    <div class="bg-slate-50 p-3 rounded-xl border border-slate-200/60">
                        @if($keteranganSaatIni)
                            <p class="text-xs text-slate-600 font-semibold mb-1 leading-snug"><i class="fas fa-quote-left text-slate-300 text-[10px] mr-1.5"></i>{{ $keteranganSaatIni }}</p>
                        @endif
                        @if($namaPenggantiSaatIni)
                            <div class="inline-flex items-center text-[10px] text-indigo-700 font-bold bg-indigo-100/50 px-2 py-1.5 rounded-lg border border-indigo-200/50 mt-1.5">
                                <i class="fas fa-user-shield mr-1.5 text-indigo-500"></i> Inval: {{ $namaPenggantiSaatIni }}
                            </div>
                        @endif
                    </div>
                </div>

                <!-- FOOTER: LABEL STATUS & CONTROL DOCK -->
                <div class="mt-auto flex justify-between items-center bg-slate-50/50 -mx-5 -mb-5 p-4 border-t border-slate-100 rounded-b-3xl">
                    <span id="label-{{ $idUtama }}" class="text-[10px] uppercase tracking-widest font-black px-3 py-1.5 rounded-xl border flex items-center transition-all duration-500 {{ $warnaStatus }}">
                        <span id="dot-{{ $idUtama }}" class="w-1.5 h-1.5 rounded-full mr-1.5 animate-pulse {{ $dotColor }}"></span> 
                        <span id="teks-{{ $idUtama }}">{{ $statusSaatIni }}</span>
                    </span>

                    <div class="bg-white p-1 rounded-xl border border-slate-200 flex space-x-1 shadow-sm shrink-0">
                        <button onclick='simpanLangsung(@json($jadwal["id_list"]), "Hadir", {{ $idUtama }})' class="w-9 h-9 flex items-center justify-center text-emerald-600 bg-slate-50 rounded-lg hover:bg-emerald-500 hover:text-white transition-all duration-300 active:scale-90" title="Tandai Hadir">
                            <i class="fas fa-check"></i>
                        </button>
                        <button onclick='bukaPopup(@json($jadwal["id_list"]), "Izin", {{ $idUtama }})' class="w-9 h-9 flex items-center justify-center text-blue-600 bg-slate-50 rounded-lg hover:bg-blue-500 hover:text-white transition-all duration-300 active:scale-90" title="Tandai Izin">
                            <i class="fas fa-info text-sm"></i>
                        </button>
                        <button onclick='bukaPopup(@json($jadwal["id_list"]), "Sakit", {{ $idUtama }})' class="w-9 h-9 flex items-center justify-center text-amber-600 bg-slate-50 rounded-lg hover:bg-amber-500 hover:text-white transition-all duration-300 active:scale-90" title="Tandai Sakit">
                            <i class="fas fa-procedures text-xs"></i>
                        </button>
                        <button onclick='bukaPopup(@json($jadwal["id_list"]), "Kosong", {{ $idUtama }})' class="w-9 h-9 flex items-center justify-center text-rose-600 bg-slate-50 rounded-lg hover:bg-rose-500 hover:text-white transition-all duration-300 active:scale-90" title="Tandai Kosong (Alpa)">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>
        @endforeach

        <!-- KONDISI KOSONG -->
        @if(count($jadwals) > 0 && !$adaJadwalTampil)
        <div class="col-span-full flex flex-col items-center justify-center py-20 bg-white rounded-3xl shadow-sm border border-slate-200 border-dashed">
            <div class="w-20 h-20 bg-rose-50 text-rose-300 rounded-full flex items-center justify-center text-4xl mb-4"><i class="fas fa-bed"></i></div>
            <p class="text-slate-600 font-bold text-lg">Semua kelas pada blok jam ini sedang diliburkan.</p>
            <p class="text-sm text-slate-400 mt-1 font-medium">Staf TU bisa beristirahat sejenak.</p>
        </div>
        @elseif(count($jadwals) == 0)
        <div class="col-span-full flex flex-col items-center justify-center py-20 bg-white rounded-3xl shadow-sm border border-slate-200 border-dashed">
            <div class="w-20 h-20 bg-slate-50 text-slate-300 rounded-full flex items-center justify-center text-4xl mb-4"><i class="fas fa-mug-hot"></i></div>
            <p class="text-slate-600 font-bold text-lg">Tidak ada jadwal kelas untuk blok jam ini.</p>
            <p class="text-sm text-slate-400 mt-1 font-medium">Staf TU bisa beristirahat sejenak.</p>
        </div>
        @endif
    </div>

    <!-- ==========================================
         MODAL POPUP ALASAN / KETERANGAN
         ========================================== -->
    <div id="popup-modal" class="hidden fixed inset-0 bg-slate-900/60 overflow-y-auto h-full w-full z-50 flex items-center justify-center backdrop-blur-sm transition-opacity">
        <div class="relative mx-auto p-7 border border-slate-100 w-full max-w-md shadow-2xl rounded-3xl bg-white transform transition-all">
            <div class="absolute top-0 right-0 pt-5 pr-5">
                <button onclick="tutupPopup()" class="w-8 h-8 flex items-center justify-center bg-slate-50 text-slate-400 rounded-full hover:bg-rose-50 hover:text-rose-500 transition-colors">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="mt-2 text-center">
                <div id="box-ikon-modal" class="mx-auto flex items-center justify-center h-16 w-16 rounded-2xl bg-blue-50 mb-5 shadow-inner">
                    <i id="ikon-modal" class="fas fa-clipboard-list text-2xl text-blue-600"></i>
                </div>
                <h3 class="text-2xl font-black text-slate-800" id="popup-judul">Konfirmasi Status</h3>
                <p class="text-sm text-slate-500 mt-1.5 font-medium">Silakan isi rincian keterangan di bawah ini.</p>
                
                <div class="mt-8 text-left">
                    <input type="hidden" id="modal-jadwal-ids">
                    <input type="hidden" id="modal-id-utama">
                    <input type="hidden" id="modal-status">
                    
                    <label class="block text-sm font-extrabold text-slate-700 mb-2"><i class="fas fa-pen-alt text-slate-400 w-5"></i> Alasan / Keterangan <span class="text-rose-500">* (Wajib)</span></label>
                    <textarea id="modal-keterangan" rows="3" required class="w-full border border-slate-200 bg-slate-50 rounded-xl p-3.5 mb-5 text-sm font-medium text-slate-700 focus:bg-white focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all placeholder-slate-400" placeholder="Contoh: Sakit tipes, dinas luar, ban bocor..."></textarea>
                    
                    <label class="block text-sm font-extrabold text-slate-700 mb-2"><i class="fas fa-user-plus text-slate-400 w-5"></i> Guru Pengganti / Inval <span class="text-slate-400 font-medium">(Opsional)</span></label>
                    <div class="relative">
                        <select id="modal-nig-pengganti" class="w-full border border-slate-200 bg-slate-50 rounded-xl p-3.5 text-sm font-medium text-slate-700 appearance-none focus:bg-white focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all cursor-pointer">
                            <option value="">-- Kosongkan jika tidak ada --</option>
                            @foreach($daftarGuru as $guru)
                                <option value="{{ $guru->nig }}">{{ $guru->nama_guru }}</option>
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-slate-400">
                            <i class="fas fa-chevron-down text-xs"></i>
                        </div>
                    </div>
                </div>
                <div class="mt-8 flex flex-col-reverse sm:flex-row sm:justify-end sm:gap-3 gap-2">
                    <button type="button" onclick="tutupPopup()" class="px-5 py-3.5 bg-white border border-slate-200 text-slate-600 text-sm font-bold rounded-xl hover:bg-slate-50 hover:text-slate-800 transition-colors w-full sm:w-auto">Batal</button>
                    
                    <button id="btn-simpan-modal" type="button" onclick="kirimDataModal()" class="px-5 py-3.5 bg-blue-600 text-white text-sm font-bold rounded-xl hover:bg-blue-700 active:scale-95 transition-all w-full sm:w-auto shadow-md shadow-blue-500/20">Simpan Data</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        let token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // ==========================================
        // FUNGSI AKSI UTAMA (SIMPAN DAN MODAL)
        // ==========================================
        function simpanLangsung(jadwalIdsArray, statusBaru, idUtama) {
            let btn = event.currentTarget;
            let teksAsli = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            btn.disabled = true;
            btn.classList.add('opacity-75', 'cursor-not-allowed');

            kirimKeServer(jadwalIdsArray, statusBaru, null, null, idUtama, btn, teksAsli, false);
        }

        function bukaPopup(jadwalIdsArray, status, idUtama) {
            let modal = document.getElementById('popup-modal');
            modal.classList.remove('hidden');
            
            document.getElementById('modal-jadwal-ids').value = JSON.stringify(jadwalIdsArray);
            document.getElementById('modal-id-utama').value = idUtama;
            document.getElementById('modal-status').value = status;
            
            // LOGIKA CERDAS: MENGAMBIL DATA PIKET/INVAL DARI ATRIBUT LAYAR CATUR
            let infoDiv = document.getElementById('info-extra-' + idUtama);
            let ketLama = infoDiv ? (infoDiv.getAttribute('data-ket') || '') : '';
            let invLama = infoDiv ? (infoDiv.getAttribute('data-inv') || '') : '';

            // SUNTIKKAN KE DALAM FORM MODAL
            let ketInput = document.getElementById('modal-keterangan');
            ketInput.value = ketLama;
            ketInput.classList.remove('border-rose-500', 'ring-rose-500/20', 'bg-rose-50'); // Hapus sisa error
            
            document.getElementById('modal-nig-pengganti').value = invLama;
            
            // DESAIN DINAMIS MODAL BERDASARKAN STATUS
            let boxIkon = document.getElementById('box-ikon-modal');
            let ikonModal = document.getElementById('ikon-modal');
            let btnSimpan = document.getElementById('btn-simpan-modal');
            
            boxIkon.className = 'mx-auto flex items-center justify-center h-16 w-16 rounded-2xl mb-5 shadow-inner';
            btnSimpan.className = 'px-5 py-3.5 text-white text-sm font-bold rounded-xl active:scale-95 transition-all w-full shadow-md';
            
            if(status === 'Izin') {
                boxIkon.classList.add('bg-blue-50');
                ikonModal.className = 'fas fa-info-circle text-3xl text-blue-600';
                btnSimpan.classList.add('bg-blue-600', 'hover:bg-blue-700', 'shadow-blue-500/20');
            } else if(status === 'Sakit') {
                boxIkon.classList.add('bg-amber-50');
                ikonModal.className = 'fas fa-procedures text-3xl text-amber-600';
                btnSimpan.classList.add('bg-amber-500', 'hover:bg-amber-600', 'shadow-amber-500/20');
            } else {
                boxIkon.classList.add('bg-rose-50');
                ikonModal.className = 'fas fa-times-circle text-3xl text-rose-600';
                btnSimpan.classList.add('bg-rose-600', 'hover:bg-rose-700', 'shadow-rose-500/20');
            }

            document.getElementById('popup-judul').innerText = "Konfirmasi " + status;
        }

        function tutupPopup() {
            document.getElementById('popup-modal').classList.add('hidden');
        }

        function kirimDataModal() {
            let status = document.getElementById('modal-status').value;
            let ketInput = document.getElementById('modal-keterangan');
            let keterangan = ketInput.value.trim();
            
            // VALIDASI KETERANGAN WAJIB
            if(keterangan === '') {
                ketInput.classList.add('border-rose-500', 'ring-rose-500/20', 'bg-rose-50');
                ketInput.focus();
                alert("Mohon maaf, alasan/keterangan WAJIB diisi untuk status " + status + ".");
                return;
            }

            let btn = document.getElementById('btn-simpan-modal');
            let teksAsli = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-circle-notch fa-spin mr-2"></i> Memproses...';
            btn.disabled = true;
            btn.classList.add('opacity-90', 'cursor-wait');

            let jadwalIdsArray = JSON.parse(document.getElementById('modal-jadwal-ids').value);
            let idUtama = document.getElementById('modal-id-utama').value;
            let nigPengganti = document.getElementById('modal-nig-pengganti').value;

            kirimKeServer(jadwalIdsArray, status, keterangan, nigPengganti, idUtama, btn, teksAsli, true);
        }

        // ==========================================
        // MESIN FETCH API (KOMUNIKASI SERVER)
        // ==========================================
        function kirimKeServer(jadwalIdsArray, statusBaru, keterangan, nigPengganti, idUtama, btnLoading, teksAsli, isModal) {
            let urlParams = new URLSearchParams(window.location.search);
            let tanggalUrl = urlParams.get('tanggal');
            let inputTanggal = document.getElementById('kalender-dinamis');
            let tanggalLayar = inputTanggal ? inputTanggal.value : (tanggalUrl || '{{ \Carbon\Carbon::now()->format("Y-m-d") }}');

            fetch('/simpan-kehadiran', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token
                },
                body: JSON.stringify({
                    jadwal_ids: jadwalIdsArray,
                    status: statusBaru,
                    keterangan: keterangan,
                    nig_pengganti: nigPengganti,
                    tanggal: tanggalLayar
                })
            })
            .then(async response => {
                if(!response.ok) throw new Error("Terjadi kesalahan jaringan.");

                // AMBIL ELEMEN BARU
                let label = document.getElementById('label-' + idUtama);
                let teks = document.getElementById('teks-' + idUtama);
                let dot = document.getElementById('dot-' + idUtama);
                let card = document.getElementById('card-' + idUtama);
                let infoDiv = document.getElementById('info-extra-' + idUtama);
                
                teks.innerText = statusBaru;
                
                // RESET KELAS ELEMEN
                label.className = "text-[10px] uppercase tracking-widest font-black px-3 py-1.5 rounded-xl border flex items-center transition-all duration-500";
                dot.className = "w-1.5 h-1.5 rounded-full mr-1.5 animate-pulse";
                card.className = "bg-white rounded-3xl p-5 shadow-sm border hover:-translate-y-1 transition-all duration-300 relative flex flex-col h-full";
                
                // SUNTIKKAN WARNA SESUAI STATUS TERBARU
                if(statusBaru === 'Hadir') {
                    label.classList.add('bg-emerald-50', 'text-emerald-700', 'border-emerald-200');
                    dot.classList.add('bg-emerald-500');
                    card.classList.add('border-emerald-200', 'shadow-[0_4px_20px_-4px_rgba(16,185,129,0.15)]');
                } else if(statusBaru === 'Izin') {
                    label.classList.add('bg-blue-50', 'text-blue-700', 'border-blue-200');
                    dot.classList.add('bg-blue-500');
                    card.classList.add('border-blue-200', 'shadow-[0_4px_20px_-4px_rgba(59,130,246,0.15)]');
                } else if(statusBaru === 'Sakit') {
                    label.classList.add('bg-amber-50', 'text-amber-700', 'border-amber-200');
                    dot.classList.add('bg-amber-500');
                    card.classList.add('border-amber-200', 'shadow-[0_4px_20px_-4px_rgba(245,158,11,0.15)]');
                } else if(statusBaru === 'Kosong') {
                    label.classList.add('bg-rose-50', 'text-rose-700', 'border-rose-200');
                    dot.classList.add('bg-rose-500');
                    card.classList.add('border-rose-200', 'shadow-[0_4px_20px_-4px_rgba(225,29,72,0.15)]');
                }

                // SINKRONISASI INFO KETERANGAN
                infoDiv.setAttribute('data-ket', keterangan || '');
                infoDiv.setAttribute('data-inv', nigPengganti || '');
                infoDiv.innerHTML = ''; 

                if(statusBaru === 'Hadir') {
                    infoDiv.classList.add('hidden');
                } else {
                    let htmlExtra = '';
                    if (keterangan) {
                        htmlExtra += `<p class="text-xs text-slate-600 font-semibold mb-1 leading-snug"><i class="fas fa-quote-left text-slate-300 text-[10px] mr-1.5"></i>${keterangan}</p>`;
                    }
                    if (nigPengganti) {
                        let selectPengganti = document.getElementById('modal-nig-pengganti');
                        let teksPengganti = selectPengganti.options[selectPengganti.selectedIndex].text;
                        htmlExtra += `<div class="inline-flex items-center text-[10px] text-indigo-700 font-bold bg-indigo-100/50 px-2 py-1.5 rounded-lg border border-indigo-200/50 mt-1.5"><i class="fas fa-user-shield mr-1.5 text-indigo-500"></i> Inval: ${teksPengganti}</div>`;
                    }

                    if(htmlExtra !== '') {
                        infoDiv.innerHTML = `<div class="bg-slate-50 p-3 rounded-xl border border-slate-200/60">${htmlExtra}</div>`;
                        infoDiv.classList.remove('hidden');
                    } else {
                        infoDiv.classList.add('hidden');
                    }
                }

                // NORMALISASI TOMBOL LOADING
                if (btnLoading) {
                    btnLoading.innerHTML = teksAsli;
                    btnLoading.disabled = false;
                    btnLoading.classList.remove('opacity-75', 'cursor-wait', 'cursor-not-allowed');
                }
                if (isModal) tutupPopup();
            })
            .catch((error) => {
                alert("Gagal menyimpan data ke server.");
                if (btnLoading) {
                    btnLoading.innerHTML = teksAsli;
                    btnLoading.disabled = false;
                    btnLoading.classList.remove('opacity-75', 'cursor-wait', 'cursor-not-allowed');
                }
            });
        }

        // ==========================================
        // RADAR SINKRONISASI REAL-TIME
        // ==========================================
        setInterval(function() {
            fetch('/cek-kehadiran-terbaru', {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(response => response.json())
            .then(data => {
                data.forEach(item => {
                    let label = document.getElementById('label-' + item.jadwal_id);
                    if (label) {
                        let teksElement = document.getElementById('teks-' + item.jadwal_id);
                        let teksSekarang = teksElement.innerText;
                        
                        let infoDiv = document.getElementById('info-extra-' + item.jadwal_id);
                        let ketSekarang = infoDiv ? infoDiv.getAttribute('data-ket') : '';
                        let invSekarang = infoDiv ? infoDiv.getAttribute('data-inv') : '';
                        
                        let itemKet = item.keterangan || '';
                        let itemInv = item.nig_pengganti || '';

                        // JIKA ADA PERUBAHAN DATA DARI LUAR (Misal: Guru Scan QR)
                        if (teksSekarang !== item.status || ketSekarang !== itemKet || invSekarang !== itemInv) {
                            
                            let dot = document.getElementById('dot-' + item.jadwal_id);
                            let card = document.getElementById('card-' + item.jadwal_id);
                            
                            teksElement.innerText = item.status;
                            
                            label.className = "text-[10px] uppercase tracking-widest font-black px-3 py-1.5 rounded-xl border flex items-center transition-all duration-500 animate-pulse";
                            dot.className = "w-1.5 h-1.5 rounded-full mr-1.5 animate-pulse";
                            card.className = "bg-white rounded-3xl p-5 shadow-sm border hover:-translate-y-1 transition-all duration-300 relative flex flex-col h-full";
                            
                            if(item.status === 'Hadir') {
                                label.classList.add('bg-emerald-50', 'text-emerald-700', 'border-emerald-200');
                                dot.classList.add('bg-emerald-500');
                                card.classList.add('border-emerald-200', 'shadow-[0_4px_20px_-4px_rgba(16,185,129,0.15)]');
                            } else if(item.status === 'Izin') {
                                label.classList.add('bg-blue-50', 'text-blue-700', 'border-blue-200');
                                dot.classList.add('bg-blue-500');
                                card.classList.add('border-blue-200', 'shadow-[0_4px_20px_-4px_rgba(59,130,246,0.15)]');
                            } else if(item.status === 'Sakit') {
                                label.classList.add('bg-amber-50', 'text-amber-700', 'border-amber-200');
                                dot.classList.add('bg-amber-500');
                                card.classList.add('border-amber-200', 'shadow-[0_4px_20px_-4px_rgba(245,158,11,0.15)]');
                            } else if(item.status === 'Kosong') {
                                label.classList.add('bg-rose-50', 'text-rose-700', 'border-rose-200');
                                dot.classList.add('bg-rose-500');
                                card.classList.add('border-rose-200', 'shadow-[0_4px_20px_-4px_rgba(225,29,72,0.15)]');
                            }
                            
                            setTimeout(() => label.classList.remove('animate-pulse'), 2000);

                            // SINKRONISASI KOTAK KETERANGAN
                            if(infoDiv) {
                                infoDiv.setAttribute('data-ket', itemKet);
                                infoDiv.setAttribute('data-inv', itemInv);
                                
                                if(item.status === 'Hadir' || item.status === 'Menunggu') {
                                    infoDiv.classList.add('hidden');
                                    infoDiv.innerHTML = '';
                                } else {
                                    let htmlExtra = '';
                                    if (itemKet) {
                                        htmlExtra += `<p class="text-xs text-slate-600 font-semibold mb-1 leading-snug"><i class="fas fa-quote-left text-slate-300 text-[10px] mr-1.5"></i>${itemKet}</p>`;
                                    }
                                    if (itemInv) {
                                        let selectPengganti = document.getElementById('modal-nig-pengganti');
                                        let teksPengganti = itemInv; 
                                        if(selectPengganti) {
                                            for(let i=0; i<selectPengganti.options.length; i++){
                                                if(selectPengganti.options[i].value === itemInv) teksPengganti = selectPengganti.options[i].text;
                                            }
                                        }
                                        htmlExtra += `<div class="inline-flex items-center text-[10px] text-indigo-700 font-bold bg-indigo-100/50 px-2 py-1.5 rounded-lg border border-indigo-200/50 mt-1.5"><i class="fas fa-user-shield mr-1.5 text-indigo-500"></i> Inval: ${teksPengganti}</div>`;
                                    }
                                    
                                    if(htmlExtra !== '') {
                                        infoDiv.innerHTML = `<div class="bg-slate-50 p-3 rounded-xl border border-slate-200/60">${htmlExtra}</div>`;
                                        infoDiv.classList.remove('hidden');
                                    } else {
                                        infoDiv.classList.add('hidden');
                                    }
                                }
                            }
                        }
                    }
                });
            })
            .catch(error => console.error('Radar Error:', error));
        }, 5000);

        // ==========================================
        // UTILITAS LAINNYA
        // ==========================================
        function salinTeksWA() {
            let teksArea = document.getElementById("teks-wa-hidden");
            teksArea.classList.remove('hidden'); 
            teksArea.select();
            teksArea.setSelectionRange(0, 99999); 
            document.execCommand("copy");
            teksArea.classList.add('hidden'); 

            let btnTeks = document.getElementById("teks-btn-wa");
            let isiAsli = btnTeks.innerText;
            btnTeks.innerText = 'Tersalin!';
            
            setTimeout(() => { btnTeks.innerText = isiAsli; }, 2000);
        }

        document.addEventListener("DOMContentLoaded", function() {
            if (typeof flatpickr !== 'undefined') {
                flatpickr("#kalender-dinamis", {
                    dateFormat: "Y-m-d", 
                    altInput: true,      
                    altFormat: "d-m-Y",  
                    disableMobile: true, 
                    onChange: function(selectedDates, dateStr, instance) {
                        instance.element.closest('form').submit();
                    }
                });
            } else {
                console.warn("Library kalender belum termuat secara sempurna.");
            }
        });
    </script>
@endpush