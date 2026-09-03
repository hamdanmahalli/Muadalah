<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Jabatan;

class JabatanController extends Controller
{
    public function index()
    {
        $jabatans = Jabatan::orderBy('nama_jabatan', 'asc')->get();
        return view('master-jabatan', compact('jabatans'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_jabatan' => 'required|string|unique:jabatans,nama_jabatan',
        ]);

        Jabatan::create([
            'nama_jabatan' => $request->nama_jabatan,
            'deskripsi' => $request->deskripsi,
            'status' => 'Aktif',
        ]);

        return redirect()->back()->with('sukses', 'Jabatan berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $jabatan = Jabatan::findOrFail($id);

        $request->validate([
            'nama_jabatan' => 'required|string|unique:jabatans,nama_jabatan,'.$id,
        ]);

        $jabatan->update([
            'nama_jabatan' => $request->nama_jabatan,
            'deskripsi' => $request->deskripsi,
            'status' => $request->status ?? 'Aktif',
        ]);

        return redirect()->back()->with('sukses', 'Jabatan berhasil diperbarui.');
    }

    public function destroy($id)
    {
        Jabatan::destroy($id);
        return redirect()->back()->with('sukses', 'Jabatan berhasil dihapus.');
    }
}
