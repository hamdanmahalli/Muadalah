<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tagihan;
use App\Models\Pembayaran;

class PembayaranController extends Controller
{
    public function store(Request $request, $id)
    {
        $tagihan = Tagihan::with('pembayarans')->findOrFail($id);

        $request->validate([
            'nominal_dibayar' => 'required|numeric|min:1',
            'tanggal_bayar' => 'required|date',
        ]);

        $sisa = $tagihan->nominal - $tagihan->totalDibayar();
        if ($request->nominal_dibayar > $sisa) {
            return redirect()->back()->with('error', 'Nominal melebihi sisa tagihan (sisa: ' . number_format($sisa, 0, ',', '.') . ').');
        }

        Pembayaran::create([
            'tagihan_id' => $tagihan->id,
            'nominal_dibayar' => $request->nominal_dibayar,
            'tanggal_bayar' => $request->tanggal_bayar,
            'metode' => $request->metode,
            'keterangan' => $request->keterangan,
            'user_id' => auth()->id(),
        ]);

        // Perbarui status tagihan
        $total = $tagihan->totalDibayar();
        if ($total >= $tagihan->nominal) {
            $tagihan->update(['status' => 'lunas']);
        } elseif ($total > 0) {
            $tagihan->update(['status' => 'parsial']);
        }

        return redirect()->back()->with('sukses', 'Pembayaran berhasil dicatat.');
    }

    public function destroy($id, $bayar)
    {
        $pembayaran = Pembayaran::findOrFail($bayar);
        $tagihan = $pembayaran->tagihan;
        $pembayaran->delete();

        // Update status
        $total = $tagihan->totalDibayar();
        $newStatus = $total >= $tagihan->nominal ? 'lunas' : ($total > 0 ? 'parsial' : 'belum');
        $tagihan->update(['status' => $newStatus]);

        return redirect()->back()->with('sukses', 'Pembayaran dihapus.');
    }
}
