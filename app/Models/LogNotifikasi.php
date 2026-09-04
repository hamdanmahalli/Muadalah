<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogNotifikasi extends Model
{
    protected $fillable = [
        'guru_id',
        'jadwal_id',
        'tanggal',
        'sent_at',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'sent_at' => 'datetime',
    ];

    public function guru()
    {
        return $this->belongsTo(Guru::class);
    }

    public function jadwal()
    {
        return $this->belongsTo(JadwalHarian::class, 'jadwal_id');
    }
}
