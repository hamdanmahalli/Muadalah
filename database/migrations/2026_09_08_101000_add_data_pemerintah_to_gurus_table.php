<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gurus', function (Blueprint $table) {
            // Data honor: jarak rumah ke sekolah (untuk transport)
            $table->decimal('jarak_km', 6, 2)->nullable()->after('pendidikan_terakhir');

            // ===================== Identitas =====================
            $table->string('nik', 16)->nullable()->after('nama_guru');
            $table->string('nik_kk', 16)->nullable();
            $table->string('agama')->nullable();
            $table->string('kewarganegaraan')->default('WNI');
            $table->string('rt')->nullable();
            $table->string('rw')->nullable();
            $table->string('kelurahan')->nullable();
            $table->string('kecamatan')->nullable();
            $table->string('kabupaten_kota')->nullable();
            $table->string('kode_pos', 5)->nullable();

            // ===================== Kepegawaian & Sertifikasi =====================
            $table->string('nuptk')->nullable();
            $table->string('nrg')->nullable();
            $table->string('status_kepegawaian')->nullable(); // GTY/Yayasan, Honorer, PPPK, PNS
            $table->string('golongan_ruang')->nullable();
            $table->string('program_studi')->nullable();
            $table->string('perguruan_tinggi')->nullable();
            $table->string('tahun_lulus', 4)->nullable();
            $table->boolean('status_sertifikasi')->default(false)->nullable();
            $table->string('no_sertifikat_pendidik')->nullable();
            $table->string('tahun_sertifikasi', 4)->nullable();
            $table->date('tmt_kerja')->nullable();
            $table->string('no_sk_pengangkatan')->nullable();
            $table->date('tgl_sk_pengangkatan')->nullable();
            $table->string('no_sk_pembagian_tugas')->nullable();

            // ===================== Pembayaran Honor / Tunjangan =====================
            $table->string('npwp')->nullable();
            $table->string('nama_bank')->nullable();
            $table->string('no_rekening')->nullable();
            $table->string('atas_nama_rekening')->nullable();
            $table->string('bpjs_ketenagakerjaan')->nullable();
            $table->string('bpjs_kesehatan')->nullable();

            // ===================== Data Keluarga =====================
            $table->string('status_menikah')->nullable();
            $table->string('nama_pasangan')->nullable();
            $table->unsignedInteger('jumlah_anak')->default(0)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('gurus', function (Blueprint $table) {
            $table->dropColumn([
                'jarak_km', 'nik', 'nik_kk', 'agama', 'kewarganegaraan',
                'rt', 'rw', 'kelurahan', 'kecamatan', 'kabupaten_kota', 'kode_pos',
                'nuptk', 'nrg', 'status_kepegawaian', 'golongan_ruang',
                'program_studi', 'perguruan_tinggi', 'tahun_lulus',
                'status_sertifikasi', 'no_sertifikat_pendidik', 'tahun_sertifikasi',
                'tmt_kerja', 'no_sk_pengangkatan', 'tgl_sk_pengangkatan', 'no_sk_pembagian_tugas',
                'npwp', 'nama_bank', 'no_rekening', 'atas_nama_rekening',
                'bpjs_ketenagakerjaan', 'bpjs_kesehatan',
                'status_menikah', 'nama_pasangan', 'jumlah_anak',
            ]);
        });
    }
};