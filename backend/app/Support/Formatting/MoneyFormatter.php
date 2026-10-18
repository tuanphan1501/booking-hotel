<?php

namespace App\Support\Formatting;

/**
 * Formats VND amounts for the current locale (design rule: full number + "₫").
 *
 * - vi: "3.415.400 ₫"   ja: "3,415,400₫"   en: "3,415,400 ₫"
 * - Compact form is only used for dashboard KPIs: vi "186,4 tr ₫"   ja "186.4M₫"   en "186.4M ₫".
 * - Implemented without the intl extension so it works on every PHP installation.
 *
 * Usage in Blade: @inject('money', App\Support\Formatting\MoneyFormatter::class) … {{ $money->full($amount) }}
 *
 * @author PH.Tuan
 * @created 2026-10-18
 */
final class MoneyFormatter
{
    /**
     * Number format per locale: [thousands separator, decimal separator, currency suffix, million suffix].
     *
     * @var array<string, array{0: string, 1: string, 2: string, 3: string}>
     */
    private const LOCALE_FORMATS = [
        'vi' => ['.', ',', ' ₫', ' tr ₫'],
        'ja' => [',', '.', '₫', 'M₫'],
        'en' => [',', '.', ' ₫', 'M ₫'],
    ];

    /**
     * Format an amount with every digit, e.g. 3415400 → "3.415.400 ₫".
     *
     * @param  int  $amount  Amount in VND; negative amounts get a leading minus sign.
     *
     * @return string
     */
    public function full(int $amount): string
    {
        [$thousandsSeparator, , $currencySuffix] = $this->localeFormat();

        return ($amount < 0 ? '−' : '').number_format(abs($amount), 0, '', $thousandsSeparator).$currencySuffix;
    }

    /**
     * Format a large amount in millions, e.g. 186400000 → "186,4 tr ₫".
     *
     * - Amounts below one million fall back to the full format.
     *
     * @param  int  $amount  Amount in VND.
     *
     * @return string
     */
    public function compact(int $amount): string
    {
        if (abs($amount) < 1_000_000) {
            return $this->full($amount);
        }

        [$thousandsSeparator, $decimalSeparator, , $millionSuffix] = $this->localeFormat();

        return number_format(round($amount / 1_000_000, 1), 1, $decimalSeparator, $thousandsSeparator).$millionSuffix;
    }

    /**
     * Get the number format of the current locale (English rules for unknown locales).
     *
     * @return array{0: string, 1: string, 2: string, 3: string}
     */
    private function localeFormat(): array
    {
        return self::LOCALE_FORMATS[app()->getLocale()] ?? self::LOCALE_FORMATS['en'];
    }
}
