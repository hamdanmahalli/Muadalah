<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KehadiranKegiatan extends Model
{
    protected $guarded = [];

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