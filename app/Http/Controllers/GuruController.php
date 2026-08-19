<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Guru;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\GuruExport;
use App\Imports\GuruImport;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class GuruController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $perPage = $request->input('per_page', 10);

        $gurus = Guru::when($search, function ($query, $search) {
                        return $query->where(function($q) use ($search) {
                            $q->where('nama_guru', 'ilike', "%{$search}%")
                              ->orWhere('nig', 'ilike', "%{$search}%");
                        });
                    })
                    // PERBAIKAN: Diurutkan berdasarkan NIG dari terkecil ke terbesar
                    ->orderBy('nig', 'asc') 
                    ->paginate($perPage)
                    ->withQueryString();

        $lastGuru = Guru::orderBy('nig', 'desc')->first();
        if ($lastGuru && is_numeric($lastGuru->nig)) {
            $nigBaru = (string) ((int) $lastGuru->nig + 1);
        } else {
            $nigBaru = '1001';
        }

        return view('guru', compact('gurus', 'search', 'nigBaru', 'perPage'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nig' => 'required|string|unique:gurus,nig',
            'nama_guru' => 'required|string|max:255',
            'no_hp' => 'nullable|string',
            'gender' => 'nullable|string',
            'alamat' => 'nullable|string',
            'status' => 'required|string'
        ]);

        // 1. Simpan Data Guru ke tabel gurus
        $guru = Guru::create($request->all());

        // 2. Otomatis Buat Akun User Baru (Username = NIG, Password = 123456)
        $user = User::create([
            'lembaga'  => 'PONDOK',
            'username' => $guru->nig, // Username menggunakan KODE NIG
            'name'     => $guru->nama_guru,
            'email'    => $guru->nig . '@pesantren.com', // Email sintetis agar unik
            'hp'       => $guru->no_hp,
            'status'   => 'Aktif',
            'password' => Hash::make('123456'), // Password bawaan disamakan
        ]);

        // 3. Berikan Role "Dewan Guru" secara otomatis
        $user->assignRole('Dewan Guru');

        return redirect()->back()->with('sukses', 'Data Guru berhasil disimpan! Akun otomatis terbuat (Username: ' . $guru->nig . ' | Password: 123456)');
    }
    
    public function update(Request $request, $id)
    {
        $guru = Guru::findOrFail($id);
        
        $request->validate([
            'nama_guru' => 'required|string|max:255',
            'no_hp' => 'nullable|string',
            'gender' => 'nullable|string',
            'alamat' => 'nullable|string',
            'status' => 'required|string'
        ]);

        // Menyimpan pembaruan SEMUA isian form ke database
        $guru->update([
            'nama_guru' => $request->nama_guru,
            'no_hp' => $request->no_hp,
            'gender' => $request->gender,
            'alamat' => $request->alamat,
            'status' => $request->status
        ]);

        return redirect()->back()->with('sukses', 'Data Guru berhasil diperbarui!');
    }

    public function destroy($id)
    {
        Guru::destroy($id);
        return redirect()->back()->with('sukses', 'Data Guru berhasil dihapus!');
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