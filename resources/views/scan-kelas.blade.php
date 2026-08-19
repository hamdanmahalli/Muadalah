@extends('layouts.app')
@section('title', 'Scan Kehadiran Kelas')

@section('content')
<div class="max-w-xl mx-auto">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 text-center bg-emerald-600 text-white">
            <h2 class="text-2xl font-bold"><i class="fas fa-camera max-w-xl mb-2 text-3xl"></i><br>Scan Barcode Kelas</h2>
            <p class="text-emerald-100 text-sm mt-1">Arahkan kamera ke QR Code yang tertempel di kelas</p>
        </div>

        <div class="p-6">
            
            <div id="pesan-area" class="hidden mb-6 p-4 rounded-xl text-center font-bold text-sm shadow-sm">
                <span id="pesan-teks"></span>
            </div>

            <div id="reader" class="w-full bg-gray-50 rounded-xl border-2 border-dashed border-gray-300 overflow-hidden min-h-[300px]"></div>

            <div class="mt-6 text-center">
                <a href="/jadwal-saya" class="text-gray-500 hover:text-emerald-600 font-semibold text-sm transition"><i class="fas fa-arrow-left mr-1"></i> Kembali ke Jadwal Saya</a>
            </div>
            
        </div>
    </div>
</div>

<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const pesanArea = document.getElementById('pesan-area');
        const pesanTeks = document.getElementById('pesan-teks');
        let isProcessing = false;

        function onScanSuccess(decodedText, decodedResult) {
            if (isProcessing) return; // Mencegah scan dobel
            isProcessing = true;
            
            // Hentikan sementara scanner
            html5QrcodeScanner.pause(true);
            
            tampilkanPesan('Memproses data...', 'bg-blue-100', 'text-blue-700');

            // Tembak data ke Mesin Laravel
            fetch('/scan-proses', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ qr_data: decodedText })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    tampilkanPesan('<i class="fas fa-check-circle mr-2 text-xl"></i>' + data.pesan, 'bg-emerald-100', 'text-emerald-700');
                    // Getar HP jika sukses
                    if (navigator.vibrate) navigator.vibrate(200);
                } else {
                    tampilkanPesan('<i class="fas fa-exclamation-triangle mr-2 text-xl"></i>' + data.pesan, 'bg-red-100', 'text-red-700');
                }
                
                // Mulai ulang scanner setelah 4 detik
                setTimeout(() => {
                    pesanArea.classList.add('hidden');
                    isProcessing = false;
                    html5QrcodeScanner.resume();
                }, 4000);
            })
            .catch(error => {
                tampilkanPesan('Gagal terhubung ke server.', 'bg-red-100', 'text-red-700');
                setTimeout(() => { isProcessing = false; html5QrcodeScanner.resume(); }, 3000);
            });
        }

        function tampilkanPesan(teks, bgClass, textClass) {
            // KECERDASAN UI: Menggunakan mb-6 (margin-bottom) agar memberi jarak ke kamera di bawahnya
            pesanArea.className = `mb-6 p-4 rounded-xl text-center font-bold text-sm shadow-sm ${bgClass} ${textClass}`;
            pesanTeks.innerHTML = teks;
            pesanArea.classList.remove('hidden');
        }

        let html5QrcodeScanner = new Html5QrcodeScanner(
            "reader",
            { fps: 10, qrbox: {width: 250, height: 250} },
            /* verbose= */ false);
            
        html5QrcodeScanner.render(onScanSuccess);
    });
</script>
@endsection