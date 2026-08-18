<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\HariOperasional;

class HariOperasionalController extends Controller
{
    public function index()
    {
        // KECERDASAN OTOMATIS: Jika tabel masih kosong, buatkan 7 hari default
        if (HariOperasional::count() == 0) {
            $hari_default = ['Sabtu', 'Ahad', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
            foreach ($hari_default as $hari) {
                HariOperasional::create([
                    'hari' => $hari,
                    'is_active' => true,
                    'max_jam' => ($hari == 'Jumat') ? 6 : 10, // Default: Jumat 6 jam, lainnya 10 jam
                    'keterangan' => ($hari == 'Jumat') ? 'Hari Pendek (Persiapan Jumat)' : 'Hari Normal'
                ]);
            }
        }

        // KECERDASAN SISTEM: Mengurutkan data menggunakan memori Laravel (Mendukung PostgreSQL & MySQL)
        $urutanHari = ['Sabtu', 'Ahad', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
        $data = HariOperasional::get()->sortBy(function($item) use ($urutanHari) {
            return array_search($item->hari, $urutanHari);
        })->values();
        
        // Hitung total kapasitas saat ini
        $total_kapasitas = $data->where('is_active', true)->sum('max_jam');

        return view('hari-operasional', compact('data', 'total_kapasitas'));
    }

    public function update(Request $request)
    {
        // Mesin penangkap pembaruan massal (Auto-Save dari banyak baris sekaligus)
        $hari_data = $request->hari_data; // Array data dari form UI

        if ($hari_data) {
            foreach ($hari_data as $id => $data) {
                HariOperasional::where('id', $id)->update([
                    'is_active' => isset($data['is_active']) ? true : false,
                    'max_jam' => $data['max_jam'] ?? 0,
                    'keterangan' => $data['keterangan'] ?? null,
                ]);
            }
        }

        return redirect()->back()->with('sukses', 'Pengaturan Hari & Jam Operasional berhasil diperbarui!');
    }
}