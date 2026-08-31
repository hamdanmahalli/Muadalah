<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mutasi_jadwals', function (Blueprint $table) {
            $table->id();

            // Lingkup periode / tahun ajaran
            $table->unsignedBigInteger('periode_id')->nullable();
            $table->string('tahun_ajaran', 20)->nullable();

            // Lokasi jadwal yang berubah
            $table->unsignedBigInteger('kelas_id')->nullable();
            $table->unsignedBigInteger('pelajaran_id')->nullable();
            $table->string('hari', 10)->nullable();
            $table->unsignedInteger('jam_ke')->nullable();
            $table->unsignedBigInteger('jadwal_id')->nullable();

            // Pelaku (guru lama -> guru baru)
            $table->unsignedBigInteger('guru_lama_id')->nullable();
            $table->unsignedBigInteger('guru_baru_id')->nullable();

            // Jenis perubahan
            $table->string('tipe', 30)->default('ganti_guru'); // ganti_guru | tukar_jam | pindah_blok | hapus_slot | plot_sync

            // Tanggal
            $table->date('tanggal_kejadian')->nullable();
            $table->date('tanggal_efektif')->nullable();

            $table->string('keterangan')->nullable();

            // Siapa yang melakukannya
            $table->unsignedBigInteger('user_id')->nullable();

            // Relasi + index
            $table->foreign('kelas_id')->references('id')->on('kelas')->nullOnDelete();
            $table->foreign('pelajaran_id')->references('id')->on('pelajarans')->nullOnDelete();
            $table->foreign('guru_lama_id')->references('id')->on('gurus')->nullOnDelete();
            $table->foreign('guru_baru_id')->references('id')->on('gurus')->nullOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();

            $table->index('tipe');
            $table->index('tanggal_kejadian');
            $table->index('kelas_id');
            $table->index('guru_baru_id');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mutasi_jadwals');
    }
};
