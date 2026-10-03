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
use Illuminate\Support\Str;
use Tests\TestCase;

class KasUmumTest extends TestCase
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

    private function buatPemasukan(float $jumlah, Carbon $tanggal): Pemasukan
    {
        return Pemasukan::create([
            'periode_id' => $this->periode->id,
            'uraian'     => 'Setoran hasil koperasi',
            'tanggal'    => $tanggal->toDateString(),
            'jumlah'     => $jumlah,
            'created_by' => $this->admin->id,
        ]);
    }

    private function buatSppDibayar(float $nominal, Carbon $dibayarAt, string $jenis = 'rutin'): Pencairan
    {
        $posId = AnggaranPos::value('id');
        $pc = Pencairan::create([
            'kode'           => 'SPP-TEST-' . uniqid(),
            'periode_id'     => $this->periode->id,
            'pos_id'         => null,
            'bulan_fiskal'   => fiskal_bulan($dibayarAt),
            'jenis'          => $jenis,
            'tanggal_aju'    => $dibayarAt->toDateString(),
            'nominal'        => $nominal,
            'keperluan'      => 'Uji kas umum',
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

    public function test_halaman_kas_umum_tampil(): void
    {
        $this->get('/kebendaharaan/kas-umum')
            ->assertOk()
            ->assertSee('Kas Umum / Buku Besar')
            ->assertSee('Semua Bulan')
            ->assertSee('Total Pemasukan')
            ->assertSee('Total Saldo')
            ->assertDontSee('Sisa Panjar');
    }

    public function test_feed_berisi_pemasukan_dan_pencairan_dengan_saldo_berjalan(): void
    {
        // Oktober 2026 (bulan fiskal 4): pemasukan 5k (10-05), SPP 100k dibayar (10-15),
        // belanja 25k disetujui (10-20) yang TIDAK boleh tampil (uang sudah keluar saat pencairan).
        $pc = $this->buatSppDibayar(100000, Carbon::parse('2026-10-15 10:00:00'));
        $this->buatPemasukan(5000, Carbon::parse('2026-10-05'));

        LaporanPengeluaran::create([
            'kode'            => LaporanPengeluaran::nextKode($this->periode->tahun),
            'periode_id'      => $this->periode->id,
            'pos_id'          => null,
            'pencairan_id'    => $pc->id,
            'tanggal'         => '2026-10-20',
            'nominal'         => 25000,
            'keterangan'      => 'Beli ATK bulan ini',
            'status'          => 'disetujui',
            'dibuat_oleh'     => $this->admin->id,
            'divalidasi_oleh' => $this->admin->id,
            'divalidasi_at'   => now(),
        ]);

        $this->get(route('kebendaharaan.kas-umum', ['bulan' => 4, 'tahun' => 2026]))
            ->assertOk()
            ->assertSee('Setoran hasil koperasi')
            ->assertSee($pc->kode)
            ->assertDontSee('Beli ATK bulan ini')
            ->assertSee('Total Pemasukan')
            ->assertSee('Total Pencairan')
            ->assertSee('Rp 5.000')
            ->assertSee('Rp 100.000')
            ->assertSee('Rp -95.000');
    }

    public function test_modal_toko_tampil_sebagai_pencairan(): void
    {
        $pc = $this->buatSppDibayar(200000, Carbon::parse('2026-10-08 10:00:00'), 'modal_toko');

        // Modal toko = pencairan kas bendahara biasa; tidak punya sisa panjar (informasi itu sudah dihapus).
        $this->get(route('kebendaharaan.kas-umum', ['bulan' => 4, 'tahun' => 2026]))
            ->assertOk()
            ->assertSee($pc->kode)
            ->assertSee('Total Pencairan')
            ->assertSee('Rp 200.000')
            ->assertSee('Rp -200.000');
    }

    public function test_filter_bulan_fiskal(): void
    {
        $pcSep = $this->buatSppDibayar(50000, Carbon::parse('2026-09-10 10:00:00'));
        $pcOkt = $this->buatSppDibayar(30000, Carbon::parse('2026-10-10 10:00:00'));

        $this->get(route('kebendaharaan.kas-umum', ['bulan' => 3, 'tahun' => 2026]))
            ->assertOk()
            ->assertSee($pcSep->kode)
            ->assertDontSee($pcOkt->kode);

        $this->get(route('kebendaharaan.kas-umum', ['bulan' => 4, 'tahun' => 2026]))
            ->assertOk()
            ->assertSee($pcOkt->kode)
            ->assertDontSee($pcSep->kode);
    }

    public function test_tanpa_akses_kebendaharaan_ditolak(): void
    {
        $user = User::factory()->create([
            'username' => 'user_' . Str::random(8),
            'status'   => 'Aktif',
            'name'     => 'Tamu Uji',
        ]);

        $this->actingAs($user)
            ->get('/kebendaharaan/kas-umum')
            ->assertForbidden();
    }
}