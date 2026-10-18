<?php

namespace Tests\Feature\Admin;

use App\Enums\RoleCode;
use App\Models\AdminUser;
use App\Models\Hotel;
use App\Services\Dashboard\DashboardService;
use Carbon\CarbonImmutable;
use Database\Seeders\AmenitySeeder;
use Database\Seeders\DemoAccountSeeder;
use Database\Seeders\DemoBookingSeeder;
use Database\Seeders\HotelCatalogSeeder;
use Database\Seeders\MemberTierSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Database\Seeders\RoomInventorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * AD-02 Home screen: the first screen after sign-in, scoped to the administrator's hotel.
 *
 * @author PH.Tuan
 * @created 2026-10-18
 */
class DashboardTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Seed the catalog and the demo bookings around today.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            MemberTierSeeder::class, RoleAndPermissionSeeder::class, AmenitySeeder::class, HotelCatalogSeeder::class,
            RoomInventorySeeder::class, DemoAccountSeeder::class, DemoBookingSeeder::class,
        ]);
    }

    public function test_signed_in_administrator_lands_on_the_home_screen(): void
    {
        $administrator = AdminUser::factory()->create();

        $this->actingAs($administrator, 'admin')->get('/admin')
            ->assertOk()
            ->assertSee('Công suất phòng')
            ->assertSee('Doanh thu 7 ngày')
            ->assertSee('Cần xử lý')
            ->assertSee('Tanaka Yuki');
    }

    public function test_branch_manager_only_sees_figures_of_their_hotel(): void
    {
        $danang = Hotel::query()->where('code', 'DN')->firstOrFail();
        $saigon = Hotel::query()->where('code', 'SG')->firstOrFail();
        $danangManager = AdminUser::factory()->withRole(RoleCode::HotelManager)->create(['hotel_id' => $danang->id]);
        $headOffice = AdminUser::factory()->create();

        $service = app(DashboardService::class);
        $today = CarbonImmutable::today();
        $danangSummary = $service->summarize($danangManager, $today);
        $chainSummary = $service->summarize($headOffice, $today);

        $this->assertTrue($danangSummary->arrivals->every(fn ($booking) => $booking->hotel_id === $danang->id));
        $this->assertGreaterThan($danangSummary->arrivalCount, $chainSummary->arrivalCount);
        $this->assertCount(7, $danangSummary->revenueByDay);
        $this->assertNotSame($saigon->id, $danangSummary->arrivals->first()?->hotel_id);
    }
}
