<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AuthenticatedGuruService;
use App\Services\Kehadiran\KehadiranScanService;

class ScanController extends Controller
{
    public function __construct(
        protected KehadiranScanService $scan
    ) {}

    // Membuka halaman kamera di HP Guru (dengan tab QR Code pribadi guru)
    public function index()
    {
        $guru = app(AuthenticatedGuruService::class)->fromAuthUser();
        $qrPribadi = $this->scan->qrPribadi();

        return view('scan-kelas', compact('guru', 'qrPribadi'));
    }

    // Mesin pemroses saat kamera berhasil membaca QR Code
    public function proses(Request $request)
    {
        try {
            $result = $this->scan->proses($request->qr_data);
            return response()->json($result);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Proses scan gagal: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'pesan' => 'Terjadi kesalahan sistem saat memproses scan.']);
        }
    }

    // Fungsi: Mengeksekusi Keputusan Piket
    public function prosesPiket(Request $request)
    {
        $request->validate(['jadwal_ids' => 'required|array']);

        $this->scan->catatPiket($request->jadwal_ids);

        return response()->json(['status' => 'success', 'pesan' => 'Tercatat! Anda bertugas sebagai Guru Piket untuk jam ini. TU akan memvalidasi alasannya.']);
    }
}
