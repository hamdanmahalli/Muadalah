<?php

namespace Tests\Feature;

use App\Http\Middleware\MenegakkanSatuDevice;
use App\Models\AnggaranKebendaharaan;
use App\Models\AnggaranKelompok;
use App\Models\AnggaranPemasukan;
use App\Models\AnggaranPos;
use App\Models\AnggaranPosBulan;
use App\Models\LaporanPengeluaran;
use App\Models\LaporanPengeluaranItem;
use App\Models\Pencairan;
use App\Models\PencairanItem;
use App\Models\Periode;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Tests\TestCase;

class AnggaranTest extends TestCase
{
    use DatabaseTransactions;

    private Periode $periode;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware([VerifyCsrfToken::class, MenegakkanSatuDevice::class]);
        $this->actingAs(User::find(1));
        $this->periode = Periode::where('is_active', true)->firstOrFail();
    }

    private function buatAnggaran(string $nama = 'RAB Uji'): AnggaranKebendaharaan
    {
        return AnggaranKebendaharaan::create([
            'periode_id'   => $this->periode->id,
            'nama'         => $nama,
            'tahun_ajaran' => $this->periode->tahun_ajaran,
            'status'       => 'draft',
            'created_by'   => 1,
        ]);
    }

    private function buatKelompok(AnggaranKebendaharaan $anggaran, int $kode = 100): AnggaranKelompok
    {
        return AnggaranKelompok::create([
            'anggaran_id' => $anggaran->id,
            'kode'        => $kode,
            'nama'        => 'Kelompok ' . $kode,
            'urutan'      => 1,
        ]);
    }

    private function buatPos(AnggaranKebendaharaan $anggaran, AnggaranKelompok $kelompok, float $jumlah, int $kode = 101): AnggaranPos
    {
        return AnggaranPos::create([
            'anggaran_id'  => $anggaran->id,
            'kelompok_id'  => $kelompok->id,
            'kode'         => $kode,
            'uraian'       => 'Pos ' . $kode,
            'volume'       => 1,
            'harga_satuan' => $jumlah,
            'jumlah'       => $jumlah,
        ]);
    }

    private function seimbangkan(AnggaranPos $pos): void
    {
        AnggaranPosBulan::create([
            'anggaran_pos_id' => $pos->id,
            'bulan_fiskal'    => 1,
            'nominal'         => (float) $pos->jumlah,
        ]);
    }

    private function buatSppItem(AnggaranPos $pos): PencairanItem
    {
        $pc = Pencairan::create([
            'kode'        => 'SPP-T-' . uniqid(),
            'periode_id'  => $this->periode->id,
            'pos_id'      => null,
            'jenis'       => 'rutin',
            'tanggal_aju' => now()->toDateString(),
            'nominal'     => 1000,
            'status'      => 'diajukan',
        ]);

        return PencairanItem::create([
            'pencairan_id'    => $pc->id,
            'anggaran_pos_id' => $pos->id,
            'bulan_fiskal'    => 1,
            'nominal'         => 1000,
        ]);
    }

    private function buatLpjItem(AnggaranPos $pos): LaporanPengeluaranItem
    {
        $lp = LaporanPengeluaran::create([
            'kode'       => LaporanPengeluaran::nextKode($this->periode->tahun),
            'periode_id' => $this->periode->id,
            'tanggal'    => now()->toDateString(),
            'nominal'    => 1000,
            'status'     => 'disetujui',
        ]);

        return LaporanPengeluaranItem::create([
            'laporan_pengeluaran_id' => $lp->id,
            'anggaran_pos_id'        => $pos->id,
            'nominal'                => 1000,
        ]);
    }

    public function test_pos_yang_dipakai_spp_tidak_bisa_dihapus(): void
    {
        $anggaran = $this->buatAnggaran();
        $kelompok = $this->buatKelompok($anggaran);
        $pos = $this->buatPos($anggaran, $kelompok, 100000);
        $this->buatSppItem($pos);

        $this->delete(route('kebendaharaan.anggaran.pos.destroy', $pos->id))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('anggaran_pos', ['id' => $pos->id]);
        $this->assertDatabaseHas('pencairan_item', ['anggaran_pos_id' => $pos->id]);
    }

    public function test_pos_yang_dipakai_lpj_tidak_bisa_dihapus(): void
    {
        $anggaran = $this->buatAnggaran();
        $kelompok = $this->buatKelompok($anggaran);
        $pos = $this->buatPos($anggaran, $kelompok, 100000);
        $this->buatLpjItem($pos);

        $this->delete(route('kebendaharaan.anggaran.pos.destroy', $pos->id))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('anggaran_pos', ['id' => $pos->id]);
        $this->assertDatabaseHas('laporan_pengeluaran_item', ['anggaran_pos_id' => $pos->id]);
    }

    public function test_pos_tanpa_pemakaian_bisa_dihapus(): void
    {
        $anggaran = $this->buatAnggaran();
        $kelompok = $this->buatKelompok($anggaran);
        $pos = $this->buatPos($anggaran, $kelompok, 100000);

        $this->delete(route('kebendaharaan.anggaran.pos.destroy', $pos->id))
            ->assertSessionHas('sukses');

        $this->assertDatabaseMissing('anggaran_pos', ['id' => $pos->id]);
    }

    public function test_kelompok_dengan_pos_tidak_bisa_dihapus(): void
    {
        $anggaran = $this->buatAnggaran();
        $kelompok = $this->buatKelompok($anggaran);
        $this->buatPos($anggaran, $kelompok, 50000);

        $this->delete(route('kebendaharaan.anggaran.kelompok.destroy', $kelompok->id))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('anggaran_kelompok', ['id' => $kelompok->id]);
        $this->assertDatabaseHas('anggaran_pos', ['kelompok_id' => $kelompok->id]);
    }

    public function test_kelompok_kosong_bisa_dihapus(): void
    {
        $anggaran = $this->buatAnggaran();
        $kelompok = $this->buatKelompok($anggaran);

        $this->delete(route('kebendaharaan.anggaran.kelompok.destroy', $kelompok->id))
            ->assertSessionHas('sukses');

        $this->assertDatabaseMissing('anggaran_kelompok', ['id' => $kelompok->id]);
    }

    public function test_pos_tidak_bisa_pakai_kelompok_anggaran_lain(): void
    {
        $a = $this->buatAnggaran('RAB A');
        $kelompokA = $this->buatKelompok($a);
        $b = $this->buatAnggaran('RAB B');
        $kelompokB = $this->buatKelompok($b);

        $this->post(route('kebendaharaan.anggaran.pos.store', $a->id), [
            'kelompok_id'  => $kelompokB->id,
            'kode'         => 101,
            'uraian'       => 'Nyasar',
            'volume'       => 1,
            'harga_satuan' => 10000,
        ])->assertSessionHas('error');

        $this->assertDatabaseMissing('anggaran_pos', ['anggaran_id' => $a->id, 'uraian' => 'Nyasar']);
    }

    public function test_halaman_detail_menampilkan_badge_defisit(): void
    {
        $anggaran = $this->buatAnggaran();
        $kelompok = $this->buatKelompok($anggaran);
        $pos = $this->buatPos($anggaran, $kelompok, 100000);
        $this->seimbangkan($pos);

        $this->get(route('kebendaharaan.anggaran.show', $anggaran->id))
            ->assertOk()
            ->assertSee('Defisit');
    }

    public function test_finalisasi_dengan_defisit_tetap_final_tapi_peringatan(): void
    {
        $anggaran = $this->buatAnggaran();
        $kelompok = $this->buatKelompok($anggaran);
        $pos = $this->buatPos($anggaran, $kelompok, 100000);
        $this->seimbangkan($pos);

        $this->post(route('kebendaharaan.anggaran.final', $anggaran->id))
            ->assertSessionHas('warning');

        $this->assertSame('final', $anggaran->fresh()->status);
    }

    public function test_finalisasi_seimbang_tanpa_peringatan(): void
    {
        $anggaran = $this->buatAnggaran();
        $kelompok = $this->buatKelompok($anggaran);
        $pos = $this->buatPos($anggaran, $kelompok, 100000);
        $this->seimbangkan($pos);
        AnggaranPemasukan::create([
            'anggaran_id'  => $anggaran->id,
            'urutan'       => 1,
            'uraian'       => 'BOS',
            'volume'       => 1,
            'harga_satuan' => 100000,
            'jumlah'       => 100000,
        ]);

        $this->post(route('kebendaharaan.anggaran.final', $anggaran->id))
            ->assertSessionHas('sukses')
            ->assertSessionMissing('warning');

        $this->assertSame('final', $anggaran->fresh()->status);
    }
}
