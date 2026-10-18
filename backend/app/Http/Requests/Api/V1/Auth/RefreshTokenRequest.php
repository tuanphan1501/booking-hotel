<?php

namespace App\Http\Requests\Api\V1\Auth;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates POST /api/v1/auth/refresh (exchange a refresh token for a new token pair).
 *
 * @author PH.Tuan
 * @created 2026-10-18
 */
class RefreshTokenRequest extends FormRequest
{
    /**
     * Determine whether the request is authorized.
     *
     * - The refresh token itself is the credential, so no prior authentication is required.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules.
     *
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'refresh_token' => ['required', 'string', 'max:255'],
        ];
    }
}
