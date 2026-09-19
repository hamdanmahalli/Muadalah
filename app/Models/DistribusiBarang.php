<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DistribusiBarang extends Model
{
    protected $table = 'distribusi_barang';
    protected $fillable = [
        'kode',
        'wali_kelas_id',
        'kelas_id',
        'tanggal',
        'keterangan',
        'created_by',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function items()
    {
        return $this->hasMany(DistribusiBarangItem::class, 'distribusi_barang_id');
    }

    public function waliKelas()
    {
        return $this->belongsTo(Guru::class, 'wali_kelas_id');
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    public function setoran()
    {
        return $this->hasMany(SetoranBarang::class, 'distribusi_barang_id');
    }

    /** Nilai barang yang dibawa wali kelas = qty x harga jual. */
    public function nilaiPengambilan()
    {
        return (float) $this->items()->sum(\Illuminate\Support\Facades\DB::raw('qty * harga_jual'));
    }

    /** Total uang yang sudah disetor wali kelas untuk distribusi ini. */
    public function totalDisetor()
    {
        return (float) $this->setoran()->sum('total');
    }

    public function sisaBelumDisetor()
    {
        return max(0, $this->nilaiPengambilan() - $this->totalDisetor());
    }

    public static function nextKode(int $tahun): string
    {
        $prefix = 'DSB-' . $tahun . '-';
        $last = self::where('kode', 'like', $prefix . '%')->orderBy('id', 'desc')->value('kode');
        $no = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;
        return $prefix . str_pad((string) $no, 4, '0', STR_PAD_LEFT);
    }
}