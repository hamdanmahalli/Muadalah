@php
$buku = $r;
$sts = $buku->status;
$user = auth()->user();
$isAdmin = $user && $user->hasRole('Administrator');
$isPengaju = $buku->pencairan && (int) $buku->pencairan->diajukan_oleh === (int) $user?->id;
$canBendahara = $isAdmin || ($user && $user->hasPermissionTo('akses_validasi_pencairan'));
$canPimpinan = $isAdmin || ($user && $user->hasPermissionTo('akses_validasi_buku_kas'));
$stMap = [
    'dilaporkan'   => ['Menunggu Validasi', 'bg-amber-100 text-amber-700', 'fa-paper-plane'],
    'diterima'     => ['Diterima Bendahara', 'bg-sky-100 text-sky-700', 'fa-circle-check'],
    'disahkan'     => ['Disahkan', 'bg-emerald-100 text-emerald-700', 'fa-stamp'],
    'dikembalikan' => ['Dikembalikan', 'bg-rose-100 text-rose-700', 'fa-rotate-left'],
];
$st = $stMap[$sts] ?? $stMap['dilaporkan'];
@endphp
<div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-4">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="min-w-0">
            <p class="text-sm font-black text-slate-800 flex items-center flex-wrap gap-2">
                {{ $buku->label }}
                <span class="px-2 py-0.5 rounded-full {{ $st[1] }} text-[10px] font-black uppercase inline-flex items-center gap-1"><i class="fas {{ $st[2] }}"></i>{{ $st[0] }}</span>
            </p>
            @if($buku->pencairan)
            <p class="text-[10px] font-black text-emerald-700 mt-1">
                SPP {{ $buku->pencairan->kode }} &middot; {{ $buku->pencairan->keperluan }}
                @if($buku->pencairan->pengaju && !$isPengaju) &middot; Pengaju: {{ $buku->pencairan->pengaju->name }}@endif
            </p>
            @endif
            <p class="text-[10px] font-bold text-slate-400 mt-1">
                Masuk <span class="text-emerald-600">Rp {{ number_format($buku->total_masuk, 0, ',', '.') }}</span> &middot;
                Keluar <span class="text-rose-600">Rp {{ number_format($buku->total_keluar, 0, ',', '.') }}</span> &middot;
                Sisa <span class="text-slate-700">Rp {{ number_format($buku->sisa, 0, ',', '.') }}</span>
                @if($buku->pemasukan_manual > 0)
                &middot; Pemasukan manual Rp {{ number_format($buku->pemasukan_manual, 0, ',', '.') }}
                @endif
            </p>
            @if($buku->catatan)
            <p class="text-[10px] font-semibold text-slate-500 mt-1.5"><i class="fas fa-comment-dots text-slate-400 mr-1"></i>Catatan pengaju: {{ $buku->catatan }}</p>
            @endif
            @if($buku->alasan_dikembalikan)
            <p class="text-[10px] font-semibold text-rose-600 mt-1.5"><i class="fas fa-circle-exclamation mr-1"></i>Alasan dikembalikan: {{ $buku->alasan_dikembalikan }}</p>
            @endif
            @php
            $rincian = 'Dilaporkan ' . $buku->dilaporkan_at?->format('d M Y H:i') . ($buku->pelapor ? ' oleh ' . $buku->pelapor->name : '');
            if ($buku->diterima_bendahara_at) {
                $rincian .= ' · Diterima bendahara ' . $buku->diterima_bendahara_at->format('d M Y H:i') . ($buku->penerima ? ' oleh ' . $buku->penerima->name : '');
            }
            if ($buku->dikembalikan_at) {
                $rincian .= ' · Dikembalikan ' . $buku->dikembalikan_at->format('d M Y H:i') . ($buku->pengembali ? ' oleh ' . $buku->pengembali->name : '');
            }
            if ($buku->disahkan_at) {
                $rincian .= ' · Disahkan ' . $buku->disahkan_at->format('d M Y H:i') . ($buku->pengesah ? ' oleh ' . $buku->pengesah->name : '');
            }
            @endphp
            <p class="text-[10px] font-semibold text-slate-400 mt-1.5">{{ $rincian }}</p>
        </div>

        <div class="flex items-center gap-2 shrink-0 flex-wrap">
            <a href="{{ route('kebendaharaan.laporan.cetak', $buku->id) }}" class="px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition-all">
                <i class="fas fa-print mr-1.5"></i> Cetak
            </a>

            @if($sts === 'dilaporkan' && $canBendahara)
            <form action="{{ route('kebendaharaan.laporan.terima', $buku->id) }}" method="POST" onsubmit="return confirm('Terima laporan {{ $buku->label }} dari {{ $buku->pelapor?->name ?? 'pengaju' }}?')">
                @csrf
                <button type="submit" class="px-3 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition-all">
                    <i class="fas fa-check mr-1.5"></i> Terima
                </button>
            </form>
            <button type="button" class="btnKembali px-3 py-2 rounded-xl bg-white border border-rose-200 text-rose-600 hover:bg-rose-50 font-bold text-xs transition-all"
                data-route="{{ route('kebendaharaan.laporan.kembalikan', $buku->id) }}" data-label="{{ $buku->label }}">
                <i class="fas fa-rotate-left mr-1.5"></i> Kembalikan
            </button>
            @endif

            @if($sts === 'diterima' && $canPimpinan)
            <form action="{{ route('kebendaharaan.laporan.sahkan', $buku->id) }}" method="POST" onsubmit="return confirm('Sahkan laporan {{ $buku->label }}? Setelah disahkan hanya Administrator yang bisa membukanya kembali.')">
                @csrf
                <button type="submit" class="px-3 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition-all">
                    <i class="fas fa-stamp mr-1.5"></i> Sahkan
                </button>
            </form>
            <button type="button" class="btnKembali px-3 py-2 rounded-xl bg-white border border-rose-200 text-rose-600 hover:bg-rose-50 font-bold text-xs transition-all"
                data-route="{{ route('kebendaharaan.laporan.kembalikan-pengesahan', $buku->id) }}" data-label="{{ $buku->label }}">
                <i class="fas fa-rotate-left mr-1.5"></i> Kembalikan
            </button>
            @endif

            @if($sts === 'dikembalikan' && ($isPengaju || $isAdmin))
            <form action="{{ route('kebendaharaan.laporan.buka-buku', $buku->id) }}" method="POST" onsubmit="return confirm('Buka kembali laporan {{ $buku->label }} untuk perbaikan? Anda bisa mengubah catatan lalu kirim ulang ke bendahara.')">
                @csrf
                <button type="submit" class="px-3 py-2 rounded-xl bg-amber-100 hover:bg-amber-200 text-amber-700 font-bold text-xs transition-all">
                    <i class="fas fa-pen mr-1.5"></i> Perbaiki
                </button>
            </form>
            @endif

            @if($isAdmin && ($sts === 'dilaporkan' || $sts === 'diterima'))
            <form action="{{ route('kebendaharaan.laporan.buka-buku', $buku->id) }}" method="POST" onsubmit="return confirm('Buka paksa laporan {{ $buku->label }}?')">
                @csrf
                <button type="submit" class="px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold text-xs transition-all">
                    <i class="fas fa-lock-open mr-1.5"></i> Buka Paksa
                </button>
            </form>
            @endif
        </div>
    </div>
</div>