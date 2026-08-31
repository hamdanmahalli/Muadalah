<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KehadiranGuru extends Model
{
    // 1. Memberi tahu sistem nama tabel aslinya di PostgreSQL
    protected $table = 'kehadiran_gurus';


    // Membuka gembok keamanan agar semua kolom bisa diisi otomatis via AJAX
    protected $guarded = [];

    // Relasi ke jadwal yang menjadi sumber sijadwal kehadiran ini
    public function jadwal()
    {
        return $this->belongsTo(JadwalHarian::class, 'jadwal_id');
    }
}