<?php

namespace Tests\Feature;

use App\Http\Controllers\RolePermissionController;
use App\Http\Middleware\MenegakkanSatuDevice;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Tests\TestCase;

class MenuAksesTest extends TestCase
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

    public function test_pohon_menu_memuat_kebendaharaan_dan_toko(): void
    {
        $grup = RolePermissionController::GRUP_MENU;

        $this->assertArrayHasKey('Kebendaharaan', $grup);
        $this->assertArrayHasKey('Toko Buku', $grup);

        $permbendaharaan = collect($grup['Kebendaharaan'])->map(fn ($i) => $i['perm'])->all();
        $this->assertContains('akses_kebendaharaan', $permbendaharaan);
        $this->assertContains('akses_pencairan', $permbendaharaan);
        $this->assertContains('akses_laporan_kebendaharaan', $permbendaharaan);

        // Sub permission di dalam pohon harus ada di whitelist agar tersimpan saat dicentang.
        $semuaPerm = [];
        foreach ($grup as $items) {
            foreach ($items as $it) {
                $semuaPerm[] = $it['perm'];
                foreach ($it['sub'] ?? [] as $sb) {
                    $semuaPerm[] = $sb['perm'];
                }
            }
        }

        foreach (array_unique($semuaPerm) as $p) {
            $this->assertContains($p, RolePermissionController::PERMISSIONS, "Permission {$p} belum masuk whitelist PERMISSIONS.");
        }
    }

    public function test_endpoint_pohon_menu_mengembalikan_grup(): void
    {
        $this->get(route('setup-user.menu-pohon'))
            ->assertOk()
            ->assertJsonPath('grup.Kebendaharaan', RolePermissionController::GRUP_MENU['Kebendaharaan'])
            ->assertJsonPath('grup.Toko Buku', RolePermissionController::GRUP_MENU['Toko Buku']);
    }
}