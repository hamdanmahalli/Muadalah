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
        Schema::table('agenda_kaldiks', function (Blueprint $table) {
            $table->string('tipe_agenda')->default('Penuh')->nullable()->after('target_libur');
            $table->json('jam_diliburkan')->nullable()->after('tipe_agenda');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('agenda_kaldiks', function (Blueprint $table) {
            $table->dropColumn(['tipe_agenda', 'jam_diliburkan']);
        });
    }
};
