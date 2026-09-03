<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Jabatan;

class JabatanSeeder extends Seeder
{
    public function run(): void
    {
        $daftar = [
            ['nama_jabatan' => 'Guru',                  'deskripsi' => 'Pengajar di kelas'],
            ['nama_jabatan' => 'Kepala Sekolah',        'deskripsi' => 'Pimpinan sekolah'],
            ['nama_jabatan' => 'Wakil Kepala',          'deskripsi' => 'Wakil kepala sekolah'],
            ['nama_jabatan' => 'Wakil Kurikulum',       'deskripsi' => 'Penanggung jawab kurikulum'],
            ['nama_jabatan' => 'Tata Usaha (TU)',       'deskripsi' => 'Administrasi sekolah'],
            ['nama_jabatan' => 'Bendahara',             'deskripsi' => 'Pengelola keuangan'],
            ['nama_jabatan' => 'Wali Kelas',            'deskripsi' => 'Penanggung jawab kelas'],
            ['nama_jabatan' => 'Pengurus Asrama',       'deskripsi' => 'Pengelola asrama'],
        ];

        foreach ($daftar as $j) {
            Jabatan::firstOrCreate(['nama_jabatan' => $j['nama_jabatan']], ['deskripsi' => $j['deskripsi']]);
        }
    }
}
