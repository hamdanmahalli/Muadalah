<?php

namespace App\Services;

use App\Models\LaporanPengeluaran;
use App\Models\Pemasukan;
use App\Models\Pencairan;
use App\Models\User;
use Illuminate\Support\Collection;

class KasPemegangService
{
    /**
     * Ringkasan kas per pemegang (uang dipegang tiap orang).
     *
     * Aturan atribusi:
     *  - SPP yang SUDAH DIBAYAR  -> masuk ke kas pengaju (pencairan.diajukan_oleh),
     *    tanggal dipakai = dibayar_at.
     *  - Belanja (LPJ disetujui) -> keluar dari kas pengaju SPP-nya; bila LPJ tanpa SPP,
     *    keluar dari kas pembuat catatan (dibuat_oleh), tanggal = tanggal belanja.
     *  - Pemasukan manual       -> masuk ke kas pencatat (created_by).
     *
     * Saldo = total masuk - total keluar. Jumlah seluruh pemegang = saldo buku kas
     * (uang masuk + pemasukan manual - uang keluar), sehingga selalu seimbang.
     *
     * @param $periode Periode aktif
     * @param int|null $bulan bulan fiskal (1..12); null = seluruh tahun fiskal
     * @param int|null $tahun tahun fiskal; null = tahun periode
     */
    public function ringkas($periode, ?int $bulan = null, ?int $tahun = null): array
    {
        $tahun = $tahun ?? (int) $periode->tahun;

        $baris = $this->barisTransaksi($periode, $bulan, $tahun);

        $pemegang = $this->daftarPemegang($baris);

        $rinci = $pemegang->map(function ($user) use ($baris) {
            $rows = $baris->where('pemegang', (int) $user->id)->values()->sortBy('tanggal');

            $saldo = 0.0;
            $rincian = $rows->map(function ($r) use (&$saldo) {
                $saldo += $r['masuk'] - $r['keluar'];
                return $r + ['saldo' => $saldo];
            })->values();

            return [
                'user'    => $user,
                'masuk'   => (float) $rows->sum('masuk'),
                'keluar'  => (float) $rows->sum('keluar'),
                'saldo'   => $saldo,
                'rincian' => $rincian,
            ];
        });

        return [
            'pemegang' => $rinci->values(),
            'total'    => [
                'masuk'  => (float) $baris->sum('masuk'),
                'keluar' => (float) $baris->sum('keluar'),
                'saldo'  => (float) $baris->sum('masuk') - (float) $baris->sum('keluar'),
            ],
            'periode' => $periode,
            'bulan'   => $bulan,
            'tahun'   => $tahun,
        ];
    }

    /**
     * Kas Umum / buku besar bendahara: seluruh pemasukan dan pengeluaran
     * KAS BENDAHARA dalam satu feed kronologis lengkap dengan saldo berjalan.
     *
     * Semua uang bermula di kas bendahara; pemasukan dari siapa pun dilaporkan
     * masuk catatan bendahara dahulu, lalu dikeluarkan saat dicairkan.
     *
     * Aturan transaksi:
     *  - SEMUA Pemasukan (manual + otomatis setoran toko) = masuk, tanggal =
     *    tanggal pemasukan; pihak = pencatat (created_by).
     *  - SEMUA Pencairan berstatus dibayar (rutin & modal toko) = keluar,
     *    tanggal = dibayar_at; pihak = pengaju (diajukan_oleh).
     *  - Realisasi belanja (LPJ disetujui) TIDAK dicatat di kas umum karena
     *    uangnya sudah keluar saat pencairan SPP (dicegah double-count);
     *    pengaju tetap terpantau lewat ringkas() / Kas per Pemegang.
     *  - Sisa panjar yang tidak terpakai otomatis DICATAT sebagai Pemasukan
     *    "Pengembalian" yang terkait ke SPP (pencairan_id) saat laporan
     *    disahkan pimpinan; jadi masuk kembali ke kas umum pada waktunya.
     *
     * Saldo = pemasukan - pencairan = sisa kas bendahara (bisa negatif).
     * Total buku ini TIDAK sama dengan total Kas per Pemegang.
     *
     * @param $periode Periode aktif
     * @param int|null $bulan bulan fiskal (1..12); null = seluruh tahun fiskal
     * @param int|null $tahun tahun fiskal; null = tahun periode
     */
    public function kasUmum($periode, ?int $bulan = null, ?int $tahun = null): array
    {
        $tahun = $tahun ?? (int) $periode->tahun;

        if ($bulan !== null) {
            [$mulai, $sampai] = fiskal_range($bulan, $tahun);
        } else {
            $mulai = fiskal_range(1, $tahun)[0];
            $sampai = fiskal_range(12, $tahun)[1];
        }

        $pemasukan = Pemasukan::with('pencairan')
            ->where('periode_id', $periode->id)
            ->whereBetween('tanggal', [$mulai, $sampai])
            ->get();

        $pencairan = Pencairan::with('items')
            ->where('periode_id', $periode->id)
            ->where('status', 'dibayar')
            ->whereNotNull('dibayar_at')
            ->whereBetween('dibayar_at', [$mulai, $sampai])
            ->get();

        $baris = collect();

        foreach ($pemasukan as $m) {
            $pencairanAsal = $m->pencairan;

            $baris->push([
                'pihak'   => $pencairanAsal ? (int) ($pencairanAsal->diajukan_oleh ?: 0) : (int) ($m->created_by ?: 0),
                'tanggal' => $m->tanggal?->toDateString() ?? '',
                'sumber'  => $pencairanAsal ? 'Pengembalian' : 'Pemasukan',
                'kode'    => $pencairanAsal ? $pencairanAsal->kode : null,
                'uraian'  => $m->uraian,
                'masuk'   => (float) $m->jumlah,
                'keluar'  => 0.0,
            ]);
        }

        foreach ($pencairan as $p) {
            $baris->push([
                'pihak'   => (int) ($p->diajukan_oleh ?: 0),
                'tanggal' => $p->dibayar_at?->toDateString() ?? '',
                'sumber'  => 'Pencairan',
                'kode'    => $p->kode,
                'uraian'  => $p->keperluan ?? 'Pencairan',
                'masuk'   => 0.0,
                'keluar'  => (float) $p->jumlah,
            ]);
        }

        $nama = User::whereIn('id', $baris->pluck('pihak')->filter()->unique()->all())
            ->get()
            ->keyBy('id');

        $tersortir = $baris->sortBy([
            fn ($r) => (string) $r['tanggal'],
            fn ($r) => (string) $r['sumber'],
            fn ($r) => (string) $r['kode'],
        ])->values();

        $saldo = 0.0;
        $barisBuku = $tersortir->map(function ($r) use (&$saldo, $nama) {
            $user = $nama->get((int) $r['pihak']);
            $saldo += (float) $r['masuk'] - (float) $r['keluar'];

            return $r + [
                'pihak_nama' => $user?->name ?? 'Pengguna tidak aktif',
                'saldo'      => $saldo,
            ];
        })->values();

        return [
            'baris'   => $barisBuku,
            'total'   => [
                'masuk'  => (float) $baris->sum('masuk'),
                'keluar' => (float) $baris->sum('keluar'),
                'saldo'  => (float) $baris->sum('masuk') - (float) $baris->sum('keluar'),
            ],
            'periode' => $periode,
            'bulan'   => $bulan,
            'tahun'   => $tahun,
        ];
    }

