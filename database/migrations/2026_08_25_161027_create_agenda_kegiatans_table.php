<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAgendaKegiatansTable extends Migration
{
    public function up()
    {
        Schema::create('agenda_kegiatans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('periode_id')->constrained('periodes')->onDelete('cascade');
            $table->string('nama_kegiatan');
            $table->date('tanggal');
            $table->time('jam_mulai');
            $table->time('jam_selesai')->nullable();
            $table->string('lokasi')->nullable();
            
            // Kunci Cerdas QR Code (Misal: AGENDA-8f7d6a...)
            $table->string('qr_token')->unique(); 
            
            // Status acara (Apakah scan masih dibuka atau sudah ditutup TU?)
            $table->boolean('is_open')->default(true); 
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('agenda_kegiatans');
    }
}