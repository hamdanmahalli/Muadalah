<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Periode extends Model
{
    use HasFactory;

    // FITUR BARU: Menambahkan izin pengisian untuk tanggal_mulai dan tanggal_selesai
    protected $fillable = [
        'tahun_ajaran', 
        'semester', 
        'is_active', 
        'tanggal_mulai', 
        'tanggal_selesai'
    ];
    
    protected $table = 'periodes';

    // Daftar tahun ajaran unik. Karena penempatan kelas siswa cukup per tahun ajaran
    // (tanpa memisahkan Ganjil/Genap), tiap tahun ajaran direpresentasikan oleh satu
    // periode acuan (aktif -> Ganjil -> yang pertama).
    public static function tahunAjaranList()
    {
        return static::orderBy('tahun_ajaran', 'desc')
            ->get()
            ->groupBy('tahun_ajaran')
            ->map(function ($periodes, $tahun) {
                $acuan = $periodes->where('is_active', true)->first()
                    ?? $periodes->where('semester', 'Ganjil')->first()
                    ?? $periodes->first();
                return (object)[
                    'tahun_ajaran' => $tahun,
                    'periode_id'   => $acuan?->id,
                    'is_active'    => (bool)($acuan?->is_active),
                ];
            })
            ->values();
    }
}