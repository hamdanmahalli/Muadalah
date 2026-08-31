<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
class UserController extends Controller
{
    public function index()
    {
        // Memuat user beserta relasi roles agar cepat & ringan
        $users = User::with('roles')->orderBy('id', 'asc')->get();
        $gurus = \App\Models\Guru::orderBy('nama_guru', 'asc')->get();
        $roles = Role::orderBy('name', 'asc')->get(); 

        return view('user', compact('users', 'gurus', 'roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'username' => 'required|string|unique:users,username',
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'hp'       => 'nullable|string',
            'roles'    => 'required|array|min:1', // Menerima Array dari Checkbox Multi-Role
        ]);

        $user = User::create([
            'lembaga'  => 'PONDOK',
            'username' => $request->username,
            'name'     => $request->name,
            'email'    => $request->email,
            'hp'       => $request->hp,
            'role'     => implode(', ', $request->roles), // Gabungan teks untuk cadangan
            'status'   => $request->status ?? 'Aktif',
            'password' => Hash::make('123456'), // Password bawaan: 123456
        ]);

        // SINKRONISASI MULTI-ROLE SPATIE
        $user->syncRoles($request->roles);

        return redirect()->back()->with('sukses', 'Pengguna baru berhasil ditambahkan dengan Multi-Role! Password bawaan: 123456');
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        $request->validate([
            'username' => 'required|string|unique:users,username,'.$id,
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email,'.$id,
            'hp'       => 'nullable|string',
            'roles'    => 'required|array|min:1', // Menerima Array dari Checkbox Multi-Role
            'status'   => 'required|string'
        ]);

        $user->update([
            'username' => $request->username,
            'name'     => $request->name,
            'email'    => $request->email,
            'hp'       => $request->hp,
            'role'     => implode(', ', $request->roles),
            'status'   => $request->status,
        ]);

        // UPDATE MULTI-ROLE SPATIE (Bisa Tambah / Cabut Role Secara Instan)
        $user->syncRoles($request->roles);

        return redirect()->back()->with('sukses', 'Data Pengguna & Hak Akses berhasil diperbarui!');
    }

    public function resetPassword($id)
    {
        $user = User::findOrFail($id);
        $sandi = $this->buatSandiAcak();
        $user->update([
            'password' => Hash::make($sandi)
        ]);

        // Simpan hasil sandi baru ke session (tampil sekali di layar, lalu hilang)
        session()->flash('hasil_reset', [
            'nama'     => $user->name,
            'username' => $user->username,
            'sandi'    => $sandi,
        ]);

        return redirect()->back()->with('sukses', 'Sandi untuk ' . $user->name . ' telah di-reset. Salin sandi baru dari layar dan sebarkan ke guru.');
    }

    // Menghasilkan sandi acak format "kata + angka" yang mudah diingat
    private function buatSandiAcak(): string
    {
        return ucfirst(Str::random(5)) . rand(10, 99);
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->syncRoles([]); 
        $user->delete();
        
        return redirect()->back()->with('sukses', 'Pengguna berhasil dihapus!');
    }
}