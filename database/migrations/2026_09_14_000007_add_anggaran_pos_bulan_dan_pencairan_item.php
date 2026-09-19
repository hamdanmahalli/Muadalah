<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Alokasi bulanan per pos belanja (1=Juli ... 12=Juni). Sumber: kolom bulan di file RAB.
        Schema::create('anggaran_pos_bulan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('anggaran_pos_id')->constrained('anggaran_pos')->onDelete('cascade');
            $table->smallInteger('bulan_fiskal'); // 1=Jul, 2=Ags, ... 12=Jun
            $table->decimal('nominal', 14, 0)->default(0);
            $table->unique(['anggaran_pos_id', 'bulan_fiskal']);
        });

        // Baris item pada SPP: satu SPP boleh memuat banyak pos dalam bulan yang sama.
        Schema::create('pencairan_item', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pencairan_id')->constrained('pencairan')->onDelete('cascade');
            $table->foreignId('anggaran_pos_id')->constrained('anggaran_pos')->onDelete('cascade');
            $table->smallInteger('bulan_fiskal')->nullable();
            $table->decimal('nominal', 14, 0)->default(0);
        });

        Schema::table('pencairan', function (Blueprint $table) {
            $table->smallInteger('bulan_fiskal')->nullable()->after('pos_id');
        });

        // Backfill data lama: SPP yang masih memakai pos_id tunggal dijadikan satu item,
        // dan status 'disetujui' disederhanakan menjadi 'dibayar' (validasi = bayar).
        foreach (DB::table('pencairan')->whereNotNull('pos_id')->cursor() as $pc) {
            DB::table('pencairan_item')->insert([
                'pencairan_id'    => $pc->id,
                'anggaran_pos_id' => $pc->pos_id,
                'bulan_fiskal'    => null,
                'nominal'         => $pc->nominal,
            ]);
        }

        foreach (DB::table('pencairan')->where('status', 'disetujui')->orderBy('id')->cursor() as $pc) {
            DB::table('pencairan')->where('id', $pc->id)->update([
                'status'         => 'dibayar',
                'dibayar_oleh'   => $pc->disetujui_oleh,
                'dibayar_at'     => $pc->disetujui_at,
                'disetujui_oleh' => null,
                'disetujui_at'   => null,
            ]);
        }
    }

    public function down(): void
    {
        DB::table('pencairan')->where('status', 'dibayar')->whereNull('disetujui_at')->orderBy('id')->each(function ($pc) {
            // Upaya minimal mengembalikan status yang tadinya disetujui.
        });
        Schema::table('pencairan', function (Blueprint $table) {
            $table->dropColumn('bulan_fiskal');
        });
        Schema::dropIfExists('pencairan_item');
        Schema::dropIfExists('anggaran_pos_bulan');
    }
};