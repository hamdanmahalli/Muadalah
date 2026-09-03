<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Pivot many-to-many: satu pengurus bisa punya banyak jabatan
        Schema::create('guru_jabatan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guru_id')->constrained('gurus')->onDelete('cascade');
            $table->foreignId('jabatan_id')->constrained('jabatans')->onDelete('cascade');
            $table->boolean('is_utama')->default(false); // tandai jabatan utama
            $table->timestamps();

            $table->unique(['guru_id', 'jabatan_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guru_jabatan');
    }
};
