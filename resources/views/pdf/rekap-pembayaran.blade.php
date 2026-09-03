<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekap Pembayaran</title>
    <style>
        @page { size: A4 landscape; margin: 12mm; }
        body { font-family: 'DejaVu Sans', sans-serif; color: #374151; font-size: 8pt; }
        .header { border-bottom: 2px solid #047857; padding-bottom: 8px; margin-bottom: 12px; text-align: center; }
        .header h1 { font-size: 13pt; margin: 0; color: #065f46; }
        .header p { margin: 3px 0 0; color: #6b7280; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #cbd5e1; padding: 5px; font-size: 8pt; }
        th { background: #ecfdf5; }
        .num { text-align: center; }
        .rp { text-align: right; }
        .lunas { color: #047857; font-weight: bold; }
        .belum { color: #e11d48; font-weight: bold; }
        .parsial { color: #d97706; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h1>REKAP PEMBAYARAN TAGIHAN</h1>
        <p>{{ $periode ? 'Periode: ' . $periode->tahun_ajaran : 'Semua Periode' }}</p>
    </div>
    <table>
        <thead>
            <tr>
                <th class="num">No</th>
                <th>NIS</th>
                <th>Nama Siswa</th>
                <th>Jenis Tagihan</th>
                <th class="rp">Nominal</th>
                <th class="rp">Dibayar</th>
                <th class="rp">Sisa</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalNominal = 0; $totalDibayar = 0; $totalSisa = 0;
            @endphp
            @foreach($tagihans as $t)
            @php
                $dibayar = $t->totalDibayar();
                $sisa = $t->sisa();
                $totalNominal += $t->nominal; $totalDibayar += $dibayar; $totalSisa += $sisa;
            @endphp
            <tr>
                <td class="num">{{ $loop->iteration }}</td>
                <td>{{ $t->siswa?->nis }}</td>
                <td>{{ $t->siswa?->nama_siswa }}</td>
                <td>{{ $t->jenisTagihan?->nama_tagihan }}</td>
                <td class="rp">{{ number_format($t->nominal,0,',','.') }}</td>
                <td class="rp">{{ number_format($dibayar,0,',','.') }}</td>
                <td class="rp">{{ number_format($sisa,0,',','.') }}</td>
                <td class="{{ $t->status }}">
                    {{ strtoupper($t->status) }}
                </td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background:#f0fdf4; font-weight:bold;">
                <td colspan="4" style="text-align:right;">TOTAL</td>
                <td class="rp">{{ number_format($totalNominal,0,',','.') }}</td>
                <td class="rp">{{ number_format($totalDibayar,0,',','.') }}</td>
                <td class="rp">{{ number_format($totalSisa,0,',','.') }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
