@extends('layouts.app')

@section('title', 'Meja Kontrol - SmartPesantren')

@section('content')
    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
        <h2 class="text-xl font-bold text-gray-700"><i class="fas fa-tv mr-2 text-indigo-600"></i> Meja Kontrol Kehadiran</h2>
        
        <div class="flex flex-wrap items-center gap-3">
            <form method="GET" action="/meja-kontrol" class="flex flex-wrap items-center gap-3">
                    
                <span class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-bold shadow">{{ $hariIni }}</span>

                <div class="flex items-center bg-white border border-gray-300 rounded-lg shadow-sm px-3 py-1.5 hover:border-emerald-400 transition">
                    <label class="text-sm font-semibold text-gray-700 mr-2"><i class="fas fa-calendar-alt text-emerald-600 mr-1"></i> </label>
                    
                    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
                    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

                    <input type="text" id="kalender-dinamis" name="tanggal" value="{{ $tanggalPilihan }}" class="text-sm bg-transparent outline-none font-bold text-emerald-700 cursor-pointer w-24">
                </div>

                <div class="flex items-center bg-white border border-gray-300 rounded-lg shadow-sm px-3 py-1.5 hover:border-indigo-400 transition">
                    <label for="jam" class="text-sm font-semibold text-gray-700 mr-2"><i class="fas fa-clock text-indigo-600 mr-1"></i></label>
                    <select name="jam" onchange="this.form.submit()" class="text-sm bg-transparent outline-none font-bold text-indigo-600 cursor-pointer">
                        @foreach($opsiBlokJam as $opsi)
                            <option value="{{ $opsi['nilai'] }}" {{ $jamPilihan == $opsi['nilai'] ? 'selected' : '' }}>
                                {{ $opsi['label'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </form>

            @php
                $tglFormat = \Carbon\Carbon::parse($tanggalPilihan)->translatedFormat('d F Y');
                $waText = "ASSALAMUALAIKUM WR. WB.\nSekedar MENGINGATKAN bahwa Jadwal Mengajar hari ini, {$hariIni}, {$tglFormat}.\n\n";

                // Membuang jadwal yang terkena Libur agar tidak ikut tersalin ke WA
                $jadwalAktif = collect($jadwals)->filter(function($j) {
                    return !isset($j['is_libur']) || !$j['is_libur'];
                });

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

            <textarea id="teks-wa-hidden" class="hidden">{!! $waText !!}</textarea>
            <button type="button" onclick="salinTeksWA()" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg text-sm font-bold shadow transition flex items-center cursor-pointer">
                <i class="fab fa-whatsapp mr-2 text-lg"></i> <span id="teks-btn-wa">Copy WA</span>
            </button>
        </div>
    </div>

    @if(isset($daftarLibur) && $daftarLibur->count() > 0)
        <div class="bg-rose-50 border border-rose-200 p-4 rounded-2xl mb-6 shadow-sm">
            @foreach($daftarLibur as $libur)
            <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-2 {{ !$loop->last ? 'mb-3 border-b border-rose-100 pb-3' : '' }}">
                <div class="flex items-center space-x-3">
                    <div class="p-2.5 bg-rose-100 text-rose-600 rounded-xl flex-shrink-0">
                        <i class="fas fa-calendar-day text-xl"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-rose-800">🎉 HARI LIBUR: {{ $libur->nama_libur }}</h4>
                        <p class="text-xs text-rose-600 mt-0.5 font-medium leading-relaxed">
                            Libur untuk 
                            @if($libur->target_libur == 'semua')
                                <b>Seluruh Kelas</b>.
                            @else
                                @php
                                    // DEKODE JSON DENGAN AMAN
                                    $kelasArr = is_string($libur->kelas_ids) ? json_decode($libur->kelas_ids, true) : (is_array($libur->kelas_ids) ? $libur->kelas_ids : []);
                                    $namaKelasStr = (!empty($kelasArr) && is_array($kelasArr) && class_exists('\App\Models\Kelas')) 
                                        ? \App\Models\Kelas::whereIn('id', $kelasArr)->pluck('nama_kelas')->implode(', ') 
                                        : 'Tertentu';
                                @endphp
                                <b>Kelas {{ $namaKelasStr }}</b>.
                            @endif
                            
                            @if($libur->tipe_libur == 'Penuh')
                                <b>Semua Jam</b>.
                            @else
                                @php
                                    // DEKODE JSON DENGAN AMAN
                                    $jamArr = is_string($libur->jam_diliburkan) ? json_decode($libur->jam_diliburkan, true) : (is_array($libur->jam_diliburkan) ? $libur->jam_diliburkan : []);
                                    if(is_array($jamArr) && count($jamArr) > 0) {
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

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 pb-20">
        @php $adaJadwalTampil = false; @endphp

        @foreach($jadwals as $jadwal)
        
            {{-- KECERDASAN MUTLAK: Hilangkan Total Kotak Kelas Jika Termasuk Target Libur --}}
            @if(isset($jadwal['is_libur']) && $jadwal['is_libur'])
                @continue
            @endif

            @php
                $adaJadwalTampil = true;
                // Mengambil data utama secara langsung dari struktur blok yang benar
                $idUtama = $jadwal['id_list'][0];
                $rekam = $kehadiranHariIni[$idUtama] ?? null;
                $statusSaatIni = $rekam ? $rekam->status : 'Menunggu';
                $keteranganSaatIni = $rekam ? $rekam->keterangan : null;
                $nigPenggantiSaatIni = $rekam ? $rekam->nig_pengganti : null;
                
                $namaPenggantiSaatIni = null;
                if($nigPenggantiSaatIni) {
                    $guruInval = collect($daftarGuru)->firstWhere('nig', $nigPenggantiSaatIni);
                    $namaPenggantiSaatIni = $guruInval ? $guruInval->nama_guru : $nigPenggantiSaatIni;
                }
                
                $warnaStatus = 'bg-yellow-100 text-yellow-800 border-yellow-200';
                $ikonStatus = 'fa-clock';
                if($statusSaatIni == 'Hadir') { $warnaStatus = 'bg-green-100 text-green-800 border-green-200'; $ikonStatus = 'fa-check-circle'; }
                if($statusSaatIni == 'Izin') { $warnaStatus = 'bg-blue-100 text-blue-800 border-blue-200'; $ikonStatus = 'fa-info-circle'; }
                if($statusSaatIni == 'Kosong') { $warnaStatus = 'bg-red-100 text-red-800 border-red-200'; $ikonStatus = 'fa-times-circle'; }
            @endphp

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 hover:shadow-md transition-all duration-300 relative flex flex-col h-full overflow-hidden" id="card-{{ $idUtama }}">
                <div id="pita-{{ $idUtama }}" class="h-2 w-full absolute top-0 left-0 {{ str_replace('bg-', 'bg-', explode(' ', $warnaStatus)[0]) }}"></div>
                
                <div class="p-5 flex-grow flex flex-col pt-6 pb-4">
                    <div class="flex justify-between items-start mb-3">
                        <span class="bg-gray-100 text-gray-800 text-xs font-bold px-2.5 py-1 rounded-md border border-gray-200">Kelas {{ $jadwal['kelas'] }}</span>
                        <span class="text-xs font-bold px-2 py-1 rounded-md bg-indigo-50 text-indigo-700 border border-indigo-100">Jam {{ $jadwal['jam_tampil'] }}</span>
                    </div>
                    
                    <h3 class="text-base font-bold text-gray-800 leading-tight mb-1">{{ $jadwal['mata_pelajaran'] }}</h3>
                    <p class="text-sm text-gray-600 mb-1 font-medium"><i class="fas fa-chalkboard-teacher mr-1 text-gray-400"></i> {{ $jadwal['nama_guru'] }}</p>
                    <p class="text-xs text-gray-400 mb-3 ml-5">NIG: {{ $jadwal['nig_guru'] }}</p>

                    <div id="info-extra-{{ $idUtama }}" class="mt-auto bg-gray-50 p-2.5 rounded-lg border border-gray-100 {{ (!$keteranganSaatIni && !$namaPenggantiSaatIni) ? 'hidden' : '' }}">
                        @if($keteranganSaatIni)
                            <p class="text-xs text-gray-600 mb-1 leading-snug"><i class="fas fa-pen-alt text-gray-400 mr-1"></i> {{ $keteranganSaatIni }}</p>
                        @endif
                        @if($namaPenggantiSaatIni)
                            <p class="text-xs text-indigo-700 font-bold"><i class="fas fa-exchange-alt mr-1"></i> Inval: {{ $namaPenggantiSaatIni }}</p>
                        @endif
                    </div>
                </div>
                
                <div class="p-4 bg-gray-50 border-t border-gray-100 flex justify-between items-center mt-auto">
                    <span id="label-{{ $idUtama }}" class="text-xs font-bold px-2.5 py-1.5 rounded-md border flex items-center {{ $warnaStatus }}">
                        <i id="ikon-{{ $idUtama }}" class="fas {{ $ikonStatus }} mr-1.5"></i> <span id="teks-{{ $idUtama }}">{{ $statusSaatIni }}</span>
                    </span>
                    
                    <div class="flex space-x-1.5 flex-shrink-0">
                        <button onclick='simpanLangsung(@json($jadwal["id_list"]), "Hadir", {{ $idUtama }})' class="w-8 h-8 flex items-center justify-center bg-white border border-gray-300 text-green-600 rounded-lg hover:bg-green-50 hover:border-green-400 transition" title="Hadir">
                            <i class="fas fa-check"></i>
                        </button>
                        <button onclick='bukaPopup(@json($jadwal["id_list"]), "Izin", {{ $idUtama }})' class="w-8 h-8 flex items-center justify-center bg-white border border-gray-300 text-blue-600 rounded-lg hover:bg-blue-50 hover:border-blue-400 transition" title="Izin">
                            <i class="fas fa-info"></i>
                        </button>
                        <button onclick='bukaPopup(@json($jadwal["id_list"]), "Kosong", {{ $idUtama }})' class="w-8 h-8 flex items-center justify-center bg-white border border-gray-300 text-red-600 rounded-lg hover:bg-red-50 hover:border-red-400 transition" title="Kosong">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>
        @endforeach

        @if(count($jadwals) > 0 && !$adaJadwalTampil)
        <div class="col-span-full flex flex-col items-center justify-center py-20 bg-white rounded-xl shadow-sm border border-gray-100 border-dashed">
            <div class="text-6xl text-rose-200 mb-4"><i class="fas fa-bed"></i></div>
            <p class="text-gray-500 font-semibold text-lg">Semua kelas pada blok jam ini sedang diliburkan.</p>
            <p class="text-sm text-gray-400 mt-1">Staf TU bisa beristirahat sejenak.</p>
        </div>
        @elseif(count($jadwals) == 0)
        <div class="col-span-full flex flex-col items-center justify-center py-20 bg-white rounded-xl shadow-sm border border-gray-100 border-dashed">
            <div class="text-6xl text-gray-200 mb-4"><i class="fas fa-mug-hot"></i></div>
            <p class="text-gray-500 font-semibold text-lg">Tidak ada jadwal kelas untuk blok jam ini.</p>
            <p class="text-sm text-gray-400 mt-1">Staf TU bisa beristirahat sejenak.</p>
        </div>
        @endif
    </div>

    <div id="popup-modal" class="hidden fixed inset-0 bg-gray-900 bg-opacity-60 overflow-y-auto h-full w-full z-50 flex items-center justify-center backdrop-blur-sm transition-opacity">
        <div class="relative mx-auto p-6 border w-full max-w-md shadow-2xl rounded-xl bg-white transform transition-all">
            <div class="absolute top-0 right-0 pt-4 pr-4">
                <button onclick="tutupPopup()" class="text-gray-400 hover:text-gray-600 transition">
                    
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <div class="mt-2 text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 mb-4">
                    <i id="ikon-modal" class="fas fa-clipboard-list text-xl text-blue-600"></i>
                </div>
                <h3 class="text-xl leading-6 font-bold text-gray-900" id="popup-judul">Konfirmasi Status</h3>
                <p class="text-sm text-gray-500 mt-1">Silakan isi detail keterangan di bawah ini.</p>
                
                <div class="mt-6 text-left">
                    <input type="hidden" id="modal-jadwal-ids">
                    <input type="hidden" id="modal-id-utama">
                    <input type="hidden" id="modal-status">
                    
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5"><i class="fas fa-pen-alt text-gray-400 mr-1"></i> Alasan / Keterangan (Opsional)</label>
                    <textarea id="modal-keterangan" rows="3" class="w-full border border-gray-300 rounded-lg p-3 mb-4 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition" placeholder="Contoh: Sakit tipes, dinas luar, ban bocor..."></textarea>
                    
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5"><i class="fas fa-user-plus text-gray-400 mr-1"></i> Guru Pengganti / Inval (Opsional)</label>
                    <div class="relative">
                        <select id="modal-nig-pengganti" class="w-full border border-gray-300 rounded-lg p-3 text-sm appearance-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition cursor-pointer bg-white">
                            <option value="">-- Kosongkan jika tidak ada --</option>
                            @foreach($daftarGuru as $guru)
                                <option value="{{ $guru->nig }}">{{ $guru->nama_guru }}</option>
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500">
                            <i class="fas fa-chevron-down text-xs"></i>
                        </div>
                    </div>
                </div>
                <div class="mt-8 flex justify-end space-x-3">
                    <button type="button" onclick="tutupPopup()" class="px-5 py-2.5 bg-white border border-gray-300 text-gray-700 text-sm font-semibold rounded-lg hover:bg-gray-50 transition w-full">Batal</button>
                    
                    <button id="btn-simpan-modal" type="button" onclick="kirimDataModal()" class="px-5 py-2.5 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition w-full shadow-sm">Simpan Data</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        
        let token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // FITUR KEMBALI: Mesin Penggerak Dropdown Pilihan Jam (Mempertahankan Filter Hari)
        function gantiJam(jamKe) {
            // Mengambil URL saat ini agar filter 'Hari' tidak hilang saat jam diganti
            const urlParams = new URLSearchParams(window.location.search);
            urlParams.set('jam', jamKe);
            window.location.search = urlParams.toString();
        }
        
        function simpanLangsung(jadwalIdsArray, statusBaru, idUtama) {
            // FITUR BARU: Ubah tombol kecil di Grid menjadi muter/loading
            let btn = event.currentTarget;
            let teksAsli = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            btn.disabled = true;
            btn.classList.add('opacity-75', 'cursor-not-allowed');

            kirimKeServer(jadwalIdsArray, statusBaru, null, null, idUtama, btn, teksAsli, false);
        }

        function bukaPopup(jadwalIdsArray, status, idUtama) {
            document.getElementById('popup-modal').classList.remove('hidden');
            document.getElementById('modal-jadwal-ids').value = JSON.stringify(jadwalIdsArray);
            document.getElementById('modal-id-utama').value = idUtama;
            document.getElementById('modal-status').value = status;
            
            let ikonModal = document.querySelector('#popup-modal .fa-clipboard-list').parentElement;
            ikonModal.className = 'mx-auto flex items-center justify-center h-12 w-12 rounded-full mb-4';
            
            if(status === 'Izin') {
                ikonModal.classList.add('bg-blue-100', 'text-blue-600');
                document.querySelector('#popup-modal .fa-clipboard-list').className = 'fas fa-info-circle text-xl';
            } else {
                ikonModal.classList.add('bg-red-100', 'text-red-600');
                document.querySelector('#popup-modal .fa-clipboard-list').className = 'fas fa-times-circle text-xl';
            }

            document.getElementById('popup-judul').innerText = "Konfirmasi " + status;
            document.getElementById('modal-keterangan').value = "";
            document.getElementById('modal-nig-pengganti').value = "";
        }

        function tutupPopup() {
            document.getElementById('popup-modal').classList.add('hidden');
        }

        function kirimDataModal() {
            // FITUR BARU: Ubah tombol biru "Simpan" di Modal menjadi Memproses
            let btn = document.getElementById('btn-simpan-modal');
            let teksAsli = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Memproses...';
            btn.disabled = true;
            btn.classList.add('opacity-75', 'cursor-not-allowed');

            let jadwalIdsArray = JSON.parse(document.getElementById('modal-jadwal-ids').value);
            let idUtama = document.getElementById('modal-id-utama').value;
            let status = document.getElementById('modal-status').value;
            let keterangan = document.getElementById('modal-keterangan').value;
            let nigPengganti = document.getElementById('modal-nig-pengganti').value;

            kirimKeServer(jadwalIdsArray, status, keterangan, nigPengganti, idUtama, btn, teksAsli, true);
        }

        function kirimKeServer(jadwalIdsArray, statusBaru, keterangan, nigPengganti, idUtama, btnLoading, teksAsli, isModal) {
            // KECERDASAN BARU: Ambil tanggal yang sedang tampil di kalender
            let tanggalLayar = document.getElementById('kalender-dinamis').value;

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
                    tanggal: tanggalLayar // <-- KIRIM TANGGAL KE MESIN DATABASE
                })
            })
            .then(async response => {
                // SULAP UI: Mengubah warna kotak secara instan tanpa reload halaman!
                let label = document.getElementById('label-' + idUtama);
                let teks = document.getElementById('teks-' + idUtama);
                let ikon = document.getElementById('ikon-' + idUtama);
                let pita = document.getElementById('pita-' + idUtama);
                
                teks.innerText = statusBaru;
                
                label.className = "text-xs font-bold px-2.5 py-1.5 rounded-md border flex items-center transition duration-300";
                label.classList.remove('bg-yellow-100', 'text-yellow-800', 'border-yellow-200', 'bg-green-100', 'text-green-800', 'border-green-200', 'bg-blue-100', 'text-blue-800', 'border-blue-200', 'bg-red-100', 'text-red-800', 'border-red-200');
                pita.className = "h-2 w-full absolute top-0 left-0 transition duration-300";
                pita.classList.remove('bg-yellow-100', 'bg-green-100', 'bg-blue-100', 'bg-red-100');
                ikon.className = 'fas mr-1.5';
                
                if(statusBaru === 'Hadir') {
                    label.classList.add('bg-green-100', 'text-green-800', 'border-green-200');
                    pita.classList.add('bg-green-100');
                    ikon.classList.add('fa-check-circle');
                }
                if(statusBaru === 'Izin') {
                    label.classList.add('bg-blue-100', 'text-blue-800', 'border-blue-200');
                    pita.classList.add('bg-blue-100');
                    ikon.classList.add('fa-info-circle');
                }
                if(statusBaru === 'Kosong') {
                    label.classList.add('bg-red-100', 'text-red-800', 'border-red-200');
                    pita.classList.add('bg-red-100');
                    ikon.classList.add('fa-times-circle');
                }

                let infoDiv = document.getElementById('info-extra-' + idUtama);
                infoDiv.innerHTML = ''; 

                if(statusBaru === 'Hadir') {
                    infoDiv.classList.add('hidden');
                } else {
                    let htmlExtra = '';
                    if (keterangan) {
                        htmlExtra += `<p class="text-xs text-gray-600 mb-1 leading-snug"><i class="fas fa-pen-alt text-gray-400 mr-1"></i> ${keterangan}</p>`;
                    }
                    
                    if (nigPengganti) {
                        let selectPengganti = document.getElementById('modal-nig-pengganti');
                        let teksPengganti = selectPengganti.options[selectPengganti.selectedIndex].text;
                        htmlExtra += `<p class="text-xs text-indigo-700 font-bold"><i class="fas fa-exchange-alt mr-1"></i> Inval: ${teksPengganti}</p>`;
                    }

                    if(htmlExtra !== '') {
                        infoDiv.innerHTML = htmlExtra;
                        infoDiv.classList.remove('hidden');
                    } else {
                        infoDiv.classList.add('hidden');
                    }
                }

                // Kembalikan teks asli dan normalkan tombol setelah sukses
                if (btnLoading) {
                    btnLoading.innerHTML = teksAsli;
                    btnLoading.disabled = false;
                    btnLoading.classList.remove('opacity-75', 'cursor-not-allowed');
                }
                if (isModal) {
                    tutupPopup();
                }
            })
            .catch((error) => {
                alert("Gagal menyimpan data.");
                // Jika error jaringan, normalkan tombol kembali
                if (btnLoading) {
                    btnLoading.innerHTML = teksAsli;
                    btnLoading.disabled = false;
                    btnLoading.classList.remove('opacity-75', 'cursor-not-allowed');
                }
            });
        }

        // FITUR BARU: Kalender Kustom dengan Format DD-MM-YYYY
        document.addEventListener("DOMContentLoaded", function() {
            flatpickr("#kalender-dinamis", {
                dateFormat: "Y-m-d", // Format asli yang dibutuhkan Database
                altInput: true,      // Menyamarkan input dengan format baru
                altFormat: "d-m-Y",  // Format DD-MM-YYYY yang dilihat Staf TU
                disableMobile: true, // Mencegah HP menimpa gaya kalender kita
                onChange: function(selectedDates, dateStr, instance) {
                    // Otomatis memuat ulang data (Submit) saat tanggal dipilih
                    instance.element.closest('form').submit();
                }
            });
        });
        
        // FITUR BARU: Radar Auto-Sync (Berjalan setiap 5 detik)
        setInterval(function() {
            fetch('/cek-kehadiran-terbaru', {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(response => response.json())
            .then(data => {
                // Loop data terbaru dari server
                data.forEach(item => {
                    // Cari kotak di layar yang cocok dengan ID jadwal
                    let label = document.getElementById('label-' + item.jadwal_id);
                    if (label) {
                        let teksSekarang = document.getElementById('teks-' + item.jadwal_id).innerText;
                        
                        // Jika status di layar berbeda dengan di server (berarti Staf lain baru saja mengubahnya)
                        if (teksSekarang !== item.status) {
                            let ikon = document.getElementById('ikon-' + item.jadwal_id);
                            let pita = document.getElementById('pita-' + item.jadwal_id);
                            let teks = document.getElementById('teks-' + item.jadwal_id);
                            
                            // Ubah teksnya
                            teks.innerText = item.status;
                            
                            // Hapus warna lama
                            label.className = "text-xs font-bold px-2.5 py-1.5 rounded-md border flex items-center transition duration-500";
                            pita.className = "h-2 w-full absolute top-0 left-0 transition duration-500";
                            ikon.className = "fas mr-1.5";
                            
                            // Pasang warna baru layaknya sihir
                            if(item.status === 'Hadir') {
                                label.classList.add('bg-green-100', 'text-green-800', 'border-green-200');
                                pita.classList.add('bg-green-100');
                                ikon.classList.add('fa-check-circle');
                            } else if(item.status === 'Izin') {
                                label.classList.add('bg-blue-100', 'text-blue-800', 'border-blue-200');
                                pita.classList.add('bg-blue-100');
                                ikon.classList.add('fa-info-circle');
                            } else if(item.status === 'Kosong') {
                                label.classList.add('bg-red-100', 'text-red-800', 'border-red-200');
                                pita.classList.add('bg-red-100');
                                ikon.classList.add('fa-times-circle');
                            }
                            
                            // (Opsional) Memunculkan peringatan visual sekilas bahwa data ini baru saja diperbarui staf lain
                            label.classList.add('animate-pulse');
                            setTimeout(() => label.classList.remove('animate-pulse'), 2000);
                        }
                    }
                });
            })
            .catch(error => console.error('Gagal Sinkronisasi:', error));
        }, 5000); // 5000 milidetik = 5 detik

        // FITUR BARU: FUNGSI MENYALIN TEKS WA
        function salinTeksWA() {
            let teksArea = document.getElementById("teks-wa-hidden");
            teksArea.classList.remove('hidden'); // Munculkan sebentar untuk disalin mesin
            teksArea.select();
            teksArea.setSelectionRange(0, 99999); // Kompatibilitas untuk HP
            document.execCommand("copy");
            teksArea.classList.add('hidden'); // Sembunyikan lagi

            // Ubah teks tombol sementara sebagai umpan balik visual
            let btnTeks = document.getElementById("teks-btn-wa");
            let isiAsli = btnTeks.innerText;
            btnTeks.innerText = 'Tersalin!';
            
            setTimeout(() => {
                btnTeks.innerText = isiAsli;
            }, 2000);
        }
        
    </script>
@endpush