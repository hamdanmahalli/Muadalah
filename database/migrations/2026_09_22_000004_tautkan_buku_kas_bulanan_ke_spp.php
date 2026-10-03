<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Backfill: tautkan buku kas lama (bulan fiskal, tanpa pencairan_id)
     * ke SPP rutin dibayar pada bulan fiskal yang sama, agar tidak terbaca
     * sebagai "buku terbuka" pada alur buku kas per SPP.
     */
    public function up(): void
    {
        $bukuRows = DB::table('buku_kas_bulanan as b')
            ->whereNull('b.pencairan_id')
            ->get(['b.id', 'b.periode_id', 'b.bulan_fiskal', 'b.tahun_fiskal']);

        foreach ($bukuRows as $buku) {
            $spp = DB::table('pencairan as p')
                ->where('p.periode_id', $buku->periode_id)
                ->where('p.bulan_fiskal', $buku->bulan_fiskal)
                ->orderBy('p.id', 'desc')
                ->first(['p.id']);

            if ($spp) {
                DB::table('buku_kas_bulanan as b')
                    ->where('b.id', $buku->id)
                    ->update(['pencairan_id' => $spp->id]);
            }
        }
    }

    public function down(): void
    {
        DB::table('buku_kas_bulanan')
            ->whereNotNull('pencairan_id')
            ->update(['pencairan_id' => null]);
    }
};