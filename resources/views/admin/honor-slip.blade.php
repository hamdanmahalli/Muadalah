<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Slip Bisyaroh</title>
    <style>
        @page { size: 210mm 330mm; margin: 5mm; }
        body { font-family: 'DejaVu Sans', sans-serif; color: #000; font-size: 7pt; margin: 0; }

        .halaman { width: 100%; border-collapse: collapse; page-break-after: always; }
        .halaman.last { page-break-after: auto; }
        .cell { width: 33.33%; vertical-align: top; padding: 1.5mm; }

        table.slip {
            width: 100%;
            border-collapse: collapse;
            border: 1.2pt solid #000;
            page-break-inside: avoid;
        }
        table.slip td { border-collapse: collapse; padding: 0.5mm 1.4mm; vertical-align: top; text-align: left; }
        table.slip td.lbl { width: 46%; color: #1e293b; }
        table.slip td.val { font-weight: bold; }
        table.slip td.uang { text-align: right; white-space: nowrap; }
        table.slip td.bagian {
            background: #f1f5f9; font-weight: bold; text-align: center;
            letter-spacing: .5px; border-bottom: 1pt solid #000; padding: .4mm 1.4mm !important;
        }
        table.slip td.bisyaroh { background: #fee2e2; color: #7f1d1d; }

        .kop { text-align: center; border-bottom: 1.4pt solid #000; padding-bottom: 1mm !important; }
        .kop .k1 { font-size: 8.5pt; font-weight: bold; }
        .kop .k2 { font-size: 7.5pt; font-weight: bold; margin-top: 1px; }
        .kop .k3 { font-size: 6.5pt; margin-top: 1px; }

        .meta { padding-top: 1mm !important; }
        .nama { font-size: 8pt; font-weight: bold; }

        .total { font-size: 8pt; font-weight: bold; border-top: 1pt solid #000; background: #f1f5f9; }
        .total span { float: right; }

        .nb { font-size: 6.5pt; padding-top: .8mm !important; }

        .ttd { text-align: center; padding-top: 1.2mm !important; }
        .ttd .cap { font-size: 7pt; padding: .7mm 0 3.5mm 0; }
        .ttd .nama { font-size: 8pt; font-weight: bold; border-top: 1pt solid #000; padding: .4mm 5mm; }
    </style>
</head>
<body>
    @php
        $fmtUang = fn ($n) => $n > 0 ? 'Rp ' . number_format((int) $n, 0, ',', '.') : '-';
    @endphp

    @foreach($details->chunk(6) as $i => $halaman)
    <table class="halaman {{ $loop->last ? 'last' : '' }}">
        @foreach($halaman->chunk(3) as $baris)
        <tr>
            @foreach($baris as $d)
            <td class="cell">
                <table class="slip">
                    <tr>
                        <td colspan="2" class="kop">
                            <div class="k1">MUADALAH SALAFIYAH WUSTHO</div>
                            <div class="k2">PONDOK PESANTREN MAQNA'UL ULUM</div>
                            <div class="k3">TAHUN PELAJARAN {{ str_replace('/', '-', $tahunAjaran) }}</div>
                        </td>
                    </tr>
                    <tr><td colspan="2" class="meta">Bisyaroh Bulan : <b>{{ $bulanIndonesia[$periode->bulan] ?? $periode->bulan }}</b></td></tr>
                    <tr><td colspan="2" class="nama">{{ $d->guru->nama_guru }}</td></tr>
                    <tr><td class="lbl">NIG</td><td class="val">{{ $d->guru->nig ?: '-' }}</td></tr>

                    <tr><td colspan="2" class="bagian">ABSENSI</td></tr>
                    <tr><td class="lbl">Jam Wajib Dalam Sebulan</td><td class="val">{{ (int) $d->jam_wajib }}</td></tr>
                    <tr><td class="lbl">Alpa</td><td class="val">{{ (int) $d->alpa }}</td></tr>
                    <tr><td class="lbl">Izin</td><td class="val">{{ (int) $d->izin }}</td></tr>
                    <tr><td class="lbl">Sakit</td><td class="val">{{ (int) $d->sakit }}</td></tr>
                    <tr><td class="lbl">Piket</td><td class="val">{{ (int) $d->piket_jam }}</td></tr>
                    <tr><td class="lbl">Realita Jam Masuk Dalam Sebulan</td><td class="val">{{ (int) $d->realita_jam }}</td></tr>
                    <tr><td class="lbl">Prosentase Mengajar</td><td class="val">{{ (int) round((float) $d->persentase) }}%</td></tr>

                    <tr><td colspan="2" class="bagian bisyaroh">BISYAROH</td></tr>
                    <tr><td class="lbl">POKOK</td><td class="val uang">{{ $fmtUang($d->honor_pokok) }}</td></tr>
                    <tr><td class="lbl">PIKET</td><td class="val uang">{{ $fmtUang($d->honor_piket) }}</td></tr>
                    <tr><td class="lbl">Tunjangan Struktural</td><td class="val uang">{{ $fmtUang($d->tunjangan_struktural) }}</td></tr>
                    <tr><td class="lbl">Tunjangan Wali Kelas</td><td class="val uang">{{ $fmtUang($d->tunjangan_wali_kelas) }}</td></tr>
                    <tr><td class="lbl">Transport &amp; Insentif</td><td class="val uang">{{ $fmtUang($d->transport) }}</td></tr>
                    <tr><td colspan="2" class="total">TOTAL <span>{{ $fmtUang($d->total) }}</span></td></tr>

                    <tr><td colspan="2" class="nb"><b>NB:</b> {{ $d->persentase >= 100 ? 'Terima kasih atas khidmah Anda.' : 'Dimohon ditingkatkan kembali keaktifan mengajar.' }}</td></tr>

                    <tr>
                        <td colspan="2" class="ttd">
                            <div class="cap">Bendahara SPM Maqna'ul Ulum</div>
                            <div class="nama">MUHYIDDIN</div>
                        </td>
                    </tr>
                </table>
            </td>
            @endforeach
            @for($kosong = $baris->count(); $kosong < 3; $kosong++)
            <td class="cell"></td>
            @endfor
        </tr>
        @endforeach
        @for($kosongBaris = $halaman->chunk(3)->count(); $kosongBaris < 2; $kosongBaris++)
        <tr>
            <td class="cell"></td>
            <td class="cell"></td>
            <td class="cell"></td>
        </tr>
        @endfor
    </table>
    @endforeach
</body>
</html>