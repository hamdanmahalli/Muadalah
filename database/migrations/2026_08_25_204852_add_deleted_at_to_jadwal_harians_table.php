<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDeletedAtToJadwalHariansTable extends Migration
{
    public function up()
    {
        Schema::table('jadwal_harians', function (Blueprint $table) {
            $table->softDeletes(); // Menambahkan kolom deleted_at
        });
    }

    public function down()
    {
        Schema::table('jadwal_harians', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
}