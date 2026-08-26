<?php

namespace App\Http\Controllers;

use App\Models\AgendaKaldik;
use App\Models\Periode;
use App\Models\Kelas;
use Illuminate\Http\Request;

class AgendaKaldikController extends Controller
{
    public function index()
    {
        $periodeAktif = get_periode_aktif();
        
        if (!$periodeAktif) {
            return redirect('/dashboard')->with('error', 'Tidak ada periode tahun ajaran yang aktif.');
        }

        // Ambil data agenda hanya untuk periode yang sedang aktif
        $agenda = AgendaKaldik::where('periode_id', $periodeAktif->id)
                    ->orderBy('tanggal_mulai', 'asc')
                    ->get();
                    
        $kelas = Kelas::all();

        return view('admin.kaldik.index', compact('agenda', 'periodeAktif', 'kelas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_agenda' => 'required|string|max:255',
            'jenis_agenda' => 'required|in:Libur,UTS,UAS,Kegiatan',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'target_libur' => 'required|in:semua,kelas_tertentu',
        ]);

        $periodeAktif = get_periode_aktif();

        AgendaKaldik::create([
            'periode_id' => $periodeAktif->id,
            'nama_agenda' => $request->nama_agenda,
            'jenis_agenda' => $request->jenis_agenda,
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'target_libur' => $request->target_libur,
            'kelas_ids' => $request->target_libur == 'kelas_tertentu' ? $request->kelas_ids : null,
            'tipe_agenda' => $request->tipe_agenda ?? 'Penuh',                                        // <-- Baru
            'jam_diliburkan' => $request->tipe_agenda == 'Parsial' ? $request->jam_diliburkan : null, // <-- Baru
            'keterangan' => $request->keterangan,
        ]);

        return redirect()->back()->with('success', 'Agenda Kalender Pendidikan berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_agenda' => 'required|string|max:255',
            'jenis_agenda' => 'required|in:Libur,UTS,UAS,Kegiatan',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'target_libur' => 'required|in:semua,kelas_tertentu',
        ]);

        $agenda = AgendaKaldik::findOrFail($id);
        
        $agenda->update([
            'nama_agenda' => $request->nama_agenda,
            'jenis_agenda' => $request->jenis_agenda,
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'target_libur' => $request->target_libur,
            'kelas_ids' => $request->target_libur == 'kelas_tertentu' ? $request->kelas_ids : null,
            'tipe_agenda' => $request->tipe_agenda ?? 'Penuh',                                        // <-- Baru
            'jam_diliburkan' => $request->tipe_agenda == 'Parsial' ? $request->jam_diliburkan : null, // <-- Baru
            'keterangan' => $request->keterangan,
        ]);

        return redirect()->back()->with('success', 'Agenda Kalender Pendidikan berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $agenda = AgendaKaldik::findOrFail($id);
        $agenda->delete();

        return redirect()->back()->with('success', 'Agenda berhasil dihapus.');
    }
}