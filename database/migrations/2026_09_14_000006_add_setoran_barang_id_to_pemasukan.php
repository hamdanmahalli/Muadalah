<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Menandai pemasukan yang lahir otomatis dari setoran penjualan buku.
        Schema::table('pemasukan', function (Blueprint $table) {
            $table->foreignId('setoran_barang_id')->nullable()->after('anggaran_pemasukan_id')->constrained('setoran_barang')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('pemasukan', function (Blueprint $table) {
            $table->dropConstrainedForeignId('setoran_barang_id');
        });
    }
};