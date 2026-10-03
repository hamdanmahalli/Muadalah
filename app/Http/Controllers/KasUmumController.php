<?php

namespace App\Http\Controllers;

use App\Services\KasPemegangService;
use Illuminate\Http\Request;

class KasUmumController extends Controller
{
    /**
     * Kas Umum / Buku Besar bendahara: seluruh pemasukan dan pengeluaran
     * kas bendahara dalam satu feed kronologis lengkap dengan saldo berjalan.
     * Filter per bulan fiskal & tahun.
     */
    public function index(Request $request, KasPemegangService $service)
    {
        $periode = get_periode_aktif();

        if (!$periode) {
            return redirect()->route('kebendaharaan.index')
                ->with('error', 'Tidak ada periode aktif.');
        }

        $bulan = null;
        if ($request->filled('bulan') && (int) $request->bulan >= 1 && (int) $request->bulan <= 12) {
            $bulan = (int) $request->bulan;
        }
        $tahun = $request->filled('tahun') ? (int) $request->tahun : (int) $periode->tahun;

        $data = $service->kasUmum($periode, $bulan, $tahun);

        return view('admin.kebendaharaan.kas-umum', [
            'periode'    => $periode,
            'bulan'      => $bulan,
            'tahun'      => $tahun,
            'bulanList'  => bulan_fiskal_list(),
            'baris'      => $data['baris'],
            'total'      => $data['total'],
            'labelBulan' => function (int $b) {
                return bulan_fiskal_label($b);
            },
        ]);
    }
}