<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Absen Mingguan {{ $kelas->nama_kelas }}</title>
    <style>
        @page { size: A4 landscape; margin: 12mm; }
        body { font-family: 'DejaVu Sans', sans-serif; color: #374151; font-size: 9pt; }
        .header { border-bottom: 2px solid #1d4ed8; padding-bottom: 8px; margin-bottom: 12px; }
        .header h1 { font-size: 14pt; margin: 0; color: #1e3a8a; }
        .header p { margin: 3px 0 0; color: #6b7280; font-size: 9pt; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #cbd5e1; padding: 6px; text-align: center; font-size: 8.5pt; }
        th { background: #eef2ff; font-weight: bold; }
        .nama { text-align: left; }
        .ket { font-size: 7.5pt; }
    </style>
</head>
<body>
    <div class="header">
        <h1>ABSENSI MINGGUAN SISWA</h1>
        <p>Kelas: <strong>{{ $kelas->nama_kelas }}</strong> ({{ $kelas->tingkat }}) &nbsp;|&nbsp; Periode: {{ $tanggalAwal }} s/d {{ $tanggalAkhir }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width:30px;">No</th>
                <th class="nama" style="width:150px;">Nama Siswa</th>
                <th style="width:50px;">NIS</th>
                <th>Sabtu</th>
                <th>Ahad</th>
                <th>Senin</th>
                <th>Selasa</th>
                <th>Rabu</th>
                <th>Kamis</th>
                <th>Jum'at</th>
                <th>Hadir</th>
                <th>Alpha</th>
                <th>Ket</th>
            </tr>
        </thead>
        <tbody>
            @php
                // Bangun daftar hari dari rentang tanggal yang diminta
                $hariList = ['Sabtu','Ahad','Senin','Selasa','Rabu','Kamis','Jumat'];
                $rentang = [];
                $d = \Carbon\Carbon::parse($tanggalAwal);
                $akhir = \Carbon\Carbon::parse($tanggalAkhir);
                while ($d->lte($akhir)) {
                    $rentang[] = $d->copy();
                    $d->addDay();
                }
            @endphp
            @foreach($siswas as $i => $s)
            @php
                $kh = ($kehadiran[$s->id] ?? collect())->keyBy('tanggal');
                $hadir = count($kh->where('status','hadir'));
                $alpha = count($kh->where('status','alpha'));
            @endphp
            <tr>
                <td>{{ $i+1 }}</td>
                <td class="nama">{{ $s->nama_siswa }}</td>
                <td>{{ $s->nis }}</td>
                @foreach($rentang as $hari)
                    <td>
                        @if(isset($kh[$hari->format('Y-m-d')]))
                            @switch($kh[$hari->format('Y-m-d')]->status)
                                @case('hadir') H @break
                                @case('sakit') S @break
                                @case('izin') I @break
                                @default A
                            @endswitch
                        @else
                            &nbsp;
                        @endif
                    </td>
                @endforeach
                <td>{{ $hadir }}</td>
                <td>{{ $alpha }}</td>
                <td class="ket"></td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
