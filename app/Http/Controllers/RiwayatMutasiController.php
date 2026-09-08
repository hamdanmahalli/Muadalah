<?php

namespace App\Http\Controllers;

use App\Models\MutasiJadwal;
use App\Models\JadwalHarian;
use App\Models\Kelas;
use App\Models\Guru;
use App\Models\Pelajaran;
use App\Models\Periode;
use App\Services\MutasiLogService;
use Illuminate\Http\Request;

class RiwayatMutasiController extends Controller
{
    public function index(Request $request)
    {
        $periodeAktif = get_periode_aktif();

        $kelasList = Kelas::orderBy('nama_kelas')->get();
        $guruList = Guru::orderBy('nama_guru')->get();
        $periodeList = Periode::orderByDesc('tahun_ajaran')->get();

        $tipeFilter = $request->tipe;
        $kelasFilter = $request->kelas_id;
        $guruFilter = $request->guru_id;
        $periodeFilter = $request->periode_id;
        $cari = trim($request->cari ?? '');

        $query = MutasiJadwal::with(['kelas', 'pelajaran', 'guruLama', 'guruBaru', 'user'])
            ->latest('tanggal_kejadian')
            ->latest('created_at');

        if ($tipeFilter)      $query->where('tipe', $tipeFilter);
        if ($kelasFilter)     $query->where('kelas_id', $kelasFilter);
        if ($guruFilter)      $query->where(function ($q) use ($guruFilter) {
            $q->where('guru_lama_id', $guruFilter)->orWhere('guru_baru_id', $guruFilter);
        });
        if ($periodeFilter)   $query->where('periode_id', $periodeFilter);
        elseif ($periodeAktif) $query->where('periode_id', $periodeAktif->id);

        if ($cari !== '') {
            $query->where(function ($q) use ($cari) {
                $q->where('keterangan', 'ilike', '%' . $cari . '%')
                  ->orWhereHas('kelas', fn($x) => $x->where('nama_kelas', 'ilike', '%' . $cari . '%'))
                  ->orWhereHas('pelajaran', fn($x) => $x->where('nama_pelajaran', 'ilike', '%' . $cari . '%'))
                  ->orWhereHas('guruLama', fn($x) => $x->where('nama_guru', 'ilike', '%' . $cari . '%'))
                  ->orWhereHas('guruBaru', fn($x) => $x->where('nama_guru', 'ilike', '%' . $cari . '%'));
            });
        }

        $riwayat = $query->limit(500)->get();

        $statistik = [
            'total'         => MutasiJadwal::count(),
            'ganti_guru'    => MutasiJadwal::where('tipe', 'ganti_guru')->count(),
            'tukar_jam'     => MutasiJadwal::whereIn('tipe', ['tukar_jam', 'pindah_blok'])->count(),
            'hapus_slot'    => MutasiJadwal::where('tipe', 'hapus_slot')->count(),
            'plot_sync'     => MutasiJadwal::where('tipe', 'plot_sync')->count(),
        ];

        return view('admin.riwayat-mutasi', compact(
            'riwayat', 'kelasList', 'guruList', 'periodeList',
            'tipeFilter', 'kelasFilter', 'guruFilter', 'periodeFilter', 'cari',
            'periodeAktif', 'statistik'
        ));
    }

