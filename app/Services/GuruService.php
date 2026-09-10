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
 * Hak akses akun = permission langsung per user, diatur manual di Setup User (kosong saat akun dibuat).
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

        $hasil = $this->siapkanAkun($guru, 'Dewan Guru');

        return [
            'sukses' => true,
            'pesan' => 'Data pengurus berhasil disimpan! ' . $hasil['pesan'],
        ];
    }

    /**
     * Buat/pastikan akun login untuk guru apa pun (jabatan apa pun).
     * Dipakai tombol "Buat Akun" di Master Guru. Hak akses selalu kosong.
     *
     * @return array{sukses: bool, pesan: string, sandi: ?string}
     */
    public function buatAkunManual(Guru $guru): array
    {
        $hasil = $this->siapkanAkun($guru);
        return [
            'sukses' => true,
            'pesan' => $hasil['pesan'],
            'sandi' => $hasil['sandi'] ?? null,
        ];
    }

    /**
     * Inti pembuatan/pembaruan akun: username = NIG, hak akses kosong (diatur manual).
     *
     * @return array{pesan: string, sandi: ?string}
     */
    private function siapkanAkun(Guru $guru): array
    {
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
                'role'     => null,
                'password' => Hash::make($sandi),
            ]);

            return [
                'pesan' => 'Akun guru otomatis terbuat (Username: ' . $guru->nig . ' | Sandi sementara: ' . $sandi . ' | Hak akses masih kosong). Segera beri tahu guru untuk mengganti sandi; beri fasilitas menu lewat Setup User.',
                'sandi' => $sandi,
            ];
        }

        // Akun sudah ada -> tidak mengubah apa pun; akses diatur manual.
        return [
            'pesan' => 'Akun untuk NIG ' . $guru->nig . ' sudah ada sebelumnya; akun tidak diubah. Atur fasilitas menu lewat Setup User.',
            'sandi' => null,
        ];
    }
}