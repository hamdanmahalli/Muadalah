<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambahan index performa untuk PostgreSQL.
     *
     * Catatan: Di PostgreSQL, foreign key CONSTRAINT tidak otomatis membuat index
     * pada kolom referensi (berbeda dengan MySQL/InnoDB). Kolom-kolom hot di bawah
     * ini dipakai pada WHERE/JOIN namun tanpa index -> berpotensi sequential
     * scan. Index ini mempercepat query yang paling sering dijalankan.
     */
    public function up(): void
    {
        // ===================== jadwal_harians (tabel terpanas) =====================
        Schema::table('jadwal_harians', function (Blueprint $table) {
            $table->index(['kelas_id', 'hari', 'jam_ke', 'tahun_ajaran'], 'idx_jadwal_kls_hari_jam');
            $table->index(['guru_id', 'tahun_ajaran'], 'idx_jadwal_guru_thn');
            $table->index(['pelajaran_id', 'kelas_id'], 'idx_jadwal_pel_kls');
            $table->index('deleted_at', 'idx_jadwal_deleted_at');
            $table->index(['berlaku_mulai', 'berlaku_sampai'], 'idx_jadwal_berlaku');
        });

        // ===================== kehadiran_gurus =====================
        Schema::table('kehadiran_gurus', function (Blueprint $table) {
            $table->index(['jadwal_id', 'tanggal'], 'idx_kg_jadwal_tanggal');
            $table->index(['tanggal', 'periode_id'], 'idx_kg_tanggal_periode');
        });

        // ===================== angkatan_siswas =====================
        Schema::table('angkatan_siswas', function (Blueprint $table) {
            $table->index(['kelas_id', 'periode_id'], 'idx_angkatan_kls_periode');
            $table->index('siswa_id', 'idx_angkatan_siswa');
        });

        // ===================== plot_jadwals =====================
        Schema::table('plot_jadwals', function (Blueprint $table) {
            $table->index(['kelas_id', 'pelajaran_id'], 'idx_plot_kls_pel');
            $table->index('guru_id', 'idx_plot_guru');
        });

        // ===================== tagihans =====================
        Schema::table('tagihans', function (Blueprint $table) {
            $table->index(['siswa_id', 'jenis_tagihan_id', 'periode_id'], 'idx_tag_siswa_jenis_periode');
            $table->index('status', 'idx_tag_status');
        });

        // ===================== pembayarans =====================
        Schema::table('pembayarans', function (Blueprint $table) {
            $table->index('tagihan_id', 'idx_bayar_tagihan');
        });

        // ===================== mutasi_jadwals =====================
        Schema::table('mutasi_jadwals', function (Blueprint $table) {
            $table->index(['periode_id', 'tanggal_kejadian'], 'idx_mutasi_periode_tgl');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jadwal_harians', function (Blueprint $table) {
            $table->dropIndex('idx_jadwal_kls_hari_jam');
            $table->dropIndex('idx_jadwal_guru_thn');
            $table->dropIndex('idx_jadwal_pel_kls');
            $table->dropIndex('idx_jadwal_deleted_at');
            $table->dropIndex('idx_jadwal_berlaku');
        });

        Schema::table('kehadiran_gurus', function (Blueprint $table) {
            $table->dropIndex('idx_kg_jadwal_tanggal');
            $table->dropIndex('idx_kg_tanggal_periode');
        });

        Schema::table('angkatan_siswas', function (Blueprint $table) {
            $table->dropIndex('idx_angkatan_kls_periode');
            $table->dropIndex('idx_angkatan_siswa');
        });

        Schema::table('plot_jadwals', function (Blueprint $table) {
            $table->dropIndex('idx_plot_kls_pel');
            $table->dropIndex('idx_plot_guru');
        });

        Schema::table('tagihans', function (Blueprint $table) {
            $table->dropIndex('idx_tag_siswa_jenis_periode');
            $table->dropIndex('idx_tag_status');
        });

        Schema::table('pembayarans', function (Blueprint $table) {
            $table->dropIndex('idx_bayar_tagihan');
        });

        Schema::table('mutasi_jadwals', function (Blueprint $table) {
            $table->dropIndex('idx_mutasi_periode_tgl');
        });
    }
};
