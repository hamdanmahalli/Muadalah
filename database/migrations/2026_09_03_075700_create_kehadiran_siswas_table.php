<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Absensi harian siswa untuk rekap mingguan
        Schema::create('kehadiran_siswas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswas')->onDelete('cascade');
            $table->foreignId('periode_id')->nullable()->constrained('periodes')->onDelete('cascade');
            $table->foreignId('kelas_id')->nullable()->constrained('kelas')->onDelete('cascade');
            $table->date('tanggal');
            $table->string('status')->default('hadir'); // hadir / sakit / izin / alpha
            $table->string('keterangan')->nullable();
            $table->unsignedBigInteger('user_id')->nullable(); // penginput
            $table->timestamps();

            $table->unique(['siswa_id', 'tanggal'], 'kehadiran_siswa_tanggal_unik');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kehadiran_siswas');
    }
};
