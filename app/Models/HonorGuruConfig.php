<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HonorGuruConfig extends Model
{
    protected $table = 'honor_guru_configs';
    protected $fillable = [
        'honor_konfigurasi_id',
        'guru_id',
        'status_honor',
        'dari_luar',
        'tarif_override',
    ];

    protected $casts = [
        'dari_luar' => 'boolean',
    ];

    public function konfigurasi()
    {
        return $this->belongsTo(HonorKonfigurasi::class, 'honor_konfigurasi_id');
    }

    public function guru()
    {
        return $this->belongsTo(Guru::class);
    }
}
