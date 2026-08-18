<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pelajaran;

class PelajaranController extends Controller
{
    public function index()
    {
        $pelajarans = Pelajaran::orderBy('kode_pelajaran', 'asc')->get();
        
        // Generate Kode Pelajaran Otomatis (Format: MP-001)
        $lastPelajaran = Pelajaran::where('kode_pelajaran', 'like', 'MP-%')->orderBy('kode_pelajaran', 'desc')->first();
        if ($lastPelajaran) {
            $lastAngka = (int) substr($lastPelajaran->kode_pelajaran, 3);
            $kodeBaru = 'MP-' . str_pad($lastAngka + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $kodeBaru = 'MP-001';
        }

        return view('pelajaran', compact('pelajarans', 'kodeBaru'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_pelajaran' => 'required|string|max:255'
        ]);

        Pelajaran::create([
            'kode_pelajaran' => $request->kode_pelajaran,
            'nama_pelajaran' => $request->nama_pelajaran,
            'nama_kitab'     => $request->nama_kitab,
            'status'         => $request->status ?? 'Aktif' // Menyimpan Status
        ]);

        return redirect()->back()->with('sukses', 'Data Pelajaran berhasil ditambahkan!');
    }

    public function update(Request $request, $id)
    {
        $pelajaran = Pelajaran::findOrFail($id);
        
        $request->validate([
            'nama_pelajaran' => 'required|string|max:255'
        ]);

        $pelajaran->update([
            'nama_pelajaran' => $request->nama_pelajaran,
            'nama_kitab'     => $request->nama_kitab,
            'status'         => $request->status ?? 'Aktif' // Memperbarui Status
        ]);

        return redirect()->back()->with('sukses', 'Data Pelajaran berhasil diperbarui!');
    }

    public function destroy($id)
    {
        Pelajaran::destroy($id);
        return redirect()->back()->with('sukses', 'Data Pelajaran berhasil dihapus!');
    }
}