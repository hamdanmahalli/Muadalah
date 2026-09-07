@extends('layouts.app')

@section('title', 'Master Data Pengurus/Guru')

@section('content')

    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Master Data Pengurus/Guru</h1>
            <p class="text-sm text-gray-500 mt-1">Kelola data pengurus, guru, dan profil profesional Pondok Pesantren.</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a href="/master-jabatan" class="bg-violet-50 text-violet-700 border border-violet-200 px-4 py-2 rounded-xl text-sm font-bold hover:bg-violet-600 hover:text-white transition flex items-center shadow-sm">
                <i class="fas fa-briefcase mr-2"></i> Master Jabatan
            </a>
            <a href="/master-guru/export" class="bg-emerald-50 text-emerald-700 border border-emerald-200 px-4 py-2 rounded-xl text-sm font-bold hover:bg-emerald-600 hover:text-white transition flex items-center shadow-sm">
                <i class="fas fa-file-excel mr-2"></i> Export
            </a>
            <button onclick="bukaModalImport()" class="bg-blue-50 text-blue-700 border border-blue-200 px-4 py-2 rounded-xl text-sm font-bold hover:bg-blue-600 hover:text-white transition flex items-center shadow-sm">
                <i class="fas fa-file-upload mr-2"></i> Import
            </button>
            <button onclick="bukaModalTambah()" class="bg-green-600 text-white px-5 py-2 rounded-xl text-sm font-bold hover:bg-green-700 transition flex items-center shadow-md">
                <i class="fas fa-plus mr-2"></i> Tambah Pengurus/Guru
            </button>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-8">
        
        <div class="flex flex-col md:flex-row justify-between items-center p-5 border-b border-gray-100 gap-4 bg-gray-50/50">
            <div class="flex items-center text-sm text-gray-600 font-medium">
                <span class="mr-2">Tampilkan</span>
                <select id="select-per-page" onchange="doLiveSearch()" class="border border-gray-200 rounded-lg p-2 bg-white focus:ring-2 focus:ring-green-500 outline-none transition cursor-pointer shadow-sm text-gray-700 font-bold">
                    <option value="10" {{ ($perPage ?? 10) == 10 ? 'selected' : '' }}>10</option>
                    <option value="25" {{ ($perPage ?? 10) == 25 ? 'selected' : '' }}>25</option>
                    <option value="50" {{ ($perPage ?? 10) == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ ($perPage ?? 10) == 100 ? 'selected' : '' }}>100</option>
                </select>
                <span class="ml-2">data</span>
            </div>

            <form id="form-pencarian" onsubmit="event.preventDefault(); doLiveSearch();" class="w-full md:w-72 relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-search text-gray-400"></i>
                </div>
                <input type="text" 
                       id="input-search"
                       value="{{ $search ?? '' }}" 
                       placeholder="Ketik nama, NIG atau NIP..." 
                       autocomplete="off"
                       oninput="doLiveSearch()"
                       class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-green-500 outline-none transition bg-white shadow-sm font-medium text-gray-700">
            </form>
        </div>

        <div id="area-tabel-guru">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-white">
                        <tr>
                            <th class="px-5 py-4 text-left text-[11px] font-extrabold text-gray-400 uppercase tracking-widest w-12">No</th>
                            <th class="px-5 py-4 text-left text-[11px] font-extrabold text-gray-400 uppercase tracking-widest w-24">NIP</th>
                            <th class="px-5 py-4 text-left text-[11px] font-extrabold text-gray-400 uppercase tracking-widest w-24">NIG</th>
                            <th class="px-5 py-4 text-left text-[11px] font-extrabold text-gray-400 uppercase tracking-widest">Nama Pengurus/Guru</th>
                            <th class="px-5 py-4 text-left text-[11px] font-extrabold text-gray-400 uppercase tracking-widest">Jabatan</th>
                            <th class="px-5 py-4 text-left text-[11px] font-extrabold text-gray-400 uppercase tracking-widest">TTL / Gender</th>
                            <th class="px-5 py-4 text-left text-[11px] font-extrabold text-gray-400 uppercase tracking-widest">Kontak & Alamat</th>
                            <th class="px-5 py-4 text-left text-[11px] font-extrabold text-gray-400 uppercase tracking-widest">Pendidikan</th>
                            <th class="px-5 py-4 text-center text-[11px] font-extrabold text-gray-400 uppercase tracking-widest w-24">Status</th>
                            <th class="px-5 py-4 text-right text-[11px] font-extrabold text-gray-400 uppercase tracking-widest w-28">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 bg-white">
                        @forelse($gurus as $index => $guru)
                        <tr class="hover:bg-gray-50/50 transition duration-150">
                            <td class="px-5 py-4 whitespace-nowrap text-sm text-gray-400 font-semibold">{{ $gurus->firstItem() + $index }}</td>
                            <td class="px-5 py-4 whitespace-nowrap text-sm font-bold text-gray-700">{{ $guru->nip ?? '-' }}</td>
                            <td class="px-5 py-4 whitespace-nowrap text-sm font-bold text-gray-700">{{ $guru->nig }}</td>
                            <td class="px-5 py-4 whitespace-nowrap text-sm font-bold text-gray-900">{{ $guru->nama_guru }}</td>
                            <td class="px-5 py-4 text-sm">
                                @if($guru->jabatans->isNotEmpty())
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($guru->jabatans as $jb)
                                            <span class="px-2 py-0.5 inline-flex text-[10px] font-bold rounded-full bg-violet-100 text-violet-700">{{ $jb->nama_jabatan }}</span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap text-sm text-gray-600">
                                <div>{{ $guru->tempat_lahir ?? '-' }}, {{ $guru->tanggal_lahir ?? '-' }}</div>
                                <div class="text-xs text-gray-400 font-medium">{{ $guru->gender ?? '-' }}</div>
                            </td>
                            <td class="px-5 py-4 text-sm text-gray-600">
                                <div><i class="fab fa-whatsapp text-emerald-500 mr-1"></i> {{ $guru->no_hp ?? '-' }}</div>
                                <div class="text-xs text-gray-400 truncate max-w-xs">{{ $guru->alamat ?? '-' }}</div>
                            </td>
                            <td class="px-5 py-4 text-sm text-gray-600 font-medium">
                                {{ $guru->pendidikan_terakhir ?? '-' }}
                                @php
                                    $gKel = 0;
                                    if (!empty($guru->nik) && !empty($guru->kelurahan)) $gKel++;
                                    if (!empty($guru->nuptk) || !empty($guru->nrg) || !empty($guru->status_kepegawaian)) $gKel++;
                                    if (!empty($guru->npwp) || !empty($guru->no_rekening)) $gKel++;
                                    if (!empty($guru->jarak_km)) $gKel++;
                                @endphp
                                <div class="mt-1.5">
                                    <span class="text-[9px] font-bold px-1.5 py-0.5 rounded-full {{ $gKel == 4 ? 'bg-emerald-100 text-emerald-700' : ($gKel >= 2 ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-500') }}">
                                        Kelengkapan {{ $gKel }}/4
                                    </span>
                                </div>
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap text-center">
                                <span class="px-3 py-1 inline-flex text-[10px] uppercase tracking-wider font-bold rounded-full {{ ($guru->status == 'Nonaktif') ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }}">
                                    {{ $guru->status ?? 'Aktif' }}
                                </span>
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex justify-end space-x-2">
                                    <!-- Tombol Edit diperbarui dengan membawa data baru -->
                                    <button type="button" onclick="bukaModalKelengkapan('{{ $guru->id }}')" class="w-8 h-8 rounded-lg bg-gray-50 text-gray-500 hover:bg-sky-500 hover:text-white transition flex items-center justify-center border border-gray-100 shadow-sm" title="Kelengkapan Data (Pemerintah & Honor)">
                                        <i class="fas fa-user-shield text-[10px]"></i>
                                    </button>
                                    <button onclick="bukaModalEdit('{{ $guru->id }}', '{{ js_q($guru->nip) }}', '{{ js_q($guru->nig) }}', '{{ js_q($guru->nama_guru) }}', '{{ js_q($guru->no_hp) }}', '{{ js_q($guru->gender) }}', '{{ js_q($guru->alamat) }}', '{{ js_q($guru->status) }}', '{{ js_q($guru->tempat_lahir) }}', '{{ js_q($guru->tanggal_lahir) }}', '{{ js_q($guru->pendidikan_terakhir) }}', '{{ $guru->jabatans->pluck('id')->implode(',') }}')" class="w-8 h-8 rounded-lg bg-gray-50 text-gray-500 hover:bg-amber-500 hover:text-white transition flex items-center justify-center border border-gray-100 shadow-sm" title="Edit">
                                        <i class="fas fa-pen text-[10px]"></i>
                                    </button>
                                    
                                    <button type="button" onclick="konfirmasiHapus('{{ $guru->id }}', '{{ js_q($guru->nama_guru) }}')" class="w-8 h-8 rounded-lg bg-gray-50 text-gray-500 hover:bg-red-500 hover:text-white transition flex items-center justify-center border border-gray-100 shadow-sm" title="Hapus">
                                        <i class="fas fa-trash text-[10px]"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="px-6 py-16 text-center">
                                <div class="mx-auto w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-4">
                                    <i class="fas fa-user-slash text-2xl text-gray-400"></i>
                                </div>
                                <p class="text-gray-500 font-medium">Belum ada data pengurus yang cocok.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($gurus->hasPages())
            <div class="px-5 py-4 border-t border-gray-100 bg-gray-50/50">
                {{ $gurus->links() }}
            </div>
            @endif
        </div>
    </div>

    <!-- MODAL IMPORT -->
    <div id="modal-import" class="hidden fixed inset-0 bg-gray-900 bg-opacity-60 overflow-y-auto h-full w-full z-50 flex items-center justify-center backdrop-blur-sm">
        <div class="relative mx-auto p-6 border w-full max-w-sm shadow-2xl rounded-xl bg-white">
            <div class="flex justify-between items-center mb-4 border-b pb-3">
                <h3 class="text-lg font-bold text-gray-800">Import Data Pengurus/Guru</h3>
                <button type="button" onclick="tutupModalImport()" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times text-xl"></i></button>
            </div>
            <form action="/master-guru/import" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Pilih File Excel (.xlsx / .xls)</label>
                    <input type="file" name="file" required class="w-full border border-gray-300 rounded-lg p-2 text-sm bg-gray-50">
                </div>
                <div class="flex justify-end space-x-2 border-t pt-4">
                    <button type="button" onclick="tutupModalImport()" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg font-semibold text-sm">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg font-semibold text-sm hover:bg-blue-700">Unggah & Import</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL TAMBAH & EDIT GURU (DISINKRONKAN DENGAN PROFIL LENGKAP) -->
    <div id="modal-guru" class="hidden fixed inset-0 bg-gray-900 bg-opacity-60 overflow-y-auto h-full w-full z-50 flex items-center justify-center backdrop-blur-sm">
        <div class="relative mx-auto p-6 border w-full max-w-xl shadow-2xl rounded-xl bg-white">
            <div class="flex justify-between items-center mb-5 border-b pb-3">
                <h3 class="text-lg font-bold text-gray-800" id="modal-judul">Tambah Pengurus/Guru Baru</h3>
                <button type="button" onclick="tutupModal()" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times text-xl"></i></button>
            </div>
            
            <form id="form-guru" method="POST" action="/master-guru">
                @csrf
                <input type="hidden" name="_method" id="form-method" value="POST">
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">NIG (Otomatis)</label>
                        <input type="text" name="nig" id="input-nig" value="{{ $nigBaru }}" readonly class="w-full border border-gray-300 rounded-lg p-2 bg-gray-100 text-gray-500 font-bold outline-none cursor-not-allowed">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">NIP (Opsional)</label>
                        <input type="text" name="nip" id="input-nip" placeholder="Nomor Induk Pegawai" class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-green-500 outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Status Keaktifan</label>
                        <select name="status" id="input-status" class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-green-500 outline-none">
                            <option value="Aktif">Aktif</option>
                            <option value="Nonaktif">Nonaktif</option>
                        </select>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Jabatan (bisa lebih dari satu)</label>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 border border-gray-200 rounded-lg p-3 bg-gray-50">
                        @forelse($jabatans as $jb)
                        <label class="flex items-center text-sm text-gray-700 font-medium space-x-2 cursor-pointer hover:text-green-700">
                            <input type="checkbox" name="jabatan_ids[]" value="{{ $jb->id }}" class="jabatan-checkbox rounded border-gray-300 text-green-600 focus:ring-green-500">
                            <span>{{ $jb->nama_jabatan }}</span>
                        </label>
                        @empty
                        <p class="text-sm text-gray-400 col-span-full">Belum ada jabatan. Tambahkan dulu di menu <b>Master Jabatan</b>.</p>
                        @endforelse
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Nama Lengkap & Gelar</label>
                        <input type="text" name="nama_guru" id="input-nama" required class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-green-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Pendidikan Terakhir</label>
                        <input type="text" name="pendidikan_terakhir" id="input-pendidikan" placeholder="Contoh: S1 PAI" class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-green-500 outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Tempat Lahir</label>
                        <input type="text" name="tempat_lahir" id="input-tempat-lahir" class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-green-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Tanggal Lahir</label>
                        <input type="date" name="tanggal_lahir" id="input-tanggal-lahir" class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-green-500 outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">No. HP / WhatsApp</label>
                        <input type="text" name="no_hp" id="input-nohp" placeholder="08123xxxx" class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-green-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Jenis Kelamin</label>
                        <select name="gender" id="input-gender" class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-green-500 outline-none">
                            <option value="">-- Pilih --</option>
                            <option value="Laki-laki">Laki-laki</option>
                            <option value="Perempuan">Perempuan</option>
                        </select>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Alamat Domisili</label>
                    <textarea name="alamat" id="input-alamat" rows="2" class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-green-500 outline-none"></textarea>
                </div>

                <div class="flex justify-end space-x-2 border-t pt-4">
                    <button type="button" onclick="tutupModal()" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg font-semibold hover:bg-gray-300 transition">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg font-semibold hover:bg-green-700 transition shadow-md">Simpan Data</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL KELENGKAPAN DATA (PEMERINTAH & HONOR) -->
    <div id="modal-kelengkapan" class="hidden fixed inset-0 bg-gray-900 bg-opacity-60 overflow-y-auto h-full w-full z-50 flex items-center justify-center backdrop-blur-sm">
        <div class="relative mx-auto p-6 border w-full max-w-2xl shadow-2xl rounded-2xl bg-white max-h-[92vh] overflow-y-auto">
            <div class="flex justify-between items-center mb-5 border-b pb-3 sticky top-0 bg-white z-10">
                <div>
                    <h3 class="text-lg font-bold text-gray-800">Kelengkapan Data</h3>
                    <p class="text-xs text-gray-500 font-medium" id="kel-nama">-</p>
                </div>
                <button type="button" onclick="tutupModalKelengkapan()" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times text-xl"></i></button>
            </div>

            <form id="form-kelengkapan" method="POST">
                @csrf

                <div class="mb-5 bg-sky-50 border border-sky-200 rounded-xl p-4">
                    <h4 class="text-sm font-bold text-sky-800 mb-3 flex items-center"><i class="fas fa-car mr-2"></i> Data Honor & Transport</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Jarak Rumah ke Sekolah (km)</label>
                            <input type="number" step="0.1" min="0" max="9999.99" name="jarak_km" id="kel-jarak" placeholder="contoh: 5" class="w-full border border-gray-300 rounded-lg p-2 bg-white focus:ring-2 focus:ring-sky-500 outline-none">
                            <p class="text-[11px] text-sky-600 mt-1">Dipakai untuk transport honor: <b>jarak × tarif/km × hari masuk</b>.</p>
                        </div>
                    </div>
                </div>

                <div class="mb-5 bg-violet-50 border border-violet-200 rounded-xl p-4">
                    <h4 class="text-sm font-bold text-violet-800 mb-3 flex items-center"><i class="fas fa-id-card mr-2"></i> Identitas & Kewarganegaraan</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div><label class="block text-sm font-semibold text-gray-700 mb-1">NIK (KTP)</label><input type="text" name="nik" id="kel-nik" maxlength="16" class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-violet-500 outline-none"></div>
                        <div><label class="block text-sm font-semibold text-gray-700 mb-1">No. KK</label><input type="text" name="nik_kk" id="kel-nik-kk" maxlength="16" class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-violet-500 outline-none"></div>
                        <div><label class="block text-sm font-semibold text-gray-700 mb-1">Agama</label><input type="text" name="agama" id="kel-agama" class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-violet-500 outline-none"></div>
                        <div><label class="block text-sm font-semibold text-gray-700 mb-1">Kewarganegaraan</label><input type="text" name="kewarganegaraan" id="kel-kewarganegaraan" value="WNI" class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-violet-500 outline-none"></div>
                        <div><label class="block text-sm font-semibold text-gray-700 mb-1">RT</label><input type="text" name="rt" id="kel-rt" class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-violet-500 outline-none"></div>
                        <div><label class="block text-sm font-semibold text-gray-700 mb-1">RW</label><input type="text" name="rw" id="kel-rw" class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-violet-500 outline-none"></div>
                        <div><label class="block text-sm font-semibold text-gray-700 mb-1">Kelurahan/Desa</label><input type="text" name="kelurahan" id="kel-kelurahan" class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-violet-500 outline-none"></div>
                        <div><label class="block text-sm font-semibold text-gray-700 mb-1">Kecamatan</label><input type="text" name="kecamatan" id="kel-kecamatan" class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-violet-500 outline-none"></div>
                        <div><label class="block text-sm font-semibold text-gray-700 mb-1">Kabupaten/Kota</label><input type="text" name="kabupaten_kota" id="kel-kabupaten" class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-violet-500 outline-none"></div>
                        <div><label class="block text-sm font-semibold text-gray-700 mb-1">Kode Pos</label><input type="text" name="kode_pos" id="kel-kodepos" maxlength="5" class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-violet-500 outline-none"></div>
                    </div>
                </div>

                <div class="mb-5 bg-emerald-50 border border-emerald-200 rounded-xl p-4">
                    <h4 class="text-sm font-bold text-emerald-800 mb-3 flex items-center"><i class="fas fa-chalkboard-teacher mr-2"></i> Kepegawaian & Sertifikasi</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div><label class="block text-sm font-semibold text-gray-700 mb-1">Status Kepegawaian</label>
                            <select name="status_kepegawaian" id="kel-kepegawaian" class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-emerald-500 outline-none">
                                <option value="">-- Pilih --</option>
                                <option value="GTY / Yayasan">GTY / Yayasan</option>
                                <option value="Honorer">Honorer</option>
                                <option value="PPPK">PPPK</option>
                                <option value="PNS">PNS</option>
                                <option value="Lainnya">Lainnya</option>
                            </select>
                        </div>
                        <div><label class="block text-sm font-semibold text-gray-700 mb-1">Golongan/Ruang</label><input type="text" name="golongan_ruang" id="kel-golongan" placeholder="III/b" class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-emerald-500 outline-none"></div>
                        <div><label class="block text-sm font-semibold text-gray-700 mb-1">NUPTK</label><input type="text" name="nuptk" id="kel-nuptk" class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-emerald-500 outline-none"></div>
                        <div><label class="block text-sm font-semibold text-gray-700 mb-1">NRG</label><input type="text" name="nrg" id="kel-nrg" class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-emerald-500 outline-none"></div>
                        <div><label class="block text-sm font-semibold text-gray-700 mb-1">TMT Kerja</label><input type="date" name="tmt_kerja" id="kel-tmt" class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-emerald-500 outline-none"></div>
                        <div><label class="block text-sm font-semibold text-gray-700 mb-1">No. SK Pengangkatan</label><input type="text" name="no_sk_pengangkatan" id="kel-sk-pengangkatan" class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-emerald-500 outline-none"></div>
                        <div><label class="block text-sm font-semibold text-gray-700 mb-1">Tgl. SK Pengangkatan</label><input type="date" name="tgl_sk_pengangkatan" id="kel-tgl-sk" class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-emerald-500 outline-none"></div>
                        <div><label class="block text-sm font-semibold text-gray-700 mb-1">No. SK Pembagian Tugas</label><input type="text" name="no_sk_pembagian_tugas" id="kel-sk-tugas" class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-emerald-500 outline-none"></div>
                        <div><label class="block text-sm font-semibold text-gray-700 mb-1">Program Studi</label><input type="text" name="program_studi" id="kel-prodi" class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-emerald-500 outline-none"></div>
                        <div><label class="block text-sm font-semibold text-gray-700 mb-1">Perguruan Tinggi</label><input type="text" name="perguruan_tinggi" id="kel-pt" class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-emerald-500 outline-none"></div>
                        <div><label class="block text-sm font-semibold text-gray-700 mb-1">Tahun Lulus</label><input type="text" name="tahun_lulus" id="kel-tahun-lulus" maxlength="4" class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-emerald-500 outline-none"></div>
                        <div class="flex items-end">
                            <label class="inline-flex items-center text-sm font-semibold text-gray-700 cursor-pointer">
                                <input type="checkbox" name="status_sertifikasi" id="kel-sertifikasi" value="1" class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 mr-2">
                                Sudah Sertifikasi
                            </label>
                        </div>
                        <div><label class="block text-sm font-semibold text-gray-700 mb-1">No. Sertifikat Pendidik</label><input type="text" name="no_sertifikat_pendidik" id="kel-no-sertifikat" class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-emerald-500 outline-none"></div>
                        <div><label class="block text-sm font-semibold text-gray-700 mb-1">Tahun Sertifikasi</label><input type="text" name="tahun_sertifikasi" id="kel-tahun-sertifikasi" maxlength="4" class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-emerald-500 outline-none"></div>
                    </div>
                </div>

                <div class="mb-5 bg-amber-50 border border-amber-200 rounded-xl p-4">
                    <h4 class="text-sm font-bold text-amber-800 mb-3 flex items-center"><i class="fas fa-money-bill-wave mr-2"></i> Pembayaran Honor & Tunjangan</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div><label class="block text-sm font-semibold text-gray-700 mb-1">NPWP</label><input type="text" name="npwp" id="kel-npwp" placeholder="00.000.000.0-000.000" class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-amber-500 outline-none"></div>
                        <div><label class="block text-sm font-semibold text-gray-700 mb-1">Nama Bank</label><input type="text" name="nama_bank" id="kel-bank" class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-amber-500 outline-none"></div>
                        <div><label class="block text-sm font-semibold text-gray-700 mb-1">No. Rekening</label><input type="text" name="no_rekening" id="kel-rekening" class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-amber-500 outline-none"></div>
                        <div><label class="block text-sm font-semibold text-gray-700 mb-1">Atas Nama Rekening</label><input type="text" name="atas_nama_rekening" id="kel-atasnama" class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-amber-500 outline-none"></div>
                        <div><label class="block text-sm font-semibold text-gray-700 mb-1">No. BPJS Ketenagakerjaan</label><input type="text" name="bpjs_ketenagakerjaan" id="kel-bpjs-kt" class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-amber-500 outline-none"></div>
                        <div><label class="block text-sm font-semibold text-gray-700 mb-1">No. BPJS Kesehatan</label><input type="text" name="bpjs_kesehatan" id="kel-bpjs-ks" class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-amber-500 outline-none"></div>
                    </div>
                </div>

                <div class="mb-5 bg-rose-50 border border-rose-200 rounded-xl p-4">
                    <h4 class="text-sm font-bold text-rose-800 mb-3 flex items-center"><i class="fas fa-house-user mr-2"></i> Data Keluarga</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div><label class="block text-sm font-semibold text-gray-700 mb-1">Status Pernikahan</label>
                            <select name="status_menikah" id="kel-menikah" class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-rose-500 outline-none">
                                <option value="">-- Pilih --</option>
                                <option value="Belum Menikah">Belum Menikah</option>
                                <option value="Menikah">Menikah</option>
                                <option value="Duda">Duda</option>
                                <option value="Janda">Janda</option>
                            </select>
                        </div>
                        <div><label class="block text-sm font-semibold text-gray-700 mb-1">Nama Pasangan</label><input type="text" name="nama_pasangan" id="kel-pasangan" class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-rose-500 outline-none"></div>
                        <div><label class="block text-sm font-semibold text-gray-700 mb-1">Jumlah Anak</label><input type="number" name="jumlah_anak" id="kel-anak" min="0" max="99" class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-rose-500 outline-none"></div>
                    </div>
                </div>

                <div class="flex justify-end space-x-2 border-t pt-4 sticky bottom-0 bg-white pb-1">
                    <button type="button" onclick="tutupModalKelengkapan()" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg font-semibold hover:bg-gray-300 transition">Tutup</button>
                    <button type="submit" class="px-5 py-2 bg-sky-600 text-white rounded-lg font-semibold hover:bg-sky-700 transition shadow-md">Simpan Kelengkapan</button>
                </div>
            </form>

            {{-- Upload & Daftar Dokumen --}}
            <div class="mt-6 border-t pt-5">
                <h4 class="text-sm font-bold text-gray-800 mb-3 flex items-center"><i class="fas fa-folder-open text-gray-500 mr-2"></i> Dokumen Kelengkapan</h4>

                <form id="form-upload-dokumen" method="POST" enctype="multipart/form-data" class="mb-4 bg-gray-50 border border-gray-200 rounded-xl p-4">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Jenis Dokumen</label>
                            <select name="jenis" class="w-full border border-gray-300 rounded-lg p-2 bg-white text-sm focus:ring-2 focus:ring-sky-500 outline-none">
                                <option value="KTP">KTP</option>
                                <option value="KK">KK</option>
                                <option value="Ijazah">Ijazah</option>
                                <option value="Sertifikat Pendidik">Sertifikat Pendidik</option>
                                <option value="SK Pengangkatan">SK Pengangkatan</option>
                                <option value="SK Pembagian Tugas">SK Pembagian Tugas</option>
                                <option value="NPWP">NPWP</option>
                                <option value="Akta Kelahiran">Akta Kelahiran</option>
                                <option value="Pas Foto">Pas Foto</option>
                                <option value="Lainnya">Lainnya</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">File (PDF/JPG/PNG, max 4MB)</label>
                            <input type="file" name="file" accept=".pdf,.jpg,.jpeg,.png" required class="w-full border border-gray-300 rounded-lg p-2 bg-white text-sm focus:ring-2 focus:ring-sky-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Keterangan (opsional)</label>
                            <input type="text" name="keterangan" placeholder="contoh: scan KTP" class="w-full border border-gray-300 rounded-lg p-2 bg-white text-sm focus:ring-2 focus:ring-sky-500 outline-none">
                        </div>
                    </div>
                    <div class="flex justify-end mt-3">
                        <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded-lg font-semibold text-sm hover:bg-emerald-700 transition shadow-md"><i class="fas fa-upload mr-1"></i> Unggah</button>
                    </div>
                </form>

                <div id="area-dokumen" class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="text-sm text-gray-400">Pilih guru untuk melihat dokumen.</div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL HAPUS -->
    <div id="modal-hapus" class="hidden fixed inset-0 bg-gray-900 bg-opacity-60 overflow-y-auto h-full w-full z-50 flex items-center justify-center backdrop-blur-sm">
        <div class="relative mx-auto p-6 border w-full max-w-sm shadow-2xl rounded-2xl bg-white text-center">
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100 mb-4 shadow-inner">
                <i class="fas fa-trash-alt text-2xl text-red-600"></i>
            </div>
            <h3 class="text-xl font-extrabold text-gray-800 mb-2">Hapus Data Pengurus/Guru?</h3>
            <p class="text-sm text-gray-600 mb-6">Yakin ingin menghapus <b id="teks-nama-hapus" class="text-gray-900"></b>? Data ini akan dihapus secara permanen dari sistem.</p>
            
            <div class="flex justify-center space-x-3">
                <button type="button" onclick="tutupModalHapus()" class="px-5 py-2.5 bg-gray-200 text-gray-800 rounded-xl font-semibold hover:bg-gray-300 transition">Batal</button>
                <button type="button" onclick="eksekusiHapus()" class="px-5 py-2.5 bg-red-600 text-white rounded-xl font-semibold hover:bg-red-700 transition shadow-md">Ya, Hapus Data</button>
            </div>
            
            <form id="form-hapus-global" method="POST" class="hidden">
                @csrf @method('DELETE')
            </form>
        </div>
    </div>

    <script>
        // 1. SKRIP PENCARIAN AJAX LIVE SEARCH
        let searchTimer;
        function doLiveSearch() {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(() => {
                const query = document.getElementById('input-search').value;
                const perPage = document.getElementById('select-per-page').value;
                const url = `/master-guru?search=${encodeURIComponent(query)}&per_page=${perPage}`;

                fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(response => response.text())
                .then(html => {
                    const doc = new DOMParser().parseFromString(html, 'text/html');
                    const newTableArea = doc.getElementById('area-tabel-guru');
                    if (newTableArea) {
                        document.getElementById('area-tabel-guru').innerHTML = newTableArea.innerHTML;
                    }
                    window.history.pushState({}, '', url);
                }).catch(error => console.error('Error Live Search:', error));
            }, 300);
        }

        // 2. SKRIP KENDALI MODAL IMPORT
        function bukaModalImport() {
            document.getElementById('modal-import').classList.remove('hidden');
        }
        function tutupModalImport() {
            document.getElementById('modal-import').classList.add('hidden');
        }

        // 3. SKRIP KENDALI MODAL TAMBAH & EDIT GURU
        function bukaModalTambah() {
            document.getElementById('modal-guru').classList.remove('hidden');
            document.getElementById('modal-judul').innerText = "Tambah Pengurus/Guru Baru";
            document.getElementById('form-guru').action = "/master-guru";
            document.getElementById('form-method').value = "POST";
            
            // Reset isi form
            document.getElementById('form-guru').reset();
            document.getElementById('input-nig').value = "{{ $nigBaru }}";
            document.getElementById('input-status').value = "Aktif";
        }

        function bukaModalEdit(id, nip, nig, nama, hp, gender, alamat, status, tempatLahir, tanggalLahir, pendidikan, jabatanIds) {
            document.getElementById('modal-guru').classList.remove('hidden');
            document.getElementById('modal-judul').innerText = "Edit Data Pengurus/Guru";
            document.getElementById('form-guru').action = "/master-guru/" + id;
            document.getElementById('form-method').value = "PUT";
            
            // Isi form dengan data lama (Sinkron dengan Profil Lengkap)
            document.getElementById('input-nip').value = nip ? nip : '';
            document.getElementById('input-nig').value = nig;
            document.getElementById('input-nama').value = nama;
            document.getElementById('input-nohp').value = hp;
            document.getElementById('input-gender').value = gender;
            document.getElementById('input-alamat').value = alamat;
            document.getElementById('input-status').value = status;
            document.getElementById('input-tempat-lahir').value = tempatLahir;
            document.getElementById('input-tanggal-lahir').value = tanggalLahir;
            document.getElementById('input-pendidikan').value = pendidikan;

            // Setel checkbox jabatan yang aktif
            const list = (jabatanIds || '').split(',').filter(v => v);
            document.querySelectorAll('.jabatan-checkbox').forEach(cb => {
                cb.checked = list.includes(cb.value);
            });
        }

        function tutupModal() {
            document.getElementById('modal-guru').classList.add('hidden');
        }

        // 4. SKRIP KENDALI MODAL HAPUS ELEGAN
        function konfirmasiHapus(id, namaGuru) {
            document.getElementById('modal-hapus').classList.remove('hidden');
            document.getElementById('teks-nama-hapus').innerText = namaGuru;
            document.getElementById('form-hapus-global').action = "/master-guru/" + id;
        }

        function tutupModalHapus() {
            document.getElementById('modal-hapus').classList.add('hidden');
        }

        function eksekusiHapus() {
            document.getElementById('form-hapus-global').submit();
        }

        // 5. SKRIP KENDALI MODAL KELENGKAPAN DATA (PEMERINTAH & HONOR)
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        const kelFieldMap = {
            'kel-jarak': 'jarak_km',
            'kel-nik': 'nik',
            'kel-nik-kk': 'nik_kk',
            'kel-agama': 'agama',
            'kel-kewarganegaraan': 'kewarganegaraan',
            'kel-rt': 'rt',
            'kel-rw': 'rw',
            'kel-kelurahan': 'kelurahan',
            'kel-kecamatan': 'kecamatan',
            'kel-kabupaten': 'kabupaten_kota',
            'kel-kodepos': 'kode_pos',
            'kel-nuptk': 'nuptk',
            'kel-nrg': 'nrg',
            'kel-kepegawaian': 'status_kepegawaian',
            'kel-golongan': 'golongan_ruang',
            'kel-tmt': 'tmt_kerja',
            'kel-sk-pengangkatan': 'no_sk_pengangkatan',
            'kel-tgl-sk': 'tgl_sk_pengangkatan',
            'kel-sk-tugas': 'no_sk_pembagian_tugas',
            'kel-prodi': 'program_studi',
            'kel-pt': 'perguruan_tinggi',
            'kel-tahun-lulus': 'tahun_lulus',
            'kel-no-sertifikat': 'no_sertifikat_pendidik',
            'kel-tahun-sertifikasi': 'tahun_sertifikasi',
            'kel-npwp': 'npwp',
            'kel-bank': 'nama_bank',
            'kel-rekening': 'no_rekening',
            'kel-atasnama': 'atas_nama_rekening',
            'kel-bpjs-kt': 'bpjs_ketenagakerjaan',
            'kel-bpjs-ks': 'bpjs_kesehatan',
            'kel-menikah': 'status_menikah',
            'kel-pasangan': 'nama_pasangan',
            'kel-anak': 'jumlah_anak',
        };

        function bukaModalKelengkapan(id) {
            document.getElementById('modal-kelengkapan').classList.remove('hidden');
            document.getElementById('form-kelengkapan').action = '/master-guru/' + id + '/kelengkapan';
            document.getElementById('form-upload-dokumen').action = '/master-guru/' + id + '/dokumen';
            document.getElementById('kel-nama').innerText = 'Memuat...';
            document.getElementById('area-dokumen').innerHTML = '<div class="text-sm text-gray-400">Memuat dokumen...</div>';

            // Reset form
            document.getElementById('form-kelengkapan').reset();
            document.getElementById('kel-kewarganegaraan').value = 'WNI';
            document.getElementById('kel-sertifikasi').checked = false;

            fetch('/master-guru/' + id + '/detail')
                .then(r => r.json())
                .then(guru => {
                    document.getElementById('kel-nama').innerText = guru.nama_guru + ' (' + guru.nig + ')';
                    Object.entries(kelFieldMap).forEach(([elId, key]) => {
                        const el = document.getElementById(elId);
                        if (!el) return;
                        let v = guru[key];
                        if (v === null || v === undefined) v = '';
                        el.value = v;
                    });
                    document.getElementById('kel-sertifikasi').checked = !!guru.status_sertifikasi;
                    renderDokumen(guru.dokumens || []);
                })
                .catch(() => {
                    document.getElementById('kel-nama').innerText = 'Gagal memuat data.';
                    document.getElementById('area-dokumen').innerHTML = '<div class="text-sm text-rose-500">Gagal memuat daftar dokumen.</div>';
                });
        }

        function tutupModalKelengkapan() {
            document.getElementById('modal-kelengkapan').classList.add('hidden');
        }

        function renderDokumen(items) {
            const area = document.getElementById('area-dokumen');
            if (!items.length) {
                area.innerHTML = '<div class="text-sm text-gray-400">Belum ada dokumen untuk guru ini.</div>';
                return;
            }
            let html = '';
            items.forEach(d => {
                html += `
                    <div class="flex items-center justify-between gap-2 bg-white border border-gray-200 rounded-lg px-3 py-2.5 shadow-sm">
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <i class="fas fa-file-pdf text-rose-500"></i>
                                <span class="text-xs font-bold text-gray-700 truncate">${d.jenis}</span>
                            </div>
                            <div class="text-[11px] text-gray-400 truncate">${d.nama_asli || d.file_path}</div>
                        </div>
                        <div class="flex items-center gap-1.5 shrink-0">
                            <a href="${d.url}" target="_blank" class="w-7 h-7 rounded-lg bg-gray-50 text-gray-500 hover:bg-sky-500 hover:text-white transition flex items-center justify-center border border-gray-100" title="Lihat">
                                <i class="fas fa-eye text-[9px]"></i>
                            </a>
                            <form method="POST" action="/master-guru-dokumen/${d.id}/hapus" onsubmit="return confirm('Hapus dokumen ini?')">
                                <input type="hidden" name="_token" value="${csrfToken}">
                                <button type="submit" class="w-7 h-7 rounded-lg bg-gray-50 text-gray-500 hover:bg-red-500 hover:text-white transition flex items-center justify-center border border-gray-100" title="Hapus">
                                    <i class="fas fa-trash text-[9px]"></i>
                                </button>
                            </form>
                        </div>
                    </div>`;
            });
            area.innerHTML = html;
        }
    </script>
@endsection