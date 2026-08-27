<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GuruNotifikasiSetting extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_enabled' => 'boolean',
    ];

    public function guru()
    {
        return $this->belongsTo(Guru::class);
    }
}
