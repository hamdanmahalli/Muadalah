<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kelas;
use Carbon\Carbon;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class BarcodeController extends Controller
{
    public function index()
    {
        $kelas = Kelas::orderBy('nama_kelas', 'asc')->get();
        
        // Menghitung tanggal Hari Sabtu di minggu ini (Siklus mingguan)
        $sabtuMingguIni = Carbon::now()->startOfWeek(Carbon::SATURDAY);
        $jumatDepan = $sabtuMingguIni->copy()->addDays(6);
        
        $periodeBerlaku = $sabtuMingguIni->translatedFormat('d M Y') . ' s/d ' . $jumatDepan->translatedFormat('d M Y');

        return view('pabrik-barcode', compact('kelas', 'periodeBerlaku'));
    }

    public function cetak($kelas_id)
    {
        $kelas = Kelas::findOrFail($kelas_id);
        
        // 1. Menentukan Tanggal Kunci (Selalu Hari Sabtu di minggu berjalan)
        $tanggalKunci = Carbon::now()->startOfWeek(Carbon::SATURDAY)->format('Y-m-d');
        
        // 2. Meracik Teks Rahasia: IDKelas + TanggalKunci + KunciAplikasi
        $teksRahasia = $kelas->id . '|' . $tanggalKunci . '|' . env('APP_KEY');
        
        // 3. Mengenkripsi Teks (Hash MD5) agar tidak bisa dibaca/ditebak manusia
        $tokenBarcode = hash('md5', $teksRahasia);
        
        // Format Final Barcode: SP (SmartPesantren) - ID Kelas - Token Rahasia
        $isiBarcode = 'SP-' . $kelas->id . '-' . $tokenBarcode;

        // 4. Menggambar QR Code
        $qrCodeImage = QrCode::size(300)
                        ->margin(2)
                        ->generate($isiBarcode);

        $sabtuMingguIni = Carbon::now()->startOfWeek(Carbon::SATURDAY);
        $jumatDepan = $sabtuMingguIni->copy()->addDays(6);
        $periodeBerlaku = $sabtuMingguIni->translatedFormat('d M Y') . ' s/d ' . $jumatDepan->translatedFormat('d M Y');

        return view('cetak-barcode', compact('kelas', 'qrCodeImage', 'periodeBerlaku'));
    }
}