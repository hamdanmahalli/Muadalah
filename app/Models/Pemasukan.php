<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pemasukan extends Model
{
    protected $table = 'pemasukan';
    protected $fillable = [
        'periode_id',
        'anggaran_pemasukan_id',
        'setoran_barang_id',
        'uraian',
        'tanggal',
        'jumlah',
        'keterangan',
        'created_by',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function periode()
    {
        return $this->belongsTo(Periode::class, 'periode_id');
    }

    public function rencana()
    {
        return $this->belongsTo(AnggaranPemasukan::class, 'anggaran_pemasukan_id');
    }

    public function anggaranPemasukan()
    {
        return $this->belongsTo(AnggaranPemasukan::class, 'anggaran_pemasukan_id');
    }
}