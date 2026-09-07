<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Guru;
use App\Models\GuruDokumen;
use App\Models\Jabatan;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\GuruExport;
use App\Imports\GuruImport;
use App\Services\GuruService;
use App\Services\GuruKelengkapanService;
use App\Services\AuthenticatedGuruService;

class GuruController extends Controller
{
    public function __construct(
        protected GuruService $guruService,
        protected GuruKelengkapanService $kelengkapanService,
        protected AuthenticatedGuruService $guruContext,
    ) {}

    public function index(Request $request)
    {
        $search = $request->input('search');
        $perPage = $request->input('per_page', 10);

        $gurus = Guru::with('jabatans')->when($search, function ($query, $search) {
                        return $query->where(function($q) use ($search) {
                            $q->where('nama_guru', 'ilike', "%{$search}%")
                              ->orWhere('nig', 'ilike', "%{$search}%")
                              ->orWhere('nip', 'ilike', "%{$search}%");
                        });
                    })
                    // PERBAIKAN: Diurutkan berdasarkan NIG dari terkecil ke terbesar
                    ->orderBy('nig', 'asc')
                    ->paginate($perPage)
                    ->withQueryString();

        $nigBaru = $this->guruService->generasikanNIG();

        $jabatans = Jabatan::where('status', 'Aktif')->orderBy('nama_jabatan', 'asc')->get();

        return view('guru', compact('gurus', 'search', 'nigBaru', 'perPage', 'jabatans'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nig' => 'required|string|unique:gurus,nig',
            'nama_guru' => 'required|string|max:255',
            'nip' => 'nullable|string|max:50',
            'no_hp' => 'nullable|string',
            'gender' => 'nullable|string',
            'alamat' => 'nullable|string',
            'status' => 'required|string',
            'jabatan_ids' => 'nullable|array',
            'jabatan_ids.*' => 'exists:jabatans,id',
        ]);

        // 1. Simpan Data Pengurus ke tabel gurus
        $guru = Guru::create($request->only([
            'nig', 'nama_guru', 'nip', 'no_hp', 'gender', 'alamat', 'status'
        ]));

        // 2. Simpan relasi jabatan (many-to-many)
        $guru->jabatans()->sync($request->jabatan_ids ?? []);

        // 3. Buat akun login HANYA jika pengurus adalah guru (memiliki jabatan "Guru")
        $hasil = $this->guruService->buatAkunGuruOtomatis($guru, $request->jabatan_ids);

        return redirect()->back()->with('sukses', $hasil['pesan']);
    }

