<?php

namespace App\Http\Resources\Api\V1;

use App\Data\Auth\IssuedTokens;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Token pair returned by POST /api/v1/auth/login and POST /api/v1/auth/refresh.
 *
 * @property IssuedTokens $resource
 *
 * @author PH.Tuan
 * @created 2026-10-18
 */
class AuthTokenResource extends JsonResource
{
    /**
     * Transform the issued tokens into an array.
     *
     * - Expiry times are ISO 8601 strings so the app can refresh before the access token expires.
     *
     * @param  Request  $request  Current request.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'token_type' => 'Bearer',
            'access_token' => $this->resource->accessToken,
            'access_token_expires_at' => $this->resource->accessTokenExpiresAt->toIso8601String(),
            'refresh_token' => $this->resource->refreshToken,
            'refresh_token_expires_at' => $this->resource->refreshTokenExpiresAt->toIso8601String(),
            'user' => new CustomerResource($this->resource->user),
        ];
    }
}
