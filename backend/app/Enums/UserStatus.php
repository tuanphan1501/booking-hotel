<?php

namespace App\Enums;

use App\Enums\Concerns\HasLabel;

/**
 * Status of a customer account (code group USER_STATUS, column users.status).
 *
 * @author PH.Tuan
 * @created 2026-10-18
 */
enum UserStatus: string
{
    use HasLabel;

    /** The account can sign in and book. */
    case Active = 'ACTIVE';

    /** The account was locked by an administrator. */
    case Locked = 'LOCKED';

    /** The customer deleted their account; personal data is anonymized. */
    case Withdrawn = 'WITHDRAWN';
}
