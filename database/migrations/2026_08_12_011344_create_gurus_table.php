<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up(): void
    {
        Schema::create('gurus', function (Blueprint $table) {
            $table->id();
            $table->string('nig')->unique(); 
            $table->string('nama_guru');
            
            // FITUR BARU: Kolom tambahan sesuai kebutuhan lapangan
            $table->string('no_hp')->nullable(); 
            $table->enum('gender', ['Laki-laki', 'Perempuan'])->nullable();
            $table->text('alamat')->nullable();
            $table->string('status')->default('Aktif'); // Aktif / Nonaktif
            
            $table->timestamps();
        });
    }
};
