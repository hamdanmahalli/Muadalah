<?php

namespace App\Services\Kehadiran;

use App\Models\AgendaKegiatan;
use App\Models\Guru;
use App\Models\JadwalHarian;
use App\Models\KehadiranGuru;
use App\Models\KehadiranKegiatan;
use App\Models\MasterJam;
use App\Services\AuthenticatedGuruService;
use Carbon\Carbon;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

/**
 * Mesin pemroses scan QR (absen kelas & absen kegiatan) untuk guru.
 *
 * SRP: Satu tanggung jawab — memproses data QR menjadi aksi kehadiran dan
 * mengembalikan hasil terstruktur. Stateless; controller hanya membungkusnya
 * menjadi respons HTTP JSON.
 */
class KehadiranScanService
{
    public function __construct(private AuthenticatedGuruService $guruService) {}

    /**
     * QR Code pribadi guru (data URI SVG) untuk tab dalam halaman scan.
     */
    public function qrPribadi(): ?string
    {
        $guru = $this->guruService->fromAuthUser();
        if (!$guru || !$guru->nig) {
            return null;
        }
        $isiQR = 'GURU-' . $guru->nig;
        return rawurlencode((string) QrCode::format('svg')->size(320)->margin(2)->generate($isiQR));
    }

    /**
     * Memproses hasil baca QR.
     *
     * @return array{status: string, pesan: string, data?: array}
     *
     * @throws \Exception bila terjadi kesalahan sistem
     */
    public function proses(?string $qrData): array
    {
        $guruSAYA = $this->guruService->fromAuthUser();
        if (!$guruSAYA) {
            return ['status' => 'error', 'pesan' => 'Akun Anda belum terhubung dengan data Master Guru.'];
        }

        // ===== JALUR A: SCAN QR AGENDA / KEGIATAN HRIS =====
        if ($qrData && strpos($qrData, 'AGENDA-') === 0) {
            return $this->prosesAgenda($qrData, $guruSAYA);
        }

        // ===== JALUR B: SCAN QR KELAS / JADWAL MENGAJAR =====
        return $this->prosesKelas($qrData, $guruSAYA);
    }

    /**
     * Rekam kehadiran piket (guru lain) untuk sekumpulan id jadwal.
     */
    public function catatPiket(array $jadwalIds): int
    {
        $guruSAYA = $this->guruService->fromAuthUser();
        $periodeAktif = get_periode_aktif();
        $tanggalSekarang = Carbon::now()->format('Y-m-d');

        $jumlahDisimpan = 0;
        foreach ($jadwalIds as $id) {
            KehadiranGuru::updateOrCreate(
                ['jadwal_id' => $id, 'tanggal' => $tanggalSekarang],
                [
                    'status' => 'Kosong',
                    'keterangan' => 'Inval/Piket. Menunggu validasi TU.',
                    'nig_pengganti' => $guruSAYA->nig,
                    'periode_id' => $periodeAktif ? $periodeAktif->id : null,
                ]
            );
            $jumlahDisimpan++;
        }

        return $jumlahDisimpan;
    }

    private function prosesAgenda(string $qrData, Guru $guruSAYA): array
    {
        $agenda = AgendaKegiatan::where('qr_token', $qrData)->first();
        if (!$agenda) {
            return ['status' => 'error', 'pesan' => 'QR Code Agenda tidak valid atau acara tidak ditemukan!'];
        }
        if (!$agenda->is_open) {
            return ['status' => 'error', 'pesan' => 'Absensi untuk kegiatan ini sudah ditutup oleh TU.'];
        }

        $sudahHadir = KehadiranKegiatan::where('agenda_kegiatan_id', $agenda->id)
            ->where('guru_id', $guruSAYA->id)
            ->exists();
        if ($sudahHadir) {
            return ['status' => 'success', 'pesan' => 'Anda sudah tercatat hadir pada kegiatan ini sebelumnya.'];
        }

        KehadiranKegiatan::create([
            'agenda_kegiatan_id' => $agenda->id,
            'guru_id' => $guruSAYA->id,
            'waktu_hadir' => Carbon::now(),
            'metode' => 'Scan QR',
        ]);

        return ['status' => 'success', 'pesan' => 'Kehadiran Kegiatan: ' . $agenda->nama_kegiatan . ' berhasil dicatat!'];
    }

