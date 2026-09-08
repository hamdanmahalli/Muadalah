<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\HonorKonfigurasi;
use App\Models\HonorGuruConfig;
use App\Models\HonorStrukturalConfig;
use App\Models\HonorPeriode;
use App\Models\HonorDetail;
use App\Models\Guru;
use App\Models\Jabatan;
use App\Models\Periode;
use App\Services\Honor\HonorService;

class HonorController extends Controller
{
    public function __construct(
        protected HonorService $service
    ) {}

    public function index()
    {
        $periodeAktif = get_periode_aktif();
        if (!$periodeAktif) {
            return redirect()->back()->with('error', 'Tidak ada periode aktif.');
        }

        $honorPeriodeList = HonorPeriode::with(['konfigurasi', 'details' => function ($q) {
            $q->with('guru');
        }])
            ->whereHas('konfigurasi', fn($q) => $q->where('periode_id', $periodeAktif->id))
            ->orderBy('tahun', 'desc')
            ->orderBy('bulan', 'desc')
            ->get();

        $bulanIndonesia = $this->bulanIndonesia();

        return view('admin.honor-index', compact('honorPeriodeList', 'periodeAktif', 'bulanIndonesia'));
    }

    public function konfigurasi(Request $request)
    {
        $periodeAktif = get_periode_aktif();
        if (!$periodeAktif) {
            return redirect()->back()->with('error', 'Tidak ada periode aktif.');
        }

        $bulan  = (int) ($request->query('bulan') ?? now()->month);
        $tahun  = (int) ($request->query('tahun') ?? now()->year);

        $config = HonorKonfigurasi::with(['guruConfigs', 'strukturalConfigs'])
            ->where('periode_id', $periodeAktif->id)
            ->where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->first();

        $gurus = Guru::orderBy('nama_guru')->get();
        $jabatans = Jabatan::orderBy('id')->get();

        $guruConfigsExist = $config?->guruConfigs->keyBy('guru_id') ?? collect();
        $strukturalExist = $config?->strukturalConfigs->keyBy('jabatan_id') ?? collect();

        $lastConfig = HonorKonfigurasi::with(['guruConfigs', 'strukturalConfigs'])
            ->where('periode_id', $periodeAktif->id)
            ->where('id', '!=', $config?->id ?? 0)
            ->orderBy('tahun', 'desc')
            ->orderBy('bulan', 'desc')
            ->first();

        $bulanIndonesia = $this->bulanIndonesia();

        return view('admin.honor-konfigurasi', compact(
            'periodeAktif', 'bulan', 'tahun', 'config',
            'gurus', 'jabatans', 'guruConfigsExist', 'strukturalExist', 'lastConfig', 'bulanIndonesia'
        ));
    }

    public function simpanKonfigurasi(Request $request)
    {
        $periodeAktif = get_periode_aktif();
        if (!$periodeAktif) {
            return redirect()->back()->with('error', 'Tidak ada periode aktif.');
        }

        $validated = $request->validate([
            'bulan'            => 'required|integer|between:1,12',
            'tahun'            => 'required|integer|min:2000|max:2100',
            'tarif_jam_normal' => 'required|integer|min:0',
            'tarif_pengabdian' => 'required|integer|min:0',
            'tarif_piket'      => 'required|integer|min:0',
            'tarif_piket_pengabdian' => 'required|integer|min:0',
            'tarif_transport'  => 'required|integer|min:0',
            'tarif_wali_kelas' => 'required|integer|min:0',
            'catatan'          => 'nullable|string',
            'guru_status.*'    => 'nullable|in:Tetap,Pengabdian',
            'guru_tarif.*'     => 'nullable|integer|min:0',
            'jabatan_nominal.*'=> 'nullable|integer|min:0',
        ]);

        $config = HonorKonfigurasi::updateOrCreate(
            [
                'periode_id' => $periodeAktif->id,
                'bulan'      => $validated['bulan'],
                'tahun'      => $validated['tahun'],
            ],
            [
                'tarif_jam_normal' => $validated['tarif_jam_normal'],
                'tarif_pengabdian' => $validated['tarif_pengabdian'],
                'tarif_piket'      => $validated['tarif_piket'],
                'tarif_piket_pengabdian' => $validated['tarif_piket_pengabdian'],
                'tarif_transport'  => $validated['tarif_transport'],
                'tarif_wali_kelas' => $validated['tarif_wali_kelas'],
                'catatan'          => $validated['catatan'] ?? null,
            ]
        );

        // Status per guru
        foreach (($request->guru_status ?? []) as $guruId => $status) {
            if (!$status) continue;
            HonorGuruConfig::updateOrCreate(
                [
                    'honor_konfigurasi_id' => $config->id,
                    'guru_id'              => (int) $guruId,
                ],
                [
                    'status_honor'   => $status,
                    'dari_luar'      => false,
                    'tarif_override' => !empty($request->guru_tarif[$guruId]) ? (int) $request->guru_tarif[$guruId] : null,
                ]
            );
        }

        // Nominal jabatan struktural
        HonorStrukturalConfig::where('honor_konfigurasi_id', $config->id)->delete();
        foreach (($request->jabatan_nominal ?? []) as $jabatanId => $nominal) {
            if (!empty($nominal) || $nominal === '0') {
                HonorStrukturalConfig::create([
                    'honor_konfigurasi_id' => $config->id,
                    'jabatan_id'           => (int) $jabatanId,
                    'nominal'              => (int) $nominal,
                ]);
            }
        }

        return redirect()->route('honor.konfigurasi', [
            'bulan' => $validated['bulan'],
            'tahun' => $validated['tahun'],
        ])->with('sukses', 'Konfigurasi honor berhasil disimpan!');
    }

