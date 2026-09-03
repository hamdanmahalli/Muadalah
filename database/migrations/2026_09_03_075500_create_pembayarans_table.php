<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Pencatatan pembayaran (bisa angsuran/parsial hingga lunas)
        Schema::create('pembayarans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tagihan_id')->constrained('tagihans')->onDelete('cascade');
            $table->decimal('nominal_dibayar', 12, 0)->default(0);
            $table->date('tanggal_bayar')->nullable();
            $table->string('metode')->nullable(); // Tunai / Transfer
            $table->string('keterangan')->nullable();
            $table->unsignedBigInteger('user_id')->nullable(); // yang mencatat
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembayarans');
    }
};
