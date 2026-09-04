<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AngkatanSiswa;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\Periode;
use App\Services\AngkatanService;

class AngkatanSiswaController extends Controller
{
    public function __construct(
        protected AngkatanService $angkatanService
    ) {}

    public function index(Request $request)
    {
        $periodeId = $request->input('periode_id');
        $kelasId = $request->input('kelas_id');

        // Daftar tahun ajaran unik (penempatan kelas cukup per tahun ajaran, tanpa semester)
        $tahunAjaran = $this->angkatanService->tahunAjaranList();
        $kelas = Kelas::orderBy('nama_kelas', 'asc')->get();

        $aktif = Periode::where('is_active', true)->first();
        if (!$periodeId && $aktif) {
            // Default: dengan tahun ajaran aktif itu (periode acuan-nya)
            $periodeId = $tahunAjaran->firstWhere('is_active', true)?->periode_id ?? $aktif->id;
        }

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

        return view('penempatan-siswa', compact('tahunAjaran', 'kelas', 'periodeId', 'kelasId', 'siswaBelum', 'penempatan'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'siswa_id' => 'required|exists:siswas,id',
            'kelas_id' => 'required|exists:kelas,id',
        ]);

        $this->angkatanService->tempatkan(
            $request->siswa_id,
            $request->kelas_id,
            $request->periode_id,
            ['tanggal_masuk' => $request->tanggal_masuk]
        );

        return redirect()->back()->with('sukses', 'Siswa berhasil ditempatkan ke kelas.');
    }

    public function autoPlace(Request $request)
    {
        $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
        ]);

        $count = $this->angkatanService->autoPlace(
            (int) $request->kelas_id,
            $request->periode_id
        );

        return redirect()->back()->with('sukses', "{$count} siswa dimasukkan otomatis ke kelas.");
    }

    public function destroy($id)
    {
        AngkatanSiswa::destroy($id);
        return redirect()->back()->with('sukses', 'Penempatan siswa dihapus.');
    }

    public function updateNomorAbsen(Request $request, $id)
    {
        $request->validate([
            'nomor_absen' => 'required|integer|min:1',
        ]);

        $angkatan = AngkatanSiswa::findOrFail($id);
        $this->angkatanService->assignNomorAbsenUnik(
            $angkatan,
            $angkatan->kelas_id,
            $angkatan->periode_id,
            (int) $request->nomor_absen
        );

        return back()->with('sukses', 'Nomor absen diperbarui (nomor dobel otomatis digeser).');
    }
}
