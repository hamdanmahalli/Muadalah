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
        Schema::create('pelajarans', function (Blueprint $table) {
            $table->id();
            $table->string('kode_pelajaran')->unique(); // Kode seperti 14, 8, 6 (Tidak boleh kembar)
            $table->string('nama_pelajaran');
            $table->string('nama_kitab')->nullable();   // Boleh kosong jika tidak ada kitab spesifik
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pelajarans');
    }
};
