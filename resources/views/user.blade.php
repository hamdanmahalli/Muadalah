@extends('layouts.app')

@section('title', 'Setup User - SmartPesantren')

@section('content')
    <div class="bg-white p-6 rounded-xl shadow-sm mb-6 border border-gray-100 flex justify-between items-center">
        <h2 class="text-xl font-bold text-gray-700">User | PP. Maqnaul Ulum</h2>
        <button onclick="bukaModalTambah()" class="bg-emerald-500 hover:bg-emerald-600 text-white px-5 py-2.5 rounded-lg text-sm font-semibold transition flex items-center shadow-sm">
            <i class="fas fa-plus mr-2"></i> Tambah
        </button>
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
        <div class="p-4 flex justify-between items-center border-b border-gray-100 bg-gray-50/50">
            <div class="flex items-center text-sm text-gray-500">
                <span class="mr-2">Show</span>
                <select class="border border-gray-300 rounded p-1 outline-none focus:border-emerald-500"><option>50</option></select>
                <span class="ml-2">entries</span>
            </div>
            <div class="flex items-center text-sm text-gray-500">
                <span class="mr-2">Search:</span>
                <input type="text" class="border border-gray-300 rounded p-1.5 outline-none focus:border-emerald-500">
            </div>
        </div>
        
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-white">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider border-b">ID</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider border-b">USERNAME</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider border-b">NAMA</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider border-b">EMAIL</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider border-b">HP</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider border-b">ROLE</th>
                    <th class="px-6 py-4 text-center text-xs font-bold text-gray-600 uppercase tracking-wider border-b">STATUS</th>
                    <th class="px-6 py-4 text-center text-xs font-bold text-gray-600 uppercase tracking-wider border-b">AKSI</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
                @foreach($users as $index => $user)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $index + 1 }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $user->username }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 font-medium">{{ $user->name }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $user->email }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $user->hp ?? '-' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $user->role }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                        <i class="fas fa-eye {{ $user->status == 'Aktif' ? 'text-emerald-500' : 'text-gray-400' }} text-lg" title="{{ $user->status }}"></i>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium space-x-1 flex justify-center">
                        <button class="w-8 h-8 rounded-full bg-cyan-50 text-cyan-500 hover:bg-cyan-500 hover:text-white transition flex items-center justify-center"><i class="fas fa-search"></i></button>
                        <button onclick='bukaModalEdit(@json($user))' class="w-8 h-8 rounded-full bg-orange-50 text-orange-400 hover:bg-orange-400 hover:text-white transition flex items-center justify-center"><i class="fas fa-edit"></i></button>
                        
                        <button onclick="bukaModalReset({{ $user->id }}, '{{ $user->name }}')" title="Reset Password ke 123456" class="w-8 h-8 inline-flex rounded-full bg-emerald-50 text-emerald-500 hover:bg-emerald-500 hover:text-white transition items-center justify-center"><i class="fas fa-sync-alt"></i></button>

                        <button onclick="bukaModalHapus({{ $user->id }}, '{{ $user->name }}')" title="Hapus User" class="w-8 h-8 inline-flex rounded-full bg-red-50 text-red-500 hover:bg-red-500 hover:text-white transition items-center justify-center"><i class="fas fa-trash-alt"></i></button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div id="modal-user" class="hidden fixed inset-0 bg-gray-900 bg-opacity-60 overflow-y-auto h-full w-full z-50 flex items-center justify-center backdrop-blur-sm">
        <div class="relative mx-auto p-6 border w-full max-w-2xl shadow-2xl rounded-xl bg-white">
            <div class="flex justify-between items-center mb-5 border-b pb-3">
                <h3 class="text-lg font-bold text-gray-800" id="modal-judul">Tambah User</h3>
                <button onclick="tutupModal()" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times text-xl"></i></button>
            </div>
            
            <form id="form-user" method="POST" action="/setup-user">
                @csrf
                <input type="hidden" name="_method" id="form-method" value="POST">
                
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Nama Lengkap</label>
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

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Email</label>
                        <input type="email" name="email" id="input-email" required class="w-full border border-gray-300 rounded-lg p-2.5 outline-none focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">No HP</label>
                        <input type="text" name="hp" id="input-hp" class="w-full border border-gray-300 rounded-lg p-2.5 outline-none focus:border-emerald-500">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Role / Hak Akses</label>
                        <div class="relative">
                            <select name="role" id="input-role" class="w-full border border-gray-300 rounded-lg p-2.5 appearance-none outline-none focus:border-emerald-500 bg-white cursor-pointer">
                                <option value="ADMIN INSTANSI">ADMIN INSTANSI</option>
                                <option value="OPERATOR INSTANSI">OPERATOR INSTANSI</option>
                                <option value="ADMIN PONDOK">ADMIN PONDOK</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500">
                                <i class="fas fa-chevron-down text-sm"></i>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Status</label>
                        <select name="status" id="input-status" class="w-full border border-gray-300 rounded-lg p-2.5 outline-none focus:border-emerald-500 bg-white">
                            <option value="Aktif">Aktif</option>
                            <option value="Nonaktif">Nonaktif</option>
                        </select>
                    </div>
                </div>

                <div class="flex justify-end space-x-2 border-t pt-4">
                    <button type="button" onclick="tutupModal()" class="px-5 py-2.5 bg-gray-200 text-gray-800 rounded-lg font-semibold hover:bg-gray-300 transition">Batal</button>
                    <button type="submit" class="px-5 py-2.5 bg-emerald-500 text-white rounded-lg font-semibold hover:bg-emerald-600 transition shadow-sm">Simpan Data</button>
                </div>
            </form>
        </div>
    </div>

    <div id="modal-reset" class="hidden fixed inset-0 bg-gray-900 bg-opacity-60 overflow-y-auto h-full w-full z-50 flex items-center justify-center backdrop-blur-sm transition-opacity">
        <div class="relative mx-auto p-6 border w-full max-w-sm shadow-2xl rounded-xl bg-white text-center transform transition-all">
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-yellow-100 mb-5">
                <i class="fas fa-exclamation-triangle text-2xl text-yellow-600"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-800 mb-2">Konfirmasi Reset</h3>
            <p class="text-sm text-gray-500 mb-6">Yakin ingin mereset password untuk <br><strong id="reset-nama-user" class="text-gray-800 text-base"></strong><br>menjadi <strong class="text-emerald-600 text-base">123456</strong>?</p>
            
            <form id="form-reset" method="POST" action="">
                @csrf @method('PUT')
                <div class="flex justify-center space-x-3">
                    <button type="button" onclick="tutupModalReset()" class="px-5 py-2.5 bg-gray-200 text-gray-800 rounded-lg font-semibold hover:bg-gray-300 transition w-full">Batal</button>
                    <button type="submit" class="px-5 py-2.5 bg-yellow-500 text-white rounded-lg font-semibold hover:bg-yellow-600 transition shadow-sm w-full">Ya, Reset</button>
                </div>
            </form>
        </div>
    </div>

    <div id="modal-hapus" class="hidden fixed inset-0 bg-gray-900 bg-opacity-60 overflow-y-auto h-full w-full z-50 flex items-center justify-center backdrop-blur-sm transition-opacity">
        <div class="relative mx-auto p-6 border w-full max-w-sm shadow-2xl rounded-xl bg-white text-center transform transition-all">
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100 mb-5">
                <i class="fas fa-trash-alt text-2xl text-red-600"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-800 mb-2">Konfirmasi Hapus</h3>
            <p class="text-sm text-gray-500 mb-6">Yakin ingin menghapus user <br><strong id="hapus-nama-user" class="text-gray-800 text-base"></strong>?<br><span class="text-xs text-red-500 mt-2 block"></span></p>
            
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

        function bukaModalReset(id, nama) {
            document.getElementById('modal-reset').classList.remove('hidden');
            document.getElementById('reset-nama-user').innerText = nama;
            document.getElementById('form-reset').action = "/setup-user/" + id + "/reset-password";
        }

        function tutupModalReset() {
            document.getElementById('modal-reset').classList.add('hidden');
        }

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
            document.getElementById('modal-judul').innerText = "Tambah User";
            document.getElementById('form-user').action = "/setup-user";
            document.getElementById('form-method').value = "POST";
            document.getElementById('form-user').reset();
        }

        function bukaModalEdit(user) {
            document.getElementById('modal-user').classList.remove('hidden');
            document.getElementById('modal-judul').innerText = "Edit Data User";
            document.getElementById('form-user').action = "/setup-user/" + user.id;
            document.getElementById('form-method').value = "PUT";
            
            document.getElementById('input-name').value = user.name;
            document.getElementById('input-username').value = user.username;
            document.getElementById('input-email').value = user.email;
            document.getElementById('input-hp').value = user.hp || '';
            document.getElementById('input-role').value = user.role;
            document.getElementById('input-status').value = user.status;
        }

        function tutupModal() {
            document.getElementById('modal-user').classList.add('hidden');
        }
    </script>
@endsection