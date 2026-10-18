<?php

namespace App\Models;

use App\Enums\AdminStatus;
use Database\Factories\AdminUserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * Administrator account stored in the `admin_users` table.
 *
 * - Signs in to the Web Admin with a session (guard "admin").
 * - `hotel_id` = NULL means the account can act on every hotel (Basic Design §10).
 * - When `mfa_secret` is set, sign-in also requires a 6-digit TOTP code (AD-01).
 *
 * @property int $id
 * @property int|null $hotel_id
 * @property int $role_id
 * @property string $email
 * @property string $password_hash
 * @property string $full_name
 * @property string|null $phone
 * @property string $language
 * @property AdminStatus $status
 * @property int $failed_login_count
 * @property string|null $mfa_secret
 * @property string|null $avatar_path
 * @property Carbon|null $last_login_at
 * @property-read Role $role
 * @property-read Hotel|null $hotel
 *
 * @author PH.Tuan
 * @created 2026-10-18
 */
class AdminUser extends Authenticatable
{
    /** @use HasFactory<AdminUserFactory> */
    use HasFactory, SoftDeletes;

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
        'hotel_id', 'role_id', 'email', 'password_hash', 'full_name', 'phone', 'language', 'status', 'mfa_secret',
        'avatar_path',
    ];

    /**
     * Attributes hidden from arrays and JSON.
     *
     * @var list<string>
     */
    protected $hidden = ['password_hash', 'mfa_secret'];

    /**
     * Get the attribute casts.
     *
     * - `password_hash` is hashed automatically on assignment.
     * - `mfa_secret` is encrypted at rest with the application key.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password_hash' => 'hashed',
            'mfa_secret' => 'encrypted',
            'status' => AdminStatus::class,
            'failed_login_count' => 'integer',
            'last_login_at' => 'datetime',
        ];
    }

    /**
     * Disable the "remember me" token column.
     *
     * - The `admin_users` table has no `remember_token` column; admin sessions always expire.
     *
     * @return string Empty string, which tells Laravel the feature is not used.
     */
    public function getRememberTokenName(): string
    {
        return '';
    }

    /**
     * Get the role of the administrator.
     *
     * @return BelongsTo<Role, $this>
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Get the hotel the administrator belongs to (null for head-office accounts).
     *
     * @return BelongsTo<Hotel, $this>
     */
    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }

    /**
     * Get the public URL of the profile picture.
     *
     * @return string|null Null when no picture was uploaded (the initials badge is shown instead).
     */
    public function avatarUrl(): ?string
    {
        return $this->avatar_path === null ? null : Storage::disk('public')->url($this->avatar_path);
    }

    /**
     * Determine whether the administrator's role grants a permission.
     *
     * - Loads the role and its permissions once per model instance.
     *
     * @param  string  $permissionCode  Permission code, e.g. "booking.view".
     *
     * @return bool
     */
    public function hasPermission(string $permissionCode): bool
    {
        $this->loadMissing('role.permissions');

        return $this->role->permissions->contains('code', $permissionCode);
    }

    /**
     * Determine whether two-factor authentication is enabled.
     *
     * @return bool True when a TOTP secret is configured.
     */
    public function hasTwoFactorEnabled(): bool
    {
        return filled($this->mfa_secret);
    }

    /**
     * Get the initials shown in the avatar badge.
     *
     * - Uses the first letter of the first and last words, e.g. "Lê Hạnh" → "LH".
     *
     * @return string Upper-case initials.
     */
    public function initials(): string
    {
        $nameParts = preg_split('/\s+/u', trim($this->full_name)) ?: [];

        return mb_strtoupper(mb_substr($nameParts[0] ?? '', 0, 1).mb_substr((string) end($nameParts), 0, 1));
    }
}
