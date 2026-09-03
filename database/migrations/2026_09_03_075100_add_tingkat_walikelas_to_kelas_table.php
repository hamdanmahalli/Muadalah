<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Selain kolom nama_kelas yang sudah ada, tambahkan tingkat dan wali kelas
        Schema::table('kelas', function (Blueprint $table) {
            $table->string('tingkat')->nullable()->after('nama_kelas');       // Misal: VII, VIII, IX
            $table->unsignedBigInteger('wali_kelas_id')->nullable()->after('tingkat'); // referensi ke gurus.id
        });
    }

    public function down(): void
    {
        Schema::table('kelas', function (Blueprint $table) {
            $table->dropColumn(['tingkat', 'wali_kelas_id']);
        });
    }
};
