<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PlotJadwal;
use App\Models\Kelas;
use App\Models\Pelajaran;
use App\Models\Guru;
use App\Models\JadwalHarian;
use App\Models\Periode;

class PlotJadwalController extends Controller
{
    public function index(Request $request)
    {
        $kelas = Kelas::orderBy('nama_kelas', 'asc')->get();
        $kelas_id = $request->kelas_id; 
        
        $pelajarans = [];
        $gurus = [];
        $plotAktif = [];
        
        $kapasitasMaksimal = \App\Models\HariOperasional::where('is_active', true)->sum('max_jam');
        $totalTarget = 0;
        $totalTerjadwal = 0;
        $terjadwalPerMapel = collect();

        $periodeAktif = get_periode_aktif();
        $tahunAjaran = $periodeAktif ? $periodeAktif->tahun_ajaran : null;

        if ($kelas_id) {
            // HANYA MENGAMBIL PELAJARAN YANG STATUSNYA AKTIF
            $pelajarans = Pelajaran::where('status', 'Aktif')->orderBy('kode_pelajaran', 'asc')->get(); 
            $gurus = Guru::where('status', 'Aktif')->orderBy('nama_guru', 'asc')->get();
            $plotAktif = PlotJadwal::where('kelas_id', $kelas_id)->get()->keyBy('pelajaran_id');
            
            $totalTarget = $plotAktif->sum('beban_jam');
            
            $totalTerjadwal = JadwalHarian::where('kelas_id', $kelas_id)
                                          ->where('tahun_ajaran', $tahunAjaran)
                                          ->count();
                                          
            $terjadwalPerMapel = JadwalHarian::where('kelas_id', $kelas_id)
                                          ->where('tahun_ajaran', $tahunAjaran)
                                          ->selectRaw('pelajaran_id, count(id) as total')
                                          ->groupBy('pelajaran_id')
                                          ->pluck('total', 'pelajaran_id');
        }

        return view('plot-jadwal', compact(
            'kelas', 'kelas_id', 'pelajarans', 'gurus', 'plotAktif', 
            'kapasitasMaksimal', 'totalTarget', 'totalTerjadwal', 'terjadwalPerMapel'
        ));
    }

