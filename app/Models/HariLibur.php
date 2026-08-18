<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HariLibur extends Model
{
    use HasFactory;

    // BUKA GEMBOK TOTAL agar semua data bebas masuk tanpa ditolak
    protected $guarded = [];

    // KECERDASAN OTOMATIS: Menerjemahkan Array <-> JSON secara cerdas
    protected $casts = [
        'jam_diliburkan' => 'array',
        'kelas_ids' => 'array',
    ];
}