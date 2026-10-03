<?php

namespace App\Http\Controllers;

use App\Models\AnggaranPemasukan;
use App\Models\BukuKasBulanan;
use App\Models\LaporanPengeluaran;
use App\Models\LaporanPengeluaranItem;
use App\Models\Pemasukan;
use App\Models\Pencairan;
use App\Services\NotaFotoService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BukuKasController extends Controller
{
    public function index()
    {
        $periode = get_periode_aktif();
        if (!$periode) {
            return redirect()->route('kebendaharaan.index')->with('error', 'Tidak ada periode aktif.');
        }

        // Buku kas aktif = SPP rutin terakhir yang diajukan user ini, dibayar, dan belum dilaporkan.
        $sppAktif = $this->sppBukuAktif($periode);
        $ringkas = $sppAktif ? $this->ringkasSpp($periode, $sppAktif) : null;

        $riwayat = BukuKasBulanan::with(['pelapor', 'penerima', 'pengembali', 'pengesah', 'pencairan'])
            ->where('periode_id', $periode->id)
            ->whereHas('pencairan', fn ($q) => $q->where('diajukan_oleh', auth()->id()))
            ->orderBy('bulan_fiskal')
            ->orderBy('id')
            ->get();

        $user = auth()->user();
        $bisaBendahara = $user->hasRole('Administrator') || $user->hasPermissionTo('akses_validasi_pencairan');
        $bisaPimpinan = $user->hasRole('Administrator') || $user->hasPermissionTo('akses_validasi_buku_kas');

        // Ringkasan jumlah menunggu untuk baris tautan ke halaman Validasi Laporan.
        $jmlMenungguBendahara = $bisaBendahara ? $this->bukuPadaTahap($periode, 'dilaporkan')->count() : 0;
        $jmlMenungguPengesahan = $bisaPimpinan ? $this->bukuPadaTahap($periode, 'diterima')->count() : 0;

        return view('admin.kebendaharaan.kas-index', compact(
            'periode', 'sppAktif', 'ringkas', 'riwayat',
            'bisaBendahara', 'bisaPimpinan',
            'jmlMenungguBendahara', 'jmlMenungguPengesahan',
        ));
    }

    /**
     * Halaman validasi & pengesahan laporan SPP: daftar kartu lengkap
     * untuk bendahara (menunggu validasi) dan pimpinan (menunggu pengesahan).
     */
    public function validasi()
    {
        $periode = get_periode_aktif();
        if (!$periode) {
            return redirect()->route('kebendaharaan.index')->with('error', 'Tidak ada periode aktif.');
        }

        $user = auth()->user();
        $bisaBendahara = $user->hasRole('Administrator') || $user->hasPermissionTo('akses_validasi_pencairan');
        $bisaPimpinan = $user->hasRole('Administrator') || $user->hasPermissionTo('akses_validasi_buku_kas');

        $bukuMenungguBendahara = $bisaBendahara ? $this->bukuPadaTahap($periode, 'dilaporkan') : collect();
        $bukuMenungguPengesahan = $bisaPimpinan ? $this->bukuPadaTahap($periode, 'diterima') : collect();

        return view('admin.kebendaharaan.kas-validasi', compact(
            'periode', 'bukuMenungguBendahara', 'bukuMenungguPengesahan',
            'bisaBendahara', 'bisaPimpinan',
        ));
    }

    public function create()
    {
        $periode = get_periode_aktif();
        if (!$periode) {
            return redirect()->route('kebendaharaan.index')->with('error', 'Tidak ada periode aktif.');
        }

        $sppAktif = $this->sppBukuAktif($periode);
        if (!$sppAktif) {
            return redirect()->route('kebendaharaan.laporan.index')
                ->with('error', 'Tidak ada buku kas aktif untuk dicatat. Cairkan dan bayar SPP rutin lebih dulu.');
        }

        $itemIds = $sppAktif->items->pluck('id');
        $terpakai = LaporanPengeluaranItem::whereIn('pencairan_item_id', $itemIds)
            ->whereHas('laporan', fn ($q) => $q->where('periode_id', $periode->id)->where('status', 'disetujui'))
            ->get()
            ->groupBy('pencairan_item_id')
            ->map(fn ($g) => (float) $g->sum('nominal'));

        $items = $sppAktif->items->map(function ($it) use ($terpakai) {
            return [
                'pencairan_item_id' => (int) $it->id,
                'pos_id'            => (int) $it->anggaran_pos_id,
                'kode'              => (string) ($it->pos?->kode ?? ''),
                'uraian'            => (string) ($it->pos?->uraian ?? ''),
                'nominal'           => (float) $it->nominal,
                'sisa'              => (float) $it->nominal - (float) ($terpakai[$it->id] ?? 0),
            ];
        })->values();

        $sppJson = collect([[
            'id'        => (int) $sppAktif->id,
            'kode'      => (string) $sppAktif->kode,
            'tanggal'   => $sppAktif->tanggal_aju?->format('d M Y'),
            'keperluan' => (string) $sppAktif->keperluan,
            'nominal'   => (float) $sppAktif->jumlah,
            'items'     => $items,
        ]]);

        $rencana = AnggaranPemasukan::whereHas('anggaran', fn ($q) => $q->where('periode_id', $periode->id))
            ->orderBy('urutan')
            ->get();

        return view('admin.kebendaharaan.kas-form', compact('periode', 'sppAktif', 'sppJson', 'rencana'));
    }

    public function store(Request $request, NotaFotoService $foto)
    {
        $periode = get_periode_aktif();
        if (!$periode) {
            return redirect()->route('kebendaharaan.index')->with('error', 'Tidak ada periode aktif.');
        }

        if ($request->input('jenis') === 'masuk') {
            return $this->storeMasuk($request, $periode);
        }

        $validated = $request->validate([
            'pencairan_id'      => 'required|exists:pencairan,id',
            'pencairan_item_id' => 'required|exists:pencairan_item,id',
            'tanggal'           => 'required|date',
            'uraian'            => 'required|string|max:255',
            'nominal'           => 'required',
            'keterangan'        => 'nullable|string|max:255',
            'nota'              => 'nullable|image|mimes:jpeg,jpg,png,webp|max:15360',
        ]);

        $cek = $this->cekTahunTanggal($periode, $validated['tanggal']);
        if ($cek !== null) {
            return redirect()->back()->withInput()->with('error', $cek);
        }

        $pencairan = Pencairan::with('items.pos')->find($validated['pencairan_id']);
        if (!$pencairan || $pencairan->periode_id !== $periode->id
            || $pencairan->status !== 'dibayar'
            || $pencairan->jenis !== 'rutin') {
            return redirect()->back()->withInput()
                ->with('error', 'SPP sumber harus sudah dibayar pada periode aktif.');
        }

        if ((int) $pencairan->diajukan_oleh !== (int) auth()->id()) {
            return redirect()->back()->withInput()
                ->with('error', 'Buku kas hanya untuk SPP yang Anda ajukan sendiri.');
        }

        if ($pencairan->bukuKas) {
            return redirect()->back()->withInput()
                ->with('error', 'Laporan SPP ' . $pencairan->kode . ' sudah dikirim ke bendahara, tidak bisa menambah transaksi.');
        }

        $item = $pencairan->items->firstWhere('id', (int) $validated['pencairan_item_id']);
        if (!$item) {
            return redirect()->back()->withInput()
                ->with('error', 'Pos yang dipilih tidak termasuk dalam SPP tersebut.');
        }

        $nominal = rupiah_to_int($validated['nominal']);
        if ($nominal <= 0) {
            return redirect()->back()->withInput()->with('error', 'Nominal belanja harus lebih dari 0.');
        }

        $terpakai = (float) LaporanPengeluaranItem::where('pencairan_item_id', $item->id)
            ->whereHas('laporan', fn ($q) => $q->where('periode_id', $periode->id)->where('status', 'disetujui'))
            ->sum('nominal');
        $sisaItem = (float) $item->nominal - $terpakai;

        $warnings = [];
        if ($nominal > $sisaItem) {
            $warnings[] = ($item->pos?->kode ?? '?') . ' ' . ($item->pos?->uraian ?? '')
                . ': belanja Rp ' . number_format($nominal, 0, ',', '.')
                . ' melebihi sisa SPP Rp ' . number_format(max(0, $sisaItem), 0, ',', '.') . '.';
        }

        $notaPath = null;
        if ($request->hasFile('nota')) {
            try {
                $notaPath = $foto->simpan($request->file('nota'), $periode->tahun);
            } catch (\InvalidArgumentException $e) {
                return redirect()->back()->withInput()->with('error', $e->getMessage());
            }
        }

        DB::transaction(function () use ($periode, $pencairan, $item, $validated, $nominal, $notaPath) {
            $laporan = LaporanPengeluaran::create([
                'kode'            => LaporanPengeluaran::nextKode($periode->tahun),
                'periode_id'      => $periode->id,
                'pos_id'          => $item->anggaran_pos_id,
                'pencairan_id'    => $pencairan->id,
                'tanggal'         => $validated['tanggal'],
                'nominal'         => $nominal,
                'keterangan'      => $validated['keterangan'] ?? null,
                'nota_foto'       => $notaPath,
                'status'          => 'disetujui',
                'dibuat_oleh'     => auth()->id(),
                'divalidasi_oleh' => auth()->id(),
                'divalidasi_at'   => now(),
            ]);

            LaporanPengeluaranItem::create([
                'laporan_pengeluaran_id' => $laporan->id,
                'anggaran_pos_id'        => $item->anggaran_pos_id,
                'pencairan_item_id'      => $item->id,
                'uraian'                 => $validated['uraian'],
                'nominal'                => $nominal,
            ]);
        });

        $redirect = redirect()->route('kebendaharaan.laporan.index');
        if ($warnings !== []) {
            return $redirect->with('warning', 'Belanja tersimpan DENGAN peringatan:')
                ->with('warning_detail', $warnings);
        }

        return $redirect->with('sukses', 'Belanja tercatat sebagai realisasi.');
    }

    public function destroy($id, NotaFotoService $foto)
    {
        $periode = get_periode_aktif();
        $laporan = LaporanPengeluaran::with('pencairan.bukuKas')->findOrFail($id);

        $user = auth()->user();
        if (!$user || !$user->hasRole('Administrator')) {
            if ((int) $laporan->dibuat_oleh !== (int) $user?->id) {
                return redirect()->route('kebendaharaan.laporan.index')
                    ->with('error', 'Catatan belanja hanya bisa dihapus oleh pencatatnya.');
            }
        }

        if ($periode && $laporan->pencairan?->bukuKas) {
            return redirect()->route('kebendaharaan.laporan.index')
                ->with('error', 'Laporan SPP ' . $laporan->pencairan->kode . ' sudah dikirim ke bendahara, catatan tidak bisa dihapus.');
        }

        $foto->hapus($laporan->nota_foto);
        $laporan->delete();

        return redirect()->route('kebendaharaan.laporan.index')->with('sukses', 'Catatan belanja dihapus.');
    }

    public function laporkan(Request $request)
    {
        $periode = get_periode_aktif();
        if (!$periode) {
            return redirect()->route('kebendaharaan.index')->with('error', 'Tidak ada periode aktif.');
        }

        $validated = $request->validate([
            'catatan' => 'nullable|string|max:500',
        ]);

        $spp = $this->sppBukuAktif($periode);
        if (!$spp) {
            return redirect()->route('kebendaharaan.laporan.index')
                ->with('error', 'Tidak ada buku kas aktif untuk dilaporkan. Cairkan dan bayar SPP rutin lebih dulu.');
        }

        $ringkas = $this->ringkasSpp($periode, $spp);

        $buku = BukuKasBulanan::create([
            'periode_id'       => $periode->id,
            'pencairan_id'     => $spp->id,
            'bulan_fiskal'     => (int) $spp->bulan_fiskal,
            'tahun_fiskal'     => (int) $periode->tahun,
            'total_masuk'      => $ringkas['uangMasuk'],
            'total_keluar'     => $ringkas['uangKeluar'],
            'pemasukan_manual' => $ringkas['pemasukanTercatat'],
            'sisa'             => $ringkas['sisa'],
            'dilaporkan_oleh'  => auth()->id(),
            'dilaporkan_at'    => now(),
            'catatan'          => $validated['catatan'] ?? null,
        ]);

        return redirect()->route('kebendaharaan.laporan.index')
            ->with('sukses', 'Laporan SPP ' . $spp->kode . ' dikirim ke bendahara (menunggu validasi).');
    }

    public function terimaBendahara(BukuKasBulanan $buku)
    {
        if (!$this->bolehValidasiBendahara()) {
            return redirect()->route('kebendaharaan.laporan.index')
                ->with('error', 'Anda tidak memiliki akses validasi laporan SPP.');
        }

        if ($buku->status !== 'dilaporkan') {
            return redirect()->route('kebendaharaan.laporan.index')
                ->with('error', 'Laporan ' . $buku->label . ' tidak dalam status menunggu validasi bendahara.');
        }

        $buku->update([
            'diterima_bendahara_oleh' => auth()->id(),
            'diterima_bendahara_at'   => now(),
        ]);

        return redirect()->route('kebendaharaan.laporan.index')
            ->with('sukses', 'Laporan ' . $buku->label . ' diterima bendahara, menunggu pengesahan pimpinan.');
    }

    public function kembalikanBendahara(Request $request, BukuKasBulanan $buku)
    {
        if (!$this->bolehValidasiBendahara()) {
            return redirect()->route('kebendaharaan.laporan.index')
                ->with('error', 'Anda tidak memiliki akses validasi laporan SPP.');
        }

        if ($buku->status !== 'dilaporkan') {
            return redirect()->route('kebendaharaan.laporan.index')
                ->with('error', 'Laporan ' . $buku->label . ' tidak dalam status menunggu validasi bendahara.');
        }

        $validated = $request->validate(['alasan' => 'required|string|max:500']);

        $buku->update([
            'dikembalikan_oleh'   => auth()->id(),
            'dikembalikan_at'     => now(),
            'alasan_dikembalikan' => $validated['alasan'],
        ]);

        return redirect()->route('kebendaharaan.laporan.index')
            ->with('sukses', 'Laporan ' . $buku->label . ' dikembalikan ke pengaju untuk revisi.');
    }

    public function kembalikanPengesahan(Request $request, BukuKasBulanan $buku)
    {
        if (!$this->bolehValidasiPimpinan()) {
            return redirect()->route('kebendaharaan.laporan.index')
                ->with('error', 'Anda tidak memiliki akses pengesahan laporan SPP.');
        }

        if ($buku->status !== 'diterima') {
            return redirect()->route('kebendaharaan.laporan.index')
                ->with('error', 'Laporan ' . $buku->label . ' belum diterima oleh bendahara.');
        }

        $validated = $request->validate(['alasan' => 'required|string|max:500']);

        $buku->update([
            'diterima_bendahara_oleh' => null,
            'diterima_bendahara_at'   => null,
            'dikembalikan_oleh'       => auth()->id(),
            'dikembalikan_at'         => now(),
            'alasan_dikembalikan'     => $validated['alasan'],
        ]);

        return redirect()->route('kebendaharaan.laporan.index')
            ->with('sukses', 'Laporan ' . $buku->label . ' dikembalikan ke pengaju untuk revisi.');
    }

    public function bukaBuku(BukuKasBulanan $buku)
    {
        $user = auth()->user();
        if (!$user) {
            return redirect()->route('kebendaharaan.laporan.index')->with('error', 'Sesi tidak valid.');
        }

        if ($buku->disahkan_at) {
            if (!$user->hasRole('Administrator')) {
                return redirect()->route('kebendaharaan.laporan.index')
                    ->with('error', 'Laporan yang sudah disahkan hanya bisa dibuka paksa oleh Administrator.');
            }
        } elseif (!$user->hasRole('Administrator')) {
            $pengajuId = $buku->pencairan?->diajukan_oleh;
            $isPengaju = $pengajuId && (int) $pengajuId === (int) $user->id;
            if (!$isPengaju || $buku->status !== 'dikembalikan') {
                return redirect()->route('kebendaharaan.laporan.index')
                    ->with('error', $isPengaju
                        ? 'Laporan sedang diproses dan tidak bisa ditarik. Bila perlu revisi, bendahara/pimpinan akan mengembalikannya lebih dulu.'
                        : 'Laporan ini hanya bisa dibuka oleh pengajunya bila dikembalikan.');
            }
        }

        $label = $buku->label;
        $buku->delete();

        return redirect()->route('kebendaharaan.laporan.index')
            ->with('sukses', 'Laporan ' . $label . ' dibuka kembali untuk perbaikan.');
    }

    public function sahkan(BukuKasBulanan $buku)
    {
        if (!$this->bolehValidasiPimpinan()) {
            return redirect()->route('kebendaharaan.laporan.index')
                ->with('error', 'Anda tidak memiliki akses pengesahan laporan SPP.');
        }

        if ($buku->disahkan_at) {
            return redirect()->route('kebendaharaan.laporan.index')
                ->with('error', 'Laporan ' . $buku->label . ' sudah disahkan.');
        }

        if ($buku->status !== 'diterima') {
            return redirect()->route('kebendaharaan.laporan.index')
                ->with('error', 'Laporan ' . $buku->label . ' belum diterima oleh bendahara.');
        }

        $buku->update(['disahkan_oleh' => auth()->id(), 'disahkan_at' => now()]);

        return $this->laporanDisahkan($buku);
    }

    /**
     * Saat laporan disahkan, sisa panjar yang tidak terpakai otomatis dicatat
     * sebagai pemasukan pengembalian yang terhubung ke SPP asal (pencairan_id).
     * Lewati bila sudah ada pengembalian (manual) untuk SPP ini agar tidak dobel.
     */
    private function laporanDisahkan(BukuKasBulanan $buku): \Illuminate\Http\RedirectResponse
    {
        $sukses = 'Laporan ' . $buku->label . ' disahkan.';

        $sisa = (float) $buku->sisa;
        $belumDikembalikan = $buku->pencairan_id && !Pemasukan::where('pencairan_id', $buku->pencairan_id)->exists();

        if ($sisa > 0 && $belumDikembalikan) {
            $pemasukan = Pemasukan::create([
                'periode_id'   => $buku->periode_id,
                'pencairan_id' => $buku->pencairan_id,
                'uraian'       => 'Pengembalian sisa panjar ' . ($buku->pencairan?->kode ?? ''),
                'tanggal'      => now()->toDateString(),
                'jumlah'       => $sisa,
                'keterangan'   => 'Otomatis terhubung saat laporan ' . $buku->label . ' disahkan.',
                'created_by'   => auth()->id(),
            ]);

            $sukses .= ' Sisa panjar Rp ' . number_format($sisa, 0, ',', '.')
                . ' dikembalikan dan tercatat otomatis sebagai pemasukan ('
                . $pemasukan->uraian . ').';
        }

        return redirect()->route('kebendaharaan.laporan.index')
            ->with('sukses', $sukses);
    }

    public function cetak(BukuKasBulanan $buku)
    {
        $buku->load(['periode', 'pelapor', 'penerima', 'pengembali', 'pengesah', 'pencairan']);
        $ringkas = $buku->pencairan
            ? $this->ringkasSpp($buku->periode, $buku->pencairan)
            : $this->ringkasLegacy($buku->periode, $buku->bulan_fiskal, $buku->tahun_fiskal);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.kebendaharaan.kas-pdf', array_merge($ringkas, [
            'buku'    => $buku,
            'periode' => $buku->periode,
        ]))->setPaper('a4', 'portrait');

        return $pdf->download('Buku_Kas_' . str_replace(' ', '_', $buku->label) . '.pdf');
    }

    private function storeMasuk(Request $request, $periode)
    {
        $validated = $request->validate([
            'uraian'                => 'required|string|max:191',
            'anggaran_pemasukan_id' => 'nullable|exists:anggaran_pemasukan,id',
            'tanggal'               => 'required|date',
            'jumlah'                => 'required',
            'keterangan'            => 'nullable|string',
        ]);

        if (!$this->sppBukuAktif($periode)) {
            return redirect()->back()->withInput()
                ->with('error', 'Tidak ada buku kas aktif. Cairkan dan bayar SPP rutin lebih dulu untuk mencatat pemasukan.');
        }

        $cek = $this->cekTahunTanggal($periode, $validated['tanggal']);
        if ($cek !== null) {
            return redirect()->back()->withInput()->with('error', $cek);
        }

        $jumlah = rupiah_to_int($validated['jumlah']);
        if ($jumlah <= 0) {
            return redirect()->back()->withInput()->with('error', 'Jumlah pemasukan harus lebih dari 0.');
        }

        Pemasukan::create([
            'periode_id'            => $periode->id,
            'anggaran_pemasukan_id' => $validated['anggaran_pemasukan_id'] ?? null,
            'uraian'                => $validated['uraian'],
            'tanggal'               => $validated['tanggal'],
            'jumlah'                => $jumlah,
            'keterangan'            => $validated['keterangan'] ?? null,
            'created_by'            => auth()->id(),
        ]);

        return redirect()->route('kebendaharaan.laporan.index')
            ->with('sukses', 'Pemasukan tercatat (nominal terpisah dari dana SPP).');
    }

    /**
     * SPP rutin terakhir yang diajukan user ini, dibayar, dan belum dilaporkan = buku kas aktif.
     */
    private function sppBukuAktif($periode): ?Pencairan
    {
        return Pencairan::with(['items.pos'])
            ->where('periode_id', $periode->id)
            ->where('jenis', 'rutin')
            ->where('status', 'dibayar')
            ->where('diajukan_oleh', auth()->id())
            ->whereDoesntHave('bukuKas')
            ->orderBy('id', 'desc')
            ->first();
    }

    /**
     * Daftar buku pada tahap tertentu (semua pengaju) untuk daftar validasi.
     */
    private function bukuPadaTahap($periode, string $tahap): Collection
    {
        $q = BukuKasBulanan::with(['pelapor', 'penerima', 'pengembali', 'pengesah', 'pencairan'])
            ->where('periode_id', $periode->id);

        switch ($tahap) {
            case 'dilaporkan':
                $q->whereNotNull('dilaporkan_at')
                    ->whereNull('diterima_bendahara_at')
                    ->whereNull('dikembalikan_at')
                    ->whereNull('disahkan_at');
                break;
            case 'diterima':
                $q->whereNotNull('diterima_bendahara_at')
                    ->whereNull('dikembalikan_at')
                    ->whereNull('disahkan_at');
                break;
            default:
                return collect();
        }

        return $q->orderBy('dilaporkan_at')->orderBy('id')->get();
    }

    private function bolehValidasiBendahara(): bool
    {
        $user = auth()->user();

        return $user && ($user->hasRole('Administrator') || $user->hasPermissionTo('akses_validasi_pencairan'));
    }

    private function bolehValidasiPimpinan(): bool
    {
        $user = auth()->user();

        return $user && ($user->hasRole('Administrator') || $user->hasPermissionTo('akses_validasi_buku_kas'));
    }

    /**
     * Ringkasan satu buku kas SPP: kartu + feed harian.
     *
     * @return array{uangMasuk: float, uangKeluar: float, pemasukanTercatat: float, sisa: float, harian: Collection}
     */
    private function ringkasSpp($periode, Pencairan $spp): array
    {
        $belanja = LaporanPengeluaran::with(['items.pos', 'pencairan'])
            ->where('periode_id', $periode->id)
            ->where('pencairan_id', $spp->id)
            ->where('status', 'disetujui')
            ->orderBy('tanggal')
            ->get();

        $pemasukanManual = Pemasukan::with('rencana')
            ->where('periode_id', $periode->id)
            ->where('created_by', $spp->diajukan_oleh)
            ->orderBy('tanggal')
            ->get();

        $uangMasuk = (float) $spp->jumlah;
        $uangKeluar = (float) $belanja->sum(fn ($l) => (float) $l->nominal);
        $pemasukanTercatat = (float) $pemasukanManual->sum('jumlah');

        return [
            'uangMasuk'         => $uangMasuk,
            'uangKeluar'        => $uangKeluar,
            'pemasukanTercatat' => $pemasukanTercatat,
            'sisa'              => $uangMasuk - $uangKeluar,
            'harian'            => $this->bangunFeed(collect([$spp]), $belanja, $pemasukanManual),
        ];
    }

    /**
     * Ringkasan buku lama (bulan fiskal) yang belum terhubung ke SPP, hanya untuk cetak.
     *
     * @return array{uangMasuk: float, uangKeluar: float, pemasukanTercatat: float, sisa: float, harian: Collection}
     */
    private function ringkasLegacy($periode, int $bulan, int $tahun): array
    {
        [$mulai, $sampai] = fiskal_range($bulan, $tahun);

        $spp = Pencairan::with(['items.pos'])
            ->where('periode_id', $periode->id)
            ->where('status', 'dibayar')
            ->whereNotNull('dibayar_at')
            ->whereBetween('dibayar_at', [$mulai, $sampai])
            ->orderBy('dibayar_at')
            ->get();

        $belanja = LaporanPengeluaran::with(['items.pos', 'pencairan'])
            ->where('periode_id', $periode->id)
            ->where('status', 'disetujui')
            ->whereBetween('tanggal', [$mulai, $sampai])
            ->orderBy('tanggal')
            ->get();

        $pemasukanManual = Pemasukan::with('rencana')
            ->where('periode_id', $periode->id)
            ->whereBetween('tanggal', [$mulai, $sampai])
            ->orderBy('tanggal')
            ->get();

        $uangMasuk = (float) $spp->sum(fn ($p) => (float) $p->jumlah);
        $uangKeluar = (float) $belanja->sum(fn ($l) => (float) $l->nominal);
        $pemasukanTercatat = (float) $pemasukanManual->sum('jumlah');

        return [
            'uangMasuk'         => $uangMasuk,
            'uangKeluar'        => $uangKeluar,
            'pemasukanTercatat' => $pemasukanTercatat,
            'sisa'              => $uangMasuk - $uangKeluar,
            'harian'            => $this->bangunFeed($spp, $belanja, $pemasukanManual),
        ];
    }

    /**
     * Gabungkan SPP (masuk), belanja (keluar), dan pemasukan manual menjadi feed per tanggal.
     */
    private function bangunFeed(Collection $spp, Collection $belanja, Collection $pemasukanManual): Collection
    {
        $feed = collect();

        foreach ($spp as $p) {
            $feed->push([
                'tanggal' => $p->dibayar_at?->toDateString() ?: $p->tanggal_aju?->toDateString(),
                'arah'    => 'masuk',
                'sumber'  => 'SPP',
                'kode'    => $p->kode,
                'judul'   => 'Pencairan ' . $p->kode,
                'uraian'  => $p->keperluan,
                'pos'     => null,
                'nominal' => (float) $p->jumlah,
                'foto'    => null,
                'id'      => null,
            ]);
        }

        foreach ($belanja as $l) {
            $item = $l->items->first();
            $feed->push([
                'tanggal' => $l->tanggal?->toDateString(),
                'arah'    => 'keluar',
                'sumber'  => 'Belanja',
                'kode'    => $l->kode,
                'judul'   => $item?->uraian ?: ($l->keterangan ?: 'Belanja'),
                'uraian'  => $l->keterangan,
                'pos'     => $item?->pos ? ($item->pos->kode . ' · ' . $item->pos->uraian) : null,
                'nominal' => (float) $l->nominal,
                'foto'    => $l->nota_foto,
                'id'      => $l->id,
            ]);
        }

        foreach ($pemasukanManual as $m) {
            $feed->push([
                'tanggal' => $m->tanggal?->toDateString(),
                'arah'    => 'masuk',
                'sumber'  => 'Pemasukan',
                'kode'    => null,
                'judul'   => $m->uraian,
                'uraian'  => $m->keterangan,
                'pos'     => $m->rencana?->uraian,
                'nominal' => (float) $m->jumlah,
                'foto'    => null,
                'id'      => null,
            ]);
        }

        return $feed->sortByDesc(fn ($r) => (string) $r['tanggal'])->groupBy('tanggal');
    }

    /**
     * Validasi tanggal transaksi terhadap tahun periode aktif.
     * Mengembalikan pesan error, atau null bila valid.
     */
    private function cekTahunTanggal($periode, string $tanggal): ?string
    {
        if (fiskal_tahun($tanggal) !== $periode->tahun) {
            return 'Tanggal transaksi berada di luar periode aktif (' . $periode->tahun_ajaran . ').';
        }

        return null;
    }
}
