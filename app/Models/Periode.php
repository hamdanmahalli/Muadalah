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
    
    // Membuka gembok agar data bisa disimpan dari form
    protected $guarded = [];
}