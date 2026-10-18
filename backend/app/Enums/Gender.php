<?php

namespace App\Enums;

use App\Enums\Concerns\HasLabel;

/**
 * Gender of a customer (code group GENDER, column users.gender).
 *
 * @author PH.Tuan
 * @created 2026-10-18
 */
enum Gender: string
{
    use HasLabel;

    case Male = 'MALE';
    case Female = 'FEMALE';
    case Other = 'OTHER';
}
