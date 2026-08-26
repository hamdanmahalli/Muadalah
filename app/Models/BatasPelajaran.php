<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BatasPelajaran extends Model
{
    protected $guarded = [];

    // Relasi balik ke tabel Pelajaran
    public function pelajaran()
    {
        return $this->belongsTo(Pelajaran::class);
    }
}