<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBatasPelajaransTable extends Migration
{
    public function up()
    {
        Schema::create('batas_pelajarans', function (Blueprint $table) {
            $table->id();
            // Relasi Wajib ke Master Periode
            $table->foreignId('periode_id')->constrained('periodes')->onDelete('cascade');
            
            // Relasi ke Master Pelajaran
            $table->foreignId('pelajaran_id')->constrained('pelajarans')->onDelete('cascade');
            
            $table->string('tingkat'); // Misal: 7, 8, 9, atau 1 Ulya
            
            $table->string('mulai_dari')->nullable();
            $table->string('batas_uts_ganjil')->nullable();
            $table->string('batas_uas_ganjil')->nullable();
            $table->string('batas_uts_genap')->nullable();
            $table->string('batas_uas_genap')->nullable();
            $table->timestamps();

            // Proteksi Anti-Duplikat
            $table->unique(['periode_id', 'pelajaran_id', 'tingkat'], 'unik_batas_kurikulum');
        });
    }

    public function down()
    {
        Schema::dropIfExists('batas_pelajarans');
    }
}