<?php

namespace App\Services;

use App\Models\MasterJam;
use Carbon\Carbon;

/**
 * Menyusun data presentasi jadwal: blok jam, urutan hari, matriks, dan blok per guru.
 *
 * SRP: Satu tanggung jawab — transformasi data jadwal mentah menjadi struktur
 * yang siap ditampilkan. Logika ini sebelumnya diulang di banyak controller
 * (JadwalController, JadwalHarianController, AuthController, ScanController),
 * sehingga dipusatkan di satu layanan stateless.
 */
class JadwalMatrixService
{
    /**
     * Urutan standar hari dalam satu minggu.
     */
    public function urutanHari(): array
    {
        return ['Sabtu', 'Ahad', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
    }

    /**
     * Bangun daftar opsi blok jam (jam ke-1/2, 3/4, ...) beserta label waktu.
     * Jika tidak ada data MasterJam, gunakan default blok 1-2 s.d. 9-10.
     *
     * @return array<int, array{key: string, jam_list: int[], label: string}>
     */
    public function opsiBlokJam(): array
    {
        $semuaJam = MasterJam::orderBy('jam_ke', 'asc')->get();

        if ($semuaJam->count() === 0) {
            $defaultBlok = ['1-2' => [1, 2], '3-4' => [3, 4], '5-6' => [5, 6], '7-8' => [7, 8], '9-10' => [9, 10]];
            $opsi = [];
            foreach ($defaultBlok as $key => $list) {
                $opsi[] = ['key' => $key, 'jam_list' => $list, 'label' => "Jam Ke-$key"];
            }
            return $opsi;
        }

        $opsi = [];
        for ($i = 0; $i < count($semuaJam); $i += 2) {
            $j1 = $semuaJam[$i];
            $j2 = $semuaJam[$i + 1] ?? $j1;
            $keyBlok = ($j1->jam_ke == $j2->jam_ke) ? (string) $j1->jam_ke : ($j1->jam_ke . '-' . $j2->jam_ke);
            $waktu = Carbon::parse($j1->jam_mulai)->format('H:i') . ' - ' . Carbon::parse($j2->jam_selesai)->format('H:i');
            $opsi[] = [
                'key'      => $keyBlok,
                'jam_list' => ($j1->jam_ke == $j2->jam_ke) ? [$j1->jam_ke] : [$j1->jam_ke, $j2->jam_ke],
                'label'    => "Jam Ke-$keyBlok ($waktu)",
            ];
        }

        return $opsi;
    }

    /**
     * Susun jadwal mentah menjadi matriks [hari][keyBlok] = jadwal.
     *
     * @param mixed $dataJadwal Eloquent collection jadwal
     * @param array $opsiBlokJam hasil opsiBlokJam()
     * @return array<string, array<string, mixed>>
     */
    public function buatMatriks($dataJadwal, array $opsiBlokJam): array
    {
        $matriks = [];
        foreach ($opsiBlokJam as $blok) {
            foreach ($dataJadwal as $j) {
                if (in_array($j->jam_ke, $blok['jam_list'])) {
                    $matriks[$j->hari][$blok['key']] = $j;
                }
            }
        }
        return $matriks;
    }

    /**
     * Kelompokkan jadwal per guru menjadi blok jam berurutan (maks 2 jam per blok).
     *
     * @param mixed $jadwalMentah Collection jadwal (with kelas/pelajaran)
     * @return array<string, array> key nama hari -> list blok
     */
    public function blokJadwalPerGuru($jadwalMentah): array
    {
        $urutan = ['Senin' => 1, 'Selasa' => 2, 'Rabu' => 3, 'Kamis' => 4, 'Jumat' => 5, 'Sabtu' => 6, 'Ahad' => 7];

        $grouped = $jadwalMentah->groupBy('hari')->sortBy(function ($item, $key) use ($urutan) {
            return $urutan[$key] ?? 99;
        });

        $hasil = [];
        foreach ($grouped as $hari => $jadwals) {
            $jadwals = $jadwals->sortBy('jam_ke')->values();
            $blokList = [];
            $current = null;

            foreach ($jadwals as $j) {
                $namaPel = $j->pelajaran->nama_pelajaran ?? '-';
                $namaKel = $j->kelas->nama_kelas ?? '-';

                if (!$current) {
                    $current = ['jam_mulai' => $j->jam_ke, 'jam_selesai' => $j->jam_ke, 'pelajaran' => $namaPel, 'kelas' => $namaKel];
                } elseif ($current['pelajaran'] == $namaPel && $current['kelas'] == $namaKel && $j->jam_ke == $current['jam_selesai'] + 1) {
                    $current['jam_selesai'] = $j->jam_ke;
                } else {
                    $blokList[] = $current;
                    $current = ['jam_mulai' => $j->jam_ke, 'jam_selesai' => $j->jam_ke, 'pelajaran' => $namaPel, 'kelas' => $namaKel];
                }
            }
            if ($current) {
                $blokList[] = $current;
            }
            if (count($blokList) > 0) {
                $hasil[$hari] = $blokList;
            }
        }

        return $hasil;
    }

    /**
     * Bangun blok jadwal hari ini untuk menu "Intip Jadwal" (login guru, tanpa login).
     *
     * Struktur output identik dengan controller asli: list blok dengan kunci
     * jam, waktu, pelajaran, kitab, kelas.
     *
     * @param mixed $jadwalMentah sudah difilter per guru & per hari
     * @return array<int, array>
     */
    public function blokJadwalIntip($jadwalMentah): array
    {
        $masterJam = \App\Models\MasterJam::orderBy('jam_ke', 'asc')->get()->keyBy('jam_ke');

        $blok = [];
        $currentBlock = null;

        foreach ($jadwalMentah as $j) {
            $namaPel = $j->pelajaran->nama_pelajaran ?? 'Pelajaran';
            $namaKel = $j->kelas->nama_kelas ?? '-';
            $tingkatKelas = preg_replace('/[^0-9]/', '', $namaKel);
            $petaKitab = $j->pelajaran->kitab_tingkat ?? [];
            $namaKitab = $petaKitab[$tingkatKelas] ?? ($j->pelajaran->nama_kitab ?? '-');

            if (!$currentBlock) {
                $currentBlock = [
                    'jam_mulai'  => $j->jam_ke,
                    'jam_selesai' => $j->jam_ke,
                    'pelajaran'  => $namaPel,
                    'kitab'      => $namaKitab,
                    'kelas'      => $namaKel,
                ];
            } elseif ($currentBlock['pelajaran'] == $namaPel
                    && $currentBlock['kelas'] == $namaKel
                    && $j->jam_ke == $currentBlock['jam_selesai'] + 1
                    && ($currentBlock['jam_selesai'] - $currentBlock['jam_mulai'] < 1)) {
                $currentBlock['jam_selesai'] = $j->jam_ke;
            } else {
                $blok[] = $currentBlock;
                $currentBlock = [
                    'jam_mulai'  => $j->jam_ke,
                    'jam_selesai' => $j->jam_ke,
                    'pelajaran'  => $namaPel,
                    'kitab'      => $namaKitab,
                    'kelas'      => $namaKel,
                ];
            }
        }

        if ($currentBlock) {
            $blok[] = $currentBlock;
        }

        $jadwal = [];
        foreach ($blok as $item) {
            $jam1 = $masterJam->get($item['jam_mulai']);
            $jam2 = $masterJam->get($item['jam_selesai']);
            $waktu = '';

            if ($jam1) {
                $waktu = \Carbon\Carbon::parse($jam1->jam_mulai)->format('H:i');
                if ($jam2 && $jam2->jam_selesai) {
                    $waktu .= ' - ' . \Carbon\Carbon::parse($jam2->jam_selesai)->format('H:i');
                }
            }

            $jadwal[] = [
                'jam'      => ($item['jam_mulai'] == $item['jam_selesai'])
                                ? (string) $item['jam_mulai']
                                : $item['jam_mulai'] . '-' . $item['jam_selesai'],
                'waktu'    => $waktu,
                'pelajaran'=> $item['pelajaran'],
                'kitab'    => $item['kitab'],
                'kelas'    => $item['kelas'],
            ];
        }

        return $jadwal;
    }

    /**
     * Urutan dinamis hari dimulai dari hari ini (untuk dashboard guru).
     *
     * @return array<string, int>
     */
    public function urutanDinamisDariHariIni(): array
    {
        $hariSeAhad = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Ahad'];
        $hariIni = map_hari(Carbon::now()->format('l'));
        $indexHariIni = array_search($hariIni, $hariSeAhad);
        // Index 0 valid untuk Senin
        if ($indexHariIni === false) {
            $indexHariIni = 0;
        }
        $diurutkan = array_merge(
            array_slice($hariSeAhad, $indexHariIni),
            array_slice($hariSeAhad, 0, $indexHariIni)
        );

        $urutan = [];
        foreach ($diurutkan as $index => $hari) {
            $urutan[$hari] = $index + 1;
        }
        return $urutan;
    }

    /**
     * Bangun blok jadwal mobile SPA (Dashboard Guru).
     *
     * Struktur output identik dengan controller asli:
     * per hari -> list blok dengan kunci jam_mulai, jam_selesai, mata_pelajaran,
     * nama_kitab, kelas, pelajaran_id, jam_tampil.
     *
     * @param mixed $jadwalMentah
     * @return array<string, array<int, array>>
     */
    public function blokJadwalDashboardGuru($jadwalMentah): array
    {
        $urutanDinamis = $this->urutanDinamisDariHariIni();

        $jadwalGrouped = $jadwalMentah->groupBy('hari')->sortBy(function ($item, $key) use ($urutanDinamis) {
            return $urutanDinamis[$key] ?? 99;
        });

        $hasil = [];
        foreach ($jadwalGrouped as $hari => $list) {
            $list = $list->sortBy('jam_ke')->values();

            $blokJadwal = [];
            $currentBlock = null;

            foreach ($list as $j) {
                $namaPel = $j->pelajaran->nama_pelajaran ?? 'Pelajaran';
                $namaKel = $j->kelas->nama_kelas ?? '-';
                $pelajaranId = $j->pelajaran_id ?? '';

                $tingkatKelas = preg_replace('/[^0-9]/', '', $namaKel);
                $petaKitab = $j->pelajaran->kitab_tingkat ?? [];
                $namaKitab = $petaKitab[$tingkatKelas] ?? ($j->pelajaran->nama_kitab ?? '-');

                if (!$currentBlock) {
                    $currentBlock = [
                        'jam_mulai' => $j->jam_ke,
                        'jam_selesai' => $j->jam_ke,
                        'mata_pelajaran' => $namaPel,
                        'nama_kitab' => $namaKitab,
                        'kelas' => $namaKel,
                        'pelajaran_id' => $pelajaranId,
                    ];
                } elseif (
                    $currentBlock['mata_pelajaran'] == $namaPel
                    && $currentBlock['kelas'] == $namaKel
                    && $j->jam_ke == $currentBlock['jam_selesai'] + 1
                    && ($currentBlock['jam_selesai'] - $currentBlock['jam_mulai'] < 1)
                ) {
                    $currentBlock['jam_selesai'] = $j->jam_ke;
                } else {
                    $currentBlock['jam_tampil'] = ($currentBlock['jam_mulai'] == $currentBlock['jam_selesai']) ? (string) $currentBlock['jam_mulai'] : $currentBlock['jam_mulai'] . '-' . $currentBlock['jam_selesai'];
                    $blokJadwal[] = $currentBlock;

                    $currentBlock = [
                        'jam_mulai' => $j->jam_ke,
                        'jam_selesai' => $j->jam_ke,
                        'mata_pelajaran' => $namaPel,
                        'nama_kitab' => $namaKitab,
                        'kelas' => $namaKel,
                        'pelajaran_id' => $pelajaranId,
                    ];
                }
            }
            if ($currentBlock) {
                $currentBlock['jam_tampil'] = ($currentBlock['jam_mulai'] == $currentBlock['jam_selesai']) ? (string) $currentBlock['jam_mulai'] : $currentBlock['jam_mulai'] . '-' . $currentBlock['jam_selesai'];
                $blokJadwal[] = $currentBlock;
            }
            if (count($blokJadwal) > 0) {
                $hasil[$hari] = $blokJadwal;
            }
        }

        return $hasil;
    }
}
