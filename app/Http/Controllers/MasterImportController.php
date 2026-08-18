<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Guru;
use App\Models\Pelajaran;
use App\Models\Kelas;
use App\Models\PlotJadwal;
use App\Models\JadwalHarian;
use App\Models\Periode; // WAJIB ADA UNTUK PERIODE
use PhpOffice\PhpSpreadsheet\IOFactory; 

class MasterImportController extends Controller
{
    public function index()
    {
        // Ambil info periode aktif untuk ditampilkan di layar
        $periodeAktif = Periode::where('is_active', true)->first();
        return view('master-import', compact('periodeAktif'));
    }

    // 1. IMPORT DATA GURU
    public function importGuru(Request $request)
    {
        $request->validate(['file' => 'required|mimes:xlsx,xls,csv']);
        $spreadsheet = IOFactory::load($request->file('file')->path());
        $rows = $spreadsheet->getActiveSheet()->toArray();

        $imported = 0;
        foreach ($rows as $index => $row) {
            if ($index == 0 || empty($row[0])) continue;

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
            if ($index == 0) continue; // Lewati header
            if (empty($row[1])) continue; // Lewati jika nama pelajaran kosong

            // KECERDASAN SISTEM: Mencari Kode Pelajaran Lanjutan Anti-Tabrakan
            $kodeBaru = $row[0] ?? null;
            if (empty($kodeBaru)) {
                // Cari angka terbesar di tabel saat ini
                $lastPelajaran = Pelajaran::orderBy('kode_pelajaran', 'desc')->first();
                if ($lastPelajaran && preg_match('/MP-(\d+)/', $lastPelajaran->kode_pelajaran, $matches)) {
                    $angkaTerakhir = (int)$matches[1];
                    // Looping sampai menemukan kode yang benar-benar belum dipakai
                    do {
                        $angkaTerakhir++;
                        $kodeBaru = 'MP-' . str_pad($angkaTerakhir, 3, '0', STR_PAD_LEFT);
                    } while (Pelajaran::where('kode_pelajaran', $kodeBaru)->exists());
                } else {
                    $kodeBaru = 'MP-001';
                }
            }

            Pelajaran::updateOrCreate(
                ['nama_pelajaran' => trim($row[1])],
                [
                    'kode_pelajaran' => $kodeBaru,
                    'nama_kitab'     => $row[2] ?? '-',
                ]
            );
            $imported++;
        }

        return redirect()->back()->with('sukses', "Berhasil mengimpor {$imported} data Pelajaran tanpa bentrok kode!");
    }

    // 3. IMPORT DATA KELAS
    public function importKelas(Request $request)
    {
        $request->validate(['file' => 'required|mimes:xlsx,xls,csv']);
        
        $spreadsheet = IOFactory::load($request->file('file')->path());
        $rows = $spreadsheet->getActiveSheet()->toArray();

        $imported = 0;
        foreach ($rows as $index => $row) {
            if ($index == 0) continue; // Lewati Header Excel
            if (empty($row[0])) continue; // Lewati jika kolom A kosong

            // REVISI CERDAS: Hanya masukkan 'nama_kelas' dan abaikan kolom 'tingkat' di Excel
            Kelas::updateOrCreate(
                ['nama_kelas' => strtoupper(trim($row[0]))]
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
            if ($index == 0 || empty($row[0]) || empty($row[1])) continue;
            
            $kelas = Kelas::where('nama_kelas', 'ilike', trim($row[0]))->first();
            $pelajaran = Pelajaran::where('nama_pelajaran', 'ilike', trim($row[1]))->first();
            
            $guru = null;
            if (!empty($row[2])) {
                $guru = Guru::where('nig', 'ilike', trim($row[2]))->orWhere('nama_guru', 'ilike', trim($row[2]))->first();
            }

            if ($kelas && $pelajaran) {
                PlotJadwal::updateOrCreate(
                    ['kelas_id' => $kelas->id, 'pelajaran_id' => $pelajaran->id],
                    ['guru_id' => $guru ? $guru->id : null, 'beban_jam' => isset($row[3]) ? (int)$row[3] : 2]
                );
                $imported++;
            }
        }
        return redirect()->back()->with('sukses', "Berhasil mengimpor {$imported} baris Target Mengajar!");
    }

    // 5. IMPORT JADWAL HARIAN (TERIKAT TAHUN AJARAN)
    public function importJadwalHarian(Request $request)
    {
        $request->validate(['file' => 'required|mimes:xlsx,xls,csv']);
        
        $periodeAktif = Periode::where('is_active', true)->first();
        if(!$periodeAktif) {
            return redirect()->back()->with('error', "Gagal! Anda belum mengaktifkan Periode di Master Periode.");
        }

        $spreadsheet = IOFactory::load($request->file('file')->path());
        $rows = $spreadsheet->getActiveSheet()->toArray();

        $imported = 0;
        foreach ($rows as $index => $row) {
            if ($index == 0 || empty($row[0]) || empty($row[1]) || empty($row[2])) continue;
            
            $kelas = Kelas::where('nama_kelas', 'ilike', trim($row[0]))->first();
            $pelajaran = Pelajaran::where('nama_pelajaran', 'ilike', trim($row[4]))->first();
            
            $guru = null;
            if (!empty($row[3])) {
                $guru = Guru::where('nig', 'ilike', trim($row[3]))->orWhere('nama_guru', 'ilike', trim($row[3]))->first();
            }

            if ($kelas && $pelajaran) {
                JadwalHarian::updateOrCreate(
                    [
                        'kelas_id'     => $kelas->id, 
                        'hari'         => trim($row[1]), 
                        'jam_ke'       => (int)$row[2],
                        'tahun_ajaran' => $periodeAktif->tahun_ajaran // TERIKAT KE TAHUN AJARAN
                    ],
                    [
                        'pelajaran_id' => $pelajaran->id, 
                        'guru_id'      => $guru ? $guru->id : null
                    ]
                );
                $imported++;
            }
        }
        return redirect()->back()->with('sukses', "Berhasil mengimpor {$imported} Jadwal Harian untuk Tahun Ajaran " . $periodeAktif->tahun_ajaran);
    }
}