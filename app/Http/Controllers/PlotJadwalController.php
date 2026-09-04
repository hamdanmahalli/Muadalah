<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PlotJadwal;
use App\Models\Kelas;
use App\Models\Pelajaran;
use App\Models\Guru;
use App\Models\JadwalHarian;
use App\Services\Jadwal\PlotJadwalService;
use App\Services\Jadwal\JadwalScheduleService;

class PlotJadwalController extends Controller
{
    public function __construct(
        protected PlotJadwalService $plotService,
        protected JadwalScheduleService $schedule,
    ) {}

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
        $request->validate([
            'kelas_id'     => 'required|exists:kelas,id',
            'pelajaran_id' => 'required|exists:pelajarans,id',
            'beban_jam'    => 'required|integer|between:0,15',
            'guru_id'      => 'nullable|exists:gurus,id',
            'force_update' => 'nullable|in:true,false',
        ]);

        $periodeAktif = get_periode_aktif();
        $tahunAjaran = $periodeAktif ? $periodeAktif->tahun_ajaran : null;

        $result = $this->plotService->simpan(
            (int) $request->kelas_id,
            (int) $request->pelajaran_id,
            (int) ($request->beban_jam ?? 0),
            $request->guru_id ? (int) $request->guru_id : null,
            $request->input('force_update') == 'true',
            $tahunAjaran
        );

        return response()->json($result);
    }

    // ==========================================================
    // FUNGSI: Menampilkan Form Mutasi Massal dari Master Plot
    // ==========================================================
    public function formMutasi($id)
    {
        $plot = PlotJadwal::with(['kelas', 'pelajaran', 'guru'])->findOrFail($id);

        // Ambil guru yang berstatus Aktif, kecuali guru yang saat ini menjabat
        $semuaGuru = $this->plotService->daftarGuruPengganti($plot->guru_id);

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

        $plot = $this->schedule->mutasiGuruPlot($id, (int) $request->guru_baru_id);

        // Arahkan kembali ke halaman Master Plotting beserta parameter kelas_id-nya
        return redirect('/master-jadwal-harian?kelas_id=' . $plot->kelas_id)
                ->with('sukses', 'Mutasi Berhasil! Jadwal lama telah diarsipkan (Soft Delete). Silakan atur ulang Hari/Jam secara manual untuk guru baru.');
    }
}
