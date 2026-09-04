<?php

namespace App\Services;

use App\Models\Guru;
use App\Models\JadwalHarian;
use App\Models\Kelas;
use App\Models\Pelajaran;
use App\Models\PlotJadwal;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Impor data master dari berkas Excel (Guru, Pelajaran, Kelas, Plot, Jadwal).
 *
 * SRP: Satu tanggung jawab — membaca baris spreadsheet dan menyimpan
 * (updateOrCreate) ke tabel terkait. Stateless; pengguna mengimpor lewat request.
 */
class MasterImportService
{
    /**
     * Impor data guru. Format kolom: NIG, Nama, Gender, Alamat, No HP, Status.
     */
    public function importGuru($file): int
    {
        $rows = $this->bacaBaris($file);
        $imported = 0;

        foreach ($rows as $index => $row) {
            if ($index == 0 || empty($row[0])) {
                continue;
            }

            Guru::updateOrCreate(
                ['nig' => (string) $row[0]],
                [
                    'nama_guru' => $row[1] ?? 'Tanpa Nama',
                    'gender'    => $row[2] ?? null,
                    'alamat'    => $row[3] ?? null,
                    'no_hp'     => $row[4] ?? null,
                    'status'    => $row[5] ?? 'Aktif',
                ]
            );
            $imported++;
        }

        return $imported;
    }

    /**
     * Impor data pelajaran dengan kode MP anti-tabrakan.
     * Format kolom: Kode MP, Nama Pelajaran, Nama Kitab.
     */
    public function importPelajaran($file): int
    {
        $rows = $this->bacaBaris($file);
        $imported = 0;

        foreach ($rows as $index => $row) {
            if ($index == 0 || empty($row[1])) {
                continue;
            }

            $kodeBaru = $row[0] ?? null;
            if (empty($kodeBaru)) {
                $kodeBaru = $this->kodeMpBerikutnya();
            }

            Pelajaran::updateOrCreate(
                ['nama_pelajaran' => trim($row[1])],
                [
                    'kode_pelajaran' => $kodeBaru,
                    'nama_kitab'     => $row[2] ?? '-',
                ]
            );
            $imported++;
        }

        return $imported;
    }

    /**
     * Impor data kelas. Format kolom: Nama Kelas.
     */
    public function importKelas($file): int
    {
        $rows = $this->bacaBaris($file);
        $imported = 0;

        foreach ($rows as $index => $row) {
            if ($index == 0 || empty($row[0])) {
                continue;
            }

            Kelas::updateOrCreate(['nama_kelas' => strtoupper(trim($row[0]))]);
            $imported++;
        }

        return $imported;
    }

    /**
     * Impor target mengajar (plot jadwal).
     * Format kolom: Nama Kelas, Nama Pelajaran, NIG/Nama Guru, Beban Jam.
     */
    public function importPlotJadwal($file): int
    {
        $rows = $this->bacaBaris($file);
        $imported = 0;

        foreach ($rows as $index => $row) {
            if ($index == 0 || empty($row[0]) || empty($row[1])) {
                continue;
            }

            $kelas = Kelas::where('nama_kelas', 'ilike', trim($row[0]))->first();
            $pelajaran = Pelajaran::where('nama_pelajaran', 'ilike', trim($row[1]))->first();

            $guru = null;
            if (!empty($row[2])) {
                $guru = Guru::where('nig', 'ilike', trim($row[2]))->orWhere('nama_guru', 'ilike', trim($row[2]))->first();
            }

            if ($kelas && $pelajaran) {
                PlotJadwal::updateOrCreate(
                    ['kelas_id' => $kelas->id, 'pelajaran_id' => $pelajaran->id],
                    ['guru_id' => $guru ? $guru->id : null, 'beban_jam' => isset($row[3]) ? (int) $row[3] : 2]
                );
                $imported++;
            }
        }

        return $imported;
    }

    /**
     * Impor jadwal harian yang terikat tahun ajaran.
     * Format kolom: Nama Kelas, Hari, Jam Ke, NIG/Nama Guru, Nama Pelajaran.
     */
    public function importJadwalHarian($file, string $tahunAjaran): int
    {
        $rows = $this->bacaBaris($file);
        $imported = 0;

        foreach ($rows as $index => $row) {
            if ($index == 0 || empty($row[0]) || empty($row[1]) || empty($row[2])) {
                continue;
            }

            $kelas = Kelas::where('nama_kelas', 'ilike', trim($row[0]))->first();
            $pelajaran = Pelajaran::where('nama_pelajaran', 'ilike', trim($row[4]))->first();

            $guru = null;
            if (!empty($row[3])) {
                $guru = Guru::where('nig', 'ilike', trim($row[3]))->orWhere('nama_guru', 'ilike', trim($row[3]))->first();
            }

            if ($kelas && $pelajaran) {
                JadwalHarian::updateOrCreate(
                    [
                        'kelas_id'     => $kelas->id,
                        'hari'         => trim($row[1]),
                        'jam_ke'       => (int) $row[2],
                        'tahun_ajaran' => $tahunAjaran,
                    ],
                    [
                        'pelajaran_id' => $pelajaran->id,
                        'guru_id'      => $guru ? $guru->id : null,
                    ]
                );
                $imported++;
            }
        }

        return $imported;
    }

    private function bacaBaris($file): array
    {
        $spreadsheet = IOFactory::load($file->path());
        return $spreadsheet->getActiveSheet()->toArray();
    }

    private function kodeMpBerikutnya(): string
    {
        $lastPelajaran = Pelajaran::orderBy('kode_pelajaran', 'desc')->first();
        if ($lastPelajaran && preg_match('/MP-(\d+)/', $lastPelajaran->kode_pelajaran, $matches)) {
            $angkaTerakhir = (int) $matches[1];
            do {
                $angkaTerakhir++;
                $kodeBaru = 'MP-' . str_pad($angkaTerakhir, 3, '0', STR_PAD_LEFT);
            } while (Pelajaran::where('kode_pelajaran', $kodeBaru)->exists());
            return $kodeBaru;
        }
        return 'MP-001';
    }
}
