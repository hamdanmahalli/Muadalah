<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\JadwalHarian;
use App\Models\Kelas;
use App\Models\Pelajaran;
use App\Models\Guru;

class JadwalHarianController extends Controller
{
    public function index(Request $request)
    {
        $kelas = Kelas::orderBy('nama_kelas', 'asc')->get();
        $kelas_id = $request->kelas_id;
        
        // Berdasarkan Excel Bapak, Hari efektif adalah Sabtu - Kamis (Jumat Libur)
        $hari_list = ['Sabtu', 'Ahad', 'Senin', 'Selasa', 'Rabu', 'Kamis'];
        $max_jam = 10; // Maksimal 10 Jam Pelajaran per hari
        
        $jadwal_matriks = [];
        $pelajarans = [];
        $gurus = [];

        if ($kelas_id) {
            $pelajarans = Pelajaran::orderBy('nama_pelajaran', 'asc')->get();
            $gurus = Guru::where('status', 'Aktif')->orderBy('nama_guru', 'asc')->get();
            
            // Mengambil jadwal dan menyusunnya ke dalam array matriks [hari][jam_ke]
            $data_jadwal = JadwalHarian::with(['pelajaran', 'guru'])
                            ->where('kelas_id', $kelas_id)->get();
            
            foreach ($data_jadwal as $j) {
                $jadwal_matriks[$j->hari][$j->jam_ke] = $j;
            }
        }

        return view('jadwal-harian', compact('kelas', 'kelas_id', 'hari_list', 'max_jam', 'jadwal_matriks', 'pelajarans', 'gurus'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kelas_id' => 'required',
            'hari' => 'required',
            'jam_ke' => 'required',
            'pelajaran_id' => 'required'
        ]);

        // ====================================================================
        // KECERDASAN 1: CEK BENTROK GURU DI KELAS LAIN
        // ====================================================================
        if ($request->guru_id) {
            $bentrok = JadwalHarian::with('kelas')
                ->where('hari', $request->hari)
                ->where('jam_ke', $request->jam_ke)
                ->where('guru_id', $request->guru_id)
                ->where('kelas_id', '!=', $request->kelas_id) // Cek di luar kelas ini
                ->first();

            if ($bentrok) {
                // Jika ketemu jadwal di tempat lain, tolak dan kembalikan pesan error
                return redirect()->back()->with('error', 'BENTROK! Guru tersebut sudah memiliki jadwal mengajar di kelas ' . $bentrok->kelas->nama_kelas . ' pada hari dan jam yang sama.');
            }
        }

        // ====================================================================
        // KECERDASAN 2: CEK BATAS MAKSIMAL BEBAN JAM + RINCIAN JADWALNYA
        // ====================================================================
        $plot = \App\Models\PlotJadwal::where('kelas_id', $request->kelas_id)
                    ->where('pelajaran_id', $request->pelajaran_id)
                    ->first();

        if ($plot) {
            $existing_slot = JadwalHarian::where('kelas_id', $request->kelas_id)
                                ->where('hari', $request->hari)
                                ->where('jam_ke', $request->jam_ke)
                                ->first();

            if (!$existing_slot || $existing_slot->pelajaran_id != $request->pelajaran_id) {
                
                // Ambil daftar rincian jadwal yang sudah terpakai sebelumnya
                $terpakaiList = JadwalHarian::where('kelas_id', $request->kelas_id)
                                    ->where('pelajaran_id', $request->pelajaran_id)
                                    ->orderBy('hari')
                                    ->orderBy('jam_ke')
                                    ->get();
                
                if ($terpakaiList->count() >= $plot->beban_jam) {
                    // Merangkum rincian hari dan jam secara otomatis (Sesuai usulan Bapak!)
                    $rincianStr = "";
                    foreach ($terpakaiList as $t) {
                        $rincianStr .= "• Hari {$t->hari} Jam Ke-{$t->jam_ke}<br>";
                    }

                    $pesanAlert = "KUOTA PENUH!<br>Pelajaran ini sudah mencapai batas maksimal target mengajar <b>({$plot->beban_jam} Jam)</b> di kelas ini.<br><br><span class='text-gray-600 text-xs'>Jadwal yang sudah terisi sebelumnya:</span><br>{$rincianStr}<br><i>Silakan ubah target di Master Plotting jika ingin menambah.</i>";

                    return redirect()->back()->with('error_popup', $pesanAlert);
                }
            }
        } else {
             return redirect()->back()->with('error_popup', 'DITOLAK!<br>Pelajaran ini belum ditambahkan ke Target Mengajar kelas ini. Silakan atur di Master Plotting terlebih dahulu.');
        }

        // ====================================================================
        // JIKA LOLOS SEMUA UJIAN DI ATAS, BARU DATA DISIMPAN KE BRANKAS
        // ====================================================================
        JadwalHarian::updateOrCreate(
            ['kelas_id' => $request->kelas_id, 'hari' => $request->hari, 'jam_ke' => $request->jam_ke],
            ['pelajaran_id' => $request->pelajaran_id, 'guru_id' => $request->guru_id]
        );

        return redirect()->back()->with('sukses', 'Jadwal berhasil disimpan!');
    }

    public function destroy($id)
    {
        JadwalHarian::destroy($id);
        return redirect()->back()->with('sukses', 'Jadwal berhasil dikosongkan!');
    }
}