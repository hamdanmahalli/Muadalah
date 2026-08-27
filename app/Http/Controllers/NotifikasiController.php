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

        $deviceCount = \App\Models\PushSubscription::where('guru_id', $guru->id)->count();
        $pulseCount = \App\Models\PushEvent::where('created_at', '>=', now()->subMinutes(10))->count();

        // Informasi kunci subskripsi tersimpan (untuk deteksi korupsi data)
        $storedKey = \App\Models\PushSubscription::where('guru_id', $guru->id)->first();
        $storedKeyInfo = $storedKey
            ? ['p256dh' => strlen($storedKey->p256dh), 'auth' => strlen($storedKey->auth)]
            : null;

        return view('guru.notifikasi-pengaturan', compact('guru', 'setting', 'deviceCount', 'pulseCount', 'storedKeyInfo'));
    }

    public function simpan(Request $request)
    {
        $guru = $this->getGuru();
        if (!$guru) abort(404);

        $request->validate([
            'is_enabled' => 'required|boolean',
            'mode' => 'required|in:sound,vibrate,silent',
            'reminder_minutes' => 'required|integer|in:10,15,30,45,60',
        ]);

        GuruNotifikasiSetting::updateOrCreate(
            ['guru_id' => $guru->id],
            [
                'is_enabled' => $request->boolean('is_enabled'),
                'mode' => $request->mode,
                'reminder_minutes' => $request->reminder_minutes,
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

            $result = $this->service->sendTestNotification($guru, $mode);

            return response()->json([
                'success' => $result['success'],
                'message' => $result['message'],
                'total' => $result['total'],
                'success_count' => $result['success_count'],
                'failed_count' => $result['failed_count'],
                'detail' => $result['detail'] ?? [],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function testPulse()
    {
        try {
            $guru = $this->getGuru();
            if (!$guru) return response()->json(['error' => 'Unauthorized'], 401);

            $result = $this->service->sendTestNoPayload($guru);

            return response()->json([
                'success' => $result['success'],
                'message' => $result['message'],
                'total' => $result['total'],
                'success_count' => $result['success_count'],
                'failed_count' => $result['failed_count'],
                'detail' => $result['detail'] ?? [],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function pulse(Request $request)
    {
        $tag = $request->input('tag', '-');
        $title = $request->input('title', '-');

        \Illuminate\Support\Facades\Log::info('PUSH-PULSE: push sampai di perangkat', [
            'tag' => $tag,
            'title' => $title,
            'waktu' => now()->toDateTimeString(),
            'agent' => $request->userAgent(),
        ]);

        try {
            \App\Models\PushEvent::create([
                'tag' => $tag,
                'title' => $title,
                'user_agent' => substr((string) $request->userAgent(), 0, 255),
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('PUSH-PULSE gagal simpan: ' . $e->getMessage());
        }

        return response()->json(['success' => true]);
    }
}
