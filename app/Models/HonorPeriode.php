<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HonorPeriode extends Model
{
    protected $table = 'honor_periodes';
    protected $fillable = [
        'honor_konfigurasi_id',
        'bulan',
        'tahun',
        'status',
        'created_by',
    ];

    public function konfigurasi()
    {
        return $this->belongsTo(HonorKonfigurasi::class, 'honor_konfigurasi_id');
    }

    public function details()
    {
        return $this->hasMany(HonorDetail::class, 'honor_periode_id');
    }
}
