<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Guru;
use App\Models\MasterJam;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Memasukkan Akun Admin Utama (Lengkap dengan username)
        User::firstOrCreate(
            ['email' => 'admin@pesantren.com'],
            [
                'username' => 'admin', // Kolom wajib yang sebelumnya kosong
                'name' => 'Administrator TU',
                'role' => 'OPERATOR INSTANSI', // Menyesuaikan struktur tabel Bapak
                'status' => 'Aktif',           // Menyesuaikan struktur tabel Bapak
                'password' => Hash::make('password123'),
            ]
        );

        // 2. Memasukkan Data Master Guru (53 Guru)
        $daftarGuru = [
            ['nig' => '1001', 'nama' => 'Gus H. A. Bachtiar Yogiarto'],
            ['nig' => '1002', 'nama' => 'Ust. Rizan'],
            ['nig' => '1003', 'nama' => 'Lr. Affan Zainul M'],
            ['nig' => '1004', 'nama' => 'Lr. M. Zadid Taqwa'],
            ['nig' => '1005', 'nama' => 'Lr. Hifni Zainul M'],
            ['nig' => '1006', 'nama' => 'Ust. Zuhdiyanto'],
            ['nig' => '1007', 'nama' => 'Neng Fidela Devina M'],
            ['nig' => '1008', 'nama' => 'Neng Indah Ramadhani'],
            ['nig' => '1009', 'nama' => 'Neng Silfia'],
            ['nig' => '1010', 'nama' => 'Ustd. Melly.Y.I'],
            ['nig' => '1011', 'nama' => 'Ust. A. Rizal'],
            ['nig' => '1012', 'nama' => 'Ustd. Rindiani Ismatul M'],
            ['nig' => '1013', 'nama' => 'Ust. Abdul Muiz'],
            ['nig' => '1014', 'nama' => 'Neng. Nilufarul Azzah'],
            ['nig' => '1015', 'nama' => 'Ust. Abdurrahman'],
            ['nig' => '1016', 'nama' => 'Ust. Agus Sugianto'],
            ['nig' => '1017', 'nama' => 'Ust. Alfanul karim'],
            ['nig' => '1018', 'nama' => 'Ust. Aliwafa'],
            ['nig' => '1019', 'nama' => 'Ust. Didik Sulaiman'],
            ['nig' => '1020', 'nama' => 'Lr. Hamdan Mahalli'],
            ['nig' => '1021', 'nama' => 'Ust. Ghufron Handika'],
            ['nig' => '1022', 'nama' => 'Ust. Hasan A'],
            ['nig' => '1023', 'nama' => 'Ust. Ikrom Hamdani'],
            ['nig' => '1024', 'nama' => 'Ust. Imam Haramain'],
            ['nig' => '1025', 'nama' => 'Ust. Khoiruddin'],
            ['nig' => '1026', 'nama' => 'Ustd. Ifa Nurhasanah'],
            ['nig' => '1027', 'nama' => 'Ust. Anshori As'],
            ['nig' => '1028', 'nama' => 'Ust. Agung Eka Putra'],
            ['nig' => '1029', 'nama' => 'Ust. M. Muqorrobin'],
            ['nig' => '1030', 'nama' => 'Ust. Muhyiddin'],
            ['nig' => '1031', 'nama' => 'Ust. Muwafiq'],
            ['nig' => '1032', 'nama' => 'Ust. Agus Herdiyanto'],
            ['nig' => '1033', 'nama' => 'Ust. Purwadi'],
            ['nig' => '1034', 'nama' => 'Ust. Akbarul Hikam'],
            ['nig' => '1035', 'nama' => 'Ust. Gufron Mahsusi'],
            ['nig' => '1036', 'nama' => 'Ustd. Azkia Ramadhani'],
            ['nig' => '1037', 'nama' => 'Ust. Andrean Maulana'],
            ['nig' => '1038', 'nama' => 'Ust. Ilham As-Siddiq'],
            ['nig' => '1039', 'nama' => 'Ust. Yuda Hardana'],
            ['nig' => '1040', 'nama' => 'Ust. M.Rofiqie'],
            ['nig' => '1041', 'nama' => 'Ust. Aldi Prasetyo'],
            ['nig' => '1042', 'nama' => 'Ust. Rian Abdullah'],
            ['nig' => '1043', 'nama' => 'Ust. Rianto Hidayat'],
            ['nig' => '1044', 'nama' => 'Ustd. Bilqis Manzila A'],
            ['nig' => '1045', 'nama' => 'Ustd. Cindy Amelia P'],
            ['nig' => '1046', 'nama' => 'Ustd. Ernita Witaloka'],
            ['nig' => '1047', 'nama' => 'Ustd. Milisa Riskiatul A'],
            ['nig' => '1048', 'nama' => 'Ustd. Nabila Putri Haryana'],
            ['nig' => '1049', 'nama' => 'Ustd. Rifatul Imamiyah'],
            ['nig' => '1050', 'nama' => 'Ustd.Titin Islamiyah'],
            ['nig' => '1051', 'nama' => 'Ustd. Afifatul Ilmiyah'],
            ['nig' => '1052', 'nama' => 'Ustd. Amelia Dwi Lestari'],
            ['nig' => '1053', 'nama' => 'Ny. Lailatul Badriyah'],
        ];

        foreach ($daftarGuru as $guru) {
            Guru::updateOrCreate(
                ['nig' => $guru['nig']],
                ['nama_guru' => $guru['nama']]
            );
        }

        // 3. Memasukkan Data Master Jam Pelajaran Real (Kebijakan 30 Menit)
        // (Pastikan tabel master_jam sudah ada via migrasi, atau lewati jika menggunakan jam statis)
        if (class_exists(MasterJam::class)) {
            MasterJam::updateOrCreate(['jam_ke' => 1], ['jam_mulai' => '07:00:00', 'jam_selesai' => '07:30:00']);
            MasterJam::updateOrCreate(['jam_ke' => 2], ['jam_mulai' => '07:30:00', 'jam_selesai' => '08:00:00']);
            MasterJam::updateOrCreate(['jam_ke' => 3], ['jam_mulai' => '08:00:00', 'jam_selesai' => '08:30:00']);
            MasterJam::updateOrCreate(['jam_ke' => 4], ['jam_mulai' => '08:30:00', 'jam_selesai' => '09:00:00']);
            
            MasterJam::updateOrCreate(['jam_ke' => 5], ['jam_mulai' => '10:00:00', 'jam_selesai' => '10:30:00']);
            MasterJam::updateOrCreate(['jam_ke' => 6], ['jam_mulai' => '10:30:00', 'jam_selesai' => '11:00:00']);
            MasterJam::updateOrCreate(['jam_ke' => 7], ['jam_mulai' => '11:00:00', 'jam_selesai' => '11:30:00']);
            MasterJam::updateOrCreate(['jam_ke' => 8], ['jam_mulai' => '11:30:00', 'jam_selesai' => '12:00:00']);
            
            MasterJam::updateOrCreate(['jam_ke' => 9], ['jam_mulai' => '12:45:00', 'jam_selesai' => '13:15:00']);
            MasterJam::updateOrCreate(['jam_ke' => 10], ['jam_mulai' => '13:15:00', 'jam_selesai' => '13:45:00']);
        }

        // 4. Memanggil Seeder Pelajaran (PelajaranSeeder) secara bersih
        $this->call([
            PelajaranSeeder::class,
        ]);
    }
}