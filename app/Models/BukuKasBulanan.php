<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BukuKasBulanan extends Model
{
    protected $table = 'buku_kas_bulanan';

    protected $fillable = [
        'periode_id',
        'pencairan_id',
        'bulan_fiskal',
        'tahun_fiskal',
        'total_masuk',
        'total_keluar',
        'pemasukan_manual',
        'sisa',
        'dilaporkan_oleh',
        'dilaporkan_at',
        'diterima_bendahara_oleh',
        'diterima_bendahara_at',
        'dikembalikan_oleh',
        'dikembalikan_at',
        'alasan_dikembalikan',
        'disahkan_oleh',
        'disahkan_at',
        'catatan',
    ];

    protected $casts = [
        'bulan_fiskal'             => 'integer',
        'tahun_fiskal'             => 'integer',
        'total_masuk'              => 'float',
        'total_keluar'             => 'float',
        'pemasukan_manual'         => 'float',
        'sisa'                     => 'float',
        'dilaporkan_at'            => 'datetime',
        'diterima_bendahara_at'    => 'datetime',
        'dikembalikan_at'          => 'datetime',
        'disahkan_at'              => 'datetime',
    ];

    public function periode()
    {
        return $this->belongsTo(Periode::class, 'periode_id');
    }

    public function pencairan()
    {
        return $this->belongsTo(Pencairan::class, 'pencairan_id');
    }

    public function pelapor()
    {
        return $this->belongsTo(User::class, 'dilaporkan_oleh');
    }

    public function penerima()
    {
        return $this->belongsTo(User::class, 'diterima_bendahara_oleh');
    }

    public function pengembali()
    {
        return $this->belongsTo(User::class, 'dikembalikan_oleh');
    }

    public function pengesah()
    {
        return $this->belongsTo(User::class, 'disahkan_oleh');
    }

    public function getDisahkanAttribute(): bool
    {
        return $this->disahkan_at !== null;
    }

    /**
     * Status alur pelaporan: dilaporkan -> diterima -> disahkan,
     * atau dikembalikan bila butuh revisi (bisa berulang).
     */
    public function getStatusAttribute(): string
    {
        if ($this->disahkan_at !== null) {
            return 'disahkan';
        }
        if ($this->dikembalikan_at !== null) {
            return 'dikembalikan';
        }
        if ($this->diterima_bendahara_at !== null) {
            return 'diterima';
        }

        return 'dilaporkan';
    }

    public function getLabelAttribute(): string
    {
        return bulan_fiskal_label($this->bulan_fiskal) . ' ' . $this->tahun_fiskal;
    }
}
