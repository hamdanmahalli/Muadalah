<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Kehadiran - {{ $agenda->nama_kegiatan }}</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 11px; color: #333; line-height: 1.3; }
        .header { text-align: center; margin-bottom: 12px; border-bottom: 1.5px solid #333; padding-bottom: 8px; }
        .title { font-size: 15px; font-weight: bold; margin: 0; text-transform: uppercase; }
        .subtitle { font-size: 10px; color: #555; margin-top: 3px; }
        .stats { margin-bottom: 10px; font-size: 10px; background: #f8fafc; padding: 6px; border: 1px solid #e2e8f0; }
        h3 { font-size: 11px; margin: 12px 0 5px 0; text-transform: uppercase; border-left: 3px solid #4f46e5; padding-left: 5px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        th { background-color: #f1f5f9; color: #333; font-weight: bold; text-align: left; border: 1px solid #cbd5e1; padding: 5px 6px; font-size: 9px; text-transform: uppercase; }
        td { border: 1px solid #cbd5e1; padding: 4px 6px; vertical-align: middle; }
        
        /* Gaya Khusus Tabel 3 Kolom untuk Belum Hadir */
        .table-grid { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .table-grid td { border: 1px solid #cbd5e1; padding: 5px 6px; font-size: 10px; }
        
        .text-center { text-align: center; }
        .text-success { color: #059669; font-weight: bold; }
        .text-warning { color: #d97706; font-weight: bold; }
        .text-danger { color: #dc2626; font-weight: bold; }
    </style>
</head>
<body>

    <div class="header">
        <h1 class="title">Laporan Kehadiran Kegiatan</h1>
        <p class="subtitle"><strong>{{ $agenda->nama_kegiatan }}</strong> | Tanggal: {{ \Carbon\Carbon::parse($agenda->tanggal)->translatedFormat('d F Y') }} | Pukul: {{ \Carbon\Carbon::parse($agenda->jam_mulai)->format('H:i') }} WIB</p>
    </div>

    <div class="stats">
        <strong>Ringkasan Kehadiran:</strong> 
        Total Guru: {{ count($dataHadir) + count($dataBelumHadir) }} orang | 
        Tercatat (Hadir/Izin/Sakit): {{ count($dataHadir) }} orang | 
        Belum Hadir (Alpa): {{ count($dataBelumHadir) }} orang
    </div>

    <h3>A. Data Tercatat (Hadir / Izin / Sakit)</h3>
    <table>
        <thead>
            <tr>
                <th width="5%" class="text-center">NO</th>
                <th width="35%">NAMA GURU</th>
                <th width="12%" class="text-center">STATUS</th>
                <th width="18%" class="text-center">WAKTU / METODE</th>
                <th width="30%">KETERANGAN</th>
            </tr>
        </thead>
        <tbody>
            @if(count($dataHadir) > 0)
                @foreach($dataHadir as $i => $hadir)
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td><strong>{{ $hadir->guru->nama_guru }}</strong></td>
                    <td class="text-center">
                        @if($hadir->status == 'Hadir')
                            <span class="text-success">{{ $hadir->status }}</span>
                        @elseif($hadir->status == 'Izin')
                            <span class="text-warning">{{ $hadir->status }}</span>
                        @else
                            <span class="text-danger">{{ $hadir->status }}</span>
                        @endif
                    </td>
                    <td class="text-center">
                        {{ \Carbon\Carbon::parse($hadir->waktu_hadir)->format('H:i') }}<br>
                        <span style="font-size: 8px; color: #666;">({{ $hadir->metode }})</span>
                    </td>
                    <td>{{ $hadir->keterangan ?? '-' }}</td>
                </tr>
                @endforeach
            @else
                <tr><td colspan="5" class="text-center" style="padding: 10px;">Belum ada data masuk.</td></tr>
            @endif
        </tbody>
    </table>

    <h3>B. Daftar Belum Hadir (Alpa)</h3>
    @if(count($dataBelumHadir) > 0)
        <table class="table-grid">
            <tbody>
                @php
                    // Memecah array guru belum hadir menjadi potongan-potongan berisi 3 kolom
                    $chunks = array_chunk($dataBelumHadir, 3);
                    $counter = 1;
                @endphp
                @foreach($chunks as $row)
                    <tr>
                        @foreach($row as $guruBelum)
                            <td width="5%" class="text-center" style="background: #f8fafc; font-weight: bold;">{{ $counter++ }}</td>
                            <td width="28%">{{ $guruBelum->nama_guru }}</td>
                        @endforeach
                        
                        {{-- Jika kolom terakhir dalam baris kurang dari 3, buat sel kosong agar layout tetap rapi --@@ --}}
                        @for($k = count($row); $k < 3; $k++)
                            <td width="5%" style="background: #f8fafc;"></td>
                            <td width="28%"></td>
                        @endfor
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p style="text-align: center; font-style: italic; color: #666; padding: 5px;">Semua guru telah tercatat hadir/izin.</p>
    @endif

</body>
</html>