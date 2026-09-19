<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnggaranKebendaharaan extends Model
{
    protected $table = 'anggaran_kebendaharaan';
    protected $fillable = [
        'periode_id',
        'nama',
        'tahun_ajaran',
        'status',
        'created_by',
    ];

    public function periode()
    {
        return $this->belongsTo(Periode::class, 'periode_id');
    }

    public function kelompok()
    {
        return $this->hasMany(AnggaranKelompok::class, 'anggaran_id')->orderBy('urutan');
    }

    public function pos()
    {
        return $this->hasMany(AnggaranPos::class, 'anggaran_id');
    }

    public function pemasukanRencana()
    {
        return $this->hasMany(AnggaranPemasukan::class, 'anggaran_id')->orderBy('urutan');
    }

    public function totalPagu()
    {
        return (float) $this->pos()->sum('jumlah');
    }

    public function totalPemasukanRencana()
    {
        return (float) $this->pemasukanRencana()->sum('jumlah');
    }
}