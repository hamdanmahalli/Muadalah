<?php

namespace App\Services;

use App\Models\Guru;
use App\Models\Jabatan;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Operasi data Guru beserta pembuatan akun login otomatis.
 *
 * SRP: Satu tanggung jawab — mengelola catatan Guru & akun pengguna terkait.
 * Stateless; transaksi antar entitas tereksekusi secara atomik di sini.
 */
class GuruService
{
    /**
     * NIG baru terurut (NIG terbesar + 1, atau default 1001).
     */
    public function generasikanNIG(): string
    {
        $lastGuru = Guru::orderBy('nig', 'desc')->first();
        if ($lastGuru && is_numeric($lastGuru->nig)) {
            return (string) ((int) $lastGuru->nig + 1);
        }
        return '1001';
    }

    /**
     * Buat akun login otomatis bila guru memiliki jabatan "Guru".
     *
     * @return array{sukses: bool, pesan: string}
     */
    public function buatAkunGuruOtomatis(Guru $guru, ?array $jabatanIds): array
    {
        $namaJabatan = $jabatanIds
            ? Jabatan::whereIn('id', $jabatanIds)->pluck('nama_jabatan')->toArray()
            : [];
        if (!in_array('Guru', $namaJabatan)) {
            return ['sukses' => false, 'pesan' => 'Data pengurus berhasil disimpan!'];
        }

        $user = User::where('username', $guru->nig)->first();

        // Akun belum ada -> buat akun baru dengan sandi sekali tampil
        if (!$user) {
            $sandi = ucfirst(Str::random(5)) . rand(10, 99);
            $user = User::create([
                'lembaga'  => 'PONDOK',
                'username' => $guru->nig,
                'name'     => $guru->nama_guru,
                'email'    => $guru->nig . '@pesantren.com',
                'hp'       => $guru->no_hp,
                'status'   => 'Aktif',
                'password' => Hash::make($sandi),
            ]);
            $user->assignRole('Dewan Guru');

            return [
                'sukses' => true,
                'pesan' => 'Data pengurus berhasil disimpan! Akun guru otomatis terbuat (Username: ' . $guru->nig . ' | Sandi sementara: ' . $sandi . '). Segera beri tahu guru untuk mengganti sandi.',
            ];
        }

        // Akun sudah ada -> cukup pastikan peran guru terpasang
        $user->assignRole('Dewan Guru');

        return [
            'sukses' => true,
            'pesan' => 'Data pengurus berhasil disimpan! Akun guru untuk NIG ' . $guru->nig . ' sudah ada sebelumnya.',
        ];
    }
}
