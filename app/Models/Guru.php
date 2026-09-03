<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Guru extends Model
{
    use HasFactory;

    // Membuka gembok agar data bisa masuk sekaligus
    protected $guarded = [];

    // Kelas yang diampu sebagai wali kelas
    public function kelasWali()
    {
        return $this->hasMany(Kelas::class, 'wali_kelas_id');
    }

    // Jabatan pengurus (many-to-many via tabel guru_jabatan)
    public function jabatans()
    {
        return $this->belongsToMany(Jabatan::class, 'guru_jabatan')
            ->withPivot('is_utama')
            ->withTimestamps();
    }

    // Apakah pengurus merupakan guru (memiliki jabatan "Guru")
    public function isGuru()
    {
        return $this->jabatans()->where('nama_jabatan', 'Guru')->exists();
    }

    // Gabungan nama jabatan untuk ditampilkan, mis. "Wk. Kurikulum, TU"
    public function namaJabatan()
    {
        return $this->jabatans->pluck('nama_jabatan')->implode(', ');
    }
}
