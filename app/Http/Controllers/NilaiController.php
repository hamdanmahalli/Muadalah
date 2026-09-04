<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Nilai;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\Periode;
use App\Models\Pelajaran;
use App\Models\AngkatanSiswa;
use App\Models\PlotJadwal;
use App\Models\NilaiKolomConfig;
use App\Services\NilaiService;
use App\Services\AuthenticatedGuruService;

class NilaiController extends Controller
{
    public function __construct(
        protected NilaiService $nilaiService,
        protected AuthenticatedGuruService $guruService
    ) {}

    /**
     * Menentukan mode penginput berdasarkan peran user saat ini.
     * - 'guru'      : hanya bisa menginput Nilai Harian (terbatas pada ampuannya)
     * - 'panitia'   : hanya bisa menginput Skor UTS/UAS
     * - 'admin'     : bisa menginput semua (Administrator / Tata Usaha)
     */
    /**
     * Role input yang dimiliki user saat ini.
     * Mengembalikan array berisi subset dari ['guru', 'panitia'] ; kosong berarti admin/TU.
     */
    protected function roleInputOptions(): array
    {
        $user = auth()->user();
        if (!$user) {
            return [];
        }

        $roles = $user->getRoleNames();
        $options = [];
        if ($roles->contains('Dewan Guru')) {
            $options[] = 'guru';
        }
        if ($roles->contains('Kepanitiaan')) {
            $options[] = 'panitia';
        }
        return $options;
    }

    protected function modePenginput(?string $force = null): string
    {
        $options = $this->roleInputOptions();

        // User dengan kedua role (guru + panitia) boleh memilih via query/form
        if (in_array($force, $options, true)) {
            return $force;
        }

        if (in_array('guru', $options, true)) {
            return 'guru';
        }
        if (in_array('panitia', $options, true)) {
            return 'panitia';
        }
        return 'admin';
    }

    /**
     * Hanya Administrator/Pimpinan yang boleh mengatur kolom yang ditampilkan.
     */
    protected function bolehKontrolKolom(): bool
    {
        $user = auth()->user();
        if (!$user) {
            return false;
        }
        return $user->getRoleNames()->contains('Administrator')
            || $user->getRoleNames()->contains('Pimpinan');
    }

    public function index(Request $request)
    {
        $roleOptions = $this->roleInputOptions();
        $bolehPilihMode = count($roleOptions) > 1;
        $force = $request->query('mode');
        $mode = $this->modePenginput($force);
        $bolehKontrol = $this->bolehKontrolKolom();
        $kolomConfig = NilaiKolomConfig::config();

        $periodes = Periode::orderBy('tahun_ajaran', 'desc')->get();
        $kelas = Kelas::orderBy('nama_kelas', 'asc')->get();
        $pelajarans = Pelajaran::orderBy('nama_pelajaran', 'asc')->get();
        $aktif = Periode::where('is_active', true)->first();

        $periodeId = $request->periode_id ?? ($aktif ? $aktif->id : null);
        $kelasId = $request->kelas_id;
        $pelajaranId = $request->pelajaran_id;

        // Guru hanya boleh memilih kelas & pelajaran yang ia ampu (PlotJadwal)
        $guruAmpu = collect();
        if ($mode === 'guru') {
            $guru = $this->guruService->fromAuthUser();
            if ($guru) {
                $guruAmpu = PlotJadwal::with(['kelas', 'pelajaran'])
                    ->where('guru_id', $guru->id)
                    ->get();

                $kelas = $guruAmpu->pluck('kelas')->filter()->unique('id')->values()->sortBy('nama_kelas');
                $pelajarans = $guruAmpu->pluck('pelajaran')->filter()->unique('id')->values()->sortBy('nama_pelajaran');

                // Validasi pilihan agar tetap dalam jangkauan ampuannya
                if ($kelasId && !$guruAmpu->pluck('kelas_id')->contains((int) $kelasId)) {
                    $kelasId = null;
                }
                if ($pelajaranId && !$guruAmpu->pluck('pelajaran_id')->contains((int) $pelajaranId)) {
                    $pelajaranId = null;
                }
            }
        }

        // Daftar siswa di kelas + periode
        $siswas = collect();
        $nilaiMap = collect();
        $absenMap = collect();
        if ($kelasId) {
            $angkatan = AngkatanSiswa::where('kelas_id', $kelasId)
                ->when($periodeId, fn($q) => $q->where('periode_id', $periodeId))
                ->get();
            $siswaIds = $angkatan->pluck('siswa_id');
            $absenMap = $angkatan->pluck('nomor_absen', 'siswa_id');
            $siswas = Siswa::whereIn('id', $siswaIds)->orderBy('nama_siswa', 'asc')->get()
                ->sortBy(fn($s) => (int) $absenMap[$s->id] ?? PHP_INT_MAX)
                ->values();

            if ($pelajaranId) {
                $nilaiMap = Nilai::where('kelas_id', $kelasId)
                    ->where('pelajaran_id', $pelajaranId)
                    ->when($periodeId, fn($q) => $q->where('periode_id', $periodeId))
                    ->get()
                    ->keyBy('siswa_id');
            }
        }

        return view('nilai.index', compact(
            'mode', 'periodes', 'kelas', 'pelajarans', 'periodeId',
            'kelasId', 'pelajaranId', 'siswas', 'nilaiMap', 'absenMap',
            'kolomConfig', 'bolehKontrol', 'bolehPilihMode'
        ));
    }

