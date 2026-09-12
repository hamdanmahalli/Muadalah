<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Tests\TestCase;

class SatuDeviceLoginTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
        $this->withoutMiddleware(VerifyCsrfToken::class);
    }

    private function buatUser(array $overrides = []): User
    {
        return User::factory()->create(array_merge([
            'username' => 'guru_' . Str::random(6),
            'status' => 'Aktif',
        ], $overrides));
    }

    public function test_sesi_yang_bukan_pemilik_diarahkan_ke_login(): void
    {
        $user = $this->buatUser();
        $user->forceFill(['active_session_id' => 'sesi-pemilik-lama'])->save();

        $this->actingAs($user);

        $this->get('/dashboard-utama')
            ->assertRedirect('/login?sesi=berpindah');

        $this->assertGuest();
    }

    public function test_konflik_dengan_pilihan_tetap_di_perangkat_lama_membatalkan_login(): void
    {
        $user = $this->buatUser();

        // Sesi perangkat "lama" yang masih hidup di sisi server.
        $handler = app('session')->getHandler();
        $handler->write('sesi-lama-abcdef0123456789', serialize(['_token' => 'x', '_last_activity' => time()]));
        $user->forceFill(['active_session_id' => 'sesi-lama-abcdef0123456789'])->save();

        // Percobaan login dari perangkat kedua -> status konflik.
        $konflik = $this->postJson('/login', [
            'login_id' => $user->username,
            'password' => 'password',
        ]);

        $konflik->assertOk()->assertJson(['status' => 'konflik']);

        // Pengguna memilih "tetap di perangkat lama".
        $cookie = collect($konflik->headers->getCookies())
            ->first(fn ($c) => $c->getName() === config('session.cookie'));

        $tetap = $this->withCookie(config('session.cookie'), $cookie?->getValue())
            ->postJson('/login/keputusan-device', ['keputusan' => 'tetap_lama']);

        $tetap->assertOk()->assertJson(['status' => 'tetap_lama']);

        // Kepemilikan sesi lama tidak berubah & perangkat baru tetap guest.
        $this->assertSame('sesi-lama-abcdef0123456789', $user->fresh()->active_session_id);
        $this->assertGuest();
    }
}