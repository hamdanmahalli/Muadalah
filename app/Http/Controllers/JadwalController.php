<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\JadwalHarian;
use App\Models\KehadiranGuru;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Pelajaran;
use App\Models\MasterJam;
use Carbon\Carbon;

class JadwalController extends Controller
{
    // ========================================================
    // 1. DASHBOARD UTAMA
    // ========================================================
    public function dashboard()
    {
        $waktuSekarang = Carbon::now();
        $tanggalHariIni = $waktuSekarang->format('Y-m-d');
        
        $namaHariInggris = $waktuSekarang->format('l');
        $daftarHari = [
            'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu', 'Sunday' => 'Minggu'
        ];
        $hariIni = $daftarHari[$namaHariInggris];

        // Murni menghitung dari tabel JadwalHarian baru
        $totalJadwal = JadwalHarian::where('hari', 'ilike', $hariIni)->count();

        $kehadiranHariIni = KehadiranGuru::where('tanggal', $tanggalHariIni)->get();
        $guruHadir = $kehadiranHariIni->where('status', 'Hadir')->count();
        $guruIzinKosong = $kehadiranHariIni->whereIn('status', ['Izin', 'Kosong', 'Alpha'])->count();

        return view('dashboard', compact('totalJadwal', 'guruHadir', 'guruIzinKosong', 'waktuSekarang'));
    }

    // ========================================================
    // 2. MEJA KONTROL (SISTEM BLOK JAM OTOMATIS)
    // ========================================================
    public function mejaKontrol(Request $request)
    {
        $waktuSekarang = Carbon::now();
        $tanggalHariIni = $waktuSekarang->format('Y-m-d');
        $jamSekarang = $waktuSekarang->format('H:i:s'); 
        
        $namaHariInggris = $waktuSekarang->format('l');
        $daftarHari = [
            'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu', 'Sunday' => 'Minggu'
        ];
        $hariIni = $daftarHari[$namaHariInggris];

        $semuaJam = MasterJam::orderBy('jam_ke', 'asc')->get();
        
        $opsiBlokJam = [];
        $blokAktifOtomatis = null; 

        for ($i = 0; $i < count($semuaJam); $i += 2) {
            $jam1 = $semuaJam[$i];
            $jam2 = $semuaJam[$i + 1] ?? $jam1; 
            
            $keyBlok = $jam1->jam_ke . '-' . $jam2->jam_ke;
            if ($jam1->jam_ke == $jam2->jam_ke) $keyBlok = (string)$jam1->jam_ke;

            $waktu = Carbon::parse($jam1->jam_mulai)->format('H:i') . ' - ' . Carbon::parse($jam2->jam_selesai)->format('H:i');
            $opsiBlokJam[] = ['nilai' => $keyBlok, 'label' => "Jam Ke-$keyBlok ($waktu)"];

            if ($jamSekarang >= $jam1->jam_mulai && $jamSekarang <= $jam2->jam_selesai) {
                $blokAktifOtomatis = $keyBlok;
            }
        }

        $jamDefault = $blokAktifOtomatis ?? ($opsiBlokJam[0]['nilai'] ?? '1-2');
        $jamPilihan = $request->input('jam', $jamDefault);
        $arrayJamPilihan = explode('-', $jamPilihan);

        // Murni mengambil data dari tabel JadwalHarian beserta relasinya
        $jadwalsMentah = JadwalHarian::with(['kelas', 'pelajaran', 'guru'])
                         ->where('hari', 'ilike', $hariIni)
                         ->whereIn('jam_ke', $arrayJamPilihan)
                         ->get();

        $jadwals = [];
        foreach ($jadwalsMentah as $j) {
            $kunci = ($j->kelas_id ?? '0') . '_' . ($j->guru_id ?? '0') . '_' . ($j->pelajaran_id ?? '0'); 
            
            if (!isset($jadwals[$kunci])) {
                $jadwals[$kunci] = [
                    'id_list' => [], 
                    'kelas' => $j->kelas->nama_kelas ?? 'Kelas -',
                    'mata_pelajaran' => $j->pelajaran->nama_pelajaran ?? 'Tanpa Pelajaran',
                    'nig_guru' => $j->guru->nig ?? '-',
                    'nama_guru' => $j->guru->nama_guru ?? 'Belum ada guru',
                    'jam_tampil' => $jamPilihan
                ];
            }
            $jadwals[$kunci]['id_list'][] = $j->id;
        }

        $infoJamLengkap = collect($opsiBlokJam)->firstWhere('nilai', $jamPilihan);
        $infoJam = $infoJamLengkap ? $infoJamLengkap['label'] : "Jam Ke-" . $jamPilihan;

        $daftarGuru = Guru::all();
        $kehadiranHariIni = KehadiranGuru::where('tanggal', $tanggalHariIni)->get()->keyBy('jadwal_id');

        return view('meja-kontrol', compact('jadwals', 'kehadiranHariIni', 'infoJam', 'daftarGuru', 'opsiBlokJam', 'jamPilihan', 'hariIni'));
    }

