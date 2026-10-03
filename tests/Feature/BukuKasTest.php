<?php

namespace Tests\Feature;

use App\Http\Middleware\MenegakkanSatuDevice;
use App\Models\AnggaranPos;
use App\Models\BukuKasBulanan;
use App\Models\LaporanPengeluaran;
use App\Models\Pemasukan;
use App\Models\Pencairan;
use App\Models\PencairanItem;
use App\Models\Periode;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class BukuKasTest extends TestCase
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

        // Tes mengasumsikan bulan berjalan masih terbuka; buka paksa di dalam transaksi (di-rollback).
        $periode = Periode::where('is_active', true)->first();
        if ($periode) {
            BukuKasBulanan::where('periode_id', $periode->id)->delete();
        }
    }

    private function buatSpp(string $status, float $nominal, int $posId, int $bulan = 1): Pencairan
    {
        $periode = Periode::where('is_active', true)->firstOrFail();

        $pc = Pencairan::create([
            'kode'           => 'SPP-TEST-' . uniqid(),
            'periode_id'     => $periode->id,
            'pos_id'         => null,
            'bulan_fiskal'   => $bulan,
            'jenis'          => 'rutin',
            'tanggal_aju'    => now()->toDateString(),
            'nominal'        => $nominal,
            'keperluan'      => 'Uji buku kas',
            'status'         => $status,
            'diajukan_oleh'  => 1,
            'disetujui_oleh' => $status === 'diajukan' ? null : 1,
            'disetujui_at'   => $status === 'diajukan' ? null : now(),
            'dibayar_oleh'   => $status === 'dibayar' ? 1 : null,
            'dibayar_at'     => $status === 'dibayar' ? now() : null,
        ]);

        PencairanItem::create([
            'pencairan_id'    => $pc->id,
            'anggaran_pos_id' => $posId,
            'bulan_fiskal'    => $bulan,
            'nominal'         => $nominal,
        ]);

        return $pc->fresh('items');
    }

    private function tutupBukuIni(float $nominal = 75000): BukuKasBulanan
    {
        $posId = AnggaranPos::value('id');
        $this->buatSpp('dibayar', $nominal, $posId, fiskal_bulan(now()));

        $this->post('/kebendaharaan/laporan/laporkan')
            ->assertRedirect(route('kebendaharaan.laporan.index'));

        $periode = Periode::where('is_active', true)->firstOrFail();

        return BukuKasBulanan::where('periode_id', $periode->id)
            ->whereNotNull('pencairan_id')
            ->latest('id')
            ->firstOrFail();
    }

    public function test_index_dan_form_tampil(): void
    {
        $this->get('/kebendaharaan/laporan')->assertOk()->assertSee('Transaksi');

        $posId = AnggaranPos::value('id');
        $this->buatSpp('dibayar', 50000, $posId);
        $this->get('/kebendaharaan/laporan/buat')->assertOk()
            ->assertSee('Catat Pengeluaran')
            ->assertSee('Ambil Foto')
            ->assertSee('capture="environment"', false)
            ->assertSee('Kembali ke Transaksi');
    }

    public function test_belanja_disimpan_langsung_realisasi(): void
    {
        $posId = AnggaranPos::value('id');
        $pc = $this->buatSpp('dibayar', 50000, $posId);
        $item = $pc->items->first();

        $res = $this->post('/kebendaharaan/laporan', [
            'jenis'             => 'keluar',
            'pencairan_id'      => $pc->id,
            'pencairan_item_id' => $item->id,
            'tanggal'           => now()->toDateString(),
            'uraian'            => 'Beli ATK',
            'nominal'           => '25.000',
        ]);

        $res->assertRedirect();
        $res->assertSessionHas('sukses');

        $lp = LaporanPengeluaran::where('pencairan_id', $pc->id)->first();
        $this->assertNotNull($lp);
        $this->assertSame('disetujui', $lp->status);
        $this->assertSame(25000.0, (float) $lp->nominal);
        $this->assertDatabaseHas('laporan_pengeluaran_item', [
            'laporan_pengeluaran_id' => $lp->id,
            'pencairan_item_id'      => $item->id,
            'nominal'                => 25000,
        ]);
    }

    public function test_belanja_melebihi_sisa_hanya_peringatan(): void
    {
        $posId = AnggaranPos::value('id');
        $pc = $this->buatSpp('dibayar', 50000, $posId);
        $item = $pc->items->first();

        $res = $this->post('/kebendaharaan/laporan', [
            'jenis'             => 'keluar',
            'pencairan_id'      => $pc->id,
            'pencairan_item_id' => $item->id,
            'tanggal'           => now()->toDateString(),
            'uraian'            => 'Belanja besar',
            'nominal'           => '999.000',
        ]);

        $res->assertRedirect();
        $res->assertSessionHas('warning');
        $this->assertDatabaseHas('laporan_pengeluaran', ['pencairan_id' => $pc->id, 'nominal' => 999000]);
    }

    public function test_spp_belum_disetujui_ditolak(): void
    {
        $posId = AnggaranPos::value('id');
        $pc = $this->buatSpp('diajukan', 50000, $posId);
        $item = $pc->items->first();

        $res = $this->post('/kebendaharaan/laporan', [
            'jenis'             => 'keluar',
            'pencairan_id'      => $pc->id,
            'pencairan_item_id' => $item->id,
            'tanggal'           => now()->toDateString(),
            'uraian'            => 'Coba',
            'nominal'           => '10.000',
        ]);

        $res->assertSessionHas('error');
        $this->assertDatabaseMissing('laporan_pengeluaran', ['pencairan_id' => $pc->id]);
    }

    public function test_pemasukan_manual_tercatat(): void
    {
        $posId = AnggaranPos::value('id');
        $this->buatSpp('dibayar', 50000, $posId);

        $res = $this->post('/kebendaharaan/laporan', [
            'jenis'   => 'masuk',
            'uraian'  => 'Setoran jual buku',
            'tanggal' => now()->toDateString(),
            'jumlah'  => '5.000',
        ]);

        $res->assertRedirect();
        $res->assertSessionHas('sukses');
        $this->assertDatabaseHas('pemasukan', ['uraian' => 'Setoran jual buku', 'jumlah' => 5000]);
    }

    public function test_laporkan_spp_mengunci_input(): void
    {
        $posId = AnggaranPos::value('id');
        $pc = $this->buatSpp('dibayar', 75000, $posId, fiskal_bulan(now()));

        // Pastikan hanya SPP uji yang terbuka (Sisa buku historis ikut terbuka karena setUp menghapus buku).
        Pencairan::where('jenis', 'rutin')->whereDoesntHave('bukuKas')
            ->where('id', '!=', $pc->id)->delete();

        $this->post('/kebendaharaan/laporan/laporkan', ['catatan' => 'Dana dipakai ATK dan fotokopi.'])
            ->assertRedirect(route('kebendaharaan.laporan.index'));

        $periode = Periode::where('is_active', true)->firstOrFail();
        $buku = BukuKasBulanan::where('pencairan_id', $pc->id)->firstOrFail();

        $this->assertNotNull($buku->dilaporkan_at);
        $this->assertSame('Dana dipakai ATK dan fotokopi.', $buku->catatan);
        $this->assertSame('dilaporkan', $buku->status);
        $this->assertSame((int) fiskal_bulan(now()), $buku->bulan_fiskal);
        $this->assertSame(75000.0, (float) $buku->total_masuk);

        // Halaman jadi kosong + hanya riwayat.
        $this->get('/kebendaharaan/laporan')->assertOk()
            ->assertSee('Riwayat Laporan Saya')->assertSee('Menunggu Validasi')->assertSee($pc->kode);

        // Input diblokir saat laporan sudah dikirim.
        $this->post('/kebendaharaan/laporan', [
            'jenis'   => 'masuk',
            'uraian'  => 'Ditolak',
            'tanggal' => now()->toDateString(),
            'jumlah'  => '1.000',
        ])->assertSessionHas('error');
        $this->assertDatabaseMissing('pemasukan', ['uraian' => 'Ditolak']);

        // Form buat juga diblokir.
        $this->get('/kebendaharaan/laporan/buat')->assertSessionHas('error');

        // Administrator boleh membuka paksa -> bisa input lagi.
        $this->post(route('kebendaharaan.laporan.buka-buku', $buku->id))
            ->assertRedirect(route('kebendaharaan.laporan.index'));
        $this->assertDatabaseMissing('buku_kas_bulanan', ['id' => $buku->id]);

        $this->post('/kebendaharaan/laporan', [
            'jenis'   => 'masuk',
            'uraian'  => 'Setelah dibuka',
            'tanggal' => now()->toDateString(),
            'jumlah'  => '2.000',
        ])->assertSessionHas('sukses');
    }

    public function test_buku_spp_berikutnya_aktif_setelah_lama_dilaporkan(): void
    {
        $this->tutupBukuIni();

        // Bersihkan sisa SPP rutin terbuka lain agar keadaan deterministik.
        Pencairan::where('jenis', 'rutin')->whereDoesntHave('bukuKas')->delete();

        // Tidak ada SPP dibayar tersisa -> buku aktif kosong, FAB disembunyikan.
        $this->get('/kebendaharaan/laporan')->assertOk()
            ->assertSee('Belum ada buku kas aktif')
            ->assertDontSee('Catat Transaksi');

        // SPP rutin berikutnya dibayar -> langsung menjadi buku kas aktif.
        $posId = AnggaranPos::value('id');
        $pc2 = $this->buatSpp('dibayar', 60000, $posId, fiskal_bulan(now()));

        $this->get('/kebendaharaan/laporan')->assertOk()
            ->assertSee('Catat Transaksi')
            ->assertSee($pc2->kode)
            ->assertDontSee('Belum ada buku kas aktif');
    }

    public function test_sahkan_mengunci_permanen_kecuali_admin(): void
    {
        $buku = $this->tutupBukuIni();

        // Pohon sahkan: laporan -> diterima bendahara -> disahkan pimpinan.
        $this->post(route('kebendaharaan.laporan.terima', $buku->id))
            ->assertRedirect(route('kebendaharaan.laporan.index'))
            ->assertSessionHas('sukses');
        $this->assertNotNull($buku->fresh()->diterima_bendahara_at);
        $this->assertSame('diterima', $buku->fresh()->status);

        $this->post(route('kebendaharaan.laporan.sahkan', $buku->id))
            ->assertRedirect(route('kebendaharaan.laporan.index'))
            ->assertSessionHas('sukses');

        $this->assertNotNull($buku->fresh()->disahkan_at);
        $this->assertSame('disahkan', $buku->fresh()->status);

        // Pengguna biasa (bukan Administrator) tidak bisa membuka buku yang disahkan.
        $staf = User::factory()->create([
            'username' => 'staf_' . Str::random(6),
            'status'   => 'Aktif',
            'name'     => 'Staf Uji',
        ]);
        $staf->givePermissionTo('akses_laporan_kebendaharaan');
        $this->actingAs($staf);

        $this->post(route('kebendaharaan.laporan.buka-buku', $buku->id))
            ->assertSessionHas('error');
        $this->assertDatabaseHas('buku_kas_bulanan', ['id' => $buku->id]);

        // Administrator boleh buka paksa.
        $this->actingAs($this->admin);
        $this->post(route('kebendaharaan.laporan.buka-buku', $buku->id))
            ->assertSessionHas('sukses');
        $this->assertDatabaseMissing('buku_kas_bulanan', ['id' => $buku->id]);
    }

    public function test_cetak_mengembalikan_pdf(): void
    {
        $buku = $this->tutupBukuIni();

        $this->get(route('kebendaharaan.laporan.cetak', $buku->id))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_foto_nota_dikompres_dan_tersimpan(): void
    {
        $posId = AnggaranPos::value('id');
        $pc = $this->buatSpp('dibayar', 100000, $posId);
        $item = $pc->items->first();

        $file = UploadedFile::fake()->image('nota.jpg', 2600, 1800);

        $this->post('/kebendaharaan/laporan', [
            'jenis'             => 'keluar',
            'pencairan_id'      => $pc->id,
            'pencairan_item_id' => $item->id,
            'tanggal'           => now()->toDateString(),
            'uraian'            => 'Belanja dengan nota',
            'nominal'           => '10.000',
            'nota'              => $file,
        ])->assertSessionHas('sukses');

        $lp = LaporanPengeluaran::where('pencairan_id', $pc->id)->first();
        $this->assertNotNull($lp->nota_foto);
        $this->assertTrue(Storage::disk('public_uploads')->exists($lp->nota_foto));
        $this->assertLessThanOrEqual(1_000_000, Storage::disk('public_uploads')->size($lp->nota_foto));

        Storage::disk('public_uploads')->delete($lp->nota_foto);
    }

    public function test_hapus_belanja(): void
    {
        $posId = AnggaranPos::value('id');
        $pc = $this->buatSpp('dibayar', 50000, $posId);
        $item = $pc->items->first();

        $this->post('/kebendaharaan/laporan', [
            'jenis'             => 'keluar',
            'pencairan_id'      => $pc->id,
            'pencairan_item_id' => $item->id,
            'tanggal'           => now()->toDateString(),
            'uraian'            => 'Akan dihapus',
            'nominal'           => '5.000',
        ]);

        $lp = LaporanPengeluaran::where('pencairan_id', $pc->id)->firstOrFail();

        $this->delete('/kebendaharaan/laporan/' . $lp->id)->assertSessionHas('sukses');
        $this->assertDatabaseMissing('laporan_pengeluaran', ['id' => $lp->id]);
    }

    public function test_rekap_tetap_berjalan(): void
    {
        $this->get('/kebendaharaan/rekap')->assertOk();
    }

    private function buatUserBiasa(string $nama): User
    {
        return User::factory()->create([
            'username' => 'user_' . strtolower(Str::random(8)),
            'status'   => 'Aktif',
            'name'     => $nama,
        ]);
    }

    /** SPP rutin dibayar milik user tertentu (bukan admin yang sedang login). */
    private function buatSppMilik(User $pemilik, float $nominal, string $keperluan): Pencairan
    {
        $periode = Periode::where('is_active', true)->firstOrFail();
        $posId = AnggaranPos::value('id');

        $pc = Pencairan::create([
            'kode'           => 'SPP-' . strtoupper(Str::random(6)),
            'periode_id'     => $periode->id,
            'pos_id'         => null,
            'bulan_fiskal'   => fiskal_bulan(now()),
            'jenis'          => 'rutin',
            'tanggal_aju'    => now()->toDateString(),
            'nominal'        => $nominal,
            'keperluan'      => $keperluan,
            'status'         => 'dibayar',
            'diajukan_oleh'  => $pemilik->id,
            'disetujui_oleh' => $pemilik->id,
            'disetujui_at'   => now(),
            'dibayar_oleh'   => $pemilik->id,
            'dibayar_at'     => now(),
        ]);

        PencairanItem::create([
            'pencairan_id'    => $pc->id,
            'anggaran_pos_id' => $posId,
            'bulan_fiskal'    => fiskal_bulan(now()),
            'nominal'         => $nominal,
        ]);

        return $pc->fresh('items');
    }

    public function test_buku_kas_spp_milik_pengaju_lain_tidak_tampil(): void
    {
        $lain = $this->buatUserBiasa('Pengaju Lain');
        $lain->givePermissionTo('akses_laporan_kebendaharaan');
        $pc = $this->buatSppMilik($lain, 100000, 'SPP milik pengaju lain');

        // Admin yang bukan pengaju melihat halaman kosong.
        $this->actingAs($this->admin);
        $this->get('/kebendaharaan/laporan')->assertOk()
            ->assertSee('Belum ada buku kas aktif untuk Anda')
            ->assertDontSee($pc->kode)
            ->assertDontSee('Catat Transaksi');

        // Pengajunya sendiri justru melihat bukunya.
        $this->actingAs($lain);
        $this->get('/kebendaharaan/laporan')->assertOk()
            ->assertSee($pc->kode)
            ->assertDontSee('Belum ada buku kas aktif untuk Anda');
    }

    public function test_belanja_spp_milik_orang_lain_ditolak(): void
    {
        $lain = $this->buatUserBiasa('Pengaju Lain');
        $pc = $this->buatSppMilik($lain, 50000, 'SPP dipakai coba-coba');
        $item = $pc->items->first();

        $this->post('/kebendaharaan/laporan', [
            'jenis'             => 'keluar',
            'pencairan_id'      => $pc->id,
            'pencairan_item_id' => $item->id,
            'tanggal'           => now()->toDateString(),
            'uraian'            => 'Coba input milik orang lain',
            'nominal'           => '10.000',
        ])->assertSessionHas('error', fn ($m) => str_contains($m, 'yang Anda ajukan'));

        $this->assertDatabaseMissing('laporan_pengeluaran', ['pencairan_id' => $pc->id]);
    }

    public function test_hapus_belanja_hanya_oleh_pencatat_atau_admin(): void
    {
        $pengaju = $this->buatUserBiasa('Pengaju Sah');
        $pengaju->givePermissionTo('akses_laporan_kebendaharaan');
        $pc = $this->buatSppMilik($pengaju, 50000, 'SPP milik pengaju sah');

        $this->actingAs($pengaju);
        $this->post('/kebendaharaan/laporan', [
            'jenis'             => 'keluar',
            'pencairan_id'      => $pc->id,
            'pencairan_item_id' => $pc->items->first()->id,
            'tanggal'           => now()->toDateString(),
            'uraian'            => 'Akan diuji hapus',
            'nominal'           => '5.000',
        ])->assertSessionHas('sukses');

        $lp = LaporanPengeluaran::where('pencairan_id', $pc->id)->firstOrFail();

        // Staf lain (bukan pencatat, bukan pengaju, bukan admin) ditolak.
        $staf = $this->buatUserBiasa('Staf Iseng');
        $staf->givePermissionTo('akses_laporan_kebendaharaan');
        $this->actingAs($staf);
        $this->delete('/kebendaharaan/laporan/' . $lp->id)->assertSessionHas('error');
        $this->assertDatabaseHas('laporan_pengeluaran', ['id' => $lp->id]);

        // Administrator tetap boleh.
        $this->actingAs($this->admin);
        $this->delete('/kebendaharaan/laporan/' . $lp->id)->assertSessionHas('sukses');
        $this->assertDatabaseMissing('laporan_pengeluaran', ['id' => $lp->id]);
    }

    public function test_pemasukan_manual_hanya_milik_pencatat_ditambilkan(): void
    {
        $posId = AnggaranPos::value('id');
        $this->buatSpp('dibayar', 50000, $posId);

        // Pemasukan yang dicatat user lain tidak boleh masuk kartu admin.
        $lain = $this->buatUserBiasa('Pencatat Lain');
        $periode = Periode::where('is_active', true)->firstOrFail();
        Pemasukan::create([
            'periode_id' => $periode->id,
            'uraian'     => 'Pemasukan milik pencatat lain',
            'tanggal'    => now()->toDateString(),
            'jumlah'     => 999999,
            'created_by' => $lain->id,
        ]);

        $this->get('/kebendaharaan/laporan')->assertOk()
            ->assertDontSee('Pemasukan milik pencatat lain');

        // Pemasukan oleh admin (pencatat & pengaju) tampil.
        $this->post('/kebendaharaan/laporan', [
            'jenis'   => 'masuk',
            'uraian'  => 'Pemasukan saya sendiri',
            'tanggal' => now()->toDateString(),
            'jumlah'  => '5.000',
        ])->assertSessionHas('sukses');

        $this->get('/kebendaharaan/laporan')->assertOk()
            ->assertSee('Pemasukan saya sendiri')
            ->assertDontSee('Pemasukan milik pencatat lain');
    }

    private function buatValidator(string $nama, string $akses): User
    {
        $u = $this->buatUserBiasa($nama);
        $u->givePermissionTo('akses_laporan_kebendaharaan', $akses);

        return $u;
    }

    /** Buat SPP milik user, catat satu belanja, lalu kirim laporan ke bendahara. */
    private function laporkanSebagai(User $pemilik): array
    {
        $pemilik->givePermissionTo('akses_laporan_kebendaharaan');
        $pc = $this->buatSppMilik($pemilik, 90000, 'SPP uji pelaporan');

        $this->actingAs($pemilik);
        $this->post('/kebendaharaan/laporan', [
            'jenis'             => 'keluar',
            'pencairan_id'      => $pc->id,
            'pencairan_item_id' => $pc->items->first()->id,
            'tanggal'           => now()->toDateString(),
            'uraian'            => 'Beli ATK',
            'nominal'           => '25.000',
        ])->assertSessionHas('sukses');

        $this->post('/kebendaharaan/laporan/laporkan', ['catatan' => 'Laporan uji dari ' . $pemilik->name])
            ->assertRedirect(route('kebendaharaan.laporan.index'));

        $buku = BukuKasBulanan::where('pencairan_id', $pc->id)->firstOrFail();

        return [$pc, $buku];
    }

    public function test_bendahara_melihat_dan_menerima_laporan_pengaju_lain(): void
    {
        $pengaju = $this->buatUserBiasa('Pengaju Pelapor');
        [$pc, $buku] = $this->laporkanSebagai($pengaju);
        $this->assertNotNull($buku->dilaporkan_at);
        $this->assertSame('dilaporkan', $buku->status);
        $this->assertSame('Laporan uji dari Pengaju Pelapor', $buku->catatan);

        // Bendahara melihat laporan milik pengaju lain di halaman Validasi Laporan.
        $bendahara = $this->buatValidator('Bendahara Uji', 'akses_validasi_pencairan');
        $this->actingAs($bendahara);
        $this->get('/kebendaharaan/laporan/validasi')->assertOk()
            ->assertSee('Menunggu Validasi Bendahara')
            ->assertSee($pc->kode)
            ->assertSee('Laporan uji dari Pengaju Pelapor');

        // Terima -> masuk tahap pengesahan, tidak lagi menunggu bendahara.
        $this->post(route('kebendaharaan.laporan.terima', $buku->id))
            ->assertSessionHas('sukses');
        $this->assertNotNull($buku->fresh()->diterima_bendahara_at);
        $this->assertSame('diterima', $buku->fresh()->status);

        $this->get('/kebendaharaan/laporan/validasi')->assertOk()
            ->assertDontSee($pc->kode)
            ->assertDontSee('Laporan uji dari Pengaju Pelapor');
    }

    public function test_bendahara_kembalikan_pengaju_perbaiki_lalu_kirim_ulang(): void
    {
        $pengaju = $this->buatUserBiasa('Pengaju Pelapor');
        [$pc, $buku] = $this->laporkanSebagai($pengaju);

        $bendahara = $this->buatValidator('Bendahara Uji', 'akses_validasi_pencairan');
        $this->actingAs($bendahara);
        $this->post(route('kebendaharaan.laporan.kembalikan', $buku->id), ['alasan' => 'Nota kurang jelas.'])
            ->assertSessionHas('sukses');

        $buku->refresh();
        $this->assertSame('dikembalikan', $buku->status);
        $this->assertSame('Nota kurang jelas.', $buku->alasan_dikembalikan);

        // Pengaju melihat status + alasan.
        $this->actingAs($pengaju);
        $this->get('/kebendaharaan/laporan')->assertOk()
            ->assertSee('Dikembalikan')
            ->assertSee('Nota kurang jelas.');

        // Pengaju bisa buka (perbaiki) karena dikembalikan -> kirim ulang.
        $this->post(route('kebendaharaan.laporan.buka-buku', $buku->id))
            ->assertSessionHas('sukses');
        $this->assertDatabaseMissing('buku_kas_bulanan', ['id' => $buku->id]);

        $this->post('/kebendaharaan/laporan/laporkan', ['catatan' => 'Perbaikan: nota dilampirkan.'])
            ->assertSessionHas('sukses');

        $buku2 = BukuKasBulanan::where('pencairan_id', $pc->id)->firstOrFail();
        $this->assertNotNull($buku2->dilaporkan_at);
        $this->assertSame('dilaporkan', $buku2->status);
        $this->assertSame('Perbaikan: nota dilampirkan.', $buku2->catatan);
    }

    public function test_pimpinan_mensahkan_setelah_diterima_bendahara(): void
    {
        $pengaju = $this->buatUserBiasa('Pengaju Pelapor');
        [$pc, $buku] = $this->laporkanSebagai($pengaju);

        // Pimpinan belum bisa sahkan sebelum diterima bendahara.
        $pimpinan = $this->buatValidator('Pimpinan Uji', 'akses_validasi_buku_kas');
        $this->actingAs($pimpinan);
        $this->post(route('kebendaharaan.laporan.sahkan', $buku->id))
            ->assertSessionHas('error');
        $this->assertNull($buku->fresh()->disahkan_at);
        $this->get('/kebendaharaan/laporan/validasi')->assertOk()
            ->assertDontSee($pc->kode);
        $this->actingAs($pengaju);

        // Bendahara terima.
        $bendahara = $this->buatValidator('Bendahara Uji', 'akses_validasi_pencairan');
        $this->actingAs($bendahara);
        $this->post(route('kebendaharaan.laporan.terima', $buku->id))
            ->assertSessionHas('sukses');

        // Pimpinan melihat di daftar pengesahan lalu sahkan final.
        $this->actingAs($pimpinan);
        $this->get('/kebendaharaan/laporan/validasi')->assertOk()
            ->assertSee('Menunggu Pengesahan Pimpinan')
            ->assertSee($pc->kode);

        $this->post(route('kebendaharaan.laporan.sahkan', $buku->id))
            ->assertSessionHas('sukses');
        $this->assertNotNull($buku->fresh()->disahkan_at);
        $this->assertSame('disahkan', $buku->fresh()->status);
    }

    public function test_disahkan_mencatat_pengembalian_sisa_panjar_otomatis(): void
    {
        $pengaju = $this->buatUserBiasa('Pengaju Pelapor');
        [$pc, $buku] = $this->laporkanSebagai($pengaju); // SPP 90.000, belanja 25.000 disetujui -> sisa 65.000.

        $this->assertSame(65000.0, (float) $buku->sisa);
        $this->assertDatabaseMissing('pemasukan', ['pencairan_id' => $pc->id]);

        $bendahara = $this->buatValidator('Bendahara Uji', 'akses_validasi_pencairan');
        $this->actingAs($bendahara);
        $this->post(route('kebendaharaan.laporan.terima', $buku->id))->assertSessionHas('sukses');

        $pimpinan = $this->buatValidator('Pimpinan Uji', 'akses_validasi_buku_kas');
        $pimpinan->givePermissionTo('akses_kebendaharaan');
        $this->actingAs($pimpinan);
        $this->post(route('kebendaharaan.laporan.sahkan', $buku->id))->assertSessionHas('sukses');
        $this->assertStringContainsString('Sisa panjar Rp 65.000 dikembalikan', session('sukses'));

        // Sisa panjar otomatis tercatat sebagai pemasukan pengembalian tertaut SPP.
        $pemasukan = Pemasukan::where('pencairan_id', $pc->id)->firstOrFail();
        $this->assertSame(65000.0, (float) $pemasukan->jumlah);
        $this->assertSame('Pengembalian sisa panjar ' . $pc->kode, $pemasukan->uraian);
        $this->assertSame($pimpinan->id, $pemasukan->created_by);

        // Kas Umum menampilkan pengembalian sebagai masuk dan sisa panjar SPP tinggal 0.
        $this->get(route('kebendaharaan.kas-umum'))->assertOk()
            ->assertSee('Pengembalian sisa panjar ' . $pc->kode);
    }

    public function test_resahkan_setelah_dibuka_admin_tidak_duplikasi_pengembalian(): void
    {
        $pengaju = $this->buatUserBiasa('Pengaju Pelapor');
        [$pc, $buku] = $this->laporkanSebagai($pengaju);

        $bendahara = $this->buatValidator('Bendahara Uji', 'akses_validasi_pencairan');
        $this->actingAs($bendahara);
        $this->post(route('kebendaharaan.laporan.terima', $buku->id))->assertSessionHas('sukses');

        $pimpinan = $this->buatValidator('Pimpinan Uji', 'akses_validasi_buku_kas');
        $this->actingAs($pimpinan);
        $this->post(route('kebendaharaan.laporan.sahkan', $buku->id))->assertSessionHas('sukses');
        $this->assertSame(1, Pemasukan::where('pencairan_id', $pc->id)->count());

        // Admin buka paksa (hapus laporan), pengaju kirim ulang, pimpinan sahkan lagi.
        $this->actingAs($this->admin);
        $this->post(route('kebendaharaan.laporan.buka-buku', $buku->id))->assertSessionHas('sukses');

        $this->actingAs($pengaju);
        $this->post('/kebendaharaan/laporan/laporkan', ['catatan' => 'Laporan ulang.'])->assertSessionHas('sukses');
        $buku2 = BukuKasBulanan::where('pencairan_id', $pc->id)->firstOrFail();

        $this->actingAs($bendahara);
        $this->post(route('kebendaharaan.laporan.terima', $buku2->id))->assertSessionHas('sukses');

        $this->actingAs($pimpinan);
        $this->post(route('kebendaharaan.laporan.sahkan', $buku2->id))->assertSessionHas('sukses');

        // Disahkan ulang TIDAK membuat pengembalian kedua (sisa sudah kembali lewat laporan pertama).
        $this->assertSame(1, Pemasukan::where('pencairan_id', $pc->id)->count());
        $this->assertSame(65000.0, (float) Pemasukan::where('pencairan_id', $pc->id)->first()->jumlah);
    }

    public function test_pimpinan_kembalikan_setelah_diterima_bendahara(): void
    {
        $pengaju = $this->buatUserBiasa('Pengaju Pelapor');
        [$pc, $buku] = $this->laporkanSebagai($pengaju);

        $bendahara = $this->buatValidator('Bendahara Uji', 'akses_validasi_pencairan');
        $this->actingAs($bendahara);
        $this->post(route('kebendaharaan.laporan.terima', $buku->id))
            ->assertSessionHas('sukses');

        $pimpinan = $this->buatValidator('Pimpinan Uji', 'akses_validasi_buku_kas');
        $this->actingAs($pimpinan);
        $this->post(route('kebendaharaan.laporan.kembalikan-pengesahan', $buku->id), ['alasan' => 'Uraian terlalu umum.'])
            ->assertSessionHas('sukses');

        $buku->refresh();
        $this->assertSame('dikembalikan', $buku->status);
        $this->assertNull($buku->diterima_bendahara_at);
        $this->assertSame('Uraian terlalu umum.', $buku->alasan_dikembalikan);
    }

    public function test_pengaju_tidak_bisa_membuka_saat_laporan_diproses(): void
    {
        $pengaju = $this->buatUserBiasa('Pengaju Pelapor');
        [$pc, $buku] = $this->laporkanSebagai($pengaju);

        $this->actingAs($pengaju);
        $this->post(route('kebendaharaan.laporan.buka-buku', $buku->id))
            ->assertSessionHas('error', fn ($m) => str_contains($m, 'sedang diproses'));
        $this->assertDatabaseHas('buku_kas_bulanan', ['id' => $buku->id, 'pencairan_id' => $pc->id]);
    }

    public function test_tanpa_akses_validasi_tidak_bisa_terima_atau_sahkan(): void
    {
        $pengaju = $this->buatUserBiasa('Pengaju Pelapor');
        [$pc, $buku] = $this->laporkanSebagai($pengaju);

        $biasa = $this->buatUserBiasa('Pegawai Iseng');
        $biasa->givePermissionTo('akses_laporan_kebendaharaan');
        $this->actingAs($biasa);

        $this->post(route('kebendaharaan.laporan.terima', $buku->id))
            ->assertSessionHas('error');
        $this->post(route('kebendaharaan.laporan.sahkan', $buku->id))
            ->assertForbidden();

        $this->assertNull($buku->fresh()->diterima_bendahara_at);
        $this->assertNull($buku->fresh()->disahkan_at);
        $this->assertSame('dilaporkan', $buku->fresh()->status);
    }
}
