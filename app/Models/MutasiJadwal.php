<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MutasiJadwal extends Model
{
    protected $table = 'mutasi_jadwals';
    protected $fillable = [
        'periode_id',
        'tahun_ajaran',
        'kelas_id',
        'pelajaran_id',
        'hari',
        'jam_ke',
        'jadwal_id',
        'guru_lama_id',
        'guru_baru_id',
        'tipe',
        'tanggal_kejadian',
        'tanggal_efektif',
        'keterangan',
        'user_id',
    ];

    protected $casts = [
        'tanggal_kejadian' => 'date',
        'tanggal_efektif'  => 'date',
    ];

    public function kelas() { return $this->belongsTo(Kelas::class); }
    public function pelajaran() { return $this->belongsTo(Pelajaran::class); }
    public function guruLama() { return $this->belongsTo(Guru::class, 'guru_lama_id'); }
    public function guruBaru() { return $this->belongsTo(Guru::class, 'guru_baru_id'); }
    public function user() { return $this->belongsTo(User::class); }

    public static function labelTipe(string $tipe): string
    {
        return [
            'ganti_guru' => 'Ganti Guru',
            'tukar_jam'  => 'Tukar Jam',
            'pindah_blok'=> 'Pindah Blok',
            'hapus_slot' => 'Hapus Slot',
            'plot_sync'  => 'Perubahan Plot',
        ][$tipe] ?? ucfirst(str_replace('_', ' ', $tipe));
    }
}
