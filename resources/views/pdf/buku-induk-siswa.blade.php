<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Buku Induk {{ $siswa->nama_siswa }}</title>
    <style>
        @page { size: A4; margin: 15mm; }
        body { font-family: 'DejaVu Sans', sans-serif; color: #374151; font-size: 10pt; }
        .header { border-bottom: 2px solid #1d4ed8; padding-bottom: 8px; margin-bottom: 14px; text-align: center; }
        .header h1 { font-size: 14pt; margin: 0; color: #1e3a8a; }
        h3 { color: #1e3a8a; border-bottom: 1px solid #cbd5e1; padding-bottom: 4px; }
        table.detail { width: 100%; border-collapse: collapse; }
        table.detail td { padding: 4px 6px; vertical-align: top; }
        table.detail td.label { width: 180px; color: #6b7280; font-weight: bold; }
        table.detail td.dots { border-bottom: 1px dotted #94a3b8; }
        .riwayat { width: 100%; border-collapse: collapse; margin-top: 8px; }
        .riwayat th, .riwayat td { border: 1px solid #cbd5e1; padding: 6px; font-size: 9pt; }
        .riwayat th { background: #eef2ff; }
        .average { font-weight: bold; color: #1d4ed8; }
    </style>
</head>
<body>
    <div class="header">
        <h1>BUKU INDUK MURID</h1>
        <p>Nomor Induk Siswa: {{ $siswa->nis }}</p>
    </div>

    <h3>A. Identitas Diri</h3>
    <table class="detail">
        <tr><td class="label">Nama Lengkap</td><td class="dots">{{ $siswa->nama_siswa }}</td></tr>
        <tr><td class="label">NIS / NISN</td><td class="dots">{{ $siswa->nis }} / {{ $siswa->nisn }}</td></tr>
        <tr><td class="label">Jenis Kelamin</td><td class="dots">{{ $siswa->jenis_kelamin == 'L' ? 'Laki-laki' : ($siswa->jenis_kelamin == 'P' ? 'Perempuan' : '-') }}</td></tr>
        <tr><td class="label">Tempat, Tanggal Lahir</td><td class="dots">{{ $siswa->tempat_lahir }}, {{ $siswa->tanggal_lahir?->format('d-m-Y') }}</td></tr>
        <tr><td class="label">Alamat</td><td class="dots">{{ $siswa->alamat }}</td></tr>
        <tr><td class="label">Tahun Masuk</td><td class="dots">{{ $siswa->tahun_masuk }}</td></tr>
    </table>

    <h3 style="margin-top:20px;">B. Data Orang Tua</h3>
    <table class="detail">
        <tr><td class="label">Nama Ayah</td><td class="dots">{{ $siswa->nama_ayah }}</td></tr>
        <tr><td class="label">Nama Ibu</td><td class="dots">{{ $siswa->nama_ibu }}</td></tr>
        <tr><td class="label">Pekerjaan Orang Tua</td><td class="dots">{{ $siswa->pekerjaan_ortu }}</td></tr>
        <tr><td class="label">No. HP Orang Tua</td><td class="dots">{{ $siswa->no_hp_ortu }}</td></tr>
    </table>

    <h3 style="margin-top:20px;">C. Riwayat Kelas / Periode</h3>
    <table class="riwayat">
        <thead>
            <tr><th>No</th><th>Kelas</th><th>Tahun Ajaran</th><th>Semester</th><th>Status</th></tr>
        </thead>
        <tbody>
            @foreach($siswa->angkatan->sortBy('periode.tahun_ajaran') as $i => $a)
            <tr>
                <td>{{ $i+1 }}</td>
                <td>{{ $a->kelas?->nama_kelas }}</td>
                <td>{{ $a->periode?->tahun_ajaran }}</td>
                <td>{{ $a->periode?->semester }}</td>
                <td>{{ $a->status }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <h3 style="margin-top:20px;">D. Ringkasan Hasil Belajar (Nilai Akhir/IP)</h3>
    @php
        $siswas2 = $siswa->nilais;
        $byPeriode = $siswas2->groupBy('periode_id');
    @endphp
    @forelse($byPeriode as $pid => $nilaiSet)
        @php $rata = $nilaiSet->avg('nilai_akhir'); @endphp
        <p style="font-weight:bold; margin:10px 0 4px;">Periode {{ $nilaiSet->first()->periode?->tahun_ajaran }} — Rata-rata: <span class="average">{{ number_format($rata,2) }}</span></p>
        <table class="riwayat">
            <thead><tr><th>No</th><th>Pelajaran</th><th>UTS</th><th>UAS</th><th>Nilai Akhir</th><th>Predikat</th></tr></thead>
            <tbody>
                @foreach($nilaiSet as $i => $n)
                <tr>
                    <td>{{ $i+1 }}</td>
                    <td>{{ $n->pelajaran?->nama_pelajaran }}</td>
                    <td>{{ $n->nilai_uts }}</td>
                    <td>{{ $n->nilai_uas }}</td>
                    <td class="average">{{ $n->nilai_akhir }}</td>
                    <td>{{ $n->predikat }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @empty
        <p>Belum ada data nilai.</p>
    @endforelse
</body>
</html>
