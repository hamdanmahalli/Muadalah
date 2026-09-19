<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnggaranPos extends Model
{
    protected $table = 'anggaran_pos';
    protected $fillable = [
        'anggaran_id',
        'kelompok_id',
        'kode',
        'uraian',
        'volume',
        'satuan',
        'volume_2',
        'satuan_2',
        'harga_satuan',
        'jumlah',
    ];

    public function anggaran()
    {
        return $this->belongsTo(AnggaranKebendaharaan::class, 'anggaran_id');
    }

    public function kelompok()
    {
        return $this->belongsTo(AnggaranKelompok::class, 'kelompok_id');
    }

    public function pencairan()
    {
        return $this->hasMany(Pencairan::class, 'pos_id');
    }

    public function posBulan()
    {
        return $this->hasMany(AnggaranPosBulan::class, 'anggaran_pos_id');
    }

    public function realisasi()
    {
        return $this->hasMany(LaporanPengeluaranItem::class, 'anggaran_pos_id')
            ->whereHas('laporan', fn ($q) => $q->where('status', 'disetujui'));
    }

    public function totalRealisasi()
    {
        return (float) $this->realisasi()->sum('nominal');
    }

    public function sisa()
    {
        return (float) $this->jumlah - $this->totalRealisasi();
    }
}