<?php

namespace App\Navigation;

/**
 * One entry of the Web Admin sidebar.
 *
 * @author PH.Tuan
 * @created 2026-10-18
 */
final readonly class MenuItem
{
    /**
     * @param  string  $translationKey  Key under "admin.menu.items", e.g. "dashboard".
     * @param  string  $iconName  Icon name in the sprite without the "i-" prefix, e.g. "dash".
     * @param  string|null  $routeName  Named route the entry links to; null while the screen is not built yet.
     * @param  string|null  $permission  Permission code required to see the entry; null means everyone.
     */
    public function __construct(
        public string $translationKey,
        public string $iconName,
        public ?string $routeName = null,
        public ?string $permission = null,
    ) {}

    /**
     * Get the translated label.
     *
     * @return string
     */
    public function label(): string
    {
        return __('admin.menu.items.'.$this->translationKey);
    }

    /**
     * Determine whether the entry links to an implemented screen.
     *
     * @return bool
     */
    public function isAvailable(): bool
    {
        return $this->routeName !== null;
    }

    /**
     * Get the URL of the entry.
     *
     * @return string|null Null when the screen is not built yet.
     */
    public function url(): ?string
    {
        return $this->isAvailable() ? route((string) $this->routeName) : null;
    }

    /**
     * Determine whether the entry matches the current route (highlighted in the sidebar).
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->isAvailable() && request()->routeIs($this->routeName, $this->routeName.'.*');
    }
}
