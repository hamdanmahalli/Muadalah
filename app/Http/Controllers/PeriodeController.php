<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Periode;

class PeriodeController extends Controller
{
    public function index()
    {
        $periodes = Periode::orderBy('tahun_ajaran', 'desc')->orderBy('semester', 'desc')->get();
        return view('master-periode', compact('periodes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tahun_ajaran'    => 'required|string',
            'semester'        => 'required|in:Ganjil,Genap',
            'tanggal_mulai'   => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
        ]);

        Periode::create([
            'tahun_ajaran'    => $request->tahun_ajaran,
            'semester'        => $request->semester,
            'tanggal_mulai'   => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'is_active'       => false // Default non-aktif
        ]);

        return redirect()->back()->with('sukses', 'Periode Akademik baru berhasil ditambahkan!');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'tahun_ajaran'    => 'required|string',
            'semester'        => 'required|in:Ganjil,Genap',
            'tanggal_mulai'   => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
        ]);

        $periode = Periode::findOrFail($id);
        $periode->update([
            'tahun_ajaran'    => $request->tahun_ajaran,
            'semester'        => $request->semester,
            'tanggal_mulai'   => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
        ]);

        return redirect()->back()->with('sukses', 'Data Periode berhasil diperbarui!');
    }

    public function setAktif($id)
    {
        // Matikan semua periode yang sedang aktif
        Periode::query()->update(['is_active' => false]);
        
        // Hidupkan hanya periode yang dipilih
        Periode::findOrFail($id)->update(['is_active' => true]);
        
        return redirect()->back()->with('sukses', 'Periode tersebut sekarang telah Aktif!');
    }

    public function destroy($id)
    {
        Periode::destroy($id);
        return redirect()->back()->with('sukses', 'Periode berhasil dihapus!');
    }
}