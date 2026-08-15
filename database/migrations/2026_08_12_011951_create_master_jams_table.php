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
    Schema::create('master_jams', function (Blueprint $table) {
        $table->id();
        
        // Kolom urutan jam pelajaran (misal: 1, 2, 3, dst.)
        $table->integer('jam_ke')->unique(); 
        
        // Kolom waktu mulai jam pelajaran (misal: 07:00:00)
        $table->time('jam_mulai'); 
        
        // Kolom waktu selesai jam pelajaran (misal: 07:40:00)
        $table->time('jam_selesai'); 
        
        $table->timestamps();
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_jams');
    }
};