    private function prosesKelas(?string $qrData, Guru $guruSAYA): array
    {
        $parts = $qrData ? explode('-', $qrData) : [];
        if (count($parts) != 3 || $parts[0] != 'SP') {
            return ['status' => 'error', 'pesan' => 'Barcode tidak dikenali / bukan dari SmartPesantren!'];
        }

        $kelas_id = $parts[1];
        $token = $parts[2];

        // Validasi kriptografi token kelas
        $now = Carbon::now();
        $hari = $now->dayOfWeek;
        $jam = (int) $now->format('H');
        if ($hari === Carbon::THURSDAY && $jam >= 18 || $hari === Carbon::FRIDAY) {
            $tanggalKunci = $now->copy()->next(Carbon::SATURDAY)->startOfDay()->format('Y-m-d');
        } else {
            $tanggalKunci = $now->copy()->startOfWeek(Carbon::SATURDAY)->format('Y-m-d');
        }
        $teksRahasia = $kelas_id . '|' . $tanggalKunci . '|' . config('app.key');
        if ($token !== hash_hmac('sha256', $teksRahasia, config('app.key'))) {
            return ['status' => 'error', 'pesan' => 'BARCODE KADALUARSA! Silakan scan barcode terbaru minggu ini.'];
        }

        $hariIni = map_hari(Carbon::now()->format('l'));
        $tanggalSekarang = Carbon::now()->format('Y-m-d');
        $waktuSekarang = Carbon::now()->format('H:i:s');

        // Ambil jadwal valid pada hari ini (konsep effective-dated)
        $semuaJadwalRuanganIni = JadwalHarian::with(['guru', 'pelajaran'])
            ->where('kelas_id', $kelas_id)
            ->where('hari', $hariIni)
            ->where(function ($query) use ($tanggalSekarang) {
                $query->whereNull('berlaku_mulai')
                      ->orWhere('berlaku_mulai', '<=', $tanggalSekarang);
            })
            ->where(function ($query) use ($tanggalSekarang) {
                $query->whereNull('berlaku_sampai')
                      ->orWhere('berlaku_sampai', '>=', $tanggalSekarang);
            })
            ->get();

        // Kecerdasan blok jam: filter jadwal yang aktif "saat detik ini"
        $masterJams = MasterJam::all()->keyBy('jam_ke');
        $jadwalAktif = [];
        $jamKeDitemukan = [];

        foreach ($semuaJadwalRuanganIni as $j) {
            $masterJam = $masterJams[$j->jam_ke] ?? null;
            if ($masterJam) {
                $batasBawah = Carbon::parse($masterJam->jam_mulai)->subMinutes(15)->format('H:i:s');
                $batasAtas = Carbon::parse($masterJam->jam_selesai)->addMinutes(15)->format('H:i:s');
                if ($waktuSekarang >= $batasBawah && $waktuSekarang <= $batasAtas) {
                    $jadwalAktif[] = $j;
                    $jamKeDitemukan[] = $j->jam_ke;
                }
            }
        }

        if (empty($jadwalAktif)) {
            return ['status' => 'error', 'pesan' => 'Waktu absen tertutup! Tidak ada KBM yang sedang berlangsung di kelas ini pada pukul ' . Carbon::now()->format('H:i')];
        }

        // Percabangan: jadwal SAYA atau jadwal ORANG LAIN? (Piket)
        $jadwalMilikSaya = array_filter($jadwalAktif, function ($j) use ($guruSAYA) {
            return $j->guru_id == $guruSAYA->id;
        });

        $periodeAktif = get_periode_aktif();

        if (count($jadwalMilikSaya) > 0) {
            // SKENARIO A: NORMAL (jadwal milik sendiri)
            $jumlahDisimpan = 0;
            foreach ($jadwalMilikSaya as $j) {
                $sudahAbsen = KehadiranGuru::query()
                    ->where('jadwal_id', $j->id)->where('tanggal', $tanggalSekarang)->exists();

                if (!$sudahAbsen) {
                    KehadiranGuru::create([
                        'jadwal_id'  => $j->id,
                        'tanggal'    => $tanggalSekarang,
                        'status'     => 'Hadir',
                        'periode_id' => $periodeAktif ? $periodeAktif->id : null,
                    ]);
                    $jumlahDisimpan++;
                }
            }

            if ($jumlahDisimpan > 0) {
                return ['status' => 'success', 'pesan' => 'Hadir (Jam Ke: ' . implode(',', $jamKeDitemukan) . ') berhasil dicatat!'];
            }
            return ['status' => 'success', 'pesan' => 'Kehadiran Anda di jam ini sudah tercatat sebelumnya.'];
        }

        // SKENARIO B: INVAL / PIKET (jadwal milik guru lain)
        $jadwalGuruLain = reset($jadwalAktif);
        $jadwalIds = array_map(function ($j) {
            return $j->id;
        }, $jadwalAktif);

        $namaGuruAsli = $jadwalGuruLain->guru->nama_guru ?? 'Guru Tanpa Nama';
        $matpelAsli = $jadwalGuruLain->pelajaran->nama_pelajaran ?? 'Pelajaran';

        return [
            'status' => 'confirm_piket',
            'pesan' => "Jadwal ini milik Ust/Ustz. <b>{$namaGuruAsli}</b> ({$matpelAsli}).<br>Apakah Anda masuk untuk menggantikan (Piket)?",
            'data' => [
                'jadwal_ids' => $jadwalIds,
                'nama_asli' => $namaGuruAsli,
                'jam_ke' => implode(',', $jamKeDitemukan),
            ],
        ];
    }
}
