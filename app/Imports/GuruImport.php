<?php

namespace App\Imports;

use App\Models\Guru;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class GuruImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return Guru::updateOrCreate(
            ['nig' => $row['nig']],
            [
                'nama_guru' => $row['nama_guru'] ?? $row['nama'] ?? 'Tanpa Nama',
                'nip' => $row['nip'] ?? null,
                'status' => 'Aktif'
            ]
        );
    }
}