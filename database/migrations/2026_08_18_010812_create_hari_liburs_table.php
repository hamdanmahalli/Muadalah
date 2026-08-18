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
        Schema::create('hari_liburs', function (Blueprint $table) {
            $table->id();
            $table->string('nama_libur');
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->string('tipe_libur')->default('Penuh'); // 'Penuh' atau 'Parsial'
            $table->json('jam_diliburkan')->nullable(); // Untuk libur parsial (jam tertentu)
            $table->string('target_libur')->default('semua'); // 'semua' atau 'kelas_tertentu'
            $table->json('kelas_ids')->nullable(); // Menyimpan array ID kelas yang diliburkan
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hari_liburs');
    }
};
