<?php

namespace App\Http\Controllers;

use App\Models\LaporanPengeluaran;
use App\Models\LaporanPengeluaranItem;
use App\Models\Pencairan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanPengeluaranController extends Controller
{
    public function realisasi(Pencairan $pencairan)
    {
        if ($pencairan->status !== 'dibayar' || $pencairan->jenis !== 'rutin' || $pencairan->items->isEmpty()) {
            return redirect()->route('kebendaharaan.laporan.index')
                ->with('error', 'Realisasi hanya untuk SPP rutin berstatus Dibayar.');
        }

        $pencairan->load('items.pos');

        $laporan = LaporanPengeluaran::with('items')
            ->where('pencairan_id', $pencairan->id)
            ->where('status', 'disetujui')
            ->first();

        $realisasiPerItem = collect();
        if ($laporan) {
            $realisasiPerItem = $laporan->items->groupBy('pencairan_item_id')
                ->map(fn ($g) => (float) $g->sum('nominal'));
        }

        $items = $pencairan->items->map(function ($it) use ($realisasiPerItem) {
            return (object) [
                'id'        => (int) $it->id,
                'kode'      => (string) ($it->pos?->kode ?? ''),
                'uraian'    => (string) ($it->pos?->uraian ?? ''),
                'nominal'   => (float) $it->nominal,
                'realisasi' => (float) ($realisasiPerItem[$it->id] ?? 0),
            ];
        })->values();

        return view('admin.kebendaharaan.laporan-realisasi', compact('pencairan', 'items', 'laporan'));
    }

    public function simpanRealisasi(Request $request, Pencairan $pencairan)
    {
        if ($pencairan->status !== 'dibayar' || $pencairan->jenis !== 'rutin' || $pencairan->items->isEmpty()) {
            return redirect()->route('kebendaharaan.laporan.index')
                ->with('error', 'Realisasi hanya untuk SPP rutin berstatus Dibayar.');
        }

        $pencairan->load('items.pos');
        $periode = get_periode_aktif();

        $validated = $request->validate([
            'realisasi'   => 'required|array',
            'realisasi.*' => 'nullable|string',
        ]);

        $validIds = $pencairan->items->pluck('id');
        $rows = collect($request->input('realisasi', []))
            ->filter(fn ($v, $k) => $validIds->contains((int) $k))
            ->mapWithKeys(function ($v, $k) {
                $val = rupiah_to_int((string) $v);
                return [(int) $k => max(0, $val)];
            });

        $laporan = LaporanPengeluaran::where('pencairan_id', $pencairan->id)
            ->where('status', 'disetujui')
            ->first();

        if ($rows->every(fn ($v) => $v <= 0)) {
            if ($laporan) {
                $laporan->items()->delete();
                $laporan->delete();

                return redirect()->route('kebendaharaan.laporan.index')
                    ->with('warning', 'Realisasi ' . $pencairan->kode . ' dikosongkan (realisasi sebelumnya dihapus).');
            }

            return redirect()->back()->withInput()
                ->with('error', 'Gagal simpan: belum ada nominal realisasi yang diisi. Isi minimal satu kolom Realisasi lebih dari 0 di bagian Rincian.');
        }

        $warnings = [];
        foreach ($pencairan->items as $it) {
            $nilai = (float) ($rows[$it->id] ?? 0);
            if ($nilai > (float) $it->nominal) {
                $warnings[] = ($it->pos?->kode ?? '?') . ' ' . ($it->pos?->uraian ?? '')
                    . ': realisasi Rp ' . number_format($nilai, 0, ',', '.')
                    . ' melebihi nilai pencairan SPP Rp ' . number_format($it->nominal, 0, ',', '.') . '.';
            }
        }

        $total = (float) $rows->sum();
        $bagian = $pencairan->items->pluck('anggaran_pos_id', 'id');

        DB::transaction(function () use ($periode, $pencairan, $rows, $total, $bagian, $laporan) {
            if ($laporan) {
                $laporan->items()->delete();
            } else {
                $laporan = LaporanPengeluaran::create([
                    'kode'           => LaporanPengeluaran::nextKode($periode->tahun),
                    'periode_id'     => $periode->id,
                    'pos_id'         => null,
                    'pencairan_id'   => $pencairan->id,
                    'tanggal'        => now()->toDateString(),
                    'nominal'        => 0,
                    'status'         => 'disetujui',
                    'dibuat_oleh'    => auth()->id(),
                    'divalidasi_oleh' => auth()->id(),
                    'divalidasi_at'  => now(),
                ]);
            }

            $laporan->update([
                'tanggal'    => $laporan->tanggal ? $laporan->tanggal->toDateString() : now()->toDateString(),
                'nominal'    => $total,
                'keterangan' => trim((string) $pencairan->keperluan),
            ]);

            foreach ($rows as $itemId => $nilai) {
                if ($nilai <= 0) {
                    continue;
                }
                LaporanPengeluaranItem::create([
                    'laporan_pengeluaran_id' => $laporan->id,
                    'anggaran_pos_id'        => (int) $bagian[$itemId],
                    'pencairan_item_id'      => (int) $itemId,
                    'uraian'                 => (string) ($pencairan->items->firstWhere('id', (int) $itemId)?->pos?->uraian ?? ''),
                    'nominal'                => $nilai,
                ]);
            }
        });

        $route = redirect()->route('kebendaharaan.laporan.index');

        if ($warnings !== []) {
            return $route->with('warning', 'Realisasi ' . $pencairan->kode . ' disimpan DENGAN peringatan:')
                ->with('warning_detail', $warnings);
        }

        return $route->with('sukses', 'Realisasi ' . $pencairan->kode . ' disimpan dan menjadi realisasi anggaran.');
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
