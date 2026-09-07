<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('honor_konfigurasis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('periode_id')->constrained('periodes')->onDelete('cascade');
            $table->integer('bulan');
            $table->integer('tahun');
            $table->integer('tarif_jam_normal')->default(5000);
            $table->integer('tarif_jam_magang')->default(4000);
            $table->integer('tarif_piket')->default(4000);
            $table->integer('tarif_transport')->default(20000);
            $table->integer('tarif_wali_kelas')->default(50000);
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->unique(['periode_id', 'bulan', 'tahun']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('honor_konfigurasis');
    }
};
