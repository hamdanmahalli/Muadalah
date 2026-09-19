<?php

namespace App\Services;

use App\Models\AnggaranKelompok;
use App\Models\AnggaranKebendaharaan;
use App\Models\AnggaranPemasukan;
use App\Models\AnggaranPos;
use App\Models\AnggaranPosBulan;
use App\Models\LaporanPengeluaran;
use App\Models\Pencairan;
use App\Models\PencairanItem;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Impor RAB dari berkas Excel (.xlsx) ke struktur anggaran.
 *
 * Format berkas (contoh file RAB SPMMU):
 *  - Bagian BELANJA: baris POS (kode kelompok kelipatan 100 + kode pos) dengan
 *    kolom POS | URAIAN | Vol | Ket | Vol | Ket | Satuan | Jumlah Harga | ... bulan JULI..JUNI.
 *  - Bagian PEMASUKAN: No | Uraian | Vol | Satuan | ... | Harga Satuan | Jumlah.
 */
class AnggaranImportService
{
    /**
     * Bangun ulang seluruh struktur anggaran dari berkas (kelompok, pos,
     * alokasi bulanan, pemasukan rencana).
     *
     * @throws \RuntimeException bila anggaran final atau sudah dipakai transaksi.
     */
    public function import(AnggaranKebendaharaan $anggaran, $file): array
    {
        if ($anggaran->status === 'final') {
            throw new \RuntimeException('Anggaran berstatus Final. Buka dahulu atau pilih periode lain sebelum meng-upload RAB pengganti.');
        }

        $posIds = $anggaran->pos()->pluck('anggaran_pos.id')->all();
        $dipakai = PencairanItem::whereIn('anggaran_pos_id', $posIds)->exists()
            || Pencairan::whereIn('pos_id', $posIds)->exists()
            || LaporanPengeluaran::whereIn('pos_id', $posIds)->exists();

        if ($dipakai) {
            throw new \RuntimeException('RAB ini sudah dipakai oleh pencairan/LPJ. Tidak bisa diganti lewat upload — edit manual per pos saja.');
        }

        $parsed = $this->parse($file);

        if (count($parsed['kelompok']) === 0 || count($parsed['pos']) === 0) {
            throw new \RuntimeException('Format berkas tidak dikenali (tidak ditemukan kelompok/pos belanja).');
        }

        DB::transaction(function () use ($anggaran, $parsed) {
            // Hapus struktur lama (pemasukan), lalu kelompok (cascade pos & pos_bulan).
            AnggaranPemasukan::where('anggaran_id', $anggaran->id)->delete();
            AnggaranKelompok::where('anggaran_id', $anggaran->id)->delete();

            $urutan = 0;
            $kelompokId = [];
            foreach ($parsed['kelompok'] as $kel) {
                $urutan++;
                $row = AnggaranKelompok::create([
                    'anggaran_id' => $anggaran->id,
                    'kode'        => $kel['kode'],
                    'nama'        => $kel['nama'],
                    'urutan'      => $urutan,
                ]);
                $kelompokId[$kel['kode']] = $row->id;
            }

            foreach ($parsed['pos'] as $p) {
                $pos = AnggaranPos::create([
                    'anggaran_id'  => $anggaran->id,
                    'kelompok_id'  => $kelompokId[$p['kelompok_kode']],
                    'kode'         => $p['kode'],
                    'uraian'       => $p['uraian'],
                    'volume'       => $p['volume'] ?? 1,
                    'satuan'       => $p['satuan'],
                    'volume_2'     => $p['volume_2'],
                    'satuan_2'     => $p['satuan_2'],
                    'harga_satuan' => $p['harga_satuan'],
                    'jumlah'       => $p['jumlah'],
                ]);

                foreach ($p['bulan'] as $bulan => $nominal) {
                    if ($nominal > 0) {
                        AnggaranPosBulan::create([
                            'anggaran_pos_id' => $pos->id,
                            'bulan_fiskal'    => $bulan,
                            'nominal'         => $nominal,
                        ]);
                    }
                }
            }

            $urutanPemasukan = 0;
            foreach ($parsed['pemasukan'] as $pm) {
                AnggaranPemasukan::create([
                    'anggaran_id'  => $anggaran->id,
                    'urutan'       => $urutanPemasukan++,
                    'uraian'       => $pm['uraian'],
                    'volume'       => $pm['volume'] ?? 1,
                    'satuan'       => $pm['satuan'],
                    'harga_satuan' => $pm['harga_satuan'],
                    'jumlah'       => $pm['jumlah'],
                ]);
            }
        });

        return [
            'kelompok'  => count($parsed['kelompok']),
            'pos'       => count($parsed['pos']),
            'pemasukan' => count($parsed['pemasukan']),
            'pagu'      => (float) AnggaranPos::where('anggaran_id', $anggaran->id)->sum('jumlah'),
        ];
    }

