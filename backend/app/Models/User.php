<?php

namespace App\Models;

use App\Enums\Gender;
use App\Enums\SocialProvider;
use App\Enums\UserStatus;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\HasApiTokens;

/**
 * Customer (member) account stored in the `users` table.
 *
 * - Signs in from the Mobile App and receives Sanctum tokens (guard "sanctum").
 * - The password is stored in `password_hash` (bcrypt), as defined in the DB design.
 * - Soft-deleted; withdrawn customers keep anonymized booking history.
 *
 * @property int $id
 * @property string $member_code
 * @property string|null $email
 * @property string|null $phone
 * @property string|null $password_hash
 * @property string $full_name
 * @property string $language
 * @property int $member_tier_id
 * @property int $point_balance
 * @property UserStatus $status
 * @property Carbon|null $last_login_at
 * @property-read MemberTier $memberTier
 *
 * @author PH.Tuan
 * @created 2026-10-18
 */
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * Name of the password column used by the authentication layer.
     *
     * @var string
     */
    protected $authPasswordName = 'password_hash';

    /**
     * Attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'member_code', 'email', 'phone', 'password_hash', 'full_name', 'avatar_url', 'date_of_birth',
        'gender', 'nationality', 'language', 'member_tier_id', 'social_provider', 'social_id', 'status',
    ];

    /**
     * Attributes hidden from arrays and JSON.
     *
     * @var list<string>
     */
    protected $hidden = ['password_hash', 'social_id'];

    /**
     * Get the attribute casts.
     *
     * - `password_hash` is hashed automatically on assignment.
     * - Code columns are cast to their enums.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password_hash' => 'hashed',
            'status' => UserStatus::class,
            'gender' => Gender::class,
            'social_provider' => SocialProvider::class,
            'date_of_birth' => 'date',
            'point_balance' => 'integer',
            'spending_12m' => 'integer',
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
        ];
    }

    /**
     * Disable the "remember me" token column.
     *
     * - The `users` table has no `remember_token` column; mobile sessions use refresh tokens instead.
     *
     * @return string Empty string, which tells Laravel the feature is not used.
     */
    public function getRememberTokenName(): string
    {
        return '';
    }

    /**
     * Get the membership tier of the customer.
     *
     * @return BelongsTo<MemberTier, $this>
     */
    public function memberTier(): BelongsTo
    {
        return $this->belongsTo(MemberTier::class);
    }
}
