<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekap Kebendaharaan {{ $periode->tahun_ajaran }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #1e293b; }
        .kop { text-align: center; border-bottom: 3px double #0f766e; padding-bottom: 8px; margin-bottom: 12px; }
        .kop h1 { margin: 0; font-size: 14px; color: #0f766e; }
        .kop p { margin: 2px 0; font-size: 10px; }
        .ringkas { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        .ringkas td { border: 1px solid #cbd5e1; padding: 5px 8px; }
        .ringkas .lbl { background: #f1f5f9; font-weight: bold; width: 25%; }
        .ringkas .val { font-weight: bold; }
        table.data { width: 100%; border-collapse: collapse; margin: 10px 0; }
        table.data th, table.data td { border: 1px solid #94a3b8; padding: 3px 5px; }
        table.data th { background: #0f766e; color: #fff; font-size: 8px; text-transform: uppercase; }
        .grup { background: #ccfbf1; font-weight: bold; }
        .tot { background: #d1fae5; font-weight: bold; }
        .right { text-align: right; }
        .judul-seksi { font-size: 11px; font-weight: bold; margin: 14px 0 2px; color: #0f766e; border-bottom: 2px solid #0f766e; }
        .ttd { margin-top: 30px; display: flex; justify-content: space-between; }
        .ttd .kol { width: 30%; text-align: center; font-size: 9px; }
        .ttd .spasi { height: 55px; }
    </style>
</head>
<body>
    @php
        $fmt = fn($v) => number_format($v, 0, ',', '.');
        $d = $data;
    @endphp
    <div class="kop">
        <h1>REKAP KEBENDAHARAAN</h1>
        <p>{{ $periode->tahun_ajaran }}</p>
    </div>

    <table class="ringkas">
        <tr>
            <td class="lbl">Total Pemasukan</td><td class="val">Rp {{ $fmt($d['pemasukanTotal']) }}</td>
            <td class="lbl">Total Pengeluaran</td><td class="val">Rp {{ $fmt($d['realisasiTotal']) }}</td>
            <td class="lbl">Saldo Kas</td><td class="val">Rp {{ $fmt($d['saldo']) }}</td>
        </tr>
        <tr>
            <td class="lbl">Dana Dicairkan</td><td class="val" colspan="5">Rp {{ $fmt($d['pencairanDibayar']) }}</td>
        </tr>
    </table>

    <div class="judul-seksi">A. REKAP BELANJA PER POS ANGGARAN</div>
    @if($d['anggaran'])
    <table class="data">
        <thead>
            <tr>
                <th style="width:30px">No</th>
                <th>Kelompok / Pos</th>
                <th class="right">Pagu</th>
                <th class="right">Realisasi</th>
                <th class="right">Sisa</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; $tpagu = 0; $treal = 0; @endphp
            @foreach($d['rekapBelanja'] as $kel)
            <tr class="grup">
                <td></td>
                <td>{{ $kel->kode }}. {{ $kel->nama }}</td>
                <td class="right">{{ $fmt($kel->pagu) }}</td>
                <td class="right">{{ $fmt($kel->realisasi) }}</td>
                <td class="right">{{ $fmt(max(0, $kel->pagu - $kel->realisasi)) }}</td>
            </tr>
            @foreach($kel->rows as $row)
            <tr>
                <td class="right">{{ $no++ }}</td>
                <td>{{ $row->kode }} — {{ $row->uraian }}</td>
                <td class="right">{{ $fmt($row->pagu) }}</td>
                <td class="right">{{ $fmt($row->realisasi) }}</td>
                <td class="right">{{ $fmt($row->sisa) }}</td>
            </tr>
            @php $tpagu += $row->pagu; $treal += $row->realisasi; @endphp
            @endforeach
            @endforeach
            <tr class="tot">
                <td colspan="2" class="right">TOTAL ANGGARAN BELANJA</td>
                <td class="right">{{ $fmt($tpagu) }}</td>
                <td class="right">{{ $fmt($treal) }}</td>
                <td class="right">{{ $fmt(max(0, $tpagu - $treal)) }}</td>
            </tr>
        </tbody>
    </table>
    @else
    <p>Belum ada anggaran.</p>
    @endif

    <div class="judul-seksi">B. REKAP PEMASUKAN</div>
    <table class="data">
        <thead>
            <tr>
                <th style="width:30px">No</th>
                <th>Uraian</th>
                <th class="right">Rencana</th>
                <th class="right">Realisasi</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @foreach($d['rekapPemasukan'] as $rp)
            <tr>
                <td class="right">{{ $no++ }}</td>
                <td>{{ $rp->uraian }}</td>
                <td class="right">{{ $fmt($rp->rencana) }}</td>
                <td class="right">{{ $fmt($rp->realisasi) }}</td>
            </tr>
            @endforeach
            @if($d['pemasukanBarang'] > 0)
            <tr>
                <td class="right">{{ $no++ }}</td>
                <td>Setoran Toko Buku (otomatis)</td>
                <td class="right">—</td>
                <td class="right">{{ $fmt($d['pemasukanBarang']) }}</td>
            </tr>
            @endif
            @foreach($d['sisaPemasukan'] as $sp)
            <tr>
                <td class="right">{{ $no++ }}</td>
                <td>{{ $sp->uraian }}</td>
                <td class="right">—</td>
                <td class="right">{{ $fmt($sp->jumlah) }}</td>
            </tr>
            @endforeach
            <tr class="tot">
                <td colspan="3" class="right">TOTAL PEMASUKAN</td>
                <td class="right">{{ $fmt($d['pemasukanTotal']) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="judul-seksi">C. POSISI TOKO BUKU</div>
    <table class="data">
        <tr>
            <th>Nilai Stok</th><td class="right">{{ $fmt($d['nilaiStok']) }}</td>
            <th>Piutang Wali Kelas</th><td class="right">{{ $fmt($d['piutangWaliKelas']) }}</td>
            <th>Total Setoran</th><td class="right">{{ $fmt($d['totalSetoran']) }}</td>
        </tr>
        <tr>
            <th>Sisa Pinjaman</th><td class="right">{{ $fmt($d['sisaPinjaman']) }}</td>
            <th>Total Penjualan</th><td class="right">{{ $fmt($d['totalPenjualan']) }}</td>
            <th>Laba Kotor</th><td class="right">{{ $fmt($d['labaKotor']) }}</td>
        </tr>
    </table>

    <div class="ttd">
        <div class="kol">Mengetahui,<br>Kepala Sekolah</div>
        <div class="kol">Bendahara,</div>
        <div class="kol">{{ $periode->tahun_ajaran }}<br>Direkap,</div>
    </div>
</body>
</html>