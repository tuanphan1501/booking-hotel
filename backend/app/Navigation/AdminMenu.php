<?php

namespace App\Navigation;

use App\Models\AdminUser;

/**
 * Defines the Web Admin sidebar (structure of mockups AD-02 … AD-18).
 *
 * - Add a screen: set the entry's route name once the route exists.
 * - Restrict a screen: set the entry's permission code (see RoleAndPermissionSeeder).
 *
 * @author PH.Tuan
 * @created 2026-10-18
 */
final class AdminMenu
{
    /**
     * Get the sidebar groups an administrator is allowed to see.
     *
     * - Entries whose permission the administrator lacks are removed.
     * - Groups left without entries are removed.
     *
     * @param  AdminUser  $administrator  Signed-in administrator.
     *
     * @return list<MenuGroup>
     */
    public function visibleFor(AdminUser $administrator): array
    {
        $visibleGroups = [];

        foreach ($this->groups() as $menuGroup) {
            $visibleItems = array_values(array_filter(
                $menuGroup->items,
                fn (MenuItem $menuItem): bool => $menuItem->permission === null
                    || $administrator->hasPermission($menuItem->permission),
            ));

            if ($visibleItems !== []) {
                $visibleGroups[] = new MenuGroup($menuGroup->translationKey, $visibleItems);
            }
        }

        return $visibleGroups;
    }

    /**
     * Build every sidebar group in display order.
     *
     * @return list<MenuGroup>
     */
    public function groups(): array
    {
        return [
            new MenuGroup(null, [
                new MenuItem('dashboard', 'dash', 'admin.dashboard', 'dashboard.view'),
            ]),
            new MenuGroup('operations', [
                new MenuItem('bookings', 'list', 'admin.bookings.index', 'booking.view'),
                new MenuItem('counter_booking', 'plus', permission: 'booking.edit'),
                new MenuItem('rooms_housekeeping', 'door', permission: 'housekeeping.view'),
            ]),
            new MenuGroup('products', [
                new MenuItem('rates_inventory', 'cal', permission: 'inventory.view'),
                new MenuItem('room_types', 'bed', permission: 'room.view'),
                new MenuItem('rate_plans', 'shield', permission: 'room.view'),
                new MenuItem('services', 'coffee', permission: 'service.view'),
            ]),
            new MenuGroup('customers', [
                new MenuItem('promotions', 'percent', permission: 'promotion.view'),
                new MenuItem('reviews', 'star', permission: 'review.view'),
                new MenuItem('members_points', 'users', permission: 'customer.view'),
            ]),
            new MenuGroup('finance', [
                new MenuItem('payments', 'money', permission: 'payment.view'),
                new MenuItem('reports', 'chart', permission: 'report.view'),
            ]),
            new MenuGroup('system', [
                new MenuItem('hotels', 'building', permission: 'system.manage'),
                new MenuItem('admins_roles', 'lock', permission: 'system.manage'),
                new MenuItem('settings', 'gear', permission: 'system.manage'),
            ]),
        ];
    }
}
