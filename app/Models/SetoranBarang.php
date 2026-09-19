<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SetoranBarang extends Model
{
    protected $table = 'setoran_barang';
    protected $fillable = [
        'kode',
        'distribusi_barang_id',
        'pinjaman_id',
        'wali_kelas_id',
        'tanggal',
        'total',
        'keterangan',
        'created_by',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function distribusi()
    {
        return $this->belongsTo(DistribusiBarang::class, 'distribusi_barang_id');
    }

    public function pinjaman()
    {
        return $this->belongsTo(Pinjaman::class, 'pinjaman_id');
    }

    public function waliKelas()
    {
        return $this->belongsTo(Guru::class, 'wali_kelas_id');
    }

    public static function nextKode(int $tahun): string
    {
        $prefix = 'STD-' . $tahun . '-';
        $last = self::where('kode', 'like', $prefix . '%')->orderBy('id', 'desc')->value('kode');
        $no = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;
        return $prefix . str_pad((string) $no, 4, '0', STR_PAD_LEFT);
    }
}