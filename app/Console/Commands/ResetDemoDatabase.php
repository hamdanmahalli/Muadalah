<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class ResetDemoDatabase extends Command
{
    protected $signature = 'demo:reset';
    protected $description = 'Reset total database mode demo (buang semua data lalu isi ulang DemoSeeder). Hanya berjalan bila APP_DEMO=true.';

    public function handle(): int
    {
        if (config('app.demo') !== true) {
            $this->error('Command demo:reset hanya boleh dijalankan di instance mode demo (APP_DEMO=true). Dibatalkan.');
            return Command::FAILURE;
        }

        $this->info('Mode demo terdeteksi. Menghapus seluruh data demo dan menyiapkan ulang...');

        Artisan::call('migrate:fresh', ['--force' => true]);
        $this->info('Migrasi dibuat ulang.');

        Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\PermissionSeeder', '--force' => true]);
        $this->info('PermissionSeeder selesai.');

        Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\DemoSeeder', '--force' => true]);
        $this->info('DemoSeeder selesai.');

        Artisan::call('permission:cache-reset');
        $this->info('Cache permission dibersihkan.');

        $this->info('Reset mode demo selesai. Silakan login ulang dengan tombol "Masuk Demo".');
        return Command::SUCCESS;
    }
}