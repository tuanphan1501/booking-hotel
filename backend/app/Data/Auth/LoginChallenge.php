<?php

namespace App\Data\Auth;

use Carbon\CarbonImmutable;

/**
 * Second step of a Web Admin sign-in that is waiting for a verification code (AD-01b).
 *
 * - Created after the email + password were accepted; stored in the cache, its id in the session.
 * - `method` is "email" (6-digit code mailed to the administrator) or "totp" (authenticator app).
 *
 * @author PH.Tuan
 * @created 2026-10-19
 */
final readonly class LoginChallenge
{
    /** The code was sent by email. */
    public const METHOD_EMAIL = 'email';

    /** The code comes from the authenticator app (two-factor enabled on the account). */
    public const METHOD_TOTP = 'totp';

    /**
     * @param  string  $id  Random challenge id kept in the session.
     * @param  int  $administratorId  Administrator who passed the password step.
     * @param  string  $method  METHOD_EMAIL or METHOD_TOTP.
     * @param  string  $maskedEmail  Address shown on the screen, e.g. "le•••h@vivustay.vn".
     * @param  CarbonImmutable  $expiresAt  When the code (and the challenge) stops being valid.
     * @param  CarbonImmutable|null  $resendAvailableAt  Earliest time a new email code can be requested.
     */
    public function __construct(
        public string $id,
        public int $administratorId,
        public string $method,
        public string $maskedEmail,
        public CarbonImmutable $expiresAt,
        public ?CarbonImmutable $resendAvailableAt,
    ) {}

    /**
     * Determine whether the code was sent by email.
     *
     * @return bool
     */
    public function usesEmail(): bool
    {
        return $this->method === self::METHOD_EMAIL;
    }

    /**
     * Seconds left before a new email code can be requested.
     *
     * @return int 0 when a new code can be sent now.
     */
    public function resendWaitSeconds(): int
    {
        if ($this->resendAvailableAt === null) {
            return 0;
        }

        return max(0, (int) ceil(now()->diffInSeconds($this->resendAvailableAt, false)));
    }
}
