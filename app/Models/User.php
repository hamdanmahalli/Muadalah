<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passkeys\Contracts\PasskeyUser;
use Laravel\Passkeys\PasskeyAuthenticatable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements PasskeyUser
{
    use HasFactory, HasRoles, Notifiable, PasskeyAuthenticatable;
    
    // Relasi ke Data Guru
    public function guru()
    {
        return $this->belongsTo(Guru::class, 'name', 'nama_guru');
    }

    // Membuka gembok agar data dari web bisa masuk sekaligus
    protected $fillable = [
        'lembaga',
        'username',
        'name',
        'email',
        'hp',
        'role',
        'status',
        'tema',
        'email_verified_at',
        'password',
        'remember_token',
        'active_session_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Label akses untuk tampilan (footer, tabel user, dll).
     * Administrator sejati = role; selain itu = jumlah fasilitas menu per-user.
     */
    public function aksesLabel(): string
    {
        if ($this->hasRole('Administrator')) {
            return 'Administrator';
        }
        $n = $this->getPermissionNames()->count();
        if ($n === 0) {
            return 'Tanpa akses';
        }
        return 'Manual (' . $n . ')';
    }
}