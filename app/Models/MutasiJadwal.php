<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MutasiJadwal extends Model
{
    protected $table = 'mutasi_jadwals';
    protected $guarded = [];

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
