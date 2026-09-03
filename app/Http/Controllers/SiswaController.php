<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\Periode;
use App\Models\AngkatanSiswa;

class SiswaController extends Controller
{
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

        $lastSiswa = Siswa::orderBy('nis', 'desc')->first();
        $nisBaru = ($lastSiswa && is_numeric($lastSiswa->nis))
            ? (string)((int)$lastSiswa->nis + 1)
            : '1001';

        $kelas = Kelas::orderBy('nama_kelas', 'asc')->get();

        return view('siswa.index', compact('siswas', 'search', 'nisBaru', 'perPage', 'kelas'));
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
            'agama' => $request->agama,
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
            AngkatanSiswa::create([
                'siswa_id' => $siswa->id,
                'periode_id' => $request->periode_id,
                'kelas_id' => $request->kelas_id,
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
        $periodes = Periode::orderBy('tahun_ajaran', 'desc')->get();
        return view('siswa.show', compact('siswa', 'kelas', 'periodes'));
    }

    public function lengkapi($id)
    {
        $siswa = Siswa::findOrFail($id);
        $kelas = Kelas::orderBy('nama_kelas', 'asc')->get();
        $periodes = Periode::orderBy('tahun_ajaran', 'desc')->get();
        return view('siswa.lengkapi', compact('siswa', 'kelas', 'periodes'));
    }

    public function simpanLengkapi(Request $request, $id)
    {
        $siswa = Siswa::findOrFail($id);
        $siswa->update($request->only([
            'nisn', 'nama_siswa', 'jenis_kelamin', 'tempat_lahir', 'tanggal_lahir',
            'agama', 'alamat', 'nama_ayah', 'nama_ibu', 'pekerjaan_ortu', 'no_hp_ortu',
            'tahun_masuk', 'status'
        ]));

        if ($request->hasFile('foto')) {
            $file = $request->file('foto');
            $nama = 'siswa-' . $siswa->id . '-' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/siswa'), $nama);
            $siswa->update(['foto' => 'uploads/siswa/' . $nama]);
        }

        // Penempatan
        if ($request->kelas_id) {
            AngkatanSiswa::updateOrCreate(
                [
                    'siswa_id' => $siswa->id,
                    'periode_id' => $request->periode_id,
                ],
                [
                    'kelas_id' => $request->kelas_id,
                    'status' => $request->status ?? 'Aktif',
                    'tanggal_masuk' => $request->tanggal_masuk,
                ]
            );
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
            'agama', 'alamat', 'nama_ayah', 'nama_ibu', 'pekerjaan_ortu', 'no_hp_ortu',
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
}