    public function store(Request $request)
    {
        $kelas_id = $request->kelas_id;
        $pelajaran_id = $request->pelajaran_id;
        $beban_jam = (int) ($request->beban_jam ?? 0);
        $guru_id = $request->guru_id ?: null;
        $force_update = $request->input('force_update') == 'true';

        $kapasitasMaksimal = \App\Models\HariOperasional::where('is_active', true)->sum('max_jam');
        $periodeAktif = get_periode_aktif();
        $tahunAjaran = $periodeAktif ? $periodeAktif->tahun_ajaran : null;

        // 1. CEK OVERLOAD KAPASITAS
        $existingPlots = PlotJadwal::where('kelas_id', $kelas_id)->get();
        $totalLain = $existingPlots->where('pelajaran_id', '!=', $pelajaran_id)->sum('beban_jam');
        $totalPlotBaru = $totalLain + $beban_jam;

        if ($totalPlotBaru > $kapasitasMaksimal) {
            return response()->json([
                'status' => 'error_overload',
                'pesan' => "<b>KAPASITAS OVERLOAD!</b><br>Total beban jam ({$totalPlotBaru} Jam) melebihi kapasitas kelas ({$kapasitasMaksimal} Jam)."
            ]);
        }

        // 2. CEK BENTROK GURU
        $konfirmasiBentrok = [];
        $jadwalDihapus = [];

        if ($guru_id) {
            $jadwalEksisting = JadwalHarian::where('kelas_id', $kelas_id)
                                           ->where('pelajaran_id', $pelajaran_id);
            if ($tahunAjaran) $jadwalEksisting->where('tahun_ajaran', $tahunAjaran);
            $jadwalEksisting = $jadwalEksisting->get();

            foreach ($jadwalEksisting as $jadwal) {
                $bentrok = JadwalHarian::with('kelas')
                                       ->where('hari', $jadwal->hari)
                                       ->where('jam_ke', $jadwal->jam_ke)
                                       ->where('tahun_ajaran', $tahunAjaran)
                                       ->where('guru_id', $guru_id)
                                       ->where('kelas_id', '!=', $kelas_id)
                                       ->first();

                if ($bentrok) {
                    $guruInfo = Guru::find($guru_id);
                    $pelajaranInfo = Pelajaran::find($pelajaran_id);
                    
                    if (!$force_update) {
                        $konfirmasiBentrok[] = "Hari <b>{$jadwal->hari} Jam Ke-{$jadwal->jam_ke}</b>: Ust. {$guruInfo->nama_guru} sedang mengajar di <b>Kelas {$bentrok->kelas->nama_kelas}</b>.<br><span class='text-red-600 font-bold text-xs'>➜ Jadwal {$pelajaranInfo->nama_pelajaran} di kelas ini akan DIHAPUS.</span>";
                    } else {
                        $jadwalDihapus[] = $jadwal->id;
                    }
                }
            }
        }

        if (count($konfirmasiBentrok) > 0 && !$force_update) {
            return response()->json([
                'status' => 'error_bentrok',
                'pesan' => "Guru yang Anda pilih mengalami bentrok jadwal. Apakah Anda setuju MENGHAPUS jadwal pelajaran (milik guru sebelumnya) di kelas ini?",
                'rincian' => $konfirmasiBentrok,
                'pelajaran_id' => $pelajaran_id,
                'guru_id' => $guru_id,
                'beban_jam' => $beban_jam
            ]);
        }

        // 3. EKSEKUSI HAPUS JADWAL BENTROK (JIKA TU SETUJU)
        if (count($jadwalDihapus) > 0) {
            JadwalHarian::whereIn('id', $jadwalDihapus)->delete();
        }

        // 4. SIMPAN TARGET MENGAJAR (PLOT)
        PlotJadwal::updateOrCreate(
            ['kelas_id' => $kelas_id, 'pelajaran_id' => $pelajaran_id],
            ['guru_id' => $guru_id, 'beban_jam' => $beban_jam]
        );

        // 5. SINKRONKAN JADWAL HARIAN
        $queryJadwal = JadwalHarian::where('kelas_id', $kelas_id)
                                   ->where('pelajaran_id', $pelajaran_id);
        if ($tahunAjaran) $queryJadwal->where('tahun_ajaran', $tahunAjaran);
        $queryJadwal->update(['guru_id' => $guru_id]);

        // 6. HITUNG STATISTIK BARU UNTUK UI
        $allPlots = PlotJadwal::where('kelas_id', $kelas_id)->get();
        $totalTarget = $allPlots->sum('beban_jam');
        $totalTerjadwal = JadwalHarian::where('kelas_id', $kelas_id)
                                      ->where('tahun_ajaran', $tahunAjaran)
                                      ->count();
        $terjadwalMapel = JadwalHarian::where('kelas_id', $kelas_id)
                                      ->where('pelajaran_id', $pelajaran_id)
                                      ->where('tahun_ajaran', $tahunAjaran)
                                      ->count();

        return response()->json([
            'status' => 'success',
            'kapasitasMaksimal' => $kapasitasMaksimal, // BARIS BARU: Mengirim kapasitas untuk JavaScript
            'totalTarget' => $totalTarget,
            'totalTerjadwal' => $totalTerjadwal,
            'sisaBelum' => $totalTarget - $totalTerjadwal,
            'terjadwalMapel' => $terjadwalMapel,
            'beban_jam' => $beban_jam,
            'pelajaran_id' => $pelajaran_id
        ]);

        
    }
    // ==========================================================
    // FUNGSI: Menampilkan Form Mutasi Massal dari Master Plot
    // ==========================================================
    public function formMutasi($id)
    {
        $plot = PlotJadwal::with(['kelas', 'pelajaran', 'guru'])->findOrFail($id);
        
        // Ambil guru yang berstatus Aktif, kecuali guru yang saat ini menjabat
        $semuaGuru = Guru::where('status', 'Aktif')
                        ->where('id', '!=', $plot->guru_id)
                        ->orderBy('nama_guru', 'asc')
                        ->get();

        return view('admin.plot-mutasi', compact('plot', 'semuaGuru'));
    }

    // ==========================================================
    // FUNGSI: Eksekusi Mutasi Massal (Konsep Soft Delete)
    // ==========================================================
    public function mutasiGuru(Request $request, $id)
    {
        $request->validate([
            'guru_baru_id' => 'required|exists:gurus,id'
        ]);

        $plot = PlotJadwal::findOrFail($id);
        $guruLamaId = $plot->guru_id;
        $guruBaruId = $request->guru_baru_id;

        \Illuminate\Support\Facades\DB::transaction(function () use ($plot, $guruLamaId, $guruBaruId) {
            
            // 1. HAPUS JADWAL LAMA (Otomatis masuk ke Soft Deletes, riwayat aman!)
            JadwalHarian::where('kelas_id', $plot->kelas_id)
                        ->where('pelajaran_id', $plot->pelajaran_id)
                        ->where('guru_id', $guruLamaId)
                        ->delete();

            // 2. Ganti nama guru di Master Plotting
            $plot->update(['guru_id' => $guruBaruId]);
        });

        // Arahkan kembali ke halaman Master Plotting beserta parameter kelas_id-nya
        return redirect('/master-jadwal-harian?kelas_id=' . $plot->kelas_id)
                ->with('sukses', 'Mutasi Berhasil! Jadwal lama telah diarsipkan (Soft Delete). Silakan atur ulang Hari/Jam secara manual untuk guru baru.');
    }
}