<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jadwal_harians', function (Blueprint $table) {
            if (!Schema::hasColumn('jadwal_harians', 'tahun_ajaran')) {
                $table->string('tahun_ajaran')->nullable()->after('id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('jadwal_harians', function (Blueprint $table) {
            $table->dropColumn('tahun_ajaran');
        });
    }
};