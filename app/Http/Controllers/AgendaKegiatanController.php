<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AgendaKegiatan;
use App\Models\Periode;
use App\Services\Kehadiran\AgendaKehadiranService;

class AgendaKegiatanController extends Controller
{
    public function __construct(
        protected AgendaKehadiranService $kehadiran
    ) {}

    public function index()
    {
        $periodeAktif = get_periode_aktif();
        if (!$periodeAktif) {
            return redirect()->back()->with('error', 'Tidak ada periode aktif.');
        }

        // Menampilkan agenda dari yang paling baru
        $agendas = AgendaKegiatan::where('periode_id', $periodeAktif->id)
                    ->orderBy('tanggal', 'desc')
                    ->orderBy('jam_mulai', 'desc')
                    ->get();

        return view('admin.agenda-index', compact('agendas', 'periodeAktif'));
    }

    public function store(Request $request)
    {
        $periodeAktif = Periode::where('is_active', true)->firstOrFail();

        $request->validate([
            'nama_kegiatan' => 'required|string|max:255',
            'tanggal'       => 'required|date',
            'jam_mulai'     => 'required|date_format:H:i',
            'jam_selesai'   => 'nullable|date_format:H:i',
            'lokasi'        => 'nullable|string|max:255',
        ]);

        AgendaKegiatan::create([
            'periode_id'    => $periodeAktif->id,
            'nama_kegiatan' => $request->nama_kegiatan,
            'tanggal'       => $request->tanggal,
            'jam_mulai'     => $request->jam_mulai,
            'jam_selesai'   => $request->jam_selesai,
            'lokasi'        => $request->lokasi,
        ]);

        return redirect()->back()->with('sukses', 'Agenda kegiatan berhasil dibuat! Sistem otomatis membuat QR Code unik.');
    }

    public function destroy($id)
    {
        $agenda = AgendaKegiatan::findOrFail($id);

        // Hapus data kehadiran terkait terlebih dahulu, lalu agenda (aman walau tanpa cascade)
        $agenda->kehadiran()->delete();
        $agenda->delete();

        return redirect()->back()->with('sukses', 'Agenda "' . $agenda->nama_kegiatan . '" berhasil dihapus.');
    }

    // Fungsi khusus untuk menampilkan QR Code penuh di layar proyektor/TV
    public function proyektor($id)
    {
        $agenda = AgendaKegiatan::findOrFail($id);
        return view('admin.agenda-proyektor', compact('agenda'));
    }

    // ==========================================================
    // 1. FUNGSI: Menampilkan Laporan Lengkap (Hadir & Belum)
    // ==========================================================
    public function laporan($id)
    {
        $agenda = AgendaKegiatan::findOrFail($id);
        $data = $this->kehadiran->rangkumUntukView($agenda->id);

        $dataHadir = $data['data_hadir'];
        $dataBelumHadir = $data['data_belum_hadir'];
        $totalGuru = $data['total_guru'];

        // $totalHadir mencakup semua yang sudah masuk data tercatat (Hadir/Izin/Sakit)
        $totalHadir = count($dataHadir);
        $belumHadir = count($dataBelumHadir);
        $persentase = $totalGuru > 0 ? round(($totalHadir / $totalGuru) * 100) : 0;

        return view('admin.agenda-laporan', compact('agenda', 'dataHadir', 'dataBelumHadir', 'totalGuru', 'totalHadir', 'belumHadir', 'persentase'));
    }

    // ==========================================================
    // 2. FUNGSI: Input Kehadiran/Izin Manual oleh Admin/TU
    // ==========================================================
    public function hadirManual(Request $request, $agenda_id)
    {
        $request->validate([
            'guru_id' => 'required|exists:gurus,id',
            'status' => 'required|in:Hadir,Izin,Sakit',
            'keterangan' => 'nullable|string|max:255'
        ]);

        $this->kehadiran->catatManual($agenda_id, $request->guru_id, $request->status, $request->keterangan);

        return back()->with('sukses', 'Status ' . $request->status . ' berhasil dicatat!');
    }

    // ==========================================================
    // 3. FUNGSI: Cetak PDF Laporan Kegiatan
    // ==========================================================
    public function cetakPdf($id)
    {
        $agenda = AgendaKegiatan::findOrFail($id);
        $data = $this->kehadiran->rangkumUntukView($agenda->id);
        $dataHadir = $data['data_hadir'];
        $dataBelumHadir = $data['data_belum_hadir'];

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.agenda-pdf', compact('agenda', 'dataHadir', 'dataBelumHadir'));

        return $pdf->download('Laporan_Acara_' . $agenda->tanggal . '_' . str_replace(' ', '_', $agenda->nama_kegiatan) . '.pdf');
    }

    // ==========================================================
    // API: Mengirim Data Kehadiran Real-time untuk Halaman Laporan
    // ==========================================================
    public function getKehadiranRealtime($id)
    {
        return response()->json($this->kehadiran->rangkumUntukApi($id));
    }

    // ==========================================================
    // 4. FUNGSI: Halaman Kamera TU untuk Memindai QR Guru
    //    (Guru tanpa internet -> TU pindai QR pribadi guru)
    // ==========================================================
    public function scanQR($id)
    {
        $agenda = AgendaKegiatan::findOrFail($id);
        return view('admin.agenda-scan-guru', compact('agenda'));
    }

    // ==========================================================
    // 5. FUNGSI: Memproses Hasil Pindai QR Guru (GURU-<NIG>)
    // ==========================================================
    public function prosesScanQR(Request $request, $id)
    {
        try {
            $result = $this->kehadiran->prosesScanQr($request->qr_data, $id);
            return response()->json($result);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Scan QR agenda gagal: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'pesan' => 'Terjadi kesalahan sistem saat memproses kehadiran.']);
        }
    }
}
