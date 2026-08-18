<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\HariLibur;
use App\Models\MasterJam;
use App\Models\Kelas;

class HariLiburController extends Controller
{
    public function index()
    {
        $hariLiburs = HariLibur::orderBy('tanggal_mulai', 'desc')->get();
        $semuaJam = MasterJam::orderBy('jam_ke', 'asc')->get();
        $semuaKelas = Kelas::orderBy('nama_kelas', 'asc')->get();

        return view('hari-libur', compact('hariLiburs', 'semuaJam', 'semuaKelas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_libur' => 'required|string|max:255',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'tipe_libur' => 'required|in:Penuh,Parsial',
            'target_libur' => 'required|in:semua,kelas_tertentu',
        ]);

        HariLibur::create([
            'nama_libur'      => $request->nama_libur,
            'tanggal_mulai'   => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'tipe_libur'      => $request->tipe_libur,
            // CUKUP DIKIRIM SEBAGAI ARRAY MURNI, MODEL AKAN OTOMATIS MERACIKNYA
            'jam_diliburkan'  => $request->tipe_libur == 'Parsial' ? array_map('intval', $request->jam_diliburkan ?? []) : null,
            'target_libur'    => $request->target_libur,
            'kelas_ids'       => $request->target_libur == 'kelas_tertentu' ? array_map('intval', $request->kelas_ids ?? []) : null,
            'keterangan'      => $request->keterangan,
        ]);

        return redirect()->back()->with('sukses', 'Agenda Hari Libur berhasil disimpan!');
    }

    public function destroy($id)
    {
        HariLibur::destroy($id);
        return redirect()->back()->with('sukses', 'Data Hari Libur berhasil dihapus!');
    }
}