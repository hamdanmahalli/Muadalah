<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Mu'adalah Wustha</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }
        /* Ornamen Pola Islami Halus */
        .islamic-pattern {
            background-image: radial-gradient(#ffffff 1px, transparent 1px);
            background-size: 20px 20px;
            opacity: 0.1;
        }
    </style>

    <style>
        /* Desain Layar Loading (Overlay) */
        #loading-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(255, 255, 255, 0.8);
            display: none; /* Disembunyikan secara default */
            justify-content: center; align-items: center;
            z-index: 9999; flex-direction: column;
        }
        /* Animasi Putaran Berputar */
        .spinner {
            border: 4px solid #f3f3f3; border-top: 4px solid #3498db;
            border-radius: 50%; width: 40px; height: 40px;
            animation: putar 1s linear infinite;
        }
        @keyframes putar { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    </style>

    <!-- SPLASH SCREEN ANIMASI -->
    <style>
        #splash-screen {
            position: fixed; inset: 0; z-index: 99999;
            background: linear-gradient(135deg, #064e3b 0%, #047857 50%, #059669 100%);
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            transition: opacity 0.6s ease, visibility 0.6s ease;
        }
        #splash-screen.fade-out { opacity: 0; visibility: hidden; }

        /* Logo container dengan glow */
        .splash-logo-wrap {
            position: relative; width: 100px; height: 100px;
            display: flex; align-items: center; justify-content: center;
            margin-bottom: 24px;
        }
        .splash-logo-icon {
            width: 80px; height: 80px;
            background: rgba(255,255,255,0.15);
            backdrop-filter: blur(12px);
            border: 2px solid rgba(255,255,255,0.25);
            border-radius: 22px;
            display: flex; align-items: center; justify-content: center;
            font-size: 36px; color: #ffffff;
            position: relative; z-index: 2;
            animation: logoFloat 3s ease-in-out infinite;
        }

        /* Glow efek bercahaya - 3 lapis */
        .splash-glow {
            position: absolute; inset: -15px;
            border-radius: 30px; z-index: 1;
            animation: pulseGlow 2s ease-in-out infinite;
        }
        .splash-glow-1 {
            box-shadow: 0 0 40px 10px rgba(16,185,129,0.5),
                        0 0 80px 20px rgba(16,185,129,0.3),
                        0 0 120px 40px rgba(16,185,129,0.15);
        }
        .splash-glow-2 {
            box-shadow: 0 0 30px 8px rgba(52,211,153,0.4),
                        0 0 60px 16px rgba(52,211,153,0.2);
            animation-delay: 0.5s;
        }
        .splash-glow-3 {
            box-shadow: 0 0 20px 6px rgba(110,231,183,0.3);
            animation-delay: 1s;
        }

        /* Partikel bercahaya kecil */
        .splash-particle {
            position: absolute; width: 4px; height: 4px;
            background: #6ee7b7; border-radius: 50%;
            animation: particleFloat 3s ease-in-out infinite;
        }
        .splash-particle:nth-child(1) { top: 10%; left: 20%; animation-delay: 0s; }
        .splash-particle:nth-child(2) { top: 25%; right: 15%; animation-delay: 0.8s; }
        .splash-particle:nth-child(3) { bottom: 30%; left: 10%; animation-delay: 1.6s; }
        .splash-particle:nth-child(4) { bottom: 15%; right: 20%; animation-delay: 0.4s; }
        .splash-particle:nth-child(5) { top: 50%; left: 5%; animation-delay: 1.2s; }
        .splash-particle:nth-child(6) { top: 40%; right: 8%; animation-delay: 2s; }

        @keyframes pulseGlow {
            0%, 100% { opacity: 0.6; transform: scale(1); }
            50% { opacity: 1; transform: scale(1.08); }
        }
        @keyframes logoFloat {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-6px); }
        }
        @keyframes particleFloat {
            0%, 100% { opacity: 0; transform: translateY(0) scale(0.5); }
            25% { opacity: 1; transform: translateY(-10px) scale(1); }
            75% { opacity: 0.5; transform: translateY(10px) scale(0.8); }
        }

        .splash-title {
            font-size: 22px; font-weight: 900; color: #ffffff;
            letter-spacing: -0.5px; text-shadow: 0 2px 20px rgba(0,0,0,0.2);
        }
        .splash-subtitle {
            font-size: 10px; font-weight: 600; color: rgba(255,255,255,0.6);
            letter-spacing: 3px; text-transform: uppercase; margin-top: 6px;
        }

        /* Loading dots di bawah teks */
        .splash-dots { display: flex; gap: 6px; margin-top: 28px; }
        .splash-dots span {
            width: 6px; height: 6px; border-radius: 50%;
            background: rgba(255,255,255,0.4);
            animation: dotPulse 1.4s ease-in-out infinite;
        }
        .splash-dots span:nth-child(2) { animation-delay: 0.2s; }
        .splash-dots span:nth-child(3) { animation-delay: 0.4s; }
        @keyframes dotPulse {
            0%, 80%, 100% { transform: scale(0.5); opacity: 0.3; }
            40% { transform: scale(1.2); opacity: 1; }
        }
    </style>

    <!-- Elemen Loading yang akan muncul -->
    <div id="loading-overlay">
        <div class="spinner"></div>
        <p style="margin-top: 15px; font-weight: bold; color: #333;">Memverifikasi Akun...</p>
    </div>

    <!-- SPLASH SCREEN OVERLAY -->
    <div id="splash-screen">
        <!-- Partikel bercahaya -->
        <div class="splash-particle"></div>
        <div class="splash-particle"></div>
        <div class="splash-particle"></div>
        <div class="splash-particle"></div>
        <div class="splash-particle"></div>
        <div class="splash-particle"></div>

        <!-- Logo dengan efek glow -->
        <div class="splash-logo-wrap">
            <div class="splash-glow splash-glow-1"></div>
            <div class="splash-glow splash-glow-2"></div>
            <div class="splash-glow splash-glow-3"></div>
            <div class="splash-logo-icon">
                <i class="fas fa-mosque"></i>
            </div>
        </div>

        <p class="splash-title">Mu'adalah Wustha</p>
        <p class="splash-subtitle">Maqna'ul Ulum</p>

        <!-- Loading dots -->
        <div class="splash-dots">
            <span></span><span></span><span></span>
        </div>
    </div>
