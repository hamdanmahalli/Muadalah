<?php

namespace App\Exports;

use App\Models\Guru;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Enumerable;

class GuruExport implements FromCollection, WithHeadings
{
    public function collection(): Enumerable
    {
        return Guru::select('nip', 'nig', 'nama_guru', 'status')->get();
    }

    public function headings(): array
    {
        return ["NIP", "NIG", "Nama Guru", "Status"];
    }
}