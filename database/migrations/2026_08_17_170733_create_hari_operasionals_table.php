<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('hari_operasional', function (Blueprint $table) {
            $table->id();
            $table->string('hari'); // Senin, Selasa, dst
            $table->boolean('is_active')->default(true); // Aktif atau Libur
            $table->integer('max_jam')->default(0); // Kapasitas jam per hari
            $table->string('keterangan')->nullable(); // Keterangan hari
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hari_operasionals');
    }
};
