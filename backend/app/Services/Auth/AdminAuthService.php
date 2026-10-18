<?php

namespace App\Services\Auth;

use App\Enums\AdminStatus;
use App\Enums\ErrorCode;
use App\Exceptions\BusinessException;
use App\Models\AdminUser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use PragmaRX\Google2FA\Google2FA;

/**
 * Signs administrators in to the Web Admin (AD-01).
 *
 * - Step 1: email + password. Step 2: 6-digit TOTP code when two-factor authentication is enabled.
 * - 5 failed attempts lock the email + IP for 15 minutes (shared LoginThrottle settings).
 * - Every failure increments `admin_users.failed_login_count`; success resets it and stores
 *   `last_login_at`.
 *
 * @author PH.Tuan
 * @created 2026-10-18
 */
class AdminAuthService
{
    /** Throttle channel name for administrator sign-in. */
    private const THROTTLE_CHANNEL = 'admin';

    /** Accepted clock drift for TOTP codes, in 30-second windows before and after "now". */
    private const TOTP_WINDOW = 1;

    /**
     * @param  LoginThrottle  $loginThrottle  Locks sign-in after repeated failures.
     * @param  Google2FA  $google2fa  TOTP verifier (RFC 6238).
     */
    public function __construct(
        private readonly LoginThrottle $loginThrottle,
        private readonly Google2FA $google2fa,
    ) {}

    /**
     * Verify the credentials (and TOTP code) and start an authenticated admin session.
     *
     * - An unknown email and a wrong password return the same INVALID_CREDENTIALS error.
     * - A missing or wrong TOTP code counts as a failed attempt.
     *
     * @param  string  $email  Administrator email address.
     * @param  string  $password  Plain-text password.
     * @param  string|null  $totpCode  6-digit code from the authenticator app, if two-factor is enabled.
     * @param  string  $ipAddress  Client IP address, used for throttling.
     *
     * @return AdminUser The signed-in administrator.
     *
     * @throws BusinessException INVALID_CREDENTIALS, INVALID_MFA_CODE, ACCOUNT_LOCKED or LOGIN_THROTTLED.
     */
    public function login(string $email, string $password, ?string $totpCode, string $ipAddress): AdminUser
    {
        $throttleKey = $this->loginThrottle->key(self::THROTTLE_CHANNEL, $email, $ipAddress);
        $this->loginThrottle->ensureNotLocked($throttleKey);

        $administrator = AdminUser::query()
            ->whereRaw('lower(email) = ?', [mb_strtolower(trim($email))])
            ->first();

        if ($administrator === null || ! Hash::check($password, $administrator->password_hash)) {
            $this->failAttempt($throttleKey, $administrator, ErrorCode::InvalidCredentials);
        }

        if ($administrator->hasTwoFactorEnabled() && ! $this->isValidTotpCode($administrator, $totpCode)) {
            $this->failAttempt($throttleKey, $administrator, ErrorCode::InvalidMfaCode);
        }

        if ($administrator->status === AdminStatus::Locked) {
            throw new BusinessException(ErrorCode::AccountLocked);
        }

        $this->loginThrottle->clear($throttleKey);
        $administrator->forceFill(['failed_login_count' => 0, 'last_login_at' => now()])->save();

        Auth::guard('admin')->login($administrator);

        return $administrator;
    }

    /**
     * End the administrator's session.
     *
     * @return void
     */
    public function logout(): void
    {
        Auth::guard('admin')->logout();
    }

    /**
     * Check a TOTP code against the administrator's secret.
     *
     * @param  AdminUser  $administrator  Administrator with two-factor authentication enabled.
     * @param  string|null  $totpCode  Code typed by the administrator.
     *
     * @return bool True when the code is valid for the current time window.
     */
    private function isValidTotpCode(AdminUser $administrator, ?string $totpCode): bool
    {
        if ($totpCode === null || preg_match('/^\d{6}$/', $totpCode) !== 1) {
            return false;
        }

        return (bool) $this->google2fa->verifyKey((string) $administrator->mfa_secret, $totpCode, self::TOTP_WINDOW);
    }

    /**
     * Record a failed attempt and stop the sign-in.
     *
     * @param  string  $throttleKey  Throttle key of the email + IP.
     * @param  AdminUser|null  $administrator  Matching account, or null when the email is unknown.
     * @param  ErrorCode  $errorCode  INVALID_CREDENTIALS or INVALID_MFA_CODE.
     *
     * @return never
     *
     * @throws BusinessException Always, with `remaining_attempts` in the details.
     */
    private function failAttempt(string $throttleKey, ?AdminUser $administrator, ErrorCode $errorCode): never
    {
        $remainingAttempts = $this->loginThrottle->recordFailure($throttleKey);
        $administrator?->increment('failed_login_count');

        throw new BusinessException(
            $errorCode,
            $errorCode->message(['remaining' => $remainingAttempts]),
            ['remaining_attempts' => $remainingAttempts],
        );
    }
}
