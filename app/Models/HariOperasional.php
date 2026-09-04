<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HariOperasional extends Model
{
    use HasFactory;

    protected $table = 'hari_operasional';
    protected $fillable = [
        'hari',
        'is_active',
        'max_jam',
        'jam_mulai',
        'keterangan',
    ];
}