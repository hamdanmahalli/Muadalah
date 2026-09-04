<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlotJadwal extends Model
{
    protected $table = 'plot_jadwals';
    protected $fillable = [
        'kelas_id',
        'pelajaran_id',
        'guru_id',
        'beban_jam',
    ];

    // Kabel penghubung ke Master Kelas
    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    // Kabel penghubung ke Master Pelajaran
    public function pelajaran()
    {
        return $this->belongsTo(Pelajaran::class);
    }

    // Kabel penghubung ke Master Guru
    public function guru()
    {
        return $this->belongsTo(Guru::class, 'guru_id'); // Memastikan menggunakan ID Guru
    }
}