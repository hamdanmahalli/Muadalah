<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Pelajaran;
use App\Models\Periode;
use App\Models\MutasiJadwal;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PanduanController extends Controller
{
    public function index()
    {
        // Ringkasan data dinamis agar panduan terasa 'hidup' dan akurat
        $data = [
            'jumlahPeriode'     => Periode::count(),
            'jumlahGuru'        => Guru::count(),
            'jumlahKelas'       => Kelas::count(),
            'jumlahPelajaran'   => Pelajaran::count(),
            'jumlahMutasi'      => MutasiJadwal::count(),
            'jumlahRole'        => Role::count(),
            'jumlahPermission'  => Permission::count(),
        ];

        $permissions = Permission::orderBy('name')->pluck('name')->toArray();

        // Matriks sederhana: role -> permissions yang di-seed di PermissionSeeder
        $matriksRole = [
            'Administrator' => 'Semua permission',
            'Pimpinan'      => ['akses_dashboard', 'akses_laporan', 'akses_riwayat_mutasi', 'akses_target_mengajar', 'akses_jadwal_harian'],
            'Tata Usaha'    => ['akses_dashboard', 'akses_meja_kontrol', 'akses_laporan', 'akses_master_guru', 'akses_master_pelajaran', 'akses_master_kelas', 'akses_riwayat_mutasi'],
            'Dewan Guru'    => ['akses_dashboard_guru', 'akses_jadwal_saya'],
            'Kepanitiaan'   => ['–'],
            'Wali Kelas'    => ['–'],
            'Murid'         => ['–'],
            'Wali Murid'    => ['–'],
        ];

        return view('panduan.index', compact('data', 'permissions', 'matriksRole'));
    }
}
