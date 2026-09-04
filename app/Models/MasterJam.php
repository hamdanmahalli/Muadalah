<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterJam extends Model
{
    use HasFactory;

    // Menentukan nama tabel secara eksplisit
    protected $table = 'master_jams';

    // Membuka gembok agar data bisa masuk sekaligus
    protected $fillable = [
        'jam_ke',
        'jam_mulai',
        'jam_selesai',
    ];
}