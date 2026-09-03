<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kelas;
use App\Models\Guru;

class KelasController extends Controller
{
    public function index()
    {
        $kelas = Kelas::with('waliKelas')->orderBy('nama_kelas', 'asc')->get();
        $pengurus = Guru::where('status', 'Aktif')->orderBy('nama_guru', 'asc')->get();
        $galur = ['VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
        return view('kelas', compact('kelas', 'pengurus', 'galur'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_kelas' => 'required|string|unique:kelas,nama_kelas'
        ]);

        Kelas::create([
            'nama_kelas'   => strtoupper($request->nama_kelas),
            'tingkat'      => $request->tingkat,
            'wali_kelas_id'=> $request->wali_kelas_id ?: null,
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
            'nama_kelas'   => strtoupper($request->nama_kelas),
            'tingkat'      => $request->tingkat,
            'wali_kelas_id'=> $request->wali_kelas_id ?: null,
        ]);

        return redirect()->back()->with('sukses', 'Data Kelas berhasil diperbarui!');
    }

    public function destroy($id)
    {
        Kelas::destroy($id);
        return redirect()->back()->with('sukses', 'Data Kelas berhasil dihapus!');
    }
}