</head>
<body class="flex items-center justify-center min-h-screen relative overflow-hidden antialiased">

    <div class="absolute top-0 w-full h-[45%] bg-gradient-to-b from-emerald-700 to-emerald-500 rounded-b-[3rem] shadow-lg z-0 overflow-hidden">
        <div class="absolute inset-0 islamic-pattern"></div>
        <div class="absolute -bottom-10 -left-10 w-40 h-40 bg-white opacity-10 rounded-full blur-2xl"></div>
        <div class="absolute top-10 -right-10 w-32 h-32 bg-sky-300 opacity-20 rounded-full blur-2xl"></div>
    </div>

    <div class="relative w-full max-w-md px-6 z-10 mt-8">
        
        <div class="text-center mb-8">
            <div class="mx-auto w-20 h-20 bg-white/20 backdrop-blur-md rounded-2xl flex items-center justify-center border border-white/30 mb-4 shadow-xl">
                <i class="fas fa-mosque text-4xl text-white"></i>
            </div>
            <h1 class="text-3xl font-black text-white tracking-tight drop-shadow-md">Mu'adalah Wustha</h1>
            <p class="text-emerald-100 font-medium mt-1 text-sm tracking-widest uppercase">Pondok Pesantren Maqna'ul Ulum</p>
        </div>

        <div class="bg-white p-8 rounded-3xl shadow-xl border border-gray-100">
            <div class="mb-8 text-center">
                <h2 class="text-2xl font-extrabold text-gray-800">Ahlan Wa Sahlan</h2>
                <p class="text-xs text-gray-500 font-medium mt-1">Silakan masuk dengan kredensial Anda</p>
            </div>

            @if(session('error'))
                <div class="bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 mb-6 text-xs rounded-xl font-bold flex items-center shadow-sm">
                    <i class="fas fa-exclamation-circle mr-2 text-rose-500 text-lg"></i> {{ session('error') }}
                </div>
            @endif

            <form method="POST" id="form-login" action="/login" class="space-y-5">
                @csrf
                <div>
                    <label class="block text-[11px] font-extrabold text-emerald-700 uppercase tracking-widest mb-1.5 ml-1">Username </label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 group-focus-within:text-emerald-600 transition">
                            <i class="fas fa-id-badge"></i>
                        </div>
                        <input type="text" name="login_id" required autocomplete="off"
                               class="w-full pl-11 pr-4 py-3.5 bg-gray-50 border border-gray-200 rounded-xl text-sm font-bold text-gray-800 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-all" 
                               placeholder="Ketik identitas...">
                    </div>
                </div>

                <div>
                    <label class="block text-[11px] font-extrabold text-emerald-700 uppercase tracking-widest mb-1.5 ml-1">Kata Sandi</label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 group-focus-within:text-emerald-600 transition">
                            <i class="fas fa-lock"></i>
                        </div>
                        <input type="password" name="password" required 
                               class="w-full pl-11 pr-4 py-3.5 bg-gray-50 border border-gray-200 rounded-xl text-sm font-bold text-gray-800 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-all" 
                               placeholder="••••••••">
                    </div>
                </div>

                <div class="pt-4">
                    <!-- Tombol Login dengan ID dan fungsi pemotong event (bypass) -->
                    <!-- Pastikan min-h-[52px] terpasang agar tinggi tombol tidak menciut saat loading -->
                    <button id="btn-login" type="submit" onclick="loadingLoginElegan(event, this.form)" class="relative w-full bg-emerald-600 text-white font-bold py-3.5 rounded-xl hover:bg-emerald-700 active:scale-[0.98] transition-all duration-300 shadow-lg shadow-emerald-200 flex justify-center items-center overflow-hidden min-h-[52px]">
                        <span id="teks-login" class="transition-all duration-300 tracking-wide">Masuk Aplikasi</span> 
                        <i id="ikon-login" class="fas fa-arrow-right ml-2 text-sm transition-all duration-300 group-hover:translate-x-1"></i>
                    </button>
                </div>

                <!-- Script Animasi Loading Login Elegan -->
                <script>
                    function loadingLoginElegan(event, form) {
                        // 1. Validasi form dasar HTML5 (pastikan username/password tidak kosong sebelum loading)
                        if (form && !form.checkValidity()) {
                            return; // Biarkan browser memunculkan peringatan "Wajib diisi"
                        }

                        // 2. Hentikan propagasi agar TIDAK ditangkap script global yang merusak desain
                        event.preventDefault();
                        event.stopPropagation();

                        // 3. Ambil elemen-elemen di dalam tombol
                        let btn = document.getElementById('btn-login');
                        let teks = document.getElementById('teks-login');
                        let ikon = document.getElementById('ikon-login');

                        // 4. Terapkan efek loading premium
                        btn.classList.add('opacity-90', 'cursor-wait', 'pointer-events-none');
                        btn.classList.remove('hover:bg-emerald-700');
                        btn.classList.replace('bg-emerald-600', 'bg-emerald-500'); // Warna sedikit meredup elegan
                        
                        // Ubah teks dan putar ikon dengan transisi halus
                        teks.innerText = 'Mengautentikasi...';
                        ikon.className = 'fas fa-circle-notch fa-spin ml-2 text-sm';

                        // 5. Submit form secara native setelah animasi berjalan
                        setTimeout(() => {
                            if(form) form.submit();
                        }, 400);
                    }
                </script>
            </form>

            <!-- Bantuan login: hubungi TU jika tidak bisa masuk -->
            <div class="mt-6 bg-emerald-50 border border-emerald-100 rounded-xl px-4 py-3 flex items-start text-emerald-800">
                <i class="fas fa-headset text-emerald-600 text-sm mt-0.5 mr-3"></i>
                <div class="min-w-0">
                    <p class="text-xs font-extrabold uppercase tracking-wide text-emerald-700">Mengalami kendala masuk?</p>
                    <p class="text-xs text-emerald-700/80 font-medium mt-1 leading-snug line-clamp-2">
                        Hubungi <strong class="font-bold">Tata Usaha (TU)</strong> 
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script>
        // SPLASH SCREEN: Fade out setelah 2.5 detik
        window.addEventListener('load', function() {
            setTimeout(function() {
                var splash = document.getElementById('splash-screen');
                if (splash) {
                    splash.classList.add('fade-out');
                    setTimeout(function() { splash.remove(); }, 600);
                }
            }, 2500);
        });

        // Bersihkan Service Worker lama sebelum login
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.getRegistrations().then(function(registrations) {
                for (var registration of registrations) {
                    registration.unregister();
                }
            });
        }
        document.getElementById('form-login').addEventListener('submit', function() {
            // 1. Munculkan animasi layar penuh
            document.getElementById('loading-overlay').style.display = 'flex';
            
            // 2. Kunci tombol login agar tidak diklik dua kali
            let btnSubmit = this.querySelector('button[type="submit"]');
            if(btnSubmit) {
                btnSubmit.disabled = true;
                btnSubmit.innerHTML = 'Memproses...';
            }
        });
    </script>

</body>
</html>