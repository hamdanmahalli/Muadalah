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

            <form method="POST" action="/login" class="space-y-5">
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
        </div>
    </div>
</body>
</html>