<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('log_notifikasis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guru_id')->constrained('gurus')->onDelete('cascade');
            $table->foreignId('jadwal_id')->constrained('jadwal_harians')->onDelete('cascade');
            $table->date('tanggal');
            $table->timestamp('sent_at');
            $table->timestamps();

            $table->unique(['guru_id', 'jadwal_id', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('log_notifikasis');
    }
};
