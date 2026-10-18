<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Seeds automatic promotions (BR-03 ②: only the highest-priority matching promotion applies).
 *
 * @author PH.Tuan
 * @created 2026-10-18
 */
class PromotionSeeder extends Seeder
{
    /**
     * Run the seeder.
     *
     * @return void
     */
    public function run(): void
    {
        $today = Carbon::today();
        $hotelIdsByCode = DB::table('hotels')->pluck('id', 'code');

        // Chain-wide early bird: check-in at least 30 days ahead (card "Đặt sớm giảm 10%" on MB-03).
        $earlyBirdId = DB::table('promotions')->insertGetId([
            'hotel_id' => null,
            'name' => 'Đặt sớm giảm 10%',
            'name_ja' => '早割10%オフ',
            'type' => 'EARLY_BIRD',
            'discount_percent' => 10,
            'min_advance_days' => 30,
            'stay_start_date' => $today->toDateString(),
            'stay_end_date' => $today->copy()->addYear()->toDateString(),
            'priority' => 10,
            'status' => 'ACTIVE',
        ]);

        // Saigon last minute: check-in within 3 days, higher priority than early bird.
        $lastMinuteId = DB::table('promotions')->insertGetId([
            'hotel_id' => $hotelIdsByCode['SG'],
            'name' => 'Phút chót giảm 15%',
            'name_ja' => '直前割15%オフ',
            'type' => 'LAST_MINUTE',
            'discount_percent' => 15,
            'max_advance_days' => 3,
            'stay_start_date' => $today->toDateString(),
            'stay_end_date' => $today->copy()->addMonths(6)->toDateString(),
            'priority' => 20,
            'status' => 'ACTIVE',
        ]);

        // Apply each promotion to every room type in its scope (all hotels when hotel_id is null).
        $this->attachRoomTypes($earlyBirdId, DB::table('room_types')->pluck('id')->all());
        $this->attachRoomTypes(
            $lastMinuteId,
            DB::table('room_types')->where('hotel_id', $hotelIdsByCode['SG'])->pluck('id')->all(),
        );
    }

    /**
     * Link a promotion to the room types it applies to.
     *
     * @param  int  $promotionId  Promotion to link.
     * @param  list<int>  $roomTypeIds  Eligible room types.
     *
     * @return void
     */
    private function attachRoomTypes(int $promotionId, array $roomTypeIds): void
    {
        DB::table('promotion_room_types')->insert(array_map(
            fn (int $roomTypeId): array => ['promotion_id' => $promotionId, 'room_type_id' => $roomTypeId],
            $roomTypeIds,
        ));
    }
}
