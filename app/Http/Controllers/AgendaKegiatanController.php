<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AgendaKegiatan;
use App\Models\Periode;
use App\Models\Guru;
use App\Models\KehadiranKegiatan;

class AgendaKegiatanController extends Controller
{
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
        
        $semuaGuru = \App\Models\Guru::orderBy('nama_guru', 'asc')->get();
        $kehadiran = \App\Models\KehadiranKegiatan::where('agenda_kegiatan_id', $id)->get()->keyBy('guru_id');

        $dataHadir = [];
        $dataBelumHadir = [];

        foreach ($semuaGuru as $guru) {
            if ($kehadiran->has($guru->id)) {
                $dataHadir[] = (object)[
                    'guru' => $guru,
                    'waktu_hadir' => $kehadiran[$guru->id]->waktu_hadir,
                    'metode' => $kehadiran[$guru->id]->metode,
                    'status' => $kehadiran[$guru->id]->status ?? 'Hadir',
                    'keterangan' => $kehadiran[$guru->id]->keterangan
                ];
            } else {
                $dataBelumHadir[] = $guru;
            }
        }

        usort($dataHadir, function($a, $b) {
            return strtotime($a->waktu_hadir) - strtotime($b->waktu_hadir);
        });

        $totalGuru = $semuaGuru->count();
        // PERUBAHAN DI SINI: $totalHadir mencakup semua yang sudah masuk data tercatat (Hadir/Izin/Sakit)
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
        
        \App\Models\KehadiranKegiatan::updateOrCreate(
            ['agenda_kegiatan_id' => $agenda_id, 'guru_id' => $request->guru_id],
            [
                'waktu_hadir' => now(), 
                'metode' => 'Manual Admin',
                'status' => $request->status,
                'keterangan' => $request->keterangan
            ]
        );

        return back()->with('sukses', 'Status ' . $request->status . ' berhasil dicatat!');
    }

    // ==========================================================
    // 3. FUNGSI: Cetak PDF Laporan Kegiatan
    // ==========================================================
    public function cetakPdf($id)
    {
        $agenda = AgendaKegiatan::findOrFail($id);
        $semuaGuru = \App\Models\Guru::orderBy('nama_guru', 'asc')->get();
        $kehadiran = \App\Models\KehadiranKegiatan::where('agenda_kegiatan_id', $id)->get()->keyBy('guru_id');

        $dataHadir = [];
        $dataBelumHadir = [];

        foreach ($semuaGuru as $guru) {
            if ($kehadiran->has($guru->id)) {
                $dataHadir[] = (object)[
                    'guru' => $guru,
                    'waktu_hadir' => $kehadiran[$guru->id]->waktu_hadir,
                    'metode' => $kehadiran[$guru->id]->metode,
                    'status' => $kehadiran[$guru->id]->status ?? 'Hadir', // PERBAIKAN: Suntikan Status
                    'keterangan' => $kehadiran[$guru->id]->keterangan // PERBAIKAN: Suntikan Keterangan
                ];
            } else {
                $dataBelumHadir[] = $guru;
            }
        }

        usort($dataHadir, function($a, $b) {
            return strtotime($a->waktu_hadir) - strtotime($b->waktu_hadir);
        });

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.agenda-pdf', compact('agenda', 'dataHadir', 'dataBelumHadir'));
        
        return $pdf->download('Laporan_Acara_'.$agenda->tanggal.'_'.str_replace(' ', '_', $agenda->nama_kegiatan).'.pdf');
    }

    // ==========================================================
    // API: Mengirim Data Kehadiran Real-time untuk Halaman Laporan
    // ==========================================================
    public function getKehadiranRealtime($id)
    {
        $semuaGuru = \App\Models\Guru::orderBy('nama_guru', 'asc')->get();
        $kehadiran = \App\Models\KehadiranKegiatan::with('guru')->where('agenda_kegiatan_id', $id)->get()->keyBy('guru_id');

        $dataHadir = [];
        $dataBelumHadir = [];

        foreach ($semuaGuru as $guru) {
            if ($kehadiran->has($guru->id)) {
                $dataHadir[] = [
                    'nama_guru' => $guru->nama_guru,
                    'waktu' => \Carbon\Carbon::parse($kehadiran[$guru->id]->waktu_hadir)->format('H:i:s'),
                    'metode' => $kehadiran[$guru->id]->metode,
                    'status' => $kehadiran[$guru->id]->status ?? 'Hadir',
                    'keterangan' => $kehadiran[$guru->id]->keterangan
                ];
            } else {
                $dataBelumHadir[] = [
                    'id' => $guru->id,
                    'nama_guru' => $guru->nama_guru
                ];
            }
        }

        // Urutkan yang hadir berdasarkan waktu
        usort($dataHadir, function($a, $b) {
            return strtotime($a['waktu']) - strtotime($b['waktu']);
        });

        return response()->json([
            'total_hadir' => count($dataHadir),
            'total_belum' => count($dataBelumHadir),
            'persentase' => count($semuaGuru) > 0 ? round((count($dataHadir) / $semuaGuru->count()) * 100) : 0,
            'data_hadir' => $dataHadir,
            'data_belum' => $dataBelumHadir
        ]);
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
            $agenda = AgendaKegiatan::findOrFail($id);

            if (!$agenda->is_open) {
                return response()->json(['status' => 'error', 'pesan' => 'Absensi untuk kegiatan ini sudah ditutup oleh TU.']);
            }

            $qr_data = $request->qr_data;

            // QR guru berformat GURU-<NIG>
            if (strpos($qr_data, 'GURU-') !== 0) {
                return response()->json(['status' => 'error', 'pesan' => 'QR tidak dikenali! Pindai QR Code pribadi guru.']);
            }

            $nig = substr($qr_data, 5);
            if ($nig === '') {
                return response()->json(['status' => 'error', 'pesan' => 'QR guru tidak valid (NIG kosong).']);
            }

            $guru = Guru::where('nig', $nig)->first();
            if (!$guru) {
                return response()->json(['status' => 'error', 'pesan' => 'Guru dengan NIG ' . $nig . ' tidak ditemukan.']);
            }

            // Catat kehadiran kegiatan (anti double-scan)
            $kehadiran = KehadiranKegiatan::firstOrNew([
                'agenda_kegiatan_id' => $agenda->id,
                'guru_id' => $guru->id,
            ]);

            if ($kehadiran->exists) {
                return response()->json([
                    'status' => 'info',
                    'pesan' => $guru->nama_guru . ' sudah tercatat hadir sebelumnya.'
                ]);
            }

            $kehadiran->waktu_hadir = now();
            $kehadiran->metode = 'Scan QR Guru';
            $kehadiran->status = 'Hadir';
            $kehadiran->keterangan = null;
            $kehadiran->save();

            return response()->json([
                'status' => 'success',
                'pesan' => $guru->nama_guru . ' (NIG ' . $guru->nig . ') tercatat hadir di ' . $agenda->nama_kegiatan . '!'
            ]);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'pesan' => 'Kesalahan sistem: ' . $e->getMessage()]);
        }
    }
}