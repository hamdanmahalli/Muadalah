<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Guru;
use App\Models\Pelajaran;
use App\Models\Kelas;
use App\Models\PlotJadwal;
use App\Models\JadwalHarian;
use PhpOffice\PhpSpreadsheet\IOFactory; // PUSTAKA INTI YANG LEBIH TANGGUH

class MasterImportController extends Controller
{
    public function index()
    {
        return view('master-import');
    }

    // 1. IMPORT DATA GURU
    public function importGuru(Request $request)
    {
        $request->validate(['file' => 'required|mimes:xlsx,xls,csv']);
        
        // MESIN PEMBACA EXCEL LANGSUNG (BYPASS)
        $spreadsheet = IOFactory::load($request->file('file')->path());
        $rows = $spreadsheet->getActiveSheet()->toArray();

        $imported = 0;
        foreach ($rows as $index => $row) {
            if ($index == 0) continue; // Lewati baris header
            if (empty($row[0])) continue;

            Guru::updateOrCreate(
                ['nig' => (string)$row[0]],
                [
                    'nama_guru' => $row[1] ?? 'Tanpa Nama',
                    'gender'    => $row[2] ?? null,
                    'alamat'    => $row[3] ?? null,
                    'no_hp'     => $row[4] ?? null,
                    'status'    => $row[5] ?? 'Aktif',
                ]
            );
            $imported++;
        }

        return redirect()->back()->with('sukses', "Berhasil mengimpor {$imported} data Guru!");
    }

    // 2. IMPORT DATA PELAJARAN
    public function importPelajaran(Request $request)
    {
        $request->validate(['file' => 'required|mimes:xlsx,xls,csv']);
        
        $spreadsheet = IOFactory::load($request->file('file')->path());
        $rows = $spreadsheet->getActiveSheet()->toArray();

        $imported = 0;
        foreach ($rows as $index => $row) {
            if ($index == 0) continue;
            if (empty($row[1])) continue;

            Pelajaran::updateOrCreate(
                ['nama_pelajaran' => $row[1]],
                [
                    'kode_pelajaran' => $row[0] ?? ('MP-' . str_pad($index, 3, '0', STR_PAD_LEFT)),
                    'nama_kitab'     => $row[2] ?? '-',
                ]
            );
            $imported++;
        }

        return redirect()->back()->with('sukses', "Berhasil mengimpor {$imported} data Pelajaran!");
    }

    // 3. IMPORT DATA KELAS
    public function importKelas(Request $request)
    {
        $request->validate(['file' => 'required|mimes:xlsx,xls,csv']);
        
        $spreadsheet = IOFactory::load($request->file('file')->path());
        $rows = $spreadsheet->getActiveSheet()->toArray();

        $imported = 0;
        foreach ($rows as $index => $row) {
            if ($index == 0) continue;
            if (empty($row[0])) continue;

            Kelas::updateOrCreate(
                ['nama_kelas' => $row[0]],
                [
                    'tingkat' => $row[1] ?? null,
                ]
            );
            $imported++;
        }

        return redirect()->back()->with('sukses', "Berhasil mengimpor {$imported} data Kelas!");
    }

    // 4. IMPORT TARGET MENGAJAR (PLOT JADWAL)
    public function importPlotJadwal(Request $request)
    {
        $request->validate(['file' => 'required|mimes:xlsx,xls,csv']);
        
        $spreadsheet = IOFactory::load($request->file('file')->path());
        $rows = $spreadsheet->getActiveSheet()->toArray();

        $imported = 0;
        foreach ($rows as $index => $row) {
            if ($index == 0) continue;
            
            // Pengaman ekstra jika ada baris Excel yang kosong di akhir
            if (empty($row[0]) || empty($row[1])) continue;
            
            // Format Excel: Nama Kelas (Col 0), Nama Pelajaran (Col 1), NIG/Nama Guru (Col 2), Beban Jam (Col 3)
            $kelas = Kelas::where('nama_kelas', 'ilike', trim($row[0]))->first();
            $pelajaran = Pelajaran::where('nama_pelajaran', 'ilike', trim($row[1]))->first();
            
            $guru = null;
            if (!empty($row[2])) {
                $guru = Guru::where('nig', 'ilike', trim($row[2]))
                            ->orWhere('nama_guru', 'ilike', trim($row[2]))
                            ->first();
            }

            if ($kelas && $pelajaran) {
                PlotJadwal::updateOrCreate(
                    ['kelas_id' => $kelas->id, 'pelajaran_id' => $pelajaran->id],
                    [
                        'guru_id' => $guru ? $guru->id : null,
                        'beban_jam' => isset($row[3]) ? (int)$row[3] : 2
                    ]
                );
                $imported++;
            }
        }

        return redirect()->back()->with('sukses', "Berhasil mengimpor {$imported} baris Target Mengajar!");
    }

    // 5. IMPORT JADWAL HARIAN (ROSTER)
    public function importJadwalHarian(Request $request)
    {
        $request->validate(['file' => 'required|mimes:xlsx,xls,csv']);
        
        $spreadsheet = IOFactory::load($request->file('file')->path());
        $rows = $spreadsheet->getActiveSheet()->toArray();

        $imported = 0;
        foreach ($rows as $index => $row) {
            if ($index == 0) continue; // Lewati baris judul kolom (header)
            
            // Pengaman: Lewati jika kolom Kelas, Hari, atau Jam kosong
            if (empty($row[0]) || empty($row[1]) || empty($row[2])) continue;
            
            // Format Asumsi Excel: Kelas (0) | Hari (1) | Jam Ke (2) | NIG/Guru (3) | Pelajaran (4)
            $kelas = Kelas::where('nama_kelas', 'ilike', trim($row[0]))->first();
            $pelajaran = Pelajaran::where('nama_pelajaran', 'ilike', trim($row[4]))->first();
            
            $guru = null;
            if (!empty($row[3])) {
                $guru = Guru::where('nig', 'ilike', trim($row[3]))
                            ->orWhere('nama_guru', 'ilike', trim($row[3]))
                            ->first();
            }

            // Jika Kelas dan Pelajaran ditemukan di database, simpan jadwalnya
            if ($kelas && $pelajaran) {
                JadwalHarian::updateOrCreate(
                    [
                        'kelas_id' => $kelas->id, 
                        'hari' => trim($row[1]), 
                        'jam_ke' => (int)$row[2]
                    ],
                    [
                        'pelajaran_id' => $pelajaran->id, 
                        'guru_id' => $guru ? $guru->id : null
                    ]
                );
                $imported++;
            }
        }

        return redirect()->back()->with('sukses', "Berhasil mengimpor {$imported} baris Jadwal Harian!");
    }
}