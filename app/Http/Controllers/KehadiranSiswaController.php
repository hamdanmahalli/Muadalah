<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\KehadiranSiswa;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\Periode;
use App\Models\AngkatanSiswa;
use App\Services\KehadiranSiswaService;
use App\Imports\AbsenImport;

class KehadiranSiswaController extends Controller
{
    public function __construct(
        protected KehadiranSiswaService $kehadiranSiswa
    ) {}

    public function index(Request $request)
    {
        $kelas = Kelas::orderBy('nama_kelas', 'asc')->get();
        $periodes = Periode::orderBy('tahun_ajaran', 'desc')->get();
        $aktif = Periode::where('is_active', true)->first();

        $periodeId = $request->periode_id ?? ($aktif ? $aktif->id : null);
        $kelasId = $request->kelas_id;
        $tanggal = $request->tanggal ?? now()->format('Y-m-d');

        $siswas = collect();
        if ($kelasId) {
            $siswaIds = AngkatanSiswa::where('kelas_id', $kelasId)
                ->when($periodeId, fn($q) => $q->where('periode_id', $periodeId))
                ->pluck('siswa_id');
            $siswas = Siswa::whereIn('id', $siswaIds)
                ->with(['angkatan' => fn($q) => $q->where('kelas_id', $kelasId)])
                ->get()
                ->sortBy(fn($s) => $s->angkatan->first()?->nomor_absen ?? PHP_INT_MAX);
        }

        $kehadiranMap = KehadiranSiswa::where('tanggal', $tanggal)
            ->when($kelasId, fn($q) => $q->where('kelas_id', $kelasId))
            ->when($periodeId, fn($q) => $q->where('periode_id', $periodeId))
            ->get()->keyBy('siswa_id');

        return view('absen-siswa', compact('kelas', 'periodes', 'periodeId', 'kelasId', 'tanggal', 'siswas', 'kehadiranMap'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'kelas_id' => 'required|exists:kelas,id',
        ]);

        $count = $this->kehadiranSiswa->simpanBulk(
            $request->tanggal,
            (int) $request->kelas_id,
            $request->periode_id,
            $request->status ?? [],
            $request->keterangan ?? [],
            auth()->id()
        );

        return redirect()->back()->with('sukses', "Absensi {$count} siswa tersimpan untuk tanggal {$request->tanggal}.");
    }

    public function cetak(Request $request)
    {
        $kelasId = $request->kelas_id;
        $tanggalAwal = $request->tanggal_awal ?? now()->startOfWeek()->format('Y-m-d');
        $tanggalAkhir = $request->tanggal_akhir ?? now()->format('Y-m-d');

        $kelas = Kelas::findOrFail($kelasId);
        $siswas = $this->kehadiranSiswa->dataSiswaKelas($kelasId);

        $kehadiran = KehadiranSiswa::where('kelas_id', $kelasId)
            ->whereBetween('tanggal', [$tanggalAwal, $tanggalAkhir])
            ->get()
            ->groupBy('siswa_id');

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.absen-mingguan', compact('kelas', 'siswas', 'kehadiran', 'tanggalAwal', 'tanggalAkhir'))
            ->setPaper('a4', 'landscape');
        return $pdf->download('absen-mingguan-' . $kelas->nama_kelas . '.pdf');
    }

    public function importAbsen(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv',
        ]);

        try {
            $import = new AbsenImport($request->periode_id);
            $import->import($request->file('file')->getRealPath());

            return redirect()->back()->with('sukses',
                "Berhasil: {$import->imported} status SIA dari file, dan {$import->hadirOpsional} siswa otomatis dicatat Hadir."
            );
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Import absen siswa gagal: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal import absen. Pastikan format file benar.');
        }
    }

    public function templateAbsen()
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template Absen');

        $sheet->fromArray([
            ['nis', 'tanggal', 'status', 'keterangan'],
            ['1001', '2026-09-05', 'S', 'Demam'],
            ['1002', '2026-09-05', 'I', 'Acara keluarga'],
            ['1003', '2026-09-06', 'A', 'Tanpa keterangan'],
        ]);

        $sheet->getStyle('A1:D1')->getFont()->setBold(true);
        foreach (range('A', 'D') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Catatan / petunjuk di bawah tabel
        $sheet->setCellValue('A6', 'Petunjuk:');
        $sheet->setCellValue('A7', 'File ini berisi DAHULU STATUS TIDAK HADIR (SIA) saja. Isi nis + tanggal + status untuk murid yg SAKIT/IZIN/ALPHA.');
        $sheet->setCellValue('A8', '- nis     = NIS siswa (wajib, harus cocok dengan data siswa di sistem)');
        $sheet->setCellValue('A9', '- tanggal = tanggal absen (format YYYY-MM-DD, atau dd/mm/YYYY, atau angka serial Excel)');
        $sheet->setCellValue('A10', '- status  = S (Sakit), I (Izin), A (Alpha)');
        $sheet->setCellValue('A11', '- keterangan = opsional, catatan tambahan');
        $sheet->setCellValue('A12', 'Murid yang TIDAK tercantum di file otomatis dicatat HADIR untuk setiap tanggal yg ada di file.');

        $sheet->getColumnDimension('A')->setWidth(14);
        $sheet->getColumnDimension('B')->setWidth(14);
        $sheet->getColumnDimension('C')->setWidth(10);
        $sheet->getColumnDimension('D')->setWidth(20);

        $outPath = storage_path('app/template-absen-' . time() . '.xlsx');
        \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx')->save($outPath);

        return response()->download($outPath, 'template-import-absen.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }
}
