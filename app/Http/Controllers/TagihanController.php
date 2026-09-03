<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\JenisTagihan;
use App\Models\Tagihan;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\Periode;
use App\Models\AngkatanSiswa;

class TagihanController extends Controller
{
    public function index(Request $request)
    {
        $jenisTagihan = JenisTagihan::orderBy('nama_tagihan', 'asc')->get();

        $tagihans = Tagihan::with(['siswa', 'jenisTagihan', 'periode'])
            ->latest()
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->jenis_tagihan_id, fn($q) => $q->where('jenis_tagihan_id', $request->jenis_tagihan_id))
            ->when($request->search, fn($q) => $q->whereHas('siswa', fn($w) =>
                $w->where('nama_siswa', 'ilike', "%{$request->search}%")
                  ->orWhere('nis', 'ilike', "%{$request->search}%")
            ))
            ->paginate(30)
            ->withQueryString();

        $totalBelum = Tagihan::where('status', 'belum')->sum('nominal');
        $totalLunas = Tagihan::where('status', 'lunas')->sum('nominal');

        return view('tagihan.index', compact('jenisTagihan', 'tagihans', 'totalBelum', 'totalLunas'));
    }

    // CRUD Jenis Tagihan
    public function storeJenis(Request $request)
    {
        $request->validate(['nama_tagihan' => 'required|string|max:255']);
        JenisTagihan::create([
            'nama_tagihan' => $request->nama_tagihan,
            'deskripsi' => $request->deskripsi,
        ]);
        return redirect()->back()->with('sukses', 'Jenis tagihan berhasil ditambahkan.');
    }

    public function updateJenis(Request $request, $id)
    {
        $jenis = JenisTagihan::findOrFail($id);
        $request->validate(['nama_tagihan' => 'required|string|max:255']);
        $jenis->update([
            'nama_tagihan' => $request->nama_tagihan,
            'deskripsi' => $request->deskripsi,
            'status' => $request->status ?? 'Aktif',
        ]);
        return redirect()->back()->with('sukses', 'Jenis tagihan diperbarui.');
    }

    public function destroyJenis($id)
    {
        JenisTagihan::destroy($id);
        return redirect()->back()->with('sukses', 'Jenis tagihan dihapus.');
    }

    // Form buat tagihan
    public function buat()
    {
        $jenisTagihan = JenisTagihan::where('status', 'Aktif')->get();
        $kelas = Kelas::orderBy('nama_kelas', 'asc')->get();
        $periodes = Periode::orderBy('tahun_ajaran', 'desc')->get();
        $aktif = Periode::where('is_active', true)->first();
        $siswas = Siswa::aktif()->orderBy('nama_siswa', 'asc')->get();
        return view('tagihan.buat', compact('jenisTagihan', 'kelas', 'periodes', 'aktif', 'siswas'));
    }

    // Proses buat tagihan (target: semua kelas / kelas tertentu / murid tertentu)
    public function store(Request $request)
    {
        $request->validate([
            'jenis_tagihan_id' => 'required|exists:jenis_tagihans,id',
            'nominal' => 'required|numeric|min:0',
            'target' => 'required|in:semua_kelas,kelas_tertentu,murid_tertentu',
        ]);

        $periodeId = $request->periode_id;
        $scope = $request->target;

        // Tentukan daftar siswa yang dikenakan tagihan
        if ($scope === 'semua_kelas') {
            $siswaIds = Siswa::aktif()->pluck('id');
        } elseif ($scope === 'kelas_tertentu') {
            $request->validate(['kelas_id' => 'required|exists:kelas,id']);
            $siswaIds = AngkatanSiswa::where('kelas_id', $request->kelas_id)
                ->when($periodeId, fn($q) => $q->where('periode_id', $periodeId))
                ->pluck('siswa_id');
        } else {
            $request->validate(['siswa_ids' => 'required|array|min:1']);
            $siswaIds = collect($request->siswa_ids);
        }

        $count = 0;
        foreach ($siswaIds as $sid) {
            // Hindari duplikat untuk jenis + periode + siswa yang sama & belum lunas
            $sudah = Tagihan::where('siswa_id', $sid)
                ->where('jenis_tagihan_id', $request->jenis_tagihan_id)
                ->when($periodeId, fn($q) => $q->where('periode_id', $periodeId))
                ->where(function ($q) {
                    $q->where('status', 'belum')->orWhere('status', 'parsial');
                })
                ->exists();

            if ($sudah) continue;

            Tagihan::create([
                'siswa_id' => $sid,
                'jenis_tagihan_id' => $request->jenis_tagihan_id,
                'periode_id' => $periodeId,
                'target_scope' => $scope,
                'target_kelas_id' => $scope === 'kelas_tertentu' ? $request->kelas_id : null,
                'keterangan' => $request->keterangan,
                'nominal' => $request->nominal,
                'tanggal_jatuh_tempo' => $request->tanggal_jatuh_tempo,
                'status' => 'belum',
                'dibuat_oleh' => auth()->id(),
            ]);
            $count++;
        }

        return redirect('/tagihan')->with('sukses', "{$count} tagihan berhasil dibuat.");
    }

    public function detail($id)
    {
        $tagihan = Tagihan::with(['siswa', 'jenisTagihan', 'pembayarans'])->findOrFail($id);
        return view('tagihan.detail', compact('tagihan'));
    }
}
