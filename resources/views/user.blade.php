@extends('layouts.app')

@section('title', 'Setup User - SmartPesantren')

@section('content')
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800"><i class="fas fa-users-cog mr-2 text-green-600"></i> Setup User</h2>
            <p class="text-sm text-gray-500 mt-1">Kelola akun, status, dan fasilitas menu (hak akses) per pengguna.</p>
        </div>
        @role('Administrator')
        <button onclick="bukaModalTambah()" class="bg-emerald-500 hover:bg-emerald-600 text-white px-5 py-2 rounded-xl text-sm font-semibold shadow transition flex items-center cursor-pointer">
            <i class="fas fa-user-plus mr-2"></i> Tambah User
        </button>
        @else
        <span class="text-xs text-gray-400 font-semibold hidden lg:block">Anda hanya dapat melihat daftar &amp; me-reset sandi.</span>
        @endrole
    </div>

    @if(session('sukses'))
        <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded shadow-sm text-sm font-semibold">
            <i class="fas fa-check-circle mr-2"></i>{{ session('sukses') }}
        </div>
    @endif
    @if(session('error'))
        <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded shadow-sm text-sm font-semibold">
            <i class="fas fa-exclamation-triangle mr-2"></i>{{ session('error') }}
        </div>
    @endif
    @if ($errors->any())
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded shadow-sm">
            <strong>Gagal menyimpan:</strong>
            <ul class="list-disc ml-5 mt-1 text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- ================= TABEL DATA USER (gaya Excel) ================= --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-200 bg-gray-50/70 flex items-center gap-2">
            <h3 class="text-sm font-bold text-gray-700"><i class="fas fa-table mr-2 text-gray-400"></i>Data User</h3>
        </div>

        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase w-10">No</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Nama User</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase hidden md:table-cell">Akses</th>
                    <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase w-24 hidden md:table-cell">Status</th>
                    <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase w-52">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($users as $index => $user)
                <tr class="hover:bg-gray-50 transition baris-user {{ !$user->hasRole('Administrator') ? 'cursor-pointer' : '' }}"
                    data-nama="{{ js_q($user->name) }}"
                    data-username="{{ js_q($user->username) }}"
                    data-id="{{ $user->id }}"
                    data-locked="{{ $user->hasRole('Administrator') ? '1' : '0' }}"
                    data-email="{{ js_q($user->email) }}"
                    data-hp="{{ js_q($user->hp ?? '') }}"
                    @if(auth()->user()->hasRole('Administrator') && !$user->hasRole('Administrator')) onclick="pilihUser(this)" @endif>
                    <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500">{{ $index + 1 }}</td>
                    <td class="px-6 py-3 whitespace-nowrap">
                        <p class="text-sm font-bold text-gray-800">{{ $user->name }}</p>
                        <p class="text-[11px] text-gray-400">{{ $user->username }}</p>
                    </td>
                    <td class="px-6 py-3 whitespace-nowrap hidden md:table-cell">
                        <span class="inline-flex items-center gap-1 px-2 py-1 text-[10px] font-bold rounded-lg {{ $user->hasRole('Administrator') ? 'bg-rose-50 text-rose-600 border border-rose-100' : 'bg-blue-50 text-blue-600 border border-blue-100' }}">
                            @if($user->hasRole('Administrator'))
                                <i class="fas fa-crown text-[9px]"></i> Kunci Semua
                            @else
                                <i class="fas fa-key text-[9px]"></i> {{ $user->aksesLabel() }}
                            @endif
                        </span>
                    </td>
                    <td class="px-6 py-3 whitespace-nowrap text-center hidden md:table-cell">
                        <span class="px-3 py-1 text-[10px] font-bold uppercase tracking-wider rounded-full {{ $user->status == 'Nonaktif' ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }}">{{ $user->status }}</span>
                    </td>
                    <td class="px-6 py-3 whitespace-nowrap text-center text-sm font-medium">
                        <div class="flex justify-center space-x-1.5">
                            @if(auth()->user()->hasRole('Administrator') && !$user->hasRole('Administrator'))
                            <button onclick="event.stopPropagation(); pilihUser(this.closest('tr'))" title="Fasilitas Menu (hak akses)"
                                    class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 hover:bg-emerald-500 hover:text-white transition flex items-center justify-center cursor-pointer">
                                <i class="fas fa-bars"></i>
                            </button>
                            @endif
                            @php($bisaKelola = auth()->user()->hasRole('Administrator') && !$user->hasRole('Administrator'))
                            @if($bisaKelola)
                            <button onclick="event.stopPropagation(); bukaModalEdit(this.closest('tr'))" title="Edit"
                                    class="w-8 h-8 rounded-lg bg-orange-50 text-orange-400 hover:bg-orange-400 hover:text-white transition flex items-center justify-center cursor-pointer">
                                <i class="fas fa-edit"></i>
                            </button>
                            @endif
                            <button onclick="event.stopPropagation(); resetSandi(this.closest('tr'))" title="Reset Sandi"
                                    class="w-8 h-8 rounded-lg bg-amber-50 text-amber-500 hover:bg-amber-400 hover:text-white transition flex items-center justify-center cursor-pointer">
                                <i class="fas fa-key"></i>
                            </button>
                            @if($bisaKelola)
                            <button onclick="event.stopPropagation(); bukaModalHapus(this.closest('tr'))" title="Hapus"
                                    class="w-8 h-8 rounded-lg bg-red-50 text-red-500 hover:bg-red-500 hover:text-white transition flex items-center justify-center cursor-pointer">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                            @endif
                            @if($user->hasRole('Administrator'))
                            <span title="Akun Administrator terkunci" class="w-8 h-8 rounded-lg bg-rose-50 text-rose-400 flex items-center justify-center cursor-not-allowed">
                                <i class="fas fa-lock"></i>
                            </span>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-8 text-center text-gray-400 text-sm">
                        <i class="fas fa-users-cog text-3xl mb-3 opacity-50 block"></i>Belum ada pengguna selain Administrator.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- ================= POPUP FASILITAS MENU ================= --}}
    <div id="modal-fasilitas" class="hidden fixed inset-0 bg-gray-900/60 backdrop-blur-sm z-50 flex overflow-y-auto p-3">
        <div class="relative m-auto border w-full max-w-3xl shadow-2xl rounded-2xl bg-white flex flex-col">
            {{-- Header --}}
            <div class="px-6 py-4 flex items-center gap-3 border-b border-gray-100 shrink-0 pr-14">
                <div class="h-10 w-10 rounded-xl bg-blue-100 flex items-center justify-center shrink-0">
                    <i class="fas fa-user text-blue-600 text-sm"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2 flex-wrap">
                        <p id="spn-nama" class="text-base font-black text-gray-800 truncate"></p>
                        <span id="badge-terkunci" class="hidden text-[10px] font-black px-2 py-0.5 rounded-full bg-rose-50 text-rose-600 border border-rose-200">
                            <i class="fas fa-lock mr-1"></i>SUPER ADMIN
                        </span>
                    </div>
                    <p class="text-[11px] text-gray-400"><span id="spn-username"></span> &middot; <span id="spn-status" class="font-bold text-gray-500"></span></p>
                </div>
            </div>
            <button onclick="tutupModalFasilitas()" class="absolute top-3 right-4 w-9 h-9 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition flex items-center justify-center cursor-pointer">
                <i class="fas fa-times text-lg"></i>
            </button>

            <form id="form-save" method="POST" class="flex-1 min-h-0 flex flex-col px-6 py-4">
                @csrf @method('PUT')
                <input type="hidden" id="target-user-id" value="">

                <div class="flex items-center justify-between gap-3 mb-3 shrink-0">
                    <p class="text-[11px] font-black text-gray-400 uppercase tracking-widest">
                        <i class="fas fa-list-check mr-1 text-emerald-500"></i> Fasilitas Menu
                    </p>
                    <div class="flex items-center gap-2">
                        <button type="button" onclick="setSemua(true)" class="px-3 py-1.5 bg-blue-50 border border-blue-200 text-blue-600 hover:bg-blue-500 hover:text-white rounded-lg text-xs font-bold transition cursor-pointer">
                            <i class="fas fa-check-double mr-1"></i>Centang Semua
                        </button>
                        <button type="button" onclick="setSemua(false)" class="px-3 py-1.5 bg-gray-50 border border-gray-200 text-gray-500 hover:bg-gray-400 hover:text-white rounded-lg text-xs font-bold transition cursor-pointer">
                            <i class="fas fa-ban mr-1"></i>Kosongkan
                        </button>
                    </div>
                </div>

                <div class="overflow-hidden border border-gray-200 rounded-xl flex-1 min-h-0 flex flex-col">
                    <div id="grup-menu" class="overflow-y-auto divide-y divide-gray-100"></div>
                </div>

                <div class="mt-4 rounded-xl border border-gray-200 bg-gray-50/70 px-4 py-3 flex items-center flex-wrap gap-2 shrink-0">
                    <button type="button" id="btn-hapus-semua" onclick="hapusSemuaFasilitas()" class="px-3 py-2 bg-red-50 border border-red-200 hover:bg-red-500 hover:text-white text-red-600 rounded-lg text-xs font-bold transition flex items-center gap-1.5 cursor-pointer">
                        <i class="fas fa-trash-alt text-[11px]"></i> Hapus Semua Fasilitas
                    </button>
                    <div class="flex items-center gap-2 ml-auto">
                        <span id="spn-count" class="hidden text-[11px] font-bold text-gray-400"></span>
                        <button type="submit" class="px-5 py-2 bg-emerald-500 hover:bg-emerald-600 text-white rounded-lg text-sm font-bold shadow transition flex items-center gap-2 cursor-pointer">
                            <i class="fas fa-save text-xs"></i> Simpan Perubahan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <form id="form-hapus-semua" method="POST" class="hidden">
        @csrf @method('PUT')
    </form>

    {{-- ================= MODAL TAMBAH / EDIT USER ================= --}}
    <div id="modal-user" class="hidden fixed inset-0 bg-gray-900/60 overflow-y-auto h-full w-full z-50 flex items-center justify-center backdrop-blur-sm p-3">
        <div class="relative mx-auto p-6 border w-full max-w-lg shadow-2xl rounded-2xl bg-white">
            <div class="flex justify-between items-center mb-5 border-b pb-3">
                <h3 class="text-lg font-bold text-gray-800" id="modal-judul">Tambah User</h3>
                <button onclick="tutupModal()" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times text-xl"></i></button>
            </div>

            <form id="form-user" method="POST" action="{{ url('setup-user') }}">
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
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Username (NmUser)</label>
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

                <div class="mb-6">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Status Akun</label>
                    <select name="status" id="input-status" class="w-full border border-gray-300 rounded-lg p-2.5 outline-none focus:border-emerald-500 bg-white">
                        <option value="Aktif">Aktif</option>
                        <option value="Nonaktif">Nonaktif</option>
                    </select>
                    <p class="text-[11px] text-gray-400 mt-1">
                        Akun baru dibuat dengan <strong>hak akses kosong</strong> — beri fasilitas menu lewat tombol Fasilitas pada baris user tersebut.
                    </p>
                </div>

                <div class="flex justify-end space-x-2 border-t pt-4">
                    <button type="button" onclick="tutupModal()" class="px-5 py-2.5 bg-gray-200 text-gray-800 rounded-lg font-semibold hover:bg-gray-300 transition">Batal</button>
                    <button type="submit" class="px-5 py-2.5 bg-emerald-500 text-white rounded-lg font-semibold hover:bg-emerald-600 transition shadow-sm">Simpan Pengguna</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ================= MODAL HASIL RESET SANDI ================= --}}
    <div id="modal-hasil-reset" class="hidden fixed inset-0 bg-gray-900/60 overflow-y-auto h-full w-full z-50 flex items-center justify-center backdrop-blur-sm p-3">
        <div class="relative mx-auto p-6 border w-full max-w-sm shadow-2xl rounded-2xl bg-white text-center">
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

    {{-- ================= MODAL KONFIRMASI HAPUS USER ================= --}}
    <div id="modal-hapus" class="hidden fixed inset-0 bg-gray-900/60 overflow-y-auto h-full w-full z-50 flex items-center justify-center backdrop-blur-sm p-3">
        <div class="relative mx-auto p-6 border w-full max-w-sm shadow-2xl rounded-2xl bg-white text-center">
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
        const URL_AKSES = @json(url('setup-user/akses'));
        let userAktifId = null;
        let userAktifLocked = false;

        // ---------- POPUP FASILITAS MENU ----------
        function pilihUser(tr) {
            const dt = tr.dataset;
            userAktifId = dt.id;
            userAktifLocked = dt.locked === '1';
            document.getElementById('target-user-id').value = dt.id;
            document.getElementById('form-save').action = URL_AKSES + '/' + dt.id;
            document.getElementById('form-hapus-semua').action = URL_AKSES + '/' + dt.id + '/hapus-semua';

            document.getElementById('badge-terkunci').classList.toggle('hidden', !userAktifLocked);
            document.getElementById('btn-hapus-semua').disabled = userAktifLocked;
            document.getElementById('spn-nama').innerText = dt.nama;
            document.getElementById('spn-username').innerText = dt.username;

            document.getElementById('modal-fasilitas').classList.remove('hidden');
            document.getElementById('grup-menu').innerHTML = '<div class="p-10 text-center text-sm text-gray-400"><i class="fas fa-spinner fa-spin mr-2"></i>Memuat fasilitas...</div>';

            fetch(URL_AKSES + '/' + dt.id, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then(r => {
                    if (!r.ok) throw new Error('respons ' + r.status);
                    return r.json();
                })
                .then(data => {
                    document.getElementById('spn-status').innerText = data.status;
                    renderGrup(data.permissions);
                })
                .catch(e => {
                    document.getElementById('grup-menu').innerHTML = '<div class="p-10 text-center text-sm text-red-500"><i class="fas fa-exclamation-triangle mr-2"></i>Gagal memuat fasilitas (' + e.message + '). Segarkan halaman lalu coba lagi.</div>';
                    console.error(e);
                });
        }

        function tutupModalFasilitas() {
            document.getElementById('modal-fasilitas').classList.add('hidden');
        }

        // ---------- RENDER TREE-GRID FASILITAS ----------
        function renderGrup(permsAktif) {
            const wadah = document.getElementById('grup-menu');
            wadah.innerHTML = '';
            const grupMenus = @json($grupMenu);
            const ikonGrup = @json($ikonGrup);
            const warnaGrup = @json($warnaGrup);
            const warnaNamaGrup = @json($warnaNamaGrup);
            const sistem = @json($daftarSistem);

            Object.keys(grupMenus).forEach(namaGrup => {
                const items = grupMenus[namaGrup];
                const divGrup = document.createElement('div');
                divGrup.className = 'bg-white';
                divGrup.innerHTML = `
                    <button type="button" onclick="togglGrup(this)" class="w-full flex items-center gap-2.5 px-4 py-2.5 ${warnaGrup[namaGrup] || 'bg-gray-50'} hover:opacity-90 transition text-left">
                        <label class="flex items-center cursor-pointer shrink-0" onclick="event.stopPropagation()" title="Centang semua menu grup ${namaGrup}">
                            <input type="checkbox" class="centang-semua w-4 h-4 rounded text-blue-600 focus:ring-blue-500 cursor-pointer" onchange="centangSemuaGrup(this)">
                        </label>
                        <i class="fas fa-chevron-down text-[10px] text-gray-400 transition-transform grup-chevon"></i>
                        <i class="fas ${ikonGrup[namaGrup] || 'fa-th'} text-xs ${warnaNamaGrup[namaGrup] || 'text-gray-700'}"></i>
                        <span class="text-sm font-black ${warnaNamaGrup[namaGrup] || 'text-gray-700'} flex-1">${namaGrup}</span>
                        <span class="text-[10px] font-bold text-gray-400 grup-jumlah">0/0</span>
                    </button>
                    <div class="grup-isi"></div>`;

                wadah.appendChild(divGrup);
                const isi = divGrup.querySelector('.grup-isi');
                items.forEach(item => isi.appendChild(buatBaris(item, permsAktif, sistem)));

                // Grup tanpa isi ATAU tanpa satu pun menu dicentang -> default terlipat
                if (!isi.children.length || !isi.querySelector('input[name="permissions[]"]:checked')) {
                    isi.classList.add('hidden');
                    divGrup.querySelector('.grup-chevon').classList.add('rotate-180');
                }
            });

            perbaruiCount();
        }

        function buatBaris(item, permsAktif, sistem) {
            const wrap = document.createElement('div');
            walk(item.label, item.perm, 0);
            (item.sub || []).forEach(sb => walk(sb.label, sb.perm, 1));

            function walk(teks, kod, indent) {
                const row = document.createElement('div');
                row.className = 'flex items-center gap-2.5 px-4 py-2 hover:bg-gray-50 transition border-t border-gray-50';
                row.innerHTML = `
                    <input type="checkbox" name="permissions[]" value="${kod}" class="perm-checkbox w-4 h-4 rounded text-blue-600 focus:ring-blue-500 cursor-pointer ${indent ? 'ml-6' : ''}" ${permsAktif.includes(kod) ? 'checked' : ''}>
                    <span class="text-sm ${indent ? 'text-gray-600 font-normal pl-1 flex items-center gap-1.5' : 'font-semibold text-gray-700 flex items-center gap-1.5'}">
                        ${indent ? '<i class="fas fa-angle-right text-[9px] text-gray-300"></i>' : ''}${teks}
                        ${sistem.includes(kod) ? '<span class="text-[9px] font-black text-rose-400" title="Kunci sistem"><i class="fas fa-shield-alt"></i></span>' : ''}
                    </span>`;
                wrap.appendChild(row);
            }
            return wrap;
        }

        function togglGrup(btn) {
            btn.querySelector('.grup-chevon').classList.toggle('rotate-180');
            btn.nextElementSibling.classList.toggle('hidden');
        }

        function centangSemuaGrup(checkbox) {
            const isi = checkbox.closest('button').nextElementSibling;
            isi.querySelectorAll('input[name="permissions[]"]').forEach(cb => cb.checked = checkbox.checked);
            perbaruiCount();
        }

        function setSemua(nilai) {
            document.querySelectorAll('#grup-menu input[name="permissions[]"]').forEach(cb => cb.checked = nilai);
            document.querySelectorAll('#grup-menu .centang-semua').forEach(cb => cb.checked = nilai);
            perbaruiCount();
        }

        function perbaruiCount() {
            const cbs = document.querySelectorAll('#grup-menu input[name="permissions[]"]');
            const centang = document.querySelectorAll('#grup-menu input[name="permissions[]"]:checked').length;
            const spn = document.getElementById('spn-count');
            spn.classList.remove('hidden');
            spn.innerText = centang + ' dari ' + cbs.length + ' menu terpilih';

            document.querySelectorAll('#grup-menu .grup-isi').forEach(isi => {
                const kbs = isi.querySelectorAll('input[name="permissions[]"]');
                const kc = isi.querySelectorAll('input[name="permissions[]"]:checked').length;
                const jun = isi.parentElement.querySelector('.grup-jumlah');
                if (jun) jun.innerText = kc + '/' + kbs.length;
                const cekSemua = isi.parentElement.querySelector('.centang-semua');
                cekSemua.checked = kbs.length > 0 && kc === kbs.length;
            });
        }

        document.addEventListener('change', function (e) {
            if (e.target.classList && e.target.classList.contains('perm-checkbox')) perbaruiCount();
        });

        function hapusSemuaFasilitas() {
            if (userAktifLocked) { alert('Akun Administrator terkunci.'); return; }
            if (!confirm('Hapus semua fasilitas menu user ini? Akun tetap aktif, semua menu menjadi tidak dapat diakses.')) return;
            document.getElementById('form-hapus-semua').submit();
        }

        // ---------- MODAL TAMBAH / EDIT USER ----------
        function bukaModalTambah() {
            document.getElementById('modal-user').classList.remove('hidden');
            document.getElementById('modal-judul').innerText = "Tambah User Baru";
            document.getElementById('form-user').action = @json(url('setup-user'));
            document.getElementById('form-method').value = "POST";
            document.getElementById('form-user').reset();
        }

        function bukaModalEdit(tr) {
            const dt = tr.dataset;
            document.getElementById('modal-user').classList.remove('hidden');
            document.getElementById('modal-judul').innerText = "Edit User";
            document.getElementById('form-user').action = @json(url('setup-user')) + '/' + dt.id;
            document.getElementById('form-method').value = "PUT";
            document.getElementById('input-name').value = dt.nama;
            document.getElementById('input-username').value = dt.username;
            document.getElementById('input-email').value = dt.email || '';
            document.getElementById('input-hp').value = dt.hp || '';
            document.getElementById('input-status').value = dt.status;
        }

        function tutupModal() {
            document.getElementById('modal-user').classList.add('hidden');
        }

        function resetSandi(tr) {
            const dt = tr.dataset;
            if (!confirm('Reset sandi ' + (dt.nama || 'user') + '? Sandi baru acak akan dibuat.')) return;
            const f = document.createElement('form');
            f.method = 'POST';
            f.action = @json(url('setup-user')) + '/' + dt.id + '/reset-password';
            const t = document.createElement('input'); t.type = 'hidden'; t.value = '{{ csrf_token() }}'; t.name = '_token';
            const m = document.createElement('input'); m.type = 'hidden'; m.value = 'PUT'; m.name = '_method';
            f.appendChild(t); f.appendChild(m);
            document.body.appendChild(f); f.submit();
        }

        function tutupModalHasilReset() {
            document.getElementById('modal-hasil-reset').classList.add('hidden');
        }

        function bukaModalHapus(tr) {
            const dt = tr.dataset;
            document.getElementById('modal-hapus').classList.remove('hidden');
            document.getElementById('hapus-nama-user').innerText = dt.nama;
            document.getElementById('form-hapus').action = @json(url('setup-user')) + '/' + dt.id;
        }

        function tutupModalHapus() {
            document.getElementById('modal-hapus').classList.add('hidden');
        }

        function salinSandi(username, sandi, btn) {
            var nama = document.getElementById('hasil-nama').innerText || '';
            var teks = 'Nama: ' + nama + '\nUsername: ' + username + '\nSandi: ' + sandi;
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(teks).then(function() { tandaiTersalin(btn); });
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
            btn.classList.remove('bg-amber-50', 'text-amber-500');
            btn.classList.add('bg-emerald-500', 'text-white');
            setTimeout(function() {
                btn.innerHTML = asli;
                btn.classList.add('bg-amber-50', 'text-amber-500');
                btn.classList.remove('bg-emerald-500', 'text-white');
            }, 1500);
        }

        @if(session()->has('hasil_reset'))
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('modal-hasil-reset').classList.remove('hidden');
        });
        @endif
    </script>
@endsection