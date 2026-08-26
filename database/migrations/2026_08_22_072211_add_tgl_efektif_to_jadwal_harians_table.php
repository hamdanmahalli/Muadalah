<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('jadwal_harians', function (Blueprint $table) {
            // Menambahkan kolom batas efektif jadwal
            $table->date('tgl_efektif_mulai')->nullable()->after('jam_ke');
            $table->date('tgl_efektif_selesai')->nullable()->after('tgl_efektif_mulai');
        });
    }

    public function down()
    {
        Schema::table('jadwal_harians', function (Blueprint $table) {
            $table->dropColumn(['tgl_efektif_mulai', 'tgl_efektif_selesai']);
        });
    }
};