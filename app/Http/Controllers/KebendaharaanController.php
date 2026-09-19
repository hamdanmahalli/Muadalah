<?php

namespace App\Http\Controllers;

use App\Models\AnggaranKebendaharaan;
use App\Models\AnggaranPemasukan;
use App\Models\AnggaranPos;
use App\Models\Barang;
use App\Models\DistribusiBarang;
use App\Models\LaporanPengeluaran;
use App\Models\LaporanPengeluaranItem;
use App\Models\Pemasukan;
use App\Models\Pencairan;
use App\Models\PenjualanBarang;
use App\Models\Pinjaman;
use App\Models\SetoranBarang;
use Illuminate\Support\Facades\DB;

class KebendaharaanController extends Controller
{
    public function index()
    {
        $periode = get_periode_aktif();
        if (!$periode) {
            return redirect()->back()->with('error', 'Tidak ada periode aktif.');
        }

        $anggaran = AnggaranKebendaharaan::with(['kelompok.pos', 'pemasukanRencana'])
            ->where('periode_id', $periode->id)
            ->orderBy('id', 'desc')
            ->first();

        $pemasukanTotal = (float) Pemasukan::where('periode_id', $periode->id)->sum('jumlah');
        $realisasiTotal = (float) LaporanPengeluaran::where('periode_id', $periode->id)
            ->where('status', 'disetujui')->sum('nominal');
        $saldo = $pemasukanTotal - $realisasiTotal;

        $dCair = [
            'diajukan' => Pencairan::where('periode_id', $periode->id)->where('status', 'diajukan')->count(),
            'dibayar' => Pencairan::where('periode_id', $periode->id)->where('status', 'dibayar')->count(),
            'ditolak' => Pencairan::where('periode_id', $periode->id)->where('status', 'ditolak')->count(),
        ];
        $dLaporan = [
            'diajukan' => LaporanPengeluaran::where('periode_id', $periode->id)->where('status', 'diajukan')->count(),
        ];

        $pinjamanAktif = Pinjaman::with('peminjam')->where('periode_id', $periode->id)->where('status', 'aktif')->get();
        $sisaPinjamanTotal = $pinjamanAktif->sum(fn ($p) => $p->sisa());

        $stokBarang = Barang::withCount([])->get()->filter(fn ($b) => $b->stok() > 0);
        $nilaistok = $stokBarang->sum(fn ($b) => $b->stok() * $b->harga_beli);

        return view('admin.kebendaharaan.beranda', compact(
            'periode', 'anggaran', 'pemasukanTotal', 'realisasiTotal', 'saldo',
            'dCair', 'dLaporan', 'pinjamanAktif', 'sisaPinjamanTotal', 'stokBarang', 'nilaistok'
        ));
    }

    public function rekap()
    {
        $periode = get_periode_aktif();
        if (!$periode) {
            return redirect()->back()->with('error', 'Tidak ada periode aktif.');
        }

        $data = $this->rekapData($periode);

        return view('admin.kebendaharaan.rekap', compact('periode', 'data'));
    }

