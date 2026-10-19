<?php

namespace App\Http\Requests\Api\V1\Booking;

use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Stay dates and guests used by the search and room list (MB-04, MB-05) and by booking creation (BR-01).
 *
 * @author PH.Tuan
 * @created 2026-10-19
 */
class StayRequest extends FormRequest
{
    /** Longest stay accepted (BR-01, system_settings booking.max_nights). */
    public const MAX_NIGHTS = 30;

    /**
     * Determine whether the request is authorized.
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
            'check_in' => ['required', 'date_format:Y-m-d', 'after_or_equal:today', 'before:+365 days'],
            'check_out' => ['required', 'date_format:Y-m-d', 'after:check_in', 'before_or_equal:'.$this->latestCheckOut()],
            'adults' => ['required', 'integer', 'between:1,6'],
            'children' => ['nullable', 'integer', 'between:0,4'],
        ];
    }

    /**
     * Get the arrival day.
     *
     * @return CarbonImmutable
     */
    public function checkIn(): CarbonImmutable
    {
        return CarbonImmutable::parse($this->string('check_in')->toString())->startOfDay();
    }

    /**
     * Get the departure day.
     *
     * @return CarbonImmutable
     */
    public function checkOut(): CarbonImmutable
    {
        return CarbonImmutable::parse($this->string('check_out')->toString())->startOfDay();
    }

    /**
     * Latest allowed departure (check-in + MAX_NIGHTS), or a far date while check_in is invalid.
     *
     * @return string Y-m-d.
     */
    private function latestCheckOut(): string
    {
        $checkIn = strtotime((string) $this->input('check_in'));

        return date('Y-m-d', ($checkIn ?: strtotime('+2 years')) + self::MAX_NIGHTS * 86400);
    }
}
