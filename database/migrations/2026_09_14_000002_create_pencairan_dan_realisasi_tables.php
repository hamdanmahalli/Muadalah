<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Pencairan (SPP): pengajuan dana. Belum menjadi pengeluaran sampai ada LPJ divalidasi.
        Schema::create('pencairan', function (Blueprint $table) {
            $table->id();
            $table->string('kode'); // SPP-2026-0001
            $table->foreignId('periode_id')->constrained('periodes')->onDelete('cascade');
            $table->foreignId('pos_id')->nullable()->constrained('anggaran_pos')->nullOnDelete();
            $table->string('jenis')->default('rutin'); // rutin / modal_toko
            $table->date('tanggal_aju');
            $table->decimal('nominal', 14, 0)->default(0);
            $table->text('keperluan')->nullable();
            $table->string('status')->default('diajukan'); // diajukan / disetujui / dibayar / ditolak
            $table->unsignedBigInteger('diajukan_oleh')->nullable();
            $table->unsignedBigInteger('disetujui_oleh')->nullable();
            $table->timestamp('disetujui_at')->nullable();
            $table->unsignedBigInteger('dibayar_oleh')->nullable();
            $table->timestamp('dibayar_at')->nullable();
            $table->text('tolak_alasan')->nullable();
            $table->timestamps();
        });

        // Laporan Pertanggung Jawaban (LPJ): sumber realisasi pengeluaran.
        Schema::create('laporan_pengeluaran', function (Blueprint $table) {
            $table->id();
            $table->string('kode'); // LPJ-2026-0001
            $table->foreignId('periode_id')->constrained('periodes')->onDelete('cascade');
            $table->foreignId('pos_id')->nullable()->constrained('anggaran_pos')->nullOnDelete();
            $table->foreignId('pencairan_id')->nullable()->constrained('pencairan')->nullOnDelete();
            $table->date('tanggal');
            $table->decimal('nominal', 14, 0)->default(0);
            $table->text('keterangan')->nullable();
            $table->string('status')->default('diajukan'); // diajukan / disetujui / ditolak
            $table->unsignedBigInteger('dibuat_oleh')->nullable();
            $table->unsignedBigInteger('divalidasi_oleh')->nullable();
            $table->timestamp('divalidasi_at')->nullable();
            $table->timestamps();
        });

        // Pemasukan dana (dicatat bendahara).
        Schema::create('pemasukan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('periode_id')->constrained('periodes')->onDelete('cascade');
            $table->foreignId('anggaran_pemasukan_id')->nullable()->constrained('anggaran_pemasukan')->nullOnDelete();
            $table->string('uraian');
            $table->date('tanggal');
            $table->decimal('jumlah', 14, 0)->default(0);
            $table->text('keterangan')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });

        // Pinjaman dana (moda blusukan): mis. staf pinjam dana ke bendahara untuk beli buku.
        Schema::create('pinjaman', function (Blueprint $table) {
            $table->id();
            $table->string('kode'); // PJ-2026-0001
            $table->foreignId('periode_id')->constrained('periodes')->onDelete('cascade');
            $table->foreignId('pencairan_id')->nullable()->constrained('pencairan')->nullOnDelete();
            $table->unsignedBigInteger('peminjam_user_id')->nullable();
            $table->decimal('jumlah', 14, 0)->default(0);
            $table->date('tanggal');
            $table->text('keperluan')->nullable();
            $table->string('status')->default('aktif'); // aktif / lunas
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pinjaman');
        Schema::dropIfExists('pemasukan');
        Schema::dropIfExists('laporan_pengeluaran');
        Schema::dropIfExists('pencairan');
    }
};