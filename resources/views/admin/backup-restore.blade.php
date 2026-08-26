@extends('layouts.app')

@section('title', 'Manajemen Database (SQL)')

@section('content')
<div class="mb-6">
    <h2 class="text-2xl font-black text-slate-800 tracking-tight flex items-center">
        <i class="fas fa-database text-indigo-500 mr-3"></i> Backup & Restore Data
    </h2>
    <p class="text-sm font-bold text-slate-400 mt-0.5">Pilih tabel spesifik untuk diekspor (Backup) atau unggah file .sql untuk pemulihan (Restore).</p>
</div>

<!-- Alert Sukses & Error -->
@if(session('sukses'))
<div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl mb-6 flex items-center shadow-sm">
    <i class="fas fa-check-circle text-xl mr-3"></i>
    <span class="font-bold text-sm">{!! session('sukses') !!}</span>
</div>
@endif

@if(session('error'))
<div class="bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-xl mb-6 flex items-center shadow-sm">
    <i class="fas fa-exclamation-triangle text-xl mr-3"></i>
    <span class="font-bold text-sm">{!! session('error') !!}</span>
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

    <!-- ============================================== -->
    <!-- KOTAK KIRI: EKSPOR / BACKUP (DENGAN KRITERIA)  -->
    <!-- ============================================== -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 flex flex-col h-full overflow-hidden">
        <div class="bg-indigo-50/50 px-5 py-4 border-b border-indigo-100 flex justify-between items-center">
            <h3 class="font-black text-indigo-700 uppercase tracking-widest text-sm"><i class="fas fa-download mr-2"></i> Ekspor (Backup)</h3>
            <span class="bg-indigo-100 text-indigo-600 text-[10px] font-bold px-2 py-1 rounded-md">{{ count($tables) ?? 0 }} Tabel</span>
        </div>
        
        <form action="/backup-restore/export" method="POST" class="p-5 flex flex-col flex-grow">
            @csrf
            <p class="text-xs font-bold text-slate-500 mb-3">Pilih kriteria tabel yang ingin di-backup:</p>
            
            <!-- Tombol Aksi Cepat Checkbox -->
            <div class="mb-3 flex gap-2">
                <button type="button" onclick="pilihSemua(true)" class="text-[10px] bg-slate-100 hover:bg-slate-200 text-slate-600 px-3 py-1.5 rounded-lg font-bold transition-colors">Pilih Semua</button>
                <button type="button" onclick="pilihSemua(false)" class="text-[10px] bg-slate-100 hover:bg-slate-200 text-slate-600 px-3 py-1.5 rounded-lg font-bold transition-colors">Kosongkan Pilihan</button>
            </div>

            <!-- Daftar Checkbox Tabel Database -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-[300px] overflow-y-auto scrollbar-thin scrollbar-thumb-slate-200 p-2 border border-slate-100 rounded-xl mb-4 bg-slate-50/50">
                @if(isset($tables))
                    @foreach($tables as $t)
                        @if(!in_array($t->tablename, ['migrations', 'failed_jobs', 'personal_access_tokens'])) <!-- Abaikan tabel sistem bawaan Laravel -->
                            <label class="flex items-center space-x-2 p-2 bg-white rounded-lg border border-slate-100 cursor-pointer hover:bg-indigo-50 hover:border-indigo-100 transition-colors shadow-sm">
                                <input type="checkbox" name="tables[]" value="{{ $t->tablename }}" class="tabel-checkbox rounded text-indigo-500 focus:ring-indigo-500 w-4 h-4 border-slate-300" checked>
                                <span class="text-xs font-bold text-slate-600 truncate">{{ $t->tablename }}</span>
                            </label>
                        @endif
                    @endforeach
                @endif
            </div>

            <!-- Tombol Eksekusi Backup -->
            <div class="mt-auto pt-4 border-t border-slate-100">
                <button type="submit" class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 active:scale-95 text-white font-black text-sm rounded-xl transition-all shadow-[0_4px_15px_-3px_rgba(79,70,229,0.4)] flex items-center justify-center">
                    <i class="fas fa-file-export mr-2"></i> Buat & Unduh File SQL
                </button>
            </div>
        </form>
    </div>

    <!-- ============================================== -->
    <!-- KOTAK KANAN: IMPOR / RESTORE                   -->
    <!-- ============================================== -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 flex flex-col h-full overflow-hidden">
        <div class="bg-rose-50/50 px-5 py-4 border-b border-rose-100 flex justify-between items-center">
            <h3 class="font-black text-rose-700 uppercase tracking-widest text-sm"><i class="fas fa-upload mr-2"></i> Impor (Restore)</h3>
        </div>
        
        <form action="/backup-restore/import" method="POST" enctype="multipart/form-data" class="p-5 flex flex-col flex-grow">
            @csrf
            
            <!-- Kotak Peringatan Destruktif -->
            <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-5 shadow-sm">
                <div class="flex items-start">
                    <i class="fas fa-exclamation-triangle text-amber-500 text-xl mr-3 mt-0.5"></i>
                    <div>
                        <h4 class="text-sm font-black text-amber-800 mb-1">Peringatan Keras!</h4>
                        <p class="text-xs font-medium text-amber-700 leading-relaxed">
                            Melakukan Restore akan <b>MENGGANTIKAN</b> seluruh isi tabel database Anda saat ini dengan data dari file SQL yang diunggah. Pastikan file yang diunggah sudah benar.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Input Upload File -->
            <div class="mb-5 flex-grow flex flex-col justify-center">
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Unggah File Backup (.SQL)</label>
                <div class="relative border-2 border-dashed border-slate-300 rounded-2xl p-8 hover:bg-slate-50 hover:border-indigo-300 transition-colors text-center group cursor-pointer">
                    <input type="file" name="file_sql" accept=".sql" required class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                    <i class="fas fa-file-code text-4xl text-slate-300 mb-3 group-hover:text-indigo-400 transition-colors"></i>
                    <p class="text-sm font-bold text-slate-600">Klik atau seret file .sql ke area ini</p>
                    <p class="text-[10px] font-bold text-slate-400 mt-1">Hanya menerima format SQL (PostgreSQL Dump)</p>
                </div>
            </div>

            <!-- Tombol Eksekusi Restore -->
            <div class="mt-auto pt-4 border-t border-slate-100">
                <button type="submit" onclick="return confirm('PERINGATAN! Seluruh data terkait saat ini akan tertimpa. Apakah Anda sangat yakin ingin melanjutkan Restore?')" class="w-full py-3 bg-rose-600 hover:bg-rose-700 active:scale-95 text-white font-black text-sm rounded-xl transition-all shadow-[0_4px_15px_-3px_rgba(225,29,72,0.4)] flex items-center justify-center">
                    <i class="fas fa-database mr-2"></i> Eksekusi Restore Data
                </button>
            </div>
        </form>
    </div>

</div>

<!-- Script Manipulasi Checkbox Massal -->
<script>
    function pilihSemua(status) {
        let checkboxes = document.querySelectorAll('.tabel-checkbox');
        checkboxes.forEach((cb) => {
            cb.checked = status;
        });
    }
</script>
@endsection