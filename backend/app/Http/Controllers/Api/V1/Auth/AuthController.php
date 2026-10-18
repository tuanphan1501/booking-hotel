<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\LoginRequest;
use App\Http\Requests\Api\V1\Auth\RefreshTokenRequest;
use App\Http\Resources\Api\V1\AuthTokenResource;
use App\Models\User;
use App\Services\Auth\CustomerAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Laravel\Sanctum\PersonalAccessToken;

/**
 * Mobile App authentication endpoints (MB-02).
 *
 * @author PH.Tuan
 * @created 2026-10-18
 */
class AuthController extends Controller
{
    /**
     * @param  CustomerAuthService  $customerAuthService  Sign-in and token management.
     */
    public function __construct(private readonly CustomerAuthService $customerAuthService) {}

    /**
     * Sign in with email/phone and password.
     *
     * - POST /api/v1/auth/login
     * - 200: token pair + customer profile. 401 INVALID_CREDENTIALS (with remaining attempts),
     *   403 ACCOUNT_LOCKED / ACCOUNT_WITHDRAWN, 429 LOGIN_THROTTLED, 422 VALIDATION_ERROR.
     *
     * @param  LoginRequest  $request  Validated credentials.
     *
     * @return JsonResponse
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $issuedTokens = $this->customerAuthService->login(
            loginIdentifier: $request->string('login')->toString(),
            password: $request->string('password')->toString(),
            deviceName: $request->deviceName(),
            ipAddress: (string) $request->ip(),
        );

        return (new AuthTokenResource($issuedTokens))->response();
    }

    /**
     * Exchange a refresh token for a new token pair.
     *
     * - POST /api/v1/auth/refresh
     * - The used refresh token and the device's access token are revoked (rotation).
     * - 401 INVALID_REFRESH_TOKEN when the token is unknown, expired or already used.
     *
     * @param  RefreshTokenRequest  $request  Validated refresh token.
     *
     * @return JsonResponse
     */
    public function refresh(RefreshTokenRequest $request): JsonResponse
    {
        $issuedTokens = $this->customerAuthService->refresh($request->string('refresh_token')->toString());

        return (new AuthTokenResource($issuedTokens))->response();
    }

    /**
     * Sign out on the current device.
     *
     * - POST /api/v1/auth/logout (Bearer access token required)
     * - Revokes the access and refresh tokens of this device only.
     *
     * @param  Request  $request  Authenticated request.
     *
     * @return Response 204 No Content.
     */
    public function logout(Request $request): Response
    {
        /** @var User $customer */
        $customer = $request->user();
        /** @var PersonalAccessToken $currentAccessToken */
        $currentAccessToken = $customer->currentAccessToken();

        $this->customerAuthService->logout($customer, $currentAccessToken);

        return response()->noContent();
    }
}
