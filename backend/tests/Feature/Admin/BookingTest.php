<?php

namespace Tests\Feature\Admin;

use App\Enums\BookingStatus;
use App\Enums\RoleCode;
use App\Models\AdminUser;
use App\Models\Booking;
use App\Models\Hotel;
use Database\Seeders\AmenitySeeder;
use Database\Seeders\DemoAccountSeeder;
use Database\Seeders\DemoBookingSeeder;
use Database\Seeders\HotelCatalogSeeder;
use Database\Seeders\MemberTierSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Database\Seeders\RoomInventorySeeder;
use Database\Seeders\SystemSettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * AD-03 Booking list and AD-04 Booking detail with the front-desk actions.
 *
 * @author PH.Tuan
 * @created 2026-10-19
 */
class BookingTest extends TestCase
{
    use RefreshDatabase;

    private AdminUser $administrator;

    /**
     * Seed the catalog and demo bookings; sign in as head office.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            MemberTierSeeder::class, RoleAndPermissionSeeder::class, SystemSettingSeeder::class, AmenitySeeder::class,
            HotelCatalogSeeder::class, RoomInventorySeeder::class, DemoAccountSeeder::class, DemoBookingSeeder::class,
        ]);
        $this->administrator = AdminUser::factory()->create();
    }

    public function test_list_searches_and_filters_bookings(): void
    {
        $this->actingAs($this->administrator, 'admin')->get('/admin/bookings')
            ->assertOk()->assertSee('Mã đặt phòng')->assertSee('HB');

        $this->get('/admin/bookings?q=tanaka')->assertOk()->assertSee('Tanaka Yuki')->assertDontSee('Phạm Quốc Bảo');
        $this->get('/admin/bookings?status=CANCELLED')->assertOk()->assertSee('chip rose', false)->assertDontSee('chip brand', false);
    }

    public function test_branch_manager_cannot_open_another_hotels_booking(): void
    {
        $danang = Hotel::query()->where('code', 'DN')->firstOrFail();
        $manager = AdminUser::factory()->withRole(RoleCode::HotelManager)->create(['hotel_id' => $danang->id]);
        $saigonBooking = Booking::query()->where('hotel_id', '!=', $danang->id)->firstOrFail();

        $this->actingAs($manager, 'admin')->get('/admin/bookings/'.$saigonBooking->booking_code)->assertNotFound();
    }

    public function test_front_desk_checks_in_and_out_with_room_assignment(): void
    {
        $booking = Booking::query()->where('guest_name', 'Tanaka Yuki')->whereDate('check_in_date', today())->firstOrFail();
        $bookingRoom = $booking->rooms()->firstOrFail();
        $roomId = DB::table('rooms')->where('room_type_id', $bookingRoom->room_type_id)->where('status', 'AVAILABLE')->value('id');

        $this->actingAs($this->administrator, 'admin')->get('/admin/bookings/'.$booking->booking_code)
            ->assertOk()->assertSee('Nhận phòng');

        $this->post("/admin/bookings/{$booking->booking_code}/check-in", ['rooms' => [$bookingRoom->id => $roomId]])
            ->assertSessionHas('status');
        $this->assertSame(BookingStatus::CheckedIn, $booking->fresh()->status);
        $this->assertSame('OCCUPIED', DB::table('rooms')->where('id', $roomId)->value('status'));

        $this->post("/admin/bookings/{$booking->booking_code}/check-out")->assertSessionHas('status');
        $this->assertSame(BookingStatus::CheckedOut, $booking->fresh()->status);
        $this->assertSame(2, DB::table('booking_status_histories')->where('booking_id', $booking->id)->count());
    }

    public function test_cancel_releases_inventory_and_requires_a_reason(): void
    {
        $booking = Booking::query()->where('status', BookingStatus::Confirmed)->whereDate('check_in_date', '>', today())->firstOrFail();
        $roomTypeId = $booking->rooms()->value('room_type_id');
        $bookedBefore = DB::table('room_inventories')->where('room_type_id', $roomTypeId)->where('stay_date', $booking->check_in_date->toDateString())->value('booked_rooms');

        $this->actingAs($this->administrator, 'admin')
            ->post("/admin/bookings/{$booking->booking_code}/cancel", ['reason' => ''])->assertSessionHasErrors('reason');

        $this->post("/admin/bookings/{$booking->booking_code}/cancel", ['reason' => 'Khách gọi điện hủy'])->assertSessionHas('status');

        $this->assertSame(BookingStatus::Cancelled, $booking->fresh()->status);
        $this->assertSame($bookedBefore - 1, DB::table('room_inventories')->where('room_type_id', $roomTypeId)->where('stay_date', $booking->check_in_date->toDateString())->value('booked_rooms'));
        $this->assertDatabaseHas('refunds', ['booking_id' => $booking->id, 'status' => 'PENDING']);
    }

    public function test_counter_payment_confirms_a_pending_booking(): void
    {
        $booking = Booking::query()->where('status', BookingStatus::PendingPayment)->firstOrFail();

        $this->actingAs($this->administrator, 'admin')
            ->post("/admin/bookings/{$booking->booking_code}/payments", ['amount' => $booking->total_amount, 'method' => 'CASH'])
            ->assertSessionHas('status');

        $booking->refresh();
        $this->assertSame(BookingStatus::Confirmed, $booking->status);
        $this->assertSame(0, $booking->outstandingAmount());
    }

    public function test_receptionist_without_cancel_permission_is_forbidden(): void
    {
        $receptionist = AdminUser::factory()->withRole(RoleCode::Accountant)->create();
        $booking = Booking::query()->where('status', BookingStatus::Confirmed)->firstOrFail();

        $this->actingAs($receptionist, 'admin')
            ->post("/admin/bookings/{$booking->booking_code}/cancel", ['reason' => 'x'])->assertForbidden();
    }
}
