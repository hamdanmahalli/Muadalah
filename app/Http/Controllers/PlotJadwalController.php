<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PlotJadwal;
use App\Models\Kelas;
use App\Models\Pelajaran;
use App\Models\Guru;

class PlotJadwalController extends Controller
{
    public function index(Request $request)
    {
        $kelas = Kelas::orderBy('nama_kelas', 'asc')->get();
        $kelas_id = $request->kelas_id; // Menangkap jika ada kelas yang dipilih
        
        $pelajarans = [];
        $gurus = [];
        $plotAktif = [];

        // Jika user sudah memilih kelas, kita panggil semua pelajaran dan guru
        if ($kelas_id) {
            $pelajarans = Pelajaran::orderBy('kode_pelajaran', 'asc')->get();
            $gurus = Guru::where('status', 'Aktif')->orderBy('nama_guru', 'asc')->get();
            
            // Mengambil data plot lama jika sudah pernah disimpan
            $plotAktif = PlotJadwal::where('kelas_id', $kelas_id)->get()->keyBy('pelajaran_id');
        }

        return view('plot-jadwal', compact('kelas', 'kelas_id', 'pelajarans', 'gurus', 'plotAktif'));
    }

    public function store(Request $request)
    {
        $kelas_id = $request->kelas_id;
        $plots = $request->plots; // Menangkap array data dari tabel

        // Kita loop/putar semua baris pelajaran yang dikirim dari form
        if($plots) {
            foreach($plots as $pelajaran_id => $data) {
                // Perbarui data jika sudah ada, atau buat baru jika belum ada
                PlotJadwal::updateOrCreate(
                    ['kelas_id' => $kelas_id, 'pelajaran_id' => $pelajaran_id],
                    [
                        'guru_id' => $data['guru_id'] ?: null, 
                        'beban_jam' => $data['beban_jam'] ?? 0
                    ]
                );
            }
        }

        return redirect()->back()->with('sukses', 'Plotting Jadwal Kelas berhasil disimpan!');
    }
}