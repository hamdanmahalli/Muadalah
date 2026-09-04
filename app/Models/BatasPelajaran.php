<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BatasPelajaran extends Model
{
    protected $fillable = [
        'periode_id',
        'pelajaran_id',
        'tingkat',
        'mulai_dari',
        'batas_uts_ganjil',
        'batas_uas_ganjil',
        'batas_uts_genap',
        'batas_uas_genap',
    ];

    // Relasi balik ke tabel Pelajaran
    public function pelajaran()
    {
        return $this->belongsTo(Pelajaran::class);
    }
}