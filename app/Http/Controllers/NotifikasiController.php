<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\GuruNotifikasiSetting;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class NotifikasiController extends Controller
{
    public function __construct(private NotificationService $service) {}

    private function getGuru(): ?Guru
    {
        $user = auth()->user();
        return Guru::where('nama_guru', $user->name)
            ->orWhere('nig', $user->username)
            ->first();
    }

    public function pengaturan()
    {
        $guru = $this->getGuru();
        if (!$guru) abort(404);

        $setting = GuruNotifikasiSetting::firstOrCreate(
            ['guru_id' => $guru->id],
            ['is_enabled' => true, 'mode' => 'sound']
        );

        return view('guru.notifikasi-pengaturan', compact('guru', 'setting'));
    }

    public function simpan(Request $request)
    {
        $guru = $this->getGuru();
        if (!$guru) abort(404);

        $request->validate([
            'is_enabled' => 'required|boolean',
            'mode' => 'required|in:sound,vibrate,silent',
        ]);

        GuruNotifikasiSetting::updateOrCreate(
            ['guru_id' => $guru->id],
            [
                'is_enabled' => $request->boolean('is_enabled'),
                'mode' => $request->mode,
            ]
        );

        return back()->with('success', 'Pengaturan notifikasi disimpan.');
    }

    public function subscribe(Request $request)
    {
        $guru = $this->getGuru();
        if (!$guru) return response()->json(['error' => 'Unauthorized'], 401);

        $request->validate([
            'endpoint' => 'required|string',
            'keys.p256dh' => 'required|string',
            'keys.auth' => 'required|string',
        ]);

        $this->service->saveSubscription($guru, $request->only('endpoint', 'keys'));

        return response()->json(['success' => true]);
    }

    public function unsubscribe(Request $request)
    {
        $guru = $this->getGuru();
        if (!$guru) return response()->json(['error' => 'Unauthorized'], 401);

        $request->validate(['endpoint' => 'required|string']);
        $this->service->removeSubscription($request->endpoint);

        return response()->json(['success' => true]);
    }

    public function test()
    {
        try {
            $guru = $this->getGuru();
            if (!$guru) return response()->json(['error' => 'Unauthorized'], 401);

            $setting = GuruNotifikasiSetting::where('guru_id', $guru->id)->first();
            $mode = $setting?->mode ?? 'sound';

            $sent = $this->service->sendTestNotification($guru, $mode);

            return response()->json([
                'success' => $sent,
                'message' => $sent ? 'Notifikasi test berhasil dikirim!' : 'Gagal mengirim. Pastikan notifikasi diaktifkan di browser.',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }
}
