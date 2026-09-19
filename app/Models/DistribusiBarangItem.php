<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DistribusiBarangItem extends Model
{
    protected $table = 'distribusi_barang_item';
    protected $fillable = [
        'distribusi_barang_id',
        'barang_id',
        'qty',
        'harga_jual',
    ];

    public function distribusi()
    {
        return $this->belongsTo(DistribusiBarang::class, 'distribusi_barang_id');
    }

    public function barang()
    {
        return $this->belongsTo(Barang::class, 'barang_id');
    }
}