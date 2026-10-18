<?php

namespace App\Enums\Concerns;

/**
 * Adds translated labels to a backed enum.
 *
 * Labels are read from lang/{locale}/enums.php using the key "{EnumClassName}.{CASE_VALUE}",
 * e.g. __('enums.UserStatus.ACTIVE').
 *
 * @author PH.Tuan
 * @created 2026-10-18
 */
trait HasLabel
{
    /**
     * Get the human-readable label of the case in the current locale.
     *
     * @return string The translated label, or the translation key when no translation exists.
     */
    public function label(): string
    {
        return __('enums.'.class_basename(static::class).'.'.$this->value);
    }

    /**
     * Get the raw values of all cases, e.g. for validation rules (Rule::in(...)).
     *
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
