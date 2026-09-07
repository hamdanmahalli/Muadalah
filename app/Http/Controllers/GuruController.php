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

    // ========================================================
    // KELENGKAPAN DATA PEMERINTAH & HONOR (per guru)
    // ========================================================

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

    public function simpanKelengkapan(Request $request, $id)
    {
        $guru = Guru::findOrFail($id);

        $validated = $request->validate([
            'jarak_km'              => 'nullable|numeric|min:0|max:9999.99',
            'nik'                   => 'nullable|string|max:16',
            'nik_kk'                => 'nullable|string|max:16',
            'agama'                 => 'nullable|string|max:50',
            'kewarganegaraan'       => 'nullable|string|max:50',
            'rt'                    => 'nullable|string|max:10',
            'rw'                    => 'nullable|string|max:10',
            'kelurahan'             => 'nullable|string|max:100',
            'kecamatan'             => 'nullable|string|max:100',
            'kabupaten_kota'        => 'nullable|string|max:100',
            'kode_pos'              => 'nullable|string|max:5',
            'nuptk'                 => 'nullable|string|max:30',
            'nrg'                   => 'nullable|string|max:30',
            'status_kepegawaian'    => 'nullable|string|max:50',
            'golongan_ruang'        => 'nullable|string|max:20',
            'program_studi'         => 'nullable|string|max:100',
            'perguruan_tinggi'      => 'nullable|string|max:150',
            'tahun_lulus'           => 'nullable|digits:4',
            'status_sertifikasi'    => 'nullable|boolean',
            'no_sertifikat_pendidik'=> 'nullable|string|max:50',
            'tahun_sertifikasi'     => 'nullable|digits:4',
            'tmt_kerja'             => 'nullable|date',
            'no_sk_pengangkatan'    => 'nullable|string|max:100',
            'tgl_sk_pengangkatan'   => 'nullable|date',
            'no_sk_pembagian_tugas' => 'nullable|string|max:100',
            'npwp'                  => 'nullable|string|max:30',
            'nama_bank'             => 'nullable|string|max:50',
            'no_rekening'           => 'nullable|string|max:30',
            'atas_nama_rekening'    => 'nullable|string|max:150',
            'bpjs_ketenagakerjaan'  => 'nullable|string|max:30',
            'bpjs_kesehatan'        => 'nullable|string|max:30',
            'status_menikah'        => 'nullable|string|max:20',
            'nama_pasangan'         => 'nullable|string|max:150',
            'jumlah_anak'           => 'nullable|integer|min:0|max:99',
        ]);

        // Normalisasi: string kosong → null (agar DB rapi)
        foreach ($validated as $key => $value) {
            if (is_string($value) && trim($value) === '') {
                $validated[$key] = null;
            }
        }

        $validated['status_sertifikasi'] = (bool) ($request->status_sertifikasi ?? false);
        $validated['kewarganegaraan']    = $validated['kewarganegaraan'] ?: 'WNI';
        $validated['jumlah_anak']        = $validated['jumlah_anak'] ?? 0;
        $validated['jarak_km']           = $validated['jarak_km'] ?? null;

        $guru->update($validated);

        return redirect()->back()->with('sukses', 'Data kelengkapan ' . $guru->nama_guru . ' berhasil disimpan!');
    }

    public function uploadDokumen(Request $request, $id)
    {
        $guru = Guru::findOrFail($id);

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
