<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HonorStrukturalConfig extends Model
{
    protected $table = 'honor_struktural_configs';
    protected $fillable = [
        'honor_konfigurasi_id',
        'jabatan_id',
        'nominal',
    ];

    public function konfigurasi()
    {
        return $this->belongsTo(HonorKonfigurasi::class, 'honor_konfigurasi_id');
    }

    public function jabatan()
    {
        return $this->belongsTo(Jabatan::class);
    }
}
