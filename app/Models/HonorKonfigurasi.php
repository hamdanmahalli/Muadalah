<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HonorKonfigurasi extends Model
{
    protected $table = 'honor_konfigurasis';
    protected $fillable = [
        'periode_id',
        'bulan',
        'tahun',
        'tarif_jam_normal',
        'tarif_jam_magang',
        'tarif_piket',
        'tarif_transport',
        'tarif_wali_kelas',
        'catatan',
    ];

    public function periode()
    {
        return $this->belongsTo(Periode::class);
    }

    public function guruConfigs()
    {
        return $this->hasMany(HonorGuruConfig::class, 'honor_konfigurasi_id');
    }

    public function strukturalConfigs()
    {
        return $this->hasMany(HonorStrukturalConfig::class, 'honor_konfigurasi_id');
    }

    public function periodes()
    {
        return $this->hasMany(HonorPeriode::class, 'honor_konfigurasi_id');
    }
}
