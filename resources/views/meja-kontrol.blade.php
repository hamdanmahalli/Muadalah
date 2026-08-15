@extends('layouts.app')

@section('title', 'Meja Kontrol - SmartPesantren')

@section('content')
    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
        <h2 class="text-xl font-bold text-gray-700"><i class="fas fa-tv mr-2 text-indigo-600"></i> Meja Kontrol Kehadiran</h2>
        
        <div class="flex items-center space-x-3">
            <div class="flex items-center bg-white border border-gray-300 rounded-lg shadow-sm px-3 py-1.5">
                <label for="pilih_jam" class="text-sm font-semibold text-gray-700 mr-2">
                <select id="pilih_jam" onchange="gantiJam(this.value)" class="text-sm bg-transparent outline-none font-bold text-indigo-600 cursor-pointer">
                    @foreach($opsiBlokJam as $opsi)
                        <option value="{{ $opsi['nilai'] }}" {{ $jamPilihan == $opsi['nilai'] ? 'selected' : '' }}>
                            {{ $opsi['label'] }}
                        </option>
                    @endforeach
                </select>
            </div>
            <span class="bg-indigo-50 text-indigo-700 px-4 py-1.5 rounded-full text-sm font-bold border border-indigo-200 flex items-center shadow-sm">
                {{ \Carbon\Carbon::now()->translatedFormat('l, d M Y') }}
            </span>
        </div>
    </div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 pb-20">
        @forelse($jadwals as $jadwal)
        @php
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
            
            <div class="p-5 flex-grow flex flex-col pt-6">
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
                <span id="label-{{ $idUtama }}" class="text-xs font-bold px-2.5 py-1.5 rounded-md border {{ $warnaStatus }} flex items-center">
                    <i id="ikon-{{ $idUtama }}" class="fas {{ $ikonStatus }} mr-1.5"></i> <span id="teks-{{ $idUtama }}">{{ $statusSaatIni }}</span>
                </span>
                
                <div class="flex space-x-1">
                    <button onclick='simpanLangsung(@json($jadwal["id_list"]), "Hadir", {{ $idUtama }})' class="w-8 h-8 flex items-center justify-center bg-white border border-gray-300 text-green-600 rounded hover:bg-green-50 hover:border-green-400 transition" title="Set Hadir">
                        <i class="fas fa-check"></i>
                    </button>
                    <button onclick='bukaPopup(@json($jadwal["id_list"]), "Izin", {{ $idUtama }})' class="w-8 h-8 flex items-center justify-center bg-white border border-gray-300 text-blue-600 rounded hover:bg-blue-50 hover:border-blue-400 transition" title="Set Izin/Inval">
                        <i class="fas fa-info"></i>
                    </button>
                    <button onclick='bukaPopup(@json($jadwal["id_list"]), "Kosong", {{ $idUtama }})' class="w-8 h-8 flex items-center justify-center bg-white border border-gray-300 text-red-600 rounded hover:bg-red-50 hover:border-red-400 transition" title="Set Kosong">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-full flex flex-col items-center justify-center py-20 bg-white rounded-xl shadow-sm border border-gray-100 border-dashed">
            <div class="text-6xl text-gray-200 mb-4"><i class="fas fa-mug-hot"></i></div>
            <p class="text-gray-500 font-semibold text-lg">Tidak ada jadwal kelas untuk blok jam ini.</p>
            <p class="text-sm text-gray-400 mt-1">Staf TU bisa beristirahat sejenak.</p>
        </div>
        @endforelse
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
                    <i class="fas fa-clipboard-list text-xl text-blue-600"></i>
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
    <div id="modal-status" class="hidden fixed inset-0 bg-gray-900 bg-opacity-60 overflow-y-auto h-full w-full z-50 flex items-center justify-center backdrop-blur-sm">
        <div class="relative mx-auto p-6 border w-full max-w-md shadow-2xl rounded-2xl bg-white">
            <div class="flex justify-between items-center mb-4 border-b pb-3">
                <h3 class="text-lg font-extrabold text-gray-800" id="judul-modal">Keterangan Status</h3>
                <button onclick="tutupPopup()" class="text-gray-400 hover:text-gray-600 transition"><i class="fas fa-times text-xl"></i></button>
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-bold text-gray-700 mb-1">Catatan / Keterangan</label>
                <textarea id="input-keterangan" rows="2" class="w-full border border-gray-200 rounded-xl p-3 bg-gray-50 focus:ring-2 focus:ring-blue-500 outline-none transition" placeholder="Tulis alasan izin/kosong..."></textarea>
            </div>

            <div id="area-inval" class="mb-5 hidden">
                <label class="block text-sm font-bold text-gray-700 mb-1">Guru Pengganti (Inval)</label>
                <select id="input-inval" class="w-full border border-gray-200 rounded-xl p-3 bg-gray-50 focus:ring-2 focus:ring-blue-500 outline-none transition">
                    <option value="">-- Tidak ada pengganti --</option>
                    @foreach($daftarGuru as $g)
                        <option value="{{ $g->nig }}">{{ $g->nama_guru }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex justify-end space-x-2 border-t pt-4">
                <button type="button" onclick="tutupPopup()" class="px-5 py-2.5 bg-gray-200 text-gray-800 rounded-xl font-bold hover:bg-gray-300 transition">Batal</button>
                <button type="button" onclick="simpanDariPopup()" class="px-5 py-2.5 bg-blue-600 text-white rounded-xl font-bold hover:bg-blue-700 transition shadow-md flex items-center">
                    <i class="fas fa-save mr-2"></i> Simpan Data
                </button>
            </div>
        </div>
    </div>

    <script>
        let jadwalIdsTerpilih = [];
        let idUtamaTerpilih = null;
        let statusTerpilih = '';

        // Fungsi klik tombol centang hijau (Hadir)
        function simpanLangsung(ids, status, idUtama) {
            kirimData(ids, status, null, null, idUtama);
        }

        // Fungsi buka pop-up (Izin / Kosong)
        function bukaPopup(ids, status, idUtama) {
            jadwalIdsTerpilih = ids;
            idUtamaTerpilih = idUtama;
            statusTerpilih = status;
            
            document.getElementById('modal-status').classList.remove('hidden');
            document.getElementById('judul-modal').innerText = "Status: " + status;
            
            document.getElementById('input-keterangan').value = '';
            document.getElementById('input-inval').value = '';
            
            // Tampilkan pilihan Guru Inval HANYA jika statusnya Izin
            if (status === 'Izin') {
                document.getElementById('area-inval').classList.remove('hidden');
            } else {
                document.getElementById('area-inval').classList.add('hidden');
            }
        }

        function tutupPopup() {
            document.getElementById('modal-status').classList.add('hidden');
        }

        // Fungsi tombol "Simpan Data" di dalam Pop-up
        function simpanDariPopup() {
            const ket = document.getElementById('input-keterangan').value;
            const inval = document.getElementById('input-inval').value;
            kirimData(jadwalIdsTerpilih, statusTerpilih, ket, inval, idUtamaTerpilih);
            tutupPopup();
        }

        // Mesin Utama Pengirim Data (AJAX Fetch) Super Canggih
        function kirimData(ids, status, keterangan, nigPengganti, idUtama) {
            fetch('/simpan-kehadiran', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json', // PENTING: Memaksa server membalas dengan JSON jika ada error
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    jadwal_ids: ids,
                    status: status,
                    keterangan: keterangan,
                    nig_pengganti: nigPengganti
                })
            })
            .then(async response => {
                const data = await response.json();
                // Jika server menolak (Error 500 / 422 / 419)
                if (!response.ok) {
                    throw new Error(data.message || data.pesan || 'Terjadi kesalahan sistem di server.');
                }
                return data;
            })
            .then(data => {
                // SULAP UI: Mengubah warna kotak secara instan tanpa reload halaman!
                let label = document.getElementById('label-' + idUtama);
                let ikon = document.getElementById('ikon-' + idUtama);
                let teks = document.getElementById('teks-' + idUtama);
                
                label.className = "text-xs font-bold px-2.5 py-1.5 rounded-md border flex items-center transition duration-300";
                
                if (status === 'Hadir') {
                    label.classList.add('bg-green-100', 'text-green-800', 'border-green-200');
                    ikon.className = "fas fa-check-circle mr-1.5";
                } else if (status === 'Izin') {
                    label.classList.add('bg-blue-100', 'text-blue-800', 'border-blue-200');
                    ikon.className = "fas fa-info-circle mr-1.5";
                } else if (status === 'Kosong') {
                    label.classList.add('bg-red-100', 'text-red-800', 'border-red-200');
                    ikon.className = "fas fa-times-circle mr-1.5";
                }
                
                teks.innerText = status;

                // Tutup modal jika sedang terbuka
                tutupPopup();
            })
            .catch(error => {
                console.error('Error Detail:', error);
                alert('GAGAL MENYIMPAN: \n\n' + error.message);
            });
        }
    </script>

@endsection

@push('scripts')
    <script>
        let token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        function gantiJam(jamKe) {
            window.location.href = "/meja-kontrol?jam=" + jamKe;
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
            fetch('/simpan-kehadiran', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token
                },
                body: JSON.stringify({
                    jadwal_ids: jadwalIdsArray,
                    status: statusBaru,
                    keterangan: keterangan,
                    nig_pengganti: nigPengganti
                })
            })
            .then(response => response.json())
            .then(data => {
                let label = document.getElementById('label-' + idUtama);
                let teks = document.getElementById('teks-' + idUtama);
                let ikon = document.getElementById('ikon-' + idUtama);
                let pita = document.getElementById('pita-' + idUtama);
                
                teks.innerText = statusBaru;
                
                label.classList.remove('bg-yellow-100', 'text-yellow-800', 'border-yellow-200', 'bg-green-100', 'text-green-800', 'border-green-200', 'bg-blue-100', 'text-blue-800', 'border-blue-200', 'bg-red-100', 'text-red-800', 'border-red-200');
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
    </script>
@endpush