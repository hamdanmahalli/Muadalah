<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Nilai harian dipisah menjadi dua: satu untuk porsi UTS, satu untuk porsi UAS.
     *   - nilai_harian_uts : komponen Nilai Harian pada UTS
     *   - nilai_harian_uas : komponen Nilai Harian pada UAS
     */
    public function up(): void
    {
        Schema::table('nilais', function (Blueprint $table) {
            $table->renameColumn('nilai_harian', 'nilai_harian_uts');
            $table->decimal('nilai_harian_uas', 5, 2)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('nilais', function (Blueprint $table) {
            $table->dropColumn('nilai_harian_uas');
            $table->renameColumn('nilai_harian_uts', 'nilai_harian');
        });
    }
};
