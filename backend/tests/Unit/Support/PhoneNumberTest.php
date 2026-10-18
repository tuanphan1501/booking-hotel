<?php

namespace Tests\Unit\Support;

use App\Support\PhoneNumber;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Phone number normalization to E.164.
 *
 * @author PH.Tuan
 * @created 2026-10-18
 */
class PhoneNumberTest extends TestCase
{
    /**
     * Inputs and their expected E.164 result (null = not a phone number).
     *
     * @return array<string, array{0: string, 1: string|null}>
     */
    public static function phoneNumbers(): array
    {
        return [
            'Vietnamese mobile with spaces' => ['0901 234 567', '+84901234567'],
            'Vietnamese mobile with dots' => ['090.123.4567', '+84901234567'],
            'international format' => ['+84 901 234 567', '+84901234567'],
            'Japanese international' => ['+81 90-1234-5678', '+819012345678'],
            'too short' => ['0901', null],
            'letters' => ['12ab', null],
            'email' => ['minh@example.com', null],
        ];
    }

    #[DataProvider('phoneNumbers')]
    public function test_converts_to_e164(string $rawPhoneNumber, ?string $expected): void
    {
        $this->assertSame($expected, PhoneNumber::toE164($rawPhoneNumber));
    }
}
