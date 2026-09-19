<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Barang extends Model
{
    protected $table = 'barang';
    protected $fillable = [
        'kode',
        'nama',
        'satuan',
        'harga_beli',
        'harga_jual',
        'is_active',
        'keterangan',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function stok()
    {
        $masuk = (float) $this->hasMany(PembelianBarangItem::class, 'barang_id')->sum('qty');
        $keluar = (float) $this->hasMany(DistribusiBarangItem::class, 'barang_id')->sum('qty');
        return max(0, $masuk - $keluar);
    }

    public static function nextKode(): string
    {
        $last = self::orderBy('id', 'desc')->value('kode');
        $no = $last ? ((int) substr($last, 4)) + 1 : 1;
        return 'BRG-' . str_pad((string) $no, 4, '0', STR_PAD_LEFT);
    }
}