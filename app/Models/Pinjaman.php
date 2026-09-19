<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pinjaman extends Model
{
    protected $table = 'pinjaman';
    protected $fillable = [
        'kode',
        'periode_id',
        'pencairan_id',
        'peminjam_user_id',
        'jumlah',
        'tanggal',
        'keperluan',
        'status',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function periode()
    {
        return $this->belongsTo(Periode::class, 'periode_id');
    }

    public function pencairan()
    {
        return $this->belongsTo(Pencairan::class, 'pencairan_id');
    }

    public function peminjam()
    {
        return $this->belongsTo(User::class, 'peminjam_user_id');
    }

    /** Jumlah yang sudah dilunasi lewat setoran barang. */
    public function totalSetoran()
    {
        return (float) SetoranBarang::where('pinjaman_id', $this->id)->sum('total');
    }

    public function setoran()
    {
        return $this->hasMany(SetoranBarang::class, 'pinjaman_id');
    }

    public function sisa()
    {
        return max(0, (float) $this->jumlah - $this->totalSetoran());
    }

    public static function nextKode(int $tahun): string
    {
        $prefix = 'PJ-' . $tahun . '-';
        $last = self::where('kode', 'like', $prefix . '%')->orderBy('id', 'desc')->value('kode');
        $no = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;
        return $prefix . str_pad((string) $no, 4, '0', STR_PAD_LEFT);
    }
}