<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>RAB {{ $anggaran->tahun_ajaran }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #1e293b; }
        .kop { text-align: center; border-bottom: 3px double #0f766e; padding-bottom: 10px; margin-bottom: 14px; }
        .kop h1 { margin: 0; font-size: 15px; color: #0f766e; }
        .kop p { margin: 2px 0; font-size: 11px; }
        .judul { text-align: center; margin: 10px 0; }
        .judul h2 { margin: 0; font-size: 14px; }
        .meta { font-size: 10px; margin-bottom: 8px; }
        table { width: 100%; border-collapse: collapse; margin-top: 6px; }
        th, td { border: 1px solid #94a3b8; padding: 4px 6px; }
        th { background: #0f766e; color: #fff; font-size: 9px; text-transform: uppercase; }
        tr.group td { background: #ccfbf1; font-weight: bold; }
        .right { text-align: right; }
        .subtotal td { font-weight: bold; background: #f1f5f9; }
        .grand td { font-weight: bold; background: #d1fae5; }
        .ttd { margin-top: 30px; display: flex; justify-content: space-between; }
        .ttd .kol-ttd { width: 30%; text-align: center; font-size: 10px; }
        .ttd .spasi { height: 60px; }
    </style>
</head>
<body>
    <div class="kop">
        <h1>RENCANA ANGGARAN DAN BELANJA (RAB)</h1>
        <p>{{ $anggaran->nama }}</p>
        <p>{{ $anggaran->tahun_ajaran }}</p>
    </div>

    <div class="meta">
        Kelompok Belanja:
        @foreach($anggaran->kelompok as $k) {{ $k->kode }}.{{ $k->nama }}; @endforeach
    </div>

    @php
        $noRibu = function($v) { return number_format($v, 0, ',', '.'); };
        $fmt = function($v) { return rtrim(rtrim(number_format($v, 2, ',', '.'), '0'), ','); };
        $totalPagu = 0;
        $totalPemasukan = 0;
    @endphp

    <table>
        <thead>
            <tr>
                <th style="width:40px">No</th>
                <th style="width:60px">Kode</th>
                <th>Uraian Pos Belanja</th>
                <th style="width:55px">Volume</th>
                <th style="width:50px">Satuan</th>
                <th style="width:55px">Vol. 2</th>
                <th style="width:50px">Sat. 2</th>
                <th style="width:80px" class="right">Harga Satuan</th>
                <th style="width:90px" class="right">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @foreach($anggaran->kelompok as $kelompok)
            <tr class="group">
                <td></td>
                <td>{{ $kelompok->kode }}</td>
                <td colspan="6">{{ $kelompok->nama }}</td>
                <td class="right">{{ $noRibu($kelompok->pos->sum('jumlah')) }}</td>
            </tr>
            @foreach($kelompok->pos as $pos)
            <tr>
                <td class="right">{{ $no++ }}</td>
                <td>{{ $pos->kode }}</td>
                <td>{{ $pos->uraian }}</td>
                <td class="right">{{ $fmt($pos->volume) }}</td>
                <td>{{ $pos->satuan }}</td>
                <td class="right">{{ $pos->volume_2 !== null ? $fmt($pos->volume_2) : '' }}</td>
                <td>{{ $pos->satuan_2 }}</td>
                <td class="right">{{ $noRibu($pos->harga_satuan) }}</td>
                <td class="right">{{ $noRibu($pos->jumlah) }}</td>
            </tr>
            @endforeach
            @php $totalPagu += $kelompok->pos->sum('jumlah'); @endphp
            @endforeach
            <tr class="grand">
                <td colspan="8" class="right" style="padding-right:12px">JUMLAH ANGGARAN BELANJA</td>
                <td class="right">{{ $noRibu($totalPagu) }}</td>
            </tr>
        </tbody>
    </table>

    @if($anggaran->pemasukanRencana->count() > 0)
    <table style="margin-top:18px">
        <thead>
            <tr>
                <th style="width:40px">No</th>
                <th>Uraian Pemasukan</th>
                <th style="width:80px" class="right">Volume</th>
                <th style="width:60px">Satuan</th>
                <th style="width:90px" class="right">Harga Satuan</th>
                <th style="width:100px" class="right">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @foreach($anggaran->pemasukanRencana as $rencana)
            <tr>
                <td class="right">{{ $no++ }}</td>
                <td>{{ $rencana->uraian }}</td>
                <td class="right">{{ $fmt($rencana->volume) }}</td>
                <td>{{ $rencana->satuan }}</td>
                <td class="right">{{ $noRibu($rencana->harga_satuan) }}</td>
                <td class="right">{{ $noRibu($rencana->jumlah) }}</td>
            </tr>
            @php $totalPemasukan += $rencana->jumlah; @endphp
            @endforeach
            <tr class="grand">
                <td colspan="5" class="right" style="padding-right:12px">JUMLAH PEMASUKAN</td>
                <td class="right">{{ $noRibu($totalPemasukan) }}</td>
            </tr>
        </tbody>
    </table>
    @endif

    <div class="ttd">
        <div class="kol-ttd">Mengetahui,<br>Kepala Sekolah</div>
        <div class="kol-ttd">Bendahara,</div>
        <div class="kol-ttd">{{ $anggaran->tahun_ajaran }}<br>Ditetapkan,</div>
    </div>
</body>
</html>