<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Kolom preferensi tampilan aplikasi per akun (sistem / terang / gelap).
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('tema', 20)->default('sistem');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('tema');
        });
    }
};