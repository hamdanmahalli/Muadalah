<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('honor_periodes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('honor_konfigurasi_id')->constrained('honor_konfigurasis')->onDelete('cascade');
            $table->integer('bulan');
            $table->integer('tahun');
            $table->string('status')->default('draft');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('honor_periodes');
    }
};
