<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Buku Kas kini ditutup PER BULAN FISKAL (1=Juli ... 12=Juni) dalam satu periode.
        // Tabel ini menyimpan snapshot penutupan + status pengesahan tiap bulan.
        Schema::create('buku_kas_bulanan', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('periode_id');
            $table->unsignedTinyInteger('bulan_fiskal');   // 1..12
            $table->unsignedSmallInteger('tahun_fiskal');  // tahun kalender awal (mis. 2026)
            $table->decimal('total_masuk', 14, 0)->default(0);
            $table->decimal('total_keluar', 14, 0)->default(0);
            $table->decimal('pemasukan_manual', 14, 0)->default(0);
            $table->decimal('sisa', 14, 0)->default(0);
            $table->unsignedBigInteger('ditutup_oleh')->nullable();
            $table->timestamp('ditutup_at')->nullable();
            $table->unsignedBigInteger('disahkan_oleh')->nullable();
            $table->timestamp('disahkan_at')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->unique(['periode_id', 'bulan_fiskal']);
            $table->index('periode_id');
        });

        // Kolom tutup buku lama (per periode) tidak dipakai lagi.
        Schema::table('periodes', function (Blueprint $table) {
            $table->dropColumn(['tutup_buku_at', 'tutup_buku_oleh']);
        });
    }

    public function down(): void
    {
        Schema::table('periodes', function (Blueprint $table) {
            $table->timestamp('tutup_buku_at')->nullable()->after('tanggal_selesai');
            $table->unsignedBigInteger('tutup_buku_oleh')->nullable()->after('tutup_buku_at');
        });

        Schema::dropIfExists('buku_kas_bulanan');
    }
};
