<?php

namespace App\Services\Auth;

use App\Enums\ErrorCode;
use App\Exceptions\BusinessException;
use Illuminate\Cache\RateLimiter;

/**
 * Temporarily locks sign-in after repeated failures (AD-01: 5 failures → locked for 15 minutes).
 *
 * - Attempts are counted per login identifier and client IP, so one attacker cannot lock out
 *   a customer who signs in from another network.
 * - Limits come from config('booking.auth.max_attempts') and config('booking.auth.lockout_minutes').
 *
 * @author PH.Tuan
 * @created 2026-10-18
 */
class LoginThrottle
{
    /**
     * @param  RateLimiter  $rateLimiter  Cache-backed attempt counter.
     */
    public function __construct(private readonly RateLimiter $rateLimiter) {}

    /**
     * Build the throttle key for one sign-in channel, identifier and IP address.
     *
     * @param  string  $channel  Sign-in channel, e.g. "customer" or "admin".
     * @param  string  $loginIdentifier  Email or phone number entered by the user.
     * @param  string  $ipAddress  Client IP address.
     *
     * @return string Cache key used by the rate limiter.
     */
    public function key(string $channel, string $loginIdentifier, string $ipAddress): string
    {
        return 'login:'.$channel.':'.sha1(mb_strtolower(trim($loginIdentifier)).'|'.$ipAddress);
    }

    /**
     * Stop the sign-in when the key is currently locked.
     *
     * @param  string  $throttleKey  Key built by key().
     *
     * @return void
     *
     * @throws BusinessException LOGIN_THROTTLED with `retry_after_seconds` in the details.
     */
    public function ensureNotLocked(string $throttleKey): void
    {
        if (! $this->rateLimiter->tooManyAttempts($throttleKey, $this->maxAttempts())) {
            return;
        }

        $retryAfterSeconds = $this->rateLimiter->availableIn($throttleKey);

        throw new BusinessException(
            ErrorCode::LoginThrottled,
            ErrorCode::LoginThrottled->message(['minutes' => (int) ceil($retryAfterSeconds / 60)]),
            ['retry_after_seconds' => $retryAfterSeconds],
        );
    }

    /**
     * Record one failed sign-in.
     *
     * @param  string  $throttleKey  Key built by key().
     *
     * @return int Attempts left before the key gets locked (0 when it is now locked).
     */
    public function recordFailure(string $throttleKey): int
    {
        $this->rateLimiter->hit($throttleKey, $this->lockoutMinutes() * 60);

        return max(0, $this->maxAttempts() - $this->rateLimiter->attempts($throttleKey));
    }

    /**
     * Reset the counter after a successful sign-in.
     *
     * @param  string  $throttleKey  Key built by key().
     *
     * @return void
     */
    public function clear(string $throttleKey): void
    {
        $this->rateLimiter->clear($throttleKey);
    }

    /**
     * Get the number of failures allowed before locking.
     *
     * @return int
     */
    private function maxAttempts(): int
    {
        return (int) config('booking.auth.max_attempts');
    }

    /**
     * Get how long a locked key stays locked.
     *
     * @return int Minutes.
     */
    private function lockoutMinutes(): int
    {
        return (int) config('booking.auth.lockout_minutes');
    }
}
