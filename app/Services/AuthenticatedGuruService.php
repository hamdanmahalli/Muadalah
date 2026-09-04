<?php

namespace App\Services;

use App\Models\Guru;
use App\Models\User;

/**
 * Menyelesaikan data Guru dari Pengguna yang sedang login.
 *
 * SRP: Satu tanggung jawab — memetakan User autentik ke record Guru.
 * Pola pencarian diulang di banyak controller (Auth, Scan, Notifikasi,
 * Jadwal, SiswaSaya), sehingga dipusatkan di satu layanan.
 */
class AuthenticatedGuruService
{
    public function fromAuthUser(?User $user = null): ?Guru
    {
        $user = $user ?: auth()->user();
        if (!$user) {
            return null;
        }

        return Guru::where('nig', $user->username)->first()
            ?? $user->guru
            ?? Guru::where('nama_guru', $user->name)->first();
    }

    public function fromUser(User $user): ?Guru
    {
        return Guru::where('nig', $user->username)->first()
            ?? $user->guru
            ?? Guru::where('nama_guru', $user->name)->first();
    }
}
