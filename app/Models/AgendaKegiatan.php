<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class AgendaKegiatan extends Model
{
    protected $fillable = [
        'periode_id',
        'nama_kegiatan',
        'tanggal',
        'jam_mulai',
        'jam_selesai',
        'lokasi',
        'qr_token',
        'is_open',
    ];

    // Relasi ke tabel periode
    public function periode()
    {
        return $this->belongsTo(Periode::class);
    }

    // Relasi ke data kehadiran
    public function kehadiran()
    {
        return $this->hasMany(KehadiranKegiatan::class, 'agenda_kegiatan_id');
    }

    // Fungsi otomatis untuk men-generate kode QR unik saat admin membuat acara baru
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($agenda) {
            if (empty($agenda->qr_token)) {
                $agenda->qr_token = 'AGENDA-' . strtoupper(Str::random(10));
            }
        });
    }
}