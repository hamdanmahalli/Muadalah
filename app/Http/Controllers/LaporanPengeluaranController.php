<?php

namespace App\Http\Controllers;

use App\Models\AnggaranKelompok;
use App\Models\AnggaranPos;
use App\Models\LaporanPengeluaran;
use App\Models\LaporanPengeluaranItem;
use App\Models\Pencairan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanPengeluaranController extends Controller
{
    public function index(Request $request)
    {
        $periode = get_periode_aktif();
        $query = LaporanPengeluaran::with(['pos', 'pencairan', 'items.pos']);

        if ($periode) {
            $query->where('periode_id', $periode->id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $list = $query->orderBy('id', 'desc')->get();

        return view('admin.kebendaharaan.laporan-index', compact('list'));
    }

    public function create()
    {
        $periode = get_periode_aktif();
        if (!$periode) {
            return redirect()->back()->with('error', 'Tidak ada periode aktif.');
        }

        // Pilihan panjar: SPP berstatus dibayar + item pos-nya (untuk prefill keranjang LPJ).
        $pencairanDibayar = Pencairan::with(['items.pos'])
            ->where('periode_id', $periode->id)
            ->where('status', 'dibayar')
            ->orderBy('id', 'desc')
            ->get()
            ->map(fn ($pc) => [
                'id'      => (int) $pc->id,
                'kode'    => (string) $pc->kode,
                'nominal' => (float) $pc->nominal,
                'items'   => $pc->items->map(fn ($it) => [
                    'pencairan_item_id' => (int) $it->id,
                    'pos_id'            => (int) $it->anggaran_pos_id,
                    'kode'              => (string) ($it->pos?->kode ?? ''),
                    'uraian'            => (string) ($it->pos?->uraian ?? ''),
                    'nominal'           => (float) $it->nominal,
                ])->values(),
            ])
            ->values();

        // Kelompok + pos dari anggaran final periode aktif (untuk keranjang kasir),
        // lengkap dengan pagu & realisasi LPJ disetujui per pos.
        $kelompokAktif = AnggaranKelompok::with(['pos'])
            ->whereHas('anggaran', function ($q) use ($periode) {
                $q->where('periode_id', $periode->id)->where('status', 'final');
            })
            ->orderBy('urutan')
            ->orderBy('kode')
            ->get();

        $approvedPerPos = collect();
        if ($kelompokAktif->isNotEmpty()) {
            $posIds = $kelompokAktif->pluck('pos')->flatten()->pluck('id');
            $approvedPerPos = LaporanPengeluaranItem::whereIn('anggaran_pos_id', $posIds)
                ->whereHas('laporan', fn ($q) => $q->where('periode_id', $periode->id)->where('status', 'disetujui'))
                ->get()
                ->groupBy('anggaran_pos_id')
                ->map(fn ($g) => (float) $g->sum('nominal'));
        }

        $kasir = $kelompokAktif->map(function ($k) use ($approvedPerPos) {
            $pos = $k->pos->map(function ($p) use ($approvedPerPos) {
                return [
                    'id'        => (int) $p->id,
                    'kode'      => (string) $p->kode,
                    'uraian'    => (string) $p->uraian,
                    'pagu'      => (float) $p->jumlah,
                    'realisasi' => (float) ($approvedPerPos[$p->id] ?? 0),
                ];
            })->values();
            return ['kode' => (string) $k->kode, 'nama' => (string) $k->nama, 'pos' => $pos];
        })->values();

        return view('admin.kebendaharaan.laporan-form', compact('periode', 'pencairanDibayar', 'kasir'));
    }

    public function store(Request $request)
    {
        $periode = get_periode_aktif();
        if (!$periode) {
            return redirect()->back()->with('error', 'Tidak ada periode aktif.');
        }

        $validated = $request->validate([
            'pencairan_id' => 'nullable|exists:pencairan,id',
            'tanggal'      => 'required|date',
            'keterangan'   => 'nullable|string|max:255',
            'items'        => 'required|array|min:1',
            'items.*.pos_id'            => 'required|integer|exists:anggaran_pos,id',
            'items.*.nominal'           => 'required',
            'items.*.uraian'            => 'required|string|max:255',
            'items.*.pencairan_item_id' => 'nullable|integer|exists:pencairan_item,id',
        ]);

        $pencairan = Pencairan::with('items')->find($validated['pencairan_id'] ?? null);
        if ($validated['pencairan_id'] && (!$pencairan || $pencairan->status !== 'dibayar')) {
            return redirect()->back()->with('error', 'Panjar harus dari SPP berstatus Dibayar.')->withInput();
        }

        $posIds = collect($validated['items'])->pluck('pos_id')->unique();
        $pos = AnggaranPos::with('anggaran')->whereKey($posIds)->get()->keyBy('id');

        $approvedPerPos = LaporanPengeluaranItem::whereIn('anggaran_pos_id', $posIds)
            ->whereHas('laporan', fn ($q) => $q->where('periode_id', $periode->id)->where('status', 'disetujui'))
            ->get()
            ->groupBy('anggaran_pos_id')
            ->map(fn ($g) => (float) $g->sum('nominal'));

        $items = [];
        $warnings = [];

        foreach ($validated['items'] as $baris) {
            $posId = (int) $baris['pos_id'];
            $nominal = rupiah_to_int($baris['nominal']);
            if ($nominal <= 0) {
                continue;
            }

            $p = $pos->get($posId);
            if (!$p || $p->anggaran->status !== 'final') {
                return redirect()->back()->with('error', 'Pos "' . ($p->uraian ?? $posId) . '" harus dari anggaran yang sudah final.')
                    ->withInput();
            }

            $pencairanItemId = !empty($baris['pencairan_item_id']) ? (int) $baris['pencairan_item_id'] : null;
            if ($pencairanItemId) {
                if (!$pencairan) {
                    return redirect()->back()->with('error', 'Jejak panjar butuh memilih SPP sumber.')->withInput();
                }
                $sumber = $pencairan->items->firstWhere('id', $pencairanItemId);
                if (!$sumber || $sumber->anggaran_pos_id !== $posId) {
                    return redirect()->back()->with('error', 'Jejak item SPP tidak cocok dengan pos yang dipilih.')->withInput();
                }
                if ($nominal > (float) $sumber->nominal) {
                    $warnings[] = $p->kode . ' ' . $p->uraian . ': nominal Rp ' . number_format($nominal, 0, ',', '.')
                        . ' melebihi panjar SPP Rp ' . number_format($sumber->nominal, 0, ',', '.') . '.';
                }
            }

            $realTerpakai = (float) ($approvedPerPos[$posId] ?? 0);
            if ($nominal + $realTerpakai > (float) $p->jumlah) {
                $warnings[] = $p->kode . ' ' . $p->uraian . ': pagu Rp ' . number_format($p->jumlah, 0, ',', '.')
                    . ', realisasi Rp ' . number_format($realTerpakai, 0, ',', '.')
                    . ', diajukan Rp ' . number_format($nominal, 0, ',', '.')
                    . ' (melebihi pagu; sisanya diambilkan dari pos lain / biaya lain-lain).';
            }

            $items[] = [
                'pos' => $p,
                'nominal' => $nominal,
                'uraian' => trim((string) ($baris['uraian'] ?? '')),
                'pencairan_item_id' => $pencairanItemId,
            ];
        }

        if ($items === []) {
            return redirect()->back()->with('error', 'Tambahkan minimal satu item dengan nominal lebih dari 0.')->withInput();
        }

        $total = (float) array_sum(array_column($items, 'nominal'));

        $laporan = DB::transaction(function () use ($periode, $validated, $items, $total) {
            $laporan = LaporanPengeluaran::create([
                'kode'          => LaporanPengeluaran::nextKode($periode->tahun),
                'periode_id'    => $periode->id,
                'pos_id'        => null,
                'pencairan_id'  => $validated['pencairan_id'] ?? null,
                'tanggal'       => $validated['tanggal'],
                'nominal'       => $total,
                'keterangan'    => $validated['keterangan'] ?? null,
                'status'        => 'diajukan',
                'dibuat_oleh'   => auth()->id(),
            ]);

            foreach ($items as $it) {
                LaporanPengeluaranItem::create([
                    'laporan_pengeluaran_id' => $laporan->id,
                    'anggaran_pos_id'        => $it['pos']->id,
                    'pencairan_item_id'      => $it['pencairan_item_id'],
                    'uraian'                 => $it['uraian'],
                    'nominal'                => $it['nominal'],
                ]);
            }

            return $laporan;
        });

        if ($warnings !== []) {
            return redirect()->route('kebendaharaan.laporan.index')
                ->with('warning', 'LPJ ' . $laporan->kode . ' diajukan DENGAN peringatan:')
                ->with('warning_detail', $warnings);
        }

        return redirect()->route('kebendaharaan.laporan.index')
            ->with('sukses', 'LPJ ' . $laporan->kode . ' dikirim untuk divalidasi.');
    }

    public function validasi($id)
    {
        $laporan = LaporanPengeluaran::with('items.pos')->findOrFail($id);

        if ($laporan->status !== 'diajukan') {
            return redirect()->route('kebendaharaan.laporan.index')
                ->with('error', 'Hanya LPJ berstatus Diajukan yang bisa divalidasi.');
        }

        // Re-check pagu per item saat validasi: melebihi hanya peringatan.
        $warnings = [];
        foreach ($laporan->items as $it) {
            if (!$it->pos) {
                continue;
            }
            $realTerpakai = (float) LaporanPengeluaranItem::where('anggaran_pos_id', $it->anggaran_pos_id)
                ->whereHas('laporan', fn ($q) => $q->where('periode_id', $laporan->periode_id)
                    ->where('status', 'disetujui')
                    ->where('id', '!=', $laporan->id))
                ->sum('nominal');
            if ($realTerpakai + (float) $it->nominal > (float) $it->pos->jumlah) {
                $warnings[] = $it->pos->kode . ' ' . $it->pos->uraian . ': melebihi pagu pos '
                    . 'Rp ' . number_format($it->pos->jumlah, 0, ',', '.')
                    . ' (realisasi terpakai Rp ' . number_format($realTerpakai, 0, ',', '.') . ').';
            }
        }

        $laporan->update([
            'status' => 'disetujui',
            'divalidasi_oleh' => auth()->id(),
            'divalidasi_at' => now(),
        ]);

        $route = redirect()->route('kebendaharaan.laporan.index');

        if ($warnings !== []) {
            return $route->with('warning', 'LPJ ' . $laporan->kode . ' disetujui DENGAN peringatan melebihi pagu pos:')
                ->with('warning_detail', $warnings);
        }

        return $route->with('sukses', 'LPJ ' . $laporan->kode . ' disetujui dan menjadi realisasi pengeluaran.');
    }

    public function tolak(Request $request, $id)
    {
        $laporan = LaporanPengeluaran::findOrFail($id);

        if ($laporan->status !== 'diajukan') {
            return redirect()->route('kebendaharaan.laporan.index')
                ->with('error', 'Hanya LPJ berstatus Diajukan yang bisa ditolak.');
        }

        $validated = $request->validate(['keterangan' => 'required|string']);
        $alasan = $validated['keterangan'];

        $laporan->update([
            'status' => 'ditolak',
            'keterangan' => $alasan,
        ]);

        return redirect()->route('kebendaharaan.laporan.index')
            ->with('sukses', 'LPJ ' . $laporan->kode . ' ditolak.');
    }
}