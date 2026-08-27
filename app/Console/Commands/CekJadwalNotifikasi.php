<?php

namespace App\Console\Commands;

use App\Services\NotificationService;
use Illuminate\Console\Command;

class CekJadwalNotifikasi extends Command
{
    protected $signature = 'notifikasi:cek-jadwal';
    protected $description = 'Cek jadwal 30 menit lagi dan kirim notifikasi push ke guru';

    public function handle(NotificationService $service): int
    {
        $sent = $service->checkAndSendNotifications();

        if ($sent > 0) {
            $this->info("Notifikasi terkirim: {$sent}");
        }

        return Command::SUCCESS;
    }
}
