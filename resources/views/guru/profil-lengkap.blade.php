@extends('layouts.app')
@section('title', 'Profil Lengkap - SmartPesantren')
@section('content')
<style>
    header, aside { display: none !important; }
    main { padding: 0 !important; background-color: #f8fafc !important; overflow: hidden !important; }
    body { overflow: hidden !important; background-color: #f8fafc !important; }
    .scrollbar-none::-webkit-scrollbar { display: none; }
    .scrollbar-none { -ms-overflow-style: none; scrollbar-width: none; }
    @supports (padding-bottom: env(safe-area-inset-bottom)) { .pb-safe { padding-bottom: env(safe-area-inset-bottom); } }
</style>

<div data-turbo="true" class="max-w-md mx-auto h-[100dvh] bg-slate-50 flex flex-col relative font-sans overflow-hidden">
    
    <!-- HEADER STICKY (Dengan Tombol Kembali) -->
    <div class="shrink-0 bg-white border-b border-slate-100 px-4 py-4 flex items-center shadow-[0_2px_10px_rgba(0,0,0,0.02)] relative z-20">
        <a href="{{ route('guru.menu') }}" class="w-10 h-10 rounded-2xl bg-slate-50 text-slate-500 flex items-center justify-center hover:bg-slate-100 hover:text-emerald-600 active:scale-95 transition-all">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div class="flex-1 px-4">
            <h2 class="text-base font-black text-slate-800 tracking-tight">Biodata Guru</h2>
            <p class="text-[9px] font-bold text-emerald-600 uppercase tracking-widest mt-0.5">Lengkapi Data Profesional</p>
        </div>
    </div>

    <!-- AREA KONTEN FORM -->
    <div class="flex-1 overflow-y-auto bg-slate-50 relative z-10 pb-28 pt-5 scrollbar-none px-5">
        
        @if(session('status'))
            <div class="mb-5 bg-emerald-50 text-emerald-700 p-4 rounded-2xl text-xs font-bold flex items-center border border-emerald-100 shadow-sm transition-all animate-[sweep_0.3s_ease-in-out]">
                <div class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center mr-3 shrink-0">
                    <i class="fas fa-check text-emerald-600"></i>
                </div>
                {{ session('status') }}
            </div>
        @endif

        <form action="{{ route('guru.profil.update') }}" method="POST" id="form-profil">
            @csrf
            @method('PUT')

            <!-- SECTION 1: DATA UTAMA (Terkunci / Read-only) -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm mb-4">
                <h3 class="text-[10px] font-black text-slate-400 mb-4 uppercase tracking-wider flex items-center"><i class="fas fa-lock mr-2 text-slate-300"></i> Identitas Utama (Otomatis)</h3>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 mb-1.5 uppercase tracking-wider">Nama Lengkap & Gelar</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <i class="fas fa-user-circle"></i>
                            </div>
                            <input type="text" value="{{ $guru->nama_guru }}" readonly class="w-full bg-slate-50 border border-slate-200 text-slate-500 font-bold rounded-xl pl-10 pr-3 py-2.5 text-sm outline-none cursor-not-allowed">
                        </div>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 mb-1.5 uppercase tracking-wider">Nomor Induk Guru (NIG)</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <i class="fas fa-id-badge"></i>
                            </div>
                            <input type="text" value="{{ $guru->nig }}" readonly class="w-full bg-slate-50 border border-slate-200 text-slate-500 font-bold rounded-xl pl-10 pr-3 py-2.5 text-sm outline-none cursor-not-allowed">
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECTION 2: BIODATA PRIBADI -->
            <!-- PERHATIAN: Sesuaikan atribut name="..." dengan nama kolom asli di tabel gurus -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm mb-4">
                <h3 class="text-[10px] font-black text-emerald-600 mb-4 uppercase tracking-wider flex items-center"><i class="fas fa-user-edit mr-2"></i> Data Pribadi</h3>
                
                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 mb-1.5 uppercase tracking-wider">Tempat Lahir</label>
                            <input type="text" name="tempat_lahir" value="{{ $guru->tempat_lahir ?? '' }}" placeholder="Contoh: Surabaya" class="w-full bg-white border border-slate-200 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100 rounded-xl px-3.5 py-2.5 text-sm font-medium outline-none transition-all">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 mb-1.5 uppercase tracking-wider">Tanggal Lahir</label>
                            <input type="date" name="tanggal_lahir" value="{{ $guru->tanggal_lahir ?? '' }}" class="w-full bg-white border border-slate-200 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100 rounded-xl px-3.5 py-2.5 text-sm font-medium outline-none transition-all">
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 mb-1.5 uppercase tracking-wider">Jenis Kelamin</label>
                        <select name="gender" class="w-full bg-white border border-slate-200 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100 rounded-xl px-3.5 py-2.5 text-sm font-medium outline-none transition-all appearance-none">
                            <option value="">-- Pilih --</option>
                            <option value="Laki-laki" {{ ($guru->gender ?? '') == 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                            <option value="Perempuan" {{ ($guru->gender ?? '') == 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 mb-1.5 uppercase tracking-wider">Alamat Domisili</label>
                        <textarea name="alamat" rows="2" placeholder="Masukkan alamat lengkap..." class="w-full bg-white border border-slate-200 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100 rounded-xl px-3.5 py-2.5 text-sm font-medium outline-none transition-all resize-none">{{ $guru->alamat ?? '' }}</textarea>
                    </div>
                </div>
            </div>

            <!-- SECTION 3: KONTAK & AKADEMIK -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm mb-2">
                <h3 class="text-[10px] font-black text-emerald-600 mb-4 uppercase tracking-wider flex items-center"><i class="fas fa-graduation-cap mr-2"></i> Kontak & Akademik</h3>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 mb-1.5 uppercase tracking-wider">Nomor HP / WhatsApp</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <i class="fab fa-whatsapp"></i>
                            </div>
                            <input type="text" name="no_hp" value="{{ $guru->no_hp ?? '' }}" placeholder="08xxxxxxxxxx" class="w-full bg-white border border-slate-200 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100 rounded-xl pl-10 pr-3 py-2.5 text-sm font-medium outline-none transition-all">
                        </div>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 mb-1.5 uppercase tracking-wider">Pendidikan Terakhir</label>
                        <input type="text" name="pendidikan_terakhir" value="{{ $guru->pendidikan_terakhir ?? '' }}" placeholder="Contoh: S1 Pendidikan Agama Islam" class="w-full bg-white border border-slate-200 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100 rounded-xl px-3.5 py-2.5 text-sm font-medium outline-none transition-all">
                    </div>
                </div>
            </div>
            
        </form>
    </div>

    <!-- TOMBOL SIMPAN MELAYANG (FLOATING ACTION BUTTON) -->
    <div class="absolute bottom-0 left-0 right-0 z-40 w-full bg-white/95 backdrop-blur-xl border-t border-slate-100 shadow-[0_-10px_25px_rgba(0,0,0,0.06)] px-5 py-4 pb-safe">
        <button type="submit" form="form-profil" class="w-full bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 active:scale-[0.98] text-white font-black text-sm py-3.5 rounded-2xl transition-all shadow-[0_8px_20px_rgba(16,185,129,0.3)] flex items-center justify-center group">
            <i class="fas fa-save mr-2.5 text-lg group-hover:scale-110 transition-transform"></i> Simpan Perubahan Biodata
        </button>
    </div>

</div>

<!-- Animasi Kustom untuk Notifikasi Sukses -->
<style>
    @keyframes sweep { 0% { opacity: 0; transform: translateY(-10px) } 100% { opacity: 1; transform: translateY(0) } }
</style>
@endsection