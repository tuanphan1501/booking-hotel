<?php

namespace App\Enums;

/**
 * Identity provider used for social sign-in (column users.social_provider).
 *
 * @author PH.Tuan
 * @created 2026-10-18
 */
enum SocialProvider: string
{
    case Google = 'GOOGLE';
    case Apple = 'APPLE';
    case Facebook = 'FACEBOOK';
}
