<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PenjualanBarang extends Model
{
    protected $table = 'penjualan_barang';
    protected $fillable = [
        'distribusi_barang_id',
        'siswa_id',
        'barang_id',
        'qty',
        'harga_jual',
        'tanggal',
        'status',
        'dicatat_oleh',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function distribusi()
    {
        return $this->belongsTo(DistribusiBarang::class, 'distribusi_barang_id');
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    public function barang()
    {
        return $this->belongsTo(Barang::class, 'barang_id');
    }
}