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
        Schema::table('nilais', function (Blueprint $table) {
            $table->decimal('nilai_harian', 5, 2)->nullable()->after('nilai_uts');
            $table->decimal('skor_uts', 5, 2)->nullable()->after('nilai_harian');
            $table->decimal('nilai_uts_akhir', 5, 2)->nullable()->after('skor_uts');
            $table->decimal('skor_uas', 5, 2)->nullable()->after('nilai_uts_akhir');
            $table->decimal('nilai_uas_akhir', 5, 2)->nullable()->after('skor_uas');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nilais', function (Blueprint $table) {
            $table->dropColumn(['nilai_harian', 'skor_uts', 'nilai_uts_akhir', 'skor_uas', 'nilai_uas_akhir']);
        });
    }
};
