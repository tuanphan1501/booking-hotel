<?php

namespace App\Enums;

use App\Enums\Concerns\HasLabel;

/**
 * Administrator roles (column roles.code). See Basic Design §10 for the permission matrix.
 *
 * @author PH.Tuan
 * @created 2026-10-18
 */
enum RoleCode: string
{
    use HasLabel;

    /** Head office: full access to every hotel, system settings and permissions. */
    case SuperAdmin = 'SUPER_ADMIN';

    /** Manages rooms, rates, services, bookings and reviews of one hotel. */
    case HotelManager = 'HOTEL_MANAGER';

    /** Front desk: counter bookings, check-in/out, room assignment, payments. */
    case Receptionist = 'RECEPTIONIST';

    /** Payments, refund approval and revenue reports. */
    case Accountant = 'ACCOUNTANT';

    /** Promotions, vouchers, banners, notifications and member points. */
    case Marketing = 'MARKETING';
}
