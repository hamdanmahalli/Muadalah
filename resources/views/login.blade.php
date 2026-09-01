<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - Mu'adalah Wustho</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }
        .islamic-pattern {
            background-image: radial-gradient(#ffffff 1px, transparent 1px);
            background-size: 20px 20px;
            opacity: 0.1;
        }
    </style>

    <style>
        #loading-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(255, 255, 255, 0.8);
            display: none;
            justify-content: center; align-items: center;
            z-index: 9999; flex-direction: column;
        }
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

        .splash-logo-wrap {
            position: relative; width: 140px; height: 140px;
            display: flex; align-items: center; justify-content: center;
            margin-bottom: 24px;
        }
        .splash-logo-icon {
            width: 120px; height: 120px;
            background: #ffffff;
            box-shadow: 0 10px 30px rgba(0,0,0,0.25);
            border-radius: 32px;
            display: flex; align-items: center; justify-content: center;
            position: relative; z-index: 2;
            overflow: hidden;
            animation: logoFloat 3s ease-in-out infinite;
        }
        .splash-logo-icon img {
            width: 100%; height: 100%;
            object-fit: contain;
            padding: 12px;
        }
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

    <!-- MODAL INTIP JADWAL -->
    <style>
        #modal-intip {
            position: fixed; inset: 0; z-index: 9998;
            display: none;
            align-items: center; justify-content: center;
            padding: 20px;
        }
        #modal-intip.buka { display: flex; }
        #bg-intip {
            position: absolute; inset: 0;
            background: rgba(15, 23, 42, 0.55);
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        #modal-intip.buka #bg-intip { opacity: 1; }
        #box-intip {
            position: relative; width: 100%; max-width: 420px;
            background: #ffffff;
            border-radius: 24px;
            padding: 20px;
            transform: scale(0.94) translateY(10px);
            opacity: 0;
            transition: transform 0.3s ease, opacity 0.3s ease;
            max-height: 86dvh;
            overflow-y: auto;
        }
        #modal-intip.buka #box-intip { transform: scale(1) translateY(0); opacity: 1; }
    </style>

    <div id="loading-overlay">
        <div class="spinner"></div>
        <p style="margin-top: 15px; font-weight: bold; color: #333;">Memverifikasi Akun...</p>
    </div>

    <!-- SPLASH SCREEN OVERLAY -->
    <div id="splash-screen">
        <div class="splash-particle"></div>
        <div class="splash-particle"></div>
        <div class="splash-particle"></div>
        <div class="splash-particle"></div>
        <div class="splash-particle"></div>
        <div class="splash-particle"></div>

        <div class="splash-logo-wrap">
            <div class="splash-glow splash-glow-1"></div>
            <div class="splash-glow splash-glow-2"></div>
            <div class="splash-glow splash-glow-3"></div>
            <div class="splash-logo-icon">
                <img src="{{ asset('img/logo-muadalah.png') }}" alt="Logo Mu'adalah">
            </div>
        </div>

        <p class="splash-title">Mu'adalah Wustho</p>
        <p class="splash-subtitle">Maqna'ul Ulum</p>

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

        <!-- ===== BAGIAN ATAS: LOGO + TULISAN (SESUAI ASAL) ===== -->
        <div class="text-center mb-8">
            <div class="mx-auto w-28 h-28 bg-white rounded-3xl flex items-center justify-center border-2 border-white/40 mb-5 shadow-[0_18px_40px_-12px_rgba(6,78,59,0.5)] overflow-hidden">
                <img src="{{ asset('img/logo-muadalah.png') }}" alt="Logo"
                     onerror="this.style.display='none'; document.getElementById('ikon-logo-fallback').style.display='flex';"
                     class="w-full h-full object-contain p-2">
                <i id="ikon-logo-fallback" class="fas fa-mosque text-4xl text-white" style="display:none;"></i>
            </div>
            <h1 class="text-3xl font-black text-white tracking-tight drop-shadow-md">Mu'adalah Wustho</h1>
            <p class="text-emerald-100 font-medium mt-1 text-sm tracking-widest uppercase">Maqna'ul Ulum</p>
        </div>

        <!-- ===== KARTU LOGIN ===== -->
        <div class="bg-white p-7 rounded-3xl shadow-xl border border-gray-100">
            @if(session('error'))
                <div class="bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 mb-5 text-xs rounded-xl font-bold flex items-start shadow-sm">
                    <i class="fas fa-exclamation-circle mr-2 text-rose-500 text-lg mt-0.5"></i> {{ session('error') }}
                </div>
            @endif

            <form method="POST" id="form-login" action="/login" class="space-y-4">
                @csrf

                <div id="wrap-field-username">
                    <label class="block text-[11px] font-extrabold text-emerald-700 uppercase tracking-widest mb-1.5 ml-1">Username</label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 group-focus-within:text-emerald-600 transition">
                            <i class="fas fa-user"></i>
                        </div>
                        <input type="text" name="login_id" id="input-login" required autocomplete="off"
                               class="w-full pl-11 pr-4 py-3.5 bg-gray-50 border border-gray-200 rounded-xl text-sm font-bold text-gray-800 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-all"
                               placeholder="Username">
                    </div>
                </div>

                <div>
                    <label class="block text-[11px] font-extrabold text-emerald-700 uppercase tracking-widest mb-1.5 ml-1">Password</label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 group-focus-within:text-emerald-600 transition">
                            <i class="fas fa-lock"></i>
                        </div>
                        <input type="password" name="password" required autocomplete="current-password"
                               class="w-full pl-11 pr-4 py-3.5 bg-gray-50 border border-gray-200 rounded-xl text-sm font-bold text-gray-800 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-all"
                               placeholder="Password">
                    </div>
                </div>

                <!-- Pemicu intip jadwal (di atas tombol masuk) -->
                <button type="button" onclick="bukaModalIntip()"
                        class="w-full flex items-center justify-center gap-2 text-emerald-600 hover:text-emerald-800 text-xs font-bold py-1.5 transition cursor-pointer">
                    <i class="fas fa-calendar-day"></i> Intip jadwal hari ini
                </button>

                <div class="pt-1 flex items-center gap-3">
                    <button id="btn-login" type="submit" onclick="loadingLoginElegan(event, this.form)"
                            class="relative flex-1 bg-emerald-600 hover:bg-emerald-700 active:scale-[0.98] text-white font-black py-4 rounded-2xl transition-all duration-300 shadow-lg shadow-emerald-200 flex justify-center items-center gap-2 text-base tracking-widest overflow-hidden min-h-[56px] cursor-pointer">
                        <span id="teks-login">MASUK</span>
                        <i id="ikon-login" class="fas fa-arrow-right text-sm transition-all duration-300"></i>
                    </button>
                    <button type="button" onclick="loginBiometrik(this)" title="Login Sidik Jari"
                            class="shrink-0 w-[56px] h-[56px] rounded-2xl bg-emerald-600 hover:bg-emerald-700 active:scale-[0.98] text-white flex items-center justify-center transition-all duration-300 shadow-lg shadow-emerald-200 cursor-pointer">
                        <i class="fas fa-fingerprint text-xl"></i>
                    </button>
                </div>
            </form>
        </div>

        <p class="text-center mt-4">
            <a href="#" onclick="tampilToast('info', 'Silakan hubungi Tata Usaha (TU).'); return false;" class="text-[11px] font-bold text-emerald-700/80 hover:text-emerald-800 transition">
                Belum Mempunyai Akun? Hubungi TU
            </a>
        </p>
    </div>

    <!-- ===== MODAL INTIP JADWAL HARI INI ===== -->
    <div id="modal-intip">
        <div id="bg-intip" onclick="tutupModalIntip()"></div>
        <div id="box-intip">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-emerald-600 text-white flex items-center justify-center"><i class="fas fa-calendar-day text-sm"></i></div>
                    <div>
                        <h3 class="text-sm font-black text-slate-800">Intip Jadwal Hari Ini</h3>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest" id="intip-hari">-</p>
                    </div>
                </div>
                <button onclick="tutupModalIntip()" class="w-8 h-8 rounded-full bg-slate-100 text-slate-400 hover:text-slate-600 flex items-center justify-center transition cursor-pointer">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="space-y-3">
                <div id="intip-loading" class="hidden items-center gap-3 py-4 justify-center">
                    <i class="fas fa-circle-notch fa-spin text-emerald-600 text-lg"></i>
                    <span class="text-xs font-bold text-emerald-700">Mengecek jadwal Anda...</span>
                </div>

                <div id="intip-sapa" class="hidden">
                    <p class="text-xs font-extrabold text-slate-800">Selamat datang kembali, <span id="intip-nama" class="text-emerald-700">-</span></p>
                    <p class="text-[10px] font-semibold text-slate-400 mt-0.5" id="intip-tanggal">-</p>
                </div>

                <div id="intip-daftar" class="hidden space-y-2"></div>

                <div id="intip-kosong" class="hidden rounded-xl border border-dashed border-emerald-200 p-5 text-center">
                    <i class="fas fa-mug-hot text-2xl text-emerald-300 mb-2"></i>
                    <p class="text-xs font-bold text-slate-600">Anda tidak mengajar hari ini.</p>
                    <p class="text-[10px] font-semibold text-slate-400 mt-0.5">Nikmati waktu Anda dan tetap semangat!</p>
                </div>

                <div id="intip-pesan" class="hidden rounded-xl border border-emerald-100 bg-emerald-50 p-4 text-center">
                    <p id="intip-pesan-teks" class="text-xs font-bold text-slate-600">-</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        // ==========================================================
        // INTIP JADWAL HARI INI (Tanpa Login)
        // ==========================================================
        var SN_USR = 'sn_username';
        var SN_INGAT = 'sn_ingat';

        // Default ingat identitas = NON-AKTIF
        var ingatAktif = localStorage.getItem(SN_INGAT) === '1';

        function tampilkanPesanIntip(teks) {
            document.getElementById('intip-loading').classList.add('hidden');
            document.getElementById('intip-sapa').classList.add('hidden');
            document.getElementById('intip-daftar').classList.add('hidden');
            document.getElementById('intip-daftar').innerHTML = '';
            document.getElementById('intip-kosong').classList.add('hidden');
            var pesan = document.getElementById('intip-pesan');
            pesan.classList.remove('hidden');
            document.getElementById('intip-pesan-teks').textContent = teks;
        }

        function ambilJadwal(loginId) {
            var loading = document.getElementById('intip-loading');
            var sapa = document.getElementById('intip-sapa');
            var daftar = document.getElementById('intip-daftar');
            var kosong = document.getElementById('intip-kosong');
            var pesan = document.getElementById('intip-pesan');

            if (!loginId) {
                loading.classList.add('hidden');
                sapa.classList.add('hidden');
                daftar.classList.add('hidden');
                kosong.classList.add('hidden');
                pesan.classList.add('hidden');
                return;
            }

            loading.classList.remove('hidden');
            loading.classList.add('flex');
            sapa.classList.add('hidden');
            daftar.classList.add('hidden');
            kosong.classList.add('hidden');
            pesan.classList.add('hidden');

            fetch('/login/intip-jadwal?login_id=' + encodeURIComponent(loginId))
                .then(function(res) { return res.json(); })
                .then(function(data) {
                    loading.classList.add('hidden');
                    loading.classList.remove('flex');

                    if (!data.ok) {
                        tampilkanPesanIntip(data.pesan || 'Identitas tidak ditemukan.');
                        return;
                    }

                    document.getElementById('intip-hari').textContent = data.hari;
                    document.getElementById('intip-nama').textContent = data.nama_guru;
                    document.getElementById('intip-tanggal').textContent = data.tanggal;
                    sapa.classList.remove('hidden');

                    if (!data.jadwal || data.jadwal.length === 0) {
                        kosong.classList.remove('hidden');
                        return;
                    }

                    var html = '';
                    data.jadwal.forEach(function(j) {
                        html += '' +
                            '<div class="flex items-center gap-3 bg-emerald-50/50 rounded-xl border border-emerald-100 p-3">' +
                                '<div class="shrink-0 w-14 text-center">' +
                                    '<div class="text-[10px] font-black text-emerald-600 uppercase tracking-wider">Jam</div>' +
                                    '<div class="text-sm font-black text-slate-800">' + j.jam + '</div>' +
                                    '<div class="text-[9px] font-bold text-slate-400">' + (j.waktu || '-') + '</div>' +
                                '</div>' +
                                '<div class="flex-1 min-w-0 text-left">' +
                                    '<div class="text-sm font-extrabold text-slate-800 truncate">' + j.pelajaran + '</div>' +
                                    '<div class="text-[10px] font-bold text-emerald-600 mt-0.5 truncate">' + (j.kitab || '-') + '</div>' +
                                    '<div class="text-[10px] font-semibold text-slate-400 mt-0.5 truncate"><i class="fas fa-school mr-1"></i>' + j.kelas + '</div>' +
                                '</div>' +
                                '<i class="fas fa-chevron-right text-slate-200 text-xs"></i>' +
                            '</div>';
                    });
                    daftar.innerHTML = html;
                    daftar.classList.remove('hidden');
                })
                .catch(function() {
                    loading.classList.add('hidden');
                    tampilkanPesanIntip('Gagal memuat jadwal. Coba lagi nanti.');
                });
        }

        // ==========================================================
        // MODAL INTIP JADWAL
        // ==========================================================
        function bukaModalIntip() {
            var teringat = localStorage.getItem(SN_USR);
            var dariForm = document.getElementById('input-login').value.trim();

            if (teringat) {
                ambilJadwal(teringat);
            } else if (dariForm) {
                ambilJadwal(dariForm);
            } else {
                document.getElementById('intip-loading').classList.add('hidden');
                document.getElementById('intip-sapa').classList.add('hidden');
                document.getElementById('intip-daftar').classList.add('hidden');
                document.getElementById('intip-kosong').classList.add('hidden');
                tampilkanPesanIntip('Masukkan username di form login untuk melihat jadwal Anda.');
            }

            document.getElementById('modal-intip').classList.add('buka');
            document.body.style.overflow = 'hidden';
        }

        function tutupModalIntip() {
            document.getElementById('modal-intip').classList.remove('buka');
            document.body.style.overflow = '';
        }

        // ==========================================================
        // LOGIN / BIOMETRIK / TOAST
        // ==========================================================
        // ---- Helper WebAuthn (base64url <-> ArrayBuffer) ----
        function keBuffer(strB64url) {
            var s = strB64url.replace(/-/g, '+').replace(/_/g, '/');
            while (s.length % 4) { s += '='; }
            var bin = atob(s);
            var out = new Uint8Array(bin.length);
            for (var i = 0; i < bin.length; i++) { out[i] = bin.charCodeAt(i); }
            return out;
        }

        function dariBuffer(buffer) {
            var bins = new Uint8Array(buffer);
            var bin = '';
            for (var i = 0; i < bins.length; i++) { bin += String.fromCharCode(bins[i]); }
            return btoa(bin).replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/, '');
        }

        function konversiOptions(options, jenis) {
            options.challenge = keBuffer(options.challenge);
            if (options.user && options.user.id) { options.user.id = keBuffer(options.user.id); }
            ['allowCredentials', 'excludeCredentials'].forEach(function(kunci) {
                if (Array.isArray(options[kunci])) {
                    options[kunci].forEach(function(item) { if (item.id) { item.id = keBuffer(item.id); } });
                }
            });
            return options;
        }

        function credentialKeJson(cred) {
            var json = { id: cred.id, rawId: dariBuffer(cred.rawId), type: cred.type, response: {} };
            var r = cred.response;
            json.response.clientDataJSON = dariBuffer(r.clientDataJSON);
            if (r.attestationObject) { json.response.attestationObject = dariBuffer(r.attestationObject); }
            if (r.authenticatorData) { json.response.authenticatorData = dariBuffer(r.authenticatorData); }
            if (r.signature) { json.response.signature = dariBuffer(r.signature); }
            if (r.userHandle) { json.response.userHandle = dariBuffer(r.userHandle); }
            if (typeof r.getTransports === 'function') { json.response.transports = r.getTransports(); }
            return json;
        }

        function tokenCsrf() {
            var meta = document.querySelector('meta[name="csrf-token"]');
            if (meta && meta.getAttribute('content')) {
                return { header: 'X-CSRF-TOKEN', value: meta.getAttribute('content') };
            }
            var cookie = document.cookie.split('; ').filter(function(c) { return c.indexOf('XSRF-TOKEN=') === 0; })[0];
            if (cookie) {
                return { header: 'X-XSRF-TOKEN', value: decodeURIComponent(cookie.split('=').slice(1).join('=')) };
            }
            return null;
        }

        function loginBiometrik(btn) {
            if (!window.PublicKeyCredential) {
                tampilToast('error', 'Browser Anda tidak mendukung login sidik jari.');
                return;
            }
            if (!window.isSecureContext) {
                tampilToast('error', 'Sidik jari hanya berfungsi di HTTPS.');
                return;
            }

            btn.disabled = true;
            btn.classList.add('opacity-80', 'cursor-wait', 'pointer-events-none');
            btn.innerHTML = '<i class="fas fa-circle-notch fa-spin text-xl"></i>';

            fetch('/passkeys/login/options', { headers: { Accept: 'application/json' }, credentials: 'same-origin' })
                .then(function(res) { return res.json(); })
                .then(function(data) {
                    var publicKey = konversiOptions(data.options, 'login');
                    return navigator.credentials.get({ publicKey: publicKey });
                })
                .then(function(cred) {
                    var csrf = tokenCsrf();
                    var headers = { 'Content-Type': 'application/json', Accept: 'application/json' };
                    if (csrf) { headers[csrf.header] = csrf.value; }
                    return fetch('/passkeys/login', {
                        method: 'POST',
                        headers: headers,
                        body: JSON.stringify({ credential: credentialKeJson(cred), remember: true }),
                        credentials: 'same-origin'
                    });
                })
                .then(function(res) { return res.json(); })
                .then(function(data) {
                    var tujuan = (data && data.redirect) || '/';
                    window.location.href = tujuan;
                })
                .catch(function(err) {
                    btn.disabled = false;
                    btn.classList.remove('opacity-80', 'cursor-wait', 'pointer-events-none');
                    btn.innerHTML = '<i class="fas fa-fingerprint text-xl"></i>';
                    if (err && (err.name === 'NotAllowedError' || err.message === 'The passkey operation was cancelled.')) {
                        tampilToast('info', 'Batal memakai sidik jari.');
                    } else {
                        tampilToast('error', 'Gagal masuk dengan sidik jari. Coba lagi.');
                    }
                });
        }

        function tampilToast(tipe, pesan) {
            var el = document.getElementById('toast-lokal');
            if (!el) {
                el = document.createElement('div');
                el.id = 'toast-lokal';
                el.className = 'fixed left-4 right-4 bottom-6 z-[10050] px-4 py-3 rounded-2xl text-xs font-bold text-center shadow-2xl transition-all duration-300 opacity-0 translate-y-3';
                document.body.appendChild(el);
            }
            el.className = el.className
                .replace(/bg-emerald-500|bg-red-500|bg-sky-500/g, '')
                + (tipe === 'success' ? ' bg-emerald-500 text-white' : tipe === 'error' ? ' bg-red-500 text-white' : ' bg-slate-800 text-white');
            el.textContent = pesan;
            el.style.opacity = '1';
            el.style.transform = 'translateY(0)';
            clearTimeout(el._t);
            el._t = setTimeout(function() {
                el.style.opacity = '0';
                el.style.transform = 'translateY(12px)';
            }, 4000);
        }

        function loadingLoginElegan(event, form) {
            if (form && !form.checkValidity()) {
                return;
            }
            event.preventDefault();
            event.stopPropagation();

            let btn = document.getElementById('btn-login');
            let teks = document.getElementById('teks-login');
            let ikon = document.getElementById('ikon-login');

            btn.classList.add('opacity-90', 'cursor-wait', 'pointer-events-none');
            btn.classList.remove('hover:bg-emerald-700');
            btn.classList.replace('bg-emerald-600', 'bg-emerald-500');

            teks.innerText = 'Mengautentikasi...';
            ikon.className = 'fas fa-circle-notch fa-spin text-sm';

            setTimeout(() => { if(form) form.submit(); }, 400);
        }

        // Simpan username saat login hanya jika "ingat identitas" aktif (default NON-aktif)
        document.getElementById('form-login').addEventListener('submit', function() {
            var loginId = this.querySelector('input[name="login_id"]').value;
            if (ingatAktif && loginId) {
                localStorage.setItem(SN_USR, loginId);
            }

            document.getElementById('loading-overlay').style.display = 'flex';
            let btnSubmit = this.querySelector('button[type="submit"]');
            if(btnSubmit) {
                btnSubmit.disabled = true;
                btnSubmit.innerHTML = 'Memproses...';
            }
        });

        // ==========================================================
        // INGAT IDENTITAS: sembunyikan kolom username saat aktif
        // ==========================================================
        function renderMasukSebagai() {
            var wrapField = document.getElementById('wrap-field-username');
            var teringat = localStorage.getItem(SN_USR);

            if (ingatAktif && teringat) {
                document.getElementById('input-login').value = teringat;
                wrapField.classList.add('hidden');
            } else {
                wrapField.classList.remove('hidden');
            }
        }

        renderMasukSebagai();

        // ==========================================================
        // SPLASH SCREEN & CLEANUP
        // ==========================================================
        window.addEventListener('load', function() {
            setTimeout(function() {
                var splash = document.getElementById('splash-screen');
                if (splash) {
                    splash.classList.add('fade-out');
                    setTimeout(function() { splash.remove(); }, 600);
                }
            }, 2500);
        });

        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.getRegistrations().then(function(registrations) {
                for (var registration of registrations) {
                    registration.unregister();
                }
            });
        }
    </script>

</body>
</html>