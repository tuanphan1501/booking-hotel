<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeds hotel-level and room-level amenities.
 *
 * The `icon` column holds the icon name from design/mobile/assets/icons (e.g. "wifi" → wifi.svg).
 *
 * @author PH.Tuan
 * @created 2026-10-18
 */
class AmenitySeeder extends Seeder
{
    /**
     * [code, scope, Vietnamese, Japanese, English, icon]
     *
     * @var list<array{0: string, 1: 'HOTEL'|'ROOM', 2: string, 3: string, 4: string, 5: string}>
     */
    private const AMENITIES = [
        ['WIFI', 'HOTEL', 'Wi-Fi miễn phí', '無料Wi-Fi', 'Free Wi-Fi', 'wifi'],
        ['POOL', 'HOTEL', 'Hồ bơi', 'プール', 'Swimming pool', 'pool'],
        ['BREAKFAST', 'HOTEL', 'Ăn sáng', '朝食', 'Breakfast', 'coffee'],
        ['SHUTTLE', 'HOTEL', 'Đưa đón sân bay', '空港送迎', 'Airport shuttle', 'car'],
        ['SPA', 'HOTEL', 'Spa', 'スパ', 'Spa', 'spa'],
        ['GYM', 'HOTEL', 'Phòng gym', 'ジム', 'Fitness center', 'wave'],
        ['PARKING', 'HOTEL', 'Bãi đỗ xe', '駐車場', 'Parking', 'car'],
        ['RESTAURANT', 'HOTEL', 'Nhà hàng', 'レストラン', 'Restaurant', 'coffee'],
        ['AIRCON', 'ROOM', 'Điều hòa', 'エアコン', 'Air conditioning', 'wave'],
        ['MINIBAR', 'ROOM', 'Minibar', 'ミニバー', 'Minibar', 'coffee'],
        ['BATHTUB', 'ROOM', 'Bồn tắm', 'バスタブ', 'Bathtub', 'wave'],
        ['SMART_TV', 'ROOM', 'Smart TV', 'スマートTV', 'Smart TV', 'image'],
        ['SAFE', 'ROOM', 'Két an toàn', 'セーフティボックス', 'In-room safe', 'lock'],
        ['BALCONY', 'ROOM', 'Ban công', 'バルコニー', 'Balcony', 'home'],
    ];

    /**
     * Run the seeder.
     *
     * @return void
     */
    public function run(): void
    {
        DB::table('amenities')->insert(array_map(
            fn (array $amenity): array => [
                'code' => $amenity[0],
                'scope' => $amenity[1],
                'name_vi' => $amenity[2],
                'name_ja' => $amenity[3],
                'name_en' => $amenity[4],
                'icon' => $amenity[5],
            ],
            self::AMENITIES,
        ));
    }
}
