<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Guru;
use App\Models\Jabatan;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\GuruExport;
use App\Imports\GuruImport;
use App\Services\GuruService;

class GuruController extends Controller
{
    public function __construct(
        protected GuruService $guruService
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
