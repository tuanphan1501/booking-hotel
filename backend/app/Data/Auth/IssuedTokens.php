<?php

namespace App\Data\Auth;

use App\Models\User;
use Carbon\CarbonInterface;

/**
 * Token pair returned to the Mobile App after sign-in or token refresh.
 *
 * - The access token authorizes API calls and expires quickly (default 15 minutes).
 * - The refresh token can only be exchanged for a new pair and expires later (default 30 days).
 *
 * @author PH.Tuan
 * @created 2026-10-18
 */
final readonly class IssuedTokens
{
    /**
     * @param  User  $user  Customer the tokens belong to.
     * @param  string  $accessToken  Plain-text access token (sent as "Bearer ...").
     * @param  CarbonInterface  $accessTokenExpiresAt  Expiry time of the access token.
     * @param  string  $refreshToken  Plain-text refresh token, stored in secure storage on the device.
     * @param  CarbonInterface  $refreshTokenExpiresAt  Expiry time of the refresh token.
     */
    public function __construct(
        public User $user,
        public string $accessToken,
        public CarbonInterface $accessTokenExpiresAt,
        public string $refreshToken,
        public CarbonInterface $refreshTokenExpiresAt,
    ) {}
}
