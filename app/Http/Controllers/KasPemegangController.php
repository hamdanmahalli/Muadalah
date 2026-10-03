<?php

namespace App\Http\Controllers;

use App\Services\KasPemegangService;
use Illuminate\Http\Request;

class KasPemegangController extends Controller
{
    /**
     * Halaman kas per pemegang: uang yang dipegang tiap orang (SPP dibayar,
     * belanja, dan pemasukan manual), dirinci per periode/bulan fiskal.
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

        $data = $service->ringkas($periode, $bulan, $tahun);

        return view('admin.kebendaharaan.kas-pemegang', [
            'periode'   => $periode,
            'bulan'     => $bulan,
            'tahun'     => $tahun,
            'bulanList' => bulan_fiskal_list(),
            'pemegang'  => $data['pemegang'],
            'total'     => $data['total'],
            'labelBulan' => function (int $b) {
                return bulan_fiskal_label($b);
            },
        ]);
    }
}