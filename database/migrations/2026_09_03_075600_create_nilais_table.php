<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Nilai siswa per pelajaran per periode. nilai_akhir = (UTS + UAS) ditotal.
        Schema::create('nilais', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswas')->onDelete('cascade');
            $table->foreignId('periode_id')->nullable()->constrained('periodes')->onDelete('cascade');
            $table->foreignId('pelajaran_id')->constrained('pelajarans')->onDelete('cascade');
            $table->foreignId('kelas_id')->nullable()->constrained('kelas')->onDelete('cascade');
            $table->unsignedBigInteger('guru_id')->nullable(); // penginput
            $table->decimal('nilai_uts', 5, 2)->nullable();
            $table->decimal('nilai_uas', 5, 2)->nullable();
            $table->decimal('nilai_akhir', 5, 2)->nullable();
            $table->string('predikat')->nullable(); // A / B / C / D
            $table->string('catatan')->nullable();
            $table->timestamps();

            // Satu siswa + satu pelajaran + satu periode hanya boleh punya satu nilai
            $table->unique(['siswa_id', 'pelajaran_id', 'periode_id'], 'nilai_unik_siswa_pelajaran_periode');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nilais');
    }
};