    /**
     * Isi alokasi bulanan (anggaran_pos_bulan) untuk pos yang sudah ada,
     * mencocokkan kode pos dari berkas. Tidak mengubah struktur lain.
     */
    public function isiAlokasiBulan(AnggaranKebendaharaan $anggaran, $file): int
    {
        $parsed = $this->parse($file);
        $byKode = AnggaranPos::where('anggaran_id', $anggaran->id)->get()->keyBy('kode');
        $terisi = 0;

        DB::transaction(function () use ($parsed, $byKode, &$terisi) {
            foreach ($parsed['pos'] as $p) {
                $pos = $byKode->get($p['kode']);
                if (!$pos) {
                    continue;
                }
                foreach ($p['bulan'] as $bulan => $nominal) {
                    AnggaranPosBulan::updateOrCreate(
                        ['anggaran_pos_id' => $pos->id, 'bulan_fiskal' => $bulan],
                        ['nominal' => $nominal]
                    );
                    $terisi++;
                }
            }
        });

        return $terisi;
    }

    /**
     * Baca berkas menjadi struktur: kelompok, pos (beserta alokasi 12 bulan), pemasukan.
     */
    public function parse($file): array
    {
        $sheet = IOFactory::load($file->path ?? $file)->getActiveSheet();
        $rows  = $sheet->toArray(null, true, true, false);

        $kelompokSeen  = [];
        $kelompokNama  = [];
        $posList       = [];
        $pemasukanList = [];
        $mode          = 'belanja';

        foreach ($rows as $cells) {
            $cells = array_map(fn ($c) => $c === null ? '' : trim((string) $c), $cells);
            $A = $cells[0] ?? '';
            $B = $cells[1] ?? '';

            if (strtoupper($A) === 'PEMASUKAN') {
                $mode = 'pemasukan';
                continue;
            }

            if ($mode === 'pemasukan') {
                if ($A !== '' && is_numeric($A) && $B !== '') {
                    $pemasukanList[] = [
                        'uraian'       => $B,
                        'volume'       => $this->angka($cells[2] ?? null),
                        'satuan'       => ($cells[3] ?? '') !== '' ? $cells[3] : null,
                        'harga_satuan' => $this->angka($cells[6] ?? null),
                        'jumlah'       => $this->angka($cells[7] ?? null),
                    ];
                }
                continue;
            }

            if ($A === '' || !is_numeric($A)) {
                continue;
            }
            if (strtoupper($B) === 'TOTAL SELURUH') {
                continue;
            }

            $kode = (int) $A;
            $volume = $this->angka($cells[2] ?? null);

            if ($kode % 100 === 0 && $volume === null && $B !== '') {
                if (!in_array($kode, $kelompokSeen, true)) {
                    $kelompokSeen[] = $kode;
                }
                $kelompokNama[$kode] = $B;
                continue;
            }

            if ($kode % 100 !== 0 && $B !== '') {
                $bulan = [];
                for ($i = 0; $i < 12; $i++) {
                    $bulan[$i + 1] = (float) ($this->angka($cells[9 + $i] ?? null) ?? 0);
                }
                $posList[] = [
                    'kode'          => $kode,
                    'kelompok_kode' => (int) (floor($kode / 100) * 100),
                    'uraian'        => $B,
                    'volume'        => $this->angka($cells[2] ?? null),
                    'satuan'        => ($cells[3] ?? '') !== '' ? $cells[3] : null,
                    'volume_2'      => $this->angka($cells[4] ?? null),
                    'satuan_2'      => ($cells[5] ?? '') !== '' ? $cells[5] : null,
                    'harga_satuan'  => $this->angka($cells[6] ?? null) ?? 0,
                    'jumlah'        => $this->angka($cells[7] ?? null) ?? 0,
                    'bulan'         => $bulan,
                ];
            }
        }

        $kelompok = array_map(
            fn ($k) => ['kode' => $k, 'nama' => $kelompokNama[$k] ?? ('Kelompok ' . $k)],
            array_values(array_unique(array_column($posList, 'kelompok_kode')))
        );

        return [
            'kelompok'  => $kelompok,
            'pos'       => $posList,
            'pemasukan' => $pemasukanList,
        ];
    }

    /**
     * Ubah nilai sel (Rp 28,615,000 / 500,000 / 6) menjadi float. null bila kosong/tidak valid.
     */
    private function angka($value): ?float
    {
        if ($value === null) {
            return null;
        }
        $s = trim((string) $value);
        if ($s === '' || $s === '-') {
            return null;
        }
        $neg = $s[0] === '-';
        if ($neg) {
            $s = substr($s, 1);
        }
        $s = str_replace(['Rp', 'rp', 'RP', '.', ' '], '', $s);
        $s = str_replace(',', '', $s);
        $clean = preg_replace('/[^0-9]/', '', $s);
        if ($clean === '') {
            return null;
        }
        $f = (float) $clean;
        return $neg ? -$f : $f;
    }
}