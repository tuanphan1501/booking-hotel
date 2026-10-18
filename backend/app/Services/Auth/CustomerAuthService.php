<?php

namespace App\Services\Auth;

use App\Data\Auth\IssuedTokens;
use App\Enums\ErrorCode;
use App\Enums\UserStatus;
use App\Exceptions\BusinessException;
use App\Models\User;
use App\Support\PhoneNumber;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;

/**
 * Signs customers in to the Mobile App and manages their API tokens (MB-02).
 *
 * - Customers sign in with an email address or a phone number plus a password.
 * - Each device holds one token pair; tokens are named after the device so signing out on one
 *   device does not affect the others.
 * - Refresh tokens rotate: every refresh revokes the old pair and issues a new one.
 *
 * @author PH.Tuan
 * @created 2026-10-18
 */
class CustomerAuthService
{
    /** Sanctum ability carried by access tokens; required by every protected API route. */
    public const ACCESS_ABILITY = 'api';

    /** Sanctum ability carried by refresh tokens; only accepted by the refresh endpoint. */
    public const REFRESH_ABILITY = 'token:refresh';

    /** Throttle channel name for customer sign-in. */
    private const THROTTLE_CHANNEL = 'customer';

    /**
     * @param  LoginThrottle  $loginThrottle  Locks sign-in after repeated failures.
     */
    public function __construct(private readonly LoginThrottle $loginThrottle) {}

    /**
     * Verify the credentials and issue a new token pair.
     *
     * - Locks the identifier + IP after too many failures (LOGIN_THROTTLED).
     * - Returns the same INVALID_CREDENTIALS error for an unknown account and a wrong password,
     *   so the API does not reveal which emails or phone numbers are registered.
     * - Rejects locked and withdrawn accounts only after the password is verified.
     *
     * @param  string  $loginIdentifier  Email address or phone number entered by the customer.
     * @param  string  $password  Plain-text password.
     * @param  string  $deviceName  Device label used to group the device's tokens, e.g. "iPhone 15".
     * @param  string  $ipAddress  Client IP address, used for throttling.
     *
     * @return IssuedTokens
     *
     * @throws BusinessException INVALID_CREDENTIALS, LOGIN_THROTTLED, ACCOUNT_LOCKED or ACCOUNT_WITHDRAWN.
     */
    public function login(string $loginIdentifier, string $password, string $deviceName, string $ipAddress): IssuedTokens
    {
        $throttleKey = $this->loginThrottle->key(self::THROTTLE_CHANNEL, $loginIdentifier, $ipAddress);
        $this->loginThrottle->ensureNotLocked($throttleKey);

        $customer = $this->findByLoginIdentifier($loginIdentifier);

        if ($customer === null || $customer->password_hash === null || ! Hash::check($password, $customer->password_hash)) {
            $remainingAttempts = $this->loginThrottle->recordFailure($throttleKey);

            throw new BusinessException(
                ErrorCode::InvalidCredentials,
                ErrorCode::InvalidCredentials->message(['remaining' => $remainingAttempts]),
                ['remaining_attempts' => $remainingAttempts],
            );
        }

        $this->ensureCanSignIn($customer);
        $this->loginThrottle->clear($throttleKey);

        $customer->forceFill(['last_login_at' => now()])->save();

        return $this->issueTokens($customer, $deviceName);
    }

    /**
     * Exchange a refresh token for a new token pair (token rotation).
     *
     * @param  string  $plainRefreshToken  Refresh token received at sign-in or at the previous refresh.
     *
     * @return IssuedTokens
     *
     * @throws BusinessException INVALID_REFRESH_TOKEN when the token is unknown, expired or not a
     *                           refresh token; ACCOUNT_LOCKED / ACCOUNT_WITHDRAWN for disabled accounts.
     */
    public function refresh(string $plainRefreshToken): IssuedTokens
    {
        $refreshToken = PersonalAccessToken::findToken($plainRefreshToken);

        $isUsable = $refreshToken !== null
            && $refreshToken->can(self::REFRESH_ABILITY)
            && $refreshToken->expires_at?->isFuture()
            && $refreshToken->tokenable instanceof User;

        if (! $isUsable) {
            throw new BusinessException(ErrorCode::InvalidRefreshToken);
        }

        /** @var User $customer */
        $customer = $refreshToken->tokenable;
        $this->ensureCanSignIn($customer);

        return $this->issueTokens($customer, $refreshToken->name);
    }

    /**
     * Sign the customer out on the current device by revoking that device's tokens.
     *
     * @param  User  $customer  Authenticated customer.
     * @param  PersonalAccessToken  $currentAccessToken  Access token used for the current request.
     *
     * @return void
     */
    public function logout(User $customer, PersonalAccessToken $currentAccessToken): void
    {
        $customer->tokens()->where('name', $currentAccessToken->name)->delete();
    }

    /**
     * Find an active (not soft-deleted) customer by email or phone number.
     *
     * - Input containing "@" is matched case-insensitively against `users.email`.
     * - Anything else is normalized to E.164 and matched against `users.phone`.
     *
     * @param  string  $loginIdentifier  Email address or phone number.
     *
     * @return User|null
     */
    private function findByLoginIdentifier(string $loginIdentifier): ?User
    {
        $loginIdentifier = trim($loginIdentifier);

        if (str_contains($loginIdentifier, '@')) {
            return User::query()->whereRaw('lower(email) = ?', [mb_strtolower($loginIdentifier)])->first();
        }

        $phoneNumber = PhoneNumber::toE164($loginIdentifier);

        return $phoneNumber === null ? null : User::query()->where('phone', $phoneNumber)->first();
    }

    /**
     * Reject customers whose account status does not allow signing in.
     *
     * @param  User  $customer  Customer whose credentials are already verified.
     *
     * @return void
     *
     * @throws BusinessException ACCOUNT_LOCKED or ACCOUNT_WITHDRAWN.
     */
    private function ensureCanSignIn(User $customer): void
    {
        match ($customer->status) {
            UserStatus::Active => null,
            UserStatus::Locked => throw new BusinessException(ErrorCode::AccountLocked),
            UserStatus::Withdrawn => throw new BusinessException(ErrorCode::AccountWithdrawn),
        };
    }

    /**
     * Revoke the device's previous tokens and create a new access + refresh token pair.
     *
     * @param  User  $customer  Customer to issue the tokens for.
     * @param  string  $deviceName  Device label shared by both tokens.
     *
     * @return IssuedTokens
     */
    private function issueTokens(User $customer, string $deviceName): IssuedTokens
    {
        $customer->tokens()->where('name', $deviceName)->delete();

        $accessTokenExpiresAt = now()->addMinutes((int) config('booking.auth.access_token_minutes'));
        $refreshTokenExpiresAt = now()->addDays((int) config('booking.auth.refresh_token_days'));

        $accessToken = $customer->createToken($deviceName, [self::ACCESS_ABILITY], $accessTokenExpiresAt);
        $refreshToken = $customer->createToken($deviceName, [self::REFRESH_ABILITY], $refreshTokenExpiresAt);

        return new IssuedTokens(
            user: $customer->loadMissing('memberTier'),
            accessToken: $accessToken->plainTextToken,
            accessTokenExpiresAt: $accessTokenExpiresAt,
            refreshToken: $refreshToken->plainTextToken,
            refreshTokenExpiresAt: $refreshTokenExpiresAt,
        );
    }
}
