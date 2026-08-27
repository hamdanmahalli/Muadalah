<div class="shrink-0 z-40 w-full bg-white/95 backdrop-blur-xl border-t border-slate-100 shadow-[0_-10px_25px_rgba(0,0,0,0.06)] px-4 py-2 flex justify-between items-end pb-safe pt-2">
    <a href="/dashboard-guru" class="flex flex-col items-center justify-center w-12 transition pb-1 group {{ ($active ?? '') === 'beranda' ? 'text-emerald-600' : 'text-slate-400 hover:text-emerald-500' }}">
        <i class="fas fa-home text-xl mb-1 group-active:scale-90 transition-transform"></i>
        <span class="text-[9px] {{ ($active ?? '') === 'beranda' ? 'font-black' : 'font-bold' }}">Beranda</span>
    </a>
    <a href="/kaldik" class="flex flex-col items-center justify-center w-12 transition pb-1 group {{ ($active ?? '') === 'kaldik' ? 'text-emerald-600' : 'text-slate-400 hover:text-emerald-500' }}">
        <i class="fas fa-calendar-alt text-xl mb-1 group-active:scale-90 transition-transform"></i>
        <span class="text-[9px] {{ ($active ?? '') === 'kaldik' ? 'font-black' : 'font-bold' }}">Kaldik</span>
    </a>
    <div class="relative -top-6 flex justify-center items-center">
        <a href="/scan-kelas" class="w-16 h-16 rounded-full bg-gradient-to-b from-emerald-400 to-emerald-600 text-white flex items-center justify-center text-3xl shadow-[0_8px_20px_rgba(16,185,129,0.4)] border-4 border-slate-50 transform hover:scale-105 active:scale-95 transition-all group">
            <i class="fas fa-qrcode group-active:scale-90 transition-transform"></i>
        </a>
    </div>
    <a href="/rekap-presensi" class="flex flex-col items-center justify-center w-12 transition pb-1 group {{ ($active ?? '') === 'rekap' ? 'text-emerald-600' : 'text-slate-400 hover:text-emerald-500' }}">
        <i class="fas fa-file-invoice text-xl mb-1 group-active:scale-90 transition-transform"></i>
        <span class="text-[9px] {{ ($active ?? '') === 'rekap' ? 'font-black' : 'font-bold' }}">Rekap</span>
    </a>
    <a href="/menu" class="flex flex-col items-center justify-center w-12 transition pb-1 group {{ ($active ?? '') === 'menu' ? 'text-emerald-600' : 'text-slate-400 hover:text-emerald-500' }}">
        <i class="fas fa-bars text-xl mb-1 group-active:scale-90 transition-transform"></i>
        <span class="text-[9px] {{ ($active ?? '') === 'menu' ? 'font-black' : 'font-bold' }}">Menu</span>
    </a>
</div>
