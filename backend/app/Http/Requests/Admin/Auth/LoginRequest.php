<?php

namespace App\Http\Requests\Admin\Auth;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates the Web Admin sign-in form (AD-01).
 *
 * @author PH.Tuan
 * @created 2026-10-18
 */
class LoginRequest extends FormRequest
{
    /**
     * Determine whether the request is authorized.
     *
     * - Sign-in is available to guests only (enforced by the "guest:admin" route middleware).
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
     * - `totp_code` is optional here; AdminAuthService requires it when two-factor is enabled.
     *
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', 'max:255'],
            'totp_code' => ['nullable', 'digits:6'],
        ];
    }

    /**
     * Get the display names of the fields used in validation messages.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'email' => __('admin.auth.email'),
            'password' => __('admin.auth.password'),
            'totp_code' => __('admin.auth.totp_code'),
        ];
    }
}
