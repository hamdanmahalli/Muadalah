<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddKitabTingkatToPelajaransTable extends Migration
{
    /**
     * Jalankan migration.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pelajarans', function (Blueprint $table) {
            // Menggunakan JSONB yang sangat cepat di PostgreSQL
            $table->jsonb('kitab_tingkat')->nullable();
        });
    }

    /**
     * Kembalikan (rollback) migration.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pelajarans', function (Blueprint $table) {
            $table->dropColumn('kitab_tingkat');
        });
    }
}