<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KehadiranKegiatan extends Model
{
    protected $fillable = [
        'agenda_kegiatan_id',
        'guru_id',
        'waktu_hadir',
        'metode',
        'status',
        'keterangan',
    ];

    // Relasi balik ke Agenda
    public function agenda()
    {
        return $this->belongsTo(AgendaKegiatan::class, 'agenda_kegiatan_id');
    }

    // Relasi ke Master Guru
    public function guru()
    {
        return $this->belongsTo(Guru::class);
    }
}