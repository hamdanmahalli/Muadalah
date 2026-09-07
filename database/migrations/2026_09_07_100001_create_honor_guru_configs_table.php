<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('honor_guru_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('honor_konfigurasi_id')->constrained('honor_konfigurasis')->onDelete('cascade');
            $table->foreignId('guru_id')->constrained('gurus')->onDelete('cascade');
            $table->string('status_honor')->default('Tetap');
            $table->boolean('dari_luar')->default(false);
            $table->integer('tarif_override')->nullable();
            $table->timestamps();

            $table->unique(['honor_konfigurasi_id', 'guru_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('honor_guru_configs');
    }
};
