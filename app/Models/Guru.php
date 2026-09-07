<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Guru extends Model
{
    use HasFactory;

    // Membuka gembok agar data bisa masuk sekaligus
    protected $fillable = [
        'nig',
        'nip',
        'nama_guru',
        'tempat_lahir',
        'tanggal_lahir',
        'jenis_kelamin',
        'gender',
        'no_hp',
        'alamat',
        'pendidikan_terakhir',
        'status',
        // ===== Data pemerintah & honor =====
        'jarak_km',
        'nik',
        'nik_kk',
        'agama',
        'kewarganegaraan',
        'rt',
        'rw',
        'kelurahan',
        'kecamatan',
        'kabupaten_kota',
        'kode_pos',
        'nuptk',
        'nrg',
        'status_kepegawaian',
        'golongan_ruang',
        'program_studi',
        'perguruan_tinggi',
        'tahun_lulus',
        'status_sertifikasi',
        'no_sertifikat_pendidik',
        'tahun_sertifikasi',
        'tmt_kerja',
        'no_sk_pengangkatan',
        'tgl_sk_pengangkatan',
        'no_sk_pembagian_tugas',
        'npwp',
        'nama_bank',
        'no_rekening',
        'atas_nama_rekening',
        'bpjs_ketenagakerjaan',
        'bpjs_kesehatan',
        'status_menikah',
        'nama_pasangan',
        'jumlah_anak',
        'boleh_edit_profil',
    ];

    protected $casts = [
        'tanggal_lahir'          => 'date',
        'jarak_km'               => 'float',
        'tmt_kerja'              => 'date',
        'tgl_sk_pengangkatan'    => 'date',
        'status_sertifikasi'     => 'boolean',
        'jumlah_anak'            => 'integer',
        'boleh_edit_profil'      => 'boolean',
    ];

    // Kelas yang diampu sebagai wali kelas
    public function kelasWali()
    {
        return $this->hasMany(Kelas::class, 'wali_kelas_id');
    }

    // Jabatan pengurus (many-to-many via tabel guru_jabatan)
    public function jabatans()
    {
        return $this->belongsToMany(Jabatan::class, 'guru_jabatan')
            ->withPivot('is_utama')
            ->withTimestamps();
    }

    // Dokumen kelengkapan data (KTP, KK, ijazah, dst)
    public function dokumens()
    {
        return $this->hasMany(GuruDokumen::class);
    }

    // Apakah pengurus merupakan guru (memiliki jabatan "Guru")
    public function isGuru()
    {
        return $this->jabatans()->where('nama_jabatan', 'Guru')->exists();
    }

    // Gabungan nama jabatan untuk ditampilkan, mis. "Wk. Kurikulum, TU"
    public function namaJabatan()
    {
        return $this->jabatans->pluck('nama_jabatan')->implode(', ');
    }
}