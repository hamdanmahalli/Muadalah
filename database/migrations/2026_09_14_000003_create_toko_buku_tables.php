<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Master barang/buku yang dijual
        Schema::create('barang', function (Blueprint $table) {
            $table->id();
            $table->string('kode'); // BRG-0001
            $table->string('nama');
            $table->string('satuan')->default('Pcs');
            $table->decimal('harga_beli', 14, 0)->default(0);
            $table->decimal('harga_jual', 14, 0)->default(0);
            $table->boolean('is_active')->default(true);
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });

        // Pembelian barang (stok masuk) - dibeli dari dana pinjaman/pencairan modal
        Schema::create('pembelian_barang', function (Blueprint $table) {
            $table->id();
            $table->string('kode'); // PB-2026-0001
            $table->foreignId('pencairan_id')->nullable()->constrained('pencairan')->nullOnDelete();
            $table->date('tanggal');
            $table->decimal('total', 14, 0)->default(0);
            $table->text('keterangan')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });

        Schema::create('pembelian_barang_item', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pembelian_barang_id')->constrained('pembelian_barang')->onDelete('cascade');
            $table->foreignId('barang_id')->constrained('barang')->onDelete('cascade');
            $table->decimal('qty', 12, 2)->default(1);
            $table->decimal('harga_beli', 14, 0)->default(0);
            $table->decimal('harga_jual', 14, 0)->default(0);
            $table->timestamps();
        });

        // Distribusi: wali kelas mengambil buku untuk kelasnya (stok keluar)
        Schema::create('distribusi_barang', function (Blueprint $table) {
            $table->id();
            $table->string('kode'); // DSB-2026-0001
            $table->foreignId('wali_kelas_id')->nullable()->constrained('gurus')->nullOnDelete();
            $table->foreignId('kelas_id')->nullable()->constrained('kelas')->nullOnDelete();
            $table->date('tanggal');
            $table->text('keterangan')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });

        Schema::create('distribusi_barang_item', function (Blueprint $table) {
            $table->id();
            $table->foreignId('distribusi_barang_id')->constrained('distribusi_barang')->onDelete('cascade');
            $table->foreignId('barang_id')->constrained('barang')->onDelete('cascade');
            $table->decimal('qty', 12, 2)->default(1);
            $table->decimal('harga_jual', 14, 0)->default(0);
            $table->timestamps();
        });

        // Penjualan per murid (dicatat wali kelas; uang masih dipegang wali kelas)
        Schema::create('penjualan_barang', function (Blueprint $table) {
            $table->id();
            $table->foreignId('distribusi_barang_id')->constrained('distribusi_barang')->onDelete('cascade');
            $table->foreignId('siswa_id')->constrained('siswas')->onDelete('cascade');
            $table->foreignId('barang_id')->constrained('barang')->onDelete('cascade');
            $table->decimal('qty', 12, 2)->default(1);
            $table->decimal('harga_jual', 14, 0)->default(0);
            $table->date('tanggal');
            $table->string('status')->default('belum'); // belum / lunas
            $table->unsignedBigInteger('dicatat_oleh')->nullable();
            $table->timestamps();
        });

        // Setoran wali kelas ke staf bendahara (jadi pemasukan di buku bendahara)
        Schema::create('setoran_barang', function (Blueprint $table) {
            $table->id();
            $table->string('kode'); // STD-2026-0001
            $table->foreignId('distribusi_barang_id')->nullable()->constrained('distribusi_barang')->nullOnDelete();
            $table->foreignId('wali_kelas_id')->nullable()->constrained('gurus')->nullOnDelete();
            $table->date('tanggal');
            $table->decimal('total', 14, 0)->default(0);
            $table->text('keterangan')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('setoran_barang');
        Schema::dropIfExists('penjualan_barang');
        Schema::dropIfExists('distribusi_barang_item');
        Schema::dropIfExists('distribusi_barang');
        Schema::dropIfExists('pembelian_barang_item');
        Schema::dropIfExists('pembelian_barang');
        Schema::dropIfExists('barang');
    }
};