<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\MasterImportService;

class MasterImportController extends Controller
{
    public function __construct(
        protected MasterImportService $importer
    ) {}

    public function index()
    {
        // Ambil info periode aktif untuk ditampilkan di layar
        $periodeAktif = get_periode_aktif();
        return view('master-import', compact('periodeAktif'));
    }

    // 1. IMPORT DATA GURU
    public function importGuru(Request $request)
    {
        $request->validate(['file' => 'required|mimes:xlsx,xls,csv']);
        $imported = $this->importer->importGuru($request->file('file'));
        return redirect()->back()->with('sukses', "Berhasil mengimpor {$imported} data Guru!");
    }

    // 2. IMPORT DATA PELAJARAN
    public function importPelajaran(Request $request)
    {
        $request->validate(['file' => 'required|mimes:xlsx,xls,csv']);
        $imported = $this->importer->importPelajaran($request->file('file'));
        return redirect()->back()->with('sukses', "Berhasil mengimpor {$imported} data Pelajaran tanpa bentrok kode!");
    }

    // 3. IMPORT DATA KELAS
    public function importKelas(Request $request)
    {
        $request->validate(['file' => 'required|mimes:xlsx,xls,csv']);
        $imported = $this->importer->importKelas($request->file('file'));
        return redirect()->back()->with('sukses', "Berhasil mengimpor {$imported} data Kelas!");
    }

    // 4. IMPORT TARGET MENGAJAR (PLOT JADWAL)
    public function importPlotJadwal(Request $request)
    {
        $request->validate(['file' => 'required|mimes:xlsx,xls,csv']);
        $imported = $this->importer->importPlotJadwal($request->file('file'));
        return redirect()->back()->with('sukses', "Berhasil mengimpor {$imported} baris Target Mengajar!");
    }

    // 5. IMPORT JADWAL HARIAN (TERIKAT TAHUN AJARAN)
    public function importJadwalHarian(Request $request)
    {
        $request->validate(['file' => 'required|mimes:xlsx,xls,csv']);

        $periodeAktif = get_periode_aktif();
        if (!$periodeAktif) {
            return redirect()->back()->with('error', "Gagal! Anda belum mengaktifkan Periode di Master Periode.");
        }

        $imported = $this->importer->importJadwalHarian($request->file('file'), $periodeAktif->tahun_ajaran);

        return redirect()->back()->with('sukses', "Berhasil mengimpor {$imported} Jadwal Harian untuk Tahun Ajaran " . $periodeAktif->tahun_ajaran);
    }
}
