<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Siswa extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'siswas';
    protected $guarded = [];

    protected $casts = [
        'tanggal_lahir' => 'date',
    ];

    public function angkatan()
    {
        return $this->hasMany(AngkatanSiswa::class);
    }

    public function tagihans()
    {
        return $this->hasMany(Tagihan::class);
    }

    public function nilais()
    {
        return $this->hasMany(Nilai::class);
    }

    public function kehadiran()
    {
        return $this->hasMany(KehadiranSiswa::class);
    }

    // Kelas aktif siswa pada periode tertentu
    public function kelasAktif($periodeId = null)
    {
        return $this->angkatan()
            ->when($periodeId, fn($q) => $q->where('periode_id', $periodeId))
            ->latest()
            ->first();
    }

    public function scopeAktif($query)
    {
        return $query->where('status', 'Aktif');
    }
}
