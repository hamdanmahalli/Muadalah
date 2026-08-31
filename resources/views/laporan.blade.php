@extends('layouts.app')

@section('title', 'Laporan Kehadiran Eksekutif')

@section('content')
<div class="space-y-6">
    <input type="hidden" id="filter-tgl-mulai" value="{{ $tglMulai }}">
    <input type="hidden" id="filter-tgl-selesai" value="{{ $tglSelesai }}">

    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-3">
                <div class="p-2.5 bg-emerald-50 text-emerald-600 rounded-xl">
                    <i class="fas fa-file-invoice-dollar text-xl"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-800 tracking-tight">Laporan Kehadiran Guru</h2>
                    <p class="text-xs text-gray-500 font-medium">Periode Aktif: <span class="text-emerald-700 font-semibold">{{ $periodeTeks }}</span></p>
                </div>
            </div>
        </div>

        <form action="/laporan" method="GET" class="flex flex-wrap items-center gap-3">
            <div class="flex items-center space-x-2 bg-gray-50 p-1.5 rounded-xl border border-gray-200">
                <div class="flex items-center pl-2 text-gray-400">
                    <i class="fas fa-calendar-alt text-sm"></i>
                </div>
                
                <input type="date" name="tgl_mulai" value="{{ $tglMulai }}" class="bg-white border border-gray-200 rounded-lg px-2.5 py-1.5 text-xs font-bold text-gray-700 focus:outline-none focus:border-emerald-500 shadow-sm cursor-pointer">

                <div class="text-gray-400 font-bold text-xs px-1">s/d</div>

                <input type="date" name="tgl_selesai" value="{{ $tglSelesai }}" class="bg-white border border-gray-200 rounded-lg px-2.5 py-1.5 text-xs font-bold text-gray-700 focus:outline-none focus:border-emerald-500 shadow-sm cursor-pointer">
            </div>

            <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-xl text-xs font-bold transition shadow-sm flex items-center space-x-1.5 cursor-pointer">
                <i class="fas fa-filter"></i>
                <span>Terapkan Filter</span>
            </button>
        </form>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        
        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-xs relative overflow-hidden">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Jam Wajib</p>
                    <h3 class="text-2xl font-black text-gray-800 mt-1">{{ number_format($totalSeluruhWajib) }} <span class="text-xs text-gray-400 font-normal">Jam</span></h3>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-slate-50 text-slate-600 flex items-center justify-center text-lg">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
            <div class="mt-3 flex items-center text-[11px] text-gray-400 font-medium">
                <i class="fas fa-info-circle mr-1"></i> Target pengajaran seluruh guru
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-xs relative overflow-hidden">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-emerald-600 uppercase tracking-wider">Kelas Terisi</p>
                    <h3 class="text-2xl font-black text-emerald-700 mt-1">{{ number_format($totalSeluruhKelasTerisi) }} <span class="text-xs text-emerald-600 font-normal">Jam</span></h3>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
            <div class="mt-3 flex items-center text-[11px] text-emerald-600 font-semibold">
                <span class="bg-emerald-100 px-2 py-0.5 rounded-full mr-1.5 font-bold">{{ $persenTotalKelasTerisi }}%</span> Efektivitas Terpenuhi
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-xs relative overflow-hidden">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-rose-500 uppercase tracking-wider">Jam Kosong / Alpa</p>
                    <h3 class="text-2xl font-black text-rose-600 mt-1">{{ number_format($totalSeluruhKosong) }} <span class="text-xs text-rose-400 font-normal">Jam</span></h3>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-500 flex items-center justify-center text-lg">
                    <i class="fas fa-user-times"></i>
                </div>
            </div>
            <div class="mt-3 flex items-center text-[11px] text-rose-500 font-semibold">
                <span class="bg-rose-100 px-2 py-0.5 rounded-full mr-1.5 font-bold">{{ $persenTotalKosong }}%</span> Jam Tidak Terisi
            </div>
        </div>

        <div class="bg-gradient-to-br from-emerald-600 to-teal-700 p-5 rounded-2xl text-white shadow-md relative overflow-hidden">
            <div class="flex items-center justify-between relative z-10">
                <div>
                    <p class="text-xs font-semibold text-emerald-100 uppercase tracking-wider">Tingkat Kehadiran</p>
                    <h3 class="text-3xl font-black mt-1">{{ $persenTotalKelasTerisi }}%</h3>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-white/10 backdrop-blur-md flex items-center justify-center text-white text-xl">
                    <i class="fas fa-award"></i>
                </div>
            </div>
            <div class="mt-3 text-[11px] text-emerald-100 font-medium relative z-10">
                @if($persenTotalKelasTerisi >= 80)
                    🌟 Status Instansi: <span class="font-bold underline">Sangat Baik</span>
                @else
                    ⚠️ Status Instansi: <span class="font-bold underline">Perlu Evaluasi</span>
                @endif
            </div>
            <div class="absolute -bottom-4 -right-4 w-24 h-24 bg-white/5 rounded-full blur-xl pointer-events-none"></div>
        </div>

    </div>

    @if(isset($daftarLibur) && $daftarLibur->count() > 0)
    <div class="mb-2">
        <details class="group bg-rose-50/50 border border-rose-100 rounded-2xl shadow-sm overflow-hidden transition-all duration-300">
            <summary class="px-6 py-3.5 cursor-pointer flex items-center justify-between font-semibold text-rose-700 text-sm hover:bg-rose-50 transition outline-none">
                <div class="flex items-center space-x-3">
                    <div class="p-1.5 bg-rose-100 rounded-lg"><i class="fas fa-calendar-day text-rose-500"></i></div>
                    <span>Terdapat <b>{{ $daftarLibur->count() }} Agenda Libur</b> pada periode tanggal ini.</span>
                </div>
                <span class="transition duration-300 group-open:rotate-180 text-rose-400">
                    <i class="fas fa-chevron-down"></i>
                </span>
            </summary>
            <div class="px-6 py-4 border-t border-rose-100 bg-white text-xs text-gray-600">
                <ul class="space-y-3">
                    @foreach($daftarLibur as $libur)
                        <li class="flex items-start">
                            <i class="fas fa-dot-circle text-[8px] text-rose-400 mt-1.5 mr-3"></i>
                            <div>
                                <strong class="text-gray-800 text-sm">{{ $libur->nama_agenda }}</strong>
                                <span class="text-gray-500 ml-2">
                                    ({{ \Carbon\Carbon::parse($libur->tanggal_mulai)->translatedFormat('d M Y') }}
                                    @if($libur->tanggal_mulai != $libur->tanggal_selesai)
                                        - {{ \Carbon\Carbon::parse($libur->tanggal_selesai)->translatedFormat('d M Y') }}
                                    @endif)
                                </span>
                                @php
                                    $kelasArr = is_string($libur->kelas_ids) ? json_decode($libur->kelas_ids, true) : (is_array($libur->kelas_ids) ? $libur->kelas_ids : []);
                                    $namaKelasStr = (!empty($kelasArr) && is_array($kelasArr) && class_exists('\App\Models\Kelas'))
                                        ? \App\Models\Kelas::whereIn('id', $kelasArr)->pluck('nama_kelas')->implode(', ')
                                        : 'Tertentu';
                                    $jamArr = is_string($libur->jam_diliburkan) ? json_decode($libur->jam_diliburkan, true) : (is_array($libur->jam_diliburkan) ? $libur->jam_diliburkan : []);
                                    $jamTeks = (is_array($jamArr) && count($jamArr) > 0) ? (count($jamArr) > 1 ? min($jamArr) . '-' . max($jamArr) : implode(', ', $jamArr)) : '-';
                                @endphp
                                <p class="text-gray-500 mt-0.5">
                                    Cakupan: <b>{{ $libur->target_libur == 'semua' ? 'Seluruh Kelas' : 'Kelas ' . $namaKelasStr }}</b> |
                                    Waktu: <b>{{ $libur->tipe_agenda == 'Penuh' ? 'Seharian Full' : 'Parsial Jam Ke-'.$jamTeks }}</b>
                                </p>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        </details>
    </div>
    @endif

    <div class="bg-white rounded-2xl border border-gray-100 shadow-xs overflow-hidden">
        
        <div class="p-6 border-b border-gray-100 bg-gray-50/50 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h3 class="text-base font-bold text-gray-800">Rincian Performa Kehadiran Guru</h3>
                <p class="text-xs text-gray-500">Satuan Pendidikan Mu'adalah Wustha Maqna'ul Ulum</p>
            </div>
            <div class="flex items-center space-x-2">
                <button type="button" onclick="unduhPDF('{{ $tglMulai }}', '{{ $tglSelesai }}', this)" class="bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 px-3.5 py-2 rounded-xl text-xs font-bold transition shadow-2xs flex items-center space-x-1.5 cursor-pointer">
                    <i class="fas fa-print text-red-500"></i>
                    <span id="teks-tombol-pdf">Cetak PDF</span>
                </button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-gray-100/70 text-gray-600 uppercase tracking-wider font-bold border-b border-gray-200">
                        <th class="py-3.5 px-4 text-center w-12">No</th>
                        <th class="py-3.5 px-4">Nama Guru</th>
                        <th class="py-3.5 px-3 text-center hidden md:table-cell">Wajib</th>
                        <th class="py-3.5 px-3 text-center text-rose-600 hidden md:table-cell">A</th>
                        <th class="py-3.5 px-3 text-center text-blue-600 hidden md:table-cell">I</th>
                        <th class="py-3.5 px-3 text-center text-amber-600 hidden md:table-cell">S</th>
                        <th class="py-3.5 px-3 text-center text-purple-600 hidden md:table-cell">Piket</th>
                        <th class="py-3.5 px-4 text-center text-emerald-700 bg-emerald-50/50 hidden md:table-cell">Realita</th>
                        
                        <th class="py-3.5 px-4 text-center">% Realita</th>
                        <th class="py-3.5 px-4 text-center">Keterangan</th>
                        <th class="py-3.5 px-4 text-center w-20">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-gray-700 font-medium">
                    @foreach($rekapData as $index => $data)
                    <tr class="hover:bg-emerald-50/30 transition duration-150">
                        <td class="py-3.5 px-4 text-center font-bold text-gray-400">{{ $index + 1 }}</td>
                        
                        <td class="py-3.5 px-4 font-bold text-gray-800">
                            {{ $data->nama_guru }}
                        </td>
                        
                        <td class="py-3.5 px-3 text-center font-bold text-gray-800 hidden md:table-cell">{{ $data->jam_wajib }}</td>
                        <td class="py-3.5 px-3 text-center font-semibold text-rose-600 hidden md:table-cell">{{ $data->a > 0 ? $data->a : '-' }}</td>
                        <td class="py-3.5 px-3 text-center font-semibold text-blue-600 hidden md:table-cell">{{ $data->i > 0 ? $data->i : '-' }}</td>
                        <td class="py-3.5 px-3 text-center font-semibold text-amber-600 hidden md:table-cell">{{ $data->s > 0 ? $data->s : '-' }}</td>
                        <td class="py-3.5 px-3 text-center font-semibold text-purple-600 hidden md:table-cell">{{ $data->piket > 0 ? $data->piket : '-' }}</td>
                        <td class="py-3.5 px-4 text-center font-bold text-emerald-700 bg-emerald-50/30 hidden md:table-cell">{{ $data->realita }}</td>
                        
                        <td class="py-3.5 px-4 text-center">
                            <div class="flex items-center justify-center space-x-2">
                                <span class="font-bold text-xs">{{ $data->persen }}%</span>
                                <div class="w-12 bg-gray-100 rounded-full h-1.5 overflow-hidden hidden sm:block">
                                    <div class="bg-emerald-500 h-1.5 rounded-full" style="width: {{ $data->persen }}%"></div>
                                </div>
                            </div>
                        </td>

                        <td class="py-3.5 px-4 text-center">
                            <span class="px-2.5 py-1 rounded-full border text-[10px] font-bold inline-block shadow-2xs {{ $data->badge_bg }}">
                                {{ $data->ket }}
                            </span>
                        </td>
                        
                        <td class="py-3.5 px-4 text-center">
                            <div class="flex justify-center items-center">
                                <button type="button" onclick="bukaModalDetail({{ $data->guru_id }}, '{{ addslashes($data->nama_guru) }}', {{ $data->jam_wajib }}, {{ $data->a }}, {{ $data->i }}, {{ $data->s }}, {{ $data->piket }}, {{ $data->realita }})" class="bg-gray-50 text-gray-500 hover:bg-emerald-100 hover:text-emerald-700 border border-gray-200 p-2 rounded-lg transition shadow-sm cursor-pointer">
                                    <i class="fas fa-eye text-xs"></i>
                                </button>
                            </td>
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
<div id="modal-detail-laporan" class="hidden fixed inset-0 bg-gray-900 bg-opacity-60 overflow-y-auto h-full w-full z-50 flex items-center justify-center backdrop-blur-sm transition-opacity">
        <div class="relative mx-auto p-5 border w-11/12 max-w-sm shadow-2xl rounded-2xl bg-white transform transition-all">
            <div class="absolute top-0 right-0 pt-4 pr-4">
                <button onclick="tutupModalDetail()" class="text-gray-400 hover:text-gray-600 transition">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <div class="mt-2 text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-emerald-100 mb-3 shadow-inner">
                    <i class="fas fa-user-tie text-xl text-emerald-600"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-800 leading-tight mb-1" id="detail-nama-guru">Nama Guru</h3>
                <p class="text-xs text-gray-500 mb-5 border-b pb-4">Rincian Performa Kehadiran</p>
                
                <div class="grid grid-cols-3 gap-3 text-center mb-6">
                    <div class="bg-gray-50 p-3 rounded-xl border border-gray-100 shadow-sm">
                        <span class="block text-[10px] font-bold text-gray-400 uppercase">Wajib</span>
                        <span class="block text-xl font-black text-gray-800 mt-1" id="detail-wajib">0</span>
                    </div>
                    <div class="bg-emerald-50 p-3 rounded-xl border border-emerald-100 shadow-sm col-span-2">
                        <span class="block text-[10px] font-bold text-emerald-600 uppercase">Realita Hadir</span>
                        <span class="block text-2xl font-black text-emerald-700 mt-1" id="detail-realita">0</span>
                    </div>
                    
                    <div class="bg-rose-50 p-2.5 rounded-xl border border-rose-100 shadow-sm">
                        <span class="block text-[10px] font-bold text-rose-500 uppercase">Alpha</span>
                        <span class="block text-lg font-bold text-rose-600" id="detail-a">0</span>
                    </div>
                    <div class="bg-blue-50 p-2.5 rounded-xl border border-blue-100 shadow-sm">
                        <span class="block text-[10px] font-bold text-blue-500 uppercase">Izin</span>
                        <span class="block text-lg font-bold text-blue-600" id="detail-i">0</span>
                    </div>
                    <div class="bg-amber-50 p-2.5 rounded-xl border border-amber-100 shadow-sm">
                        <span class="block text-[10px] font-bold text-amber-500 uppercase">Sakit</span>
                        <span class="block text-lg font-bold text-amber-600" id="detail-s">0</span>
                    </div>
                </div>

                <div class="mt-2 text-left bg-gray-50 border border-gray-100 rounded-xl p-3 mb-4 max-h-56 overflow-y-auto shadow-inner">
                    <h4 class="text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-2 border-b pb-1">Riwayat Kelas (Bulan Ini)</h4>
                    <div id="wadah-riwayat" class="space-y-2">
                        <div class="text-center py-4 text-emerald-600">
                            <i class="fas fa-spinner fa-spin text-xl"></i>
                            <p class="text-xs mt-2 font-medium">Menarik data audit...</p>
                        </div>
                    </div>
                </div>

                <button onclick="tutupModalDetail()" class="w-full py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-xl shadow-sm transition duration-200 text-sm">
                    Tutup Detail
                </button>
            </div>
        </div>
    </div>

    <script>
    function bukaModalDetail(guruId, nama, wajib, a, i, s, piket, realita) {
        // Tampilkan Modal & Data Dasar
        document.getElementById('modal-detail-laporan').classList.remove('hidden');
        document.getElementById('detail-nama-guru').innerText = nama;
        document.getElementById('detail-wajib').innerText = wajib;
        document.getElementById('detail-realita').innerText = realita;
        document.getElementById('detail-a').innerText = a > 0 ? a : '-';
        document.getElementById('detail-i').innerText = i > 0 ? i : '-';
        document.getElementById('detail-s').innerText = s > 0 ? s : '-';

        // Tampilkan Loading
        let wadahRiwayat = document.getElementById('wadah-riwayat');
        wadahRiwayat.innerHTML = `<div class="text-center py-4 text-emerald-600"><i class="fas fa-spinner fa-spin text-xl"></i><p class="text-xs mt-2 font-medium">Menarik data audit...</p></div>`;

        // Ambil Tanggal Filter
        let tglMulai = document.getElementById('filter-tgl-mulai').value;
        let tglSelesai = document.getElementById('filter-tgl-selesai').value;

        // Tarik Data Riwayat dari Server
        fetch(`/laporan/riwayat-guru?guru_id=${guruId}&tgl_mulai=${tglMulai}&tgl_selesai=${tglSelesai}`)
        .then(response => response.json())
        .then(data => {
            if(data.length === 0) {
                wadahRiwayat.innerHTML = `<p class="text-center text-xs text-gray-400 py-3 font-semibold"><i class="fas fa-folder-open text-xl mb-1 block"></i>Tidak ada riwayat kehadiran.</p>`;
                return;
            }

            let htmlList = '';
            data.forEach(item => {
                // Skema Warna Status (Ditambah Piket)
                let wrnBg = 'bg-gray-100 text-gray-600';
                if(item.status === 'Hadir') wrnBg = 'bg-emerald-100 text-emerald-700';
                else if(item.status === 'Kosong' || item.status === 'Alpha' || item.status === 'Alpa') wrnBg = 'bg-rose-100 text-rose-700';
                else if(item.status === 'Izin') wrnBg = 'bg-blue-100 text-blue-700';
                else if(item.status === 'Sakit') wrnBg = 'bg-amber-100 text-amber-700';
                else if(item.status === 'Piket') wrnBg = 'bg-purple-100 text-purple-700';

                htmlList += `
                <div class="flex items-center justify-between p-2.5 bg-white border border-gray-100 rounded-lg shadow-sm hover:shadow transition mb-2">
                    <div>
                        <p class="text-[11px] font-extrabold text-gray-800">${item.tanggal_indo} <span class="text-gray-400 font-medium ml-1">Jam ke ${item.jam_tampil || item.jam_ke}</span></p>
                        <p class="text-[10px] text-gray-500 font-medium mt-0.5"><i class="fas fa-book text-gray-300 mr-1"></i> ${item.nama_pelajaran || 'Pelajaran (?)'} <span class="mx-1">•</span> Kls ${item.nama_kelas || '?'}</p>
                    </div>
                    <span class="px-2 py-1 rounded border text-[9px] font-extrabold ${wrnBg}">${item.status}</span>
                </div>`;
            });
            wadahRiwayat.innerHTML = htmlList;
        })
        .catch(error => {
            wadahRiwayat.innerHTML = `<p class="text-center text-xs text-rose-500 py-3 font-bold"><i class="fas fa-exclamation-triangle block text-lg mb-1"></i> Gagal memuat data jaringan.</p>`;
        });
    }

    function tutupModalDetail() {
        document.getElementById('modal-detail-laporan').classList.add('hidden');
    }

    // FITUR UNDUH PDF TANPA PINDAH TAB
    function unduhPDF(tglMulai, tglSelesai, btn) {
        let ikon = btn.querySelector('i');
        let teks = document.getElementById('teks-tombol-pdf');
        
        // Simpan gaya asli
        let kelasAsliIkon = ikon.className;
        let teksAsli = teks.innerText;
        
        // Ubah jadi Muter/Loading
        ikon.className = "fas fa-spinner fa-spin text-red-500";
        teks.innerText = "Memproses...";
        btn.disabled = true;
        btn.classList.add('opacity-75', 'cursor-not-allowed');

        // Melakukan Fetch (AJAX) untuk mengambil file PDF secara diam-diam
        fetch(`/laporan/cetak?tgl_mulai=${tglMulai}&tgl_selesai=${tglSelesai}`)
        .then(response => {
            if (!response.ok) throw new Error('Jaringan bermasalah');
            return response.blob();
        })
        .then(blob => {
            // Memicu jendela 'Save As / Unduh' secara otomatis
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.style.display = 'none';
            a.href = url;
            a.download = `Rekap_Kehadiran_${tglMulai}_hingga_${tglSelesai}.pdf`;
            document.body.appendChild(a);
            a.click();
            
            // Bersihkan memori dan kembalikan tombol
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);
            
            ikon.className = kelasAsliIkon;
            teks.innerText = teksAsli;
            btn.disabled = false;
            btn.classList.remove('opacity-75', 'cursor-not-allowed');
        })
        .catch(error => {
            alert('Gagal mencetak PDF. Silakan coba lagi.');
            ikon.className = kelasAsliIkon;
            teks.innerText = teksAsli;
            btn.disabled = false;
            btn.classList.remove('opacity-75', 'cursor-not-allowed');
        });
    }
    
</script>
@endsection