<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AngkatanSiswa;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\Periode;

class AngkatanSiswaController extends Controller
{
    public function index(Request $request)
    {
        $periodeId = $request->input('periode_id');
        $kelasId = $request->input('kelas_id');

        // Daftar tahun ajaran unik (penempatan kelas cukup per tahun ajaran, tanpa semester)
        $tahunAjaran = Periode::tahunAjaranList();
        $kelas = Kelas::orderBy('nama_kelas', 'asc')->get();

        $aktif = Periode::where('is_active', true)->first();
        if (!$periodeId && $aktif) {
            // Default: dengan tahun ajaran aktif itu (periode acuan-nya)
            $periodeId = $tahunAjaran->firstWhere('is_active', true)?->periode_id ?? $aktif->id;
        }

        // Daftar siswa (yang belum ditempatkan di periode yang dipilih)
        $terpasangIds = AngkatanSiswa::when($periodeId, fn($q) => $q->where('periode_id', $periodeId))
            ->pluck('siswa_id');

        $siswaBelum = Siswa::aktif()
            ->when($terpasangIds->isNotEmpty(), fn($q) => $q->whereNotIn('id', $terpasangIds))
            ->orderBy('nama_siswa', 'asc')->get();

        $penempatan = AngkatanSiswa::with(['siswa', 'kelas', 'periode'])
            ->when($periodeId, fn($q) => $q->where('periode_id', $periodeId))
            ->when($kelasId, fn($q) => $q->where('kelas_id', $kelasId))
            ->orderBy('kelas_id', 'asc')
            ->paginate(30)
            ->withQueryString();

        return view('penempatan-siswa', compact('tahunAjaran', 'kelas', 'periodeId', 'kelasId', 'siswaBelum', 'penempatan'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'siswa_id' => 'required|exists:siswas,id',
            'kelas_id' => 'required|exists:kelas,id',
        ]);

        $periodeId = $request->periode_id;

        $angkatan = AngkatanSiswa::firstOrNew([
            'siswa_id'   => $request->siswa_id,
            'periode_id' => $periodeId,
        ]);

        // Nomor absen TETAP selama satu tahun ajaran: hanya diisi jika belum ada
        if (!$angkatan->exists || $angkatan->nomor_absen === null) {
            $angkatan->nomor_absen = $this->nomorAbsenBerikutnya($request->kelas_id, $periodeId);
        }

        $angkatan->kelas_id      = $request->kelas_id;
        $angkatan->status        = 'Aktif';
        $angkatan->tanggal_masuk = $request->tanggal_masuk ?? $angkatan->tanggal_masuk ?? now();
        $angkatan->save();

        return redirect()->back()->with('sukses', 'Siswa berhasil ditempatkan ke kelas.');
    }

    public function autoPlace(Request $request)
    {
        $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
        ]);
        $periodeId = $request->periode_id;
        $kelasId = $request->kelas_id;

        $sudah = AngkatanSiswa::where('kelas_id', $kelasId)
            ->when($periodeId, fn($q) => $q->where('periode_id', $periodeId))
            ->pluck('siswa_id');

        $calon = Siswa::aktif()
            ->when($sudah->isNotEmpty(), fn($q) => $q->whereNotIn('id', $sudah))
            ->orderBy('nama_siswa', 'asc')
            ->limit(50)
            ->get();

        $count = 0;
        foreach ($calon as $s) {
            $angkatan = AngkatanSiswa::firstOrNew([
                'siswa_id'   => $s->id,
                'periode_id' => $periodeId,
            ]);

            if (!$angkatan->exists || $angkatan->nomor_absen === null) {
                $angkatan->nomor_absen = $this->nomorAbsenBerikutnya($kelasId, $periodeId);
            }

            $angkatan->kelas_id      = $kelasId;
            $angkatan->status        = 'Aktif';
            $angkatan->tanggal_masuk = $angkatan->tanggal_masuk ?? now();
            $angkatan->save();
            $count++;
        }

        return redirect()->back()->with('sukses', "{$count} siswa dimasukkan otomatis ke kelas.");
    }

    // Nomor absen berikutnya (tertinggi + 1) dalam kelas & periode tertentu
    protected function nomorAbsenBerikutnya($kelasId, $periodeId)
    {
        $max = AngkatanSiswa::where('kelas_id', $kelasId)
            ->when($periodeId, fn($q) => $q->where('periode_id', $periodeId))
            ->max('nomor_absen');

        return $max ? (int)$max + 1 : 1;
    }

    // Menetapkan nomor absen unik: bila nomor yang diminta sudah dipakai siswa lain
    // pada kelas & periode yang sama, siswa itu digeser otomatis ke nomor berikutnya
    // (rantai) sampai ditemukan nomor kosong. Dijalankan dalam satu transaksi.
    protected function assignNomorAbsenUnik($angkatan, $kelasId, $periodeId, $nomor)
    {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($angkatan, $kelasId, $periodeId, $nomor) {
            $nomor = max(1, (int)$nomor);

            // Peta pemilik nomor saat ini (tanpa angkatan yang sedang diubah)
            $peta = AngkatanSiswa::where('kelas_id', $kelasId)
                ->where('periode_id', $periodeId)
                ->whereNotNull('nomor_absen')
                ->where('id', '!=', $angkatan->id)
                ->pluck('id', 'nomor_absen');

            // Cari slot kosong pertama di atas nomor yang diminta (rantai terpakai)
            $target = $nomor;
            while (isset($peta[$target])) {
                $target++;
            }

            // Angkatan tujuan mengambil nomor yang diminta
            $angkatan->kelas_id = $kelasId;
            $angkatan->nomor_absen = $nomor;
            $angkatan->save();

            // Geser pemilik nomor di blok [nomor, target-1] naik satu angka,
            // urut menurun agar nomor tujuan slot selalu bebas
            for ($k = $target - 1; $k >= $nomor; $k--) {
                if (isset($peta[$k])) {
                    AngkatanSiswa::where('id', $peta[$k])->update(['nomor_absen' => $k + 1]);
                }
            }

            return $nomor;
        });
    }

    public function destroy($id)
    {
        AngkatanSiswa::destroy($id);
        return redirect()->back()->with('sukses', 'Penempatan siswa dihapus.');
    }

    public function updateNomorAbsen(Request $request, $id)
    {
        $request->validate([
            'nomor_absen' => 'required|integer|min:1',
        ]);

        $angkatan = AngkatanSiswa::findOrFail($id);
        $this->assignNomorAbsenUnik(
            $angkatan,
            $angkatan->kelas_id,
            $angkatan->periode_id,
            (int)$request->nomor_absen
        );

        return back()->with('sukses', 'Nomor absen diperbarui (nomor dobel otomatis digeser).');
    }
}
