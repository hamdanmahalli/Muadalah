<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kelas extends Model
{
    protected $table = 'kelas';
    protected $fillable = [
        'nama_kelas',
        'tingkat',
        'wali_kelas_id',
    ];

    // Wali kelas (relasi ke Guru)
    public function waliKelas()
    {
        return $this->belongsTo(Guru::class, 'wali_kelas_id');
    }

    public function angkatan()
    {
        return $this->hasMany(AngkatanSiswa::class);
    }

    // Semua murid aktif di kelas ini (lewat angkatan)
    public function siswas()
    {
        return $this->hasManyThrough(
            Siswa::class,
            AngkatanSiswa::class,
            'kelas_id',
            'id',
            'id',
            'siswa_id'
        );
    }
}
