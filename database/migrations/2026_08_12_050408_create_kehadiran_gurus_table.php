<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kehadiran_gurus', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('jadwal_id'); // Menghubungkan ke tabel jadwal
            $table->date('tanggal');                  // Tanggal absensi
            
            // Status yang diperbarui: Menunggu, Hadir, Izin, Alpha
            $table->string('status')->default('Menunggu'); 

            // FITUR BARU: Kolom untuk Guru Pengganti dan Keterangan Izin
            $table->string('nig_pengganti')->nullable(); // Boleh kosong jika tidak ada yang inval
            $table->text('keterangan')->nullable();      // Boleh kosong, diisi jika izin (Sakit/Bepergian dll)

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kehadiran_gurus');
    }
};