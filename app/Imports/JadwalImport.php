<?php

namespace App\Imports;

use App\Models\Jadwal;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class JadwalImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            
            // Mengambil NIG dengan sangat fleksibel
            $nigGuru = $row['nig_guru'] ?? $row['nig'] ?? $row['guru'] ?? null;
            $nigGuru = trim($nigGuru); // Membersihkan spasi gaib
            
            if (!empty($nigGuru)) {
                try {
                    Jadwal::create([
                        'kelas'          => $row['kelas'] ?? $row['kel'] ?? 'Tidak Diketahui',
                        'hari'           => $row['hari'] ?? '-',
                        'jam_ke'         => (int) ($row['jam_ke'] ?? $row['jam'] ?? 0),
                        'nig_guru'       => $nigGuru,
                        'mata_pelajaran' => $row['mata_pelajaran'] ?? $row['pelajaran'] ?? '-',
                    ]);
                } catch (\Exception $e) {
                    // FITUR DIAGNOSTIK: Membongkar alasan penolakan database
                    $baris = $index + 2; // +2 karena baris 1 adalah header Excel
                    
                    // Sistem akan berhenti dan menampilkan pesan hitam berisi detail error
                    dd("Sistem Menolak Excel Anda di Baris ke-" . $baris . " | Isi Kolom Guru: '" . $nigGuru . "' | Alasan Brankas: " . $e->getMessage());
                }
            }
        }
    }
}