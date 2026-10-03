<?php

namespace Tests\Feature;

use App\Http\Middleware\MenegakkanSatuDevice;
use App\Models\AnggaranPos;
use App\Models\Pencairan;
use App\Models\PencairanItem;
use App\Models\Periode;
use App\Models\Pinjaman;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Str;
use Tests\TestCase;

class PencairanTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware([VerifyCsrfToken::class, MenegakkanSatuDevice::class]);

        $this->admin = User::find(1);
        $this->admin->forceFill(['active_session_id' => null])->save();
        $this->actingAs($this->admin);
    }

    private function buatSpp(string $status, float $nominal, ?int $posId = null, string $jenis = 'rutin'): Pencairan
    {
        $periode = Periode::where('is_active', true)->firstOrFail();

        $pc = Pencairan::create([
            'kode'           => 'SPP-TEST-' . uniqid(),
            'periode_id'     => $periode->id,
            'pos_id'         => null,
            'bulan_fiskal'   => $jenis === 'rutin' ? 1 : null,
            'jenis'          => $jenis,
            'tanggal_aju'    => now()->toDateString(),
            'nominal'        => $nominal,
            'keperluan'      => 'Uji SPP dua langkah',
            'status'         => $status,
            'diajukan_oleh'  => $this->admin->id,
            'disetujui_oleh' => $status === 'disetujui' || $status === 'dibayar' ? $this->admin->id : null,
            'disetujui_at'   => $status === 'disetujui' || $status === 'dibayar' ? now() : null,
            'dibayar_oleh'   => $status === 'dibayar' ? $this->admin->id : null,
            'dibayar_at'     => $status === 'dibayar' ? now() : null,
        ]);

        if ($jenis === 'rutin' && $posId) {
            PencairanItem::create([
                'pencairan_id'    => $pc->id,
                'anggaran_pos_id' => $posId,
                'bulan_fiskal'    => 1,
                'nominal'         => $nominal,
            ]);
        }

        return $pc->fresh('items');
    }

    public function test_halaman_spp_tampil(): void
    {
        $this->get('/kebendaharaan/pencairan')->assertOk()
            ->assertSee('Surat Permintaan Pembayaran')
            ->assertSee('Lembar SPP')
            ->assertSee('Tambah Baris Pos')
            ->assertSee('Nominal (Rp)');
    }

    public function test_detail_spp_tampil(): void
    {
        $posId = AnggaranPos::value('id');
        $pc = $this->buatSpp('diajukan', 100000, $posId);

        $this->get(route('kebendaharaan.pencairan.show', $pc->id))
            ->assertOk()
            ->assertSee($pc->kode)
            ->assertSee($pc->keperluan)
            ->assertSee('Rincian Pos')
            ->assertSee('Riwayat Status');
    }

    public function test_spp_mengalir_diajukan_disetujui_dibayar(): void
    {
        $posId = AnggaranPos::value('id');
        $pc = $this->buatSpp('diajukan', 100000, $posId);

        $this->post(route('kebendaharaan.pencairan.setujui', $pc->id))
            ->assertRedirect(route('kebendaharaan.pencairan.index'))
            ->assertSessionHas('sukses');

        $disetujui = $pc->fresh();
        $this->assertSame('disetujui', $disetujui->status);
        $this->assertSame($this->admin->id, $disetujui->disetujui_oleh);
        $this->assertNotNull($disetujui->disetujui_at);
        $this->assertNull($disetujui->dibayar_at);

        // SPP disetujui tampil dengan label status pada daftar.
        $this->get(route('kebendaharaan.pencairan.index', ['status' => 'disetujui']))
            ->assertOk()->assertSee('Disetujui')->assertSee($pc->kode);

        $this->post(route('kebendaharaan.pencairan.bayar', $pc->id))
            ->assertRedirect(route('kebendaharaan.pencairan.index'))
            ->assertSessionHas('sukses');

        $dibayar = $disetujui->fresh();
        $this->assertSame('dibayar', $dibayar->status);
        $this->assertNotNull($dibayar->dibayar_at);
        $this->assertNull($dibayar->tolak_alasan);
    }

    public function test_bayar_ditolak_jika_belum_disetujui(): void
    {
        $posId = AnggaranPos::value('id');
        $pc = $this->buatSpp('diajukan', 100000, $posId);

        $this->post(route('kebendaharaan.pencairan.bayar', $pc->id))
            ->assertRedirect(route('kebendaharaan.pencairan.index'))
            ->assertSessionHas('error');

        $this->assertSame('diajukan', $pc->fresh()->status);
    }

    public function test_setujui_ditolak_jika_bukan_diajukan(): void
    {
        $pc = $this->buatSpp('dibayar', 100000);

        $this->post(route('kebendaharaan.pencairan.setujui', $pc->id))
            ->assertRedirect(route('kebendaharaan.pencairan.index'))
            ->assertSessionHas('error');

        $this->assertSame('dibayar', $pc->fresh()->status);
    }

    public function test_modal_toko_jadi_pinjaman_saat_dibayar(): void
    {
        $pc = $this->buatSpp('diajukan', 250000, null, 'modal_toko');

        $this->post(route('kebendaharaan.pencairan.setujui', $pc->id))->assertSessionHas('sukses');
        $this->post(route('kebendaharaan.pencairan.bayar', $pc->id))->assertSessionHas('sukses');

        $pinjaman = Pinjaman::where('pencairan_id', $pc->id)->first();
        $this->assertNotNull($pinjaman);
        $this->assertSame('aktif', $pinjaman->status);
        $this->assertSame(250000.0, (float) $pinjaman->jumlah);
        $this->assertSame($this->admin->id, $pinjaman->peminjam_user_id);
    }

    public function test_blokir_buku_kas_terbuka_hanya_untuk_pengajunya(): void
    {
        $posId = AnggaranPos::value('id');

        // Admin punya buku terbuka (SPP rutin dibayar tanpa tutup buku).
        $this->buatSpp('dibayar', 100000, $posId);

        // Pengaju lain tidak terblokir oleh buku terbuka milik admin.
        $lain = User::factory()->create([
            'username' => 'pengaju_' . strtolower(Str::random(8)),
            'status'   => 'Aktif',
            'name'     => 'Pengaju Lain',
        ]);
        $lain->givePermissionTo('akses_pencairan');
        $this->actingAs($lain);

        $this->post('/kebendaharaan/pencairan', [
            'jenis'       => 'rutin',
            'tanggal_aju' => now()->toDateString(),
            'keperluan'   => 'SPP milik pengaju lain',
            'nominal'     => '75000',
            'bulan_fiskal'=> 1,
            'items'       => [['pos_id' => $posId, 'nominal' => '75000']],
        ])->assertRedirect(route('kebendaharaan.pencairan.index'));

        $this->assertTrue(Pencairan::where('keperluan', 'SPP milik pengaju lain')->exists());

        // Admin sendiri tetap diblokir oleh buku kas miliknya.
        $this->actingAs($this->admin);
        $this->post('/kebendaharaan/pencairan', [
            'jenis'       => 'rutin',
            'tanggal_aju' => now()->toDateString(),
            'keperluan'   => 'SPP admin diblokir',
            'nominal'     => '50000',
            'bulan_fiskal'=> 1,
            'items'       => [['pos_id' => $posId, 'nominal' => '50000']],
        ])->assertSessionHas('error', fn ($m) => str_contains($m, 'belum diselesaikan'));

        $this->assertFalse(Pencairan::where('keperluan', 'SPP admin diblokir')->exists());
    }

    public function test_spp_rutin_diblokir_selama_buku_kas_terbuka(): void
    {
        // Satu SPP rutin dibayar tanpa buku tertutup = buku kas aktif terbuka.
        $posId = AnggaranPos::value('id');
        $aktif = $this->buatSpp('dibayar', 100000, $posId);

        // SPP rutin baru ditolak selama laporan SPP aktif belum diselesaikan.
        $this->post('/kebendaharaan/pencairan', [
            'jenis'       => 'rutin',
            'tanggal_aju' => now()->toDateString(),
            'keperluan'   => 'SPP diblokir',
            'nominal'     => '50000',
            'bulan_fiskal'=> 1,
            'items'       => [['pos_id' => $posId, 'nominal' => '50000']],
        ])->assertSessionHas('error', fn ($m) => str_contains($m, 'belum diselesaikan'));

        $this->assertFalse(Pencairan::where('keperluan', 'SPP diblokir')->exists());

        // Laporkan buku kas SPP aktif -> SPP berikutnya boleh diajukan.
        $this->post('/kebendaharaan/laporan/laporkan')
            ->assertRedirect(route('kebendaharaan.laporan.index'))
            ->assertSessionHas('sukses');
        $this->assertNotNull($aktif->fresh()->bukuKas);

        // Bersihkan sisa SPP rutin terbuka lain agar keadaan deterministik.
        Pencairan::where('jenis', 'rutin')->whereDoesntHave('bukuKas')->delete();

        $this->post('/kebendaharaan/pencairan', [
            'jenis'       => 'rutin',
            'tanggal_aju' => now()->toDateString(),
            'keperluan'   => 'SPP setelah tutup',
            'nominal'     => '75000',
            'bulan_fiskal'=> 1,
            'items'       => [['pos_id' => $posId, 'nominal' => '75000']],
        ])->assertSessionHas('sukses');

        $this->assertTrue(Pencairan::where('keperluan', 'SPP setelah tutup')->exists());
    }
}