<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('agenda_kaldiks', function (Blueprint $table) {
            $table->id();
            // Relasi ke tabel periodes, gunakan cascade agar saat periode dihapus, agenda ikut terhapus
            $table->foreignId('periode_id')->constrained('periodes')->onDelete('cascade');
            
            $table->string('nama_agenda');
            // Menggunakan ENUM untuk mengunci opsi, mencegah TU salah input data
            $table->enum('jenis_agenda', ['Libur', 'UTS', 'UAS', 'Kegiatan']);
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->enum('target_libur', ['semua', 'kelas_tertentu'])->default('semua');
            $table->json('kelas_ids')->nullable(); // Disimpan dalam JSON agar ringan
            $table->text('keterangan')->nullable();
            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('agenda_kaldiks');
    }
};
