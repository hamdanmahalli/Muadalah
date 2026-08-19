<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Menambahkan laci jam_mulai dan jam_selesai ke tabel yang sudah ada
        Schema::table('hari_operasional', function (Blueprint $table) {
            $table->time('jam_mulai')->nullable();
            $table->time('jam_selesai')->nullable();
        });
    }

    public function down(): void
    {
        // Fitur mundur (hapus laci jika dibatalkan)
        Schema::table('hari_operasional', function (Blueprint $table) {
            $table->dropColumn(['jam_mulai', 'jam_selesai']);
        });
    }
};