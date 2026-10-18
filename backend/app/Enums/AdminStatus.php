<?php

namespace App\Enums;

use App\Enums\Concerns\HasLabel;

/**
 * Status of an administrator account (column admin_users.status).
 *
 * @author PH.Tuan
 * @created 2026-10-18
 */
enum AdminStatus: string
{
    use HasLabel;

    /** The administrator can sign in to the Web Admin. */
    case Active = 'ACTIVE';

    /** The administrator is not allowed to sign in. */
    case Locked = 'LOCKED';
}
