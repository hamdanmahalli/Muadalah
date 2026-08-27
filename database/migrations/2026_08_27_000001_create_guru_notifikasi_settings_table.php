<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guru_notifikasi_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guru_id')->unique()->constrained('gurus')->onDelete('cascade');
            $table->boolean('is_enabled')->default(true);
            $table->string('mode')->default('sound'); // sound | vibrate | silent
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guru_notifikasi_settings');
    }
};
