<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('honor_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('honor_periode_id')->constrained('honor_periodes')->onDelete('cascade');
            $table->foreignId('guru_id')->constrained('gurus')->onDelete('cascade');
            $table->integer('jam_wajib')->default(0);
            $table->integer('alpa')->default(0);
            $table->integer('izin')->default(0);
            $table->integer('sakit')->default(0);
            $table->integer('piket_jam')->default(0);
            $table->integer('realita_jam')->default(0);
            $table->decimal('persentase', 5, 2)->default(0);
            $table->string('keterangan')->default('-');
            $table->integer('honor_pokok')->default(0);
            $table->integer('tunjangan_struktural')->default(0);
            $table->integer('tunjangan_wali_kelas')->default(0);
            $table->integer('transport')->default(0);
            $table->integer('honor_piket')->default(0);
            $table->integer('total')->default(0);
            $table->boolean('is_diterima')->default(false);
            $table->datetime('waktu_diterima')->nullable();
            $table->string('metode_penerimaan')->nullable();
            $table->string('qr_token')->unique()->nullable();
            $table->timestamps();

            $table->unique(['honor_periode_id', 'guru_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('honor_details');
    }
};
