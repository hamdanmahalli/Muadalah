@extends('layouts.app')

@section('title', 'Setup User & Hak Akses - SmartPesantren')

@section('content')
    <div class="bg-white p-6 rounded-xl shadow-sm mb-6 border border-gray-100 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
        <div>
            <h2 class="text-xl font-bold text-gray-700">Setup User & Hak Akses (Multi-Role)</h2>
            <p class="text-xs text-gray-500 mt-0.5">Kelola akun pengguna dan tentukan hak akses jabatan (Admin, TU, Guru, dll).</p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <button onclick="bukaModalTambah()" class="bg-emerald-500 hover:bg-emerald-600 text-white px-5 py-2.5 rounded-lg text-sm font-semibold transition flex items-center shadow-sm cursor-pointer">
                <i class="fas fa-plus mr-2"></i> Tambah User
            </button>
        </div>
    </div>

    @if ($errors->any())
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded shadow-sm">
            <strong>Gagal menyimpan data:</strong>
            <ul class="list-disc ml-5 mt-1 text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider border-b w-12">NO</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider border-b">USERNAME</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider border-b">NAMA LENGKAP</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider border-b hidden md:table-cell">EMAIL / HP</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider border-b hidden md:table-cell">HAK AKSES / JABATAN</th>
                    <th class="px-6 py-4 text-center text-xs font-bold text-gray-600 uppercase tracking-wider border-b">STATUS</th>
                    <th class="px-6 py-4 text-center text-xs font-bold text-gray-600 uppercase tracking-wider border-b w-32">AKSI</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
                @foreach($users as $index => $user)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 font-bold">{{ $index + 1 }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 font-semibold">{{ $user->username }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-bold">{{ $user->name }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 hidden md:table-cell">
                        <div>{{ $user->email }}</div>
                        <div class="text-xs text-gray-400">{{ $user->hp ?? '-' }}</div>
                    </td>
                    
                    <td class="px-6 py-4 whitespace-normal text-sm hidden md:table-cell">
                        @forelse($user->getRoleNames() as $roleName)
                            <span class="inline-block px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 mr-1 mb-1">
                                <i class="fas fa-user-shield mr-1 text-[10px]"></i> {{ $roleName }}
                            </span>
                        @empty
                            <span class="text-gray-400 text-xs italic">Tanpa Role</span>
                        @endforelse
                    </td>

                    <td class="px-6 py-4 whitespace-nowrap text-center">
                        <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $user->status == 'Aktif' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                            {{ $user->status }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium space-x-1 flex justify-center">
                        <button onclick='bukaModalEdit(@json($user), @json($user->getRoleNames()))' class="w-8 h-8 rounded-full bg-orange-50 text-orange-500 hover:bg-orange-500 hover:text-white transition flex items-center justify-center cursor-pointer" title="Edit User & Role">
                            <i class="fas fa-edit"></i>
                        </button>
                        
                        <form method="POST" action="/setup-user/{{ $user->id }}/reset-password" class="inline" onsubmit="return confirm('Reset sandi untuk {{ js_q($user->name) }} menjadi sandi acak baru? Hasilnya akan ditampilkan di layar.');">
                            @csrf @method('PUT')
                            <button type="submit" title="Reset Sandi (Sandi Acak Baru)" class="w-8 h-8 inline-flex rounded-full bg-emerald-50 text-emerald-600 hover:bg-emerald-600 hover:text-white transition items-center justify-center cursor-pointer">
                                <i class="fas fa-key"></i>
                            </button>
                        </form>

                        <button onclick="bukaModalHapus({{ $user->id }}, '{{ js_q($user->name) }}')" title="Hapus User" class="w-8 h-8 inline-flex rounded-full bg-red-50 text-red-500 hover:bg-red-500 hover:text-white transition items-center justify-center cursor-pointer">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div id="modal-user" class="hidden fixed inset-0 bg-gray-900 bg-opacity-60 overflow-y-auto h-full w-full z-50 flex items-center justify-center backdrop-blur-sm">
        <div class="relative mx-auto p-6 border w-full max-w-lg shadow-2xl rounded-xl bg-white">
            <div class="flex justify-between items-center mb-5 border-b pb-3">
                <h3 class="text-lg font-bold text-gray-800" id="modal-judul">Tambah User</h3>
                <button onclick="tutupModal()" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times text-xl"></i></button>
            </div>
            
            <form id="form-user" method="POST" action="/setup-user">
                @csrf
                <input type="hidden" name="_method" id="form-method" value="POST">
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Nama Lengkap Guru / Staf</label>
                        <div class="relative">
                            <select name="name" id="input-name" required class="w-full border border-gray-300 rounded-lg p-2.5 appearance-none outline-none focus:border-emerald-500 bg-white cursor-pointer">
                                <option value="" disabled selected hidden>Pilih nama lengkap...</option>
                                @foreach($gurus as $guru)
                                    <option value="{{ $guru->nama_guru }}">{{ $guru->nama_guru }} (NIG: {{ $guru->nig }})</option>
                                @endforeach
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500">
                                <i class="fas fa-chevron-down text-sm"></i>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Username</label>
                        <input type="text" name="username" id="input-username" required class="w-full border border-gray-300 rounded-lg p-2.5 outline-none focus:border-emerald-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Email Log In</label>
                        <input type="email" name="email" id="input-email" required class="w-full border border-gray-300 rounded-lg p-2.5 outline-none focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">No. WhatsApp / HP</label>
                        <input type="text" name="hp" id="input-hp" class="w-full border border-gray-300 rounded-lg p-2.5 outline-none focus:border-emerald-500">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Hak Akses / Jabatan (Bisa Centang Lebih Dari 1)</label>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 bg-gray-50 p-3 rounded-xl border border-gray-200">
                        @foreach($roles as $role)
                            <label class="flex items-center space-x-2 p-2 bg-white rounded-lg border border-gray-200 cursor-pointer hover:bg-emerald-50 transition">
                                <input type="checkbox" name="roles[]" value="{{ $role->name }}" class="role-checkbox text-emerald-600 rounded focus:ring-emerald-500 w-4 h-4">
                                <span class="text-xs font-bold text-gray-700">{{ $role->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Status Akun</label>
                    <select name="status" id="input-status" class="w-full border border-gray-300 rounded-lg p-2.5 outline-none focus:border-emerald-500 bg-white">
                        <option value="Aktif">Aktif</option>
                        <option value="Nonaktif">Nonaktif</option>
                    </select>
                </div>

                <div class="flex justify-end space-x-2 border-t pt-4">
                    <button type="button" onclick="tutupModal()" class="px-5 py-2.5 bg-gray-200 text-gray-800 rounded-lg font-semibold hover:bg-gray-300 transition">Batal</button>
                    <button type="submit" class="px-5 py-2.5 bg-emerald-500 text-white rounded-lg font-semibold hover:bg-emerald-600 transition shadow-sm">Simpan Pengguna</button>
                </div>
            </form>
        </div>
    </div>

    <div id="modal-hasil-reset" class="hidden fixed inset-0 bg-gray-900 bg-opacity-60 overflow-y-auto h-full w-full z-50 flex items-center justify-center backdrop-blur-sm">
        <div class="relative mx-auto p-6 border w-full max-w-sm shadow-2xl rounded-xl bg-white text-center">
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-emerald-100 mb-5">
                <i class="fas fa-key text-2xl text-emerald-600"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-800 mb-2">Sandi Baru Berhasil Dibuat</h3>
            <p class="text-xs text-gray-400 mb-5">Salin lalu sebarkan ke guru. Sandi ini hanya tampil sekali dan akan hilang saat halaman ditutup/refresh.</p>

            <div class="bg-gray-50 border border-gray-200 rounded-xl p-5 mb-5 text-left">
                <div class="flex items-center justify-between mb-3">
                    <span id="hasil-nama" class="text-sm font-semibold text-gray-700">{{ session('hasil_reset')['nama'] ?? '' }}</span>
                    <button type="button" id="btn-salin-hasil" onclick="salinSandi({{ json_encode(session('hasil_reset')['username'] ?? '') }}, {{ json_encode(session('hasil_reset')['sandi'] ?? '') }}, this)" class="shrink-0 w-9 h-9 rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white transition flex items-center justify-center cursor-pointer" title="Salin Username & Sandi">
                        <i class="fas fa-copy text-sm"></i>
                    </button>
                </div>
                <div class="grid grid-cols-2 gap-3 text-sm">
                    <div>
                        <div class="text-xs text-gray-400 font-semibold">USERNAME</div>
                        <div id="hasil-username" class="font-bold text-gray-800">{{ session('hasil_reset')['username'] ?? '-' }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-400 font-semibold">SANDI BARU</div>
                        <div id="hasil-sandi" class="font-bold text-emerald-600">{{ session('hasil_reset')['sandi'] ?? '-' }}</div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="button" onclick="tutupModalHasilReset()" class="px-5 py-2.5 bg-gray-200 text-gray-800 rounded-lg font-semibold hover:bg-gray-300 transition w-full">Tutup</button>
            </div>
        </div>
    </div>

    <div id="modal-hapus" class="hidden fixed inset-0 bg-gray-900 bg-opacity-60 overflow-y-auto h-full w-full z-50 flex items-center justify-center backdrop-blur-sm">
        <div class="relative mx-auto p-6 border w-full max-w-sm shadow-2xl rounded-xl bg-white text-center">
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100 mb-5">
                <i class="fas fa-trash-alt text-2xl text-red-600"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-800 mb-2">Konfirmasi Hapus</h3>
            <p class="text-sm text-gray-500 mb-6">Yakin ingin menghapus user <br><strong id="hapus-nama-user" class="text-gray-800 text-base"></strong>?</p>
            
            <form id="form-hapus" method="POST" action="">
                @csrf @method('DELETE')
                <div class="flex justify-center space-x-3">
                    <button type="button" onclick="tutupModalHapus()" class="px-5 py-2.5 bg-gray-200 text-gray-800 rounded-lg font-semibold hover:bg-gray-300 transition w-full">Batal</button>
                    <button type="submit" class="px-5 py-2.5 bg-red-500 text-white rounded-lg font-semibold hover:bg-red-600 transition shadow-sm w-full">Ya, Hapus</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        @if(session()->has('hasil_reset'))
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('modal-hasil-reset').classList.remove('hidden');
        });
        @endif

        function bukaModalHapus(id, nama) {
            document.getElementById('modal-hapus').classList.remove('hidden');
            document.getElementById('hapus-nama-user').innerText = nama;
            document.getElementById('form-hapus').action = "/setup-user/" + id;
        }

        function tutupModalHapus() {
            document.getElementById('modal-hapus').classList.add('hidden');
        }

        function bukaModalTambah() {
            document.getElementById('modal-user').classList.remove('hidden');
            document.getElementById('modal-judul').innerText = "Tambah User Baru";
            document.getElementById('form-user').action = "/setup-user";
            document.getElementById('form-method').value = "POST";
            document.getElementById('form-user').reset();

            // Uncheck semua role
            document.querySelectorAll('.role-checkbox').forEach(cb => cb.checked = false);
        }

        function bukaModalEdit(user, userRoles) {
            document.getElementById('modal-user').classList.remove('hidden');
            document.getElementById('modal-judul').innerText = "Edit User & Hak Akses";
            document.getElementById('form-user').action = "/setup-user/" + user.id;
            document.getElementById('form-method').value = "PUT";
            
            document.getElementById('input-name').value = user.name;
            document.getElementById('input-username').value = user.username;
            document.getElementById('input-email').value = user.email;
            document.getElementById('input-hp').value = user.hp || '';
            document.getElementById('input-status').value = user.status;

            // Centang otomatis checkbox role yang dimiliki user
            document.querySelectorAll('.role-checkbox').forEach(cb => {
                cb.checked = userRoles.includes(cb.value);
            });
        }

        function tutupModal() {
            document.getElementById('modal-user').classList.add('hidden');
        }

        function tutupModalHasilReset() {
            document.getElementById('modal-hasil-reset').classList.add('hidden');
        }

        function salinSandi(username, sandi, btn) {
            var nama = document.getElementById('hasil-nama').innerText || '';
            var teks = 'Nama: ' + nama + '\nUsername: ' + username + '\nSandi: ' + sandi;
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(teks).then(function() {
                    tandaiTersalin(btn);
                });
            } else {
                var area = document.createElement('textarea');
                area.value = teks;
                document.body.appendChild(area);
                area.select();
                document.execCommand('copy');
                document.body.removeChild(area);
                tandaiTersalin(btn);
            }
        }

        function tandaiTersalin(btn) {
            var asli = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-check text-sm"></i>';
            btn.classList.remove('bg-indigo-50', 'text-indigo-600');
            btn.classList.add('bg-emerald-500', 'text-white');
            setTimeout(function() {
                btn.innerHTML = asli;
                btn.classList.add('bg-indigo-50', 'text-indigo-600');
                btn.classList.remove('bg-emerald-500', 'text-white');
            }, 1500);
        }
    </script>
@endsection