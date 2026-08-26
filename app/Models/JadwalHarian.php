<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // Tambahkan baris ini

class JadwalHarian extends Model
{
    use SoftDeletes; // Aktifkan di sini

    protected $table = 'jadwal_harians';
    protected $guarded = [];

    public function kelas() { return $this->belongsTo(Kelas::class); }
    public function pelajaran() { return $this->belongsTo(Pelajaran::class); }
    public function guru() { return $this->belongsTo(Guru::class); }
}