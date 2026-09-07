<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\HonorDetail;
use App\Services\Honor\HonorService;

class GuruHonorController extends Controller
{
    public const BULAN_INDO = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
        7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];

    public function __construct(
        protected HonorService $service,
        protected \App\Services\AuthenticatedGuruService $guruContext,
    ) {}

    public function index()
    {
        $user = auth()->user();
        $guru = $this->guruContext->fromUser($user);

        if (!$guru) {
            return redirect('/dashboard-guru')->with('pesan', 'Akun Anda belum terhubung dengan Data Master Guru.');
        }

        $honors = HonorDetail::with(['periode.konfigurasi'])
            ->where('guru_id', $guru->id)
            ->whereHas('periode.konfigurasi')
            ->orderByDesc('created_at')
            ->get();

        $bulanIndonesia = self::BULAN_INDO;

        return view('guru.honor-dashboard', compact('guru', 'honors', 'bulanIndonesia'));
    }

    public function scan(Request $request)
    {
        $request->validate([
            'qr_data' => 'required|string',
        ]);

        if (!str_starts_with($request->qr_data, 'HONOR-')) {
            return response()->json(['success' => false, 'pesan' => 'QR bukan token honor guru.'], 422);
        }

        $detail = $this->service->prosesScan($request->qr_data, 'Scan QR Guru');

        if (!$detail) {
            return response()->json(['success' => false, 'pesan' => 'Token honor tidak ditemukan.'], 404);
        }

        if ($detail->is_diterima) {
            return response()->json([
                'success' => true,
                'pesan' => 'Honor sudah tercatat diterima pada ' . $detail->waktu_diterima->format('d/m/Y H:i') . '.',
                'nama_guru' => $detail->guru->nama_guru,
                'total' => $detail->total,
            ]);
        }

        return response()->json([
            'success' => true,
            'pesan' => 'Honor tercatat telah diterima!',
            'nama_guru' => $detail->guru->nama_guru,
            'total' => $detail->total,
        ]);
    }
}