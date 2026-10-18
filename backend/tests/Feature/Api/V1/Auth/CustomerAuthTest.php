<?php

namespace Tests\Feature\Api\V1\Auth;

use App\Models\User;
use Database\Seeders\MemberTierSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

/**
 * Mobile App authentication: POST /api/v1/auth/login|refresh|logout and GET /api/v1/me.
 *
 * @author PH.Tuan
 * @created 2026-10-18
 */
class CustomerAuthTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Seed the member tiers required by UserFactory.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(MemberTierSeeder::class);
    }

    public function test_customer_signs_in_with_email_and_receives_a_token_pair(): void
    {
        $customer = User::factory()->create(['email' => 'minh.nguyen@gmail.com']);

        $response = $this->login('Minh.Nguyen@gmail.com', 'password');

        $response->assertOk()
            ->assertJsonPath('data.token_type', 'Bearer')
            ->assertJsonPath('data.user.id', $customer->id)
            ->assertJsonPath('data.user.member_tier.code', 'MEMBER')
            ->assertJsonStructure(['data' => ['access_token', 'access_token_expires_at', 'refresh_token', 'refresh_token_expires_at']]);
        $this->assertNotNull($customer->fresh()->last_login_at);
    }

    public function test_customer_signs_in_with_a_local_phone_number(): void
    {
        User::factory()->create(['phone' => '+84901234567']);

        $this->login('0901 234 567', 'password')->assertOk();
    }

    public function test_wrong_password_returns_invalid_credentials_with_remaining_attempts(): void
    {
        User::factory()->create(['email' => 'minh.nguyen@gmail.com']);

        $this->login('minh.nguyen@gmail.com', 'wrong-password')
            ->assertUnauthorized()
            ->assertJsonPath('code', 'INVALID_CREDENTIALS')
            ->assertJsonPath('details.remaining_attempts', 4);
    }

    public function test_unknown_account_returns_the_same_error_as_a_wrong_password(): void
    {
        $this->login('nobody@example.com', 'password')
            ->assertUnauthorized()
            ->assertJsonPath('code', 'INVALID_CREDENTIALS');
    }

    public function test_sign_in_is_locked_after_five_failures(): void
    {
        User::factory()->create(['email' => 'minh.nguyen@gmail.com']);

        foreach (range(1, 5) as $attempt) {
            $this->login('minh.nguyen@gmail.com', 'wrong-password');
        }

        $this->login('minh.nguyen@gmail.com', 'password')
            ->assertTooManyRequests()
            ->assertJsonPath('code', 'LOGIN_THROTTLED');
    }

    public function test_locked_account_cannot_sign_in(): void
    {
        User::factory()->locked()->create(['email' => 'locked@example.com']);

        $this->login('locked@example.com', 'password')
            ->assertForbidden()
            ->assertJsonPath('code', 'ACCOUNT_LOCKED');
    }

    public function test_missing_fields_return_validation_error_details(): void
    {
        $this->postJson('/api/v1/auth/login', [])
            ->assertUnprocessable()
            ->assertJsonPath('code', 'VALIDATION_ERROR')
            ->assertJsonStructure(['details' => ['fields' => ['login', 'password']]]);
    }

    public function test_error_messages_follow_accept_language(): void
    {
        $this->postJson('/api/v1/auth/login', ['login' => 'x@example.com', 'password' => 'x'], ['Accept-Language' => 'ja'])
            ->assertJsonPath('message', 'メールアドレス/電話番号またはパスワードが正しくありません。残り4回です。');
    }

    public function test_error_messages_are_available_in_english(): void
    {
        $this->postJson('/api/v1/auth/login', ['login' => 'x@example.com', 'password' => 'x'], ['Accept-Language' => 'en-US,en;q=0.9'])
            ->assertJsonPath('message', 'Incorrect email/phone number or password. 4 attempts left.');
    }

    public function test_access_token_reads_the_profile_but_refresh_token_does_not(): void
    {
        User::factory()->create(['email' => 'minh.nguyen@gmail.com']);
        $tokens = $this->login('minh.nguyen@gmail.com', 'password')->json('data');

        $this->withToken($tokens['access_token'])->getJson('/api/v1/me')
            ->assertOk()
            ->assertJsonPath('data.email', 'minh.nguyen@gmail.com');

        $this->app['auth']->forgetGuards();

        $this->withToken($tokens['refresh_token'])->getJson('/api/v1/me')
            ->assertForbidden()
            ->assertJsonPath('code', 'FORBIDDEN');
    }

    public function test_refresh_rotates_the_token_pair(): void
    {
        User::factory()->create(['email' => 'minh.nguyen@gmail.com']);
        $firstRefreshToken = $this->login('minh.nguyen@gmail.com', 'password')->json('data.refresh_token');

        $this->postJson('/api/v1/auth/refresh', ['refresh_token' => $firstRefreshToken])
            ->assertOk()
            ->assertJsonStructure(['data' => ['access_token', 'refresh_token']]);

        $this->postJson('/api/v1/auth/refresh', ['refresh_token' => $firstRefreshToken])
            ->assertUnauthorized()
            ->assertJsonPath('code', 'INVALID_REFRESH_TOKEN');
    }

    public function test_logout_revokes_the_device_tokens(): void
    {
        $customer = User::factory()->create(['email' => 'minh.nguyen@gmail.com']);
        $accessToken = $this->login('minh.nguyen@gmail.com', 'password')->json('data.access_token');

        $this->withToken($accessToken)->postJson('/api/v1/auth/logout')->assertNoContent();

        $this->assertSame(0, $customer->tokens()->count());
    }

    public function test_protected_route_without_token_returns_unauthenticated(): void
    {
        $this->getJson('/api/v1/me')
            ->assertUnauthorized()
            ->assertJsonPath('code', 'UNAUTHENTICATED');
    }

    /**
     * Call the sign-in endpoint from a fixed test device.
     *
     * @param  string  $loginIdentifier  Email address or phone number.
     * @param  string  $password  Plain-text password.
     *
     * @return TestResponse
     */
    private function login(string $loginIdentifier, string $password): TestResponse
    {
        return $this->postJson('/api/v1/auth/login', [
            'login' => $loginIdentifier,
            'password' => $password,
            'device_name' => 'phpunit-device',
        ]);
    }
}
