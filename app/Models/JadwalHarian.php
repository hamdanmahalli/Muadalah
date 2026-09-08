<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // Tambahkan baris ini

class JadwalHarian extends Model
{
    use SoftDeletes; // Aktifkan di sini

    protected $table = 'jadwal_harians';
    protected $fillable = [
        'periode_id',
        'tahun_ajaran',
        'kelas_id',
        'hari',
        'berlaku_mulai',
        'berlaku_sampai',
        'jam_ke',
        'tgl_efektif_mulai',
        'tgl_efektif_selesai',
        'pelajaran_id',
        'guru_id',
    ];

    public function kelas() { return $this->belongsTo(Kelas::class); }
    public function pelajaran() { return $this->belongsTo(Pelajaran::class); }
    public function guru() { return $this->belongsTo(Guru::class); }

    /**
     * Filter jadwal yang masih aktif pada tanggal tertentu.
     *
     * Logika mengikuti coalesce per kolom: mulai = berlaku_mulai ?? tgl_efektif_mulai,
     * selesai = berlaku_sampai ?? tgl_efektif_selesai. Nilai null berarti tanpa batas.
     */
    public function scopeAktifPada($query, string $tanggal)
    {
        return $query
            ->where(function ($q) use ($tanggal) {
                $q->whereRaw('COALESCE(berlaku_mulai, tgl_efektif_mulai) IS NULL')
                  ->orWhereRaw('COALESCE(berlaku_mulai, tgl_efektif_mulai) <= ?', [$tanggal]);
            })
            ->where(function ($q) use ($tanggal) {
                $q->whereRaw('COALESCE(berlaku_sampai, tgl_efektif_selesai) IS NULL')
                  ->orWhereRaw('COALESCE(berlaku_sampai, tgl_efektif_selesai) >= ?', [$tanggal]);
            });
    }
}