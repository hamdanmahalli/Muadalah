<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PembelianBarang extends Model
{
    protected $table = 'pembelian_barang';
    protected $fillable = [
        'kode',
        'pencairan_id',
        'tanggal',
        'total',
        'keterangan',
        'created_by',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function items()
    {
        return $this->hasMany(PembelianBarangItem::class, 'pembelian_barang_id');
    }

    public function pencairan()
    {
        return $this->belongsTo(Pencairan::class, 'pencairan_id');
    }

    public static function nextKode(int $tahun): string
    {
        $prefix = 'PB-' . $tahun . '-';
        $last = self::where('kode', 'like', $prefix . '%')->orderBy('id', 'desc')->value('kode');
        $no = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;
        return $prefix . str_pad((string) $no, 4, '0', STR_PAD_LEFT);
    }
}