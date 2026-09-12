<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menambahkan kolom active_session_id untuk mendukung aturan
     * "satu akun hanya aktif di satu perangkat".
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('active_session_id', 191)->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['active_session_id']);
            $table->dropColumn('active_session_id');
        });
    }
};