<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ubah alur "tutup buku" menjadi pelaporan ke bendahara:
     * - ditutup_* -> dilaporkan_* (pengaju mengirim laporan),
     * - tahap 1: diterima_bendahara_* (bendahara memvalidasi),
     * - tahap 2: disahkan_* (pimpinan mengesahkan final),
     * - revisi: dikembalikan_* + alasan_dikembalikan (pengaju perbaiki lalu kirim ulang).
     */
    public function up(): void
    {
        Schema::table('buku_kas_bulanan', function (Blueprint $table) {
            $table->renameColumn('ditutup_oleh', 'dilaporkan_oleh');
            $table->renameColumn('ditutup_at', 'dilaporkan_at');

            $table->unsignedBigInteger('diterima_bendahara_oleh')->nullable()->after('dilaporkan_at');
            $table->timestamp('diterima_bendahara_at')->nullable()->after('diterima_bendahara_oleh');

            $table->unsignedBigInteger('dikembalikan_oleh')->nullable()->after('diterima_bendahara_at');
            $table->timestamp('dikembalikan_at')->nullable()->after('dikembalikan_oleh');
            $table->text('alasan_dikembalikan')->nullable()->after('dikembalikan_at');
        });
    }

    public function down(): void
    {
        Schema::table('buku_kas_bulanan', function (Blueprint $table) {
            $table->dropColumn(['alasan_dikembalikan', 'dikembalikan_at', 'dikembalikan_oleh']);
            $table->dropColumn(['diterima_bendahara_at', 'diterima_bendahara_oleh']);
            $table->renameColumn('dilaporkan_at', 'ditutup_at');
            $table->renameColumn('dilaporkan_oleh', 'ditutup_oleh');
        });
    }
};