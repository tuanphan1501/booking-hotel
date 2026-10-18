<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeds the hotel chain catalog: hotels, images, amenities, cancellation policies, room types,
 * physical rooms, rate plans and add-on services.
 *
 * Data matches the mockups (e.g. Vivu Stay Đà Nẵng · Deluxe Ocean View 1,500,000 VND/night,
 * breakfast 150,000 VND per person per night, airport pickup 350,000 VND booked 24 hours ahead).
 *
 * @author PH.Tuan
 * @created 2026-10-18
 */
class HotelCatalogSeeder extends Seeder
{
    /**
     * Hotel definitions keyed by hotel code.
     *
     * @var array<string, array<string, mixed>>
     */
    private const HOTELS = [
        'DN' => [
            'name' => 'Vivu Stay Đà Nẵng',
            'name_ja' => 'ヴィヴステイ ダナン',
            'description' => 'Khách sạn 5 sao hướng biển Mỹ Khê, cách bãi biển 200 m.',
            'description_ja' => 'ミーケービーチまで200mのオーシャンビュー5つ星ホテル。',
            'star_rating' => 5,
            'address' => '120 Võ Nguyên Giáp, Sơn Trà, Đà Nẵng',
            'city' => 'Đà Nẵng',
            'latitude' => 16.0717000,
            'longitude' => 108.2457000,
            'phone' => '+842363888888',
            'email' => 'danang@vivustay.vn',
            'rating_avg' => 4.7,
            'review_count' => 1286,
            'images' => ['sea', 'pool', 'room', 'suite'],
            'amenities' => ['WIFI', 'POOL', 'BREAKFAST', 'SHUTTLE', 'SPA', 'GYM', 'RESTAURANT'],
            // floor => room numbers are generated as {floor}{01..n}
            'room_types' => [
                ['DLX', 'Deluxe Ocean View', 'デラックス オーシャンビュー', 32.0, 'KING', 2, 1, 'SEA', 1_500_000, [8, 9, 10], 6],
                ['JRS', 'Junior Suite', 'ジュニアスイート', 48.0, 'KING', 3, 1, 'SEA', 2_700_000, [11, 12], 3],
                ['SUP', 'Superior City', 'スーペリア シティビュー', 26.0, 'TWIN', 2, 0, 'CITY', 1_200_000, [5, 6], 6],
            ],
        ],
        'SG' => [
            'name' => 'Vivu Stay Saigon',
            'name_ja' => 'ヴィヴステイ サイゴン',
            'description' => 'Khách sạn trung tâm Quận 1, gần phố đi bộ Nguyễn Huệ.',
            'description_ja' => '1区中心部、グエンフエ通りに近いシティホテル。',
            'star_rating' => 5,
            'address' => '45 Lê Lợi, Bến Nghé, Quận 1, TP. Hồ Chí Minh',
            'city' => 'Hồ Chí Minh',
            'latitude' => 10.7740000,
            'longitude' => 106.7010000,
            'phone' => '+842838888888',
            'email' => 'saigon@vivustay.vn',
            'rating_avg' => 4.6,
            'review_count' => 932,
            'images' => ['city', 'room', 'suite'],
            'amenities' => ['WIFI', 'BREAKFAST', 'SPA', 'GYM', 'PARKING', 'RESTAURANT'],
            'room_types' => [
                ['SUP', 'Superior City', 'スーペリア シティビュー', 28.0, 'DOUBLE', 2, 1, 'CITY', 1_200_000, [6, 7, 8], 6],
                ['DLX', 'Deluxe Skyline', 'デラックス スカイライン', 34.0, 'KING', 2, 1, 'CITY', 1_650_000, [9, 10], 5],
            ],
        ],
        'HA' => [
            'name' => 'Vivu Stay Hội An',
            'name_ja' => 'ヴィヴステイ ホイアン',
            'description' => 'Resort phong cách phố cổ bên sông Thu Bồn.',
            'description_ja' => 'トゥボン川沿いの旧市街スタイルのリゾート。',
            'star_rating' => 4,
            'address' => '25 Cửa Đại, Cẩm Châu, Hội An, Quảng Nam',
            'city' => 'Hội An',
            'latitude' => 15.8794000,
            'longitude' => 108.3350000,
            'phone' => '+842353888888',
            'email' => 'hoian@vivustay.vn',
            'rating_avg' => 4.5,
            'review_count' => 645,
            'images' => ['lantern', 'pool', 'room'],
            'amenities' => ['WIFI', 'POOL', 'BREAKFAST', 'SHUTTLE', 'SPA', 'PARKING'],
            'room_types' => [
                ['GDN', 'Garden Deluxe', 'ガーデン デラックス', 30.0, 'DOUBLE', 2, 1, 'GARDEN', 980_000, [1, 2], 5],
                ['RVS', 'River Suite', 'リバースイート', 45.0, 'KING', 3, 1, 'GARDEN', 1_800_000, [3], 4],
            ],
        ],
    ];

