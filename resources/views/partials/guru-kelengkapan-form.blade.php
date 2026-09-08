{{-- Bagian formulir gabungan: Data Dasar Master Guru + Kelengkapan Data + Dokumen.
     Variabel yang dibutuhkan: $guru, $editable, $isAdmin, $jabatans (opsional), $guruPage,
     $bolehDokumen (boleh unggah/hapus dokumen). --}}
@php
    $kelClass = $editable
        ? 'w-full border border-gray-300 rounded-lg p-2 bg-white focus:ring-2 focus:ring-sky-500 outline-none'
        : 'w-full border border-gray-200 rounded-lg p-2 bg-gray-100 text-gray-600 outline-none cursor-not-allowed';
    $aktifAttr = $editable ? '' : 'disabled';
    $jabatanAktif = ($guru->jabatans ?? collect())->pluck('id')->all();
@endphp

{{-- 1. DATA DASAR MASTER GURU --}}
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 mb-4">
    <h4 class="text-sm font-bold text-gray-800 mb-3 flex items-center"><i class="fas fa-file-signature text-sky-600 mr-2"></i> Data Dasar</h4>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Nama Lengkap &amp; Gelar</label>
            <input type="text" name="nama_guru" value="{{ old('nama_guru', $guru->nama_guru) }}" required {{ $aktifAttr }} class="{{ $kelClass }}">
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">NIP</label>
            <input type="text" name="nip" value="{{ old('nip', $guru->nip) }}" {{ $aktifAttr }} class="{{ $kelClass }}">
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">NIG</label>
            <input type="text" value="{{ $guru->nig }}" disabled class="{{ $kelClass }}">
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Tempat Lahir</label>
            <input type="text" name="tempat_lahir" value="{{ old('tempat_lahir', $guru->tempat_lahir) }}" {{ $aktifAttr }} class="{{ $kelClass }}">
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Tanggal Lahir</label>
            <input type="date" name="tanggal_lahir" value="{{ old('tanggal_lahir', $guru->tanggal_lahir ? \Illuminate\Support\Carbon::parse($guru->tanggal_lahir)->format('Y-m-d') : '') }}" {{ $aktifAttr }} class="{{ $kelClass }}">
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Jenis Kelamin</label>
            <select name="gender" {{ $aktifAttr }} class="{{ $kelClass }}">
                <option value="">-- Pilih --</option>
                <option value="Laki-laki" {{ old('gender', $guru->gender) == 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                <option value="Perempuan" {{ old('gender', $guru->gender) == 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">No. HP / WhatsApp</label>
            <input type="text" name="no_hp" value="{{ old('no_hp', $guru->no_hp) }}" {{ $aktifAttr }} class="{{ $kelClass }}">
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Pendidikan Terakhir</label>
            <input type="text" name="pendidikan_terakhir" value="{{ old('pendidikan_terakhir', $guru->pendidikan_terakhir) }}" {{ $aktifAttr }} class="{{ $kelClass }}">
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Status Keaktifan</label>
            @if($isAdmin)
                <select name="status" {{ $aktifAttr }} class="{{ $kelClass }}">
                    <option value="Aktif" {{ old('status', $guru->status) == 'Aktif' ? 'selected' : '' }}>Aktif</option>
                    <option value="Nonaktif" {{ old('status', $guru->status) == 'Nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                </select>
            @else
                <span class="inline-flex items-center px-3 py-2 rounded-lg text-sm font-semibold {{ $guru->status == 'Aktif' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                    {{ $guru->status ?? 'Aktif' }}
                </span>
            @endif
        </div>
        <div class="sm:col-span-2">
            <label class="block text-sm font-semibold text-gray-700 mb-1">Alamat Domisili</label>
            <textarea name="alamat" rows="2" {{ $aktifAttr }} class="{{ $kelClass }} resize-none">{{ old('alamat', $guru->alamat) }}</textarea>
        </div>
        @if($isAdmin && isset($jabatans) && $jabatans->isNotEmpty())
            <div class="sm:col-span-3">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Jabatan</label>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                    @foreach($jabatans as $jabatan)
                        <label class="inline-flex items-center text-sm text-gray-700 cursor-pointer {{ $editable ? '' : 'opacity-60' }}">
                            <input type="checkbox" name="jabatan_ids[]" value="{{ $jabatan->id }}" {{ in_array($jabatan->id, $jabatanAktif) ? 'checked' : '' }} {{ $aktifAttr }} class="rounded border-gray-300 text-sky-600 focus:ring-sky-500 mr-2">
                            {{ $jabatan->nama_jabatan }}
                        </label>
                    @endforeach
                </div>
            </div>
        @elseif(!$isAdmin && ($guru->jabatans ?? collect())->isNotEmpty())
            <div class="sm:col-span-3">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Jabatan</label>
                <div class="flex flex-wrap gap-2">
                    @foreach($guru->jabatans as $jabatan)
                        <span class="inline-flex items-center px-3 py-1.5 rounded-full bg-sky-50 text-sky-700 border border-sky-200 text-xs font-semibold">
                            <i class="fas fa-briefcase text-[10px] mr-1.5"></i> {{ $jabatan->nama_jabatan }}
                        </span>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>

{{-- 2. IDENTITAS & KEWARGANEGARAAN (termasuk jarak rumah ke sekolah) --}}
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 mb-4">
    <h4 class="text-sm font-bold text-gray-800 mb-3 flex items-center"><i class="fas fa-id-card text-violet-600 mr-2"></i> Identitas &amp; Kewarganegaraan</h4>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div><label class="block text-sm font-semibold text-gray-700 mb-1">NIK (KTP)</label><input type="text" name="nik" value="{{ old('nik', $guru->nik) }}" maxlength="16" {{ $aktifAttr }} class="{{ $kelClass }}"></div>
        <div><label class="block text-sm font-semibold text-gray-700 mb-1">No. KK</label><input type="text" name="nik_kk" value="{{ old('nik_kk', $guru->nik_kk) }}" maxlength="16" {{ $aktifAttr }} class="{{ $kelClass }}"></div>
        <div><label class="block text-sm font-semibold text-gray-700 mb-1">Agama</label><input type="text" name="agama" value="{{ old('agama', $guru->agama) }}" {{ $aktifAttr }} class="{{ $kelClass }}"></div>
        <div><label class="block text-sm font-semibold text-gray-700 mb-1">Kewarganegaraan</label><input type="text" name="kewarganegaraan" value="{{ old('kewarganegaraan', $guru->kewarganegaraan ?? 'WNI') }}" {{ $aktifAttr }} class="{{ $kelClass }}"></div>
        <div><label class="block text-sm font-semibold text-gray-700 mb-1">RT</label><input type="text" name="rt" value="{{ old('rt', $guru->rt) }}" {{ $aktifAttr }} class="{{ $kelClass }}"></div>
        <div><label class="block text-sm font-semibold text-gray-700 mb-1">RW</label><input type="text" name="rw" value="{{ old('rw', $guru->rw) }}" {{ $aktifAttr }} class="{{ $kelClass }}"></div>
        <div><label class="block text-sm font-semibold text-gray-700 mb-1">Kelurahan/Desa</label><input type="text" name="kelurahan" value="{{ old('kelurahan', $guru->kelurahan) }}" {{ $aktifAttr }} class="{{ $kelClass }}"></div>
        <div><label class="block text-sm font-semibold text-gray-700 mb-1">Kecamatan</label><input type="text" name="kecamatan" value="{{ old('kecamatan', $guru->kecamatan) }}" {{ $aktifAttr }} class="{{ $kelClass }}"></div>
        <div><label class="block text-sm font-semibold text-gray-700 mb-1">Kabupaten/Kota</label><input type="text" name="kabupaten_kota" value="{{ old('kabupaten_kota', $guru->kabupaten_kota) }}" {{ $aktifAttr }} class="{{ $kelClass }}"></div>
        <div><label class="block text-sm font-semibold text-gray-700 mb-1">Kode Pos</label><input type="text" name="kode_pos" value="{{ old('kode_pos', $guru->kode_pos) }}" maxlength="5" {{ $aktifAttr }} class="{{ $kelClass }}"></div>
        <div><label class="block text-sm font-semibold text-gray-700 mb-1">Jarak Rumah ke Sekolah (km)</label><input type="number" step="0.1" min="0" max="9999.99" name="jarak_km" value="{{ old('jarak_km', $guru->jarak_km) }}" placeholder="contoh: 5" {{ $aktifAttr }} class="{{ $kelClass }}"></div>
    </div>
</div>

{{-- 3. KEPEGAWAIAN & SERTIFIKASI --}}
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 mb-4">
    <h4 class="text-sm font-bold text-gray-800 mb-3 flex items-center"><i class="fas fa-chalkboard-teacher text-emerald-600 mr-2"></i> Kepegawaian &amp; Sertifikasi</h4>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Status Kepegawaian</label>
            <select name="status_kepegawaian" {{ $aktifAttr }} class="{{ $kelClass }}">
                <option value="">-- Pilih --</option>
                @foreach(['GTY / Yayasan', 'Honorer', 'PPPK', 'PNS', 'Lainnya'] as $opt)
                    <option value="{{ $opt }}" {{ old('status_kepegawaian', $guru->status_kepegawaian) == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                @endforeach
            </select>
        </div>
        <div><label class="block text-sm font-semibold text-gray-700 mb-1">Golongan/Ruang</label><input type="text" name="golongan_ruang" value="{{ old('golongan_ruang', $guru->golongan_ruang) }}" placeholder="III/b" {{ $aktifAttr }} class="{{ $kelClass }}"></div>
        <div><label class="block text-sm font-semibold text-gray-700 mb-1">NUPTK</label><input type="text" name="nuptk" value="{{ old('nuptk', $guru->nuptk) }}" {{ $aktifAttr }} class="{{ $kelClass }}"></div>
        <div><label class="block text-sm font-semibold text-gray-700 mb-1">NRG</label><input type="text" name="nrg" value="{{ old('nrg', $guru->nrg) }}" {{ $aktifAttr }} class="{{ $kelClass }}"></div>
        <div><label class="block text-sm font-semibold text-gray-700 mb-1">TMT Kerja</label><input type="date" name="tmt_kerja" value="{{ old('tmt_kerja', $guru->tmt_kerja ? \Illuminate\Support\Carbon::parse($guru->tmt_kerja)->format('Y-m-d') : '') }}" {{ $aktifAttr }} class="{{ $kelClass }}"></div>
        <div><label class="block text-sm font-semibold text-gray-700 mb-1">No. SK Pengangkatan</label><input type="text" name="no_sk_pengangkatan" value="{{ old('no_sk_pengangkatan', $guru->no_sk_pengangkatan) }}" {{ $aktifAttr }} class="{{ $kelClass }}"></div>
        <div><label class="block text-sm font-semibold text-gray-700 mb-1">Tgl. SK Pengangkatan</label><input type="date" name="tgl_sk_pengangkatan" value="{{ old('tgl_sk_pengangkatan', $guru->tgl_sk_pengangkatan ? \Illuminate\Support\Carbon::parse($guru->tgl_sk_pengangkatan)->format('Y-m-d') : '') }}" {{ $aktifAttr }} class="{{ $kelClass }}"></div>
        <div><label class="block text-sm font-semibold text-gray-700 mb-1">No. SK Pembagian Tugas</label><input type="text" name="no_sk_pembagian_tugas" value="{{ old('no_sk_pembagian_tugas', $guru->no_sk_pembagian_tugas) }}" {{ $aktifAttr }} class="{{ $kelClass }}"></div>
        <div><label class="block text-sm font-semibold text-gray-700 mb-1">Program Studi</label><input type="text" name="program_studi" value="{{ old('program_studi', $guru->program_studi) }}" {{ $aktifAttr }} class="{{ $kelClass }}"></div>
        <div><label class="block text-sm font-semibold text-gray-700 mb-1">Perguruan Tinggi</label><input type="text" name="perguruan_tinggi" value="{{ old('perguruan_tinggi', $guru->perguruan_tinggi) }}" {{ $aktifAttr }} class="{{ $kelClass }}"></div>
        <div><label class="block text-sm font-semibold text-gray-700 mb-1">Tahun Lulus</label><input type="text" name="tahun_lulus" value="{{ old('tahun_lulus', $guru->tahun_lulus) }}" maxlength="4" {{ $aktifAttr }} class="{{ $kelClass }}"></div>
        <div class="flex items-end">
            <label class="inline-flex items-center text-sm font-semibold text-gray-700 cursor-pointer {{ $editable ? '' : 'opacity-60' }}">
                <input type="checkbox" name="status_sertifikasi" value="1" {{ old('status_sertifikasi', $guru->status_sertifikasi) ? 'checked' : '' }} {{ $aktifAttr }} class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 mr-2">
                Sudah Sertifikasi
            </label>
        </div>
        <div><label class="block text-sm font-semibold text-gray-700 mb-1">No. Sertifikat Pendidik</label><input type="text" name="no_sertifikat_pendidik" value="{{ old('no_sertifikat_pendidik', $guru->no_sertifikat_pendidik) }}" {{ $aktifAttr }} class="{{ $kelClass }}"></div>
        <div><label class="block text-sm font-semibold text-gray-700 mb-1">Tahun Sertifikasi</label><input type="text" name="tahun_sertifikasi" value="{{ old('tahun_sertifikasi', $guru->tahun_sertifikasi) }}" maxlength="4" {{ $aktifAttr }} class="{{ $kelClass }}"></div>
    </div>
</div>

{{-- 4. PEMBAYARAN HONOR & TUNJANGAN --}}
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 mb-4">
    <h4 class="text-sm font-bold text-gray-800 mb-3 flex items-center"><i class="fas fa-money-bill-wave text-amber-600 mr-2"></i> Pembayaran Honor &amp; Tunjangan</h4>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div><label class="block text-sm font-semibold text-gray-700 mb-1">NPWP</label><input type="text" name="npwp" value="{{ old('npwp', $guru->npwp) }}" placeholder="00.000.000.0-000.000" {{ $aktifAttr }} class="{{ $kelClass }}"></div>
        <div><label class="block text-sm font-semibold text-gray-700 mb-1">Nama Bank</label><input type="text" name="nama_bank" value="{{ old('nama_bank', $guru->nama_bank) }}" {{ $aktifAttr }} class="{{ $kelClass }}"></div>
        <div><label class="block text-sm font-semibold text-gray-700 mb-1">No. Rekening</label><input type="text" name="no_rekening" value="{{ old('no_rekening', $guru->no_rekening) }}" {{ $aktifAttr }} class="{{ $kelClass }}"></div>
        <div><label class="block text-sm font-semibold text-gray-700 mb-1">Atas Nama Rekening</label><input type="text" name="atas_nama_rekening" value="{{ old('atas_nama_rekening', $guru->atas_nama_rekening) }}" {{ $aktifAttr }} class="{{ $kelClass }}"></div>
        <div><label class="block text-sm font-semibold text-gray-700 mb-1">No. BPJS Ketenagakerjaan</label><input type="text" name="bpjs_ketenagakerjaan" value="{{ old('bpjs_ketenagakerjaan', $guru->bpjs_ketenagakerjaan) }}" {{ $aktifAttr }} class="{{ $kelClass }}"></div>
        <div><label class="block text-sm font-semibold text-gray-700 mb-1">No. BPJS Kesehatan</label><input type="text" name="bpjs_kesehatan" value="{{ old('bpjs_kesehatan', $guru->bpjs_kesehatan) }}" {{ $aktifAttr }} class="{{ $kelClass }}"></div>
    </div>
</div>

{{-- 5. DATA KELUARGA --}}
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 mb-4">
    <h4 class="text-sm font-bold text-gray-800 mb-3 flex items-center"><i class="fas fa-house-user text-rose-600 mr-2"></i> Data Keluarga</h4>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Status Pernikahan</label>
            <select name="status_menikah" {{ $aktifAttr }} class="{{ $kelClass }}">
                <option value="">-- Pilih --</option>
                @foreach(['Belum Menikah', 'Menikah', 'Duda', 'Janda'] as $opt)
                    <option value="{{ $opt }}" {{ old('status_menikah', $guru->status_menikah) == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                @endforeach
            </select>
        </div>
        <div><label class="block text-sm font-semibold text-gray-700 mb-1">Nama Pasangan</label><input type="text" name="nama_pasangan" value="{{ old('nama_pasangan', $guru->nama_pasangan) }}" {{ $aktifAttr }} class="{{ $kelClass }}"></div>
        <div><label class="block text-sm font-semibold text-gray-700 mb-1">Jumlah Anak</label><input type="number" name="jumlah_anak" value="{{ old('jumlah_anak', $guru->jumlah_anak ?? 0) }}" min="0" max="99" {{ $aktifAttr }} class="{{ $kelClass }}"></div>
    </div>
</div>

{{-- 6. DOKUMEN KELENGKAPAN --}}
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 mb-4">
    <h4 class="text-sm font-bold text-gray-800 mb-3 flex items-center"><i class="fas fa-folder-open text-gray-500 mr-2"></i> Dokumen Kelengkapan</h4>

    @if($bolehDokumen)
        <form method="POST" action="{{ $dokumenAction }}" enctype="multipart/form-data" class="mb-4 bg-gray-50 border border-gray-200 rounded-xl p-4" data-turbo="false">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Jenis Dokumen</label>
                    <select name="jenis" class="w-full border border-gray-300 rounded-lg p-2 bg-white text-sm focus:ring-2 focus:ring-sky-500 outline-none">
                        @foreach(['KTP', 'KK', 'Ijazah', 'Sertifikat Pendidik', 'SK Pengangkatan', 'SK Pembagian Tugas', 'NPWP', 'Akta Kelahiran', 'Pas Foto', 'Lainnya'] as $jn)
                            <option value="{{ $jn }}">{{ $jn }}</option>
                        @endforeach
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
    @endif

    @php $dokumens = $guru->dokumens ?? collect(); @endphp

    @if($dokumens->isEmpty())
        <div class="text-sm text-gray-400">Belum ada dokumen untuk guru ini.</div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            @foreach($dokumens as $dok)
                <div class="flex items-center justify-between gap-2 bg-white border border-gray-200 rounded-lg px-3 py-2.5 shadow-sm">
                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-file-pdf text-rose-500"></i>
                            <span class="text-xs font-bold text-gray-700 truncate">{{ $dok->jenis }}</span>
                        </div>
                        <div class="text-[11px] text-gray-400 truncate">{{ $dok->nama_asli ?? $dok->file_path }}</div>
                        @if($dok->keterangan)
                            <div class="text-[11px] text-gray-500 truncate">{{ $dok->keterangan }}</div>
                        @endif
                    </div>
                    <div class="flex items-center gap-1.5 shrink-0">
                        <a href="{{ asset('uploads/' . $dok->file_path) }}" target="_blank" class="w-7 h-7 rounded-lg bg-gray-50 text-gray-500 hover:bg-sky-500 hover:text-white transition flex items-center justify-center border border-gray-100" title="Lihat">
                            <i class="fas fa-eye text-[9px]"></i>
                        </a>
                        @if($bolehDokumen)
                            <form method="POST" action="{{ $guruPage ? route('guru.profil.dokumen.hapus', $dok->id) : '/master-guru-dokumen/' . $dok->id . '/hapus' }}" onsubmit="return confirm('Hapus dokumen ini?')">
                                @csrf
                                <button type="submit" class="w-7 h-7 rounded-lg bg-gray-50 text-gray-500 hover:bg-red-500 hover:text-white transition flex items-center justify-center border border-gray-100" title="Hapus">
                                    <i class="fas fa-trash text-[9px]"></i>
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>