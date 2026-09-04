<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambahkan constraint UNIQUE untuk integritas data.
     *
     * Pasangan kolom berikut selalu di-update via updateOrCreate() sehingga
     * seharusnya unik. Index non-unique dari migration sebelumnya diganti
     * menjadi UNIQUE (di PostgreSQL, UNIQUE juga otomatis membuat index).
     *
     * Data duplikat sudah diperiksa: tidak ada.
     */
    public function up(): void
    {
        // plot_jadwals: satu pelajaran hanya boleh di-plot satu kali per kelas
        Schema::table('plot_jadwals', function (Blueprint $table) {
            $table->dropIndex('idx_plot_kls_pel');
            $table->unique(['kelas_id', 'pelajaran_id'], 'plot_kls_pel_unik');
        });

        // kehadiran_gurus: satu jadwal hanya boleh punya satu record kehadiran per tanggal
        Schema::table('kehadiran_gurus', function (Blueprint $table) {
            $table->dropIndex('idx_kg_jadwal_tanggal');
            $table->unique(['jadwal_id', 'tanggal'], 'kehadiran_guru_jadwal_tanggal_unik');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plot_jadwals', function (Blueprint $table) {
            $table->dropUnique('plot_kls_pel_unik');
            $table->index(['kelas_id', 'pelajaran_id'], 'idx_plot_kls_pel');
        });

        Schema::table('kehadiran_gurus', function (Blueprint $table) {
            $table->dropUnique('kehadiran_guru_jadwal_tanggal_unik');
            $table->index(['jadwal_id', 'tanggal'], 'idx_kg_jadwal_tanggal');
        });
    }
};
