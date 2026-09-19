<?php

namespace App\Http\Controllers;

use App\Models\Pinjaman;
use App\Models\User;
use Illuminate\Http\Request;

class PinjamanController extends Controller
{
    public function index(Request $request)
    {
        $periode = get_periode_aktif();
        $query = Pinjaman::with(['peminjam', 'pencairan']);

        if ($periode) {
            $query->where('periode_id', $periode->id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $list = $query->orderBy('tanggal', 'asc')->orderBy('id', 'asc')->get();

        return view('admin.kebendaharaan.pinjaman-index', compact('list'));
    }

    public function create()
    {
        $periode = get_periode_aktif();
        $users = User::where('status', 'aktif')
            ->orderBy('name')
            ->get(['id', 'name', 'username']);

        return view('admin.kebendaharaan.pinjaman-form', compact('periode', 'users'));
    }

    public function store(Request $request)
    {
        $periode = get_periode_aktif();
        if (!$periode) {
            return redirect()->back()->with('error', 'Tidak ada periode aktif.');
        }

        $validated = $request->validate([
            'peminjam_user_id' => 'nullable|exists:users,id',
            'tanggal' => 'required|date',
            'jumlah' => 'required',
            'keperluan' => 'required|string',
        ]);

        $jumlah = rupiah_to_int($validated['jumlah']);
        if ($jumlah <= 0) {
            return redirect()->back()->with('error', 'Jumlah harus lebih dari 0.')->withInput();
        }

        Pinjaman::create([
            'kode' => Pinjaman::nextKode($periode->tahun),
            'periode_id' => $periode->id,
            'peminjam_user_id' => $validated['peminjam_user_id'] ?? auth()->id(),
            'jumlah' => $jumlah,
            'tanggal' => $validated['tanggal'],
            'keperluan' => $validated['keperluan'],
            'status' => 'aktif',
        ]);

        return redirect()->route('kebendaharaan.pinjaman.index')
            ->with('sukses', 'Pinjaman dicatat.');
    }

    public function lunasi($id)
    {
        $pinjaman = Pinjaman::findOrFail($id);

        if ($pinjaman->sisa() > 0) {
            return redirect()->route('kebendaharaan.pinjaman.index')
                ->with('error', 'Tidak bisa ditandai lunas karena masih ada sisa ' .
                    'Rp ' . number_format($pinjaman->sisa(), 0, ',', '.') . '.');
        }

        $pinjaman->update(['status' => 'lunas']);

        return redirect()->route('kebendaharaan.pinjaman.index')
            ->with('sukses', 'Pinjaman ' . $pinjaman->kode . ' ditandai lunas.');
    }

    public function aktifkan($id)
    {
        $pinjaman = Pinjaman::findOrFail($id);

        if ($pinjaman->sisa() > 0) {
            $pinjaman->update(['status' => 'aktif']);
        } else {
            return redirect()->route('kebendaharaan.pinjaman.index')
                ->with('error', 'Sisa pinjaman 0. Gunakan tombol Lunasi.');
        }

        return redirect()->route('kebendaharaan.pinjaman.index')
            ->with('sukses', 'Pinjaman ' . $pinjaman->kode . ' diaktifkan kembali.');
    }

    public function destroy($id)
    {
        $pinjaman = Pinjaman::findOrFail($id);

        if ($pinjaman->totalSetoran() > 0) {
            return redirect()->route('kebendaharaan.pinjaman.index')
                ->with('error', 'Pinjaman sudah memiliki setoran, tidak bisa dihapus.');
        }

        $pinjaman->delete();

        return redirect()->route('kebendaharaan.pinjaman.index')
            ->with('sukses', 'Pinjaman dihapus.');
    }
}