    // Simpan satu baris nilai
    public function store(Request $request)
    {
        $request->validate([
            'siswa_id' => 'required|exists:siswas,id',
            'pelajaran_id' => 'required|exists:pelajarans,id',
            'nilai_harian_uts' => 'nullable|numeric|between:0,100',
            'nilai_harian_uas' => 'nullable|numeric|between:0,100',
            'skor_uts' => 'nullable|numeric|between:0,100',
            'skor_uas' => 'nullable|numeric|between:0,100',
            'catatan' => 'nullable|string|max:255',
        ]);

        $mode = $this->modePenginput();
        $fields = [
            'kelas_id' => $request->kelas_id,
            'guru_id' => $request->guru_id,
            'catatan' => $request->catatan,
        ];

        if ($mode !== 'panitia') {
            $fields['nilai_harian_uts'] = $request->nilai_harian_uts;
            $fields['nilai_harian_uas'] = $request->nilai_harian_uas;
        }
        if ($mode !== 'guru') {
            $fields['skor_uts'] = $request->skor_uts;
            $fields['skor_uas'] = $request->skor_uas;
        }

        $this->nilaiService->simpan(
            (int) $request->siswa_id,
            (int) $request->pelajaran_id,
            $request->periode_id,
            $fields
        );

        return redirect()->back()->with('sukses', 'Nilai berhasil disimpan.');
    }

    // Simpan massal (grid)
    public function simpanMassal(Request $request)
    {
        $request->validate([
            'pelajaran_id' => 'required|exists:pelajarans,id',
            'kelas_id' => 'required|exists:kelas,id',
            'siswa' => 'nullable|array',
            'siswa.*.harian_uts' => 'nullable|numeric|between:0,100',
            'siswa.*.harian_uas' => 'nullable|numeric|between:0,100',
            'siswa.*.skor_uts' => 'nullable|numeric|between:0,100',
            'siswa.*.skor_uas' => 'nullable|numeric|between:0,100',
        ]);

        $mode = $this->modePenginput($request->input('mode'));

        // Normalisasi: hanya kirim kolom sesuai peran
        $skor = collect($request->siswa ?? [])->map(function ($row) use ($mode) {
            return [
                'harian_uts' => $mode !== 'panitia' ? ($row['harian_uts'] ?? null) : null,
                'harian_uas' => $mode !== 'panitia' ? ($row['harian_uas'] ?? null) : null,
                'skor_uts' => $mode !== 'guru' ? ($row['skor_uts'] ?? null) : null,
                'skor_uas' => $mode !== 'guru' ? ($row['skor_uas'] ?? null) : null,
            ];
        })->all();

        $count = $this->nilaiService->simpanMassal(
            (int) $request->kelas_id,
            (int) $request->pelajaran_id,
            $request->periode_id,
            $this->guruService->fromAuthUser()?->id,
            $skor
        );

        return redirect()->back()->with('sukses', "Nilai {$count} siswa berhasil disimpan.");
    }

    /**
     * Simpan konfigurasi kolom yang ditampilkan (khusus Administrator/Pimpinan).
     */
    public function updateKolom(Request $request)
    {
        if (!$this->bolehKontrolKolom()) {
            abort(403, 'Anda tidak berwenang mengatur tampilan kolom nilai.');
        }

        $request->validate([
            'harian_uts' => 'nullable|boolean',
            'skor_uts' => 'nullable|boolean',
            'uts_akhir' => 'nullable|boolean',
            'harian_uas' => 'nullable|boolean',
            'skor_uas' => 'nullable|boolean',
            'uas_akhir' => 'nullable|boolean',
            'nilai_akhir' => 'nullable|boolean',
            'predikat' => 'nullable|boolean',
        ]);

        $config = NilaiKolomConfig::config();
        $config->update([
            'harian_uts' => $request->has('harian_uts'),
            'skor_uts' => $request->has('skor_uts'),
            'uts_akhir' => $request->has('uts_akhir'),
            'harian_uas' => $request->has('harian_uas'),
            'skor_uas' => $request->has('skor_uas'),
            'uas_akhir' => $request->has('uas_akhir'),
            'nilai_akhir' => $request->has('nilai_akhir'),
            'predikat' => $request->has('predikat'),
        ]);

        return redirect()->back()->with('sukses', 'Pengaturan kolom nilai berhasil disimpan.');
    }

    public function downloadTemplate()
    {
        return redirect()->back()->with('sukses', 'Template nilai diunduh.');
    }
}
