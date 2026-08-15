<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kelas;

class KelasController extends Controller
{
    public function index()
    {
        $kelas = Kelas::orderBy('nama_kelas', 'asc')->get();
        return view('kelas', compact('kelas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_kelas' => 'required|string|unique:kelas,nama_kelas'
        ]);

        Kelas::create([
            'nama_kelas' => strtoupper($request->nama_kelas)
        ]);

        return redirect()->back()->with('sukses', 'Data Kelas berhasil ditambahkan!');
    }

    public function update(Request $request, $id)
    {
        $kelas = Kelas::findOrFail($id);

        $request->validate([
            'nama_kelas' => 'required|string|unique:kelas,nama_kelas,'.$id
        ]);

        $kelas->update([
            'nama_kelas' => strtoupper($request->nama_kelas)
        ]);

        return redirect()->back()->with('sukses', 'Data Kelas berhasil diperbarui!');
    }

    public function destroy($id)
    {
        Kelas::destroy($id);
        return redirect()->back()->with('sukses', 'Data Kelas berhasil dihapus!');
    }
}