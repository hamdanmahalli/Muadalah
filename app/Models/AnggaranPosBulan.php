<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnggaranPosBulan extends Model
{
    public $timestamps = false;
    protected $table = 'anggaran_pos_bulan';
    protected $fillable = [
        'anggaran_pos_id',
        'bulan_fiskal',
        'nominal',
    ];

    public function pos()
    {
        return $this->belongsTo(AnggaranPos::class, 'anggaran_pos_id');
    }
}