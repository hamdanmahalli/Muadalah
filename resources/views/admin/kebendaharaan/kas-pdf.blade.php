<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Buku Kas {{ $buku->label }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #1e293b; }
        .kop { text-align: center; border-bottom: 3px double #0f766e; padding-bottom: 8px; margin-bottom: 12px; }
        .kop h1 { margin: 0; font-size: 14px; color: #0f766e; }
        .kop p { margin: 2px 0; font-size: 10px; }
        .ringkas { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        .ringkas td { border: 1px solid #cbd5e1; padding: 5px 8px; }
        .ringkas .lbl { background: #f1f5f9; font-weight: bold; width: 22%; }
        .ringkas .val { font-weight: bold; }
        table.data { width: 100%; border-collapse: collapse; margin: 10px 0; }
        table.data th, table.data td { border: 1px solid #94a3b8; padding: 3px 5px; }
        table.data th { background: #0f766e; color: #fff; font-size: 8px; text-transform: uppercase; }
        .grup { background: #f1f5f9; font-weight: bold; }
        .tot { background: #d1fae5; font-weight: bold; }
        .right { text-align: right; }
        .masuk { color: #047857; }
        .keluar { color: #be123c; }
        .judul-seksi { font-size: 11px; font-weight: bold; margin: 14px 0 2px; color: #0f766e; border-bottom: 2px solid #0f766e; }
        .ttd { margin-top: 34px; width: 100%; }
        .ttd td { width: 33%; text-align: center; font-size: 9px; vertical-align: top; }
        .ttd .spasi { height: 55px; }
        .ttd .nama { font-weight: bold; text-decoration: underline; }
        .catatan { font-size: 8px; color: #64748b; margin-top: 6px; }
    </style>
</head>
<body>
    @php
        $fmt = fn($v) => number_format($v, 0, ',', '.');
        $saldoAkhir = $uangMasuk + $pemasukanTercatat - $uangKeluar;
        $ledger = $harian->sortKeys();
    @endphp

    <div class="kop">
        <h1>BUKU KAS</h1>
        <p>Bulan {{ ucwords(strtolower(bulan_fiskal_label($buku->bulan_fiskal))) }} {{ $buku->tahun_fiskal }}</p>
        <p>Periode {{ $periode->tahun_ajaran }} &middot; {{ ucfirst($periode->semester) }}</p>
    </div>

    <table class="ringkas">
        <tr>
            <td class="lbl">Uang Masuk (SPP)</td><td class="val">Rp {{ $fmt($uangMasuk) }}</td>
            <td class="lbl">Pemasukan Manual</td><td class="val">Rp {{ $fmt($pemasukanTercatat) }}</td>
        </tr>
        <tr>
            <td class="lbl">Uang Keluar</td><td class="val">Rp {{ $fmt($uangKeluar) }}</td>
            <td class="lbl">Saldo Akhir Kas</td><td class="val">Rp {{ $fmt($saldoAkhir) }}</td>
        </tr>
        <tr>
            <td class="lbl">Status</td>
            <td class="val" colspan="3">
                @if($buku->catatan)
                    Catatan pengaju: {{ $buku->catatan }}<br>
                @endif
                Dilaporkan {{ $buku->dilaporkan_at?->format('d M Y H:i') }}@if($buku->pelapor) oleh {{ $buku->pelapor->name }}@endif<br>
                @if($buku->diterima_bendahara_at)
                    Diterima bendahara {{ $buku->diterima_bendahara_at->format('d M Y H:i') }}@if($buku->penerima) oleh {{ $buku->penerima->name }}@endif<br>
                @endif
                @if($buku->dikembalikan_at)
                    Dikembalikan {{ $buku->dikembalikan_at->format('d M Y H:i') }}@if($buku->pengembali) oleh {{ $buku->pengembali->name }}@endif
                    @if($buku->alasan_dikembalikan) &mdash; {{ $buku->alasan_dikembalikan }}@endif<br>
                @endif
                @if($buku->disahkan_at)
                    Disahkan {{ $buku->disahkan_at->format('d M Y H:i') }}@if($buku->pengesah) oleh {{ $buku->pengesah->name }}@endif
                @else
                    Status: {{ match($buku->status) {
                        'dilaporkan' => 'Menunggu Validasi Bendahara',
                        'diterima' => 'Diterima Bendahara (Menunggu Pengesahan)',
                        'dikembalikan' => 'Dikembalikan ke Pengaju',
                        default => 'Disahkan',
                    } }}
                @endif
            </td>
        </tr>
    </table>

    <div class="judul-seksi">RINCIAN TRANSAKSI &amp; SALDO BERJALAN</div>
    <table class="data">
        <thead>
            <tr>
                <th style="width:26px">No</th>
                <th style="width:70px">Tanggal</th>
                <th>Sumber / Uraian</th>
                <th class="right" style="width:85px">Masuk</th>
                <th class="right" style="width:85px">Keluar</th>
                <th class="right" style="width:90px">Saldo</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; $saldo = 0; @endphp
            @forelse($ledger as $tanggal => $rows)
            @foreach($rows as $r)
            @php
                $masuk = $r['arah'] === 'masuk' ? (float) $r['nominal'] : 0;
                $keluar = $r['arah'] === 'keluar' ? (float) $r['nominal'] : 0;
                $saldo += $masuk - $keluar;
                $tgl = $tanggal ? \Illuminate\Support\Carbon::parse($tanggal)->format('d/m/Y') : '-';
                $keterangan = trim(($r['kode'] ? $r['kode'] . ' · ' : '') . $r['judul']
                    . ($r['pos'] ? ' — ' . $r['pos'] : '')
                    . ($r['uraian'] ? ' (' . $r['uraian'] . ')' : ''));
            @endphp
            <tr>
                <td class="right">{{ $no++ }}</td>
                <td>{{ $tgl }}</td>
                <td>{{ $keterangan }}</td>
                <td class="right masuk">{{ $masuk > 0 ? $fmt($masuk) : '—' }}</td>
                <td class="right keluar">{{ $keluar > 0 ? $fmt($keluar) : '—' }}</td>
                <td class="right">{{ $fmt($saldo) }}</td>
            </tr>
            @endforeach
            @empty
            <tr><td colspan="6" style="text-align:center">Tidak ada transaksi pada bulan ini.</td></tr>
            @endforelse
            <tr class="tot">
                <td colspan="3" class="right">TOTAL</td>
                <td class="right">{{ $fmt($uangMasuk + $pemasukanTercatat) }}</td>
                <td class="right">{{ $fmt($uangKeluar) }}</td>
                <td class="right">{{ $fmt($saldoAkhir) }}</td>
            </tr>
        </tbody>
    </table>
    <p class="catatan">Catatan: Pemasukan manual dicatat terpisah dari dana SPP dan ikut dihitung pada saldo kas.</p>

    <table class="ttd">
        <tr>
            <td>Pengaju/Pelapor,<div class="spasi"></div><span class="nama">{{ $buku->pelapor->name ?? ' ' }}</span></td>
            <td>Bendahara,<div class="spasi"></div><span class="nama">{{ $buku->penerima->name ?? ' ' }}</span>
                @if($buku->diterima_bendahara_at)<br>{{ $buku->diterima_bendahara_at->format('d/m/Y') }}@endif
            </td>
            <td>Disahkan,<br>Pimpinan<div class="spasi"></div>
                <span class="nama">{{ $buku->pengesah->name ?? ' ' }}</span>
                @if($buku->disahkan_at)<br>{{ $buku->disahkan_at->format('d/m/Y') }}@endif
            </td>
        </tr>
    </table>
</body>
</html>
