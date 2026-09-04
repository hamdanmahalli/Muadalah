<?php

namespace App\Services\Kehadiran;

use App\Models\AgendaKegiatan;
use App\Models\Guru;
use App\Models\KehadiranKegiatan;
use Carbon\Carbon;

/**
 * Penyusun data kehadiran kegiatan HRIS.
 *
 * SRP: Satu tanggung jawab — merangkum data kehadiran (hadir/belum hadir)
 * dan memproses pencatatan kehadiran kegiatan. Stateless.
 */
class AgendaKehadiranService
{
    /**
     * Rangkum data hadir vs belum hadir untuk sebuah agenda (versi objek untuk view).
     *
     * @return array{data_hadir: array, data_belum_hadir: array, total_guru: int}
     */
    public function rangkumUntukView(int $agendaId): array
    {
        $semuaGuru = Guru::orderBy('nama_guru', 'asc')->get();
        $kehadiran = KehadiranKegiatan::where('agenda_kegiatan_id', $agendaId)->get()->keyBy('guru_id');

        $dataHadir = [];
        $dataBelumHadir = [];

        foreach ($semuaGuru as $guru) {
            if ($kehadiran->has($guru->id)) {
                $dataHadir[] = (object)[
                    'guru' => $guru,
                    'waktu_hadir' => $kehadiran[$guru->id]->waktu_hadir,
                    'metode' => $kehadiran[$guru->id]->metode,
                    'status' => $kehadiran[$guru->id]->status ?? 'Hadir',
                    'keterangan' => $kehadiran[$guru->id]->keterangan,
                ];
            } else {
                $dataBelumHadir[] = $guru;
            }
        }

        usort($dataHadir, function ($a, $b) {
            return strtotime($a->waktu_hadir) - strtotime($b->waktu_hadir);
        });

        return [
            'data_hadir' => $dataHadir,
            'data_belum_hadir' => $dataBelumHadir,
            'total_guru' => $semuaGuru->count(),
        ];
    }

    /**
     * Rangkum data hadir vs belum hadir untuk JSON real-time (versi array).
     */
    public function rangkumUntukApi(int $agendaId): array
    {
        $semuaGuru = Guru::orderBy('nama_guru', 'asc')->get();
        $kehadiran = KehadiranKegiatan::with('guru')->where('agenda_kegiatan_id', $agendaId)->get()->keyBy('guru_id');

        $dataHadir = [];
        $dataBelumHadir = [];

        foreach ($semuaGuru as $guru) {
            if ($kehadiran->has($guru->id)) {
                $dataHadir[] = [
                    'guru_id' => $guru->id,
                    'nama_guru' => $guru->nama_guru,
                    'waktu' => Carbon::parse($kehadiran[$guru->id]->waktu_hadir)->format('Y-m-d H:i:s'),
                    'metode' => $kehadiran[$guru->id]->metode,
                    'status' => $kehadiran[$guru->id]->status ?? 'Hadir',
                    'keterangan' => $kehadiran[$guru->id]->keterangan,
                ];
            } else {
                $dataBelumHadir[] = [
                    'id' => $guru->id,
                    'nama_guru' => $guru->nama_guru,
                ];
            }
        }

        usort($dataHadir, function ($a, $b) {
            return strtotime($a['waktu']) - strtotime($b['waktu']);
        });

        return [
            'total_hadir' => count($dataHadir),
            'total_belum' => count($dataBelumHadir),
            'persentase' => $semuaGuru->count() > 0 ? round((count($dataHadir) / $semuaGuru->count()) * 100) : 0,
            'data_hadir' => $dataHadir,
            'data_belum' => $dataBelumHadir,
        ];
    }

    /**
     * Catat kehadiran manual oleh admin/TU.
     */
    public function catatManual(int $agendaId, int $guruId, string $status, ?string $keterangan): void
    {
        KehadiranKegiatan::updateOrCreate(
            ['agenda_kegiatan_id' => $agendaId, 'guru_id' => $guruId],
            [
                'waktu_hadir' => now(),
                'metode' => 'Manual Admin',
                'status' => $status,
                'keterangan' => $keterangan,
            ]
        );
    }

    /**
     * Proses hasil pindai QR guru (GURU-<NIG>) untuk sebuah agenda.
     *
     * @return array{status: string, pesan: string}
     *
     * @throws \App\Models\ModelNotFoundException bila agenda tidak ada
     */
    public function prosesScanQr(string $qrData, int $agendaId): array
    {
        $agenda = AgendaKegiatan::findOrFail($agendaId);

        if (!$agenda->is_open) {
            return ['status' => 'error', 'pesan' => 'Absensi untuk kegiatan ini sudah ditutup oleh TU.'];
        }

        if (strpos($qrData, 'GURU-') !== 0) {
            return ['status' => 'error', 'pesan' => 'QR tidak dikenali! Pindai QR Code pribadi guru.'];
        }

        $nig = substr($qrData, 5);
        if ($nig === '') {
            return ['status' => 'error', 'pesan' => 'QR guru tidak valid (NIG kosong).'];
        }

        $guru = Guru::where('nig', $nig)->first();
        if (!$guru) {
            return ['status' => 'error', 'pesan' => 'Guru dengan NIG ' . $nig . ' tidak ditemukan.'];
        }

        $kehadiran = KehadiranKegiatan::firstOrNew([
            'agenda_kegiatan_id' => $agenda->id,
            'guru_id' => $guru->id,
        ]);

        if ($kehadiran->exists) {
            return ['status' => 'info', 'pesan' => $guru->nama_guru . ' sudah tercatat hadir sebelumnya.'];
        }

        $kehadiran->waktu_hadir = now();
        $kehadiran->metode = 'Scan QR Guru';
        $kehadiran->status = 'Hadir';
        $kehadiran->keterangan = null;
        $kehadiran->save();

        return ['status' => 'success', 'pesan' => $guru->nama_guru . ' (NIG ' . $guru->nig . ') tercatat hadir di ' . $agenda->nama_kegiatan . '!'];
    }
}
