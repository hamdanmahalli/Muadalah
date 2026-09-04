<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pembayaran extends Model
{
    protected $table = 'pembayarans';
    protected $fillable = [
        'tagihan_id',
        'nominal_dibayar',
        'tanggal_bayar',
        'metode',
        'keterangan',
        'user_id',
    ];

    protected $casts = [
        'tanggal_bayar' => 'date',
        'nominal_dibayar' => 'float',
    ];

    public function tagihan()
    {
        return $this->belongsTo(Tagihan::class);
    }
}
