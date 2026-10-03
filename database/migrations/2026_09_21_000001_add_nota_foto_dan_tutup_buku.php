<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Buku Kas: bukti nota (gambar terkompresi) pada tiap catatan pengeluaran.
        Schema::table('laporan_pengeluaran', function (Blueprint $table) {
            $table->string('nota_foto')->nullable()->after('keterangan');
        });

        // Tutup buku: periode yang sudah dilaporkan terkunci (read-only).
        Schema::table('periodes', function (Blueprint $table) {
            $table->timestamp('tutup_buku_at')->nullable()->after('tanggal_selesai');
            $table->unsignedBigInteger('tutup_buku_oleh')->nullable()->after('tutup_buku_at');
        });
    }

    public function down(): void
    {
        Schema::table('laporan_pengeluaran', function (Blueprint $table) {
            $table->dropColumn('nota_foto');
        });

        Schema::table('periodes', function (Blueprint $table) {
            $table->dropColumn(['tutup_buku_at', 'tutup_buku_oleh']);
        });
    }
};