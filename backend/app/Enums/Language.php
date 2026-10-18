<?php

namespace App\Enums;

/**
 * Display language of a user or administrator (columns users.language, admin_users.language).
 *
 * @author PH.Tuan
 * @created 2026-10-18
 */
enum Language: string
{
    case Vietnamese = 'vi';
    case Japanese = 'ja';
    case English = 'en';

    /**
     * Get the language codes that currently have UI translations.
     *
     * @return list<string>
     */
    public static function supported(): array
    {
        return [self::Vietnamese->value, self::Japanese->value];
    }
}
