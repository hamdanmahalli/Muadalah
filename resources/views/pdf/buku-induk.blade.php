<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Buku Induk Murid</title>
    <style>
        @page { size: A4 landscape; margin: 12mm; }
        body { font-family: 'DejaVu Sans', sans-serif; color: #374151; font-size: 8pt; }
        .header { border-bottom: 2px solid #1d4ed8; padding-bottom: 8px; margin-bottom: 12px; text-align: center; }
        .header h1 { font-size: 13pt; margin: 0; color: #1e3a8a; }
        .header p { margin: 3px 0 0; color: #6b7280; font-size: 9pt; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #cbd5e1; padding: 4px; font-size: 7.5pt; }
        th { background: #eef2ff; }
        .num { text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h1>BUKU INDUK MURID</h1>
        <p>{{ $kelas ? 'Kelas: ' . $kelas->nama_kelas : 'Semua Kelas' }} @if($periode) | Periode: {{ $periode->tahun_ajaran }} @endif</p>
    </div>
    <table>
        <thead>
            <tr>
                <th class="num">No</th>
                <th>NIS</th>
                <th>NISN</th>
                <th>Nama Siswa</th>
                <th>L/P</th>
                <th>Tempat, Tgl Lahir</th>
                <th>Alamat</th>
                <th>Nama Ayah</th>
                <th>Nama Ibu</th>
                <th>Pekerjaan Ortu</th>
                <th>No. HP Ortu</th>
                <th>Tahun Masuk</th>
            </tr>
        </thead>
        <tbody>
            @foreach($siswas as $i => $s)
            @php
                $anggota = $s->angkatan
                    ->when($kelas, fn($c) => $c->where('kelas_id', $kelas->id))
                    ->when($periode, fn($p) => $p->where('periode_id', $periode->id))
                    ->first();
            @endphp
            <tr>
                <td class="num">{{ $anggota?->nomor_absen ?? $i+1 }}</td>
                <td>{{ $s->nis }}</td>
                <td>{{ $s->nisn }}</td>
                <td>{{ $s->nama_siswa }}</td>
                <td>{{ $s->jenis_kelamin }}</td>
                <td>{{ $s->tempat_lahir }}, {{ $s->tanggal_lahir?->format('d-m-Y') }}</td>
                <td>{{ $s->alamat }}</td>
                <td>{{ $s->nama_ayah }}</td>
                <td>{{ $s->nama_ibu }}</td>
                <td>{{ $s->pekerjaan_ortu }}</td>
                <td>{{ $s->no_hp_ortu }}</td>
                <td>{{ $s->tahun_masuk }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
