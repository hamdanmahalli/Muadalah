<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Models\User;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Menciptakan 3 Jabatan Utama
        Role::create(['name' => 'Administrator']);
        Role::create(['name' => 'TU']);
        Role::create(['name' => 'Guru']);

        // 2. Mencari akun pertama di sistem Bapak (Akun Admin saat ini)
        $admin = User::find(1); 
        
        // 3. Menobatkan akun tersebut sebagai Administrator Mutlak
        if ($admin) {
            $admin->assignRole('Administrator');
        }
    }
}