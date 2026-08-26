<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKehadiranKegiatansTable extends Migration
{
    public function up()
    {
        Schema::create('kehadiran_kegiatans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agenda_kegiatan_id')->constrained('agenda_kegiatans')->onDelete('cascade');
            $table->foreignId('guru_id')->constrained('gurus')->onDelete('cascade');
            
            $table->dateTime('waktu_hadir'); // Mencatat presisi jam, menit, detik scan
            $table->string('metode')->default('Scan QR'); // Opsi: 'Scan QR' atau 'Manual Admin'
            $table->timestamps();

            // PROTEKSI ANTI-DOUBLE SCAN (Satu guru hanya bisa 1x hadir di 1 agenda yang sama)
            $table->unique(['agenda_kegiatan_id', 'guru_id'], 'unik_kehadiran_agenda');
        });
    }

    public function down()
    {
        Schema::dropIfExists('kehadiran_kegiatans');
    }
}