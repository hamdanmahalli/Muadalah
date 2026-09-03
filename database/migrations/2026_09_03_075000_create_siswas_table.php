<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('siswas', function (Blueprint $table) {
            $table->id();
            $table->string('nis')->unique();              // Nomor Induk Siswa
            $table->string('nisn')->nullable()->unique(); // Nomor Induk Siswa Nasional
            $table->string('nama_siswa');
            $table->string('jenis_kelamin')->nullable();  // L / P
            $table->string('tempat_lahir')->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->string('agama')->nullable();
            $table->text('alamat')->nullable();
            $table->string('nama_ayah')->nullable();
            $table->string('nama_ibu')->nullable();
            $table->string('pekerjaan_ortu')->nullable();
            $table->string('no_hp_ortu')->nullable();
            $table->string('foto')->nullable();
            $table->string('tahun_masuk')->nullable();
            $table->string('status')->default('Aktif');   // Aktif / Alumni / Keluar
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('siswas');
    }
};
