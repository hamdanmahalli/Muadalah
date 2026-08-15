@extends('layouts.app')

@section('title', 'Master Jadwal Harian')

@section('content')
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800"><i class="fas fa-calendar-alt mr-2 text-indigo-600"></i> Master Jadwal Harian</h2>
    </div>

    <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-200 mb-6">
        <form action="/master-jadwal-harian" method="GET">
            <div class="w-full md:w-1/2 lg:w-1/3">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Pilih Kelas:</label>
                <select name="kelas_id" class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-indigo-500 outline-none" onchange="this.form.submit()">
                    <option value="">-- Tampilkan Jadwal Kelas --</option>
                    @foreach($kelas as $k)
                        <option value="{{ $k->id }}" {{ $kelas_id == $k->id ? 'selected' : '' }}>{{ $k->nama_kelas }}</option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>

    @if($kelas_id)
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($hari_list as $hari)
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden flex flex-col">
            <div class="bg-indigo-50 px-4 py-3 border-b border-gray-200 flex justify-between items-center">
                <h3 class="font-bold text-indigo-800 text-lg">{{ $hari }}</h3>
                <button onclick="bukaModalJadwal('{{ $hari }}')" class="bg-indigo-600 text-white p-1.5 rounded hover:bg-indigo-700 transition" title="Tambah Jam">
                    <i class="fas fa-plus text-sm"></i>
                </button>
            </div>
            
            <div class="p-0 flex-1">
                @php 
                    $ada_jadwal = false; 
                @endphp
                
                <table class="w-full text-sm">
                    <tbody class="divide-y divide-gray-100">
                        @for($jam = 1; $jam <= $max_jam; $jam++)
                            @if(isset($jadwal_matriks[$hari][$jam]))
                                @php 
                                    $jadwal = $jadwal_matriks[$hari][$jam]; 
                                    $ada_jadwal = true;
                                @endphp
                                <tr class="hover:bg-gray-50 transition group">
                                    <td class="px-4 py-3 w-16 text-center border-r border-gray-100">
                                        <span class="block text-xs text-gray-400 font-medium">Jam</span>
                                        <span class="block font-bold text-gray-800 text-base">{{ $jam }}</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="font-bold text-indigo-600">{{ $jadwal->pelajaran->nama_pelajaran }}</div>
                                        <div class="text-xs text-gray-500 mt-0.5"><i class="fas fa-user-tie mr-1"></i>{{ $jadwal->guru->nama_guru ?? 'Belum ada guru' }}</div>
                                    </td>
                                    <td class="px-3 py-3 text-right w-20">
                                        <div class="flex justify-end space-x-1 opacity-100 md:opacity-0 md:group-hover:opacity-100 transition duration-200">
                                            <button onclick="bukaModalJadwal('{{ $hari }}', {{ $jam }}, {{ $jadwal->pelajaran_id }}, {{ $jadwal->guru_id ?? 'null' }})" class="w-7 h-7 rounded bg-orange-50 text-orange-400 hover:bg-orange-500 hover:text-white flex items-center justify-center">
                                                <i class="fas fa-edit text-xs"></i>
                                            </button>
                                            <form action="/master-jadwal-harian/{{ $jadwal->id }}" method="POST" class="inline">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="w-7 h-7 rounded bg-red-50 text-red-500 hover:bg-red-500 hover:text-white flex items-center justify-center" onclick="return confirm('Hapus jam pelajaran ini?')">
                                                    <i class="fas fa-trash text-xs"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        @endfor
                        
                        @if(!$ada_jadwal)
                        <tr>
                            <td colspan="3" class="px-4 py-8 text-center text-gray-400">
                                <i class="fas fa-mug-hot text-2xl mb-2 block opacity-30"></i>
                                Belum ada jadwal di hari ini.
                            </td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    <div id="modal-jadwal" class="hidden fixed inset-0 bg-gray-900 bg-opacity-60 overflow-y-auto h-full w-full z-50 flex items-center justify-center backdrop-blur-sm">
        <div class="relative mx-auto p-6 border w-full max-w-sm shadow-2xl rounded-xl bg-white">
            <div class="flex justify-between items-center mb-5 border-b pb-3">
                <h3 class="text-lg font-bold text-gray-800" id="modal-judul">Atur Jam Pelajaran</h3>
                <button onclick="tutupModal()" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times text-xl"></i></button>
            </div>
            
            <form method="POST" action="/master-jadwal-harian">
                @csrf
                <input type="hidden" name="kelas_id" value="{{ $kelas_id }}">
                <input type="hidden" name="hari" id="input-hari">
                
                <div class="mb-4 bg-indigo-50 p-3 rounded-lg text-center font-bold text-indigo-800">
                    Hari: <span id="label-hari"></span>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Pilih Jam Ke-</label>
                    <select name="jam_ke" id="input-jam" required class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-indigo-500">
                        <option value="">-- Pilih Jam --</option>
                        @for($i = 1; $i <= $max_jam; $i++)
                            <option value="{{ $i }}">Jam ke-{{ $i }}</option>
                        @endfor
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Mata Pelajaran</label>
                    <select name="pelajaran_id" id="input-pelajaran" required class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-indigo-500">
                        <option value="">-- Pilih Pelajaran --</option>
                        @foreach($pelajarans ?? [] as $pelajaran)
                            <option value="{{ $pelajaran->id }}">{{ $pelajaran->nama_pelajaran }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Guru Pengajar</label>
                    <select name="guru_id" id="input-guru" class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-indigo-500">
                        <option value="">-- Kosongkan / Belum ada --</option>
                        @foreach($gurus ?? [] as $guru)
                            <option value="{{ $guru->id }}">{{ $guru->nama_guru }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex justify-end space-x-2 border-t pt-4">
                    <button type="button" onclick="tutupModal()" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg font-semibold">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg font-semibold hover:bg-indigo-700">Simpan Jadwal</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function bukaModalJadwal(hari, jam = '', pelajaran_id = '', guru_id = '') {
            document.getElementById('modal-jadwal').classList.remove('hidden');
            
            document.getElementById('label-hari').innerText = hari;
            document.getElementById('input-hari').value = hari;
            
            // Jika edit, atur judul dan kunci jam, jika tambah, reset jam
            document.getElementById('modal-judul').innerText = jam ? "Edit Jam Pelajaran" : "Tambah Jam Pelajaran";
            document.getElementById('input-jam').value = jam;
            
            document.getElementById('input-pelajaran').value = pelajaran_id;
            document.getElementById('input-guru').value = guru_id;
        }

        function tutupModal() {
            document.getElementById('modal-jadwal').classList.add('hidden');
        }
    </script>

    @if(session('error_popup'))
    <div id="modal-peringatan" class="fixed inset-0 bg-gray-900 bg-opacity-70 overflow-y-auto h-full w-full z-50 flex items-center justify-center backdrop-blur-sm animate-fade-in">
        <div class="relative mx-auto p-6 border w-full max-w-md shadow-2xl rounded-2xl bg-white text-center">
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100 mb-4 shadow-inner">
                <i class="fas fa-exclamation-triangle text-2xl text-red-600 animate-bounce"></i>
            </div>
            <h3 class="text-xl font-extrabold text-gray-800 mb-2">Perhatian Sistem</h3>
            <div class="text-sm text-gray-700 mb-6 bg-red-50 p-4 rounded-xl border border-red-100 text-left leading-relaxed">
                {!! session('error_popup') !!}
            </div>
            <button onclick="tutupModalPeringatan()" class="w-full py-3 bg-red-600 hover:bg-red-700 text-white font-bold rounded-xl shadow-md transition duration-200">
                <i class="fas fa-check mr-2"></i> Mengerti, Kembali Atur Jadwal
            </button>
        </div>
    </div>
    <script>
        function tutupModalPeringatan() {
            document.getElementById('modal-peringatan').style.display = 'none';
        }
    </script>
    @endif

@endsection