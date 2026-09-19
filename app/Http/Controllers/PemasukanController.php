<?php

namespace App\Http\Controllers;

use App\Models\AnggaranPemasukan;
use App\Models\Pemasukan;
use Illuminate\Http\Request;

class PemasukanController extends Controller
{
    public function index()
    {
        $periode = get_periode_aktif();
        $query = Pemasukan::with('anggaranPemasukan');

        if ($periode) {
            $query->where('periode_id', $periode->id);
        }

        $list = $query->orderBy('tanggal', 'desc')->orderBy('id', 'desc')->get();
        $total = (float) $list->sum('jumlah');

        return view('admin.kebendaharaan.pemasukan-index', compact('list', 'total'));
    }

    public function create()
    {
        $periode = get_periode_aktif();
        $rencana = [];
        if ($periode) {
            $rencana = AnggaranPemasukan::whereHas('anggaran', function ($q) use ($periode) {
                $q->where('periode_id', $periode->id);
            })->orderBy('urutan')->get();
        }

        return view('admin.kebendaharaan.pemasukan-form', compact('periode', 'rencana'));
    }

    public function store(Request $request)
    {
        $periode = get_periode_aktif();
        if (!$periode) {
            return redirect()->back()->with('error', 'Tidak ada periode aktif.');
        }

        $validated = $request->validate([
            'uraian' => 'required|string|max:191',
            'anggaran_pemasukan_id' => 'nullable|exists:anggaran_pemasukan,id',
            'tanggal' => 'required|date',
            'jumlah' => 'required',
            'keterangan' => 'nullable|string',
        ]);

        $jumlah = rupiah_to_int($validated['jumlah']);
        if ($jumlah <= 0) {
            return redirect()->back()->with('error', 'Jumlah harus lebih dari 0.')->withInput();
        }

        Pemasukan::create([
            'periode_id' => $periode->id,
            'anggaran_pemasukan_id' => $validated['anggaran_pemasukan_id'] ?? null,
            'uraian' => $validated['uraian'],
            'tanggal' => $validated['tanggal'],
            'jumlah' => $jumlah,
            'keterangan' => $validated['keterangan'] ?? null,
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('kebendaharaan.pemasukan.index')
            ->with('sukses', 'Pemasukan dicatat.');
    }

    public function destroy($id)
    {
        $pemasukan = Pemasukan::findOrFail($id);

        // Pemasukan yang lahir dari setoran buku dihapus lewat menu setoran.
        if ($pemasukan->setoran_barang_id) {
            return redirect()->route('kebendaharaan.pemasukan.index')
                ->with('error', 'Pemasukan ini berasal dari setoran buku. Hapus lewat menu Setoran Toko Buku.');
        }

        $pemasukan->delete();

        return redirect()->route('kebendaharaan.pemasukan.index')
            ->with('sukses', 'Pemasukan dihapus.');
    }
}