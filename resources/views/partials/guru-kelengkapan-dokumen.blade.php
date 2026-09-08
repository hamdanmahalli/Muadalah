{{-- Bagian Dokumen Kelengkapan (BERDASAR: di-render DI LUAR form profil utama agar tidak nested).
     Variabel: $guru (load 'dokumens'), $bolehDokumen, $guruPage, $dokumenAction. --}}
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