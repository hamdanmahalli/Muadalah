@extends('layouts.app')

@section('title', 'Monitoring & Valdasi Kehadiran - SmartPesantren')

@section('content')
    <style>
        .mon-badge { font-size: 11px; font-weight: 700; padding: 4px 10px; border-radius: 9999px; }
        .mon-badge-opt { cursor: pointer; }
        .mon-badge-opt:checked { outline: 2px solid #00c0c7; outline-offset: 1px; }
    </style>

    <!-- HEADER -->
    <div class="bg-white p-5 md:p-6 rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100 mb-6 flex flex-col xl:flex-row justify-between items-start xl:items-center gap-6 relative overflow-hidden">
        <div class="absolute top-0 left-0 w-1.5 h-full bg-[#00c0c7] rounded-l-3xl"></div>
        <div>
            <h2 class="text-2xl font-black text-slate-800 tracking-tight flex items-center">
                <div class="w-10 h-10 rounded-xl bg-[#00c0c7]/15 flex items-center justify-center text-[#00c0c7] mr-3">
                    <i class="fas fa-user-check"></i>
                </div>
                Monitoring &amp; Valdasi Kehadiran
            </h2>
            <p class="text-xs text-slate-400 font-medium mt-1 ml-13">Tabel status kehadiran pada rentang tanggal — termasuk slot yang belum diisi.</p>
        </div>

        <!-- Kartu Statistik Status -->
        <div class="flex flex-wrap gap-2">
            @php $warna = [
                'Hadir'    => 'bg-emerald-100 text-emerald-700',
                'Izin'     => 'bg-amber-100 text-amber-700',
                'Sakit'    => 'bg-cyan-100 text-cyan-700',
                'Alpa'     => 'bg-red-100 text-red-700',
                'Menunggu' => 'bg-yellow-100 text-yellow-700',
            ]; @endphp
            @foreach($daftarStatus as $st)
                <div class="px-3 py-2 rounded-2xl {{ $warna[$st] ?? 'bg-gray-100 text-gray-600' }} border border-black/5 shadow-sm text-center min-w-[64px]">
                    <p class="text-lg font-black leading-tight">{{ $total[$st] ?? 0 }}</p>
                    <p class="text-[10px] font-bold uppercase tracking-wider">{{ $st }}</p>
                </div>
            @endforeach
        </div>
    </div>

    @if(session('sukses'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm font-semibold px-4 py-3 rounded-2xl mb-4">
            <i class="fas fa-check-circle mr-2"></i>{{ session('sukses') }}
        </div>
    @endif

    <!-- FORM FILTER -->
    <div class="bg-white p-5 rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100 mb-6">
        <form method="GET" action="/monitoring-kehadiran" class="grid grid-cols-2 md:grid-cols-5 gap-3">
            <div>
                <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1 block">Dari Tanggal</label>
                <input type="date" name="tgl_mulai" value="{{ $tglMulai }}" class="w-full border border-slate-200 rounded-xl p-2 text-sm bg-white outline-none focus:ring-2 focus:ring-[#00c0c7]">
            </div>
            <div>
                <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1 block">Sampai Tanggal</label>
                <input type="date" name="tgl_selesai" value="{{ $tglSelesai }}" class="w-full border border-slate-200 rounded-xl p-2 text-sm bg-white outline-none focus:ring-2 focus:ring-[#00c0c7]">
            </div>
            <div>
                <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1 block">Kelas</label>
                <select name="kelas" class="w-full border border-slate-200 rounded-xl p-2 text-sm bg-white outline-none focus:ring-2 focus:ring-[#00c0c7]">
                    <option value="">Semua Kelas</option>
                    @foreach($daftarKelas as $k)
                        <option value="{{ $k->id }}" {{ (string)$kelasId === (string)$k->id ? 'selected' : '' }}>{{ $k->nama_kelas }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1 block">Status</label>
                <select name="status" class="w-full border border-slate-200 rounded-xl p-2 text-sm bg-white outline-none focus:ring-2 focus:ring-[#00c0c7]">
                    <option value="">Semua Status</option>
                    @foreach($daftarStatus as $st)
                        <option value="{{ $st }}" {{ (string)$statusF === (string)$st ? 'selected' : '' }}>{{ $st }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1 block">Cari Guru</label>
                <div class="flex gap-2">
                    <input type="text" name="cari" value="{{ $cari }}" placeholder="Nama / NIG" class="w-full border border-slate-200 rounded-xl p-2 text-sm bg-white outline-none focus:ring-2 focus:ring-[#00c0c7]">
                    <button type="submit" class="px-4 rounded-xl bg-[#00c0c7] hover:bg-[#00a8b8] text-white text-sm font-bold shadow-sm transition"><i class="fas fa-filter"></i></button>
                </div>
            </div>
        </form>
        <div class="mt-3 text-xs text-slate-400 font-medium">
            Rentang <b class="text-slate-600">{{ \Carbon\Carbon::parse($tglMulai)->translatedFormat('d F Y') }} s/d {{ \Carbon\Carbon::parse($tglSelesai)->translatedFormat('d F Y') }}</b> —
            <b class="text-[#00c0c7]">{{ count($slots) }}</b> slot kehadiran dalam rentang.
            <span class="text-slate-400">(<i class="fas fa-info-circle"></i> Slot tanpa record dihitung <b class="text-yellow-600">Menunggu</b>, kosong = <b class="text-red-600">Alpa</b>)</span>
        </div>
    </div>

    <!-- TABEL DATAR KEHADIRAN -->
    <div class="bg-white rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100 overflow-hidden">
        @if(count($slots) === 0)
            <div class="text-center py-16 text-slate-400">
                <i class="fas fa-inbox text-5xl mb-4 opacity-40"></i>
                <p class="font-bold">Tidak ada slot kehadiran pada rentang &amp; filter ini.</p>
            </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100 text-[11px] font-extrabold text-slate-400 uppercase tracking-wider">
                        <th class="px-4 py-3">No</th>
                        <th class="px-4 py-3">Tanggal</th>
                        <th class="px-4 py-3">Jam Ke</th>
                        <th class="px-4 py-3">Kelas</th>
                        <th class="px-4 py-3">Mapel</th>
                        <th class="px-4 py-3">Guru</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Piket</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @php $badge = [
                        'Hadir'    => 'bg-emerald-100 text-emerald-700',
                        'Izin'     => 'bg-amber-100 text-amber-700',
                        'Sakit'    => 'bg-cyan-100 text-cyan-700',
                        'Alpa'     => 'bg-red-100 text-red-700',
                        'Menunggu' => 'bg-yellow-100 text-yellow-700',
                    ]; @endphp
                    @foreach($slots as $i => $s)
                    <tr class="border-b border-slate-50 hover:bg-slate-50/50 transition">
                        <td class="px-4 py-3 font-bold text-gray-400">{{ $i + 1 }}</td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <p class="font-bold text-slate-700">{{ \Carbon\Carbon::parse($s['tanggal'])->format('d/m/Y') }}</p>
                            <p class="text-[11px] text-slate-400 font-semibold">{{ $s['hari'] }}</p>
                        </td>
                        <td class="px-4 py-3 text-slate-600">Ke-{{ $s['jam_ke'] }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $s['kelas'] }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $s['mapel'] }}</td>
                        <td class="px-4 py-3 text-slate-700 font-semibold">{{ $s['guru'] }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="mon-badge {{ $badge[$s['status']] ?? 'bg-gray-100 text-gray-600' }}">{{ $s['status'] }}</span>
                        </td>
                        <td class="px-4 py-3 text-center text-slate-500 font-semibold">{{ $s['pengganti'] ?: '—' }}</td>
                        <td class="px-4 py-3 text-center">
                            <button onclick="bukaEdit({{ $i }})"
                                    class="px-3 py-1.5 rounded-xl bg-[#00c0c7]/10 text-[#00a8b8] hover:bg-[#00c0c7] hover:text-white text-xs font-bold transition">
                                <i class="fas fa-pen mr-1"></i>Edit
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

    <!-- MODAL EDIT SATU SLOT -->
    <div id="modal-edit" class="fixed inset-0 z-[95] hidden items-center justify-center bg-black/40 p-4" onclick="if(event.target===this)tutupEdit()">
        <div class="bg-white w-full max-w-md rounded-3xl shadow-2xl overflow-hidden" onclick="event.stopPropagation()">
            <div class="bg-slate-800 px-6 py-4 flex justify-between items-center">
                <div>
                    <h3 class="text-white font-black text-lg">Edit Status</h3>
                    <p id="edit-sub" class="text-white/70 text-xs font-semibold mt-0.5">—</p>
                </div>
                <button onclick="tutupEdit()" class="text-white/80 hover:text-white text-2xl leading-none">&times;</button>
            </div>
            <div class="p-6 space-y-4" id="edit-fields">
                <input type="hidden" id="edit-jadwal">
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Status</label>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach(['Hadir','Izin','Sakit','Alpa','Menunggu'] as $st)
                            <label class="flex items-center gap-2 border border-slate-200 rounded-xl px-3 py-2 cursor-pointer">
                                <input type="radio" name="st" value="{{ $st }}" class="accent-[#00c0c7]">
                                <span class="text-sm font-semibold text-slate-700">{{ $st }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Keterangan</label>
                    <textarea id="edit-ket" rows="2" class="w-full border border-slate-200 rounded-xl p-2 text-sm outline-none focus:ring-2 focus:ring-[#00c0c7]" placeholder="Mis. sakit, izin, cuti..."></textarea>
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Guru Pengganti / Piket (NIG)</label>
                    <input type="text" id="edit-pengganti" class="w-full border border-slate-200 rounded-xl p-2 text-sm outline-none focus:ring-2 focus:ring-[#00c0c7]" placeholder="Kosongkan jika tidak ada">
                </div>
                <div id="edit-msg" class="hidden bg-rose-50 border border-rose-200 text-rose-700 text-xs font-semibold px-3 py-2 rounded-xl"></div>
                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="tutupEdit()" class="flex-1 px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 text-sm font-bold hover:bg-slate-50 transition">Batal</button>
                    <button type="button" onclick="simpanStatus()" class="flex-1 px-4 py-2.5 rounded-xl bg-[#00c0c7] hover:bg-[#00a8b8] text-white text-sm font-bold shadow-sm transition">Simpan</button>
                </div>
            </div>
        </div>
    </div>

    <script>
    const SLOT_DATA = @json(array_values($slots));

    let EDIT = { jadwal: null, tanggal: null };

    function ambilStatus() {
        const r = document.querySelector('input[name="st"]:checked');
        return r ? r.value : '';
    }

    function bukaEdit(i) {
        const s = SLOT_DATA[i];
        if (!s) return;
        EDIT.jadwal = s.jadwal_id; EDIT.tanggal = s.tanggal;
        document.getElementById('edit-sub').textContent = s.tanggal + ' • Kelas ' + s.kelas + ' • Ke-' + s.jam_ke + (s.guru ? ' • ' + s.guru : '');
        document.querySelectorAll('input[name="st"]').forEach(r => r.checked = (r.value === s.status));
        document.getElementById('edit-ket').value = s.keterangan || '';
        document.getElementById('edit-pengganti').value = s.pengganti || '';
        const msg = document.getElementById('edit-msg'); msg.classList.add('hidden'); msg.textContent = '';
        const m = document.getElementById('modal-edit');
        m.classList.remove('hidden'); m.classList.add('flex');
    }

    function tutupEdit() {
        document.getElementById('modal-edit').classList.add('hidden');
        document.getElementById('modal-edit').classList.remove('flex');
    }

    function simpanStatus() {
        const st = ambilStatus();
        if (!st) { const msg = document.getElementById('edit-msg'); msg.classList.remove('hidden'); msg.textContent = 'Pilih status terlebih dahulu.'; return; }
        const fd = new FormData();
        fd.append('jadwal_id', EDIT.jadwal);
        fd.append('tanggal', EDIT.tanggal);
        fd.append('status', st);
        fd.append('keterangan', document.getElementById('edit-ket').value);
        fd.append('nig_pengganti', document.getElementById('edit-pengganti').value);

        fetch('/monitoring-kehadiran/simpan', { method: 'POST', headers: { 'X-CSRF-TOKEN': @json(csrf_token()) }, body: fd })
            .then(r => r.json()).then(res => {
                if (res.status === 'success') {
                    tutupEdit();
                    location.reload();
                } else {
                    const msg = document.getElementById('edit-msg'); msg.classList.remove('hidden'); msg.textContent = res.pesan || 'Gagal menyimpan.';
                }
            }).catch(() => { const msg = document.getElementById('edit-msg'); msg.classList.remove('hidden'); msg.textContent = 'Gagal menghubungi server.'; });
    }
    </script>
@endsection
