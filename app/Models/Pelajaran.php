<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pelajaran extends Model
{
    protected $guarded = [];

    // CASTING KE ARRAY
    protected $casts = [
        'kitab_tingkat' => 'array', 
    ];

    public function batasPelajaran()
    {
        return $this->hasMany(BatasPelajaran::class);
    }
}
