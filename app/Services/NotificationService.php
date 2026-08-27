<?php

namespace App\Services;

use App\Models\Guru;
use App\Models\GuruNotifikasiSetting;
use App\Models\JadwalHarian;
use App\Models\LogNotifikasi;
use App\Models\MasterJam;
use App\Models\PushSubscription;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

class NotificationService
{
    private WebPush $webPush;

    public function __construct()
    {
        $this->webPush = new WebPush([
            'VAPID' => [
                'subject' => config('webpush.vapid.subject'),
                'publicKey' => config('webpush.vapid.public_key'),
                'privateKey' => config('webpush.vapid.private_key'),
            ],
        ]);
    }

    public function checkAndSendNotifications(): int
    {
        // PENGINGAT DINONAKTIFKAN SEMENTARA (sampai versi APK).
        // Aktifkan kembali dengan set NOTIFIKASI_AKTIF=true di .env lalu clear cache.
        if (!filter_var(env('NOTIFIKASI_AKTIF', false), FILTER_VALIDATE_BOOLEAN)) return 0;

        $now = Carbon::now();
        $hariIni = map_hari($now->isoFormat('dddd'));
        $waktuSekarang = $now->format('H:i:s');
        $hariLibur = \App\Models\AgendaKaldik::where('jenis_agenda', 'Libur')
            ->where('tanggal_mulai', '<=', $now->toDateString())
            ->where('tanggal_selesai', '>=', $now->toDateString())
            ->exists();

        if ($hariLibur) return 0;

        $jadwals = JadwalHarian::where('hari', $hariIni)
            ->whereHas('guru', fn($q) => $q->where('status', 'Aktif'))
            ->with(['guru', 'kelas', 'pelajaran'])
            ->get();

        $sentCount = 0;

        foreach ($jadwals as $jadwal) {
            $masterJam = MasterJam::where('jam_ke', $jadwal->jam_ke)->first();
            if (!$masterJam) continue;

            $setting = GuruNotifikasiSetting::where('guru_id', $jadwal->guru_id)->first();
            if (!$setting || !$setting->is_enabled) continue;

            $jamMulai = $masterJam->jam_mulai;
            $waktuAkhir = $now->copy()->addMinutes($setting->reminder_minutes)->format('H:i:s');

            if ($jamMulai <= $waktuAkhir && $jamMulai > $waktuSekarang) {
                $alreadySent = LogNotifikasi::where('guru_id', $jadwal->guru_id)
                    ->where('jadwal_id', $jadwal->id)
                    ->whereDate('tanggal', $now->toDateString())
                    ->exists();

                if ($alreadySent) continue;

                $this->sendNotification($jadwal->guru, $jadwal, $setting->mode);

                Log::info('Notifikasi jadwal dikirim', [
                    'guru_id' => $jadwal->guru_id,
                    'jadwal_id' => $jadwal->id,
                    'jam_mulai' => $jamMulai,
                    'reminder_minutes' => $setting->reminder_minutes,
                ]);

                LogNotifikasi::create([
                    'guru_id' => $jadwal->guru_id,
                    'jadwal_id' => $jadwal->id,
                    'tanggal' => $now->toDateString(),
                    'sent_at' => $now,
                ]);

                $sentCount++;
            }
        }

        return $sentCount;
    }

    public function sendNotification(Guru $guru, JadwalHarian $jadwal, string $mode): void
    {
        $subscriptions = PushSubscription::where('guru_id', $guru->id)->get();
        if ($subscriptions->isEmpty()) return;

        $jam = MasterJam::where('jam_ke', $jadwal->jam_ke)->first();
        $waktu = $jam ? $jam->jam_mulai : '-';
        $namaKelas = $jadwal->kelas->nama_kelas ?? '-';
        $namaMapel = $jadwal->pelajaran->nama_pelajaran ?? '-';

        $title = 'Jadwal Mengajar 30 Menit Lagi';
        $body = "Anda mengajar {$namaMapel} di {$namaKelas} pada {$waktu}";

        $payload = json_encode([
            'title' => $title,
            'body' => $body,
            'mode' => $mode,
            'icon' => '/icons/icon-192x192.png',
            'badge' => '/icons/icon-192x192.png',
            'tag' => "jadwal-{$jadwal->id}-{$jadwal->jam_ke}",
            'url' => '/dashboard-guru',
        ]);

        foreach ($subscriptions as $sub) {
            $webPushSub = Subscription::create([
                'endpoint' => $sub->endpoint,
                'keys' => ['p256dh' => $sub->p256dh, 'auth' => $sub->auth],
            ]);

            $this->webPush->sendOneNotification($webPushSub, $payload);
        }
    }

