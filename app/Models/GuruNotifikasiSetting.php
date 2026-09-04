<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GuruNotifikasiSetting extends Model
{
    protected $fillable = [
        'guru_id',
        'is_enabled',
        'mode',
        'reminder_minutes',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
    ];

    public function guru()
    {
        return $this->belongsTo(Guru::class);
    }
}
