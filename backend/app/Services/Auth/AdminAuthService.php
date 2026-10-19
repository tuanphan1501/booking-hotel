<?php

namespace App\Services\Auth;

use App\Data\Auth\LoginChallenge;
use App\Enums\AdminStatus;
use App\Enums\ErrorCode;
use App\Exceptions\BusinessException;
use App\Mail\AdminLoginCodeMail;
use App\Models\AdminUser;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;

/**
 * Signs administrators in to the Web Admin in two steps (AD-01 → AD-01b).
 *
 * - Step 1: email + password. Step 2: 6-digit verification code —
 *   from the authenticator app when two-factor (TOTP) is enabled, otherwise sent by email.
 * - 5 failed attempts (password or code) lock the email + IP for 15 minutes (shared LoginThrottle settings).
 * - An email code is valid for 10 minutes and accepts 5 wrong entries; a new code can be requested
 *   after 60 seconds, at most 5 times per sign-in.
 * - Every failure increments `admin_users.failed_login_count`; success resets it and stores `last_login_at`.
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

    /** Minutes a verification step stays open. */
    private const CODE_TTL_MINUTES = 10;

    /** Wrong codes accepted before the verification step is cancelled. */
    private const MAX_CODE_ATTEMPTS = 5;

    /** Seconds between two email codes. */
    private const RESEND_COOLDOWN_SECONDS = 60;

    /** Email codes that can be requested again for one sign-in. */
    private const MAX_RESENDS = 5;

    /** Cache key prefix of pending verification steps. */
    private const CACHE_PREFIX = 'admin-login-challenge:';

    /**
     * @param  LoginThrottle  $loginThrottle  Locks sign-in after repeated failures.
     * @param  Google2FA  $google2fa  TOTP verifier (RFC 6238).
     * @param  CacheRepository  $cache  Stores pending verification steps.
     */
    public function __construct(
        private readonly LoginThrottle $loginThrottle,
        private readonly Google2FA $google2fa,
        private readonly CacheRepository $cache,
    ) {}

    /**
     * Check the email + password and open the verification step.
     *
     * - An unknown email and a wrong password return the same INVALID_CREDENTIALS error.
     * - Sends the email code right away unless the account uses an authenticator app.
     *
     * @param  string  $email  Administrator email address.
     * @param  string  $password  Plain-text password.
     * @param  string  $ipAddress  Client IP address, used for throttling.
     *
     * @return LoginChallenge Verification step to keep in the session.
     *
     * @throws BusinessException INVALID_CREDENTIALS, ACCOUNT_LOCKED or LOGIN_THROTTLED.
     */
    public function startLogin(string $email, string $password, string $ipAddress): LoginChallenge
    {
        $throttleKey = $this->loginThrottle->key(self::THROTTLE_CHANNEL, $email, $ipAddress);
        $this->loginThrottle->ensureNotLocked($throttleKey);

        $administrator = AdminUser::query()
            ->whereRaw('lower(email) = ?', [mb_strtolower(trim($email))])
            ->first();

        if ($administrator === null || ! Hash::check($password, $administrator->password_hash)) {
            $this->failAttempt($throttleKey, $administrator, ErrorCode::InvalidCredentials);
        }

        if ($administrator->status === AdminStatus::Locked) {
            throw new BusinessException(ErrorCode::AccountLocked);
        }

        $method = $administrator->hasTwoFactorEnabled() ? LoginChallenge::METHOD_TOTP : LoginChallenge::METHOD_EMAIL;
        $state = [
            'administrator_id' => $administrator->id,
            'method' => $method,
            'email' => $administrator->email,
            'throttle_key' => $throttleKey,
            'code_hash' => null,
            'attempts' => 0,
            'resends' => 0,
            'expires_at' => now()->addMinutes(self::CODE_TTL_MINUTES)->getTimestamp(),
            'resend_at' => null,
        ];

        $challengeId = Str::random(40);

        if ($method === LoginChallenge::METHOD_EMAIL) {
            $state = $this->sendEmailCode($administrator, $state);
        }

        $this->store($challengeId, $state);

        return $this->toChallenge($challengeId, $state);
    }

    /**
     * Sign in with an email address already verified by Google ("Sign in with Google").
     *
     * - Only existing administrators can sign in; Google never creates an account.
     * - Google proves ownership of the mailbox, so no email code is sent; accounts with an
     *   authenticator app still have to enter their TOTP code.
     *
     * @param  string  $email  Verified email address returned by Google.
     *
     * @return LoginChallenge|null TOTP step to complete, or null when the administrator is now signed in.
     *
     * @throws BusinessException GOOGLE_ACCOUNT_NOT_ALLOWED or ACCOUNT_LOCKED.
     */
    public function loginWithGoogle(string $email): ?LoginChallenge
    {
        $administrator = AdminUser::query()
            ->whereRaw('lower(email) = ?', [mb_strtolower(trim($email))])
            ->first() ?? throw new BusinessException(ErrorCode::GoogleAccountNotAllowed);

        if ($administrator->status === AdminStatus::Locked) {
            throw new BusinessException(ErrorCode::AccountLocked);
        }

        if ($administrator->hasTwoFactorEnabled()) {
            $challengeId = Str::random(40);
            $state = [
                'administrator_id' => $administrator->id,
                'method' => LoginChallenge::METHOD_TOTP,
                'email' => $administrator->email,
                'throttle_key' => $this->loginThrottle->key(self::THROTTLE_CHANNEL, $administrator->email, 'google'),
                'code_hash' => null,
                'attempts' => 0,
                'resends' => 0,
                'expires_at' => now()->addMinutes(self::CODE_TTL_MINUTES)->getTimestamp(),
                'resend_at' => null,
            ];
            $this->store($challengeId, $state);

            return $this->toChallenge($challengeId, $state);
        }

        $administrator->forceFill(['failed_login_count' => 0, 'last_login_at' => now()])->save();
        Auth::guard('admin')->login($administrator);

        return null;
    }

    /**
     * Get a pending verification step.
     *
     * @param  string|null  $challengeId  Id kept in the session.
     *
     * @return LoginChallenge|null Null when missing, expired or cancelled.
     */
    public function pendingChallenge(?string $challengeId): ?LoginChallenge
    {
        $state = $this->load($challengeId);

        return $state === null ? null : $this->toChallenge((string) $challengeId, $state);
    }

    /**
     * Check the verification code and start an authenticated admin session.
     *
     * - A wrong code counts towards both the challenge limit and the email + IP lockout.
     * - After MAX_CODE_ATTEMPTS wrong codes the step is cancelled and the administrator starts over.
     *
     * @param  string|null  $challengeId  Id kept in the session.
     * @param  string  $code  6 digits typed by the administrator.
     *
     * @return AdminUser The signed-in administrator.
     *
     * @throws BusinessException VERIFICATION_EXPIRED, INVALID_VERIFICATION_CODE, INVALID_MFA_CODE,
     *                           ACCOUNT_LOCKED or LOGIN_THROTTLED.
     */
    public function verifyLogin(?string $challengeId, string $code): AdminUser
    {
        $state = $this->load($challengeId) ?? throw new BusinessException(ErrorCode::VerificationExpired);
        $this->loginThrottle->ensureNotLocked($state['throttle_key']);

        $administrator = AdminUser::query()->find($state['administrator_id']);

        if ($administrator === null || $administrator->status === AdminStatus::Locked) {
            $this->forget((string) $challengeId);

            throw new BusinessException($administrator === null ? ErrorCode::VerificationExpired : ErrorCode::AccountLocked);
        }

        if (! $this->isValidCode($administrator, $state, $code)) {
            $this->failCode((string) $challengeId, $state, $administrator);
        }

        $this->forget((string) $challengeId);
        $this->loginThrottle->clear($state['throttle_key']);
        $administrator->forceFill(['failed_login_count' => 0, 'last_login_at' => now()])->save();

        Auth::guard('admin')->login($administrator);

        return $administrator;
    }

    /**
     * Email a new code for a pending verification step.
     *
     * @param  string|null  $challengeId  Id kept in the session.
     *
     * @return LoginChallenge The step with its new expiry and cooldown.
     *
     * @throws BusinessException VERIFICATION_EXPIRED or RESEND_TOO_SOON.
     */
    public function resendCode(?string $challengeId): LoginChallenge
    {
        $state = $this->load($challengeId);
        $administrator = $state === null ? null : AdminUser::query()->find($state['administrator_id']);

        if ($state === null || $administrator === null || $state['method'] !== LoginChallenge::METHOD_EMAIL
            || $state['resends'] >= self::MAX_RESENDS) {
            throw new BusinessException(ErrorCode::VerificationExpired);
        }

        $waitSeconds = $this->toChallenge((string) $challengeId, $state)->resendWaitSeconds();

        if ($waitSeconds > 0) {
            throw new BusinessException(ErrorCode::ResendTooSoon, ErrorCode::ResendTooSoon->message(['seconds' => $waitSeconds]));
        }

        $state = $this->sendEmailCode($administrator, $state);
        $state['resends']++;
        $state['attempts'] = 0;
        $state['expires_at'] = now()->addMinutes(self::CODE_TTL_MINUTES)->getTimestamp();
        $this->store((string) $challengeId, $state);

        return $this->toChallenge((string) $challengeId, $state);
    }

    /**
     * Cancel a pending verification step (administrator chose "use another account").
     *
     * @param  string|null  $challengeId  Id kept in the session.
     *
     * @return void
     */
    public function cancelLogin(?string $challengeId): void
    {
        if ($challengeId !== null) {
            $this->forget($challengeId);
        }
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
     * Generate a 6-digit code, mail it and remember its hash.
     *
     * @param  AdminUser  $administrator  Recipient.
     * @param  array<string, mixed>  $state  Current challenge state.
     *
     * @return array<string, mixed> State with the new code hash and resend cooldown.
     */
    private function sendEmailCode(AdminUser $administrator, array $state): array
    {
        $code = str_pad((string) random_int(0, 999_999), 6, '0', STR_PAD_LEFT);

        Mail::to($administrator->email, $administrator->full_name)
            ->locale($administrator->language)
            ->send(new AdminLoginCodeMail($administrator, $code, self::CODE_TTL_MINUTES));

        $state['code_hash'] = $this->hashCode($code);
        $state['resend_at'] = now()->addSeconds(self::RESEND_COOLDOWN_SECONDS)->getTimestamp();

        return $state;
    }

    /**
     * Check a code against the email code hash or the TOTP secret.
     *
     * @param  AdminUser  $administrator  Administrator being verified.
     * @param  array<string, mixed>  $state  Challenge state.
     * @param  string  $code  Code typed by the administrator.
     *
     * @return bool
     */
    private function isValidCode(AdminUser $administrator, array $state, string $code): bool
    {
        if (preg_match('/^\d{6}$/', $code) !== 1) {
            return false;
        }

        if ($state['method'] === LoginChallenge::METHOD_TOTP) {
            return (bool) $this->google2fa->verifyKey((string) $administrator->mfa_secret, $code, self::TOTP_WINDOW);
        }

        return is_string($state['code_hash']) && hash_equals($state['code_hash'], $this->hashCode($code));
    }

    /**
     * Record a wrong code and stop the verification.
     *
     * @param  string  $challengeId  Challenge id.
     * @param  array<string, mixed>  $state  Challenge state.
     * @param  AdminUser  $administrator  Administrator being verified.
     *
     * @return never
     *
     * @throws BusinessException INVALID_VERIFICATION_CODE / INVALID_MFA_CODE, or VERIFICATION_EXPIRED on the last attempt.
     */
    private function failCode(string $challengeId, array $state, AdminUser $administrator): never
    {
        $this->loginThrottle->recordFailure($state['throttle_key']);
        $administrator->increment('failed_login_count');

        $state['attempts']++;
        $remainingAttempts = self::MAX_CODE_ATTEMPTS - $state['attempts'];

        if ($remainingAttempts <= 0) {
            $this->forget($challengeId);

            throw new BusinessException(ErrorCode::VerificationExpired);
        }

        $this->store($challengeId, $state);
        $errorCode = $state['method'] === LoginChallenge::METHOD_TOTP ? ErrorCode::InvalidMfaCode : ErrorCode::InvalidVerificationCode;

        throw new BusinessException(
            $errorCode,
            $errorCode->message(['remaining' => $remainingAttempts]),
            ['remaining_attempts' => $remainingAttempts],
        );
    }

    /**
     * Record a failed password attempt and stop the sign-in.
     *
     * @param  string  $throttleKey  Throttle key of the email + IP.
     * @param  AdminUser|null  $administrator  Matching account, or null when the email is unknown.
     * @param  ErrorCode  $errorCode  INVALID_CREDENTIALS.
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

    /**
     * Hash an email code with the application key so the cache never holds the plain code.
     *
     * @param  string  $code  6-digit code.
     *
     * @return string
     */
    private function hashCode(string $code): string
    {
        return hash_hmac('sha256', $code, (string) config('app.key'));
    }

    /**
     * Read a challenge state, ignoring expired ones.
     *
     * @param  string|null  $challengeId  Challenge id.
     *
     * @return array<string, mixed>|null
     */
    private function load(?string $challengeId): ?array
    {
        if ($challengeId === null || $challengeId === '') {
            return null;
        }

        $state = $this->cache->get(self::CACHE_PREFIX.$challengeId);

        return is_array($state) && $state['expires_at'] > now()->getTimestamp() ? $state : null;
    }

    /**
     * Save a challenge state until it expires.
     *
     * @param  string  $challengeId  Challenge id.
     * @param  array<string, mixed>  $state  Challenge state.
     *
     * @return void
     */
    private function store(string $challengeId, array $state): void
    {
        $this->cache->put(self::CACHE_PREFIX.$challengeId, $state, CarbonImmutable::createFromTimestamp($state['expires_at']));
    }

    /**
     * Delete a challenge.
     *
     * @param  string  $challengeId  Challenge id.
     *
     * @return void
     */
    private function forget(string $challengeId): void
    {
        $this->cache->forget(self::CACHE_PREFIX.$challengeId);
    }

    /**
     * Build the public view of a challenge state.
     *
     * @param  string  $challengeId  Challenge id.
     * @param  array<string, mixed>  $state  Challenge state.
     *
     * @return LoginChallenge
     */
    private function toChallenge(string $challengeId, array $state): LoginChallenge
    {
        return new LoginChallenge(
            id: $challengeId,
            administratorId: $state['administrator_id'],
            method: $state['method'],
            maskedEmail: self::maskEmail($state['email']),
            expiresAt: CarbonImmutable::createFromTimestamp($state['expires_at']),
            resendAvailableAt: $state['resend_at'] === null ? null : CarbonImmutable::createFromTimestamp($state['resend_at']),
        );
    }

    /**
     * Hide most of the local part of an email address, e.g. "le.hanh@vivustay.vn" → "le•••h@vivustay.vn".
     *
     * @param  string  $email  Address to mask.
     *
     * @return string
     */
    public static function maskEmail(string $email): string
    {
        [$localPart, $domain] = array_pad(explode('@', $email, 2), 2, '');
        $visibleStart = mb_substr($localPart, 0, min(2, max(1, mb_strlen($localPart) - 1)));
        $visibleEnd = mb_strlen($localPart) > 3 ? mb_substr($localPart, -1) : '';

        return $visibleStart.'•••'.$visibleEnd.'@'.$domain;
    }
}
