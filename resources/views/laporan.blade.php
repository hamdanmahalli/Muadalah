<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekap Kehadiran Guru</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .kop-surat { text-align: center; margin-bottom: 20px; border-bottom: 3px solid black; padding-bottom: 10px; }
        .judul { text-align: center; font-weight: bold; font-size: 18px; margin-bottom: 15px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid black; padding: 8px; font-size: 14px; text-align: left; }
        th { background-color: #f2f2f2; text-align: center; }
        .text-center { text-align: center; }
        .tombol-cetak { display: inline-block; padding: 10px 15px; background-color: #2563eb; color: white; text-decoration: none; border-radius: 5px; margin-bottom: 15px; font-weight: bold; font-family: sans-serif;}
    </style>
</head>
<body>

    @if(!isset($is_pdf))
    <div style="text-align: right;">
        <a href="/laporan/cetak" class="tombol-cetak">📥 Download PDF Sekarang</a>
    </div>
    @endif

    <div class="kop-surat">
        <h2>LEMBAGA PENDIDIKAN SMART PESANTREN</h2>
        <p style="margin: 0; font-size: 14px;">Jl. Pendidikan No. 1, Surabaya, Jawa Timur | Telp: (031) 123456</p>
    </div>

    <div class="judul">
        REKAPITULASI KEHADIRAN GURU HARIAN
    </div>
    <p><strong>Tanggal:</strong> {{ \Carbon\Carbon::parse($tanggalHariIni)->translatedFormat('d F Y') }}</p>

    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="15%">Kelas</th>
                <th width="10%">Jam Ke-</th>
                <th width="20%">Mata Pelajaran</th>
                <th width="35%">Nama Guru</th>
                <th width="15%">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($jadwals as $index => $jadwal)
                @php
                    $status = $kehadiranHariIni[$jadwal->id] ?? 'Menunggu';
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center">{{ $jadwal->kelas }}</td>
                    <td class="text-center">{{ $jadwal->jam_ke }}</td>
                    <td>{{ $jadwal->mata_pelajaran }}</td>
                    
                    <td>{{ $jadwal->masterGuru->nama_guru ?? 'Data Guru Tidak Ditemukan' }}</td>
                    
                    <td class="text-center"><strong>{{ $status }}</strong></td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div style="margin-top: 50px; text-align: right; font-size: 14px;">
        <p>Surabaya, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
        <br><br><br>
        <p><strong>Kepala Tata Usaha</strong></p>
    </div>

</body>
</html>