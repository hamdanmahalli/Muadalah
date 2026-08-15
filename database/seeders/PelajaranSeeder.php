<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Pelajaran;

class PelajaranSeeder extends Seeder
{
    public function run(): void
    {
        $daftarPelajaran = [
            'Fiqh', 'Nahwu', 'Shorof', 'Tauhid', 'Balaghoh', 
            'Hadist', 'M. Hadits', 'Q. Fiqh', 'Tafsir', 'U. Fiqh', 
            'Tarikh', 'B. Arab', 'Akhlak', 'Tajwid', 'Imla', 
            'B. INDO', 'BMKK', 'IPAS', 'MTK', 'PKN', 
            'PPKN', 'TIK', 'PJOK'
        ];

        foreach ($daftarPelajaran as $index => $nama) {
            // Membuat kode otomatis rapi (MP-001, MP-002, dst)
            $kode = 'MP-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT);

            Pelajaran::firstOrCreate(
                ['nama_pelajaran' => $nama],
                [
                    'kode_pelajaran' => $kode,
                    'nama_kitab' => '-' // Bisa diisi atau diedit manual nanti lewat web
                ]
            );
        }
    }
}