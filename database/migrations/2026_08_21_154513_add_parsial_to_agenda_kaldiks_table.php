<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        return; // <-- TAMBAHKAN BARIS INI UNTUK BYPASS
        Schema::table('agenda_kaldiks', function (Blueprint $table) {
            $table->enum('tipe_agenda', ['Penuh', 'Parsial'])->default('Penuh')->after('target_libur');
            $table->json('jam_diliburkan')->nullable()->after('tipe_agenda');
        });
    }

    public function down()
    {
        Schema::table('agenda_kaldiks', function (Blueprint $table) {
            $table->dropColumn(['tipe_agenda', 'jam_diliburkan']);
        });
    }
};