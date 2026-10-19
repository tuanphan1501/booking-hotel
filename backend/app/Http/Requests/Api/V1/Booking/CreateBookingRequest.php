<?php

namespace App\Http\Requests\Api\V1\Booking;

use App\Support\PhoneNumber;

/**
 * Validates POST /api/v1/bookings (MB-07 guest information).
 *
 * @author PH.Tuan
 * @created 2026-10-19
 */
class CreateBookingRequest extends StayRequest
{
    /**
     * Normalize the phone number to E.164 ("0901 234 567" → "+84901234567") before validating.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        $this->merge(['guest_phone' => PhoneNumber::toE164((string) $this->input('guest_phone')) ?? $this->input('guest_phone')]);
    }

    /**
     * Get the validation rules.
     *
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return parent::rules() + [
            'rate_plan_id' => ['required', 'integer', 'exists:rate_plans,id'],
            'guest_name' => ['required', 'string', 'max:100'],
            'guest_phone' => ['required', 'string', 'regex:/^\+\d{8,15}$/'],
            'guest_email' => ['required', 'email', 'max:255'],
            'estimated_arrival' => ['nullable', 'date_format:H:i'],
            'special_request' => ['nullable', 'string', 'max:500'],
        ];
    }
}
