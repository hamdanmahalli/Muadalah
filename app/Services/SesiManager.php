<?php

namespace App\Services;

use App\Models\User;

/**
 * Mengelola "kepemilikan sesi aktif" milik user.
 *
 * Aturan: satu akun hanya boleh aktif di satu perangkat.
 * Pemilik sesi disimpan di kolom users.active_session_id,
 * sehingga berlaku untuk driver sesi apa pun (file/database).
 */
class SesiManager
{
    /**
     * Apakah sebuah sesi masih hidup (file/row masih ada & belum kedaluwarsa)?
     */
    public function masihAktif(?string $sessionId): bool
    {
        if ($sessionId === null || $sessionId === '') {
            return false;
        }

        return app('session')->getHandler()->read($sessionId) !== '';
    }

    /**
     * Hancurkan sebuah sesi di sisi server (memutus perangkat lain).
     */
    public function putusSesi(?string $sessionId): void
    {
        if ($sessionId === null || $sessionId === '') {
            return;
        }

        app('session')->getHandler()->destroy($sessionId);
    }

    /**
     * Tetapkan sesi sebagai pemilik aktif milik user.
     */
    public function tetapkan(User $user, ?string $sessionId): void
    {
        if ($sessionId === null || $sessionId === '') {
            return;
        }

        $user->update(['active_session_id' => $sessionId]);
    }

    /**
     * Lepas kepemilikan bila sesi tsb masih menjadi pemilik (untuk logout).
     */
    public function lepas(User $user, ?string $sessionId): void
    {
        if ($sessionId === null || $sessionId === '') {
            return;
        }

        if ($user->active_session_id === $sessionId) {
            $user->update(['active_session_id' => null]);
        }
    }

    /**
     * Cari sesi lain yang masih hidup milik user selain $currentSid.
     * Mengembalikan id sesi lama tersebut, atau null bila tidak ada konflik.
     */
    public function cariSesiLain(User $user, ?string $currentSid): ?string
    {
        $owner = $user->active_session_id;

        if ($owner === null || $owner === '' || $owner === $currentSid) {
            return null;
        }

        return $this->masihAktif($owner) ? $owner : null;
    }
}