<?php

namespace App\Console\Commands;

use Database\Seeders\ConvertRoleToDirectPermissionSeeder;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('akses:convert')]
#[Description('Konversi sekali-jalan role lama -> akses per-user (alias pendek untuk ConvertRoleToDirectPermissionSeeder).')]
class ConvertAksesPerUser extends Command
{
    public function handle()
    {
        $this->call('db:seed', [
            '--class' => ConvertRoleToDirectPermissionSeeder::class,
            '--force' => true,
        ]);

        return Command::SUCCESS;
    }
}