<?php

namespace Database\Seeders;

use App\Enums\BookingSource;
use App\Enums\BookingStatus;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Seeds demo bookings around today so the Web Admin home screen (AD-02) shows realistic figures.
 *
 * - Covers the last 7 and the next 7 days for every hotel, with every booking status.
 * - Keeps room_inventories.booked_rooms, room assignments and room statuses consistent.
 * - Adds payments, one refund waiting for approval and two reviews waiting for moderation.
 * - Today's first three arrivals at the Đà Nẵng hotel match mockup AD-02.
 * - Deterministic: the random generator is seeded, so every run produces the same data.
 *
 * @author PH.Tuan
 * @created 2026-10-18
 */
class DemoBookingSeeder extends Seeder
{
    /** Seed of the random generator (makes the demo data reproducible). */
    private const RANDOM_SEED = 20261018;

    /** Service charge rate (BR-03 ⑤) — matches system_settings fee.service_charge_percent. */
    private const SERVICE_CHARGE_RATE = 0.05;

    /** VAT rate (BR-03 ⑥) — matches system_settings tax.vat_percent. */
    private const VAT_RATE = 0.08;

    /** Guest names used for counter bookings without a customer account. */
    private const WALK_IN_GUESTS = [
        'Lê Thu Hà', 'Phạm Quốc Bảo', 'Hoàng Gia Huy', 'Ngô Bảo Ngọc', 'Suzuki Haruto', 'Đặng Minh Khang',
        'Bùi Thanh Tâm', 'Watanabe Aoi', 'Vũ Hải Yến', 'Trịnh Công Sơn', 'Lý Mỹ Linh', 'Kobayashi Ren',
    ];

    /** Arrival times offered on the guest form (MB-07). */
    private const ARRIVAL_TIMES = ['13:30', '14:00', '15:00', '16:30', '18:00', '20:00', null];

    private CarbonImmutable $today;

    /**
     * Physical rooms already taken per night: [room_id][Y-m-d] => true.
     *
     * @var array<int, array<string, bool>>
     */
    private array $roomNights = [];

    /**
     * Run the seeder.
     *
     * @return void
     */
    public function run(): void
    {
        mt_srand(self::RANDOM_SEED);
        $this->today = CarbonImmutable::today();

        $customers = DB::table('users')->where('status', 'ACTIVE')->get(['id', 'full_name', 'email', 'phone']);

        foreach (DB::table('hotels')->orderBy('id')->get(['id', 'code']) as $hotel) {
            $roomTypes = DB::table('room_types')->where('hotel_id', $hotel->id)->orderBy('sort_order')->get(['id', 'code', 'base_price']);

            if ($hotel->code === 'DN') {
                $this->seedMockupArrivals($hotel->id, $roomTypes->keyBy('code'), $customers->firstWhere('email', 'yuki.tanaka@example.jp'));
            }

            for ($dayOffset = -7; $dayOffset <= 7; $dayOffset++) {
                $bookingsForDay = mt_rand(3, 6);

                for ($index = 0; $index < $bookingsForDay; $index++) {
                    $roomType = $roomTypes[mt_rand(0, $roomTypes->count() - 1)];
                    $customer = mt_rand(0, 1) === 1 ? $customers->random() : null;

                    $this->createBooking(
                        hotelId: $hotel->id,
                        roomType: $roomType,
                        checkInDate: $this->today->addDays($dayOffset),
                        nights: mt_rand(1, 3),
                        customer: $customer,
                        guestName: $customer->full_name ?? self::WALK_IN_GUESTS[mt_rand(0, count(self::WALK_IN_GUESTS) - 1)],
                        arrivalTime: self::ARRIVAL_TIMES[mt_rand(0, count(self::ARRIVAL_TIMES) - 1)],
                    );
                }
            }
        }

        $this->seedPendingRefund();
        $this->seedPendingReviews();
    }

    /**
     * Create the three arrivals shown on mockup AD-02 at Vivu Stay Đà Nẵng.
     *
     * @param  int  $hotelId  Đà Nẵng hotel.
     * @param  Collection<array-key, \stdClass>  $roomTypesByCode  Room types keyed by code.
     * @param  object|null  $tanakaCustomer  Demo customer "Tanaka Yuki", if seeded.
     *
     * @return void
     */
    private function seedMockupArrivals(int $hotelId, Collection $roomTypesByCode, ?object $tanakaCustomer): void
    {
        $this->createBooking($hotelId, $roomTypesByCode['JRS'], $this->today, 3, $tanakaCustomer, 'Tanaka Yuki', '13:30', BookingStatus::Confirmed);
        $this->createBooking($hotelId, $roomTypesByCode['DLX'], $this->today, 3, null, 'Lê Thu Hà', '15:00', BookingStatus::Confirmed, assignRoom: true);
        $this->createBooking($hotelId, $roomTypesByCode['SUP'], $this->today, 2, null, 'Phạm Quốc Bảo', '18:00', BookingStatus::Confirmed, paidAmount: 1_000_000);
    }

