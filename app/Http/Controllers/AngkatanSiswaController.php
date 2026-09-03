<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AngkatanSiswa;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\Periode;

class AngkatanSiswaController extends Controller
{
    public function index(Request $request)
    {
        $periodeId = $request->input('periode_id');
        $kelasId = $request->input('kelas_id');

        $periodes = Periode::orderBy('tahun_ajaran', 'desc')->get();
        $kelas = Kelas::orderBy('nama_kelas', 'asc')->get();

        $aktif = Periode::where('is_active', true)->first();
        if (!$periodeId && $aktif) $periodeId = $aktif->id;

        // Daftar siswa (yang belum ditempatkan di periode yang dipilih)
        $terpasangIds = AngkatanSiswa::when($periodeId, fn($q) => $q->where('periode_id', $periodeId))
            ->pluck('siswa_id');

        $siswaBelum = Siswa::aktif()
            ->when($terpasangIds->isNotEmpty(), fn($q) => $q->whereNotIn('id', $terpasangIds))
            ->orderBy('nama_siswa', 'asc')->get();

        $penempatan = AngkatanSiswa::with(['siswa', 'kelas', 'periode'])
            ->when($periodeId, fn($q) => $q->where('periode_id', $periodeId))
            ->when($kelasId, fn($q) => $q->where('kelas_id', $kelasId))
            ->orderBy('kelas_id', 'asc')
            ->paginate(30)
            ->withQueryString();

        return view('penempatan-siswa', compact('periodes', 'kelas', 'periodeId', 'kelasId', 'siswaBelum', 'penempatan'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'siswa_id' => 'required|exists:siswas,id',
            'kelas_id' => 'required|exists:kelas,id',
        ]);

        $periodeId = $request->periode_id;

        AngkatanSiswa::updateOrCreate(
            ['siswa_id' => $request->siswa_id, 'periode_id' => $periodeId],
            [
                'kelas_id' => $request->kelas_id,
                'status' => 'Aktif',
                'tanggal_masuk' => $request->tanggal_masuk ?? now(),
            ]
        );

        return redirect()->back()->with('sukses', 'Siswa berhasil ditempatkan ke kelas.');
    }

    public function autoPlace(Request $request)
    {
        $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
        ]);
        $periodeId = $request->periode_id;
        $kelasId = $request->kelas_id;

        $sudah = AngkatanSiswa::where('kelas_id', $kelasId)
            ->when($periodeId, fn($q) => $q->where('periode_id', $periodeId))
            ->pluck('siswa_id');

        $calon = Siswa::aktif()
            ->when($sudah->isNotEmpty(), fn($q) => $q->whereNotIn('id', $sudah))
            ->limit(50)
            ->get();

        $count = 0;
        foreach ($calon as $s) {
            AngkatanSiswa::updateOrCreate(
                ['siswa_id' => $s->id, 'periode_id' => $periodeId],
                ['kelas_id' => $kelasId, 'status' => 'Aktif', 'tanggal_masuk' => now()]
            );
            $count++;
        }

        return redirect()->back()->with('sukses', "{$count} siswa dimasukkan otomatis ke kelas.");
    }

    public function destroy($id)
    {
        AngkatanSiswa::destroy($id);
        return redirect()->back()->with('sukses', 'Penempatan siswa dihapus.');
    }
}
