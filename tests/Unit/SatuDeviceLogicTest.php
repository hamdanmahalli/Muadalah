<?php

namespace Tests\Unit;

use App\Http\Middleware\MenegakkanSatuDevice;
use App\Listeners\TetapkanSesiAktif;
use App\Models\User;
use App\Services\SesiManager;
use Illuminate\Auth\Events\Login;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Tests\TestCase;

class SatuDeviceLogicTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
    }

    private function buatUser(array $overrides = []): User
    {
        return User::factory()->create(array_merge([
            'username' => 'guru_' . Str::random(6),
            'status' => 'Aktif',
        ], $overrides));
    }

    /** Id sesi berformat valid (min 40 karakter alnum), deterministik. */
    private function sid(string $seed): string
    {
        $seed = preg_replace('/[^a-zA-Z0-9]/', '', $seed);

        return str_pad($seed, 40, '0', STR_PAD_RIGHT);
    }

    private function tulisSesi(string $sesiId): void
    {
        app('session')->getHandler()->write($sesiId, serialize(['_token' => 'x', '_last_activity' => time()]));
    }

    public function test_sesi_manager_cek_putus_dan_lepas(): void
    {
        $m = app(SesiManager::class);
        $user = $this->buatUser();
        $lama = $this->sid('sesi-lama');
        $baru = $this->sid('sesi-baru');

        $this->assertFalse($m->masihAktif($this->sid('sesi-tidak-ada')));
        $this->assertNull($m->cariSesiLain($user, null));
        $this->assertNull($m->cariSesiLain($user, $baru));

        $this->tulisSesi($lama);
        $user->forceFill(['active_session_id' => $lama])->save();

        $this->assertTrue($m->masihAktif($lama));
        $this->assertSame($lama, $m->cariSesiLain($user, $baru));
        $this->assertNull($m->cariSesiLain($user, $lama)); // perangkat yang sama

        $m->putusSesi($lama);
        $this->assertFalse($m->masihAktif($lama));
        $this->assertNull($m->cariSesiLain($user, $baru));

        $user->forceFill(['active_session_id' => $lama])->save();
        $m->lepas($user, $lama);
        $this->assertNull($user->fresh()->active_session_id);

        $user->forceFill(['active_session_id' => $lama])->save();
        $m->lepas($user, $this->sid('sesi-lain'));
        $this->assertSame($lama, $user->fresh()->active_session_id);
    }

    public function test_listener_login_tanpa_konflik_mencatat_rencana_penetapan(): void
    {
        $user = $this->buatUser();
        app('session')->setId($this->sid('sesi-baru'));

        app(TetapkanSesiAktif::class)->handle(new Login('web', $user, false));

        $this->assertSame($user->getKey(), session('sesi_aktif_menunggu'));
        $this->assertNull($user->fresh()->active_session_id);
    }

    public function test_listener_menolak_login_passkey_saat_sesi_lama_hidup(): void
    {
        $user = $this->buatUser();
        $lama = $this->sid('sesi-lama');
        $this->tulisSesi($lama);
        $user->forceFill(['active_session_id' => $lama])->save();

        app('session')->setId($this->sid('sesi-baru'));

        app(TetapkanSesiAktif::class)->handle(new Login('web', $user, false));

        $this->assertTrue((bool) session('penanda_konflik_passkey'));
        $this->assertGuest();
        $this->assertSame($lama, $user->fresh()->active_session_id);
    }

    public function test_listener_mengambil_alih_saat_sesi_lama_sudah_mati(): void
    {
        $user = $this->buatUser();
        // Tanpa menulis data handler -> sesi lama dianggap mati/kedaluwarsa.
        $user->forceFill(['active_session_id' => $this->sid('sesi-lama')])->save();

        app('session')->setId($this->sid('sesi-baru'));

        app(TetapkanSesiAktif::class)->handle(new Login('web', $user, false));

        $this->assertSame($user->getKey(), session('sesi_aktif_menunggu'));
        $this->assertSame($this->sid('sesi-lama'), $user->fresh()->active_session_id);
    }

    public function test_middleware_menetapkan_pemilik_dan_memutus_sesi_bukan_pemilik(): void
    {
        $user = $this->buatUser();
        $baru = $this->sid('sesi-baru');
        $mw = app(MenegakkanSatuDevice::class);

        // Kasus 1: request dari perangkat yang baru login (ada marker/rencana).
        app('session')->setId($baru);
        Auth::guard('web')->setUser($user);
        session(['sesi_aktif_menunggu' => $user->getKey()]);

        $response = $mw->handle(Request::create('/dashboard-utama', 'GET'), fn () => response('ok', 200));

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame($baru, $user->fresh()->active_session_id);

        // Kasus 2: request dari sesi yang bukan pemilik -> logout & arahkan ke login.
        $user->forceFill(['active_session_id' => $this->sid('sesi-pemilik-lain')])->save();
        $user->refresh();

        $response = $mw->handle(Request::create('/dashboard-utama', 'GET'), fn () => response('ok', 200));

        $this->assertSame(302, $response->getStatusCode());
        $this->assertStringContainsString('/login', $response->headers->get('Location'));
        $this->assertGuest();
    }
}