    /**
     * Run the seeder.
     *
     * @return void
     */
    public function run(): void
    {
        $amenityIdsByCode = DB::table('amenities')->pluck('id', 'code');
        $roomAmenityIds = DB::table('amenities')->where('scope', 'ROOM')->pluck('id');

        foreach (self::HOTELS as $hotelCode => $hotel) {
            $hotelId = $this->createHotel($hotelCode, $hotel);

            $this->attachHotelImages($hotelId, $hotel['images']);
            DB::table('hotel_amenities')->insert(array_map(
                fn (string $amenityCode): array => ['hotel_id' => $hotelId, 'amenity_id' => $amenityIdsByCode[$amenityCode]],
                $hotel['amenities'],
            ));

            [$flexiblePolicyId, $nonRefundablePolicyId] = $this->createCancellationPolicies($hotelId);

            foreach ($hotel['room_types'] as $sortOrder => $roomTypeDefinition) {
                $roomTypeId = $this->createRoomType($hotelId, $roomTypeDefinition, $sortOrder);

                DB::table('room_type_amenities')->insert($roomAmenityIds->map(
                    fn (int $amenityId): array => ['room_type_id' => $roomTypeId, 'amenity_id' => $amenityId],
                )->all());

                $this->createRooms($hotelId, $roomTypeId, floors: $roomTypeDefinition[9], roomsPerFloor: $roomTypeDefinition[10]);
                $this->createRatePlans($roomTypeId, $flexiblePolicyId, $nonRefundablePolicyId);
            }

            $this->createServices($hotelId);
        }
    }

    /**
     * Insert a published hotel.
     *
     * @param  string  $hotelCode  Short unique code, e.g. "DN".
     * @param  array<string, mixed>  $hotel  Hotel definition from self::HOTELS.
     *
     * @return int The new hotel id.
     */
    private function createHotel(string $hotelCode, array $hotel): int
    {
        return DB::table('hotels')->insertGetId([
            'code' => $hotelCode,
            'name' => $hotel['name'],
            'name_ja' => $hotel['name_ja'],
            'description' => $hotel['description'],
            'description_ja' => $hotel['description_ja'],
            'star_rating' => $hotel['star_rating'],
            'address' => $hotel['address'],
            'city' => $hotel['city'],
            'latitude' => $hotel['latitude'],
            'longitude' => $hotel['longitude'],
            'phone' => $hotel['phone'],
            'email' => $hotel['email'],
            'check_in_time' => '14:00',
            'check_out_time' => '12:00',
            'rating_avg' => $hotel['rating_avg'],
            'review_count' => $hotel['review_count'],
            'status' => 'PUBLISHED',
        ]);
    }

    /**
     * Insert gallery images. Placeholders point to the design illustrations until real photos exist.
     *
     * @param  int  $hotelId  Owning hotel.
     * @param  list<string>  $illustrationNames  Illustration names; the first one becomes the cover.
     *
     * @return void
     */
    private function attachHotelImages(int $hotelId, array $illustrationNames): void
    {
        foreach ($illustrationNames as $index => $illustrationName) {
            DB::table('hotel_images')->insert([
                'hotel_id' => $hotelId,
                'url' => "/images/placeholders/{$illustrationName}.svg",
                'sort_order' => $index,
                'is_cover' => $index === 0,
            ]);
        }
    }

    /**
     * Insert the two standard cancellation policies of a hotel.
     *
     * @param  int  $hotelId  Owning hotel.
     *
     * @return array{0: int, 1: int} [flexible policy id, non-refundable policy id]
     */
    private function createCancellationPolicies(int $hotelId): array
    {
        $flexiblePolicyId = DB::table('cancellation_policies')->insertGetId([
            'hotel_id' => $hotelId,
            'name' => 'Linh hoạt — hủy miễn phí trước 48 giờ',
            'is_refundable' => true,
            'free_cancel_hours' => 48,
            'penalty_percent' => 100,
            'no_show_percent' => 100,
            'description' => 'Hủy miễn phí đến 48 giờ trước giờ nhận phòng; sau đó tính 100% tiền đêm đầu.',
            'description_ja' => 'チェックイン48時間前まで無料。以降は初泊料金の100%。',
        ]);

        $nonRefundablePolicyId = DB::table('cancellation_policies')->insertGetId([
            'hotel_id' => $hotelId,
            'name' => 'Không hoàn hủy',
            'is_refundable' => false,
            'free_cancel_hours' => 0,
            'penalty_percent' => 100,
            'no_show_percent' => 100,
            'description' => 'Không hoàn tiền khi hủy hoặc không đến.',
            'description_ja' => 'キャンセル・不泊時の返金なし。',
        ]);

        return [$flexiblePolicyId, $nonRefundablePolicyId];
    }

