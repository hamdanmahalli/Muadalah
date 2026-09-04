<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Konfigurasi kolom yang ditampilkan di halaman Input Nilai.
     * Satu baris tunggal (id = 1) sebagai preferensi global.
     * Hanya Administrator/Pimpinan yang boleh mengubahnya.
     */
    public function up(): void
    {
        Schema::create('nilai_kolom_configs', function (Blueprint $table) {
            $table->id();
            $table->boolean('harian_uts')->default(true);
            $table->boolean('skor_uts')->default(true);
            $table->boolean('uts_akhir')->default(true);
            $table->boolean('harian_uas')->default(true);
            $table->boolean('skor_uas')->default(true);
            $table->boolean('uas_akhir')->default(true);
            $table->boolean('nilai_akhir')->default(true);
            $table->boolean('predikat')->default(true);
            $table->timestamps();
        });

        // Sisipkan baris konfigurasi default
        DB::table('nilai_kolom_configs')->insert([
            'id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('nilai_kolom_configs');
    }
};
