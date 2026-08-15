<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::orderBy('id', 'asc')->get();
        $gurus = \App\Models\Guru::orderBy('nama_guru', 'asc')->get();
        return view('user', compact('users', 'gurus'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'username' => 'required|string|unique:users,username',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'hp' => 'nullable|string',
            'role' => 'required|string',
        ]);

        User::create([
            'lembaga' => 'PONDOK',
            'username' => $request->username,
            'name' => $request->name,
            'email' => $request->email,
            'hp' => $request->hp,
            'role' => $request->role,
            'status' => $request->status ?? 'Aktif',
            'password' => Hash::make('123456'), // PASSWORD OTOMATIS: 123456
        ]);

        return redirect()->back()->with('sukses', 'Pengguna baru berhasil ditambahkan! Password bawaan adalah: 123456');
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        $request->validate([
            'username' => 'required|string|unique:users,username,'.$id,
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$id,
            'hp' => 'nullable|string',
            'role' => 'required|string',
            'status' => 'required|string'
        ]);

        $user->update([
            'username' => $request->username,
            'name' => $request->name,
            'email' => $request->email,
            'hp' => $request->hp,
            'role' => $request->role,
            'status' => $request->status,
        ]);

        return redirect()->back()->with('sukses', 'Data Pengguna berhasil diperbarui!');
    }

    // FITUR BARU: Fungsi untuk mereset password ke 123456
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
        User::destroy($id);
        return redirect()->back()->with('sukses', 'Pengguna berhasil dihapus!');
    }
}