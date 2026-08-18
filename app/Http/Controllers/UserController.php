<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
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
        $user->update([
            'password' => Hash::make('123456')
        ]);

        return redirect()->back()->with('sukses', 'Password untuk ' . $user->name . ' telah di-reset menjadi: 123456');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->syncRoles([]); 
        $user->delete();
        
        return redirect()->back()->with('sukses', 'Pengguna berhasil dihapus!');
    }
}