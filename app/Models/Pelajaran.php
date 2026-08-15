<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pelajaran extends Model
{
    // Menentukan nama tabel secara eksplisit agar pasti terbaca
    protected $table = 'pelajarans';

    // Membuka gembok agar data bisa dimanipulasi
    protected $guarded = [];
}