<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Menambahkan kolom periode_id di tabel jadwal harian
        Schema::table('jadwal_harians', function (Blueprint $table) {
            $table->unsignedBigInteger('periode_id')->nullable()->after('id');
        });

        // 2. Menambahkan kolom periode_id di tabel kehadiran guru
        Schema::table('kehadiran_gurus', function (Blueprint $table) {
            $table->unsignedBigInteger('periode_id')->nullable()->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('jadwal_harians', function (Blueprint $table) {
            $table->dropColumn('periode_id');
        });

        Schema::table('kehadiran_gurus', function (Blueprint $table) {
            $table->dropColumn('periode_id');
        });
    }
};