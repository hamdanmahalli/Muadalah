<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PembelianBarangItem extends Model
{
    protected $table = 'pembelian_barang_item';
    protected $fillable = [
        'pembelian_barang_id',
        'barang_id',
        'qty',
        'harga_beli',
        'harga_jual',
    ];

    public function pembelian()
    {
        return $this->belongsTo(PembelianBarang::class, 'pembelian_barang_id');
    }

    public function barang()
    {
        return $this->belongsTo(Barang::class, 'barang_id');
    }
}