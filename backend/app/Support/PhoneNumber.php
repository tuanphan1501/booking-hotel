<?php

namespace App\Support;

/**
 * Normalizes phone numbers to E.164 so they can be compared with `users.phone`.
 *
 * @author PH.Tuan
 * @created 2026-10-18
 */
final class PhoneNumber
{
    /** Country calling code used when a number starts with a national trunk prefix "0". */
    private const DEFAULT_COUNTRY_CODE = '84';

    /**
     * Convert a phone number to E.164.
     *
     * - Removes spaces, dots, dashes and parentheses.
     * - Replaces a leading "0" with the Vietnamese country code: "0901 234 567" → "+84901234567".
     * - Keeps numbers that already start with "+" (e.g. Japanese "+8190...").
     *
     * @param  string  $rawPhoneNumber  Phone number as typed by the user.
     *
     * @return string|null The E.164 number, or null when the input is not a phone number.
     */
    public static function toE164(string $rawPhoneNumber): ?string
    {
        $digitsWithPlus = preg_replace('/[\s.\-()]/', '', $rawPhoneNumber) ?? '';

        if (preg_match('/^\+\d{8,15}$/', $digitsWithPlus) === 1) {
            return $digitsWithPlus;
        }

        if (preg_match('/^0\d{9,10}$/', $digitsWithPlus) === 1) {
            return '+'.self::DEFAULT_COUNTRY_CODE.substr($digitsWithPlus, 1);
        }

        return null;
    }
}