    public function destroy($id)
    {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($id) {
            $mutasi = MutasiJadwal::find($id);
            if (!$mutasi) {
                return redirect()->back()->with('error', 'Riwayat mutasi tidak ditemukan.');
            }

            $pesan = 'Riwayat mutasi berhasil dihapus.';
            $tglEfektif = $mutasi->tanggal_efektif ? $mutasi->tanggal_efektif->format('Y-m-d') : null;

            // ===== KEMBALIKAN JADWAL (undo) untuk tipe yang bisa di-rollback =====
            if (in_array($mutasi->tipe, ['tukar_jam', 'pindah_blok', 'ganti_guru']) && $tglEfektif) {
                $kemarin = \Carbon\Carbon::parse($tglEfektif)->subDay()->format('Y-m-d');
                $adaPerubahan = false;

                if ($mutasi->tipe == 'tukar_jam') {
                    // Swap menyentuh DUA blok (dua sisi, bisa beda pelajaran/guru).
                    // Balik SEMUA slot yang terkena tanggal efektif pada hari-hari yang terlibat,
                    // sehingga blok 5-6 dan 7-8 kembali utuh seperti semula.
                    $rekamLama = JadwalHarian::where('kelas_id', $mutasi->kelas_id)
                        ->where('pelajaran_id', $mutasi->pelajaran_id)
                        ->where('guru_id', $mutasi->guru_lama_id)
                        ->where('berlaku_sampai', $kemarin)
                        ->orderBy('id')
                        ->first();

                    $rekamBaru = JadwalHarian::where('kelas_id', $mutasi->kelas_id)
                        ->where('pelajaran_id', $mutasi->pelajaran_id)
                        ->where('guru_id', $mutasi->guru_lama_id)
                        ->where('berlaku_mulai', $tglEfektif)
                        ->orderBy('id')
                        ->first();

                    if ($rekamLama && $rekamBaru) {
                        $hariTerlibat = array_values(array_unique([$rekamLama->hari, $rekamBaru->hari]));

                        // Buka kembali rekaman "sebelum swap" (berlaku_sampai = sehari sebelum efektif)
                        $yangDibuka = JadwalHarian::where('kelas_id', $mutasi->kelas_id)
                            ->whereIn('hari', $hariTerlibat)
                            ->where('berlaku_sampai', $kemarin)
                            ->get();
                        foreach ($yangDibuka as $r) {
                            if ($r->berlaku_sampai == $kemarin) {
                                $r->berlaku_sampai = null;
                                $r->save();
                                $adaPerubahan = true;
                            }
                        }

                        // Tutup rekaman "sesudah swap" (berlaku_mulai = tanggal efektif)
                        $yangDitutup = JadwalHarian::where('kelas_id', $mutasi->kelas_id)
                            ->whereIn('hari', $hariTerlibat)
                            ->where('berlaku_mulai', $tglEfektif)
                            ->get();
                        foreach ($yangDitutup as $r) {
                            if ($r->berlaku_mulai == $tglEfektif) {
                                $r->berlaku_sampai = $kemarin;
                                $r->save();
                                $adaPerubahan = true;
                            }
                        }
                    }

                    $pesan = $adaPerubahan
                        ? 'Riwayat dihapus dan tukar jam dibatalkan (dua blok dikembalikan ke posisi semula terhitung tanggal efekfit).'
                        : $pesan . ' (Riwayat sudah tidak bisa dibatalkan — kemungkinan tukar lama tanpa tanggal efekfit).';
                } else {
                    // pindah_blok / ganti_guru: satu guru & satu pelajaran (blok tetap, jam terjaga)
                    $queryLama = JadwalHarian::where('kelas_id', $mutasi->kelas_id)
                        ->where('pelajaran_id', $mutasi->pelajaran_id)
                        ->where('guru_id', $mutasi->guru_lama_id)
                        ->where('berlaku_sampai', $kemarin);

                    $queryBaru = JadwalHarian::where('kelas_id', $mutasi->kelas_id)
                        ->where('pelajaran_id', $mutasi->pelajaran_id)
                        ->where('guru_id', $mutasi->guru_baru_id)
                        ->where('berlaku_mulai', $tglEfektif);

                    if ($mutasi->tipe == 'ganti_guru' && $mutasi->hari && $mutasi->jam_ke) {
                        $queryLama->where('hari', $mutasi->hari)->where('jam_ke', $mutasi->jam_ke);
                        $queryBaru->where('hari', $mutasi->hari)->where('jam_ke', $mutasi->jam_ke);
                    }

                    foreach ($queryLama->get() as $r) {
                        if ($r->berlaku_sampai == $kemarin) {
                            $r->berlaku_sampai = null;
                            $r->save();
                            $adaPerubahan = true;
                        }
                    }
                    foreach ($queryBaru->get() as $r) {
                        if ($r->berlaku_mulai == $tglEfektif) {
                            $r->berlaku_sampai = $kemarin;
                            $r->save();
                            $adaPerubahan = true;
                        }
                    }

                    $pesan = $adaPerubahan
                        ? 'Riwayat dihapus dan jadwal dikembalikan ke kondisi sebelum mutasi.'
                        : $pesan . ' (Tidak ada jadwal yang bisa dikembalikan — kemungkinan riwayat lama tanpa tanggal efekfit).';
                }
            }

            // Tukar jam mencatat dua baris (sisi guru) — hapus pasangannya agar tidak mengambang
            if ($mutasi->tipe == 'tukar_jam' && $tglEfektif) {
                $pasangan = MutasiJadwal::where('tipe', 'tukar_jam')
                    ->where('kelas_id', $mutasi->kelas_id)
                    ->whereDate('tanggal_efektif', $tglEfektif)
                    ->where('id', '!=', $mutasi->id)
                    ->where('guru_lama_id', '!=', $mutasi->guru_lama_id)
                    ->first();
                if ($pasangan) {
                    $pasangan->delete();
                }
            }

            $mutasi->delete();

            return redirect()->back()->with('sukses', $pesan);
        });
    }

    /**
     * Halaman kelola / perbaiki tanggal masa berlaku (berlaku_mulai/sampai) tiap slot.
     */
    public function kelolaTanggal(Request $request)
    {
        $kelasList = Kelas::orderBy('nama_kelas')->get();
        $kelasFilter = $request->kelas_id;
        $guruFilter = $request->guru_id;

        $guruList = Guru::orderBy('nama_guru')->get();

        $periodeAktif = get_periode_aktif();
        $tahunAjaran = $periodeAktif->tahun_ajaran ?? null;

        $query = JadwalHarian::with(['kelas', 'pelajaran', 'guru']);

        if ($kelasFilter) $query->where('kelas_id', $kelasFilter);
        if ($guruFilter)  $query->where('guru_id', $guruFilter);
        if ($tahunAjaran && !$request->semua) {
            $query->where(function ($q) use ($tahunAjaran) {
                $q->where('tahun_ajaran', $tahunAjaran)->orWhereNull('tahun_ajaran');
            });
        }

        $jadwal = $query->orderBy('kelas_id')->orderBy('hari')->orderBy('jam_ke')->limit(800)->get();

        return view('admin.kelola-tanggal-mutasi', compact(
            'jadwal', 'kelasList', 'guruList', 'kelasFilter', 'guruFilter', 'periodeAktif', 'tahunAjaran'
        ));
    }

    public function simpanTanggal(Request $request)
    {
        $request->validate([
            'jadwal_id'     => 'required|exists:jadwal_harians,id',
            'berlaku_mulai' => 'nullable|date',
            'berlaku_sampai'=> 'nullable|date|after_or_equal:berlaku_mulai',
        ]);

        $jadwal = JadwalHarian::findOrFail($request->jadwal_id);

        $mulaiLama = $jadwal->berlaku_mulai;
        $sampaiLama = $jadwal->berlaku_sampai;

        $jadwal->berlaku_mulai = $request->berlaku_mulai ?: null;
        $jadwal->berlaku_sampai = $request->berlaku_sampai ?: null;
        $jadwal->save();

        MutasiLogService::catat([
            'kelas_id'        => $jadwal->kelas_id,
            'pelajaran_id'    => $jadwal->pelajaran_id,
            'hari'            => $jadwal->hari,
            'jam_ke'          => $jadwal->jam_ke,
            'jadwal_id'       => $jadwal->id,
            'guru_lama_id'    => $jadwal->guru_id,
            'guru_baru_id'    => $jadwal->guru_id,
            'tipe'            => 'ganti_guru',
            'tanggal_efektif' => $request->berlaku_mulai,
            'keterangan'      => 'Perbaikan masa berlaku jadwal: mulai ' . ($mulaiLama ?? '-') . ' -> ' . ($jadwal->berlaku_mulai ?? '-') . ' | selesai ' . ($sampaiLama ?? '-') . ' -> ' . ($jadwal->berlaku_sampai ?? '-'),
        ]);

        return redirect()->back()->with('sukses', 'Tanggal masa berlaku jadwal berhasil diperbarui.');
    }
}
