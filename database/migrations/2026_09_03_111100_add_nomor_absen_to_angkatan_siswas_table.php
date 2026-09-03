<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Nomor absen siswa dalam kelas, TETAP selama satu tahun ajaran (per penempatan/angkatan)
        if (!Schema::hasColumn('angkatan_siswas', 'nomor_absen')) {
            Schema::table('angkatan_siswas', function (Blueprint $table) {
                $table->integer('nomor_absen')->nullable()->after('kelas_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('angkatan_siswas', 'nomor_absen')) {
            Schema::table('angkatan_siswas', function (Blueprint $table) {
                $table->dropColumn('nomor_absen');
            });
        }
    }
};