    public function salinKonfigurasi(Request $request)
    {
        $periodeAktif = get_periode_aktif();
        if (!$periodeAktif) {
            return redirect()->back()->with('error', 'Tidak ada periode aktif.');
        }

        $validated = $request->validate([
            'bulan' => 'required|integer|between:1,12',
            'tahun' => 'required|integer|min:2000|max:2100',
        ]);

        $target = HonorKonfigurasi::where('periode_id', $periodeAktif->id)
            ->where('bulan', $validated['bulan'])
            ->where('tahun', $validated['tahun'])
            ->first();

        if ($target) {
            return redirect()->route('honor.konfigurasi', [
                'bulan' => $validated['bulan'],
                'tahun' => $validated['tahun'],
            ])->with('error', 'Periode ini sudah punya konfigurasi. Salin hanya untuk periode yang masih kosong.');
        }

        $source = HonorKonfigurasi::with(['guruConfigs', 'strukturalConfigs'])
            ->where('periode_id', $periodeAktif->id)
            ->orderBy('tahun', 'desc')
            ->orderBy('bulan', 'desc')
            ->first();

        if (!$source) {
            return redirect()->route('honor.konfigurasi', [
                'bulan' => $validated['bulan'],
                'tahun' => $validated['tahun'],
            ])->with('error', 'Belum ada konfigurasi bulan sebelumnya untuk disalin.');
        }

        $config = HonorKonfigurasi::create([
            'periode_id'        => $periodeAktif->id,
            'bulan'             => $validated['bulan'],
            'tahun'             => $validated['tahun'],
            'tarif_jam_normal'  => $source->tarif_jam_normal,
            'tarif_pengabdian'  => $source->tarif_pengabdian,
            'tarif_piket'       => $source->tarif_piket,
            'tarif_piket_pengabdian' => $source->tarif_piket_pengabdian,
            'tarif_transport'   => $source->tarif_transport,
            'tarif_wali_kelas'  => $source->tarif_wali_kelas,
            'catatan'           => $source->catatan,
        ]);

        foreach ($source->guruConfigs as $gc) {
            HonorGuruConfig::create([
                'honor_konfigurasi_id' => $config->id,
                'guru_id'              => $gc->guru_id,
                'status_honor'         => $gc->status_honor,
                'dari_luar'            => $gc->dari_luar,
                'tarif_override'       => $gc->tarif_override,
            ]);
        }

        foreach ($source->strukturalConfigs as $sc) {
            HonorStrukturalConfig::create([
                'honor_konfigurasi_id' => $config->id,
                'jabatan_id'           => $sc->jabatan_id,
                'nominal'              => $sc->nominal,
            ]);
        }

        $bulanNama = $this->bulanIndonesia()[$validated['bulan']] ?? $validated['bulan'];

        return redirect()->route('honor.konfigurasi', [
            'bulan' => $validated['bulan'],
            'tahun' => $validated['tahun'],
        ])->with('sukses', 'Konfigurasi disalin dari bulan ' . $bulanNama . ' ' . $source->tahun . ' ke ' . $bulanNama . ' ' . $validated['tahun'] . '! Silakan periksa lalu sesuaikan bila perlu.');
    }

