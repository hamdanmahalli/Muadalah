<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\DistribusiBarang;
use App\Models\DistribusiBarangItem;
use App\Models\Kelas;
use App\Models\PembelianBarang;
use App\Models\PembelianBarangItem;
use App\Models\Pemasukan;
use App\Models\Pencairan;
use App\Models\PenjualanBarang;
use App\Models\Pinjaman;
use App\Models\SetoranBarang;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TokoController extends Controller
{
    // ──────────────────────────────────────────────────
    //  DASHBOARD
    // ──────────────────────────────────────────────────

    public function index()
    {
        $barang = Barang::all();
        $stokValues = $barang->filter(fn ($b) => $b->stok() > 0);
        $totalNilaiStok = $stokValues->sum(fn ($b) => $b->stok() * $b->harga_beli);

        $distribusiOut = DistribusiBarang::with('waliKelas')
            ->get()
            ->filter(fn ($d) => $d->sisaBelumDisetor() > 0);
        $totalPiutang = $distribusiOut->sum(fn ($d) => $d->sisaBelumDisetor());

        $pinjaman = Pinjaman::where('status', 'aktif')->get();
        $sisaPinjaman = $pinjaman->sum(fn ($p) => $p->sisa());

        $totalSetoran = (float) SetoranBarang::sum('total');
        $totalPenjualan = (float) PenjualanBarang::where('status', 'lunas')
            ->sum(DB::raw('qty * harga_jual'));
        $bebanPokok = (float) PenjualanBarang::with('barang')
            ->where('status', 'lunas')
            ->get()
            ->sum(fn ($p) => $p->qty * (float) ($p->barang?->harga_beli ?? 0));

        return view('admin.kebendaharaan.toko-beranda', compact(
            'barang', 'stokValues', 'totalNilaiStok', 'distribusiOut',
            'totalPiutang', 'pinjaman', 'sisaPinjaman', 'totalSetoran',
            'totalPenjualan', 'bebanPokok'
        ));
    }

    // ──────────────────────────────────────────────────
    //  MASTER BARANG
    // ──────────────────────────────────────────────────

    public function barangIndex()
    {
        $list = Barang::orderBy('kode')->get();
        return view('admin.kebendaharaan.toko-barang-index', compact('list'));
    }

    public function barangStore(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:191',
            'satuan' => 'nullable|string|max:50',
            'harga_beli' => 'required',
            'harga_jual' => 'required',
            'keterangan' => 'nullable|string',
        ]);

        Barang::create([
            'kode' => Barang::nextKode(),
            'nama' => $validated['nama'],
            'satuan' => $validated['satuan'] ?? 'Pcs',
            'harga_beli' => rupiah_to_int($validated['harga_beli']),
            'harga_jual' => rupiah_to_int($validated['harga_jual']),
            'keterangan' => $validated['keterangan'] ?? null,
        ]);

        return redirect()->route('kebendaharaan.toko.barang.index')
            ->with('sukses', 'Barang/buku ditambahkan.');
    }

    public function barangUpdate(Request $request, $id)
    {
        $barang = Barang::findOrFail($id);

        $validated = $request->validate([
            'nama' => 'required|string|max:191',
            'satuan' => 'nullable|string|max:50',
            'harga_beli' => 'required',
            'harga_jual' => 'required',
            'keterangan' => 'nullable|string',
        ]);

        $barang->update([
            'nama' => $validated['nama'],
            'satuan' => $validated['satuan'] ?? $barang->satuan,
            'harga_beli' => rupiah_to_int($validated['harga_beli']),
            'harga_jual' => rupiah_to_int($validated['harga_jual']),
            'keterangan' => $validated['keterangan'] ?? $barang->keterangan,
        ]);

        return redirect()->route('kebendaharaan.toko.barang.index')
            ->with('sukses', 'Data barang diperbarui.');
    }

    public function barangToggle($id)
    {
        $barang = Barang::findOrFail($id);
        $barang->update(['is_active' => !$barang->is_active]);

        return redirect()->route('kebendaharaan.toko.barang.index')
            ->with('sukses', 'Status barang diubah.');
    }

    public function barangDestroy($id)
    {
        $barang = Barang::findOrFail($id);

        if ($barang->stok() > 0) {
            return redirect()->route('kebendaharaan.toko.barang.index')
                ->with('error', 'Tidak bisa menghapus barang yang masih memiliki stok.');
        }

        $barang->delete();

        return redirect()->route('kebendaharaan.toko.barang.index')
            ->with('sukses', 'Barang dihapus.');
    }

    // ──────────────────────────────────────────────────
    //  PEMBELIAN (stok masuk)
    // ──────────────────────────────────────────────────

    public function pembelianIndex()
    {
        $list = PembelianBarang::with('pencairan')
            ->orderBy('id', 'desc')
            ->get();

        return view('admin.kebendaharaan.toko-pembelian-index', compact('list'));
    }

    public function pembelianCreate()
    {
        $periode = get_periode_aktif();
        $barangList = Barang::where('is_active', true)->orderBy('nama')->get();
        $pencairanModal = $periode
            ? Pencairan::where('periode_id', $periode->id)
                ->where('jenis', 'modal_toko')
                ->where('status', 'dibayar')
                ->orderBy('id', 'desc')
                ->get()
            : collect();

        return view('admin.kebendaharaan.toko-pembelian-form', compact('barangList', 'pencairanModal'));
    }

    public function pembelianStore(Request $request)
    {
        $periode = get_periode_aktif();
        if (!$periode) {
            return redirect()->back()->with('error', 'Tidak ada periode aktif.');
        }

        $validated = $request->validate([
            'pencairan_id' => 'nullable|exists:pencairan,id',
            'tanggal' => 'required|date',
            'keterangan' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.barang_id' => 'required|exists:barang,id',
            'items.*.qty' => 'required|numeric|min:1',
            'items.*.harga_beli' => 'required',
            'items.*.harga_jual' => 'nullable',
        ]);

        DB::transaction(function () use ($validated, $periode) {
            $total = 0;
            $items = [];
            foreach ($validated['items'] as $i => $row) {
                $hargaJual = $row['harga_jual'] !== null && $row['harga_jual'] !== ''
                    ? rupiah_to_int($row['harga_jual'])
                    : (float) Barang::find($row['barang_id'])->harga_jual;
                $hargaBeli = rupiah_to_int($row['harga_beli']);
                $qty = (float) $row['qty'];
                $total += $qty * $hargaBeli;
                $items[] = [
                    'barang_id' => $row['barang_id'],
                    'qty' => $qty,
                    'harga_beli' => $hargaBeli,
                    'harga_jual' => $hargaJual,
                ];
            }

            $pb = PembelianBarang::create([
                'kode' => PembelianBarang::nextKode($periode->tahun),
                'pencairan_id' => $validated['pencairan_id'] ?? null,
                'tanggal' => $validated['tanggal'],
                'total' => $total,
                'keterangan' => $validated['keterangan'] ?? null,
                'created_by' => auth()->id(),
            ]);

            foreach ($items as $item) {
                PembelianBarangItem::create($item + ['pembelian_barang_id' => $pb->id]);
            }
        });

        return redirect()->route('kebendaharaan.toko.pembelian.index')
            ->with('sukses', 'Pembelian dicatat. Stok bertambah.');
    }

    public function pembelianShow($id)
    {
        $pb = PembelianBarang::with(['items.barang', 'pencairan'])->findOrFail($id);
        return view('admin.kebendaharaan.toko-pembelian-show', compact('pb'));
    }

    // ──────────────────────────────────────────────────
    //  DISTRIBUSI (stok keluar ke wali kelas)
    // ──────────────────────────────────────────────────

    public function distribusiIndex()
    {
        $list = DistribusiBarang::with(['waliKelas', 'items.barang'])
            ->orderBy('id', 'desc')
            ->get();

        return view('admin.kebendaharaan.toko-distribusi-index', compact('list'));
    }

    public function distribusiCreate()
    {
        $barangList = Barang::where('is_active', true)->get()
            ->filter(fn ($b) => $b->stok() > 0)
            ->sortBy('nama')
            ->values();
        $guruList = \App\Models\Guru::orderBy('nama_guru')->get();
        $kelasList = Kelas::orderBy('nama_kelas')->get();

        return view('admin.kebendaharaan.toko-distribusi-form', compact('barangList', 'guruList', 'kelasList'));
    }

    public function distribusiStore(Request $request)
    {
        $periode = get_periode_aktif();
        if (!$periode) {
            return redirect()->back()->with('error', 'Tidak ada periode aktif.');
        }

        $validated = $request->validate([
            'wali_kelas_id' => 'nullable|exists:gurus,id',
            'kelas_id' => 'nullable|exists:kelas,id',
            'tanggal' => 'required|date',
            'keterangan' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.barang_id' => 'required|exists:barang,id',
            'items.*.qty' => 'required|numeric|min:1',
            'items.*.harga_jual' => 'nullable',
        ]);

        // Cek stok tersedia per barang.
        $qtyPerBarang = [];
        foreach ($validated['items'] as $row) {
            $qtyPerBarang[$row['barang_id']] = ($qtyPerBarang[$row['barang_id']] ?? 0) + (float) $row['qty'];
        }
        foreach ($qtyPerBarang as $bid => $total) {
            $stok = Barang::find($bid)->stok();
            if ($total > $stok) {
                return redirect()->back()
                    ->with('error', 'Stok barang ' . Barang::find($bid)->kode .
                        ' tidak cukup. Sisa stok: ' . $stok . '.')
                    ->withInput();
            }
        }

        DB::transaction(function () use ($validated, $periode) {
            $dsb = DistribusiBarang::create([
                'kode' => DistribusiBarang::nextKode($periode->tahun),
                'wali_kelas_id' => $validated['wali_kelas_id'] ?? null,
                'kelas_id' => $validated['kelas_id'] ?? null,
                'tanggal' => $validated['tanggal'],
                'keterangan' => $validated['keterangan'] ?? null,
                'created_by' => auth()->id(),
            ]);

            foreach ($validated['items'] as $row) {
                $hargaJual = $row['harga_jual'] !== null && $row['harga_jual'] !== ''
                    ? rupiah_to_int($row['harga_jual'])
                    : (float) Barang::find($row['barang_id'])->harga_jual;
                DistribusiBarangItem::create([
                    'distribusi_barang_id' => $dsb->id,
                    'barang_id' => $row['barang_id'],
                    'qty' => (float) $row['qty'],
                    'harga_jual' => $hargaJual,
                ]);
            }
        });

        return redirect()->route('kebendaharaan.toko.distribusi.index')
            ->with('sukses', 'Distribusi dicatat. Stok berkurang.');
    }

    public function distribusiShow($id)
    {
        $dsb = DistribusiBarang::with(['waliKelas', 'items.barang', 'setoran', 'penjualan.barang', 'penjualan.siswa'])
            ->findOrFail($id);

        $barangDistribusi = $dsb->items->pluck('barang')->unique('id');
        $siswaDiKelas = $dsb->kelas_id
            ? Siswa::where('kelas_id', $dsb->kelas_id)->orderBy('nama_siswa')->get()
            : collect();
        $penjualanBelumLunas = $dsb->penjualan()->where('status', 'belum')->get();
        $pinjamanAktif = Pinjaman::where('status', 'aktif')->orderBy('id')->get();

        return view('admin.kebendaharaan.toko-distribusi-show', compact(
            'dsb', 'barangDistribusi', 'siswaDiKelas', 'penjualanBelumLunas', 'pinjamanAktif'
        ));
    }

    public function distribusiDestroy($id)
    {
        $dsb = DistribusiBarang::with('penjualan', 'setoran')->findOrFail($id);

        if ($dsb->penjualan()->count() > 0 || $dsb->setoran()->count() > 0) {
            return redirect()->route('kebendaharaan.toko.distribusi.index')
                ->with('error', 'Distribusi sudah memiliki data penjualan/setoran. Tidak bisa dihapus.');
        }

        $dsb->delete();

        return redirect()->route('kebendaharaan.toko.distribusi.index')
            ->with('sukses', 'Distribusi dihapus. Stok kembali.');
    }

    // ──────────────────────────────────────────────────
    //  PENJUALAN (per murid, uang dipegang wali kelas)
    // ──────────────────────────────────────────────────

    public function penjualanStore(Request $request)
    {
        $validated = $request->validate([
            'distribusi_barang_id' => 'required|exists:distribusi_barang,id',
            'siswa_id' => 'required|exists:siswas,id',
            'barang_id' => 'required|exists:barang,id',
            'qty' => 'required|numeric|min:1',
            'harga_jual' => 'nullable',
            'tanggal' => 'required|date',
        ]);

        $dsb = DistribusiBarang::findOrFail($validated['distribusi_barang_id']);

        $hargaJual = $validated['harga_jual'] !== null && $validated['harga_jual'] !== ''
            ? rupiah_to_int($validated['harga_jual'])
            : (float) Barang::find($validated['barang_id'])->harga_jual;

        PenjualanBarang::create([
            'distribusi_barang_id' => $dsb->id,
            'siswa_id' => $validated['siswa_id'],
            'barang_id' => $validated['barang_id'],
            'qty' => (float) $validated['qty'],
            'harga_jual' => $hargaJual,
            'tanggal' => $validated['tanggal'],
            'status' => 'belum',
            'dicatat_oleh' => auth()->id(),
        ]);

        return redirect()->route('kebendaharaan.toko.distribusi.show', $dsb->id)
            ->with('sukses', 'Penjualan untuk siswa dicatat. Uang masih dipegang wali kelas.');
    }

    public function penjualanLunas($id)
    {
        $penjualan = PenjualanBarang::findOrFail($id);
        $penjualan->update(['status' => 'lunas']);

        return redirect()->route('kebendaharaan.toko.distribusi.show', $penjualan->distribusi_barang_id)
            ->with('sukses', 'Penjualan ditandai lunas (uang sudah diterima wali kelas).');
    }

    // ──────────────────────────────────────────────────
    //  SETORAN (wali kelas → staf bendahara)
    // ──────────────────────────────────────────────────

    public function setoranStore(Request $request)
    {
        $periode = get_periode_aktif();
        if (!$periode) {
            return redirect()->back()->with('error', 'Tidak ada periode aktif.');
        }

        $validated = $request->validate([
            'distribusi_barang_id' => 'required|exists:distribusi_barang,id',
            'pinjaman_id' => 'required|exists:pinjaman,id',
            'tanggal' => 'required|date',
            'total' => 'required',
            'keterangan' => 'nullable|string',
        ]);

        $total = rupiah_to_int($validated['total']);
        if ($total <= 0) {
            return redirect()->back()->with('error', 'Total harus lebih dari 0.')->withInput();
        }

        $dsb = DistribusiBarang::find($validated['distribusi_barang_id']);
        if ($dsb && $total > $dsb->sisaBelumDisetor()) {
            return redirect()->back()
                ->with('error', 'Total setoran melebihi sisa piutang wali kelas (Rp ' .
                    number_format($dsb->sisaBelumDisetor(), 0, ',', '.') . ').')
                ->withInput();
        }

        $pinjaman = Pinjaman::find($validated['pinjaman_id']);

        DB::transaction(function () use ($validated, $total, $dsb, $pinjaman, $periode) {
            // 1. Buat setoran
            $std = SetoranBarang::create([
                'kode' => SetoranBarang::nextKode($periode->tahun),
                'distribusi_barang_id' => $dsb?->id,
                'pinjaman_id' => $pinjaman?->id,
                'wali_kelas_id' => $dsb?->wali_kelas_id,
                'tanggal' => $validated['tanggal'],
                'total' => $total,
                'keterangan' => $validated['keterangan'] ?? null,
                'created_by' => auth()->id(),
            ]);

            // 2. Buat pemasukan otomatis
            Pemasukan::create([
                'periode_id' => $periode->id,
                'setoran_barang_id' => $std->id,
                'uraian' => 'Setoran penjualan buku — ' . $std->kode,
                'tanggal' => $validated['tanggal'],
                'jumlah' => $total,
                'keterangan' => 'Otomatis dari setoran toko buku',
                'created_by' => auth()->id(),
            ]);

            // 3. Tandai penjualan belum lunas pada distribusi menjadi lunas secara proporsional (sisa piutang utama sudah dikurangi).
            if ($dsb) {
                $sisaSetor = $total;
                $belumLunas = $dsb->penjualan()->where('status', 'belum')->get();
                foreach ($belumLunas as $pj) {
                    $hutang = (float) $pj->qty * (float) $pj->harga_jual;
                    if ($sisaSetor <= 0) break;
                    if ($sisaSetor >= $hutang) {
                        $pj->update(['status' => 'lunas']);
                        $sisaSetor -= $hutang;
                    }
                }
            }

            // 4. Update status pinjaman
            if ($pinjaman && $pinjaman->fresh()->sisa() <= 0) {
                $pinjaman->update(['status' => 'lunas']);
            }
        });

        $redirect = $dsb
            ? route('kebendaharaan.toko.distribusi.show', $dsb->id)
            : route('kebendaharaan.toko.index');

        return redirect($redirect)
            ->with('sukses', 'Setoran dicatat. Pemasukan otomatis tercatat di buku bendahara.');
    }

    public function setoranDestroy($id)
    {
        $std = SetoranBarang::findOrFail($id);

        DB::transaction(function () use ($std) {
            // Hapus pemasukan yang terhubung
            Pemasukan::where('setoran_barang_id', $std->id)->delete();

            $pinjaman = $std->pinjaman;
            $std->delete();

            // Kembalikan status pinjaman jika sudah aktif kembali
            if ($pinjaman && $pinjaman->fresh()->sisa() > 0) {
                $pinjaman->update(['status' => 'aktif']);
            }
        });

        return redirect()->route('kebendaharaan.toko.index')
            ->with('sukses', 'Setoran dihapus. Pemasukan otomatis terhapus.');
    }
}