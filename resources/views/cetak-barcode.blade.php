<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Barcode - {{ $kelas->nama_kelas }}</title>
    <style>
        body { font-family: 'Arial', sans-serif; background-color: #e5e7eb; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .kertas-a4 { background-color: white; width: 210mm; height: 297mm; padding: 20mm; box-sizing: border-box; box-shadow: 0 10px 25px rgba(0,0,0,0.1); text-align: center; position: relative; }
        .kop { border-bottom: 3px solid #1f2937; padding-bottom: 15px; margin-bottom: 40px; }
        .judul-pesantren { font-size: 28px; font-weight: 900; color: #111827; margin: 0; text-transform: uppercase; letter-spacing: 2px; }
        .sub-judul { font-size: 16px; color: #4b5563; margin: 5px 0 0 0; }
        .nama-kelas { font-size: 80px; font-weight: 900; color: #111827; margin: 20px 0; letter-spacing: -2px; }
        .kotak-qr { display: inline-block; padding: 20px; border: 4px dashed #9ca3af; border-radius: 20px; margin: 30px 0; }
        .peringatan { font-size: 18px; font-weight: bold; color: #dc2626; margin-top: 30px; text-transform: uppercase; }
        .periode { display: inline-block; font-size: 20px; font-weight: bold; color: #ffffff; background-color: #4f46e5; padding: 10px 30px; border-radius: 50px; margin-top: 10px; }
        
        /* Pengaturan agar saat diprint, garis putus-putus dan background hilang/bersih */
        @media print {
            body { background-color: white; }
            .kertas-a4 { box-shadow: none; width: auto; height: auto; padding: 0; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="kertas-a4">
        <div class="kop">
            <h1 class="judul-pesantren">MU'DALAH WUSTHA</h1>
            <p class="sub-judul">Sistem Kehadiran Guru Terintegrasi Berbasis QR Code</p>
        </div>
        
        <p style="font-size: 24px; font-weight: bold; color: #6b7280; margin: 0;">SCAN KEHADIRAN KELAS</p>
        <h2 class="nama-kelas">{{ $kelas->nama_kelas }}</h2>
        
        <div class="kotak-qr">
            {!! $qrCodeImage !!}
        </div>
        
        <div class="peringatan">
            ⚠️ PERHATIAN: BARCODE INI HANYA BERLAKU UNTUK PERIODE SABTU-KAMIS!
        </div>
        <div class="periode">
            {{ $periodeBerlaku }}
        </div>
        
        <p style="margin-top: 50px; font-size: 14px; color: #9ca3af;">
            *Silakan buka menu <b>Scan Barcode</b> di aplikasi MUMARIS pada perangkat Anda.
        </p>
    </div>
</body>
</html>