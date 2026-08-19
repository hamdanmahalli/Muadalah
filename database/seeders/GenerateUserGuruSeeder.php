<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Guru;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class GenerateUserGuruSeeder extends Seeder
{
    public function run(): void
    {
        $gurus = Guru::all();
        $createdCount = 0;

        foreach ($gurus as $guru) {
            // Cek apakah User dengan Username (NIG) ini sudah ada atau belum
            $existingUser = User::where('username', $guru->nig)->first();

            if (!$existingUser) {
                // Buat Akun User dengan Username = NIG
                $user = User::create([
                    'lembaga'  => 'PONDOK',
                    'username' => $guru->nig,
                    'name'     => $guru->nama_guru,
                    'email'    => $guru->nig . '@pesantren.com',
                    'hp'       => $guru->no_hp,
                    'status'   => 'Aktif',
                    'password' => Hash::make('123456'),
                ]);

                // Pasang Role Dewan Guru
                $user->assignRole('Dewan Guru');
                $createdCount++;
            }
        }

        $this->command->info("Selesai! Berhasil membuat {$createdCount} akun Dewan Guru baru (Username = NIG | Password = 123456).");
    }
}