<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}"> 
    <title>@yield('title', 'SmartPesantren')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    @stack('styles')
</head>
<body class="bg-gray-50 flex h-screen overflow-hidden text-sm">

    <aside class="w-64 bg-white border-r border-gray-200 flex flex-col shadow-sm z-20">
        <div class="h-16 flex items-center px-6 border-b border-gray-100">
            <span class="text-green-600 text-2xl mr-3"><i class="fas fa-mosque"></i></span>
            <span class="font-bold text-lg text-gray-800">Muadalah Wustha</span>
        </div>
        
        <div class="flex-1 overflow-y-auto py-4">
            
            <div class="px-6 mb-2 text-xs font-bold text-green-600 uppercase tracking-wider">Menu Utama</div>
            <nav class="space-y-1 pb-8">
                <a href="/" class="flex items-center px-6 py-3 {{ request()->is('/') ? 'bg-green-50 text-green-700 border-r-4 border-green-600 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-green-600 transition font-medium' }}">
                    <i class="fas fa-desktop w-6"></i> Dashboard
                </a>

                                
                <a href="/meja-kontrol" class="flex items-center px-6 py-3 {{ request()->is('meja-kontrol') ? 'bg-green-50 text-green-700 border-r-4 border-green-600 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-green-600 transition font-medium' }}">
                    <i class="fas fa-tv w-6"></i> Meja Kontrol
                </a>
                <a href="/laporan" class="flex items-center px-6 py-3 {{ request()->is('laporan') ? 'bg-green-50 text-green-700 border-r-4 border-green-600 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-green-600 transition font-medium' }}">
                    <i class="fas fa-print w-6"></i> Rekap Laporan
                </a>

                <a href="/master-import" class="flex items-center px-6 py-3 {{ request()->is('master-import*') ? 'bg-green-50 text-green-700 border-r-4 border-green-600 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-green-600 transition font-medium' }}">
                    <i class="fas fa-file-excel w-6 text-green-600"></i> Master Import Excel
                </a>

                <div class="px-6 mt-6 mb-2 text-xs font-bold text-green-600 uppercase tracking-wider">Basis Data Master</div>
                
                <a href="/master-guru" class="flex items-center px-6 py-3 {{ request()->is('master-guru*') ? 'bg-green-50 text-green-700 border-r-4 border-green-600 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-green-600 transition font-medium' }}">
                    <i class="fas fa-chalkboard-teacher w-6"></i> Master Guru
                </a>
                <a href="/master-pelajaran" class="flex items-center px-6 py-3 {{ request()->is('master-pelajaran*') ? 'bg-green-50 text-green-700 border-r-4 border-green-600 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-green-600 transition font-medium' }}">
                    <i class="fas fa-book-open w-6"></i> Master Pelajaran
                </a>
                <a href="/master-kelas" class="flex items-center px-6 py-3 {{ request()->is('master-kelas*') ? 'bg-green-50 text-green-700 border-r-4 border-green-600 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-green-600 transition font-medium' }}">
                    <i class="fas fa-school w-6"></i> Master Kelas
                </a>

                <div class="px-6 mt-6 mb-2 text-xs font-bold text-green-600 uppercase tracking-wider">Akademik & Jadwal</div>
                
                <a href="/master-plot-jadwal" class="flex items-center px-6 py-3 {{ request()->is('master-plot-jadwal*') ? 'bg-green-50 text-green-700 border-r-4 border-green-600 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-green-600 transition font-medium' }}">
                    <i class="fas fa-sitemap w-6"></i> Target Mengajar
                </a>
                <a href="/master-jadwal-harian" class="flex items-center px-6 py-3 {{ request()->is('master-jadwal-harian*') ? 'bg-green-50 text-green-700 border-r-4 border-green-600 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-green-600 transition font-medium' }}">
                    <i class="fas fa-calendar-alt w-6"></i> Jadwal Harian
                </a>
                
                <div class="px-6 py-2 mt-6 text-xs font-bold text-gray-400 uppercase tracking-wider border-t border-gray-100 pt-4">Setup & Lainnya</div>
                
                <a href="#" class="flex items-center px-6 py-3 text-gray-600 hover:bg-gray-50 transition font-medium">
                    <i class="fas fa-chart-pie w-6"></i> Statistik
                </a>
                <a href="/setup-user" class="flex items-center px-6 py-3 {{ request()->is('setup-user') || request()->is('user*') ? 'bg-green-500 text-white font-semibold shadow-md rounded-r-full mr-4' : 'text-gray-600 hover:bg-gray-50 hover:text-green-600 transition font-medium' }}">
                    <i class="fas fa-users-cog w-6"></i> User
                </a>
            </nav>
        </div>
    </aside>

    <div class="flex-1 flex flex-col overflow-hidden">
        
       <header class="relative h-16 bg-white border-b border-gray-200 flex items-center justify-between px-6 z-50">
            
            <div class="hidden md:block text-gray-600 font-medium">
                Assalamu'alaikum, Selamat datang di Muadalah Wustha Maqna'ul Ulum!
            </div>

            <div class="flex items-center space-x-4 text-gray-600 relative">
                <button class="hover:text-green-600 transition"><i class="fas fa-bell text-lg"></i></button>
                
                <div class="relative">
                    <button onclick="toggleUserMenu()" class="flex items-center space-x-2 focus:outline-none hover:bg-gray-50 p-1 rounded-lg transition">
                        <div class="text-right hidden md:block">
                            <p class="text-xs text-gray-500 font-bold uppercase tracking-wider">{{ auth()->user()->role ?? 'ADMIN INSTANSI' }}</p>
                            <p class="text-sm font-bold text-green-700">{{ auth()->user()->name ?? 'Nama User' }}</p>
                        </div>
                        <div class="h-9 w-9 rounded-full bg-green-600 flex items-center justify-center text-white shadow-sm border-2 border-white">
                            <i class="fas fa-user"></i>
                        </div>
                        <i class="fas fa-chevron-down text-xs text-gray-400"></i>
                    </button>

                    <div id="user-dropdown" class="hidden absolute right-0 mt-2 w-64 bg-white rounded-xl shadow-2xl border border-gray-100 z-50 overflow-hidden transform transition-all">
                        <div class="p-4 border-b border-gray-100 flex items-center space-x-3 bg-gray-50">
                            <div class="h-12 w-12 rounded-full bg-gray-700 flex items-center justify-center text-white flex-shrink-0 shadow-inner">
                                <i class="fas fa-user text-xl"></i>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-gray-800">{{ auth()->user()->name ?? 'Nama User' }}</p>
                                <p class="text-xs text-gray-500 font-semibold">{{ auth()->user()->role ?? 'Role' }}</p>
                            </div>
                        </div>
                        
                        <div class="p-2">
                            <button type="button" onclick="bukaModalGantiPassword()" class="w-full flex items-center px-4 py-2.5 text-sm font-semibold text-gray-600 hover:bg-gray-50 rounded-lg transition text-left cursor-pointer">
                                <i class="fas fa-key w-6 text-gray-400"></i> Ganti Password
                            </button>
                        </div>
                        
                        <div class="p-3 border-t border-gray-100 bg-gray-50">
                            <form method="POST" action="/logout">
                                @csrf
                                <button type="submit" class="flex items-center justify-center w-full px-4 py-2 text-sm font-bold text-white bg-red-500 hover:bg-red-600 rounded-lg transition shadow-sm cursor-pointer">
                                    Logout <i class="fas fa-sign-out-alt ml-2"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </header>

        <main class="flex-1 overflow-y-auto p-6 bg-gray-50 relative">

        @if(session('sukses'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded shadow-sm">
                    <i class="fas fa-check-circle mr-2"></i> {{ session('sukses') }}
                </div>
            @endif
            
            @if(session('error'))
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded shadow-sm">
                    <i class="fas fa-exclamation-triangle mr-2"></i> {{ session('error') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded shadow-sm">
                    <strong>Gagal menyimpan:</strong>
                    <ul class="list-disc ml-5 mt-1 text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')

            <div id="modal-ganti-password" class="hidden fixed inset-0 bg-gray-900 bg-opacity-60 overflow-y-auto h-full w-full z-50 flex items-center justify-center backdrop-blur-sm">
            <div class="relative mx-auto p-6 border w-full max-w-sm shadow-2xl rounded-xl bg-white">
                <div class="flex justify-between items-center mb-5 border-b pb-3">
                    <h3 class="text-lg font-bold text-gray-800">Ganti Password Anda</h3>
                    <button type="button" onclick="tutupModalGantiPassword()" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times text-xl"></i></button>
                </div>
                
                <form id="form-ganti-password" onsubmit="prosesGantiPassword(event)">
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Password Lama</label>
                        <input type="password" name="password_lama" required class="w-full border border-gray-300 rounded-lg p-2.5 outline-none focus:border-green-500" placeholder="Masukkan password saat ini">
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Password Baru</label>
                        <input type="password" name="password_baru" required minlength="6" class="w-full border border-gray-300 rounded-lg p-2.5 outline-none focus:border-green-500" placeholder="Minimal 6 karakter">
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Konfirmasi Password Baru</label>
                        <input type="password" name="password_baru_confirmation" required minlength="6" class="w-full border border-gray-300 rounded-lg p-2.5 outline-none focus:border-green-500" placeholder="Ketik ulang password baru">
                    </div>

                    <p id="pesan-notif-password" class="text-xs font-medium text-red-700 mb-3 hidden"></p>

                    <div class="flex justify-end space-x-2 border-t pt-4 mt-2">
                        <button type="button" onclick="tutupModalGantiPassword()" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg font-semibold hover:bg-gray-300 transition">Tutup</button>
                        <button type="submit" id="btn-simpan-password" class="px-4 py-2 bg-green-600 text-white rounded-lg font-semibold hover:bg-green-700 transition shadow-sm">Simpan Password</button>
                    </div>
                </form>
            </div>
        </div>

        </main>
    </div>

    <script>
        function updateClock() {
            const clockElement = document.getElementById('live-clock');
            if(clockElement) {
                const now = new Date();
                const hours = String(now.getHours()).padStart(2, '0');
                const minutes = String(now.getMinutes()).padStart(2, '0');
                const seconds = String(now.getSeconds()).padStart(2, '0');
                clockElement.innerText = hours + ':' + minutes + ':' + seconds;
            }
        }
        setInterval(updateClock, 1000);
        updateClock();

        // Fungsi untuk Pop-up Ganti Password
        function bukaModalGantiPassword() {
            document.getElementById('modal-ganti-password').classList.remove('hidden');
            document.getElementById('user-dropdown').classList.add('hidden'); // Tutup dropdown
            
            // FITUR BARU: Menyapu bersih sisa ketikan sebelumnya
            document.getElementById('form-ganti-password').reset();
            
            // Menyembunyikan sisa teks error sebelumnya (jika ada)
            let pesanNotif = document.getElementById('pesan-notif-password');
            pesanNotif.classList.add('hidden');
            pesanNotif.innerText = '';
        }
        
        function tutupModalGantiPassword() {
            document.getElementById('modal-ganti-password').classList.add('hidden');
        }

        function prosesGantiPassword(event) {
            event.preventDefault(); // Menahan layar agar tidak memuat ulang (reload)

            let passLama = document.getElementsByName('password_lama')[0].value;
            let passBaru = document.getElementsByName('password_baru')[0].value;
            let passKonfirm = document.getElementsByName('password_baru_confirmation')[0].value;
            let pesanNotif = document.getElementById('pesan-notif-password');
            let btnSimpan = document.getElementById('btn-simpan-password');

            // Ubah tombol jadi loading
            btnSimpan.innerText = "Memproses...";
            btnSimpan.disabled = true;

            let token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            // Mengirim data ke brankas untuk dicek sesuai urutan prioritas Controller
            fetch('/ganti-password', {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token
                },
                body: JSON.stringify({
                    password_lama: passLama,
                    password_baru: passBaru,
                    password_baru_confirmation: passKonfirm
                })
            })
            .then(async response => {
                let data = await response.json();
                if (!response.ok) {
                    // Menangkap teguran dari Controller (Password Lama Salah / Konfirmasi Tidak Cocok)
                    throw new Error(data.pesan || data.message || "Terjadi kesalahan sistem.");
                }
                return data;
            })
            .then(data => {
                // JIKA SUKSES
                document.getElementById('form-ganti-password').reset();
                btnSimpan.innerText = "Simpan Password";
                btnSimpan.disabled = false;
                pesanNotif.classList.add('hidden'); 

                tutupModalGantiPassword();

                // Memunculkan Notifikasi di luar Pop-up
                let notifUtama = `
                    <div id="notif-sukses-ajax" class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded shadow-sm transition-all">
                        <i class="fas fa-check-circle mr-2"></i> ${data.pesan}
                    </div>
                `;
                document.querySelector('main').insertAdjacentHTML('afterbegin', notifUtama);

                setTimeout(() => {
                    let elemenNotif = document.getElementById('notif-sukses-ajax');
                    if(elemenNotif) elemenNotif.remove();
                }, 4000);
            })
            .catch((error) => {
                // JIKA GAGAL (Memunculkan teks error merah di urutan teratas form)
                pesanNotif.innerText = "❌ " + error.message;
                pesanNotif.className = "text-xs font-medium text-red-700 mb-4 block";
                
                // Kembalikan status tombol
                btnSimpan.innerText = "Simpan Password";
                btnSimpan.disabled = false;
            });
        }

        // Menampilkan & Menyembunyikan Dropdown User
        function toggleUserMenu() {
            const menu = document.getElementById('user-dropdown');
            menu.classList.toggle('hidden');
        }

        // Otomatis menutup dropdown jika klik sembarang di luar menu
        window.addEventListener('click', function(e) {
            const menu = document.getElementById('user-dropdown');
            const button = menu.previousElementSibling;
            if (!button.contains(e.target) && !menu.contains(e.target)) {
                menu.classList.add('hidden');
            }
        });

    </script>
    @stack('scripts')

    <script>
        document.addEventListener('submit', function(e) {
            let form = e.target;
            
            // Abaikan form yang sudah memiliki animasi loading tersendiri (seperti form AJAX)
            if (form.id === 'form-ganti-password') return;

            // Cari tombol submit di dalam form yang ditekan
            let btnSubmit = form.querySelector('button[type="submit"]');
            if (btnSubmit) {
                // Cegah klik ganda jika sudah loading
                if (btnSubmit.disabled) {
                    e.preventDefault();
                    return;
                }
                
                // Kunci tombol, jadikan buram, dan putar ikon loading
                btnSubmit.disabled = true;
                btnSubmit.classList.add('opacity-75', 'cursor-not-allowed');
                btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Memproses...';
            }
        });
    </script>
    
</body>
</html>