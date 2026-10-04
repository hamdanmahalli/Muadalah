<?php

namespace Tests\Feature;

use App\Http\Middleware\MenegakkanSatuDevice;
use App\Models\AnggaranKebendaharaan;
use App\Models\AnggaranKelompok;
use App\Models\AnggaranPos;
use App\Models\Guru;
use App\Models\HonorDetail;
use App\Models\HonorKonfigurasi;
use App\Models\HonorPeriode;
use App\Models\HonorStrukturalConfig;
use App\Models\Jabatan;
use App\Models\Pencairan;
use App\Models\Periode;
use App\Models\User;
use App\Services\Honor\HonorService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Tests\TestCase;

class HonorPencairanTest extends TestCase
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

    private function buatAnggaranHonor(): array
    {
        $anggaran = AnggaranKebendaharaan::create([
            'periode_id'   => $this->periode->id,
            'nama'         => 'RAB Honor Test ' . uniqid(),
            'tahun_ajaran' => $this->periode->tahun_ajaran,
            'status'       => 'final',
            'created_by'   => 1,
        ]);

        $kelompok = AnggaranKelompok::create([
            'anggaran_id' => $anggaran->id,
            'kode'        => 100,
            'nama'        => 'HONORIUM DAN TUNJANGAN',
            'urutan'      => 1,
        ]);

        $uraian = [
            101 => 'Tunjangan Kepala Sekolah',
            102 => 'Tunjangan Struktural',
            103 => 'Tunjangan Wali Kelas',
            104 => 'Transport Guru Luar',
            105 => 'Honor Guru Harian',
        ];

        $pos = [];
        foreach ($uraian as $kode => $nama) {
            $pos[$kode] = AnggaranPos::create([
                'anggaran_id'  => $anggaran->id,
                'kelompok_id'  => $kelompok->id,
                'kode'         => $kode,
                'uraian'       => $nama,
                'volume'       => 1,
                'harga_satuan' => 90000000,
                'jumlah'       => 90000000,
            ]);
        }

        return $pos;
    }

    private function buatKonfigurasiHonor(): HonorKonfigurasi
    {
        return HonorKonfigurasi::create([
            'periode_id'          => $this->periode->id,
            'bulan'               => 7,
            'tahun'               => 2026,
            'tarif_jam_normal'    => 14000,
            'tarif_pengabdian'    => 10000,
            'tarif_piket'         => 10000,
            'tarif_piket_pengabdian' => 8000,
            'tarif_transport'     => 7000,
            'tarif_wali_kelas'    => 60000,
        ]);
    }

    private function buatPeriodeHonor(HonorKonfigurasi $cfg, string $status = 'draft'): HonorPeriode
    {
        return HonorPeriode::create([
            'honor_konfigurasi_id' => $cfg->id,
            'bulan'                => $cfg->bulan,
            'tahun'                => $cfg->tahun,
            'status'               => $status,
            'created_by'           => 1,
        ]);
    }

    private function buatGuru(string $nama, array $namaJabatan = []): Guru
    {
        $guru = Guru::create([
            'nig'       => 'NIG-' . uniqid(),
            'nama_guru' => $nama . '_' . uniqid(),
            'status'    => 'Aktif',
        ]);

        foreach ($namaJabatan as $jn) {
            $jab = Jabatan::firstOrCreate(['nama_jabatan' => $jn], ['status' => 'Aktif']);
            $guru->jabatans()->attach($jab->id, ['is_utama' => false]);
        }

        return $guru;
    }

    private function pasangStruktural(HonorKonfigurasi $cfg, string $namaJabatan, int $nominal): void
    {
        $jab = Jabatan::firstOrCreate(['nama_jabatan' => $namaJabatan], ['status' => 'Aktif']);
        HonorStrukturalConfig::create([
            'honor_konfigurasi_id' => $cfg->id,
            'jabatan_id'           => $jab->id,
            'nominal'              => $nominal,
        ]);
    }

    private function buatDetail(HonorPeriode $periode, Guru $guru, array $komponen): HonorDetail
    {
        $komponen = array_merge([
            'honor_pokok'           => 0,
            'tunjangan_struktural'  => 0,
            'tunjangan_wali_kelas'  => 0,
            'transport'             => 0,
            'honor_piket'           => 0,
        ], $komponen);

        return HonorDetail::create([
            'honor_periode_id'      => $periode->id,
            'guru_id'               => $guru->id,
            'jam_wajib'             => 0,
            'alpa'                  => 0,
            'izin'                  => 0,
            'sakit'                 => 0,
            'piket_jam'             => 0,
            'realita_jam'           => 0,
            'persentase'            => 0,
            'keterangan'            => '-',
            'honor_pokok'           => $komponen['honor_pokok'],
            'tunjangan_struktural'  => $komponen['tunjangan_struktural'],
            'tunjangan_wali_kelas'  => $komponen['tunjangan_wali_kelas'],
            'transport'             => $komponen['transport'],
            'honor_piket'           => $komponen['honor_piket'],
            'total'                 => array_sum($komponen),
        ]);
    }

    private function sinkron(HonorPeriode $periode): ?Pencairan
    {
        return app(HonorService::class)->sinkronSpp($periode, 1);
    }

    private function setupLengkap(): HonorPeriode
    {
        $this->buatAnggaranHonor();
        $cfg = $this->buatKonfigurasiHonor();
        $this->pasangStruktural($cfg, 'Kepala Madrasah', 150000);
        $this->pasangStruktural($cfg, 'Waka Kurikulum', 100000);

        $kepala = $this->buatGuru('Kepala', ['Kepala Madrasah']);
        $wali   = $this->buatGuru('Wali', ['Waka Kurikulum']);

        $periode = $this->buatPeriodeHonor($cfg);
        $this->buatDetail($periode, $kepala, [
            'honor_pokok'          => 1000000,
            'honor_piket'          => 50000,
            'tunjangan_struktural' => 150000,
        ]);
        $this->buatDetail($periode, $wali, [
            'honor_pokok'          => 800000,
            'tunjangan_struktural' => 100000,
            'tunjangan_wali_kelas' => 60000,
            'transport'            => 45000,
        ]);

        return $periode;
    }

    public function test_sinkron_membuat_spp_honor_diajukan_berisi_items_per_pos(): void
    {
        $periode = $this->setupLengkap();

        $spp = $this->sinkron($periode);

        $this->assertNotNull($spp);
        $this->assertSame('honor', $spp->jenis);
        $this->assertSame('diajukan', $spp->status);
        $this->assertSame($periode->id, $spp->honor_periode_id);
        $this->assertSame(fiskal_bulan(Carbon::create(2026, 7, 1)), $spp->bulan_fiskal);
        $this->assertStringContainsString('Honor & Tunjangan Guru', $spp->keperluan);
        $this->assertStringContainsString('Juli 2026', $spp->keperluan);

        $items = $spp->fresh('items')->items->mapWithKeys(fn ($it) => [$it->pos->kode => (int) $it->nominal]);
        $this->assertCount(5, $items);
        $this->assertSame(150000, $items->get('101'));
        $this->assertSame(100000, $items->get('102'));
        $this->assertSame(60000, $items->get('103'));
        $this->assertSame(45000, $items->get('104'));
        $this->assertSame(1850000, $items->get('105'));
        $this->assertSame(2205000, (int) $spp->jumlah);
        $this->assertSame(1, Pencairan::where('honor_periode_id', $periode->id)->count());
    }

    public function test_update_detail_merekonstruksi_spp_saat_masih_draft(): void
    {
        $periode = $this->setupLengkap();
        $spp1 = $this->sinkron($periode);
        $detail = $periode->details->first();

        $this->from('/honor/rekap/' . $periode->id)->post(route('honor.detail.update', $detail->id), [
            'honor_pokok'          => '2000000',
            'tunjangan_struktural' => '0',
            'tunjangan_wali_kelas' => '0',
            'transport'            => '0',
            'honor_piket'          => '0',
        ])->assertSessionHas('sukses');

        $this->assertSame(2000000, (int) $detail->fresh()->honor_pokok);
        $this->assertSame('draft', $periode->fresh()->status);

        $spp2 = Pencairan::where('honor_periode_id', $periode->id)->firstOrFail();
        $this->assertSame('diajukan', $spp2->status);
        $this->assertNotSame($spp1->id, $spp2->id);
        $this->assertDatabaseMissing('pencairan', ['id' => $spp1->id]);
        $this->assertSame(1, Pencairan::where('honor_periode_id', $periode->id)->count());

        $item105 = $spp2->fresh('items')->items->firstWhere('pos.kode', '105');
        $this->assertSame(2800000, (int) $item105->nominal);
    }

    public function test_setujui_spp_honor_memfinalkan_honor_dan_mengunci_sinkron(): void
    {
        $periode = $this->setupLengkap();
        $spp = $this->sinkron($periode);

        $this->post(route('kebendaharaan.pencairan.setujui', $spp->id))
            ->assertSessionHas('sukses');

        $this->assertSame('disetujui', $spp->fresh()->status);
        $this->assertSame('final', $periode->fresh()->status);

        // SPP sudah disetujui = terkunci; sinkron ulang tidak mengubah apa pun.
        $ulang = $this->sinkron($periode);
        $this->assertSame($spp->id, $ulang->id);
        $this->assertSame('disetujui', $ulang->status);
        $this->assertSame(1, Pencairan::where('honor_periode_id', $periode->id)->count());
    }

    public function test_edit_nominal_ditolak_setelah_spp_disetujui(): void
    {
        $periode = $this->setupLengkap();
        $spp = $this->sinkron($periode);
        $detail = $periode->details->first();

        $this->post(route('kebendaharaan.pencairan.setujui', $spp->id))->assertSessionHas('sukses');

        $this->from('/honor/rekap/' . $periode->id)->post(route('honor.detail.update', $detail->id), [
            'honor_pokok'          => '5000000',
            'tunjangan_struktural' => '0',
            'tunjangan_wali_kelas' => '0',
            'transport'            => '0',
            'honor_piket'          => '0',
        ])->assertSessionHas('error');

        $this->assertSame(1000000, (int) $detail->fresh()->honor_pokok);
        $this->assertSame('disetujui', $spp->fresh()->status);
    }

    public function test_spp_ditolak_lalu_sinkron_dibuat_ulang(): void
    {
        $periode = $this->setupLengkap();
        $spp1 = $this->sinkron($periode);

        $this->post(route('kebendaharaan.pencairan.tolak', $spp1->id), [
            'tolak_alasan' => 'Ditolak oleh bendahara.',
        ])->assertSessionHas('sukses');
        $this->assertSame('ditolak', $spp1->fresh()->status);

        $spp2 = $this->sinkron($periode);

        $this->assertNotSame($spp1->id, $spp2->id);
        $this->assertSame('diajukan', $spp2->status);
        $this->assertDatabaseMissing('pencairan', ['id' => $spp1->id]);
        $this->assertSame(1, Pencairan::where('honor_periode_id', $periode->id)->count());
        $this->assertSame('draft', $periode->fresh()->status);
    }

    public function test_spp_honor_tidak_wajib_laporan_di_buku_kas(): void
    {
        $periode = $this->setupLengkap();
        $spp = $this->sinkron($periode);

        // Bersihkan SPP rutin uji agar buku kas aktif kosong.
        Pencairan::where('jenis', 'rutin')->whereDoesntHave('bukuKas')->delete();

        $this->post(route('kebendaharaan.pencairan.setujui', $spp->id))->assertSessionHas('sukses');
        $this->post(route('kebendaharaan.pencairan.bayar', $spp->id))->assertSessionHas('sukses');
        $this->assertSame('dibayar', $spp->fresh()->status);

        // Konsumsi flash "sukses" (memuat kode SPP) lewat beranda pencairan, seperti alur nyata.
        $this->get(route('kebendaharaan.pencairan.index'))->assertOk();

        // Honor jenis 'honor' tidak menjadi buku kas aktif & tidak wajib laporan.
        $this->assertFalse($spp->fresh()->bukuKas()->exists());
        $this->get('/kebendaharaan/laporan')->assertOk()
            ->assertDontSee($spp->kode)
            ->assertSee('Belum ada buku kas aktif untuk Anda');
    }

    public function test_rekap_honor_draft_tak_ada_tombol_final(): void
    {
        $periode = $this->setupLengkap();
        $this->sinkron($periode);

        $this->get(route('honor.rekap', $periode->id))
            ->assertOk()
            ->assertSee('Lihat SPP Honor (Pencairan)')
            ->assertSee('SPP honor otomatis diajukan ke Modul Pencairan')
            ->assertDontSee('Finalkan Rekap')
            ->assertDontSee('Buka Kembali');
    }

    public function test_rekap_honor_final_muncul_scan_dan_slip(): void
    {
        $periode = $this->setupLengkap();
        $spp = $this->sinkron($periode);

        $this->post(route('kebendaharaan.pencairan.setujui', $spp->id))->assertSessionHas('sukses');
        $this->assertSame('final', $periode->fresh()->status);

        $this->get(route('honor.rekap', $periode->id))
            ->assertOk()
            ->assertSee('Download Slip PDF')
            ->assertSee('Scan Penerimaan')
            ->assertDontSee('Lihat SPP Honor (Pencairan)')
            ->assertDontSee('Finalkan Rekap')
            ->assertDontSee('Buka Kembali');
    }

    public function test_daftar_spp_menampilkan_badge_dari_honor(): void
    {
        $periode = $this->setupLengkap();
        $spp = $this->sinkron($periode);

        $this->get(route('kebendaharaan.pencairan.index'))
            ->assertOk()
            ->assertSee($spp->kode)
            ->assertSee('Dari Honor');
    }
}