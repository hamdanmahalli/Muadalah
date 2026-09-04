<?php

namespace App\Imports;

use App\Models\KehadiranSiswa;
use App\Models\Siswa;
use App\Models\Periode;
use App\Models\AngkatanSiswa;
use PhpOffice\PhpSpreadsheet\IOFactory;

class AbsenImport
{
    public $imported = 0;
    public $hadirOpsional = 0;

    protected $periodeId;
    protected $periode;

    public function __construct($periodeId = null)
    {
        $this->periodeId = $periodeId
            ? (int) $periodeId
            : (Periode::where('is_active', true)->first()?->id);
        $this->periode = $this->periodeId ? Periode::find($this->periodeId) : null;
    }

    /**
     * Proses file absen SIA.
     * 1) Semua siswa aktif periode dicatat Hadir untuk setiap tanggal unik di file.
     * 2) Baris yang ada di file di-override ke statusnya (S/I/A).
     */
    public function import($filePath)
    {
        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true);

        // Ambil baris valid (nis + tanggal + status)
        $records = [];      // key "siswaId|tanggal" => baris
        $tanggalList = [];  // unik tanggal
        foreach ($rows as $i => $r) {
            if ($i === 1) {
                continue; // header
            }
            $nis = trim((string) ($r['A'] ?? ''));
            $status = $this->normalizeStatus($r['C'] ?? null);
            if ($nis === '' || !$status) {
                continue;
            }
            $tanggal = $this->parseTanggal($r['B'] ?? null);
            if (!$tanggal) {
                continue;
            }
            $keterangan = trim((string) ($r['D'] ?? '')) ?: null;

            $siswa = Siswa::where('nis', $nis)->where('status', 'Aktif')->first();
            if (!$siswa) {
                continue;
            }

            $records[$siswa->id . '|' . $tanggal] = [
                'siswa_id' => $siswa->id,
                'tanggal'  => $tanggal,
                'status'   => $status,
                'keterangan' => $keterangan,
            ];
            $tanggalList[$tanggal] = true;
        }

        $tanggalList = array_keys($tanggalList);

        // ---- FASE 1: semua siswa aktif periode => Hadir untuk tiap tanggal ----
        $placements = $this->siswaAktifPeriode(); // [siswa_id => kelas_id]
        foreach ($tanggalList as $tanggal) {
            // siswa yang SUDAH punya record di tanggal ini (jangan ditimpa)
            $existing = KehadiranSiswa::where('tanggal', $tanggal)
                ->pluck('status', 'siswa_id');

            $now = now()->format('Y-m-d H:i:s');
            $inserts = [];
            foreach ($placements as $siswaId => $kelasId) {
                if (isset($existing[$siswaId])) {
                    continue; // sudah tercatat (SIA/manual), biarkan
                }
                $inserts[] = [
                    'siswa_id'    => $siswaId,
                    'periode_id'  => $this->periodeId,
                    'kelas_id'    => $kelasId,
                    'tanggal'     => $tanggal,
                    'status'      => 'hadir',
                    'user_id'     => auth()->id(),
                    'created_at'  => $now,
                    'updated_at'  => $now,
                ];
            }

            foreach ($inserts as $rec) {
                KehadiranSiswa::insert($rec);
                $this->hadirOpsional++;
            }
        }

        // ---- FASE 2: override sesuai file (S/I/A) ----
        foreach ($records as $rec) {
            KehadiranSiswa::updateOrCreate(
                ['siswa_id' => $rec['siswa_id'], 'tanggal' => $rec['tanggal']],
                [
                    'periode_id' => $this->periodeId,
                    'kelas_id'   => $this->kelasIdUntuk($rec['siswa_id']),
                    'status'     => $rec['status'],
                    'keterangan' => $rec['keterangan'],
                    'user_id'    => auth()->id(),
                ]
            );
            $this->imported++;
        }

        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);
    }

    /**
     * Daftar siswa aktif pada periode terpilih beserta kelasnya.
     * @return array [siswa_id => kelas_id]
     */
    protected function siswaAktifPeriode()
    {
        return AngkatanSiswa::where('periode_id', $this->periodeId)
            ->where('status', 'Aktif')
            ->get()
            ->pluck('kelas_id', 'siswa_id')
            ->all();
    }

    protected function kelasIdUntuk($siswaId)
    {
        return AngkatanSiswa::where('siswa_id', $siswaId)
            ->where('periode_id', $this->periodeId)
            ->value('kelas_id');
    }

    protected function normalizeStatus($value)
    {
        $v = strtoupper(trim((string) $value));
        $map = [
            'S' => 'sakit', 'SAKIT' => 'sakit',
            'I' => 'izin', 'IZIN' => 'izin',
            'A' => 'alpha', 'ALPHA' => 'alpha', 'TANPA KETERANGAN' => 'alpha',
        ];
        return $map[$v] ?? null;
    }

    protected function parseTanggal($value)
    {
        $v = trim((string) $value);
        if ($v === '') return null;

        if (strpos($v, '-') !== false || strpos($v, '/') !== false) {
            try {
                $date = \Carbon\Carbon::parse(str_replace('/', '-', $v));
                if ($date->year < 2000 || $date->year > 2100) return null;
                return $date->format('Y-m-d');
            } catch (\Exception $e) {
                return null;
            }
        }

        if (is_numeric($v)) {
            try {
                $unix = ($v - 25569) * 86400;
                return gmdate('Y-m-d', $unix);
            } catch (\Exception $e) {
                return null;
            }
        }

        return null;
    }
}
