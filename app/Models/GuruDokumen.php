<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GuruDokumen extends Model
{
    use HasFactory;

    protected $fillable = [
        'guru_id',
        'jenis',
        'nama_asli',
        'file_path',
        'keterangan',
    ];

    protected $casts = [
        'guru_id' => 'integer',
    ];

    public function guru()
    {
        return $this->belongsTo(Guru::class);
    }

    public function url()
    {
        return asset('uploads/' . $this->file_path);
    }
}