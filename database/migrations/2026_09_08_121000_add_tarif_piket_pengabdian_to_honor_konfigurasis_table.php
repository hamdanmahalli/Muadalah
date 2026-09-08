<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('honor_konfigurasis', function (Blueprint $table) {
            $table->integer('tarif_piket_pengabdian')->default(4000)->after('tarif_piket');
        });
    }

    public function down(): void
    {
        Schema::table('honor_konfigurasis', function (Blueprint $table) {
            $table->dropColumn('tarif_piket_pengabdian');
        });
    }
};