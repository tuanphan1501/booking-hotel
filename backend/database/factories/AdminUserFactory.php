<?php

namespace Database\Factories;

use App\Enums\AdminStatus;
use App\Enums\RoleCode;
use App\Models\AdminUser;
use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Builds administrators for tests.
 *
 * - Requires the roles to be seeded (RoleAndPermissionSeeder).
 * - Default role: SUPER_ADMIN. Default password: "password".
 *
 * @extends Factory<AdminUser>
 *
 * @author PH.Tuan
 * @created 2026-10-18
 */
class AdminUserFactory extends Factory
{
    /**
     * Model created by the factory.
     *
     * @var class-string<AdminUser>
     */
    protected $model = AdminUser::class;

    /**
     * Define the default attributes of an active super administrator without two-factor.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'hotel_id' => null,
            'phone' => null,
            'role_id' => fn (): int => $this->roleId(RoleCode::SuperAdmin),
            'email' => fake()->unique()->userName().'@vivustay.vn',
            'password_hash' => 'password',
            'full_name' => fake()->name(),
            'language' => 'vi',
            'status' => AdminStatus::Active,
            'mfa_secret' => null,
            'avatar_path' => null,
            'last_login_at' => null,
        ];
    }

    /**
     * Give the administrator another role.
     *
     * @param  RoleCode  $roleCode  Role to assign.
     *
     * @return static
     */
    public function withRole(RoleCode $roleCode): static
    {
        return $this->state(fn (): array => ['role_id' => $this->roleId($roleCode)]);
    }

    /**
     * Enable two-factor authentication with the given TOTP secret.
     *
     * @param  string  $totpSecret  Base32 secret shared with the authenticator app.
     *
     * @return static
     */
    public function withTwoFactor(string $totpSecret): static
    {
        return $this->state(['mfa_secret' => $totpSecret]);
    }

    /**
     * Look up the id of a seeded role.
     *
     * @param  RoleCode  $roleCode  Role code.
     *
     * @return int
     */
    private function roleId(RoleCode $roleCode): int
    {
        return Role::query()->where('code', $roleCode->value)->valueOrFail('id');
    }
}
