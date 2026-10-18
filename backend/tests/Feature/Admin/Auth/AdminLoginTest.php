<?php

namespace Tests\Feature\Admin\Auth;

use App\Enums\RoleCode;
use App\Models\AdminUser;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

/**
 * Web Admin sign-in (AD-01), sign-out and permission-based sidebar.
 *
 * @author PH.Tuan
 * @created 2026-10-18
 */
class AdminLoginTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Seed the roles and permissions required by AdminUserFactory and the sidebar.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleAndPermissionSeeder::class);
    }

    public function test_guest_is_redirected_to_the_sign_in_screen(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
        $this->get('/admin/login')->assertOk()->assertSee('Đăng nhập');
    }

    public function test_administrator_signs_in_and_sees_the_dashboard(): void
    {
        $administrator = AdminUser::factory()->create(['email' => 'le.hanh@vivustay.vn', 'full_name' => 'Lê Hạnh']);

        $this->post('/admin/login', ['email' => 'Le.Hanh@vivustay.vn', 'password' => 'password'])
            ->assertRedirect('/admin');

        $this->assertAuthenticatedAs($administrator, 'admin');
        $this->get('/admin')->assertOk()->assertSee('Xin chào, Lê Hạnh')->assertSee('LH');
        $this->assertNotNull($administrator->fresh()->last_login_at);
    }

    public function test_wrong_password_shows_an_error_and_counts_the_failure(): void
    {
        $administrator = AdminUser::factory()->create(['email' => 'le.hanh@vivustay.vn']);

        $this->from('/admin/login')
            ->post('/admin/login', ['email' => 'le.hanh@vivustay.vn', 'password' => 'wrong-password'])
            ->assertRedirect('/admin/login')
            ->assertSessionHasErrors('email');

        $this->assertGuest('admin');
        $this->assertSame(1, $administrator->fresh()->failed_login_count);
    }

    public function test_two_factor_code_is_required_when_enabled(): void
    {
        $google2fa = new Google2FA;
        $totpSecret = $google2fa->generateSecretKey();
        AdminUser::factory()->withTwoFactor($totpSecret)->create(['email' => 'secure@vivustay.vn']);

        $this->from('/admin/login')
            ->post('/admin/login', ['email' => 'secure@vivustay.vn', 'password' => 'password'])
            ->assertSessionHasErrors('totp_code');
        $this->assertGuest('admin');

        $this->post('/admin/login', [
            'email' => 'secure@vivustay.vn',
            'password' => 'password',
            'totp_code' => $google2fa->getCurrentOtp($totpSecret),
        ])->assertRedirect('/admin');
        $this->assertAuthenticated('admin');
    }

    public function test_sidebar_only_lists_permitted_screens(): void
    {
        $receptionist = AdminUser::factory()->withRole(RoleCode::Receptionist)->create();

        $this->actingAs($receptionist, 'admin')->get('/admin')
            ->assertOk()
            ->assertSee('Đặt phòng')
            ->assertDontSee('Báo cáo')
            ->assertDontSee('Cấu hình');
    }

    public function test_language_can_be_switched_before_sign_in(): void
    {
        $this->from('/admin/login')->post('/admin/language', ['locale' => 'ja'])->assertRedirect('/admin/login');

        $this->get('/admin/login')->assertSee('ログイン');
    }

    public function test_administrator_signs_out(): void
    {
        $administrator = AdminUser::factory()->create();

        $this->actingAs($administrator, 'admin')->post('/admin/logout')->assertRedirect('/admin/login');

        $this->assertGuest('admin');
    }
}
