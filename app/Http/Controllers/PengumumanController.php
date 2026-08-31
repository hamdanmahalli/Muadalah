<?php

namespace App\Http\Controllers;

use App\Models\Pengumuman;
use Illuminate\Http\Request;

class PengumumanController extends Controller
{
    public function index()
    {
        $pengumuman = Pengumuman::latest()->get();
        return view('admin.pengumuman.index', compact('pengumuman'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'judul' => 'required|string|max:255',
            'isi' => 'nullable|string',
            'warna' => 'nullable|string|max:50',
            'gambar' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:5120',
            'aktif' => 'nullable|boolean',
            'tanggal_mulai' => 'nullable|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
        ]);

        $gambar = null;
        if ($request->hasFile('gambar')) {
            $gambar = $request->file('gambar')->store('pengumuman', 'public');
        }

        Pengumuman::create([
            'judul' => $request->judul,
            'isi' => $request->isi,
            'warna' => $request->warna ?? 'emerald',
            'gambar' => $gambar,
            'aktif' => $request->has('aktif'),
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'created_by' => auth()->id(),
        ]);

        return redirect()->back()->with('success', 'Pengumuman berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'judul' => 'required|string|max:255',
            'isi' => 'nullable|string',
            'warna' => 'nullable|string|max:50',
            'gambar' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:5120',
            'aktif' => 'nullable|boolean',
            'tanggal_mulai' => 'nullable|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
        ]);

        $item = Pengumuman::findOrFail($id);

        $gambar = $item->gambar;
        if ($request->hasFile('gambar')) {
            if ($item->gambar && \Illuminate\Support\Facades\Storage::disk('public')->exists($item->gambar)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($item->gambar);
            }
            $gambar = $request->file('gambar')->store('pengumuman', 'public');
        }

        $item->update([
            'judul' => $request->judul,
            'isi' => $request->isi,
            'warna' => $request->warna ?? 'emerald',
            'gambar' => $gambar,
            'aktif' => $request->has('aktif'),
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
        ]);

        return redirect()->back()->with('success', 'Pengumuman berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $item = Pengumuman::findOrFail($id);
        $item->delete();

        return redirect()->back()->with('success', 'Pengumuman berhasil dihapus.');
    }
}
