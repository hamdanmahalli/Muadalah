<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Buku kas kini ditutup PER SPP: satu SPP rutin yang dibayar = satu buku berjalan.
        Schema::table('buku_kas_bulanan', function (Blueprint $table) {
            $table->unsignedBigInteger('pencairan_id')->nullable();

            $table->dropUnique(['periode_id', 'bulan_fiskal']);
            $table->unique(['periode_id', 'pencairan_id']);
        });

        Schema::table('buku_kas_bulanan', function (Blueprint $table) {
            $table->foreign('pencairan_id')
                ->references('id')->on('pencairan')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('buku_kas_bulanan', function (Blueprint $table) {
            $table->dropUnique(['periode_id', 'pencairan_id']);
            $table->unique(['periode_id', 'bulan_fiskal']);
        });

        Schema::table('buku_kas_bulanan', function (Blueprint $table) {
            $table->dropForeign(['pencairan_id']);
            $table->dropColumn('pencairan_id');
        });
    }
};