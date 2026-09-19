<?php

namespace App\Http\Controllers;

use App\Models\AnggaranKelompok;
use App\Models\AnggaranPos;
use App\Models\AnggaranPosBulan;
use App\Models\Pencairan;
use App\Models\PencairanItem;
use App\Models\Pinjaman;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PencairanController extends Controller
{
    public function index(Request $request)
    {
        $periode = get_periode_aktif();
        $query = Pencairan::with(['pos', 'pengaju', 'items.pos']);

        if ($periode) {
            $query->where('periode_id', $periode->id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $list = $query->orderBy('id', 'desc')->get();

        // Kelompok pos + anak-anak pos dari anggaran final periode aktif,
        // disiapkan sebagai data "kasir" (alokasi & terpakai per bulan).
        $kelompokAktif = AnggaranKelompok::with(['pos.posBulan'])
            ->whereHas('anggaran', function ($q) use ($periode) {
                $q->where('periode_id', $periode?->id)->where('status', 'final');
            })
            ->orderBy('urutan')
            ->orderBy('kode')
            ->get();

        $posIds = $kelompokAktif->pluck('pos')->flatten()->pluck('id');
        $terpakaiPerBulan = collect();
        if ($periode && $posIds->isNotEmpty()) {
            $terpakaiPerBulan = PencairanItem::selectRaw('anggaran_pos_id, bulan_fiskal, sum(nominal) as total')
                ->whereIn('anggaran_pos_id', $posIds)
                ->whereHas('pencairan', fn ($q) => $q->where('periode_id', $periode->id)
                    ->whereIn('status', ['diajukan', 'dibayar']))
                ->groupBy('anggaran_pos_id', 'bulan_fiskal')
                ->get()
                ->groupBy('anggaran_pos_id');
        }

        $kasir = $kelompokAktif->map(function ($k) use ($terpakaiPerBulan) {
            $pos = $k->pos->map(function ($p) use ($terpakaiPerBulan) {
                $alokasi = [];
                $terpakai = [];
                foreach ($p->posBulan as $pb) {
                    $alokasi[(int) $pb->bulan_fiskal] = (float) $pb->nominal;
                }
                foreach (($terpakaiPerBulan->get($p->id) ?? collect()) as $g) {
                    if ($g->bulan_fiskal) {
                        $terpakai[(int) $g->bulan_fiskal] = (float) $g->total;
                    }
                }
                return [
                    'id'      => (int) $p->id,
                    'kode'    => (string) $p->kode,
                    'uraian'  => $p->uraian,
                    'alokasi' => $alokasi,
                    'terpakai' => $terpakai,
                ];
            })->values();

            return ['kode' => $k->kode, 'nama' => $k->nama, 'pos' => $pos];
        })->values();

        return view('admin.kebendaharaan.pencairan-index', compact(
            'list', 'kasir'
        ));
    }

    public function store(Request $request)
    {
        $periode = get_periode_aktif();
        if (!$periode) {
            return redirect()->back()->with('error', 'Tidak ada periode aktif.');
        }

        $validated = $request->validate([
            'jenis'           => 'required|in:rutin,modal_toko',
            'tanggal_aju'     => 'required|date',
            'keperluan'       => 'required|string',
            'bulan_fiskal'    => 'nullable|integer|between:1,12',
            'nominal'         => 'nullable',
            'items'           => 'nullable|array',
            'items.*.pos_id'  => 'required|integer|exists:anggaran_pos,id',
            'items.*.nominal' => 'required|numeric|min:1',
        ]);

        if ($validated['jenis'] === 'rutin') {
            return $this->storeRutin($periode, $validated);
        }

        return $this->storeModalToko($periode, $validated);
    }

    /**
     * SPP rutin: satu SPP = satu bulan anggaran, boleh memuat banyak pos.
     * Melebihi alokasi bulanan DIPERBOLEHKAN, cukup diberi peringatan.
     */
    private function storeRutin($periode, array $validated)
    {
        if (!$validated['bulan_fiskal']) {
            return back()->with('error', 'Pilih bulan anggaran untuk SPP rutin.')->withInput();
        }
        if (empty($validated['items']) || !is_array($validated['items'])) {
            return back()->with('error', 'Tambahkan minimal satu pos belanja pada SPP.')->withInput();
        }

        $posIds = collect($validated['items'])->pluck('pos_id')->unique();
        $pos = AnggaranPos::with('anggaran')->whereKey($posIds)->get()->keyBy('id');

        $items = [];
        $jiu = 0;
        $warnings = [];

        foreach ($validated['items'] as $baris) {
            $posId = (int) $baris['pos_id'];
            $nominal = (float) rupiah_to_int($baris['nominal']);
            if ($nominal <= 0) {
                continue;
            }

            $p = $pos->get($posId);
            if (!$p || $p->anggaran->status !== 'final') {
                return back()->with('error', 'Pos "' . ($p->uraian ?? $posId) . '" harus dari anggaran yang sudah final.')
                    ->withInput();
            }

            $alokasi = (float) (AnggaranPosBulan::where('anggaran_pos_id', $posId)
                ->where('bulan_fiskal', (int) $validated['bulan_fiskal'])->value('nominal') ?? 0);
            $terpakai = (float) (PencairanItem::where('anggaran_pos_id', $posId)
                ->where('bulan_fiskal', (int) $validated['bulan_fiskal'])
                ->whereHas('pencairan', fn ($q) => $q->where('periode_id', $periode->id)
                    ->whereIn('status', ['diajukan', 'dibayar']))
                ->sum('nominal') ?? 0);

            $sisa = $alokasi - $terpakai;
            if ($nominal > $sisa) {
                $warnings[] = $p->kode . ' ' . $p->uraian . ': alokasi bulan ' . bulan_fiskal_label((int) $validated['bulan_fiskal'])
                    . ' Rp ' . number_format($alokasi, 0, ',', '.')
                    . ', tersisa Rp ' . number_format(max($sisa, 0), 0, ',', '.')
                    . ', diajukan Rp ' . number_format($nominal, 0, ',', '.') . ' (melebihi alokasi).';
            }

            $items[] = ['pos' => $p, 'nominal' => $nominal];
            $jiu += $nominal;
        }

        if ($items === []) {
            return back()->with('error', 'Nominal semua baris harus lebih dari 0.')->withInput();
        }

        $pencairan = DB::transaction(function () use ($periode, $validated, $items, $jiu) {
            $pencairan = Pencairan::create([
                'kode'          => Pencairan::nextKode($periode->tahun),
                'periode_id'    => $periode->id,
                'pos_id'        => null,
                'bulan_fiskal'  => (int) $validated['bulan_fiskal'],
                'jenis'         => 'rutin',
                'tanggal_aju'   => $validated['tanggal_aju'],
                'nominal'       => $jiu,
                'keperluan'     => $validated['keperluan'],
                'status'        => 'diajukan',
                'diajukan_oleh' => auth()->id(),
            ]);

            foreach ($items as $it) {
                PencairanItem::create([
                    'pencairan_id'    => $pencairan->id,
                    'anggaran_pos_id' => $it['pos']->id,
                    'bulan_fiskal'    => (int) $validated['bulan_fiskal'],
                    'nominal'         => $it['nominal'],
                ]);
            }

            return $pencairan;
        });

        if ($warnings !== []) {
            return redirect()->route('kebendaharaan.pencairan.index')
                ->with('warning', 'SPP ' . $pencairan->kode . ' diajukan DENGAN peringatan melebihi alokasi bulan:')
                ->with('warning_detail', $warnings);
        }

        return redirect()->route('kebendaharaan.pencairan.index')
            ->with('sukses', 'SPP ' . $pencairan->kode . ' diajukan untuk divalidasi & dibayar.');
    }

    private function storeModalToko($periode, array $validated)
    {
        $nominal = rupiah_to_int($validated['nominal'] ?? 0);
        if ($nominal <= 0) {
            return back()->with('error', 'Nominal modal toko harus lebih dari 0.')->withInput();
        }

        $pencairan = Pencairan::create([
            'kode'          => Pencairan::nextKode($periode->tahun),
            'periode_id'    => $periode->id,
            'pos_id'        => null,
            'bulan_fiskal'  => null,
            'jenis'         => 'modal_toko',
            'tanggal_aju'   => $validated['tanggal_aju'],
            'nominal'       => $nominal,
            'keperluan'     => $validated['keperluan'],
            'status'        => 'diajukan',
            'diajukan_oleh' => auth()->id(),
        ]);

        return redirect()->route('kebendaharaan.pencairan.index')
            ->with('sukses', 'SPP ' . $pencairan->kode . ' (modal toko) diajukan untuk divalidasi & dibayar.');
    }

    /**
     * Validasi & pembayaran sekaligus: hanya SPP berstatus Diajukan yang bisa dibayar.
     */
    public function bayar($id)
    {
        $pencairan = Pencairan::findOrFail($id);

        if ($pencairan->status !== 'diajukan') {
            return redirect()->route('kebendaharaan.pencairan.index')
                ->with('error', 'Hanya SPP berstatus Diajukan yang bisa dibayar.');
        }

        DB::transaction(function () use ($pencairan) {
            $pencairan->update([
                'status'      => 'dibayar',
                'dibayar_oleh' => auth()->id(),
                'dibayar_at'  => now(),
            ]);

            // Pencairan modal toko otomatis menjadi pinjaman (buku belanja).
            if ($pencairan->jenis === 'modal_toko' && !$pencairan->pinjaman) {
                Pinjaman::create([
                    'kode'            => Pinjaman::nextKode($pencairan->periode->tahun),
                    'periode_id'      => $pencairan->periode_id,
                    'pencairan_id'    => $pencairan->id,
                    'peminjam_user_id' => $pencairan->diajukan_oleh,
                    'jumlah'          => (float) $pencairan->jumlah,
                    'tanggal'         => $pencairan->tanggal_aju,
                    'keperluan'       => $pencairan->keperluan,
                    'status'          => 'aktif',
                ]);
            }
        });

        return redirect()->route('kebendaharaan.pencairan.index')
            ->with('sukses', 'SPP ' . $pencairan->kode . ' dibayar dan dicatat sebagai pengeluaran kas.');
    }

    public function tolak(Request $request, $id)
    {
        $pencairan = Pencairan::findOrFail($id);

        if ($pencairan->status !== 'diajukan') {
            return redirect()->route('kebendaharaan.pencairan.index')
                ->with('error', 'Hanya SPP berstatus Diajukan yang bisa ditolak.');
        }

        $validated = $request->validate(['tolak_alasan' => 'required|string']);

        $pencairan->update([
            'status'       => 'ditolak',
            'tolak_alasan' => $validated['tolak_alasan'],
        ]);

        return redirect()->route('kebendaharaan.pencairan.index')
            ->with('sukses', 'SPP ' . $pencairan->kode . ' ditolak.');
    }
}