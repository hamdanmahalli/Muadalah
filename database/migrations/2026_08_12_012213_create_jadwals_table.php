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
    Schema::create('jadwals', function (Blueprint $table) {
        $table->id();
        
        // Data Dasar Jadwal
        $table->string('kelas');           // Contoh: 7-A
        $table->string('hari');            // Contoh: Senin
        $table->string('mata_pelajaran');  // Contoh: Nahwu
        
        // TALI PENGHUBUNG (Relasi)
        $table->integer('jam_ke');         // Menyambung ke urutan jam di Master Jam
        $table->string('nig_guru');        // Menyambung ke NIG di tabel Master Guru
        
        $table->timestamps();

        // -------------------------------------------------------
        // PENGAMANAN DATABASE (BEST PRACTICE)
        // -------------------------------------------------------
        // Ini adalah "Gembok Pengaman". Jika staf TU tidak sengaja 
        // memasukkan jadwal untuk Guru/Jam yang belum ada di master data, 
        // sistem (PostgreSQL) akan otomatis memblokirnya agar tidak terjadi error.
        
        $table->foreign('jam_ke')->references('jam_ke')->on('master_jams')->onDelete('cascade');
        $table->foreign('nig_guru')->references('nig')->on('gurus')->onDelete('cascade');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jadwals');
    }
};
