<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Seeds a fresh database: `php artisan migrate:fresh --seed`.
 *
 * Master data is always seeded. Demo accounts (administrators and customers with a shared
 * development password) are skipped in production.
 *
 * @author PH.Tuan
 * @created 2026-10-18
 */
class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds in dependency order.
     *
     * @return void
     */
    public function run(): void
    {
        $this->call([
            MemberTierSeeder::class,
            RoleAndPermissionSeeder::class,
            SystemSettingSeeder::class,
            AmenitySeeder::class,
            HotelCatalogSeeder::class,
            RoomInventorySeeder::class,
            PromotionSeeder::class,
            VoucherSeeder::class,
            BannerSeeder::class,
        ]);

        if (! app()->isProduction()) {
            $this->call([DemoAccountSeeder::class, DemoBookingSeeder::class]);
        }
    }
}
