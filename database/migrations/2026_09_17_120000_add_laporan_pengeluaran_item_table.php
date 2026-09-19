<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Baris item pada LPJ: satu LPJ boleh memuat banyak pos (keranjang),
        // masing-masing mencatat "apa yang dibeli" (uraian) + pos anggaran.
        Schema::create('laporan_pengeluaran_item', function (Blueprint $table) {
            $table->id();
            $table->foreignId('laporan_pengeluaran_id')->constrained('laporan_pengeluaran')->onDelete('cascade');
            $table->foreignId('anggaran_pos_id')->constrained('anggaran_pos')->onDelete('cascade');
            $table->foreignId('pencairan_item_id')->nullable()->constrained('pencairan_item')->nullOnDelete();
            $table->string('uraian')->nullable();
            $table->decimal('nominal', 14, 0)->default(0);
        });

        // Backfill data lama: LPJ yang masih memakai pos_id tunggal dijadikan satu item.
        foreach (DB::table('laporan_pengeluaran')->whereNotNull('pos_id')->cursor() as $lp) {
            DB::table('laporan_pengeluaran_item')->insert([
                'laporan_pengeluaran_id' => $lp->id,
                'anggaran_pos_id'        => $lp->pos_id,
                'pencairan_item_id'      => null,
                'uraian'                 => $lp->keterangan,
                'nominal'                => $lp->nominal,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('laporan_pengeluaran_item');
    }
};