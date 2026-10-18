<?php

namespace App\Http\Requests\Admin\Profile;

use App\Data\Admin\AdminProfileData;
use App\Support\PhoneNumber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Validates PUT /admin/profile (AD-19 Personal settings — profile form).
 *
 * @author PH.Tuan
 * @created 2026-10-18
 */
class UpdateProfileRequest extends FormRequest
{
    /**
     * Determine whether the request is authorized.
     *
     * - Every signed-in administrator may edit their own profile ("auth:admin" route middleware).
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
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:20'],
            'language' => ['required', Rule::in(config('booking.locales'))],
        ];
    }

    /**
     * Add checks that need more than a single rule.
     *
     * - The phone number must be convertible to E.164 (Vietnamese "0…" or international "+…").
     *
     * @return list<callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $rawPhone = $this->string('phone')->trim()->toString();

                if ($rawPhone !== '' && PhoneNumber::toE164($rawPhone) === null) {
                    $validator->errors()->add('phone', __('validation.phone'));
                }
            },
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
            'full_name' => __('admin.profile.full_name'),
            'phone' => __('admin.profile.phone'),
            'language' => __('admin.profile.language'),
        ];
    }

    /**
     * Convert the validated input into profile data.
     *
     * @return AdminProfileData
     */
    public function toProfileData(): AdminProfileData
    {
        $rawPhone = $this->string('phone')->trim()->toString();

        return new AdminProfileData(
            fullName: $this->string('full_name')->trim()->toString(),
            phoneNumber: $rawPhone === '' ? null : PhoneNumber::toE164($rawPhone),
            language: $this->string('language')->toString(),
        );
    }
}
