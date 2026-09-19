<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pencairan extends Model
{
    protected $table = 'pencairan';
    protected $fillable = [
        'kode',
        'periode_id',
        'pos_id',
        'bulan_fiskal',
        'jenis',
        'tanggal_aju',
        'nominal',
        'keperluan',
        'status',
        'diajukan_oleh',
        'disetujui_oleh',
        'disetujui_at',
        'dibayar_oleh',
        'dibayar_at',
        'tolak_alasan',
    ];

    protected $casts = [
        'tanggal_aju' => 'date',
        'bulan_fiskal' => 'integer',
        'disetujui_at' => 'datetime',
        'dibayar_at' => 'datetime',
    ];

    public function getJumlahAttribute()
    {
        if ($this->items && $this->items->count() > 0) {
            return (float) $this->items->sum('nominal');
        }
        return (float) $this->nominal;
    }

    public function getBulanFiskalLabelAttribute(): string
    {
        return $this->bulan_fiskal ? bulan_fiskal_label($this->bulan_fiskal) : '—';
    }

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
        return $this->hasMany(PencairanItem::class, 'pencairan_id');
    }

    public function pengaju()
    {
        return $this->belongsTo(User::class, 'diajukan_oleh');
    }

    public function pinjaman()
    {
        return $this->hasOne(Pinjaman::class, 'pencairan_id');
    }

    public function isLunasPanjar()
    {
        return !$this->pinjaman || $this->pinjaman->status === 'lunas';
    }

    public static function nextKode(int $tahun): string
    {
        $prefix = 'SPP-' . $tahun . '-';
        $last = self::where('kode', 'like', $prefix . '%')->orderBy('id', 'desc')->value('kode');
        $no = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;
        return $prefix . str_pad((string) $no, 4, '0', STR_PAD_LEFT);
    }
}