    public function rekapPdf()
    {
        $periode = get_periode_aktif();
        if (!$periode) {
            return redirect()->back()->with('error', 'Tidak ada periode aktif.');
        }

        $data = $this->rekapData($periode);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.kebendaharaan.rekap-pdf', [
                'periode' => $periode,
                'data' => $data,
            ])
            ->setPaper('a4', 'portrait');

        return $pdf->download('Rekap_Kebendaharaan_' . str_replace('/', '-', $periode->tahun_ajaran) . '.pdf');
    }

    /**
     * Merangkum seluruh data rekap (dipakai halaman rekap & PDF).
     */
    public function rekapData($periode): array
    {
        $anggaran = AnggaranKebendaharaan::with(['kelompok.pos', 'pemasukanRencana'])
            ->where('periode_id', $periode->id)
            ->orderBy('id', 'desc')
            ->first();

        $pemasukan = Pemasukan::where('periode_id', $periode->id)->get();
        $realisasiItems = LaporanPengeluaranItem::whereHas('laporan', function ($q) use ($periode) {
            $q->where('periode_id', $periode->id)->where('status', 'disetujui');
        })->get();

        $pemasukanTotal = (float) $pemasukan->sum('jumlah');
        $realisasiTotal = (float) $realisasiItems->sum('nominal');
        $pencairanDibayar = (float) Pencairan::where('periode_id', $periode->id)
            ->where('status', 'dibayar')->sum('nominal');

        // Rekap belanja per kelompok & per pos
        $rekapBelanja = [];
        $nilaiRealisasiPerPos = $realisasiItems->groupBy('anggaran_pos_id')->map(fn ($g) => (float) $g->sum('nominal'));
        if ($anggaran) {
            foreach ($anggaran->kelompok as $kelompok) {
                $rows = [];
                foreach ($kelompok->pos as $pos) {
                    $rows[] = (object) [
                        'kode' => $pos->kode,
                        'uraian' => $pos->uraian,
                        'pagu' => (float) $pos->jumlah,
                        'realisasi' => (float) ($nilaiRealisasiPerPos[$pos->id] ?? 0),
                        'sisa' => max(0, (float) $pos->jumlah - (float) ($nilaiRealisasiPerPos[$pos->id] ?? 0)),
                        'persen' => $pos->jumlah > 0 ? round(((float) ($nilaiRealisasiPerPos[$pos->id] ?? 0)) / (float) $pos->jumlah * 100, 1) : 0,
                    ];
                }
                $rekapBelanja[] = (object)[
                    'kode' => $kelompok->kode,
                    'nama' => $kelompok->nama,
                    'rows' => $rows,
                    'pagu' => (float) $kelompok->pos->sum('jumlah'),
                    'realisasi' => (float) collect($rows)->sum('realisasi'),
                ];
            }
        }

        // Rekap pemasukan: rencana vs realisasi
        $rekapPemasukan = [];
        $sisaPemasukan = collect();
        if ($anggaran) {
            foreach ($anggaran->pemasukanRencana as $rencana) {
                $real = (float) $pemasukan->where('anggaran_pemasukan_id', $rencana->id)->sum('jumlah');
                $rekapPemasukan[] = (object) [
                    'uraian' => $rencana->uraian,
                    'rencana' => (float) $rencana->jumlah,
                    'realisasi' => $real,
                ];
            }
        }
        $sisaPemasukan = $pemasukan->whereNull('anggaran_pemasukan_id');

        // Sisi toko / koperasi
        $barang = Barang::all();
        $nilaiStok = $barang->sum(fn ($b) => $b->stok() * $b->harga_beli);
        $distribusi = DistribusiBarang::with('items')->get();
        $piutangWaliKelas = $distribusi->sum(fn ($d) => $d->sisaBelumDisetor());
        $totalSetoran = (float) SetoranBarang::sum('total');
        $penjualan = PenjualanBarang::with('barang')->where('status', 'lunas')->get();
        $totalPenjualan = $penjualan->sum(fn ($p) => $p->qty * $p->harga_jual);
        $bebanPokok = $penjualan->sum(fn ($p) => $p->qty * (float) ($p->barang?->harga_beli ?? 0));
        $labaKotor = $totalPenjualan - $bebanPokok;
        $pinjaman = Pinjaman::where('periode_id', $periode->id)->get();
        $sisaPinjaman = $pinjaman->sum(fn ($p) => $p->sisa());

        return [
            'periode' => $periode,
            'anggaran' => $anggaran,
            'pemasukanTotal' => $pemasukanTotal,
            'realisasiTotal' => $realisasiTotal,
            'pencairanDibayar' => $pencairanDibayar,
            'saldo' => $pemasukanTotal - $realisasiTotal,
            'rekapBelanja' => $rekapBelanja,
            'rekapPemasukan' => $rekapPemasukan,
            'sisaPemasukan' => $sisaPemasukan,
            'nilaiStok' => $nilaiStok,
            'piutangWaliKelas' => $piutangWaliKelas,
            'totalSetoran' => $totalSetoran,
            'totalPenjualan' => $totalPenjualan,
            'labaKotor' => $labaKotor,
            'sisaPinjaman' => $sisaPinjaman,
            'pemasukanBarang' => $pemasukan->whereNotNull('setoran_barang_id')->sum('jumlah'),
        ];
    }
}