    public function hitung(Request $request)
    {
        $periodeAktif = get_periode_aktif();
        if (!$periodeAktif) {
            return redirect()->back()->with('error', 'Tidak ada periode aktif.');
        }

        $request->validate([
            'bulan' => 'required|integer|between:1,12',
            'tahun' => 'required|integer|min:2000|max:2100',
        ]);

        try {
            $periodeHonor = $this->service->hitung($request->bulan, $request->tahun, $periodeAktif);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->route('honor.rekap', $periodeHonor->id)->with('sukses', 'Honor berhasil dihitung!');
    }

    public function rekap($id)
    {
        $periodeHonor = HonorPeriode::with(['konfigurasi', 'details' => function ($q) {
            $q->with('guru');
        }])->findOrFail($id);

        $bulanIndonesia = $this->bulanIndonesia();

        return view('admin.honor-rekap', compact('periodeHonor', 'bulanIndonesia'));
    }

    public function finalisasi($id)
    {
        $periodeHonor = HonorPeriode::findOrFail($id);

        if ($periodeHonor->details()->count() === 0) {
            return redirect()->back()->with('error', 'Tidak bisa difinalkan: belum ada data honor untuk periode ini. Hitung dulu rekapnya.');
        }

        $periodeHonor->update(['status' => 'final']);

        return redirect()->back()->with('sukses', 'Honor periode ini sudah difinalkan. Barcode penerimaan kini aktif.');
    }

    public function buka($id)
    {
        $periodeHonor = HonorPeriode::findOrFail($id);

        if ($periodeHonor->status !== 'final') {
            return redirect()->back()->with('error', 'Hanya periode berstatus Final yang bisa dibuka kembali.');
        }

        $periodeHonor->update(['status' => 'draft']);

        return redirect()->back()->with('sukses', 'Periode dibuka kembali menjadi Draft. Hitung ulang bila perlu, lalu finalkan lagi.');
    }

    public function scanPenerimaan()
    {
        $periodeAktif = get_periode_aktif();
        if (!$periodeAktif) {
            return redirect()->back()->with('error', 'Tidak ada periode aktif.');
        }

        $periodeHonor = HonorPeriode::with('details.guru')
            ->whereHas('konfigurasi', fn($q) => $q->where('periode_id', $periodeAktif->id))
            ->where('status', 'final')
            ->orderBy('tahun', 'desc')
            ->orderBy('bulan', 'desc')
            ->first();

        $bulanIndonesia = $this->bulanIndonesia();

        return view('admin.honor-scan', compact('periodeHonor', 'bulanIndonesia'));
    }

    public function prosesScan(Request $request)
    {
        $request->validate([
            'qr_data' => 'required|string',
        ]);

        if (!str_starts_with($request->qr_data, 'HONOR-')) {
            return response()->json(['success' => false, 'pesan' => 'QR bukan token honor guru.'], 422);
        }

        try {
            $detail = $this->service->prosesScan($request->qr_data, 'Scan QR');
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'pesan' => $e->getMessage()], 409);
        }

        if (!$detail) {
            return response()->json(['success' => false, 'pesan' => 'Token honor tidak ditemukan.'], 404);
        }

        if ($detail->is_diterima && $detail->waktu_diterima) {
            return response()->json([
                'success' => true,
                'pesan' => strtoupper($detail->guru->nama_guru) . ' — honor sudah diterima pada ' . $detail->waktu_diterima->format('d/m/Y H:i') . '.',
                'sudah_diterima_sebelumnya' => true,
            ]);
        }

        return response()->json([
            'success' => true,
            'pesan' => 'Honor ' . $detail->guru->nama_guru . ' tercatat telah diterima!',
            'nama_guru' => $detail->guru->nama_guru,
            'total' => $detail->total,
        ]);
    }

    private function bulanIndonesia()
    {
        return [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
            7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];
    }
}