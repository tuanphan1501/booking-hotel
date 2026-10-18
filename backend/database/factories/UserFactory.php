<?php

namespace Database\Factories;

use App\Enums\MemberTierCode;
use App\Enums\UserStatus;
use App\Models\MemberTier;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Builds customers for tests.
 *
 * - Requires the member tiers to be seeded (MemberTierSeeder).
 * - Default password: "password".
 *
 * @extends Factory<User>
 *
 * @author PH.Tuan
 * @created 2026-10-18
 */
class UserFactory extends Factory
{
    /**
     * Model created by the factory.
     *
     * @var class-string<User>
     */
    protected $model = User::class;

    /**
     * Define the default attributes of an active MEMBER-tier customer.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'member_code' => 'M'.fake()->unique()->numerify('########'),
            'email' => fake()->unique()->safeEmail(),
            'phone' => '+849'.fake()->unique()->numerify('########'),
            'password_hash' => 'password',
            'full_name' => fake()->name(),
            'language' => 'vi',
            'member_tier_id' => fn (): int => MemberTier::query()->where('code', MemberTierCode::Member->value)->valueOrFail('id'),
            'status' => UserStatus::Active,
        ];
    }

    /**
     * Mark the customer as locked by an administrator.
     *
     * @return static
     */
    public function locked(): static
    {
        return $this->state(['status' => UserStatus::Locked]);
    }
}
