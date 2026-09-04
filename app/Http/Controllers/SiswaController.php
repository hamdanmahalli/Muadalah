<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\Periode;
use App\Services\SiswaService;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\SiswaImport;

class SiswaController extends Controller
{
    public function __construct(
        protected SiswaService $siswaService
    ) {}

    public function index(Request $request)
    {
        $search = $request->input('search');
        $perPage = $request->input('per_page', 15);

        $siswas = Siswa::when($search, function ($q, $search) {
                return $q->where(function ($w) use ($search) {
                    $w->where('nis', 'ilike', "%{$search}%")
                      ->orWhere('nisn', 'ilike', "%{$search}%")
                      ->orWhere('nama_siswa', 'ilike', "%{$search}%");
                });
            })
            ->orderBy('nis', 'asc')
            ->paginate($perPage)
            ->withQueryString();

        $nisBaru = $this->siswaService->generasikanNIS();

        $kelas = Kelas::orderBy('nama_kelas', 'asc')->get();

        $tahunAjaran = Periode::tahunAjaranList();

        return view('siswa.index', compact('siswas', 'search', 'nisBaru', 'perPage', 'kelas', 'tahunAjaran'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nis' => 'required|string|unique:siswas,nis',
            'nama_siswa' => 'required|string|max:255',
        ]);

        $siswa = Siswa::create([
            'nis' => $request->nis,
            'nisn' => $request->nisn,
            'nama_siswa' => $request->nama_siswa,
            'jenis_kelamin' => $request->jenis_kelamin,
            'tempat_lahir' => $request->tempat_lahir,
            'tanggal_lahir' => $request->tanggal_lahir,
            'alamat' => $request->alamat,
            'nama_ayah' => $request->nama_ayah,
            'nama_ibu' => $request->nama_ibu,
            'pekerjaan_ortu' => $request->pekerjaan_ortu,
            'no_hp_ortu' => $request->no_hp_ortu,
            'tahun_masuk' => $request->tahun_masuk,
            'status' => 'Aktif',
        ]);

        // Jika dipilih saat tambah, langsung masukkan ke penempatan
        if ($request->kelas_id && $request->periode_id) {
            $this->siswaService->tempatkan($siswa->id, $request->kelas_id, $request->periode_id, [
                'status' => 'Aktif',
                'tanggal_masuk' => now(),
            ]);
        }

        return redirect()->back()->with('sukses', 'Data siswa ' . $siswa->nama_siswa . ' berhasil disimpan! (NIS: ' . $siswa->nis . ')');
    }

    public function show($id)
    {
        $siswa = Siswa::with(['angkatan.kelas', 'angkatan.periode', 'tagihans.jenisTagihan'])->findOrFail($id);
        $kelas = Kelas::orderBy('nama_kelas', 'asc')->get();
        $tahunAjaran = Periode::tahunAjaranList();
        return view('siswa.show', compact('siswa', 'kelas', 'tahunAjaran'));
    }

    public function lengkapi($id)
    {
        $siswa = Siswa::findOrFail($id);
        $kelas = Kelas::orderBy('nama_kelas', 'asc')->get();
        $tahunAjaran = Periode::tahunAjaranList();
        return view('siswa.lengkapi', compact('siswa', 'kelas', 'tahunAjaran'));
    }

    public function simpanLengkapi(Request $request, $id)
    {
        $siswa = Siswa::findOrFail($id);

        $request->validate([
            'nisn'          => 'nullable|string|max:20',
            'nama_siswa'    => 'required|string|max:255',
            'jenis_kelamin' => 'nullable|in:L,P',
            'tempat_lahir'  => 'nullable|string|max:255',
            'tanggal_lahir' => 'nullable|date',
            'alamat'        => 'nullable|string|max:255',
            'nama_ayah'     => 'nullable|string|max:255',
            'nama_ibu'      => 'nullable|string|max:255',
            'pekerjaan_ortu'=> 'nullable|string|max:255',
            'no_hp_ortu'    => 'nullable|string|max:30',
            'tahun_masuk'   => 'nullable|string|max:10',
            'status'        => 'nullable|in:Aktif,Nonaktif,Lulus,Keluar',
            'foto'          => 'nullable|image|mimes:jpeg,jpg,png,webp|max:2048',
            'kelas_id'      => 'nullable|integer|exists:kelas,id',
            'periode_id'    => 'nullable|integer|exists:periodes,id',
        ]);

        $siswa->update($request->only([
            'nisn', 'nama_siswa', 'jenis_kelamin', 'tempat_lahir', 'tanggal_lahir',
            'alamat', 'nama_ayah', 'nama_ibu', 'pekerjaan_ortu', 'no_hp_ortu',
            'tahun_masuk', 'status'
        ]));

        if ($request->hasFile('foto')) {
            // Simpan lewat Storage disk "public" (bukan move + ekstensi client) utk cegah RCE
            $path = $request->file('foto')->store('uploads/siswa', 'public');
            $siswa->update(['foto' => 'storage/' . $path]);
        }

        // Penempatan
        if ($request->kelas_id) {
            $this->siswaService->tempatkan($siswa->id, $request->kelas_id, $request->periode_id, [
                'status' => $request->status,
                'tanggal_masuk' => $request->tanggal_masuk,
            ]);
        }

        return redirect()->back()->with('sukses', 'Data siswa lengkap berhasil disimpan!');
    }

    public function update(Request $request, $id)
    {
        $siswa = Siswa::findOrFail($id);
        $request->validate([
            'nama_siswa' => 'required|string|max:255',
        ]);

        $siswa->update($request->only([
            'nis', 'nisn', 'nama_siswa', 'jenis_kelamin', 'tempat_lahir', 'tanggal_lahir',
            'alamat', 'nama_ayah', 'nama_ibu', 'pekerjaan_ortu', 'no_hp_ortu',
            'tahun_masuk', 'status'
        ]));

        return redirect()->back()->with('sukses', 'Data siswa berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $siswa = Siswa::findOrFail($id);
        $nama = $siswa->nama_siswa;
        $siswa->delete();
        return redirect()->route('master-siswa.index')->with('sukses', 'Data siswa ' . $nama . ' berhasil dihapus!');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv',
        ]);

        try {
            Excel::import(new SiswaImport($request->periode_id), $request->file('file'));
            return redirect()->back()->with('sukses', 'Data siswa berhasil di-import dari Excel!');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Import siswa gagal: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal import data siswa. Pastikan format file & kolom benar.');
        }
    }
}
