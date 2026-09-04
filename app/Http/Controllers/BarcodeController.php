<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kelas;
use Carbon\Carbon;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class BarcodeController extends Controller
{
    private function getTanggalKunci(): Carbon
    {
        $now = Carbon::now();
        $hari = $now->dayOfWeek;
        $jam = (int) $now->format('H');

        if ($hari === Carbon::THURSDAY && $jam >= 18 || $hari === Carbon::FRIDAY) {
            return $now->copy()->next(Carbon::SATURDAY)->startOfDay();
        }

        return $now->copy()->startOfWeek(Carbon::SATURDAY);
    }

    private function isCetakAllowed(): bool
    {
        $now = Carbon::now();
        return !($now->dayOfWeek === Carbon::THURSDAY && (int) $now->format('H') < 18);
    }

    private function getPeriodeBerlaku(): string
    {
        $sabtu = $this->getTanggalKunci();
        $kamis = $sabtu->copy()->addDays(5);
        return $sabtu->translatedFormat('d M Y') . ' s/d ' . $kamis->translatedFormat('d M Y');
    }

    public function index()
    {
        $kelas = Kelas::orderBy('nama_kelas', 'asc')->get();
        $periodeBerlaku = $this->getPeriodeBerlaku();

        return view('pabrik-barcode', compact('kelas', 'periodeBerlaku'));
    }

    public function cetak($kelas_id)
    {
        if (!$this->isCetakAllowed()) {
            return redirect('/pabrik-barcode')->with('error', 'Cetak barcode hanya bisa dimulai dari hari Kamis pukul 18.00!');
        }

        $kelas = Kelas::findOrFail($kelas_id);

        $tanggalKunci = $this->getTanggalKunci()->format('Y-m-d');
        $teksRahasia = $kelas->id . '|' . $tanggalKunci . '|' . config('app.key');
        $tokenBarcode = hash_hmac('sha256', $teksRahasia, config('app.key'));
        $isiBarcode = 'SP-' . $kelas->id . '-' . $tokenBarcode;

        $qrCodeImage = QrCode::size(300)
                        ->margin(2)
                        ->generate($isiBarcode);

        $periodeBerlaku = $this->getPeriodeBerlaku();

        return view('cetak-barcode', compact('kelas', 'qrCodeImage', 'periodeBerlaku'));
    }
}
