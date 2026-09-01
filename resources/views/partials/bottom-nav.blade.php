<div class="fixed bottom-4 left-0 right-0 z-50 max-w-md mx-auto w-full px-4 pb-safe pointer-events-none">
    <div class="pointer-events-auto mx-auto max-w-[320px] bg-white/90 backdrop-blur-2xl border border-white/70 rounded-full px-4 py-2 shadow-[0_18px_45px_-15px_rgba(2,6,23,0.28)]">

        <div class="flex items-center justify-between relative">

            <!-- BERANDA -->
            <a href="/dashboard-guru" title="Beranda" aria-label="Beranda" class="flex items-center justify-center w-11 h-10 rounded-full transition-all {{ ($active ?? '') === 'beranda' ? 'text-emerald-500' : 'text-slate-400 hover:bg-emerald-50 hover:text-emerald-600' }}">
                <i class="fas fa-house text-base"></i>
            </a>

            <!-- KALDIK -->
            <a href="/kaldik" title="Kaldik" aria-label="Kaldik" class="flex items-center justify-center w-11 h-10 rounded-full transition-all {{ ($active ?? '') === 'kaldik' ? 'text-emerald-500' : 'text-slate-400 hover:bg-emerald-50 hover:text-emerald-600' }}">
                <i class="fas fa-calendar-days text-base"></i>
            </a>

            <!-- SCAN QR (Tombol Utama) -->
            <a href="/scan-kelas" title="Scan QR" aria-label="Scan QR" class="relative -top-5 flex items-center justify-center w-[52px] h-[52px] rounded-full bg-gradient-to-br from-emerald-400 to-emerald-600 text-white text-xl shadow-[0_12px_26px_-6px_rgba(16,185,129,0.7)] ring-4 ring-slate-100 transform transition-all group hover:scale-105 active:scale-95">
                <i class="fas fa-qrcode"></i>
            </a>

            <!-- REKAP -->
            <a href="/rekap-presensi" title="Rekap" aria-label="Rekap" class="flex items-center justify-center w-11 h-10 rounded-full transition-all {{ ($active ?? '') === 'rekap' ? 'text-emerald-500' : 'text-slate-400 hover:bg-emerald-50 hover:text-emerald-600' }}">
                <i class="fas fa-file-lines text-base"></i>
            </a>

            <!-- MENU -->
            <a href="/menu" title="Menu" aria-label="Menu" class="flex items-center justify-center w-11 h-10 rounded-full transition-all {{ ($active ?? '') === 'menu' ? 'text-emerald-500' : 'text-slate-400 hover:bg-emerald-50 hover:text-emerald-600' }}">
                <i class="fas fa-bars text-base"></i>
            </a>

        </div>
    </div>
</div>