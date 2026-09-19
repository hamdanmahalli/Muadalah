<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Dokumen RAB (Rancangan Anggaran Belanja) per periode tahun ajaran
        Schema::create('anggaran_kebendaharaan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('periode_id')->constrained('periodes')->onDelete('cascade');
            $table->string('nama');
            $table->string('tahun_ajaran')->nullable();
            $table->string('status')->default('draft'); // draft / final
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });

        // Kelompok pos (kode ratusan: 100, 200, ...)
        Schema::create('anggaran_kelompok', function (Blueprint $table) {
            $table->id();
            $table->foreignId('anggaran_id')->constrained('anggaran_kebendaharaan')->onDelete('cascade');
            $table->integer('kode');
            $table->string('nama');
            $table->integer('urutan')->default(0);
            $table->timestamps();
        });

        // Item pos belanja (kode di bawah kelompok: 101, 102, ...)
        Schema::create('anggaran_pos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('anggaran_id')->constrained('anggaran_kebendaharaan')->onDelete('cascade');
            $table->foreignId('kelompok_id')->constrained('anggaran_kelompok')->onDelete('cascade');
            $table->integer('kode');
            $table->string('uraian');
            $table->decimal('volume', 12, 2)->default(1);
            $table->string('satuan')->nullable();
            $table->decimal('volume_2', 12, 2)->nullable();
            $table->string('satuan_2')->nullable();
            $table->decimal('harga_satuan', 14, 0)->default(0);
            $table->decimal('jumlah', 14, 0)->default(0); // hasil hitung volume*volume_2*harga
            $table->timestamps();
        });

        // Pemasukan rencana (bagian PEMASUKAN di RAB, contoh: BOS Wustha)
        Schema::create('anggaran_pemasukan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('anggaran_id')->constrained('anggaran_kebendaharaan')->onDelete('cascade');
            $table->integer('urutan')->default(0);
            $table->string('uraian');
            $table->decimal('volume', 12, 2)->nullable();
            $table->string('satuan')->nullable();
            $table->decimal('harga_satuan', 14, 0)->default(0);
            $table->decimal('jumlah', 14, 0)->default(0); // volume * harga_satuan
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('anggaran_pemasukan');
        Schema::dropIfExists('anggaran_pos');
        Schema::dropIfExists('anggaran_kelompok');
        Schema::dropIfExists('anggaran_kebendaharaan');
    }
};