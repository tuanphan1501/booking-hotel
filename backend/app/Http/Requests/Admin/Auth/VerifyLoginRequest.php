<?php

namespace App\Http\Requests\Admin\Auth;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates the verification code form of the Web Admin sign-in (AD-01b).
 *
 * @author PH.Tuan
 * @created 2026-10-19
 */
class VerifyLoginRequest extends FormRequest
{
    /**
     * Determine whether the request is authorized (guests only, enforced by the route middleware).
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Strip the spaces some mail clients add when copying the code.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        $this->merge(['code' => preg_replace('/\D/', '', (string) $this->input('code'))]);
    }

    /**
     * Get the validation rules.
     *
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return ['code' => ['required', 'digits:6']];
    }

    /**
     * Get the display names of the fields used in validation messages.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return ['code' => __('admin.verify.code')];
    }
}
