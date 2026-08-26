<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStatusToKehadiranKegiatansTable extends Migration
{
    public function up()
    {
        Schema::table('kehadiran_kegiatans', function (Blueprint $table) {
            $table->string('status')->default('Hadir')->after('waktu_hadir');
            $table->string('keterangan')->nullable()->after('status');
        });
    }

    public function down()
    {
        Schema::table('kehadiran_kegiatans', function (Blueprint $table) {
            $table->dropColumn(['status', 'keterangan']);
        });
    }
}