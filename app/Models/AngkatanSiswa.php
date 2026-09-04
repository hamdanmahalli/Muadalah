<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AngkatanSiswa extends Model
{
    protected $table = 'angkatan_siswas';
    protected $fillable = [
        'siswa_id',
        'periode_id',
        'kelas_id',
        'nomor_absen',
        'status',
        'tanggal_masuk',
        'tanggal_keluar',
    ];

    protected $casts = [
        'tanggal_masuk' => 'date',
        'tanggal_keluar' => 'date',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function periode()
    {
        return $this->belongsTo(Periode::class);
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }
}
