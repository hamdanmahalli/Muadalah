<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Jabatan extends Model
{
    protected $table = 'jabatans';
    protected $fillable = [
        'nama_jabatan',
        'deskripsi',
        'status',
    ];

    // Pengurus yang memiliki jabatan ini (many-to-many)
    public function pengurus()
    {
        return $this->belongsToMany(Guru::class, 'guru_jabatan');
    }
}
