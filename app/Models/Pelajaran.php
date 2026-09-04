<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pelajaran extends Model
{
    protected $fillable = [
        'kode_pelajaran',
        'nama_pelajaran',
        'nama_kitab',
        'status',
        'kitab_tingkat',
    ];

    // CASTING KE ARRAY
    protected $casts = [
        'kitab_tingkat' => 'array', 
    ];

    public function batasPelajaran()
    {
        return $this->hasMany(BatasPelajaran::class);
    }
}
