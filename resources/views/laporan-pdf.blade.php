<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekap Kehadiran Guru</title>
    <style>
        /* MENGATUR UKURAN KERTAS F4 (FOLIO) & MARGIN TIPIS AGAR MUAT 1 LEMBAR */
        @page {
            size: 215.9mm 330.2mm; 
            margin: 10mm 15mm;
        }
        
        /* FONT DEJAVU SANS WAJIB AGAR SIMBOL UNICODE (✔, ✖, dll) TERBACA DI PDF */
        body {
            font-family: 'DejaVu Sans', sans-serif;
            color: #374151;
            font-size: 8.5pt;
        }

        /* HEADER SEPERTI DI WEB (RATA KIRI) */
        .header-container {
            border-bottom: 2px solid #f3f4f6;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .header-container h1 {
            font-size: 16pt;
            margin: 0;
            color: #1f2937;
            font-weight: bold;
        }
        .header-container p {
            margin: 4px 0 0 0;
            color: #6b7280;
            font-size: 9pt;
        }
        .text-green { color: #047857; font-weight: bold; }

        /* KARTU METRIK (DIBUAT MENGGUNAKAN TABEL AGAR SEJAJAR DI PDF) */
        .metric-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 8px 0;
            margin-bottom: 15px;
            margin-left: -8px; /* Kompensasi border-spacing */
        }
        .metric-card {
            background-color: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 12px;
            width: 25%;
            vertical-align: top;
        }
        .metric-title { font-size: 7.5pt; font-weight: bold; text-transform: uppercase; margin: 0 0 5px 0; }
        .metric-value { font-size: 16pt; font-weight: bold; margin: 0; }
        .metric-desc { font-size: 7pt; margin-top: 4px; }
        
        /* Pewarnaan Kartu */
        .c-wajib .metric-title { color: #9ca3af; }
        .c-wajib .metric-value { color: #1f2937; }
        .c-wajib .metric-desc { color: #9ca3af; }
        
        .c-hadir .metric-title { color: #059669; }
        .c-hadir .metric-value { color: #047857; }
        .c-hadir .metric-desc { color: #059669; }
        
        .c-kosong .metric-title { color: #e11d48; }
        .c-kosong .metric-value { color: #be123c; }
        .c-kosong .metric-desc { color: #e11d48; }
        
        .c-tingkat { background-color: #047857; border: none; }
        .c-tingkat .metric-title { color: #d1fae5; }
        .c-tingkat .metric-value { color: #ffffff; }
        .c-tingkat .metric-desc { color: #a7f3d0; }

        /* KOTAK LIBUR (LANGSUNG TERBUKA & ELEGAN) */
        .libur-box {
            background-color: #fff1f2;
            border: 1px solid #fecdd3;
            border-radius: 8px;
            padding: 10px 15px;
            margin-bottom: 15px;
        }
        .libur-title { color: #be123c; font-weight: bold; font-size: 8.5pt; margin: 0 0 5px 0; }
        .libur-list { margin: 0; padding-left: 15px; color: #4b5563; font-size: 8pt; }
        .libur-list li { margin-bottom: 3px; }

        /* TABEL UTAMA (RINCIAN PERFORMA) */
        .table-header { margin-bottom: 8px; }
        .table-header h2 { font-size: 11pt; margin: 0; color: #1f2937; }
        .table-header p { font-size: 8pt; margin: 2px 0 0 0; color: #6b7280; }

        .main-table { width: 100%; border-collapse: collapse; }
        .main-table th, .main-table td { padding: 6px 4px; border-bottom: 1px solid #f3f4f6; text-align: center; }
        .main-table th { background-color: #f9fafb; font-size: 7.5pt; font-weight: bold; color: #6b7280; text-transform: uppercase; border-bottom: 2px solid #e5e7eb; }
        .main-table td { font-size: 8pt; color: #374151; font-weight: bold; }
        .main-table .text-left { text-align: left; }
        
        /* Pewarnaan Teks Tabel */
        .text-a { color: #e11d48; }
        .text-i { color: #2563eb; }
        .text-s { color: #d97706; }
        .text-p { color: #7c3aed; }
        .bg-realita { background-color: #ecfdf5; color: #047857; }

        /* Lencana Keterangan / Status */
        .badge { padding: 3px 8px; border-radius: 10px; font-size: 7pt; display: inline-block; border: 1px solid transparent; }
        .badge-sangat-baik { background-color: #ecfdf5; color: #047857; border-color: #a7f3d0; }
        .badge-baik { background-color: #eff6ff; color: #1d4ed8; border-color: #bfdbfe; }
        .badge-cukup { background-color: #fffbeb; color: #b45309; border-color: #fde68a; }
        .badge-kurang { background-color: #fff1f2; color: #be123c; border-color: #fecdd3; }

        /* Progress Bar Simulasi untuk persentase */
        .progress-bg { background-color: #e5e7eb; border-radius: 2px; height: 4px; width: 100%; margin-top: 3px; }
        .progress-bar { background-color: #10b981; height: 4px; border-radius: 2px; }
    </style>
</head>
<body>

    <div class="header-container">
        <h1>Laporan Kehadiran Guru</h1>
        <p>Periode Aktif: <span class="text-green">{{ $periodeTeks }}</span></p>
    </div>

    <table class="metric-table">
        <tr>
            <td class="metric-card c-wajib">
                <p class="metric-title">&#9679; TOTAL JAM WAJIB</p>
                <p class="metric-value">{{ number_format($totalSeluruhWajib) }} <span style="font-size:9pt; font-weight:normal;">Jam</span></p>
                <p class="metric-desc">Target pengajaran seluruh guru</p>
            </td>
            <td class="metric-card c-hadir">
                <p class="metric-title">&#10004; REALITA HADIR</p>
                <p class="metric-value">{{ number_format($totalSeluruhRealita) }} <span style="font-size:9pt; font-weight:normal;">Jam</span></p>
                <p class="metric-desc">{{ $persenTotalRealita }}% Efektivitas Terpenuhi</p>
            </td>
            <td class="metric-card c-kosong">
                <p class="metric-title">&#10006; JAM KOSONG / ALPA</p>
                <p class="metric-value">{{ number_format($totalSeluruhKosong) }} <span style="font-size:9pt; font-weight:normal;">Jam</span></p>
                <p class="metric-desc">{{ $persenTotalKosong }}% Jam Tidak Terisi</p>
            </td>
            <td class="metric-card c-tingkat">
                <p class="metric-title">&#9733; TINGKAT KEHADIRAN</p>
                <p class="metric-value" style="font-size: 20pt;">{{ $persenTotalRealita }}%</p>
                <p class="metric-desc">Status Instansi: {{ $persenTotalRealita >= 80 ? 'Sangat Baik' : 'Perlu Evaluasi' }}</p>
            </td>
        </tr>
    </table>

    @if(isset($daftarLibur) && $daftarLibur->count() > 0)
    <div class="libur-box">
        <p class="libur-title">&#9873; Terdapat {{ $daftarLibur->count() }} Agenda Libur pada periode tanggal ini:</p>
        <ul class="libur-list">
            @foreach($daftarLibur as $libur)
                @php
                    $kelasArr = is_string($libur->kelas_ids) ? json_decode($libur->kelas_ids, true) : (is_array($libur->kelas_ids) ? $libur->kelas_ids : []);
                    $namaKelasStr = (!empty($kelasArr) && is_array($kelasArr) && class_exists('\App\Models\Kelas')) 
                        ? \App\Models\Kelas::whereIn('id', $kelasArr)->pluck('nama_kelas')->implode(', ') 
                        : 'Tertentu';
                    
                    $jamArr = is_string($libur->jam_diliburkan) ? json_decode($libur->jam_diliburkan, true) : (is_array($libur->jam_diliburkan) ? $libur->jam_diliburkan : []);
                    $jamTeks = (is_array($jamArr) && count($jamArr) > 0) ? (count($jamArr) > 1 ? min($jamArr) . '-' . max($jamArr) : implode(', ', $jamArr)) : '-';
                @endphp
                <li>
                    <strong>{{ $libur->nama_libur }}</strong> 
                    ({{ \Carbon\Carbon::parse($libur->tanggal_mulai)->translatedFormat('d M Y') }}
                    @if($libur->tanggal_mulai != $libur->tanggal_selesai)
                        - {{ \Carbon\Carbon::parse($libur->tanggal_selesai)->translatedFormat('d M Y') }}
                    @endif)
                    &nbsp;|&nbsp; Cakupan: <b>{{ $libur->target_libur == 'semua' ? 'Seluruh Kelas' : 'Kelas ' . $namaKelasStr }}</b>
                    &nbsp;|&nbsp; Waktu: <b>{{ $libur->tipe_agenda == 'Penuh' ? 'Seharian Full' : 'Parsial Jam Ke-'.$jamTeks }}</b>
                </li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="table-header">
        <h2>Rincian Performa Kehadiran Guru</h2>
        <p>Satuan Pendidikan Mu'adalah Wustha Maqna'ul Ulum</p>
    </div>

    <table class="main-table">
        <thead>
            <tr>
                <th style="width: 4%;">NO</th>
                <th class="text-left" style="width: 26%;">NAMA GURU</th>
                <th>WAJIB</th>
                <th class="text-a">A</th>
                <th class="text-i">I</th>
                <th class="text-s">S</th>
                <th class="text-p">PIKET</th>
                <th class="bg-realita">REALITA</th>
                <th style="width: 12%;">% REALITA</th>
                <th style="width: 15%;">KETERANGAN</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rekapData as $index => $data)
            <tr>
                <td style="color:#9ca3af;">{{ $index + 1 }}</td>
                <td class="text-left">{{ $data->nama_guru }}</td>
                <td>{{ $data->jam_wajib }}</td>
                <td class="text-a">{{ $data->a > 0 ? $data->a : '-' }}</td>
                <td class="text-i">{{ $data->i > 0 ? $data->i : '-' }}</td>
                <td class="text-s">{{ $data->s > 0 ? $data->s : '-' }}</td>
                <td class="text-p">{{ $data->piket > 0 ? $data->piket : '-' }}</td>
                <td class="bg-realita">{{ $data->realita }}</td>
                <td>
                    {{ $data->persen }}%
                    <div class="progress-bg">
                        <div class="progress-bar" style="width: {{ $data->persen }}%;"></div>
                    </div>
                </td>
                <td>
                    @php
                        $badgeClass = 'badge-kurang';
                        if($data->ket == 'Sangat Baik') $badgeClass = 'badge-sangat-baik';
                        elseif($data->ket == 'Baik') $badgeClass = 'badge-baik';
                        elseif($data->ket == 'Cukup') $badgeClass = 'badge-cukup';
                    @endphp
                    <span class="badge {{ $badgeClass }}">{{ $data->ket }}</span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

</body>
</html>