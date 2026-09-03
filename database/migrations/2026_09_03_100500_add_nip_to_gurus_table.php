<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // NIP (Nomor Induk Peran) — opsional, identitas tambahan.
        // NIG tetap unik & wajib. Penambahan additive, tidak merusak data.
        Schema::table('gurus', function (Blueprint $table) {
            $table->string('nip')->nullable()->after('nig');
        });
    }

    public function down(): void
    {
        Schema::table('gurus', function (Blueprint $table) {
            $table->dropColumn('nip');
        });
    }
};
