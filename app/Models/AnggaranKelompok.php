<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnggaranKelompok extends Model
{
    protected $table = 'anggaran_kelompok';
    protected $fillable = [
        'anggaran_id',
        'kode',
        'nama',
        'urutan',
    ];

    public function anggaran()
    {
        return $this->belongsTo(AnggaranKebendaharaan::class, 'anggaran_id');
    }

    public function pos()
    {
        return $this->hasMany(AnggaranPos::class, 'kelompok_id')->orderBy('kode');
    }

    public function totalPagu()
    {
        return (float) $this->pos()->sum('jumlah');
    }
}