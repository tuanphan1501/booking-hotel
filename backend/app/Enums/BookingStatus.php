<?php

namespace App\Enums;

use App\Enums\Concerns\HasLabel;

/**
 * Booking lifecycle status (code group BOOKING_STATUS, column bookings.status). See Basic Design §6.1.
 *
 * @author PH.Tuan
 * @created 2026-10-18
 */
enum BookingStatus: string
{
    use HasLabel;

    /** Created from the app; inventory is held for 15 minutes while waiting for payment. */
    case PendingPayment = 'PENDING_PAYMENT';

    /** Paid (app) or created at the counter. */
    case Confirmed = 'CONFIRMED';

    /** Guest is staying; every booked room has an assigned physical room. */
    case CheckedIn = 'CHECKED_IN';

    /** Stay finished; points are earned and a review can be written. */
    case CheckedOut = 'CHECKED_OUT';

    /** Cancelled by the guest or an administrator. */
    case Cancelled = 'CANCELLED';

    /** Guest did not arrive (batch BAT02). */
    case NoShow = 'NO_SHOW';

    /** Payment was not completed within the hold time (batch BAT01). */
    case Expired = 'EXPIRED';

    /**
     * Get the statuses that count as sold rooms for revenue and occupancy.
     *
     * @return list<self>
     */
    public static function revenueGenerating(): array
    {
        return [self::Confirmed, self::CheckedIn, self::CheckedOut];
    }

    /**
     * Get the design-system chip tone used to display the status.
     *
     * @return string One of "amber", "brand", "sky", "" (neutral) or "rose".
     */
    public function tone(): string
    {
        return match ($this) {
            self::PendingPayment => 'amber',
            self::Confirmed => 'brand',
            self::CheckedIn => 'sky',
            self::CheckedOut => '',
            self::Cancelled, self::NoShow, self::Expired => 'rose',
        };
    }
}
