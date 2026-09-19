<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LaporanPengeluaranItem extends Model
{
    public $timestamps = false;
    protected $table = 'laporan_pengeluaran_item';
    protected $fillable = [
        'laporan_pengeluaran_id',
        'anggaran_pos_id',
        'pencairan_item_id',
        'uraian',
        'nominal',
    ];

    protected $casts = [
        'nominal' => 'float',
    ];

    public function laporan()
    {
        return $this->belongsTo(LaporanPengeluaran::class, 'laporan_pengeluaran_id');
    }

    public function pos()
    {
        return $this->belongsTo(AnggaranPos::class, 'anggaran_pos_id');
    }

    public function pencairanItem()
    {
        return $this->belongsTo(PencairanItem::class, 'pencairan_item_id');
    }
}