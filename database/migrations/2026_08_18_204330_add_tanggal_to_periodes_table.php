<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('periodes', function (Blueprint $table) {
            // Menambahkan kolom tanggal mulai dan tanggal selesai
            $table->date('tanggal_mulai')->nullable()->after('semester');
            $table->date('tanggal_selesai')->nullable()->after('tanggal_mulai');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('periodes', function (Blueprint $table) {
            // Membuang kolom jika dilakukan rollback
            $table->dropColumn(['tanggal_mulai', 'tanggal_selesai']);
        });
    }
};