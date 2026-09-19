<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PencairanItem extends Model
{
    public $timestamps = false;
    protected $table = 'pencairan_item';
    protected $fillable = [
        'pencairan_id',
        'anggaran_pos_id',
        'bulan_fiskal',
        'nominal',
    ];

    protected $casts = [
        'nominal' => 'float',
    ];

    public function pencairan()
    {
        return $this->belongsTo(Pencairan::class, 'pencairan_id');
    }

    public function pos()
    {
        return $this->belongsTo(AnggaranPos::class, 'anggaran_pos_id');
    }
}