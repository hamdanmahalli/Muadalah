<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jadwal extends Model
{
    use HasFactory;

    // Menentukan nama tabel secara eksplisit agar terbaca tepat di PostgreSQL
    protected $table = 'jadwals';

    // Membuka gembok agar data dari Excel bisa masuk sekaligus secara massal
    protected $fillable = [
        'kelas',
        'hari',
        'mata_pelajaran',
        'jam_ke',
        'nig_guru',
    ];

    // FITUR BARU: Tali penghubung untuk memanggil Nama Guru dari tabel 'gurus'
    public function masterGuru()
    {
        return $this->belongsTo(Guru::class, 'nig_guru', 'nig');
    }
}