<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Nilai extends Model
{
    protected $table = 'nilais';
    protected $fillable = [
        'siswa_id',
        'periode_id',
        'pelajaran_id',
        'kelas_id',
        'guru_id',
        'nilai_harian_uts',
        'nilai_harian_uas',
        'skor_uts',
        'nilai_uts_akhir',
        'skor_uas',
        'nilai_uas_akhir',
        'nilai_uts',
        'nilai_uas',
        'nilai_akhir',
        'predikat',
        'catatan',
    ];

    protected $casts = [
        'nilai_harian_uts' => 'float',
        'nilai_harian_uas' => 'float',
        'skor_uts' => 'float',
        'nilai_uts_akhir' => 'float',
        'skor_uas' => 'float',
        'nilai_uas_akhir' => 'float',
        'nilai_uts' => 'float',
        'nilai_uas' => 'float',
        'nilai_akhir' => 'float',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function periode()
    {
        return $this->belongsTo(Periode::class);
    }

    public function pelajaran()
    {
        return $this->belongsTo(Pelajaran::class);
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }
}
