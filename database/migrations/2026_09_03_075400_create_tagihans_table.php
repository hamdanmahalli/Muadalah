<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Satu baris tagihan untuk satu siswa.
        // Dapat dibuat untuk: semua siswa, kelas tertentu, atau murid tertentu.
        Schema::create('tagihans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswas')->onDelete('cascade');
            $table->foreignId('jenis_tagihan_id')->constrained('jenis_tagihans')->onDelete('cascade');
            $table->foreignId('periode_id')->nullable()->constrained('periodes')->onDelete('cascade');

            // Info target saat dibuat (untuk audit)
            $table->string('target_scope')->nullable(); // semua_kelas / kelas_tertentu / murid_tertentu
            $table->unsignedBigInteger('target_kelas_id')->nullable();

            $table->string('keterangan')->nullable();
            $table->decimal('nominal', 12, 0)->default(0);
            $table->date('tanggal_jatuh_tempo')->nullable();
            $table->string('status')->default('belum'); // belum / lunas / parsial
            $table->unsignedBigInteger('dibuat_oleh')->nullable(); // user_id
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tagihans');
    }
};