    /**
     * Create one booking with its room, nightly prices, payment and inventory update.
     *
     * - The status is derived from the dates unless $forcedStatus is given.
     * - Skipped silently when the room type is sold out on one of the nights.
     *
     * @param  int  $hotelId  Hotel of the booking.
     * @param  object  $roomType  Room type row (id, code, base_price).
     * @param  CarbonImmutable  $checkInDate  First night.
     * @param  int  $nights  Number of nights.
     * @param  object|null  $customer  Customer row for app bookings; null for counter bookings.
     * @param  string  $guestName  Name of the staying guest.
     * @param  string|null  $arrivalTime  Estimated arrival time (H:i) or null.
     * @param  BookingStatus|null  $forcedStatus  Status to use instead of the date-based one.
     * @param  bool  $assignRoom  Assign a physical room even if the guest has not checked in yet.
     * @param  int|null  $paidAmount  Amount already paid; defaults to full payment (or 0 when pending).
     *
     * @return void
     */
    private function createBooking(
        int $hotelId,
        object $roomType,
        CarbonImmutable $checkInDate,
        int $nights,
        ?object $customer,
        string $guestName,
        ?string $arrivalTime,
        ?BookingStatus $forcedStatus = null,
        bool $assignRoom = false,
        ?int $paidAmount = null,
    ): void {
        $stayDates = array_map(fn (int $night) => $checkInDate->addDays($night), range(0, $nights - 1));

        if (! $this->hasAvailability($roomType->id, $stayDates)) {
            return;
        }

        $status = $forcedStatus ?? $this->statusFor($checkInDate, $checkInDate->addDays($nights));
        $source = $customer === null ? BookingSource::Counter : BookingSource::App;
        $nightlyPrices = array_map(fn (CarbonImmutable $stayDate) => $this->nightlyPrice((int) $roomType->base_price, $stayDate), $stayDates);

        $roomAmount = array_sum($nightlyPrices);
        $serviceCharge = (int) round($roomAmount * self::SERVICE_CHARGE_RATE);
        $taxAmount = (int) round(($roomAmount + $serviceCharge) * self::VAT_RATE);
        $totalAmount = $roomAmount + $serviceCharge + $taxAmount;
        $paidAmount ??= match ($status) {
            BookingStatus::PendingPayment, BookingStatus::NoShow => 0,
            default => $totalAmount,
        };

        $createdAt = $this->createdAtFor($checkInDate, $status);
        $bookingId = DB::table('bookings')->insertGetId([
            'booking_code' => $this->bookingCode($createdAt),
            'user_id' => $customer?->id,
            'hotel_id' => $hotelId,
            'check_in_date' => $checkInDate->toDateString(),
            'check_out_date' => $checkInDate->addDays($nights)->toDateString(),
            'nights' => $nights,
            'adults' => 2,
            'children' => 0,
            'guest_name' => $guestName,
            'guest_email' => $customer->email ?? Str::slug($guestName, '.').'@example.com',
            'guest_phone' => $customer->phone ?? '+8490'.mt_rand(1000000, 9999999),
            'estimated_arrival' => $arrivalTime,
            'status' => $status->value,
            'source' => $source->value,
            'room_amount' => $roomAmount,
            'service_charge' => $serviceCharge,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount,
            'payable_amount' => $totalAmount,
            'paid_amount' => $paidAmount,
            'cancel_policy_snapshot' => json_encode(['is_refundable' => true, 'free_cancel_hours' => 48, 'penalty_percent' => 100, 'no_show_percent' => 100]),
            'confirmed_at' => $status === BookingStatus::PendingPayment ? null : $createdAt,
            'checked_in_at' => in_array($status, [BookingStatus::CheckedIn, BookingStatus::CheckedOut], true) ? $checkInDate->setTime(14, 30) : null,
            'checked_out_at' => $status === BookingStatus::CheckedOut ? $checkInDate->addDays($nights)->setTime(11, 30) : null,
            'cancelled_at' => $status === BookingStatus::Cancelled ? $this->today->setTime(10, 15) : null,
            'cancel_reason' => $status === BookingStatus::Cancelled ? 'Thay đổi lịch trình' : null,
            'cancelled_by' => $status === BookingStatus::Cancelled ? 'USER' : null,
            'hold_expires_at' => $status === BookingStatus::PendingPayment ? now()->addMinutes(12) : null,
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);

        $needsRoom = $assignRoom || in_array($status, [BookingStatus::CheckedIn, BookingStatus::CheckedOut], true);
        $roomId = $needsRoom ? $this->assignRoom($roomType->id, $stayDates, occupyNow: $status === BookingStatus::CheckedIn) : null;

        $bookingRoomId = DB::table('booking_rooms')->insertGetId([
            'booking_id' => $bookingId,
            'room_type_id' => $roomType->id,
            'rate_plan_id' => DB::table('rate_plans')->where('room_type_id', $roomType->id)->where('code', 'BB')->value('id'),
            'room_id' => $roomId,
            'adults' => 2,
            'children' => 0,
            'subtotal' => $roomAmount,
        ]);

        DB::table('booking_room_nights')->insert(array_map(
            fn (CarbonImmutable $stayDate, int $price) => ['booking_room_id' => $bookingRoomId, 'stay_date' => $stayDate->toDateString(), 'price' => $price],
            $stayDates,
            $nightlyPrices,
        ));

        if (! in_array($status, [BookingStatus::Cancelled, BookingStatus::Expired], true)) {
            $this->reserveInventory($roomType->id, $stayDates);
        }

        if ($paidAmount > 0) {
            DB::table('payments')->insert([
                'payment_code' => 'PM'.strtoupper(Str::random(10)),
                'booking_id' => $bookingId,
                'method' => $source === BookingSource::App ? ['VNPAY', 'MOMO', 'CARD'][mt_rand(0, 2)] : 'CASH',
                'amount' => $paidAmount,
                'status' => 'SUCCESS',
                'paid_at' => $createdAt,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);
        }
    }

    /**
     * Derive the status of a generated booking from its dates.
     *
     * @param  CarbonImmutable  $checkInDate  First night.
     * @param  CarbonImmutable  $checkOutDate  Departure day.
     *
     * @return BookingStatus
     */
    private function statusFor(CarbonImmutable $checkInDate, CarbonImmutable $checkOutDate): BookingStatus
    {
        $roll = mt_rand(1, 100);

        return match (true) {
            $checkOutDate->lt($this->today) => $roll <= 6 ? BookingStatus::NoShow : BookingStatus::CheckedOut,
            $checkOutDate->isSameDay($this->today) => $roll <= 35 ? BookingStatus::CheckedIn : BookingStatus::CheckedOut,
            $checkInDate->lt($this->today) => BookingStatus::CheckedIn,
            $checkInDate->isSameDay($this->today) => $roll <= 25 ? BookingStatus::CheckedIn : BookingStatus::Confirmed,
            default => match (true) {
                $roll <= 8 => BookingStatus::Cancelled,
                $roll <= 14 => BookingStatus::PendingPayment,
                default => BookingStatus::Confirmed,
            },
        };
    }

    /**
     * Pick the creation time of a booking: usually days before arrival, sometimes today.
     *
     * @param  CarbonImmutable  $checkInDate  First night.
     * @param  BookingStatus  $status  Generated status.
     *
     * @return CarbonImmutable
     */
    private function createdAtFor(CarbonImmutable $checkInDate, BookingStatus $status): CarbonImmutable
    {
        $isCreatedToday = $status === BookingStatus::PendingPayment
            || ($checkInDate->gte($this->today) && mt_rand(1, 100) <= 30);

        $createdAt = $isCreatedToday
            ? $this->today->setTime(mt_rand(7, 17), mt_rand(0, 59))
            : $checkInDate->subDays(mt_rand(1, 30))->setTime(mt_rand(7, 22), mt_rand(0, 59));

        return $createdAt->min($this->today->setTime(17, 59));
    }

    /**
     * Price of one night: base price, +10 % on Friday and Saturday, rounded to 1,000 VND (BR-02).
     *
     * @param  int  $basePrice  Room type base price.
     * @param  CarbonImmutable  $stayDate  Night being priced.
     *
     * @return int
     */
    private function nightlyPrice(int $basePrice, CarbonImmutable $stayDate): int
    {
        $price = $stayDate->isFriday() || $stayDate->isSaturday() ? $basePrice * 1.1 : $basePrice;

        return (int) (round($price / 1000) * 1000);
    }

    /**
     * Check that every night still has a free room of the type.
     *
     * @param  int  $roomTypeId  Room type to check.
     * @param  list<CarbonImmutable>  $stayDates  Nights of the stay.
     *
     * @return bool
     */
    private function hasAvailability(int $roomTypeId, array $stayDates): bool
    {
        $fullNights = DB::table('room_inventories')
            ->where('room_type_id', $roomTypeId)
            ->whereIn('stay_date', array_map(fn (CarbonImmutable $date) => $date->toDateString(), $stayDates))
            ->whereRaw('booked_rooms + blocked_rooms >= total_rooms')
            ->exists();

        return ! $fullNights;
    }

    /**
     * Increment booked_rooms for each night of the stay (BR-04).
     *
     * @param  int  $roomTypeId  Booked room type.
     * @param  list<CarbonImmutable>  $stayDates  Nights of the stay.
     *
     * @return void
     */
    private function reserveInventory(int $roomTypeId, array $stayDates): void
    {
        DB::table('room_inventories')
            ->where('room_type_id', $roomTypeId)
            ->whereIn('stay_date', array_map(fn (CarbonImmutable $date) => $date->toDateString(), $stayDates))
            ->increment('booked_rooms');
    }

    /**
     * Assign the first physical room of the type that is free on every night.
     *
     * @param  int  $roomTypeId  Booked room type.
     * @param  list<CarbonImmutable>  $stayDates  Nights of the stay.
     * @param  bool  $occupyNow  Mark the room OCCUPIED (guest is checked in).
     *
     * @return int|null Assigned room id, or null when every room is taken.
     */
    private function assignRoom(int $roomTypeId, array $stayDates, bool $occupyNow): ?int
    {
        $dateKeys = array_map(fn (CarbonImmutable $date) => $date->toDateString(), $stayDates);

        foreach (DB::table('rooms')->where('room_type_id', $roomTypeId)->orderBy('room_number')->pluck('id') as $roomId) {
            $isFree = array_filter($dateKeys, fn (string $dateKey) => isset($this->roomNights[$roomId][$dateKey])) === [];

            if ($isFree) {
                foreach ($dateKeys as $dateKey) {
                    $this->roomNights[$roomId][$dateKey] = true;
                }

                if ($occupyNow) {
                    DB::table('rooms')->where('id', $roomId)->update(['status' => 'OCCUPIED']);
                }

                return $roomId;
            }
        }

        return null;
    }

    /**
     * Build a booking code "HB" + yymmdd of creation + 4 random characters, e.g. HB261018A7K2.
     *
     * @param  CarbonImmutable  $createdAt  Creation time of the booking.
     *
     * @return string
     */
    private function bookingCode(CarbonImmutable $createdAt): string
    {
        do {
            $bookingCode = 'HB'.$createdAt->format('ymd').strtoupper(Str::random(4));
        } while (DB::table('bookings')->where('booking_code', $bookingCode)->exists());

        return $bookingCode;
    }

    /**
     * Turn one paid cancellation into a refund waiting for approval (shown on AD-02 and AD-14).
     *
     * @return void
     */
    private function seedPendingRefund(): void
    {
        $cancelledBooking = DB::table('bookings')
            ->join('payments', 'payments.booking_id', '=', 'bookings.id')
            ->where('bookings.status', BookingStatus::Cancelled->value)
            ->select('bookings.id', 'payments.id as payment_id', 'payments.amount')
            ->first();

        if ($cancelledBooking === null) {
            return;
        }

        DB::table('refunds')->insert([
            'refund_code' => 'RF'.strtoupper(Str::random(10)),
            'payment_id' => $cancelledBooking->payment_id,
            'booking_id' => $cancelledBooking->id,
            'amount' => $cancelledBooking->amount,
            'reason' => 'Khách hủy trước hạn miễn phí',
            'status' => 'PENDING',
        ]);
    }

    /**
     * Add reviews waiting for moderation for two finished app bookings (shown on AD-02 and AD-12).
     *
     * @return void
     */
    private function seedPendingReviews(): void
    {
        $finishedBookings = DB::table('bookings')
            ->where('status', BookingStatus::CheckedOut->value)
            ->whereNotNull('user_id')
            ->limit(2)
            ->get(['id', 'user_id', 'hotel_id']);

        $comments = ['Phòng sạch, lễ tân nhiệt tình. Bữa sáng hơi ít món chay.', 'Vị trí đẹp, view biển tuyệt vời, sẽ quay lại.'];

        foreach ($finishedBookings as $index => $booking) {
            DB::table('reviews')->insert([
                'booking_id' => $booking->id,
                'user_id' => $booking->user_id,
                'hotel_id' => $booking->hotel_id,
                'rating_overall' => 5 - $index,
                'rating_cleanliness' => 5,
                'rating_service' => 4,
                'rating_location' => 5,
                'rating_value' => 4,
                'comment' => $comments[$index],
                'status' => 'PENDING',
            ]);
        }
    }
}
