<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class HonorDetail extends Model
{
    protected $table = 'honor_details';
    protected $fillable = [
        'honor_periode_id',
        'guru_id',
        'jam_wajib',
        'alpa',
        'izin',
        'sakit',
        'piket_jam',
        'realita_jam',
        'persentase',
        'keterangan',
        'honor_pokok',
        'tunjangan_struktural',
        'tunjangan_wali_kelas',
        'transport',
        'honor_piket',
        'total',
        'is_diterima',
        'waktu_diterima',
        'metode_penerimaan',
        'qr_token',
    ];

    protected $casts = [
        'is_diterima' => 'boolean',
        'waktu_diterima' => 'datetime',
        'persentase' => 'decimal:2',
    ];

    public function periode()
    {
        return $this->belongsTo(HonorPeriode::class, 'honor_periode_id');
    }

    public function guru()
    {
        return $this->belongsTo(Guru::class);
    }

    // Nominal 0 => tidak ada uang yang diterima, tidak perlu penerimaan
    public function getButuhPenerimaanAttribute(): bool
    {
        return $this->total > 0;
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($detail) {
            if (empty($detail->qr_token)) {
                $detail->qr_token = 'HONOR-' . strtoupper(Str::random(12));
            }
        });
    }
}
