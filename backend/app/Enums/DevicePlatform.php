<?php

namespace App\Enums;

/**
 * Mobile platform of a push notification token (column device_tokens.platform).
 *
 * @author PH.Tuan
 * @created 2026-10-18
 */
enum DevicePlatform: string
{
    case Ios = 'IOS';
    case Android = 'ANDROID';
}