    /**
     * Baris transaksi mentah (SPP dibayar, belanja disetujui, pemasukan manual)
     * dalam jendela fiskal periode/filter yang diminta. Dipakai bersama oleh
     * ringkas() (per pemegang) dan bukuBesar() (agregat semua).
     *
     * @return Collection<int, array{pemegang:int, tanggal:string, sumber:string, kode:?string, uraian:string, masuk:float, keluar:float}>
     */
    private function barisTransaksi($periode, ?int $bulan = null, ?int $tahun = null): Collection
    {
        $tahun = $tahun ?? (int) $periode->tahun;

        if ($bulan !== null) {
            [$mulai, $sampai] = fiskal_range($bulan, $tahun);
        } else {
            $mulai = fiskal_range(1, $tahun)[0];
            $sampai = fiskal_range(12, $tahun)[1];
        }

        $spp = Pencairan::with('items')
            ->where('periode_id', $periode->id)
            ->where('status', 'dibayar')
            ->whereNotNull('dibayar_at')
            ->whereBetween('dibayar_at', [$mulai, $sampai])
            ->get();

        $belanja = LaporanPengeluaran::with('pencairan')
            ->where('periode_id', $periode->id)
            ->where('status', 'disetujui')
            ->whereBetween('tanggal', [$mulai, $sampai])
            ->get();

        $pemasukan = Pemasukan::where('periode_id', $periode->id)
            ->whereBetween('tanggal', [$mulai, $sampai])
            ->get();

        $baris = collect();

        foreach ($spp as $p) {
            $pemegang = (int) ($p->diajukan_oleh ?: 0);
            if ($pemegang <= 0) {
                continue;
            }
            $baris->push([
                'pemegang' => $pemegang,
                'tanggal'  => $p->dibayar_at?->toDateString() ?? '',
                'sumber'   => 'SPP',
                'kode'     => $p->kode,
                'uraian'   => $p->keperluan ?? 'Pencairan',
                'masuk'    => (float) $p->jumlah,
                'keluar'   => 0.0,
            ]);
        }

        foreach ($belanja as $l) {
            $pemegang = $l->pencairan?->diajukan_oleh
                ? (int) $l->pencairan->diajukan_oleh
                : (int) ($l->dibuat_oleh ?: 0);
            if ($pemegang <= 0) {
                continue;
            }

            $baris->push([
                'pemegang' => $pemegang,
                'tanggal'  => $l->tanggal?->toDateString() ?? '',
                'sumber'   => 'Belanja',
                'kode'     => $l->kode,
                'uraian'   => $l->keterangan ?? 'Belanja',
                'masuk'    => 0.0,
                'keluar'   => (float) $l->nominal,
            ]);
        }

        foreach ($pemasukan as $m) {
            $pemegang = (int) ($m->created_by ?: 0);
            if ($pemegang <= 0) {
                continue;
            }

            $baris->push([
                'pemegang' => $pemegang,
                'tanggal'  => $m->tanggal?->toDateString() ?? '',
                'sumber'   => 'Pemasukan',
                'kode'     => null,
                'uraian'   => $m->uraian,
                'masuk'    => (float) $m->jumlah,
                'keluar'   => 0.0,
            ]);
        }

        return $baris;
    }

    /**
     * Semua user pemegang kas: yang berizin akses_kebendaharaan ditambah
     * siapa pun yang muncul di transaksi (agar total tetap seimbang).
     */
    private function daftarPemegang(Collection $baris): Collection
    {
        $dariIzin = User::permission('akses_kebendaharaan')->get()->keyBy('id');
        $dariTransaksi = User::whereIn('id', $baris->pluck('pemegang')->filter()->unique()->all())
            ->get()
            ->keyBy('id');

        return $dariTransaksi->merge($dariIzin)
            ->sortBy('name')
            ->values();
    }
}