<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LaporanPengeluaran extends Model
{
    protected $table = 'laporan_pengeluaran';
    protected $fillable = [
        'kode',
        'periode_id',
        'pos_id',
        'pencairan_id',
        'tanggal',
        'nominal',
        'keterangan',
        'status',
        'dibuat_oleh',
        'divalidasi_oleh',
        'divalidasi_at',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'divalidasi_at' => 'datetime',
    ];

    public function periode()
    {
        return $this->belongsTo(Periode::class, 'periode_id');
    }

    public function pos()
    {
        return $this->belongsTo(AnggaranPos::class, 'pos_id');
    }

    public function items()
    {
        return $this->hasMany(LaporanPengeluaranItem::class, 'laporan_pengeluaran_id');
    }

    public function getJumlahAttribute()
    {
        if ($this->items && $this->items->count() > 0) {
            return (float) $this->items->sum('nominal');
        }
        return (float) $this->nominal;
    }

    public function pencairan()
    {
        return $this->belongsTo(Pencairan::class, 'pencairan_id');
    }

    public static function nextKode(int $tahun): string
    {
        $prefix = 'LPJ-' . $tahun . '-';
        $last = self::where('kode', 'like', $prefix . '%')->orderBy('id', 'desc')->value('kode');
        $no = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;
        return $prefix . str_pad((string) $no, 4, '0', STR_PAD_LEFT);
    }
}