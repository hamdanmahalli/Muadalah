<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('plot_jadwals', function (Blueprint $table) {
            $table->id();
            // Menghubungkan ke tabel Kelas
            $table->foreignId('kelas_id')->constrained('kelas')->onDelete('cascade');
            
            // Menghubungkan ke tabel Pelajaran
            $table->foreignId('pelajaran_id')->constrained('pelajarans')->onDelete('cascade');
            
            // Menghubungkan ke tabel Guru (Boleh kosong jika TU belum menentukan gurunya)
            $table->foreignId('guru_id')->nullable()->constrained('gurus')->onDelete('set null');
            
            // Menyimpan target jam mengajar seminggu (seperti 2, 4, 6 di Excel Bapak)
            $table->integer('beban_jam')->default(2);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plot_jadwals');
    }
};
