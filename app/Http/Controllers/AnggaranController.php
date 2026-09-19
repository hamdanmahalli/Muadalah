<?php

namespace App\Http\Controllers;

use App\Models\AnggaranKebendaharaan;
use App\Models\AnggaranKelompok;
use App\Models\AnggaranPemasukan;
use App\Models\AnggaranPos;
use App\Models\AnggaranPosBulan;
use App\Models\Periode;
use App\Services\AnggaranImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnggaranController extends Controller
{
    public function index()
    {
        $periodeAktif = get_periode_aktif();
        $list = AnggaranKebendaharaan::with(['periode', 'kelompok'])
            ->orderBy('id', 'desc')
            ->get();

        return view('admin.kebendaharaan.anggaran-index', compact('list', 'periodeAktif'));
    }

    public function create()
    {
        $periodeAktif = get_periode_aktif();
        $periodes = Periode::orderBy('tahun_ajaran', 'desc')->get();

        if ($periodeAktif && AnggaranKebendaharaan::where('periode_id', $periodeAktif->id)->exists()) {
            return redirect()->route('kebendaharaan.anggaran.index')
                ->with('error', 'Periode aktif sudah memiliki anggaran. Buka baris anggaran yang ada untuk melanjutkan.');
        }

        return view('admin.kebendaharaan.anggaran-form', compact('periodeAktif', 'periodes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'periode_id' => 'required|exists:periodes,id',
            'nama' => 'required|string|max:191',
        ]);

        $periode = Periode::findOrFail($validated['periode_id']);

        $anggaran = AnggaranKebendaharaan::create([
            'periode_id' => $periode->id,
            'nama' => $validated['nama'],
            'tahun_ajaran' => $periode->tahun_ajaran,
            'status' => 'draft',
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('kebendaharaan.anggaran.show', $anggaran->id)
            ->with('sukses', 'Anggaran dibuat. Silakan tambahkan kelompok pos dan item pos.');
    }

    public function show($id)
    {
        $anggaran = AnggaranKebendaharaan::with(['periode', 'kelompok.pos.posBulan', 'pemasukanRencana'])
            ->findOrFail($id);

        return view('admin.kebendaharaan.anggaran-detail', compact('anggaran'));
    }

    public function import(Request $request)
    {
        $validated = $request->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:5120',
        ]);

        $periode = get_periode_aktif();
        if (!$periode) {
            return back()->with('error', 'Belum ada periode aktif.');
        }

        $anggaran = AnggaranKebendaharaan::where('periode_id', $periode->id)->latest('id')->first();

        if (!$anggaran) {
            $anggaran = AnggaranKebendaharaan::create([
                'periode_id'   => $periode->id,
                'nama'         => 'RAB ' . $periode->tahun_ajaran,
                'tahun_ajaran' => $periode->tahun_ajaran,
                'status'       => 'draft',
                'created_by'   => auth()->id(),
            ]);
        }

        try {
            $hasil = app(AnggaranImportService::class)->import($anggaran, $validated['file']);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('kebendaharaan.anggaran.show', $anggaran->id)
            ->with('sukses', 'Import RAB berhasil: ' . $hasil['kelompok'] . ' kelompok, '
                . $hasil['pos'] . ' pos belanja, ' . $hasil['pemasukan'] . ' pemasukan rencana. '
                . 'Total belanja Rp ' . number_format($hasil['pagu']) . '.');
    }

    public function storeKelompok(Request $request, $id)
    {
        $anggaran = AnggaranKebendaharaan::findOrFail($id);
        abort_if($anggaran->status === 'final', 403, 'Anggaran sudah difinalkan.');

        $validated = $request->validate([
            'kode' => 'required|integer|min:100',
            'nama' => 'required|string|max:191',
        ]);

        AnggaranKelompok::create([
            'anggaran_id' => $anggaran->id,
            'kode' => $validated['kode'],
            'nama' => $validated['nama'],
            'urutan' => AnggaranKelompok::where('anggaran_id', $anggaran->id)->max('urutan') + 1,
        ]);

        return redirect()->route('kebendaharaan.anggaran.show', $anggaran->id)
            ->with('sukses', 'Kelompok pos berhasil ditambahkan.');
    }

    public function destroyKelompok($id)
    {
        $kelompok = AnggaranKelompok::with('anggaran')->findOrFail($id);
        abort_if($kelompok->anggaran->status === 'final', 403, 'Anggaran sudah difinalkan.');

        $kelompok->delete();

        return redirect()->route('kebendaharaan.anggaran.show', $kelompok->anggaran_id)
            ->with('sukses', 'Kelompok pos dihapus.');
    }

    public function storePos(Request $request, $id)
    {
        $anggaran = AnggaranKebendaharaan::findOrFail($id);
        abort_if($anggaran->status === 'final', 403, 'Anggaran sudah difinalkan.');

        $validated = $request->validate([
            'kelompok_id' => 'required|exists:anggaran_kelompok,id',
            'kode' => 'required|integer',
            'uraian' => 'required|string|max:191',
            'volume' => 'nullable|numeric|min:0',
            'satuan' => 'nullable|string|max:50',
            'volume_2' => 'nullable|numeric|min:0',
            'satuan_2' => 'nullable|string|max:50',
            'harga_satuan' => 'required|numeric|min:0',
        ]);

        $volume = (float) ($validated['volume'] ?? 1);
        $volume2 = $validated['volume_2'] !== null && $validated['volume_2'] !== '' ? (float) $validated['volume_2'] : null;
        $harga = (float) $validated['harga_satuan'];
        $jumlah = round($volume * ($volume2 ?? 1) * $harga);

        AnggaranPos::create([
            'anggaran_id' => $anggaran->id,
            'kelompok_id' => $validated['kelompok_id'],
            'kode' => $validated['kode'],
            'uraian' => $validated['uraian'],
            'volume' => $validated['volume'] ?? 1,
            'satuan' => $validated['satuan'] ?? null,
            'volume_2' => $validated['volume_2'] ?? null,
            'satuan_2' => $validated['satuan_2'] ?? null,
            'harga_satuan' => $validated['harga_satuan'],
            'jumlah' => $jumlah,
        ]);

        return redirect()->route('kebendaharaan.anggaran.show', $anggaran->id)
            ->with('sukses', 'Pos belanja berhasil ditambahkan.');
    }

    public function updatePos(Request $request, $id)
    {
        $pos = AnggaranPos::with('anggaran')->findOrFail($id);
        abort_if($pos->anggaran->status === 'final', 403, 'Anggaran sudah difinalkan.');

        $validated = $request->validate([
            'kode' => 'required|integer',
            'uraian' => 'required|string|max:191',
            'volume' => 'nullable|numeric|min:0',
            'satuan' => 'nullable|string|max:50',
            'volume_2' => 'nullable|numeric|min:0',
            'satuan_2' => 'nullable|string|max:50',
            'harga_satuan' => 'required|numeric|min:0',
        ]);

        $volume = (float) ($validated['volume'] ?? 1);
        $volume2 = $validated['volume_2'] !== null && $validated['volume_2'] !== '' ? (float) $validated['volume_2'] : null;
        $harga = (float) $validated['harga_satuan'];

        $pos->update($validated + ['jumlah' => round($volume * ($volume2 ?? 1) * $harga)]);

        return redirect()->route('kebendaharaan.anggaran.show', $pos->anggaran_id)
            ->with('sukses', 'Pos belanja diperbarui.');
    }

    public function updateAlokasiBulan(Request $request, $id)
    {
        $pos = AnggaranPos::with('anggaran')->findOrFail($id);
        abort_if($pos->anggaran->status === 'final', 403, 'Anggaran sudah difinalkan.');

        $validated = $request->validate([
            'bulan' => 'required|array',
            'bulan.*' => 'nullable|numeric|min:0',
        ]);

        foreach (range(1, 12) as $b) {
            $nominal = (float) ($validated['bulan'][$b] ?? 0);

            if ($nominal > 0) {
                AnggaranPosBulan::updateOrCreate(
                    ['anggaran_pos_id' => $pos->id, 'bulan_fiskal' => $b],
                    ['nominal' => $nominal]
                );
            } else {
                AnggaranPosBulan::where('anggaran_pos_id', $pos->id)
                    ->where('bulan_fiskal', $b)
                    ->delete();
            }
        }

        return redirect()->route('kebendaharaan.anggaran.show', $pos->anggaran_id)
            ->with('sukses', 'Alokasi bulanan pos ' . $pos->kode . ' diperbarui.');
    }

    public function updateDetail(Request $request, $id)
    {
        $pos = AnggaranPos::with('anggaran')->findOrFail($id);
        abort_if($pos->anggaran->status === 'final', 403, 'Anggaran sudah difinalkan.');

        $validated = $request->validate([
            'kode' => 'required|integer',
            'uraian' => 'required|string|max:191',
            'volume' => 'nullable|numeric|min:0',
            'satuan' => 'nullable|string|max:50',
            'volume_2' => 'nullable|numeric|min:0',
            'satuan_2' => 'nullable|string|max:50',
            'harga_satuan' => 'required|numeric|min:0',
            'bulan' => 'sometimes|array',
            'bulan.*' => 'nullable|numeric|min:0',
        ]);

        $volume = (float) ($validated['volume'] ?? 1);
        $volume2 = $validated['volume_2'] !== null && $validated['volume_2'] !== '' ? (float) $validated['volume_2'] : null;
        $harga = (float) $validated['harga_satuan'];

        DB::transaction(function () use ($pos, $validated, $volume, $volume2, $harga) {
            $pos->update($validated + ['jumlah' => round($volume * ($volume2 ?? 1) * $harga)]);

            foreach (range(1, 12) as $b) {
                $nominal = (float) ($validated['bulan'][$b] ?? 0);

                if ($nominal > 0) {
                    AnggaranPosBulan::updateOrCreate(
                        ['anggaran_pos_id' => $pos->id, 'bulan_fiskal' => $b],
                        ['nominal' => $nominal]
                    );
                } else {
                    AnggaranPosBulan::where('anggaran_pos_id', $pos->id)
                        ->where('bulan_fiskal', $b)
                        ->delete();
                }
            }
        });

        return redirect()->route('kebendaharaan.anggaran.show', $pos->anggaran_id)
            ->with('sukses', 'Detail & alokasi bulanan pos ' . $pos->kode . ' berhasil disimpan.');
    }

    public function destroyPos($id)
    {
        $pos = AnggaranPos::with('anggaran')->findOrFail($id);
        abort_if($pos->anggaran->status === 'final', 403, 'Anggaran sudah difinalkan.');

        $pos->delete();

        return redirect()->route('kebendaharaan.anggaran.show', $pos->anggaran_id)
            ->with('sukses', 'Pos belanja dihapus.');
    }

    public function storePemasukanRen(Request $request, $id)
    {
        $anggaran = AnggaranKebendaharaan::findOrFail($id);
        abort_if($anggaran->status === 'final', 403, 'Anggaran sudah difinalkan.');

        $validated = $request->validate([
            'uraian' => 'required|string|max:191',
            'volume' => 'nullable|numeric|min:0',
            'satuan' => 'nullable|string|max:50',
            'harga_satuan' => 'required|numeric|min:0',
        ]);

        $volume = (float) ($validated['volume'] ?? 1);

        AnggaranPemasukan::create([
            'anggaran_id' => $anggaran->id,
            'urutan' => AnggaranPemasukan::where('anggaran_id', $anggaran->id)->max('urutan') + 1,
            'uraian' => $validated['uraian'],
            'volume' => $validated['volume'] ?? 1,
            'satuan' => $validated['satuan'] ?? null,
            'harga_satuan' => $validated['harga_satuan'],
            'jumlah' => round($volume * (float) $validated['harga_satuan']),
        ]);

        return redirect()->route('kebendaharaan.anggaran.show', $anggaran->id)
            ->with('sukses', 'Pemasukan rencana ditambahkan.');
    }

    public function destroyPemasukanRen($id)
    {
        $rencana = AnggaranPemasukan::with('anggaran')->findOrFail($id);
        abort_if($rencana->anggaran->status === 'final', 403, 'Anggaran sudah difinalkan.');

        $rencana->delete();

        return redirect()->route('kebendaharaan.anggaran.show', $rencana->anggaran_id)
            ->with('sukses', 'Pemasukan rencana dihapus.');
    }

    public function finalisasi($id)
    {
        $anggaran = AnggaranKebendaharaan::with(['kelompok.pos.posBulan'])->findOrFail($id);

        if ($anggaran->pos()->count() === 0 || $anggaran->kelompok()->count() === 0) {
            return redirect()->route('kebendaharaan.anggaran.show', $anggaran->id)
                ->with('error', 'Belum ada kelompok/pos. Tambahkan dulu sebelum memfinalkan.');
        }

        $tidakSeimbang = [];
        foreach ($anggaran->kelompok as $kelompok) {
            foreach ($kelompok->pos as $pos) {
                $alokasi = (float) $pos->posBulan->sum('nominal');
                $jumlah = (float) $pos->jumlah;
                if (abs($alokasi - $jumlah) > 0.5) {
                    $tidakSeimbang[] = $pos->kode . ' ' . $pos->uraian . ' — alokasi 12 bln Rp '
                        . number_format($alokasi, 0, ',', '.') . ' ≠ jumlah pos Rp '
                        . number_format($jumlah, 0, ',', '.');
                }
            }
        }

        if ($tidakSeimbang !== []) {
            return redirect()->route('kebendaharaan.anggaran.show', $anggaran->id)
                ->with('error', 'RAB tidak bisa difinalkan: distribusi alokasi bulanan belum seimbang dengan jumlah tiap pos.')
                ->with('error_detail', $tidakSeimbang);
        }

        $anggaran->update(['status' => 'final']);

        return redirect()->route('kebendaharaan.anggaran.show', $anggaran->id)
            ->with('sukses', 'RAB difinalkan. Struktur anggaran kini terkunci.');
    }

    public function buka($id)
    {
        $anggaran = AnggaranKebendaharaan::findOrFail($id);

        if ($anggaran->status !== 'final') {
            return redirect()->route('kebendaharaan.anggaran.show', $anggaran->id)
                ->with('error', 'Hanya anggaran berstatus Final yang bisa dibuka kembali.');
        }

        $anggaran->update(['status' => 'draft']);

        return redirect()->route('kebendaharaan.anggaran.show', $anggaran->id)
            ->with('sukses', 'Anggaran dibuka kembali menjadi Draft.');
    }

    public function pdf($id)
    {
        $anggaran = AnggaranKebendaharaan::with(['periode', 'kelompok.pos', 'pemasukanRencana'])
            ->findOrFail($id);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.kebendaharaan.anggaran-pdf', [
                'anggaran' => $anggaran,
            ])
            ->setPaper('a4', 'landscape');

        return $pdf->download('RAB_' . str_replace('/', '-', $anggaran->tahun_ajaran) . '.pdf');
    }
}