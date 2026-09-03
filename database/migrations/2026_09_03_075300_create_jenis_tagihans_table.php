<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Jenis tagihan yang dibuat/dihapus admin (SPP, Uang Kegiatan, dll.)
        Schema::create('jenis_tagihans', function (Blueprint $table) {
            $table->id();
            $table->string('nama_tagihan');
            $table->text('deskripsi')->nullable();
            $table->string('status')->default('Aktif'); // Aktif / Nonaktif
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jenis_tagihans');
    }
};
