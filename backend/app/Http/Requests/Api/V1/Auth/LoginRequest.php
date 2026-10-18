<?php

namespace App\Http\Requests\Api\V1\Auth;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates POST /api/v1/auth/login (Mobile App sign-in, MB-02).
 *
 * @author PH.Tuan
 * @created 2026-10-18
 */
class LoginRequest extends FormRequest
{
    /**
     * Determine whether the request is authorized.
     *
     * - Sign-in is public.
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
     * - `login`: email address or phone number.
     * - `device_name`: optional label that groups the device's tokens; defaults to the User-Agent.
     *
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'login' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'max:255'],
            'device_name' => ['nullable', 'string', 'max:100'],
        ];
    }

    /**
     * Get the device label used to name the issued tokens.
     *
     * @return string
     */
    public function deviceName(): string
    {
        $deviceName = $this->string('device_name')->trim()->toString();

        return $deviceName !== '' ? $deviceName : mb_substr((string) $this->userAgent() ?: 'mobile-app', 0, 100);
    }
}
