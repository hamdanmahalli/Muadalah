<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgendaKaldik extends Model
{
    use HasFactory;

    protected $table = 'agenda_kaldiks';

    protected $fillable = [
        'periode_id',
        'nama_agenda',
        'jenis_agenda',
        'tanggal_mulai',
        'tanggal_selesai',
        'target_libur',
        'kelas_ids',
        'tipe_agenda',      // <-- Tambahan Baru
        'jam_diliburkan',   // <-- Tambahan Baru
        'keterangan'
    ];

    // Otomatis mengubah JSON dari database menjadi Array di PHP, dan sebaliknya
    protected $casts = [
        'kelas_ids' => 'array',
        'jam_diliburkan' => 'array', // <-- Otomatis konversi JSON ke Array
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
    ];

    // Relasi ke tabel Periode
    public function periode()
    {
        return $this->belongsTo(Periode::class, 'periode_id');
    }
}