    public function sendTestNotification(Guru $guru, string $mode): array
    {
        $subscriptions = PushSubscription::where('guru_id', $guru->id)->get();

        $total = $subscriptions->count();
        $success = 0;
        $failed = 0;
        $detail = [];

        if ($total === 0) {
            return [
                'success' => false,
                'total' => 0,
                'success_count' => 0,
                'failed_count' => 0,
                'detail' => [],
                'message' => 'Belum ada perangkat terhubung. Buka Pengaturan Notifikasi lalu ketuk "Hubungkan Perangkat Ini".',
            ];
        }

        $payload = json_encode([
            'title' => 'Notifikasi Test',
            'body' => 'Notifikasi berhasil dikirim! Mode: ' . $mode,
            'mode' => $mode,
            'icon' => '/icons/icon-192x192.png',
            'badge' => '/icons/icon-192x192.png',
            'tag' => 'test-notif',
            'url' => '/dashboard-guru',
        ]);

        foreach ($subscriptions as $sub) {
            $webPushSub = Subscription::create([
                'endpoint' => $sub->endpoint,
                'keys' => ['p256dh' => $sub->p256dh, 'auth' => $sub->auth],
            ]);

            $result = $this->webPush->sendOneNotification($webPushSub, $payload);

            $statusCode = $result->getResponse()?->getStatusCode();
            $reason = $result->getReason();

            $detail[] = [
                'host' => parse_url($sub->endpoint, PHP_URL_HOST) ?? '-',
                'success' => $result->isSuccess(),
                'status_code' => $statusCode,
                'reason' => $reason,
            ];

            if ($result->isSuccess()) {
                $success++;
            } else {
                $failed++;
                if ($result->isSubscriptionExpired()) {
                    $sub->delete();
                }
            }
        }

        $semuaSukses = $failed === 0 && $success > 0;

        return [
            'success' => $semuaSukses,
            'total' => $total,
            'success_count' => $success,
            'failed_count' => $failed,
            'detail' => $detail,
            'message' => $semuaSukses
                ? "Berhasil! Notification test terkirim ke {$success} perangkat."
                : ($success > 0
                    ? "Sebagian gagal: {$failed} dari {$total} perangkat tidak menerima (mungkin sudah tidak aktif)."
                    : "Gagal terkirim ke semua {$total} perangkat. Periksa detail status di bawah."),
        ];
    }

    public function sendTestNoPayload(Guru $guru): array
    {
        $subscriptions = PushSubscription::where('guru_id', $guru->id)->get();
        $total = $subscriptions->count();
        $success = 0;
        $failed = 0;
        $detail = [];

        if ($total === 0) {
            return [
                'success' => false,
                'total' => 0,
                'success_count' => 0,
                'failed_count' => 0,
                'detail' => [],
                'message' => 'Belum ada perangkat terhubung.',
            ];
        }

        foreach ($subscriptions as $sub) {
            $webPushSub = Subscription::create([
                'endpoint' => $sub->endpoint,
                'keys' => ['p256dh' => $sub->p256dh, 'auth' => $sub->auth],
            ]);

            // Payload null => push TANPA enkripsi. Memisahkan masalah enkripsi vs masalah pengiriman FCM.
            $result = $this->webPush->sendOneNotification($webPushSub);

            $detail[] = [
                'host' => parse_url($sub->endpoint, PHP_URL_HOST) ?? '-',
                'success' => $result->isSuccess(),
                'status_code' => $result->getResponse()?->getStatusCode(),
                'reason' => $result->getReason(),
            ];

            if ($result->isSuccess()) {
                $success++;
            } else {
                $failed++;
                if ($result->isSubscriptionExpired()) {
                    $sub->delete();
                }
            }
        }

        $semuaSukses = $failed === 0 && $success > 0;

        return [
            'success' => $semuaSukses,
            'total' => $total,
            'success_count' => $success,
            'failed_count' => $failed,
            'detail' => $detail,
            'message' => $semuaSukses
                ? "Push mentah (tanpa payload) diterima FCM untuk {$success} perangkat."
                : "Push mentah gagal: {$failed} dari {$total} perangkat.",
        ];
    }

    public function saveSubscription(Guru $guru, array $subscription): void
    {
        PushSubscription::updateOrCreate(
            ['endpoint' => $subscription['endpoint']],
            [
                'guru_id' => $guru->id,
                'p256dh' => $subscription['keys']['p256dh'],
                'auth' => $subscription['keys']['auth'],
                'user_agent' => request()->userAgent(),
            ]
        );
    }

    public function removeSubscription(string $endpoint): void
    {
        PushSubscription::where('endpoint', $endpoint)->delete();
    }
}
