<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\KehadiranGuru;
use App\Models\JadwalHarian;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\AgendaKaldik;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class MonitoringKehadiranController extends Controller
{
    // Status yang boleh diisi Admin (Kosong lama ditampilkan sebagai Alpa)
    protected $daftarStatus = ['Hadir', 'Izin', 'Sakit', 'Alpa', 'Menunggu'];

    public function index(Request $request)
    {
        $tglMulai   = $request->input('tgl_mulai');
        $tglSelesai = $request->input('tgl_selesai');

        // Isi sisi tanggal yang kosong satu per satu (jangan set ulang keduanya).
        // Batas data gabungan dari catatan kehadiran DAN jadwal, agar rentang 'di bawah' ikut terlihat.
        $jadwalMinRaw = JadwalHarian::withTrashed()->min('created_at');
        $jadwalMaxRaw = JadwalHarian::withTrashed()->max('created_at');
        $jadwalMin = $jadwalMinRaw ? substr((string) $jadwalMinRaw, 0, 10) : null;
        $jadwalMax = $jadwalMaxRaw ? substr((string) $jadwalMaxRaw, 0, 10) : null;

        $batasMin = min(
            KehadiranGuru::min('tanggal') ?? '9999-12-31',
            $jadwalMin ?? '9999-12-31'
        );
        $batasMax = max(
            KehadiranGuru::max('tanggal') ?? '1970-01-01',
            $jadwalMax ?? '1970-01-01'
        );

        if (!$tglMulai) {
            $tglMulai = $batasMin !== '9999-12-31' ? $batasMin : Carbon::now()->startOfMonth()->format('Y-m-d');
        }
        if (!$tglSelesai) {
            $tglSelesai = $batasMax !== '1970-01-01' ? $batasMax : Carbon::now()->format('Y-m-d');
        }

        $cari    = $request->input('cari', '');
        $kelasId = $request->input('kelas', '');
        $statusF = $request->input('status', '');

        $periodeAktif = get_periode_aktif();
        $periodeId    = $periodeAktif ? $periodeAktif->id : null;

        $daftarLibur = AgendaKaldik::where('periode_id', $periodeId)
            ->whereIn('jenis_agenda', ['Libur', 'UTS', 'UAS'])
            ->where('tanggal_mulai', '<=', $tglSelesai)
            ->where('tanggal_selesai', '>=', $tglMulai)
            ->get();

        // Semua jadwal (termasuk yang di-soft-delete via mutasi) + relasi
        $semuaJadwal = JadwalHarian::withTrashed()->with(['kelas', 'pelajaran', 'guru'])->get();

        // Semua record kehadiran dalam rentang, diindeks per jadwal_id+tanggal
        $records = KehadiranGuru::whereBetween('tanggal', [$tglMulai, $tglSelesai])->get()
            ->keyBy(fn ($r) => $r->jadwal_id . '|' . $r->tanggal);

        $slots = [];
        $period = CarbonPeriod::create($tglMulai, $tglSelesai);
        foreach ($period as $date) {
            $tglStr   = $date->format('Y-m-d');
            $hariIndo = map_hari($date->format('l'));

            foreach ($semuaJadwal as $j) {
                // Filter kelas
                if ($kelasId !== '' && $kelasId !== null && (int) $j->kelas_id !== (int) $kelasId) {
                    continue;
                }
                // Filter cari (nama/NIG guru)
                if ($cari !== '' && $cari !== null) {
                    $namaG = $j->guru->nama_guru ?? '';
                    $nigG  = $j->guru->nig ?? '';
                    if (stripos($namaG, $cari) === false && stripos($nigG, $cari) === false) {
                        continue;
                    }
                }
                // Cocokkan hari
                if (strtolower($j->hari) !== strtolower($hariIndo)) {
                    continue;
                }
                // Cocokkan tanggal efektif
                $mulaiAktif = $j->created_at ? $j->created_at->format('Y-m-d') : '2000-01-01';
                $selesaiAktif = $j->deleted_at ? $j->deleted_at->format('Y-m-d') : '2099-12-31';
                if ($tglStr < $mulaiAktif || $tglStr > $selesaiAktif) {
                    continue;
                }
                // Lewati hari libur
                $libur = $this->isLibur($j, $daftarLibur, $tglStr);
                if ($libur['is_libur']) {
                    continue;
                }

                $record = $records->get($j->id . '|' . $tglStr);

                // Kosong (atau Alpha) ditampilkan sebagai Alpa
                $statusAsli = $record ? $record->status : 'Menunggu';
                $statusTampil = in_array($statusAsli, ['Kosong', 'Alpha']) ? 'Alpa' : $statusAsli;

                // Filter status yang ditampilkan (cocok dengan status tampilan)
                if ($statusF !== '' && $statusF !== null && $statusTampil !== $statusF) {
                    continue;
                }

                $slots[] = [
                    'jadwal_id'   => $j->id,
                    'tanggal'     => $tglStr,
                    'hari'        => $hariIndo,
                    'jam_ke'      => (int) ($j->jam_ke ?? 0),
                    'kelas'       => $j->kelas->nama_kelas ?? '-',
                    'kelas_urut'  => (int) ($j->kelas_id ?? 0),
                    'mapel'       => $j->pelajaran->nama_pelajaran ?? '-',
                    'guru'        => $j->guru->nama_guru ?? '-',
                    'guru_urut'   => (int) ($j->guru_id ?? 0),
                    'status'      => $statusTampil,
                    'ada_record'  => (bool) $record,
                    'keterangan'  => $record ? $record->keterangan : null,
                    'pengganti'   => $record ? $record->nig_pengganti : null,
                ];
            }
        }

        // Urutkan per tanggal, lalu kelas, lalu guru, lalu jam — agar jam gandeng per blok guru
        usort($slots, fn ($a, $b) =>
            [$a['tanggal'], $a['kelas_urut'], $a['guru_urut'], $a['jam_ke']]
            <=> [$b['tanggal'], $b['kelas_urut'], $b['guru_urut'], $b['jam_ke']]);

        // Total per status dari baris hasil
        $total = ['Hadir' => 0, 'Izin' => 0, 'Sakit' => 0, 'Alpa' => 0, 'Menunggu' => 0];
        foreach ($slots as $s) {
            if (isset($total[$s['status']])) {
                $total[$s['status']]++;
            }
        }

        $daftarKelas = Kelas::orderBy('nama_kelas')->get();
        $daftarStatus = $this->daftarStatus;

        return view('monitoring-kehadiran', compact(
            'slots', 'total', 'daftarKelas', 'daftarStatus',
            'tglMulai', 'tglSelesai', 'cari', 'kelasId', 'statusF'
        ));
    }

    // AJAX: daftar slot (kehadiran + belum diisi) untuk satu guru pada rentang tanggal
    public function detailGuru(Request $request)
    {
        $guruId    = $request->input('guru_id');
        $tglMulai  = $request->input('tgl_mulai');
        $tglSelesai = $request->input('tgl_selesai');

        $guru = Guru::find($guruId);
        if (!$guru) {
            return response()->json(['status' => 'error', 'pesan' => 'Guru tidak ditemukan.']);
        }

        $periodeAktif = get_periode_aktif();
        $periodeId    = $periodeAktif ? $periodeAktif->id : null;

        $semuaJadwal = JadwalHarian::withTrashed()->with(['kelas', 'pelajaran'])->get();
        $daftarLibur = AgendaKaldik::where('periode_id', $periodeId)
            ->whereIn('jenis_agenda', ['Libur', 'UTS', 'UAS'])
            ->where('tanggal_mulai', '<=', $tglSelesai)
            ->where('tanggal_selesai', '>=', $tglMulai)
            ->get();

        $records = KehadiranGuru::whereBetween('tanggal', [$tglMulai, $tglSelesai])->get()
            ->keyBy(fn ($r) => $r->jadwal_id . '|' . $r->tanggal);

        $slots = [];
        $period = CarbonPeriod::create($tglMulai, $tglSelesai);
        foreach ($period as $date) {
            $tglStr   = $date->format('Y-m-d');
            $hariIndo = map_hari($date->format('l'));

            foreach ($semuaJadwal->where('guru_id', $guru->id) as $j) {
                if (strtolower($j->hari) !== strtolower($hariIndo)) {
                    continue;
                }
                $mulaiAktif = $j->created_at ? $j->created_at->format('Y-m-d') : '2000-01-01';
                $selesaiAktif = $j->deleted_at ? $j->deleted_at->format('Y-m-d') : '2099-12-31';
                if ($tglStr < $mulaiAktif || $tglStr > $selesaiAktif) {
                    continue;
                }
                $libur = $this->isLibur($j, $daftarLibur, $tglStr);
                if ($libur['is_libur']) {
                    continue;
                }

                $record = $records->get($j->id . '|' . $tglStr);
                $slots[] = [
                    'jadwal_id'   => $j->id,
                    'tanggal'     => $tglStr,
                    'hari'        => $hariIndo,
                    'jam_ke'      => $j->jam_ke,
                    'kelas'       => $j->kelas->nama_kelas ?? '-',
                    'mapel'       => $j->pelajaran->nama_pelajaran ?? '-',
                    'status'      => $record ? $record->status : 'Menunggu',
                    'ada_record'  => (bool) $record,
                    'keterangan'  => $record ? $record->keterangan : null,
                    'pengganti'   => $record ? $record->nig_pengganti : null,
                ];
            }
        }

        // urutkan per tanggal lalu jam
        usort($slots, fn ($a, $b) => [$a['tanggal'], $a['jam_ke']] <=> [$b['tanggal'], $b['jam_ke']]);

        return response()->json(['status' => 'success', 'guru' => $guru->nama_guru, 'slots' => $slots]);
    }

    // Simpan / buat record kehadiran (updateOrCreate per jadwal_id + tanggal)
    public function update(Request $request)
    {
        $request->validate([
            'jadwal_id'    => 'required|integer',
            'tanggal'      => 'required|date',
            'status'       => 'required|string',
            'keterangan'   => 'nullable|string',
            'nig_pengganti'=> 'nullable|string',
        ]);

        if (!in_array($request->status, ['Hadir', 'Izin', 'Sakit', 'Alpa', 'Menunggu'])) {
            return response()->json(['status' => 'error', 'pesan' => 'Status tidak valid.'], 422);
        }

        $periodeAktif = get_periode_aktif();

        // 'Alpa' tersimpan sebagai 'Alpa' (Kosong lama ditampilkan sebagai Alpa)
        $statusTersimpan = $request->status;

        KehadiranGuru::updateOrCreate(
            [
                'jadwal_id' => $request->jadwal_id,
                'tanggal'   => $request->tanggal,
            ],
            [
                'status'        => $statusTersimpan,
                'keterangan'    => $request->keterangan ?: null,
                'nig_pengganti' => $request->nig_pengganti ?: null,
                'periode_id'    => $periodeAktif ? $periodeAktif->id : null,
            ]
        );

        return response()->json(['status' => 'success', 'pesan' => 'Status kehadiran berhasil disimpan!']);
    }

    private function isLibur($jadwal, $daftarLibur, ?string $tglStr = null): array
    {
        $isLibur = false;
        $namaLibur = '';

        foreach ($daftarLibur as $agenda) {
            if ($tglStr !== null) {
                $mulai = Carbon::parse($agenda->tanggal_mulai)->format('Y-m-d');
                $selesai = Carbon::parse($agenda->tanggal_selesai)->format('Y-m-d');
                if ($tglStr < $mulai || $tglStr > $selesai) {
                    continue;
                }
            }

            $arrKls = is_array($agenda->kelas_ids)
                ? $agenda->kelas_ids
                : (is_string($agenda->kelas_ids) ? json_decode($agenda->kelas_ids, true) : []);

            $kenaTarget = false;
            if ($agenda->target_libur === 'semua') {
                $kenaTarget = true;
            } elseif ($agenda->target_libur === 'kelas_tertentu' && in_array($jadwal->kelas_id, $arrKls)) {
                $kenaTarget = true;
            }

            if (!$kenaTarget) {
                continue;
            }

            if ($agenda->tipe_agenda === 'Penuh') {
                $isLibur = true;
                $namaLibur = $agenda->nama_agenda;
                break;
            }

            if ($agenda->tipe_agenda === 'Parsial') {
                $jamArr = is_array($agenda->jam_diliburkan)
                    ? $agenda->jam_diliburkan
                    : (is_string($agenda->jam_diliburkan) ? json_decode($agenda->jam_diliburkan, true) : []);
                if (in_array($jadwal->jam_ke, $jamArr)) {
                    $isLibur = true;
                    $namaLibur = $agenda->nama_agenda;
                    break;
                }
            }
        }

        return ['is_libur' => $isLibur, 'nama_libur' => $namaLibur];
    }
}
