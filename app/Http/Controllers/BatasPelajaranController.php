<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BatasPelajaran;
use App\Models\Pelajaran;
use App\Models\Periode; 

class BatasPelajaranController extends Controller
{
    public function index(Request $request)
    {
        // 1. Kunci ke Periode Aktif (Hanya menggunakan is_active sesuai struktur tabel Anda)
        $periodeAktif = get_periode_aktif();
        if (!$periodeAktif) {
            return redirect()->back()->with('error', 'Tidak ada Tahun Ajaran/Periode yang aktif saat ini!');
        }

        // 2. Tangkap filter tingkat (Default ke Kelas 7 / 1 Ulya)
        $tingkatPilihan = $request->input('tingkat', '7');
        
        // 3. Ambil data pelajaran yang statusnya aktif
        $pelajarans = Pelajaran::where('status', 'Aktif')->orderBy('kode_pelajaran', 'asc')->get();
        
        // 4. Ambil data batas pelajaran yang sudah tersimpan untuk periode & tingkat ini
        $batasData = BatasPelajaran::where('periode_id', $periodeAktif->id)
                        ->where('tingkat', $tingkatPilihan)
                        ->get()
                        ->keyBy('pelajaran_id'); 

        return view('batas-pelajaran', compact('periodeAktif', 'tingkatPilihan', 'pelajarans', 'batasData'));
    }

    public function store(Request $request)
    {
        // Kunci ke Periode Aktif (Hanya menggunakan is_active)
        $periodeAktif = Periode::where('is_active', true)->firstOrFail();

        $request->validate([
            'tingkat'                        => 'required|string|max:10',
            'batas'                          => 'nullable|array',
            'batas.*.mulai_dari'             => 'nullable|string|max:100',
            'batas.*.batas_uts_ganjil'       => 'nullable|string|max:100',
            'batas.*.batas_uas_ganjil'       => 'nullable|string|max:100',
            'batas.*.batas_uts_genap'        => 'nullable|string|max:100',
            'batas.*.batas_uas_genap'        => 'nullable|string|max:100',
        ]);

        $tingkat = $request->tingkat;

        if ($request->has('batas')) {
            foreach ($request->batas as $pelajaranId => $dataBatas) {
                BatasPelajaran::updateOrCreate(
                    [
                        'periode_id'   => $periodeAktif->id,
                        'pelajaran_id' => $pelajaranId,
                        'tingkat'      => $tingkat
                    ],
                    [
                        'mulai_dari'       => $dataBatas['mulai_dari'] ?? null,
                        'batas_uts_ganjil' => $dataBatas['batas_uts_ganjil'] ?? null,
                        'batas_uas_ganjil' => $dataBatas['batas_uas_ganjil'] ?? null,
                        'batas_uts_genap'  => $dataBatas['batas_uts_genap'] ?? null,
                        'batas_uas_genap'  => $dataBatas['batas_uas_genap'] ?? null,
                    ]
                );
            }
        }

        return redirect()->back()->with('sukses', 'Target kurikulum Tingkat/Kelas ' . $tingkat . ' berhasil disimpan!');
    }
    // ==========================================================
    // API: PENGAMBIL DATA TARGET KURIKULUM UNTUK POP-UP GURU
    // ==========================================================
    public function getTargetKurikulum(Request $request)
    {
        $periodeAktif = get_periode_aktif();
        if (!$periodeAktif) {
            return response()->json(['status' => 'error', 'pesan' => 'Tidak ada periode aktif.']);
        }

        // Ekstrak angka tingkat kelas saja (misal: "9-B" -> "9", "Kelas 7A" -> "7")
        $tingkat = preg_replace('/[^0-9]/', '', $request->kelas);

        $batas = BatasPelajaran::where('periode_id', $periodeAktif->id)
                    ->where('tingkat', $tingkat)
                    ->where('pelajaran_id', $request->pelajaran_id)
                    ->first();

        if ($batas) {
            return response()->json(['status' => 'success', 'data' => $batas]);
        }

        return response()->json(['status' => 'empty', 'pesan' => 'Target kurikulum belum diatur oleh TU.']);
    }
}