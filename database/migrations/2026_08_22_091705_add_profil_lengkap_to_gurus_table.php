<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('gurus', function (Blueprint $table) {
            $table->string('tempat_lahir')->nullable()->after('nama_guru');
            $table->date('tanggal_lahir')->nullable()->after('tempat_lahir');
            // Catatan: Kolom 'gender', 'alamat', 'no_hp', 'status' diasumsikan sudah ada sebelumnya
            $table->string('pendidikan_terakhir')->nullable()->after('alamat');
        });
    }

    public function down()
    {
        Schema::table('gurus', function (Blueprint $table) {
            $table->dropColumn(['tempat_lahir', 'tanggal_lahir', 'pendidikan_terakhir']);
        });
    }
};