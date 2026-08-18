<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionController extends Controller
{
    public function index()
    {
        // Ambil SEMUA role tanpa terkecuali
        $roles = Role::orderBy('id', 'asc')->get();
        $permissions = Permission::orderBy('id', 'asc')->get();

        return view('role-permission', compact('roles', 'permissions'));
    }

    public function update(Request $request)
    {
        // Ambil SEMUA role untuk diupdate hak aksesnya
        $roles = Role::all();

        foreach ($roles as $role) {
            $safeRoleName = str_replace(' ', '_', $role->name);
            $permissionsForRole = $request->input('permissions.' . $safeRoleName, []);
            
            // Sinkronisasi kunci ke jabatan tersebut
            $role->syncPermissions($permissionsForRole);
        }

        return redirect()->back()->with('sukses', 'Matriks Hak Akses berhasil diperbarui! Perubahan langsung aktif.');
    }
}