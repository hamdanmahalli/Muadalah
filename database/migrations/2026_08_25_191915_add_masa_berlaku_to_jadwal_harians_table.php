<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('jadwal_harians', function (Blueprint $table) {
            $table->date('berlaku_mulai')->nullable()->after('hari');
            $table->date('berlaku_sampai')->nullable()->after('berlaku_mulai');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jadwal_harians', function (Blueprint $table) {
            //
        });
    }
};
