<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\JadwalHarian;
use App\Models\Kelas;
use App\Models\Pelajaran;
use App\Models\Guru;
use App\Models\Periode;
use App\Models\MasterJam;
use Carbon\Carbon;

class JadwalHarianController extends Controller
{
    public function index(Request $request)
    {
        $kelas_id = $request->kelas_id;
        $guru_id = $request->guru_id;
        
        // KECERDASAN SISTEM: Tarik daftar hari aktif dan batas jamnya dari Pengaturan Operasional
        $urutanHari = ['Sabtu', 'Ahad', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
        $hariAktifDB = \App\Models\HariOperasional::where('is_active', true)->get();
        
        $hari_list = [];
        $max_jam_per_hari = []; // Wadah baru penyimpan batas jam

        if ($hariAktifDB->isEmpty()) {
            // Pengaman jika staf TU belum mengatur sama sekali
            $hari_list = ['Sabtu', 'Ahad', 'Senin', 'Selasa', 'Rabu', 'Kamis'];
            foreach($hari_list as $h) {
                $max_jam_per_hari[$h] = 10; // Default 10 Jam
            }
        } else {
            $hari_list = $hariAktifDB->sortBy(function($item) use ($urutanHari) {
                return array_search($item->hari, $urutanHari);
            })->pluck('hari')->toArray();
            
            // Mengambil batas jam untuk masing-masing hari (Misal: ['Jumat' => 6, 'Sabtu' => 10])
            $max_jam_per_hari = $hariAktifDB->pluck('max_jam', 'hari')->toArray();
        }
        $semuaJam = \App\Models\MasterJam::orderBy('jam_ke', 'asc')->get();
        $opsiBlokJam = [];

        if ($semuaJam->count() > 0) {
            for ($i = 0; $i < count($semuaJam); $i += 2) {
                $j1 = $semuaJam[$i];
                $j2 = $semuaJam[$i + 1] ?? $j1;
                $keyBlok = ($j1->jam_ke == $j2->jam_ke) ? (string)$j1->jam_ke : ($j1->jam_ke . '-' . $j2->jam_ke);
                $waktu = \Carbon\Carbon::parse($j1->jam_mulai)->format('H:i') . ' - ' . \Carbon\Carbon::parse($j2->jam_selesai)->format('H:i');
                $opsiBlokJam[] = ['key' => $keyBlok, 'jam_list' => ($j1->jam_ke == $j2->jam_ke) ? [$j1->jam_ke] : [$j1->jam_ke, $j2->jam_ke], 'label' => "Jam Ke-$keyBlok ($waktu)"];
            }
        } else {
            $defaultBlok = ['1-2' => [1, 2], '3-4' => [3, 4], '5-6' => [5, 6], '7-8' => [7, 8], '9-10' => [9, 10]];
            foreach ($defaultBlok as $key => $list) {
                $opsiBlokJam[] = ['key' => $key, 'jam_list' => $list, 'label' => "Jam Ke-$key"];
            }
        }

        $jadwal_matriks = [];
        $plotAktif = [];
        $mode = null;

        $periodeAktif = get_periode_aktif();
        $tahunAjaran = $periodeAktif ? $periodeAktif->tahun_ajaran : null;

        // Data Default untuk Pencarian Atas
        $kelas = \App\Models\Kelas::orderBy('nama_kelas', 'asc')->get();
        $gurus = \App\Models\Guru::where('status', 'Aktif')->orderBy('nama_guru', 'asc')->get();
        // HANYA MENAMPILKAN PELAJARAN AKTIF PADA DROPDOWN TAMBAH/EDIT JADWAL
        $pelajarans = Pelajaran::where('status', 'Aktif')->orderBy('nama_pelajaran', 'asc')->get();
        
        $kelas_popup = $kelas; // Wadah khusus untuk dropdown pop-up

        if ($kelas_id) {
            $mode = 'kelas';
            $plotAktif = \App\Models\PlotJadwal::where('kelas_id', $kelas_id)->get()->keyBy('pelajaran_id');
            
            $data_jadwal = JadwalHarian::with(['pelajaran', 'guru'])
                            ->where('kelas_id', $kelas_id)
                            ->where('tahun_ajaran', $tahunAjaran)
                            ->get();
            
            foreach ($opsiBlokJam as $blok) {
                foreach ($data_jadwal as $j) {
                    if (in_array($j->jam_ke, $blok['jam_list'])) {
                        $jadwal_matriks[$j->hari][$blok['key']] = $j;
                    }
                }
            }
        } 
        elseif ($guru_id) {
            $mode = 'guru';
            $data_jadwal = JadwalHarian::with(['pelajaran', 'kelas'])
                            ->where('guru_id', $guru_id)
                            ->where('tahun_ajaran', $tahunAjaran)
                            ->get();
            
            foreach ($opsiBlokJam as $blok) {
                foreach ($data_jadwal as $j) {
                    if (in_array($j->jam_ke, $blok['jam_list'])) {
                        $jadwal_matriks[$j->hari][$blok['key']] = $j;
                    }
                }
            }

            // KECERDASAN BARU: Tarik Plot Khusus Guru Ini untuk Filter Dropdown Pop-up
            $plotsGuru = \App\Models\PlotJadwal::with(['kelas', 'pelajaran'])
                             ->where('guru_id', $guru_id)
                             ->get();

            // Saring agar Dropdown Kelas hanya menampilkan kelas yang diajar Guru tersebut
            $kelas_popup = $plotsGuru->pluck('kelas')->filter()->unique('id')->sortBy('nama_kelas')->values();
            
            // Susun relasi Kelas -> Pelajaran agar Dropdown JS merespons otomatis
            $plotMap = [];
            foreach ($plotsGuru as $plot) {
                if ($plot->kelas && $plot->pelajaran) {
                    $plotMap[$plot->kelas_id][] = [
                        'id' => $plot->pelajaran->id,
                        'nama_pelajaran' => $plot->pelajaran->nama_pelajaran
                    ];
                }
            }
            $plotAktif = $plotMap;
        }

        return view('jadwal-harian', compact('kelas', 'kelas_popup', 'gurus', 'kelas_id', 'guru_id', 'mode', 'hari_list', 'max_jam_per_hari', 'opsiBlokJam', 'jadwal_matriks', 'pelajarans', 'plotAktif'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kelas_id' => 'required',
            'hari' => 'required',
            'jam_pilihan' => 'required',
            'pelajaran_id' => 'required'
        ]);

        $periodeAktif = get_periode_aktif();
        if(!$periodeAktif) return redirect()->back()->with('error_popup', 'DITOLAK!<br>Anda belum mengaktifkan Periode di Master Periode.');
        $tahunAjaran = $periodeAktif->tahun_ajaran;

        $arrayJam = explode('-', $request->jam_pilihan);
        $jumlahJamDiisi = count($arrayJam);
        
        $force_swap = $request->input('force_swap');
        $swap_target = $request->input('swap_target'); 
        $force_pindah_bentrok = $request->input('force_pindah_bentrok');
        $force_timpa = $request->input('force_timpa'); 

        // ====================================================================
        // KECERDASAN 1: CEK BENTROK GURU DI KELAS LAIN
        // ====================================================================
        if ($request->guru_id) {
            $bentroks = \App\Models\JadwalHarian::with('kelas')
                ->where('hari', $request->hari)
                ->whereIn('jam_ke', $arrayJam)
                ->where('tahun_ajaran', $tahunAjaran)
                ->where('guru_id', $request->guru_id)
                ->where('kelas_id', '!=', $request->kelas_id) 
                ->get();

            if ($bentroks->count() > 0) {
                if ($force_pindah_bentrok == 'true') {
                    foreach ($bentroks as $b) {
                        $b->delete();
                    }
                } else {
                    $rincianBentrok = "";
                    
                    // FITUR BARU: Mengelompokkan jam agar tampil sebagai Blok (Misal: 1-2)
                    $groupedBentroks = $bentroks->groupBy('kelas_id');
                    foreach ($groupedBentroks as $kelasId => $items) {
                        $namaKls = $items->first()->kelas->nama_kelas ?? '-';
                        $jamArray = $items->pluck('jam_ke')->sort()->values()->all();
                        
                        // Deteksi otomatis jika blok, ubah jadi format X-Y
                        $jamStr = count($jamArray) > 1 ? $jamArray[0] . '-' . end($jamArray) : $jamArray[0];
                        
                        $rincianBentrok .= "• Kelas <b>" . $namaKls . "</b> (Jam ke-" . $jamStr . ")<br>";
                    }
                    
                    return redirect()->back()->with('bentrok_popup', [
                        'pesan' => "Guru tersebut sudah memiliki jadwal mengajar di kelas lain pada hari dan jam yang sama:<br><br><div class='text-left ml-4 mb-2'>{$rincianBentrok}</div>Apakah Anda ingin <b>MENGHAPUS</b> jadwal beliau di kelas lama dan <b>MEMINDAHKANNYA</b> ke kelas ini?",
                        'request_data' => $request->all()
                    ]);
                }
            }
        }

        // ====================================================================
        // KECERDASAN 1.5: CEK BENTROK KELAS (SLOT SUDAH TERISI OLEH GURU LAIN)
        // ====================================================================
        $slotTerisi = \App\Models\JadwalHarian::with(['guru', 'pelajaran'])
            ->where('kelas_id', $request->kelas_id)
            ->where('hari', $request->hari)
            ->whereIn('jam_ke', $arrayJam)
            ->where('tahun_ajaran', $tahunAjaran)
            ->get();

        $nabrak = false;
        $groupedNabrak = [];
        
        foreach($slotTerisi as $slot) {
            if($slot->guru_id != $request->guru_id || $slot->pelajaran_id != $request->pelajaran_id) {
                $nabrak = true;
                $kunci = $slot->pelajaran_id . '_' . $slot->guru_id;
                
                if(!isset($groupedNabrak[$kunci])) {
                    $groupedNabrak[$kunci] = [
                        'pelajaran' => $slot->pelajaran->nama_pelajaran ?? 'Tanpa Pelajaran',
                        'guru' => $slot->guru->nama_guru ?? 'Tanpa Guru',
                        'jam' => []
                    ];
                }
                // Tampung jam-jam yang nabrak
                $groupedNabrak[$kunci]['jam'][] = $slot->jam_ke;
            }
        }

        if ($nabrak && $force_timpa != 'true') {
            $rincianNabrak = "";
            
            // FITUR BARU: Merakit teks agar jam tampil sebagai Blok (Misal: 1-2)
            foreach($groupedNabrak as $gn) {
                sort($gn['jam']);
                $jamStr = count($gn['jam']) > 1 ? $gn['jam'][0] . '-' . end($gn['jam']) : $gn['jam'][0];
                
                $rincianNabrak .= "• Jam Ke-{$jamStr}: <b>{$gn['pelajaran']}</b> ({$gn['guru']})<br>";
            }

            return redirect()->back()->with('timpa_popup', [
                'pesan' => "Slot kelas pada waktu tersebut sudah terisi oleh jadwal lain:<br><br><div class='text-left ml-4 mb-2 bg-orange-50 border border-orange-100 p-2 rounded-lg text-orange-800'>{$rincianNabrak}</div>Apakah Anda yakin ingin <b>MENGHAPUS</b> jadwal lama tersebut dan <b>MENIMPANYA</b> dengan jadwal ini?",
                'request_data' => $request->all()
            ]);
        }

        // ====================================================================
        // KECERDASAN 2: CEK KUOTA & LOGIKA PERTUKARAN JADWAL
        // ====================================================================
        $plot = \App\Models\PlotJadwal::where('kelas_id', $request->kelas_id)->where('pelajaran_id', $request->pelajaran_id)->first();

        if ($plot) {
            $terpakaiLain = \App\Models\JadwalHarian::where('kelas_id', $request->kelas_id)
                                ->where('pelajaran_id', $request->pelajaran_id)
                                ->where('tahun_ajaran', $tahunAjaran)
                                ->whereNotIn('jam_ke', $arrayJam)
                                ->get();
            
            $totalSesiLain = $terpakaiLain->count();
            
            if (($totalSesiLain + $jumlahJamDiisi) > $plot->beban_jam) {
                if ($force_swap == 'true' && $swap_target) {
                    list($hariLama, $jamLamaStr) = explode('_', $swap_target);
                    $arrayJamLama = explode('-', $jamLamaStr);
                    
                    \App\Models\JadwalHarian::where('kelas_id', $request->kelas_id)
                        ->where('pelajaran_id', $request->pelajaran_id)
                        ->where('hari', $hariLama)
                        ->whereIn('jam_ke', $arrayJamLama)
                        ->where('tahun_ajaran', $tahunAjaran)
                        ->delete();
                } else {
                    $opsiTukar = [];
                    $groupedByHari = $terpakaiLain->groupBy('hari');
                    foreach ($groupedByHari as $hari => $items) {
                        $items = $items->sortBy('jam_ke')->values();
                        $currentSub = [];
                        foreach ($items as $item) {
                            if (empty($currentSub)) $currentSub[] = $item;
                            else {
                                $lastItem = end($currentSub);
                                if ($item->jam_ke == $lastItem->jam_ke + 1) $currentSub[] = $item;
                                else {
                                    $first = $currentSub[0]; $last = end($currentSub);
                                    $jamTampil = ($first->jam_ke == $last->jam_ke) ? $first->jam_ke : $first->jam_ke . '-' . $last->jam_ke;
                                    $opsiTukar[] = ['hari' => $hari, 'jam_tampil' => $jamTampil, 'value' => $hari.'_'.$jamTampil];
                                    $currentSub = [$item];
                                }
                            }
                        }
                        if (!empty($currentSub)) {
                            $first = $currentSub[0]; $last = end($currentSub);
                            $jamTampil = ($first->jam_ke == $last->jam_ke) ? $first->jam_ke : $first->jam_ke . '-' . $last->jam_ke;
                            $opsiTukar[] = ['hari' => $hari, 'jam_tampil' => $jamTampil, 'value' => $hari.'_'.$jamTampil];
                        }
                    }

                    return redirect()->back()->with('tukar_popup', [
                        'pesan' => "Pelajaran ini memiliki Target ({$plot->beban_jam} Jam). Memasukkan jadwal ke sini akan melebihi target.",
                        'opsi_tukar' => $opsiTukar,
                        'request_data' => $request->all() 
                    ]);
                }
            }
        } else {
             return redirect()->back()->with('error_popup', 'DITOLAK!<br>Pelajaran ini belum di-plot di Target Mengajar untuk kelas ini.');
        }

        // SIMPAN JADWAL BARU KE BRANKAS
        foreach ($arrayJam as $jamKe) {
            \App\Models\JadwalHarian::updateOrCreate(
                ['kelas_id' => $request->kelas_id, 'hari' => $request->hari, 'jam_ke' => (int)$jamKe, 'tahun_ajaran' => $tahunAjaran],
                ['pelajaran_id' => $request->pelajaran_id, 'guru_id' => $request->guru_id]
            );
        }

        return redirect()->back()->with('sukses', "Jadwal Blok Jam {$request->jam_pilihan} berhasil disimpan!");
    }

    public function destroy(Request $request, $id)
    {
        $jadwal = JadwalHarian::find($id);
        if ($jadwal) {
            $jamKe = $jadwal->jam_ke;
            // Tentukan pasangan blok jamnya (misal jika jam 1, pasangannya 1 dan 2)
            $pasanganJam = ($jamKe % 2 == 1) ? [$jamKe, $jamKe + 1] : [$jamKe - 1, $jamKe];
            
            JadwalHarian::where('kelas_id', $jadwal->kelas_id)
                ->where('hari', $jadwal->hari)
                ->whereIn('jam_ke', $pasanganJam)
                ->where('tahun_ajaran', $jadwal->tahun_ajaran)
                ->delete();
        }
        return redirect()->back()->with('sukses', 'Jadwal Blok berhasil dikosongkan!');
    }

    public function prosesDragDrop(Request $request)
    {
        $request->validate([
            'source_id' => 'required|exists:jadwal_harians,id',
            'target_hari' => 'required|string',
            'target_jam' => 'required', 
            'target_id' => 'nullable', 
        ]);

        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($request) {
                
                $sourceRecord = \App\Models\JadwalHarian::find($request->source_id);
                if (!$sourceRecord) {
                    throw new \Exception("Jadwal sumber tidak ditemukan.");
                }

                $sourceHari = $sourceRecord->hari;
                $kelasId = $sourceRecord->kelas_id;
                $guruId = $sourceRecord->guru_id;
                $pelajaranId = $sourceRecord->pelajaran_id;

                // Tentukan rentang jam target dari request (bisa "3-4" atau "3")
                $targetJamInput = $request->target_jam;
                $targetJamArray = [];
                if (strpos($targetJamInput, '-') !== false) {
                    list($start, $end) = explode('-', $targetJamInput);
                    $targetJamArray = range((int)$start, (int)$end);
                } else {
                    $targetJamArray = [(int)$targetJamInput];
                }

                // Cari semua jam_ke yang tergabung dalam blok jadwal sumber ini
                $sourceJamList = \Illuminate\Support\Facades\DB::table('jadwal_harians')
                    ->where('hari', $sourceHari)
                    ->where('kelas_id', $kelasId)
                    ->where('pelajaran_id', $pelajaranId)
                    ->where('guru_id', $guruId)
                    ->pluck('jam_ke')
                    ->toArray();

                sort($sourceJamList);
                
                $sourceJamGroup = [];
                $tempGroup = [];
                foreach ($sourceJamList as $jKe) {
                    if (empty($tempGroup) || $jKe == end($tempGroup) + 1) {
                        $tempGroup[] = $jKe;
                    } else {
                        if (in_array($sourceRecord->jam_ke, $tempGroup)) {
                            $sourceJamGroup = $tempGroup;
                            break;
                        }
                        $tempGroup = [$jKe];
                    }
                }
                if (empty($sourceJamGroup) && in_array($sourceRecord->jam_ke, $tempGroup)) {
                    $sourceJamGroup = $tempGroup;
                }
                if (empty($sourceJamGroup)) {
                    $sourceJamGroup = [$sourceRecord->jam_ke];
                }

                $sourceIds = \Illuminate\Support\Facades\DB::table('jadwal_harians')
                    ->where('hari', $sourceHari)
                    ->where('kelas_id', $kelasId)
                    ->where('pelajaran_id', $pelajaranId)
                    ->where('guru_id', $guruId)
                    ->whereIn('jam_ke', $sourceJamGroup)
                    ->pluck('id')
                    ->toArray();

                $targetHari = $request->target_hari;
                $targetId = $request->target_id;

                if (!empty($targetId)) {
                    // ==============================================================
                    // SKENARIO 1: SWAP (TUKAR POSISI ANTAR BLOK)
                    // ==============================================================
                    $targetRecord = \Illuminate\Support\Facades\DB::table('jadwal_harians')->where('id', $targetId)->first();
                    if (!$targetRecord) {
                        throw new \Exception("Jadwal target tidak ditemukan.");
                    }
                    
                    $targetKelId = $targetRecord->kelas_id;
                    $targetGuruId = $targetRecord->guru_id;
                    $targetPelId = $targetRecord->pelajaran_id;

                    $targetJamList = \Illuminate\Support\Facades\DB::table('jadwal_harians')
                        ->where('hari', $targetHari)
                        ->where('kelas_id', $targetKelId)
                        ->where('pelajaran_id', $targetPelId)
                        ->where('guru_id', $targetGuruId)
                        ->pluck('jam_ke')
                        ->toArray();
                    sort($targetJamList);

                    $targetJamGroup = [];
                    $tempGroup2 = [];
                    foreach ($targetJamList as $jKe) {
                        if (empty($tempGroup2) || $jKe == end($tempGroup2) + 1) {
                            $tempGroup2[] = $jKe;
                        } else {
                            if (in_array($targetRecord->jam_ke, $tempGroup2)) {
                                $targetJamGroup = $tempGroup2;
                                break;
                            }
                            $tempGroup2 = [$jKe];
                        }
                    }
                    if (empty($targetJamGroup) && in_array($targetRecord->jam_ke, $tempGroup2)) {
                        $targetJamGroup = $tempGroup2;
                    }
                    if (empty($targetJamGroup)) {
                        $targetJamGroup = [$targetRecord->jam_ke];
                    }

                    $targetIds = \Illuminate\Support\Facades\DB::table('jadwal_harians')
                        ->where('hari', $targetHari)
                        ->where('kelas_id', $targetKelId)
                        ->where('pelajaran_id', $targetPelId)
                        ->where('guru_id', $targetGuruId)
                        ->whereIn('jam_ke', $targetJamGroup)
                        ->pluck('id')
                        ->toArray();

                    // LANGKAH A: Pindahkan Source ke Ruang Transit (Jam 999)
                    \Illuminate\Support\Facades\DB::table('jadwal_harians')
                        ->whereIn('id', $sourceIds)
                        ->update(['jam_ke' => 999, 'hari' => 'TRANSIT']);

                    // LANGKAH B: Pindahkan Target ke posisi Source asli
                    foreach ($targetIds as $index => $tId) {
                        $newJam = $sourceJamGroup[$index] ?? $sourceJamGroup[0];
                        \Illuminate\Support\Facades\DB::table('jadwal_harians')
                            ->where('id', $tId)
                            ->update([
                                'hari' => $sourceHari,
                                'jam_ke' => $newJam
                            ]);
                    }

                    // LANGKAH C: Pindahkan Source dari Transit ke posisi Target asli
                    foreach ($sourceIds as $index => $sId) {
                        $newJam = $targetJamGroup[$index] ?? $targetJamGroup[0];
                        \Illuminate\Support\Facades\DB::table('jadwal_harians')
                            ->where('id', $sId)
                            ->update([
                                'hari' => $targetHari,
                                'jam_ke' => $newJam
                            ]);
                    }

                } else {
                    // ==============================================================
                    // SKENARIO 2: MOVE KE KOTAK KOSONG
                    // ==============================================================
                    foreach ($sourceIds as $index => $sId) {
                        $newJam = $targetJamArray[$index] ?? $targetJamArray[0];
                        \Illuminate\Support\Facades\DB::table('jadwal_harians')
                            ->where('id', $sId)
                            ->update([
                                'hari' => $targetHari,
                                'jam_ke' => $newJam
                            ]);
                    }
                }
            });

            return response()->json([
                'status' => 'success', 
                'pesan' => 'Blok jadwal berhasil ' . (!empty($request->target_id) ? 'ditukar seutuhnya!' : 'dipindahkan seutuhnya!')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error', 
                'pesan' => 'Gagal memproses blok jadwal: ' . $e->getMessage()
            ], 500);
        }
    }
    // ==========================================================
    // 1. FUNGSI: Menampilkan Form Mutasi Guru
    // ==========================================================
    public function formMutasi($id)
    {
        // Ambil data jadwal lama beserta relasinya
        $jadwal = \App\Models\JadwalHarian::with(['guru', 'pelajaran', 'kelas'])->findOrFail($id);
        
        // Ambil daftar guru pengganti (kecuali guru yang sedang menjabat)
        $semuaGuru = \App\Models\Guru::where('id', '!=', $jadwal->guru_id)
                        ->orderBy('nama_guru', 'asc')
                        ->get();

        return view('admin.jadwal-mutasi', compact('jadwal', 'semuaGuru'));
    }

    // ==========================================================
    // 2. FUNGSI: Eksekusi Pergantian Guru (Effective-Dated)
    // ==========================================================
    public function mutasiGuru(Request $request, $jadwal_id)
    {
        $request->validate([
            'guru_baru_id' => 'required|exists:gurus,id',
            'tanggal_efektif' => 'required|date'
        ]);

        $jadwalLama = \App\Models\JadwalHarian::findOrFail($jadwal_id);
        $tanggalEfektif = $request->tanggal_efektif;
        
        // Tanggal terakhir guru lama mengajar adalah 1 hari sebelum tanggal efektif
        $kemarin = \Carbon\Carbon::parse($tanggalEfektif)->subDay()->format('Y-m-d');

        \Illuminate\Support\Facades\DB::transaction(function () use ($jadwalLama, $request, $tanggalEfektif, $kemarin) {
            // 1. Tutup masa berlaku jadwal guru lama
            $jadwalLama->update(['berlaku_sampai' => $kemarin]);

            // 2. Kloning (Duplikat) jadwal untuk guru baru
            $jadwalBaru = $jadwalLama->replicate();
            $jadwalBaru->guru_id = $request->guru_baru_id;
            $jadwalBaru->berlaku_mulai = $tanggalEfektif;
            $jadwalBaru->berlaku_sampai = null; // Aktif sampai akhir semester/tidak terhingga
            $jadwalBaru->save();
        });

        // Redirect kembali ke halaman daftar jadwal
        return redirect('/jadwal-harian')->with('sukses', 'Mutasi guru berhasil diproses! Riwayat absen guru lama tetap aman.');
    }
}