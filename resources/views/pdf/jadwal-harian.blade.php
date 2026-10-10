<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $judul }}</title>
    <style>
        @page { size: A4 landscape; margin: 12mm; }
        body { font-family: 'DejaVu Sans', sans-serif; color: #374151; font-size: 9pt; margin: 0; }
        .header { border-bottom: 2px solid #047857; padding-bottom: 8px; margin-bottom: 12px; }
        .header h1 { font-size: 14pt; margin: 0; color: #065f46; }
        .header p { margin: 3px 0 0; color: #6b7280; font-size: 9pt; }
        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        th, td { border: 1px solid #cbd5e1; padding: 6px 5px; vertical-align: top; }
        th { background-color: #047857; color: #ffffff; font-size: 9pt; text-align: center; text-transform: uppercase; letter-spacing: 0.5px; }
        th.blok { background-color: #065f46; }
        td.blok { background-color: #ecfdf5; text-align: center; font-weight: bold; font-size: 11pt; color: #065f46; }
        td.blok .jam { display: block; font-size: 7pt; font-weight: normal; color: #6b7280; }
        .mapel { font-weight: bold; color: #111827; font-size: 9pt; }
        .sub { font-size: 8pt; color: #047857; margin-top: 2px; }
        .sub-kelas { color: #4338ca; }
        .kosong { color: #cbd5e1; font-style: italic; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $judul }}</h1>
        <p>Tahun Ajaran {{ $tahunAjaran ?? '-' }} &bull; Master Jadwal Harian &mdash; MUMARIS</p>
    </div>

    <table>
        <thead>
            <tr>
                <th class="blok" style="width: 70px;">Blok</th>
                @foreach($hari_list as $hari)
                    <th>{{ $hari }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($opsiBlokJam as $blok)
                @php
                    $jamMulaiBlok = min($blok['jam_list']);
                    $waktuLabel = '';
                    if (preg_match('/\((.*)\)/', $blok['label'], $m)) {
                        $waktuLabel = $m[1];
                    }
                @endphp
                <tr>
                    <td class="blok">
                        {{ $blok['key'] }}
                        @if($waktuLabel)
                            <span class="jam">{{ $waktuLabel }}</span>
                        @endif
                    </td>
                    @foreach($hari_list as $hari)
                        @php
                            $batasJamHariIni = $max_jam_per_hari[$hari] ?? 10;
                        @endphp
                        @if($jamMulaiBlok > $batasJamHariIni)
                            <td></td>
                        @else
                            @php $j = $jadwal_matriks[$hari][$blok['key']] ?? null; @endphp
                            @if($j)
                                <td>
                                    <span class="mapel">{{ $j->pelajaran->nama_pelajaran ?? '-' }}</span>
                                    @if($mode == 'kelas')
                                        <div class="sub">{{ $j->guru->nama_guru ?? 'Tanpa Guru' }}</div>
                                    @else
                                        <div class="sub sub-kelas">Kelas {{ $j->kelas->nama_kelas ?? '-' }}</div>
                                    @endif
                                </td>
                            @else
                                <td class="kosong">&mdash;</td>
                            @endif
                        @endif
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
