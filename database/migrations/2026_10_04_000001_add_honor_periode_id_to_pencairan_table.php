<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pencairan', function (Blueprint $table) {
            $table->unsignedBigInteger('honor_periode_id')->nullable()->after('pos_id');
            $table->index('honor_periode_id');
            $table->foreign('honor_periode_id')
                ->references('id')
                ->on('honor_periodes')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pencairan', function (Blueprint $table) {
            $table->dropForeign(['honor_periode_id']);
            $table->dropIndex(['honor_periode_id']);
            $table->dropColumn('honor_periode_id');
        });
    }
};