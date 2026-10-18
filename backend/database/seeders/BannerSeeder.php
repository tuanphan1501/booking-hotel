<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Seeds home screen banners (MB-03 "Offers for you").
 *
 * @author PH.Tuan
 * @created 2026-10-18
 */
class BannerSeeder extends Seeder
{
    /**
     * Run the seeder.
     *
     * @return void
     */
    public function run(): void
    {
        $today = Carbon::today();
        $danangHotelId = DB::table('hotels')->where('code', 'DN')->value('id');

        DB::table('banners')->insert([
            [
                'title' => 'Đặt sớm giảm 10%',
                'image_url' => '/images/placeholders/sea.svg',
                'link_type' => 'URL',
                'link_value' => '/offers/early-bird',
                'start_at' => $today->copy()->startOfDay(),
                'end_at' => $today->copy()->addMonths(3)->endOfDay(),
                'sort_order' => 1,
                'status' => 'ACTIVE',
            ],
            [
                'title' => 'Mùa hè Đà Nẵng',
                'image_url' => '/images/placeholders/pool.svg',
                'link_type' => 'HOTEL',
                'link_value' => (string) $danangHotelId,
                'start_at' => $today->copy()->startOfDay(),
                'end_at' => $today->copy()->addMonths(6)->endOfDay(),
                'sort_order' => 2,
                'status' => 'ACTIVE',
            ],
            [
                'title' => 'Chào mừng thành viên mới',
                'image_url' => '/images/placeholders/room.svg',
                'link_type' => 'VOUCHER',
                'link_value' => 'WELCOME200',
                'start_at' => $today->copy()->startOfDay(),
                'end_at' => $today->copy()->addMonths(3)->endOfDay(),
                'sort_order' => 3,
                'status' => 'ACTIVE',
            ],
        ]);
    }
}
