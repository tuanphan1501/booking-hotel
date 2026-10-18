<?php

namespace App\Enums;

use App\Enums\Concerns\HasLabel;

/**
 * Membership tiers (column member_tiers.code), evaluated monthly from 12-month spending (BR-09).
 *
 * @author PH.Tuan
 * @created 2026-10-18
 */
enum MemberTierCode: string
{
    use HasLabel;

    case Member = 'MEMBER';
    case Silver = 'SILVER';
    case Gold = 'GOLD';
    case Platinum = 'PLATINUM';
}
