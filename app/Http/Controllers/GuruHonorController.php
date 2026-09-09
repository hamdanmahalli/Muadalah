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
            ->whereHas('periode', fn($q) => $q->where('status', 'final'))
            ->whereHas('periode.konfigurasi')
            ->orderByDesc('created_at')
            ->get();

        $bulanIndonesia = self::BULAN_INDO;

        return view('guru.honor-dashboard', compact('guru', 'honors', 'bulanIndonesia'));
    }

    public function status()
    {
        $user = auth()->user();
        $guru = $this->guruContext->fromUser($user);

        if (!$guru) {
            return response()->json(['success' => false, 'pesan' => 'Guru tidak ditemukan.']);
        }

        $honors = HonorDetail::where('guru_id', $guru->id)
            ->whereHas('periode', fn($q) => $q->where('status', 'final'))
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'success' => true,
            'status' => $honors->map(fn($h) => [
                'id' => $h->id,
                'qr_token' => $h->qr_token,
                'is_diterima' => (bool) $h->is_diterima,
                'butuh_penerimaan' => (bool) $h->butuh_penerimaan,
                'waktu_diterima' => $h->waktu_diterima ? $h->waktu_diterima->translatedFormat('l, d F Y · H:i') : null,
                'metode_penerimaan' => $h->metode_penerimaan,
            ])->values()->all(),
        ]);
    }

    public function scan(Request $request)
    {
        $request->validate([
            'qr_data' => 'required|string',
        ]);

        if (!str_starts_with($request->qr_data, 'HONOR-')) {
            return response()->json(['success' => false, 'pesan' => 'QR bukan token honor guru.'], 422);
        }

        try {
            $detail = $this->service->prosesScan($request->qr_data, 'Scan QR Guru');
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'pesan' => $e->getMessage()], 409);
        }

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