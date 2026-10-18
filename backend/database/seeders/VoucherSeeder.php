<?php

namespace Database\Seeders;

use App\Enums\MemberTierCode;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Seeds the vouchers shown on mockups MB-08 and MB-14 (WELCOME200, GOLD15, DANANG11).
 *
 * @author PH.Tuan
 * @created 2026-10-18
 */
class VoucherSeeder extends Seeder
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
        $goldTierId = DB::table('member_tiers')->where('code', MemberTierCode::Gold->value)->value('id');
        $singlesDay = Carbon::create($today->month > 11 ? $today->year + 1 : $today->year, 11, 11);

        DB::table('vouchers')->insert([
            [
                'code' => 'WELCOME200',
                'name' => 'Chào mừng thành viên mới — giảm 200.000 ₫',
                'description' => 'Đơn từ 2.000.000 ₫, áp dụng mọi khách sạn.',
                'hotel_id' => null,
                'discount_type' => 'FIXED',
                'discount_value' => 200_000,
                'max_discount' => null,
                'min_order_amount' => 2_000_000,
                'min_tier_id' => null,
                'is_public' => true,
                'start_at' => $today->copy()->startOfDay(),
                'end_at' => $today->copy()->addMonths(3)->endOfDay(),
                'usage_limit' => 10_000,
                'usage_per_user' => 1,
            ],
            [
                'code' => 'GOLD15',
                'name' => 'Ưu đãi hạng Gold — giảm 15%',
                'description' => 'Hạng Gold trở lên, giảm tối đa 1.000.000 ₫.',
                'hotel_id' => null,
                'discount_type' => 'PERCENT',
                'discount_value' => 15,
                'max_discount' => 1_000_000,
                'min_order_amount' => 0,
                'min_tier_id' => $goldTierId,
                'is_public' => false,
                'start_at' => $today->copy()->startOfDay(),
                'end_at' => $today->copy()->addMonths(6)->endOfDay(),
                'usage_limit' => null,
                'usage_per_user' => 3,
            ],
            [
                'code' => 'DANANG11',
                'name' => 'Sale 11/11 tại Đà Nẵng — giảm 11%',
                'description' => 'Chỉ Vivu Stay Đà Nẵng, đặt trong ngày 11/11.',
                'hotel_id' => $danangHotelId,
                'discount_type' => 'PERCENT',
                'discount_value' => 11,
                'max_discount' => 500_000,
                'min_order_amount' => 0,
                'min_tier_id' => null,
                'is_public' => true,
                'start_at' => $singlesDay->copy()->startOfDay(),
                'end_at' => $singlesDay->copy()->endOfDay(),
                'usage_limit' => 500,
                'usage_per_user' => 1,
            ],
        ]);
    }
}
