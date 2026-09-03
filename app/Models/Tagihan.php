<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tagihan extends Model
{
    protected $table = 'tagihans';
    protected $guarded = [];

    protected $casts = [
        'tanggal_jatuh_tempo' => 'date',
        'nominal' => 'float',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function jenisTagihan()
    {
        return $this->belongsTo(JenisTagihan::class);
    }

    public function periode()
    {
        return $this->belongsTo(Periode::class);
    }

    public function pembayarans()
    {
        return $this->hasMany(Pembayaran::class);
    }

    // Total yang sudah dibayar
    public function totalDibayar()
    {
        return $this->pembayarans()->sum('nominal_dibayar');
    }

    // Sisa yang harus dibayar
    public function sisa()
    {
        return $this->nominal - $this->totalDibayar();
    }

    public function getStatusAttribute($value)
    {
        $total = $this->pembayarans()->sum('nominal_dibayar');
        if ($total <= 0) return 'belum';
        if ($total >= $this->nominal) return 'lunas';
        return 'parsial';
    }
}