    /**
     * Insert a room type and its placeholder image.
     *
     * @param  int  $hotelId  Owning hotel.
     * @param  array<int, mixed>  $definition  [code, name, name_ja, area, bed, adults, children, view, price, floors, perFloor]
     * @param  int  $sortOrder  Display order within the hotel.
     *
     * @return int The new room type id.
     */
    private function createRoomType(int $hotelId, array $definition, int $sortOrder): int
    {
        [$code, $name, $nameJa, $areaM2, $bedType, $maxAdults, $maxChildren, $viewType, $basePrice] = $definition;

        $roomTypeId = DB::table('room_types')->insertGetId([
            'hotel_id' => $hotelId,
            'code' => $code,
            'name' => $name,
            'name_ja' => $nameJa,
            'area_m2' => $areaM2,
            'bed_type' => $bedType,
            'max_adults' => $maxAdults,
            'max_children' => $maxChildren,
            'view_type' => $viewType,
            'base_price' => $basePrice,
            'sort_order' => $sortOrder,
            'status' => 'ACTIVE',
        ]);

        DB::table('room_type_images')->insert([
            'room_type_id' => $roomTypeId,
            'url' => '/images/placeholders/'.($code === 'JRS' || str_ends_with($code, 'S') ? 'suite' : 'room').'.svg',
        ]);

        return $roomTypeId;
    }

    /**
     * Insert physical rooms numbered {floor}{01..n}, e.g. floor 8 → 801, 802, ...
     *
     * @param  int  $hotelId  Owning hotel.
     * @param  int  $roomTypeId  Room type of every created room.
     * @param  list<int>  $floors  Floors holding this room type.
     * @param  int  $roomsPerFloor  Number of rooms on each floor.
     *
     * @return void
     */
    private function createRooms(int $hotelId, int $roomTypeId, array $floors, int $roomsPerFloor): void
    {
        foreach ($floors as $floor) {
            for ($position = 1; $position <= $roomsPerFloor; $position++) {
                DB::table('rooms')->insert([
                    'hotel_id' => $hotelId,
                    'room_type_id' => $roomTypeId,
                    'room_number' => $floor.str_pad((string) $position, 2, '0', STR_PAD_LEFT),
                    'floor' => $floor,
                    'status' => 'AVAILABLE',
                ]);
            }
        }
    }

    /**
     * Insert the two rate plans offered for every room type (as on mockup MB-06).
     *
     * @param  int  $roomTypeId  Room type the plans belong to.
     * @param  int  $flexiblePolicyId  Policy for the refundable "room + breakfast" plan.
     * @param  int  $nonRefundablePolicyId  Policy for the discounted non-refundable plan.
     *
     * @return void
     */
    private function createRatePlans(int $roomTypeId, int $flexiblePolicyId, int $nonRefundablePolicyId): void
    {
        DB::table('rate_plans')->insert([
            [
                'room_type_id' => $roomTypeId,
                'cancellation_policy_id' => $flexiblePolicyId,
                'code' => 'BB',
                'name' => 'Phòng + ăn sáng',
                'name_ja' => '朝食付き',
                'breakfast_included' => true,
                'price_adjust_type' => 'NONE',
                'price_adjust_value' => 0,
                'min_nights' => 1,
                'advance_days_min' => null,
                'status' => 'ACTIVE',
            ],
            [
                'room_type_id' => $roomTypeId,
                'cancellation_policy_id' => $nonRefundablePolicyId,
                'code' => 'NR',
                'name' => 'Không hoàn hủy',
                'name_ja' => '返金不可',
                'breakfast_included' => false,
                'price_adjust_type' => 'FIXED',
                'price_adjust_value' => -200_000,
                'min_nights' => 1,
                'advance_days_min' => null,
                'status' => 'ACTIVE',
            ],
        ]);
    }

    /**
     * Insert the add-on services bookable in the app (BR-12).
     *
     * @param  int  $hotelId  Owning hotel.
     *
     * @return void
     */
    private function createServices(int $hotelId): void
    {
        $services = [
            ['BREAKFAST', 'BREAKFAST', 'Ăn sáng buffet', '朝食ビュッフェ', 150_000, 'PER_PERSON_NIGHT', 0],
            ['AIRPORT', 'TRANSFER', 'Đón sân bay', '空港送迎', 350_000, 'PER_TRIP', 24],
            ['SPA60', 'SPA', 'Massage 60 phút', 'マッサージ60分', 600_000, 'PER_PERSON', 4],
            ['EXTRABED', 'EXTRA_BED', 'Giường phụ', 'エキストラベッド', 400_000, 'PER_ROOM_NIGHT', 12],
            ['LATECO', 'LATE_CHECKOUT', 'Trả phòng muộn đến 18:00', 'レイトチェックアウト18時', 500_000, 'PER_ITEM', 24],
        ];

        foreach ($services as [$code, $category, $name, $nameJa, $price, $unit, $leadTimeHours]) {
            DB::table('services')->insert([
                'hotel_id' => $hotelId,
                'code' => $code,
                'category' => $category,
                'name' => $name,
                'name_ja' => $nameJa,
                'price' => $price,
                'unit' => $unit,
                'bookable_in_app' => true,
                'lead_time_hours' => $leadTimeHours,
                'status' => 'ACTIVE',
            ]);
        }
    }
}
