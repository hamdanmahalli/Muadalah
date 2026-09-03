<?php

namespace App\Imports;

use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\Periode;
use App\Models\AngkatanSiswa;
use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SiswaImport implements ToModel, WithHeadingRow
{
    protected $periode;

    public function __construct($periodeId = null)
    {
        $this->periode = $periodeId
            ? Periode::find($periodeId)
            : Periode::where('is_active', true)->first();
    }

    public function model(array $row): Model|array|null
    {
        // Kolom yang wajib: NIS
        $nis = trim((string)($row['nis'] ?? ''));
        if ($nis === '') {
            return null;
        }

        $nama = trim((string)($row['nama_siswa'] ?? $row['nama'] ?? '')) ?: 'Tanpa Nama';

        // Cari kelas berdasarkan nama_kelas (kolom opsional)
        $kelasId = null;
        if (!empty(trim((string)($row['kelas'] ?? '')))) {
            $kelas = Kelas::where('nama_kelas', 'ilike', trim((string)$row['kelas']))->first();
            $kelasId = $kelas ? $kelas->id : null;
        }

        // NISN opsional; jadikan null jika kosong agar tidak bentrok unique
        $nisn = trim((string)($row['nisn'] ?? ''));
        $nisn = $nisn === '' ? null : $nisn;

        $tglLahir = $this->parseTanggal($row['tanggal_lahir'] ?? null);

        $siswa = Siswa::updateOrCreate(
            ['nis' => $nis],
            [
                'nisn'           => $nisn,
                'nama_siswa'     => $nama,
                'jenis_kelamin'  => $this->normalizeJk($row['jenis_kelamin'] ?? $row['jk'] ?? null),
                'tempat_lahir'   => trim((string)($row['tempat_lahir'] ?? '')),
                'tanggal_lahir'  => $tglLahir,
                'alamat'         => trim((string)($row['alamat'] ?? '')),
                'nama_ayah'      => trim((string)($row['nama_ayah'] ?? '')),
                'nama_ibu'       => trim((string)($row['nama_ibu'] ?? '')),
                'pekerjaan_ortu' => trim((string)($row['pekerjaan_ortu'] ?? '')),
                'no_hp_ortu'     => trim((string)($row['no_hp_ortu'] ?? $row['no_hp'] ?? '')),
                'tahun_masuk'    => trim((string)($row['tahun_masuk'] ?? '')),
                'status'         => $this->normalizeStatus($row['status'] ?? null),
            ]
        );

        // Jika ada kelas & periode, masukkan ke penempatan (pangkat)
        if ($kelasId && $this->periode) {
            $angkatan = AngkatanSiswa::firstOrNew([
                'siswa_id'   => $siswa->id,
                'periode_id' => $this->periode->id,
            ]);

            // Nomor absen TETAP selama satu tahun ajaran: hanya diisi jika belum ada
            if (!$angkatan->exists || $angkatan->nomor_absen === null) {
                $nomor = AngkatanSiswa::where('kelas_id', $kelasId)
                    ->where('periode_id', $this->periode->id)
                    ->max('nomor_absen');
                $angkatan->nomor_absen = ($nomor ? (int)$nomor + 1 : 1);
            }

            $angkatan->kelas_id      = $kelasId;
            $angkatan->status        = 'Aktif';
            $angkatan->tanggal_masuk = $angkatan->tanggal_masuk ?? now();
            $angkatan->save();
        }

        return $siswa;
    }

    protected function normalizeJk($value)
    {
        $v = strtoupper(trim((string)$value));
        if (in_array($v, ['L', 'LAKI', 'LAKI-LAKI', 'LAKI LAKI', 'LAKI_LAKI', 'MALE', '1'])) return 'L';
        if (in_array($v, ['P', 'PEREMPUAN', 'FEMALE', 'WANITA', '2'])) return 'P';
        return null;
    }

    protected function normalizeStatus($value)
    {
        $v = strtoupper(trim((string)$value));
        if ($v === '' || $v === 'AKTIF' || $v === 'A') return 'Aktif';
        if ($v === 'ALUMNI' || $v === 'LULUS') return 'Alumni';
        return 'Keluar';
    }

    protected function parseTanggal($value)
    {
        $v = trim((string)$value);
        if ($v === '') return null;

        // Format Y-m-d (date Excel)
        if (strpos($v, '-') !== false || strpos($v, '/') !== false) {
            $normal = str_replace('/', '-', $v);
            try {
                $date = \Carbon\Carbon::parse($normal);
                if ($date->year < 1900 || $date->year > 2100) return null;
                return $date->format('Y-m-d');
            } catch (\Exception $e) {
                return null;
            }
        }

        // Nilai numerik (serial date Excel)
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
