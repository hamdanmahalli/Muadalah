<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('honor_konfigurasis', function (Blueprint $table) {
            $table->renameColumn('tarif_jam_magang', 'tarif_pengabdian');
        });

        DB::table('honor_guru_configs')
            ->where('status_honor', 'Magang')
            ->update(['status_honor' => 'Pengabdian']);
    }

    public function down(): void
    {
        DB::table('honor_guru_configs')
            ->where('status_honor', 'Pengabdian')
            ->update(['status_honor' => 'Magang']);

        Schema::table('honor_konfigurasis', function (Blueprint $table) {
            $table->renameColumn('tarif_pengabdian', 'tarif_jam_magang');
        });
    }
};