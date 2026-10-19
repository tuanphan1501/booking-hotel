<?php

namespace Tests\Feature\Api\V1;

use App\Models\RatePlan;
use App\Models\User;
use Database\Seeders\AmenitySeeder;
use Database\Seeders\DemoAccountSeeder;
use Database\Seeders\HotelCatalogSeeder;
use Database\Seeders\MemberTierSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Database\Seeders\RoomInventorySeeder;
use Database\Seeders\SystemSettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Mobile App booking flow: search (MB-04), rooms (MB-05/06), book (MB-07), pay (MB-08), trips (MB-10/11), cancel (MB-11c).
 *
 * @author PH.Tuan
 * @created 2026-10-19
 */
class BookingApiTest extends TestCase
{
    use RefreshDatabase;

    private string $checkIn;

    private string $checkOut;

    /**
     * Seed the catalog with inventory; stay = 10 → 12 days from today.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            MemberTierSeeder::class, RoleAndPermissionSeeder::class, SystemSettingSeeder::class, AmenitySeeder::class,
            HotelCatalogSeeder::class, RoomInventorySeeder::class, DemoAccountSeeder::class,
        ]);
        $this->checkIn = today()->addDays(10)->toDateString();
        $this->checkOut = today()->addDays(12)->toDateString();
    }

    public function test_guest_searches_hotels_and_reads_room_prices(): void
    {
        $hotels = $this->getJson("/api/v1/hotels?check_in={$this->checkIn}&check_out={$this->checkOut}&adults=2")
            ->assertOk()
            ->assertJsonCount(3, 'data')
            ->json('data');
        $this->assertGreaterThan(0, $hotels[0]['from_price']);

        $this->getJson("/api/v1/hotels/{$hotels[0]['id']}/rooms?check_in={$this->checkIn}&check_out={$this->checkOut}&adults=2")
            ->assertOk()
            ->assertJsonPath('data.room_types.0.rate_plans.0.quote.nights', 2)
            ->assertJsonStructure(['data' => ['hotel' => ['name'], 'room_types' => [['id', 'name', 'rooms_left', 'rate_plans']]]]);
    }

    public function test_invalid_stay_returns_validation_error(): void
    {
        $this->getJson('/api/v1/hotels?check_in=2020-01-01&check_out=2020-01-01&adults=0')
            ->assertStatus(422)
            ->assertJsonPath('code', 'VALIDATION_ERROR');
    }

    public function test_customer_books_pays_lists_and_cancels(): void
    {
        $customer = User::query()->firstOrFail();
        Sanctum::actingAs($customer, ['api']);
        $ratePlan = RatePlan::query()->where('code', 'BB')->firstOrFail();
        $inventory = fn () => DB::table('room_inventories')->where('room_type_id', $ratePlan->room_type_id)->where('stay_date', $this->checkIn)->value('booked_rooms');
        $bookedBefore = $inventory();

        $booking = $this->postJson('/api/v1/bookings', [
            'rate_plan_id' => $ratePlan->id,
            'check_in' => $this->checkIn,
            'check_out' => $this->checkOut,
            'adults' => 2,
            'guest_name' => 'Nguyễn Văn Minh',
            'guest_phone' => '0901 234 567',
            'guest_email' => 'minh@example.com',
            'estimated_arrival' => '15:00',
        ])->assertCreated()
            ->assertJsonPath('data.status', 'PENDING_PAYMENT')
            ->assertJsonPath('data.guest_phone', '+84901234567')
            ->json('data');
        $this->assertSame($bookedBefore + 1, $inventory());
        $this->assertNotNull($booking['hold_expires_at']);

        $this->postJson("/api/v1/bookings/{$booking['booking_code']}/payments", ['method' => 'VNPAY'])
            ->assertOk()
            ->assertJsonPath('data.status', 'CONFIRMED')
            ->assertJsonPath('data.amounts.outstanding_amount', 0);

        $this->getJson('/api/v1/bookings?scope=upcoming')->assertOk()->assertJsonPath('data.0.booking_code', $booking['booking_code']);

        $this->postJson("/api/v1/bookings/{$booking['booking_code']}/cancel", ['reason' => 'Đổi lịch'])
            ->assertOk()
            ->assertJsonPath('data.status', 'CANCELLED')
            ->assertJsonPath('data.amounts.cancel_fee', 0);
        $this->assertSame($bookedBefore, $inventory());
    }

    public function test_expired_hold_cannot_be_paid(): void
    {
        Sanctum::actingAs(User::query()->firstOrFail(), ['api']);
        $ratePlan = RatePlan::query()->firstOrFail();

        $code = $this->postJson('/api/v1/bookings', [
            'rate_plan_id' => $ratePlan->id, 'check_in' => $this->checkIn, 'check_out' => $this->checkOut, 'adults' => 2,
            'guest_name' => 'A', 'guest_phone' => '+84901234567', 'guest_email' => 'a@example.com',
        ])->json('data.booking_code');

        $this->travel(16)->minutes();

        $this->postJson("/api/v1/bookings/{$code}/payments", ['method' => 'MOMO'])->assertStatus(409)->assertJsonPath('code', 'BOOKING_EXPIRED');
        $this->getJson("/api/v1/bookings/{$code}")->assertJsonPath('data.status', 'EXPIRED');
    }

    public function test_sold_out_room_type_cannot_be_booked(): void
    {
        Sanctum::actingAs(User::query()->firstOrFail(), ['api']);
        $ratePlan = RatePlan::query()->firstOrFail();
        DB::table('room_inventories')->where('room_type_id', $ratePlan->room_type_id)->where('stay_date', $this->checkIn)
            ->update(['booked_rooms' => DB::raw('total_rooms')]);

        $this->postJson('/api/v1/bookings', [
            'rate_plan_id' => $ratePlan->id, 'check_in' => $this->checkIn, 'check_out' => $this->checkOut, 'adults' => 2,
            'guest_name' => 'A', 'guest_phone' => '+84901234567', 'guest_email' => 'a@example.com',
        ])->assertStatus(409)->assertJsonPath('code', 'ROOM_NOT_AVAILABLE');
    }

    public function test_bookings_require_sign_in(): void
    {
        $this->getJson('/api/v1/bookings')->assertUnauthorized();
    }
}
