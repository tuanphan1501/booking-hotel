<?php

namespace App\Enums;

/**
 * Channel a booking was created from (code group BOOKING_SOURCE, column bookings.source).
 *
 * @author PH.Tuan
 * @created 2026-10-18
 */
enum BookingSource: string
{
    /** Booked by the customer in the Mobile App. */
    case App = 'APP';

    /** Booked by the front desk at the counter or over the phone (AD-05). */
    case Counter = 'COUNTER';
}
