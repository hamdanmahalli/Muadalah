<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NilaiKolomConfig extends Model
{
    protected $table = 'nilai_kolom_configs';

    protected $fillable = [
        'harian_uts',
        'skor_uts',
        'uts_akhir',
        'harian_uas',
        'skor_uas',
        'uas_akhir',
        'nilai_akhir',
        'predikat',
    ];

    protected $casts = [
        'harian_uts' => 'boolean',
        'skor_uts' => 'boolean',
        'uts_akhir' => 'boolean',
        'harian_uas' => 'boolean',
        'skor_uas' => 'boolean',
        'uas_akhir' => 'boolean',
        'nilai_akhir' => 'boolean',
        'predikat' => 'boolean',
    ];

    /**
     * Ambil konfigurasi default (baris tunggal id=1).
     */
    public static function config(): self
    {
        return self::firstOrCreate(['id' => 1]);
    }
}
