<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Menghubungkan setoran wali kelas dengan pinjaman yang dilunasi secara otomatis.
        Schema::table('setoran_barang', function (Blueprint $table) {
            $table->foreignId('pinjaman_id')->nullable()->after('distribusi_barang_id')->constrained('pinjaman')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('setoran_barang', function (Blueprint $table) {
            $table->dropConstrainedForeignId('pinjaman_id');
        });
    }
};