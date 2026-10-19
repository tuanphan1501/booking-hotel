<?php

namespace App\Data\Booking;

/**
 * Price breakdown of a stay following BR-03 (before promotions, vouchers and points).
 *
 * @author PH.Tuan
 * @created 2026-10-19
 */
final readonly class PriceQuote
{
    /**
     * @param  int  $nights  Number of nights.
     * @param  int  $roomAmount  Sum of the night prices (VND).
     * @param  int  $serviceCharge  Service charge (VND).
     * @param  int  $taxAmount  VAT on room amount + service charge (VND).
     * @param  int  $totalAmount  Amount to pay (VND).
     */
    public function __construct(
        public int $nights,
        public int $roomAmount,
        public int $serviceCharge,
        public int $taxAmount,
        public int $totalAmount,
    ) {}

    /**
     * Get the breakdown for JSON responses.
     *
     * @return array<string, int>
     */
    public function toArray(): array
    {
        return [
            'nights' => $this->nights,
            'room_amount' => $this->roomAmount,
            'service_charge' => $this->serviceCharge,
            'tax_amount' => $this->taxAmount,
            'total_amount' => $this->totalAmount,
        ];
    }
}
