<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Raport {{ $siswa->nama_siswa }}</title>
    <style>
        @page { size: A4; margin: 15mm; }
        body { font-family: 'DejaVu Sans', sans-serif; color: #374151; font-size: 10pt; }
        .kops { text-align: center; border-bottom: 2px solid #1d4ed8; padding-bottom: 8px; margin-bottom: 14px; }
        .kops h1 { font-size: 14pt; margin: 0; color: #1e3a8a; }
        .kops p { margin: 2px 0; font-size: 9pt; color: #6b7280; }
        table.detail { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
        table.detail td { padding: 3px 8px; vertical-align: top; font-size: 9.5pt; }
        table.detail td.label { width: 170px; color: #6b7280; }
        .nilai { width: 100%; border-collapse: collapse; }
        .nilai th, .nilai td { border: 1px solid #cbd5e1; padding: 6px; font-size: 9pt; text-align: center; }
        .nilai th { background: #eef2ff; }
        .nilai td.l { text-align: left; }
        .rata { font-weight: bold; color: #1d4ed8; }
        .ttd { margin-top: 40px; }
        .ttd td { width: 33%; text-align: center; }
    </style>
</head>
<body>
    <div class="kops">
        <h1>LAPORAN HASIL BELAJAR SISWA</h1>
        <p>Tahun Pelajaran {{ $periode?->tahun_ajaran }} — Semester {{ $periode?->semester }}</p>
    </div>

    <table class="detail">
        <tr><td class="label">Nama Siswa</td><td>: {{ $siswa->nama_siswa }}</td>
            <td class="label">NIS</td><td>: {{ $siswa->nis }}</td></tr>
        <tr><td class="label">Kelas</td><td>: {{ $kelas?->nama_kelas }}</td>
            <td class="label">NISN</td><td>: {{ $siswa->nisn }}</td></tr>
    </table>

    <table class="nilai">
        <thead>
            <tr><th>No</th><th style="text-align:left">Mata Pelajaran</th><th>Nilai UTS</th><th>Nilai UAS</th><th>Nilai Akhir</th><th>Predikat</th></tr>
        </thead>
        <tbody>
            @foreach($nilai as $i => $n)
            <tr>
                <td>{{ $i+1 }}</td>
                <td class="l">{{ $n->pelajaran?->nama_pelajaran }}</td>
                <td>{{ $n->nilai_uts }}</td>
                <td>{{ $n->nilai_uas }}</td>
                <td class="rata">{{ $n->nilai_akhir }}</td>
                <td>{{ $n->predikat }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" style="text-align:right; font-weight:bold;">Rata-rata</td>
                <td class="rata">{{ number_format($rataRata,2) }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <table class="detail" style="margin-top:14px;">
        <tr><td class="label">Jumlah Mapel</td><td>: {{ $jumlah }}</td>
            <td class="label">Nilai Tertinggi</td><td>: {{ $tertinggi }}</td></tr>
        <tr><td class="label">Nilai Terendah</td><td>: {{ $terendah }}</td>
            <td class="label">Kehadiran (H/S/I/A)</td><td>: {{ $kehadiran['hadir'] ?? 0 }} / {{ $kehadiran['sakit'] ?? 0 }} / {{ $kehadiran['izin'] ?? 0 }} / {{ $kehadiran['alpha'] ?? 0 }}</td></tr>
    </table>

    <table class="ttd nilai">
        <tr>
            <td></td>
            <td>{{ now()->format('d F Y') }}<br>Wali Kelas</td>
            <td>Kepala Sekolah</td>
        </tr>
        <tr style="height:70px;"><td></td><td></td><td></td></tr>
        <tr>
            <td></td>
            <td>( {{ $kelas?->waliKelas?->nama_guru ?? '___________' }} )</td>
            <td>( ___________ )</td>
        </tr>
    </table>
</body>
</html>
