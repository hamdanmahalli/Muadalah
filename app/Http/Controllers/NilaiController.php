<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Nilai;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\Periode;
use App\Models\Pelajaran;
use App\Models\AngkatanSiswa;

class NilaiController extends Controller
{
    public function index(Request $request)
    {
        $periodes = Periode::orderBy('tahun_ajaran', 'desc')->get();
        $kelas = Kelas::orderBy('nama_kelas', 'asc')->get();
        $pelajarans = Pelajaran::orderBy('nama_pelajaran', 'asc')->get();
        $aktif = Periode::where('is_active', true)->first();

        $periodeId = $request->periode_id ?? ($aktif ? $aktif->id : null);
        $kelasId = $request->kelas_id;
        $pelajaranId = $request->pelajaran_id;

        // Daftar siswa di kelas + periode
        $siswas = collect();
        $nilaiMap = collect();
        if ($kelasId) {
            $siswaIds = AngkatanSiswa::where('kelas_id', $kelasId)
                ->when($periodeId, fn($q) => $q->where('periode_id', $periodeId))
                ->pluck('siswa_id');
            $siswas = Siswa::whereIn('id', $siswaIds)->orderBy('nama_siswa', 'asc')->get();

            if ($pelajaranId) {
                $nilaiMap = Nilai::where('kelas_id', $kelasId)
                    ->where('pelajaran_id', $pelajaranId)
                    ->when($periodeId, fn($q) => $q->where('periode_id', $periodeId))
                    ->get()
                    ->keyBy('siswa_id');
            }
        }

        return view('nilai.index', compact('periodes', 'kelas', 'pelajarans', 'periodeId', 'kelasId', 'pelajaranId', 'siswas', 'nilaiMap'));
    }

    // Simpan satu baris nilai
    public function store(Request $request)
    {
        $request->validate([
            'siswa_id' => 'required|exists:siswas,id',
            'pelajaran_id' => 'required|exists:pelajarans,id',
        ]);

        $uts = $request->nilai_uts;
        $uas = $request->nilai_uas;
        $akhir = (is_numeric($uts) && is_numeric($uas))
            ? round((($uts + $uas) / 2), 2)
            : (is_numeric($uts) ? $uts : (is_numeric($uas) ? $uas : null));

        Nilai::updateOrCreate(
            [
                'siswa_id' => $request->siswa_id,
                'pelajaran_id' => $request->pelajaran_id,
                'periode_id' => $request->periode_id,
            ],
            [
                'kelas_id' => $request->kelas_id,
                'guru_id' => $request->guru_id,
                'nilai_uts' => $uts,
                'nilai_uas' => $uas,
                'nilai_akhir' => $akhir,
                'predikat' => $this->predikat($akhir),
                'catatan' => $request->catatan,
            ]
        );

        return redirect()->back()->with('sukses', 'Nilai berhasil disimpan.');
    }

    // Simpan massal (grid)
    public function simpanMassal(Request $request)
    {
        $request->validate([
            'pelajaran_id' => 'required|exists:pelajarans,id',
            'kelas_id' => 'required|exists:kelas,id',
        ]);

        $periodeId = $request->periode_id;
        $count = 0;
        foreach (($request->siswa ?? []) as $siswaId => $vals) {
            $uts = $vals['uts'] ?? null;
            $uas = $vals['uas'] ?? null;
            if (!is_numeric($uts) && !is_numeric($uas)) continue;

            $akhir = (is_numeric($uts) && is_numeric($uas))
                ? round((($uts + $uas) / 2), 2)
                : (is_numeric($uts) ? $uts : $uas);

            Nilai::updateOrCreate(
                [
                    'siswa_id' => $siswaId,
                    'pelajaran_id' => $request->pelajaran_id,
                    'periode_id' => $periodeId,
                ],
                [
                    'kelas_id' => $request->kelas_id,
                    'guru_id' => auth()->user()->guru?->id,
                    'nilai_uts' => $uts,
                    'nilai_uas' => $uas,
                    'nilai_akhir' => $akhir,
                    'predikat' => $this->predikat($akhir),
                ]
            );
            $count++;
        }

        return redirect()->back()->with('sukses', "Nilai {$count} siswa berhasil disimpan.");
    }

    public function downloadTemplate()
    {
        return redirect()->back()->with('sukses', 'Template nilai diunduh.');
    }

    private function predikat($nilai)
    {
        if (!is_numeric($nilai)) return null;
        if ($nilai >= 90) return 'A';
        if ($nilai >= 80) return 'B';
        if ($nilai >= 70) return 'C';
        if ($nilai >= 60) return 'D';
        return 'E';
    }
}
