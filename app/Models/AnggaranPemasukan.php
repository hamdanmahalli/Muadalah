<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnggaranPemasukan extends Model
{
    protected $table = 'anggaran_pemasukan';
    protected $fillable = [
        'anggaran_id',
        'urutan',
        'uraian',
        'volume',
        'satuan',
        'harga_satuan',
        'jumlah',
    ];

    public function anggaran()
    {
        return $this->belongsTo(AnggaranKebendaharaan::class, 'anggaran_id');
    }

    public function realisasiMasuk()
    {
        return $this->hasMany(Pemasukan::class, 'anggaran_pemasukan_id');
    }
}