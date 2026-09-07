<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('honor_struktural_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('honor_konfigurasi_id')->constrained('honor_konfigurasis')->onDelete('cascade');
            $table->foreignId('jabatan_id')->constrained('jabatans')->onDelete('cascade');
            $table->integer('nominal')->default(0);
            $table->timestamps();

            $table->unique(['honor_konfigurasi_id', 'jabatan_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('honor_struktural_configs');
    }
};