    public function update(Request $request, $id)
    {
        $guru = Guru::findOrFail($id);

        $request->validate([
            'nama_guru' => 'required|string|max:255',
            'nip' => 'nullable|string|max:50',
            'no_hp' => 'nullable|string',
            'gender' => 'nullable|string',
            'alamat' => 'nullable|string',
            'status' => 'required|string',
            'jabatan_ids' => 'nullable|array',
            'jabatan_ids.*' => 'exists:jabatans,id',
        ]);

        // Menyimpan pembaruan SEMUA isian form ke database
        $guru->update([
            'nama_guru' => $request->nama_guru,
            'nip' => $request->nip,
            'no_hp' => $request->no_hp,
            'gender' => $request->gender,
            'alamat' => $request->alamat,
            'status' => $request->status
        ]);

        // Perbarui relasi jabatan (many-to-many)
        $guru->jabatans()->sync($request->jabatan_ids ?? []);

        return redirect()->back()->with('sukses', 'Data pengurus berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $guru = Guru::findOrFail($id);
        $guru->jabatans()->detach();
        $guru->delete();
        return redirect()->back()->with('sukses', 'Data pengurus berhasil dihapus!');
    }

    // ========================================================
    // KELENGKAPAN DATA GURU (data dasar + kelengkapan + dokumen)
    // diakses Admin/TU dari Master Guru, dan guru dari menu Profil
    // ========================================================

    private function bolehKelola(Guru $guru): bool
    {
        $user = auth()->user();
        if ($user->can('akses_master_guru')) {
            return true;
        }
        if ($user->hasAnyRole(['Administrator', 'Pimpinan'])) {
            return true;
        }

        $saya = $this->guruContext->fromUser($user);
        return $saya && $saya->id === $guru->id && (bool) $guru->boleh_edit_profil;
    }

    private function bolehTampil(Guru $guru): bool
    {
        $user = auth()->user();
        if ($user->can('akses_master_guru') || $user->hasAnyRole(['Administrator', 'Pimpinan'])) {
            return true;
        }

        $saya = $this->guruContext->fromUser($user);
        return $saya && $saya->id === $guru->id;
    }

    // Halaman gabungan: data dasar master guru + kelengkapan + dokumen
    public function kelengkapan($id)
    {
        $guru = Guru::with(['jabatans', 'dokumens'])->findOrFail($id);

        if (!$this->bolehTampil($guru)) {
            abort(403, 'Anda tidak berhak membuka data kelengkapan guru ini.');
        }

        $user = auth()->user();

        $editMode    = (bool) $guru->boleh_edit_profil;
        $isAdmin     = $user->can('akses_master_guru');
        $bolehToggle = $user->hasAnyRole(['Administrator', 'Pimpinan']);
        $editable    = $this->bolehKelola($guru);
        $jabatans    = Jabatan::orderBy('nama_jabatan', 'asc')->get();

        return view('admin.guru-kelengkapan', compact('guru', 'editMode', 'isAdmin', 'bolehToggle', 'editable', 'jabatans'));
    }

    // Tombol "Aktifkan Edit" / "Nonaktifkan Edit" (khusus Administrator/Pimpinan)
    public function toggleEditKelengkapan($id)
    {
        $user = auth()->user();
        abort_unless($user->can('akses_master_guru') || $user->hasAnyRole(['Administrator', 'Pimpinan']), 403);

        $guru = Guru::findOrFail($id);
        $guru->update(['boleh_edit_profil' => !$guru->boleh_edit_profil]);

        $status = $guru->boleh_edit_profil ? 'Pengeditan DIAKTIFKAN untuk ' . $guru->nama_guru . '.' : 'Pengeditan DINONAKTIFKAN untuk ' . $guru->nama_guru . '.';
        return redirect()->back()->with('sukses', $status);
    }

    public function detail($id)
    {
        $guru = Guru::with('dokumens')->findOrFail($id);

        $data = $guru->toArray();
        foreach ($data['dokumens'] as &$dok) {
            $dok['url'] = asset('uploads/' . $dok['file_path']);
        }
        unset($dok);

        return response()->json($data);
    }

    private const BIDANG_DASAR_ADMIN = [
        'nama_guru', 'nip', 'no_hp', 'gender', 'alamat', 'status',
        'tempat_lahir', 'tanggal_lahir', 'pendidikan_terakhir',
    ];

    private const BIDANG_KELENGKAPAN = [
        'jarak_km', 'nik', 'nik_kk', 'agama', 'kewarganegaraan', 'rt', 'rw',
        'kelurahan', 'kecamatan', 'kabupaten_kota', 'kode_pos', 'nuptk', 'nrg',
        'status_kepegawaian', 'golongan_ruang', 'program_studi', 'perguruan_tinggi',
        'tahun_lulus', 'status_sertifikasi', 'no_sertifikat_pendidik', 'tahun_sertifikasi',
        'tmt_kerja', 'no_sk_pengangkatan', 'tgl_sk_pengangkatan', 'no_sk_pembagian_tugas',
        'npwp', 'nama_bank', 'no_rekening', 'atas_nama_rekening',
        'bpjs_ketenagakerjaan', 'bpjs_kesehatan', 'status_menikah', 'nama_pasangan',
        'jumlah_anak',
    ];

    public function simpanKelengkapan(Request $request, $id)
    {
        $guru = Guru::findOrFail($id);

        if (!$this->bolehKelola($guru)) {
            abort(403, 'Anda tidak berhak menyimpan data kelengkapan guru ini.');
        }

        $isAdmin = auth()->user()->can('akses_master_guru');

        $this->kelengkapanService->simpan(
            $guru,
            $request->all(),
            array_merge(self::BIDANG_DASAR_ADMIN, self::BIDANG_KELENGKAPAN),
            syncJabatan: $isAdmin
        );

        return redirect()->back()->with('sukses', 'Data kelengkapan ' . $guru->nama_guru . ' berhasil disimpan!');
    }

    public function uploadDokumen(Request $request, $id)
    {
        $guru = Guru::findOrFail($id);
        return $this->simpanUploadDokumen($request, $guru);
    }

    // Unggah dokumen oleh guru dari halaman Profil (data milik sendiri)
    public function uploadDokumenProfil(Request $request)
    {
        $guru = $this->guruContext->fromUser(auth()->user());
        abort_unless($guru, 404);
        return $this->simpanUploadDokumen($request, $guru);
    }

    private function simpanUploadDokumen(Request $request, Guru $guru)
    {
        if (!$this->bolehKelola($guru)) {
            abort(403, 'Anda tidak berhak mengunggah dokumen guru ini.');
        }

        $request->validate([
            'jenis'      => 'required|string|max:50',
            'file'       => 'required|file|mimes:pdf,jpg,jpeg,png|max:4096',
            'keterangan' => 'nullable|string|max:255',
        ]);

        $path = $request->file('file')->store('dokumen-guru/' . $guru->id, 'public_uploads');

        GuruDokumen::create([
            'guru_id'    => $guru->id,
            'jenis'      => $request->jenis,
            'nama_asli'  => $request->file('file')->getClientOriginalName(),
            'file_path'  => $path,
            'keterangan' => $request->keterangan,
        ]);

        return redirect()->back()->with('sukses', 'Dokumen ' . $request->jenis . ' untuk ' . $guru->nama_guru . ' berhasil diunggah!');
    }

    public function hapusDokumen($id)
    {
        $dok = GuruDokumen::findOrFail($id);

        if (!$this->bolehKelola($dok->guru)) {
            abort(403, 'Anda tidak berhak menghapus dokumen ini.');
        }

        if (Storage::disk('public_uploads')->exists($dok->file_path)) {
            Storage::disk('public_uploads')->delete($dok->file_path);
        }

        $dok->delete();

        return redirect()->back()->with('sukses', 'Dokumen berhasil dihapus!');
    }

    // FITUR EXPORT EXCEL
    public function export()
    {
        return Excel::download(new GuruExport, 'data-guru-pesantren.xlsx');
    }

    // FITUR IMPORT EXCEL
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        Excel::import(new GuruImport, $request->file('file'));

        return redirect()->back()->with('sukses', 'Data Guru berhasil di-import dari Excel!');
    }
}