    // ========================================================
    // 3. SIMPAN KEHADIRAN (AJAX)
    // ========================================================
    public function simpanKehadiran(Request $request)
    {
        $request->validate([
            'jadwal_ids' => 'required|array', 
            'status' => 'required|string'
        ]);

        foreach ($request->jadwal_ids as $id) {
            KehadiranGuru::updateOrCreate(
                [
                    'jadwal_id' => $id,
                    'tanggal' => Carbon::now()->format('Y-m-d')
                ],
                [
                    'status' => $request->status,
                    'nig_pengganti' => $request->nig_pengganti ?? null,
                    'keterangan' => $request->keterangan ?? null,
                ]
            );
        }
        return response()->json(['pesan' => 'Status blok berhasil dikunci!']);
    }

    // ========================================================
    // 4. LAPORAN & CETAK PDF
    // ========================================================
    public function laporanKehadiran() 
    {
        $waktuSekarang = Carbon::now();
        $tanggalHariIni = $waktuSekarang->format('Y-m-d');
        
        $daftarHari = [
            'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu', 'Sunday' => 'Minggu'
        ];
        $hariIni = $daftarHari[$waktuSekarang->format('l')];

        // Tarik laporan khusus untuk hari ini dari JadwalHarian
        $jadwals = JadwalHarian::with(['kelas', 'pelajaran', 'guru'])
                    ->where('hari', 'ilike', $hariIni)
                    ->orderBy('kelas_id')
                    ->orderBy('jam_ke')
                    ->get(); 

        $kehadiranHariIni = KehadiranGuru::where('tanggal', $tanggalHariIni)->get()->keyBy('jadwal_id');
        
        return view('laporan', compact('jadwals', 'kehadiranHariIni', 'tanggalHariIni'));
    }

    public function cetakPdf() 
    {
        $waktuSekarang = Carbon::now();
        $tanggalHariIni = $waktuSekarang->format('Y-m-d');
        
        $daftarHari = [
            'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu', 'Sunday' => 'Minggu'
        ];
        $hariIni = $daftarHari[$waktuSekarang->format('l')];

        $jadwals = JadwalHarian::with(['kelas', 'pelajaran', 'guru'])
                    ->where('hari', 'ilike', $hariIni)
                    ->orderBy('kelas_id')
                    ->orderBy('jam_ke')
                    ->get();

        $kehadiranHariIni = KehadiranGuru::where('tanggal', $tanggalHariIni)->get()->keyBy('jadwal_id');
        $pdf = Pdf::loadView('laporan', compact('jadwals', 'kehadiranHariIni', 'tanggalHariIni'));
        
        return $pdf->download('Rekap_Kehadiran_'.$tanggalHariIni.'.pdf');
    }
}