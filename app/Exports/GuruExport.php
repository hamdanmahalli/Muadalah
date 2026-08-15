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
        return Guru::select('nig', 'nama_guru', 'status')->get();
    }

    public function headings(): array
    {
        return ["NIG", "Nama Guru", "Status"];
    }
}