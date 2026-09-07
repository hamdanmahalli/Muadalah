<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guru_dokumens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guru_id')->constrained('gurus')->onDelete('cascade');
            $table->string('jenis'); // KTP, KK, Ijazah, Sertifikat Pendidik, SK Pengangkatan, SK Pembagian Tugas, NPWP, Akta Kelahiran, Pas Foto, Lainnya
            $table->string('nama_asli');
            $table->string('file_path');
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guru_dokumens');
    }
};