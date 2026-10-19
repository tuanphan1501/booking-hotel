<?php

namespace Tests\Feature\Admin\Auth;

use App\Enums\RoleCode;
use App\Mail\AdminLoginCodeMail;
use App\Models\AdminUser;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
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

    public function test_administrator_signs_in_with_the_code_sent_by_email(): void
    {
        Mail::fake();
        $administrator = AdminUser::factory()->create(['email' => 'le.hanh@vivustay.vn', 'full_name' => 'Lê Hạnh']);

        $this->post('/admin/login', ['email' => 'Le.Hanh@vivustay.vn', 'password' => 'password'])
            ->assertRedirect('/admin/login/verify');
        $this->assertGuest('admin');

        $code = null;
        Mail::assertSent(AdminLoginCodeMail::class, function (AdminLoginCodeMail $mail) use (&$code): bool {
            $code = $mail->code;

            return $mail->hasTo('le.hanh@vivustay.vn');
        });

        $this->get('/admin/login/verify')->assertOk()->assertSee('le•••h@vivustay.vn');

        $this->post('/admin/login/verify', ['code' => $code])->assertRedirect('/admin');
        $this->assertAuthenticatedAs($administrator, 'admin');
        $this->get('/admin')->assertOk()->assertSee('Xin chào, Lê Hạnh')->assertSee('LH');
        $this->assertNotNull($administrator->fresh()->last_login_at);
    }

    public function test_wrong_email_code_is_rejected_and_the_step_is_cancelled_after_five_tries(): void
    {
        Mail::fake();
        AdminUser::factory()->create(['email' => 'phtuancntt@gmail.com']);

        $this->post('/admin/login', ['email' => 'phtuancntt@gmail.com', 'password' => 'password']);
        Mail::assertSent(AdminLoginCodeMail::class);

        for ($attempt = 1; $attempt <= 4; $attempt++) {
            $this->from('/admin/login/verify')->post('/admin/login/verify', ['code' => '000000'])
                ->assertRedirect('/admin/login/verify')
                ->assertSessionHasErrors('code');
        }

        $this->post('/admin/login/verify', ['code' => '000000'])->assertRedirect('/admin/login');
        $this->get('/admin/login/verify')->assertRedirect('/admin/login');
        $this->assertGuest('admin');
    }

    public function test_resend_waits_for_the_cooldown(): void
    {
        Mail::fake();
        AdminUser::factory()->create(['email' => 'le.hanh@vivustay.vn']);
        $this->post('/admin/login', ['email' => 'le.hanh@vivustay.vn', 'password' => 'password']);

        $this->from('/admin/login/verify')->post('/admin/login/resend')->assertSessionHasErrors('code');

        $this->travel(61)->seconds();
        $this->from('/admin/login/verify')->post('/admin/login/resend')->assertSessionHas('status');
        Mail::assertSentCount(2);
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

    public function test_two_factor_accounts_verify_with_the_authenticator_instead_of_email(): void
    {
        Mail::fake();
        $google2fa = new Google2FA;
        $totpSecret = $google2fa->generateSecretKey();
        AdminUser::factory()->withTwoFactor($totpSecret)->create(['email' => 'secure@vivustay.vn']);

        $this->post('/admin/login', ['email' => 'secure@vivustay.vn', 'password' => 'password'])
            ->assertRedirect('/admin/login/verify');
        Mail::assertNothingSent();

        $this->from('/admin/login/verify')->post('/admin/login/verify', ['code' => '123456'])->assertSessionHasErrors('code');
        $this->assertGuest('admin');

        $this->post('/admin/login/verify', ['code' => $google2fa->getCurrentOtp($totpSecret)])->assertRedirect('/admin');
        $this->assertAuthenticated('admin');
    }

    public function test_google_sign_in_is_hidden_until_configured(): void
    {
        config(['services.google.client_id' => null]);
        $this->get('/admin/login')->assertDontSee('Đăng nhập bằng Google');
        $this->get('/admin/auth/google')->assertNotFound();

        config(['services.google.client_id' => 'id', 'services.google.client_secret' => 'secret']);
        $this->get('/admin/login')->assertSee('Đăng nhập bằng Google');
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
