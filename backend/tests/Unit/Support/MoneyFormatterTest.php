<?php

namespace Tests\Unit\Support;

use App\Support\Formatting\MoneyFormatter;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * VND formatting per locale (design rule: full number + "₫", compact only for KPIs).
 *
 * @author PH.Tuan
 * @created 2026-10-18
 */
class MoneyFormatterTest extends TestCase
{
    /**
     * Locale, amount, expected full format, expected compact format.
     *
     * @return array<string, array{0: string, 1: int, 2: string, 3: string}>
     */
    public static function amounts(): array
    {
        return [
            'vi' => ['vi', 3_415_400, '3.415.400 ₫', '3,4 tr ₫'],
            'ja' => ['ja', 3_415_400, '3,415,400₫', '3.4M₫'],
            'en' => ['en', 186_400_000, '186,400,000 ₫', '186.4M ₫'],
            'small amount stays full' => ['vi', 150_000, '150.000 ₫', '150.000 ₫'],
            'negative' => ['en', -100_000, '−100,000 ₫', '−100,000 ₫'],
        ];
    }

    #[DataProvider('amounts')]
    public function test_formats_amount_for_locale(string $locale, int $amount, string $expectedFull, string $expectedCompact): void
    {
        $this->app->setLocale($locale);
        $moneyFormatter = new MoneyFormatter;

        $this->assertSame($expectedFull, $moneyFormatter->full($amount));
        $this->assertSame($expectedCompact, $moneyFormatter->compact($amount));
    }
}
