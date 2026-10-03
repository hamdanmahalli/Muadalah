<?php

namespace Tests\Feature;

use App\Http\Middleware\MenegakkanSatuDevice;
use App\Models\AnggaranPos;
use App\Models\LaporanPengeluaran;
use App\Models\Pemasukan;
use App\Models\Pencairan;
use App\Models\PencairanItem;
use App\Models\Periode;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class KasPemegangTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;
    private Periode $periode;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware([VerifyCsrfToken::class, MenegakkanSatuDevice::class]);

        $this->admin = User::find(1);
        $this->admin->forceFill(['active_session_id' => null])->save();
        $this->actingAs($this->admin);

        $this->periode = Periode::where('is_active', true)->firstOrFail();
    }

    private function buatSppDibayar(float $nominal, Carbon $dibayarAt): Pencairan
    {
        $posId = AnggaranPos::value('id');
        $pc = Pencairan::create([
            'kode'           => 'SPP-TEST-' . uniqid(),
            'periode_id'     => $this->periode->id,
            'pos_id'         => null,
            'bulan_fiskal'   => fiskal_bulan($dibayarAt),
            'jenis'          => 'rutin',
            'tanggal_aju'    => $dibayarAt->toDateString(),
            'nominal'        => $nominal,
            'keperluan'      => 'Uji kas pemegang',
            'status'         => 'dibayar',
            'diajukan_oleh'  => $this->admin->id,
            'disetujui_oleh' => $this->admin->id,
            'disetujui_at'   => $dibayarAt->subMinutes(30),
            'dibayar_oleh'   => $this->admin->id,
            'dibayar_at'     => $dibayarAt,
        ]);

        PencairanItem::create([
            'pencairan_id'    => $pc->id,
            'anggaran_pos_id' => $posId,
            'bulan_fiskal'    => fiskal_bulan($dibayarAt),
            'nominal'         => $nominal,
        ]);

        return $pc->fresh('items');
    }

    public function test_halaman_kas_pemegang_tampil(): void
    {
        $this->get('/kebendaharaan/kas-pemegang')
            ->assertOk()
            ->assertSee('Kas per Pemegang')
            ->assertSee('Semua Bulan')
            ->assertSee('Total Saldo Dipegang');
    }

    public function test_saldo_mengikuti_pengaju_spp_dan_belanja(): void
    {
        // Oktober 2026 (bulan fiskal 4) bersih dari data dev, sehingga saldo pasti.
        $pc = $this->buatSppDibayar(100000, Carbon::parse('2026-10-15 10:00:00'));

        LaporanPengeluaran::create([
            'kode'          => LaporanPengeluaran::nextKode($this->periode->tahun),
            'periode_id'    => $this->periode->id,
            'pos_id'        => null,
            'pencairan_id'  => $pc->id,
            'tanggal'       => '2026-10-20',
            'nominal'       => 25000,
            'keterangan'    => 'Beli ATK bulan ini',
            'status'        => 'disetujui',
            'dibuat_oleh'   => $this->admin->id,
            'divalidasi_oleh' => $this->admin->id,
            'divalidasi_at' => now(),
        ]);

        $this->get(route('kebendaharaan.kas-pemegang', ['bulan' => 4, 'tahun' => 2026]))
            ->assertOk()
            ->assertSee($this->admin->name)
            ->assertSee('Rp 100.000')
            ->assertSee('Rp 25.000')
            ->assertSee('Rp 75.000')
            ->assertSee('Beli ATK bulan ini');
    }

    public function test_pemasukan_manual_masuk_kas_pencatat(): void
    {
        Pemasukan::create([
            'periode_id' => $this->periode->id,
            'uraian'     => 'Setoran hasil koperasi',
            'tanggal'    => '2026-10-05',
            'jumlah'     => 5000,
            'created_by' => $this->admin->id,
        ]);

        $this->get(route('kebendaharaan.kas-pemegang', ['bulan' => 4, 'tahun' => 2026]))
            ->assertOk()
            ->assertSee($this->admin->name)
            ->assertSee('Setoran hasil koperasi')
            ->assertSee('Rp 5.000');
    }

    public function test_filter_bulan_fiskal(): void
    {
        $pcSep = $this->buatSppDibayar(50000, Carbon::parse('2026-09-10 10:00:00'));
        $pcOkt = $this->buatSppDibayar(30000, Carbon::parse('2026-10-10 10:00:00'));

        $this->get(route('kebendaharaan.kas-pemegang', ['bulan' => 3, 'tahun' => 2026]))
            ->assertOk()
            ->assertSee($pcSep->kode)
            ->assertDontSee($pcOkt->kode);

        $this->get(route('kebendaharaan.kas-pemegang', ['bulan' => 4, 'tahun' => 2026]))
            ->assertOk()
            ->assertSee($pcOkt->kode)
            ->assertDontSee($pcSep->kode);
    }
}