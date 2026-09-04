<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\KehadiranGuru;
use App\Services\Kehadiran\KehadiranSlotService;

class MonitoringKehadiranController extends Controller
{
    public function __construct(
        protected KehadiranSlotService $kehadiran
    ) {}

    public function index(Request $request)
    {
        $tglMulai   = $request->input('tgl_mulai');
        $tglSelesai = $request->input('tgl_selesai');

        // Isi sisi tanggal yang kosong satu per satu (jangan set ulang keduanya).
        $rentang = $this->kehadiran->normalisasiRentang($tglMulai, $tglSelesai);
        $tglMulai = $rentang['tgl_mulai'];
        $tglSelesai = $rentang['tgl_selesai'];

        $cari    = $request->input('cari', '');
        $kelasId = $request->input('kelas', '');
        $statusF = $request->input('status', '');

        $periodeAktif = get_periode_aktif();
        $periodeId    = $periodeAktif ? $periodeAktif->id : null;

        $data = $this->kehadiran->bangunSlotIndex($tglMulai, $tglSelesai, $cari, $kelasId, $statusF, $periodeId);
        $slots = $data['slots'];
        $total = $data['total'];

        $daftarKelas = Kelas::orderBy('nama_kelas')->get();
        $daftarStatus = KehadiranSlotService::DAFTAR_STATUS;

        return view('monitoring-kehadiran', compact(
            'slots', 'total', 'daftarKelas', 'daftarStatus',
            'tglMulai', 'tglSelesai', 'cari', 'kelasId', 'statusF'
        ));
    }

    // AJAX: daftar slot (kehadiran + belum diisi) untuk satu guru pada rentang tanggal
    public function detailGuru(Request $request)
    {
        $guruId    = $request->input('guru_id');
        $tglMulai  = $request->input('tgl_mulai');
        $tglSelesai = $request->input('tgl_selesai');

        $guru = Guru::find($guruId);
        if (!$guru) {
            return response()->json(['status' => 'error', 'pesan' => 'Guru tidak ditemukan.']);
        }

        $periodeAktif = get_periode_aktif();
        $periodeId    = $periodeAktif ? $periodeAktif->id : null;

        $slots = $this->kehadiran->bangunSlotGuru($guru->id, $tglMulai, $tglSelesai, $periodeId);

        return response()->json(['status' => 'success', 'guru' => $guru->nama_guru, 'slots' => $slots]);
    }

    // Simpan / buat record kehadiran (updateOrCreate per jadwal_id + tanggal)
    public function update(Request $request)
    {
        $request->validate([
            'jadwal_id'    => 'required|integer',
            'tanggal'      => 'required|date',
            'status'       => 'required|string',
            'keterangan'   => 'nullable|string',
            'nig_pengganti'=> 'nullable|string',
        ]);

        if (!in_array($request->status, ['Hadir', 'Izin', 'Sakit', 'Alpa', 'Menunggu'])) {
            return response()->json(['status' => 'error', 'pesan' => 'Status tidak valid.'], 422);
        }

        $periodeAktif = get_periode_aktif();

        // 'Alpa' tersimpan sebagai 'Alpa' (Kosong lama ditampilkan sebagai Alpa)
        $statusTersimpan = $request->status;

        KehadiranGuru::updateOrCreate(
            [
                'jadwal_id' => $request->jadwal_id,
                'tanggal'   => $request->tanggal,
            ],
            [
                'status'        => $statusTersimpan,
                'keterangan'    => $request->keterangan ?: null,
                'nig_pengganti' => $request->nig_pengganti ?: null,
                'periode_id'    => $periodeAktif ? $periodeAktif->id : null,
            ]
        );

        return response()->json(['status' => 'success', 'pesan' => 'Status kehadiran berhasil disimpan!']);
    }
}
