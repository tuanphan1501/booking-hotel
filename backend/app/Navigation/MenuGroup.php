<?php

namespace App\Navigation;

/**
 * A titled group of sidebar entries, e.g. "Operations".
 *
 * @author PH.Tuan
 * @created 2026-10-18
 */
final readonly class MenuGroup
{
    /**
     * @param  string|null  $translationKey  Key under "admin.menu.groups"; null for the untitled top group.
     * @param  list<MenuItem>  $items  Entries in display order.
     */
    public function __construct(
        public ?string $translationKey,
        public array $items,
    ) {}

    /**
     * Get the translated group title.
     *
     * @return string|null Null for the untitled top group.
     */
    public function title(): ?string
    {
        return $this->translationKey === null ? null : __('admin.menu.groups.'.$this->translationKey);
    }
}
