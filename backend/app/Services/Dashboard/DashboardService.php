<?php

namespace App\Services\Dashboard;

use App\Data\Dashboard\DashboardSummary;
use App\Data\Dashboard\PendingTasks;
use App\Enums\BookingStatus;
use App\Models\AdminUser;
use App\Models\Booking;
use App\Models\RoomType;
use Carbon\CarbonImmutable;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Computes the figures of the Web Admin home screen (AD-02).
 *
 * - Every figure is limited to the administrator's hotel; head-office accounts see the whole chain.
 * - Revenue = room revenue of sold nights (booking_room_nights) for CONFIRMED / CHECKED_IN /
 *   CHECKED_OUT bookings, before tax and service charge.
 * - Occupancy, ADR and RevPAR follow the usual hotel definitions using room_inventories.
 *
 * @author PH.Tuan
 * @created 2026-10-18
 */
class DashboardService
{
    /** Number of days shown in the revenue chart, including today. */
    private const CHART_DAYS = 7;

    /** Number of arrivals listed in the "Arrivals today" table. */
    private const ARRIVALS_LIMIT = 10;

    /** A room type with this many rooms left or fewer counts as low availability. */
    private const LOW_AVAILABILITY_THRESHOLD = 3;

    /**
     * Build the dashboard summary for an administrator.
     *
     * @param  AdminUser  $administrator  Signed-in administrator; decides the hotel scope.
     * @param  CarbonImmutable  $reportDate  Day to report on, usually today.
     *
     * @return DashboardSummary
     */
    public function summarize(AdminUser $administrator, CarbonImmutable $reportDate): DashboardSummary
    {
        $roomTypeIds = RoomType::query()
            ->when($administrator->hotel_id !== null, fn ($query) => $query->where('hotel_id', $administrator->hotel_id))
            ->pluck('id')
            ->all();

        $revenueByDay = $this->revenueByDay($administrator, $reportDate->subDays(self::CHART_DAYS - 1), $reportDate);
        $revenueToday = $revenueByDay[$reportDate->toDateString()] ?? 0;
        $revenueSameDayLastWeek = $this->revenueByDay($administrator, $reportDate->subWeek(), $reportDate->subWeek())[$reportDate->subWeek()->toDateString()] ?? 0;

        [$totalRooms, $soldRooms] = $this->inventoryTotals($roomTypeIds, $reportDate);
        [$totalRoomsLastWeek, $soldRoomsLastWeek] = $this->inventoryTotals($roomTypeIds, $reportDate->subWeek());

        $occupancyRate = $this->percentage($soldRooms, $totalRooms);
        $occupancyLastWeek = $this->percentage($soldRoomsLastWeek, $totalRoomsLastWeek);
        $roomNightsSoldToday = $this->roomNightsSold($administrator, $reportDate);

        $arrivalsQuery = Booking::query()
            ->visibleTo($administrator)
            ->whereDate('check_in_date', $reportDate)
            ->whereIn('status', [BookingStatus::Confirmed, BookingStatus::CheckedIn]);

        return new DashboardSummary(
            reportDate: $reportDate,
            occupancyRate: $occupancyRate,
            occupancyChangePoints: $occupancyRate !== null && $occupancyLastWeek !== null ? round($occupancyRate - $occupancyLastWeek, 1) : null,
            revenueToday: $revenueToday,
            revenueChangePercent: $revenueSameDayLastWeek > 0 ? round(($revenueToday - $revenueSameDayLastWeek) / $revenueSameDayLastWeek * 100, 1) : null,
            averageDailyRate: $roomNightsSoldToday > 0 ? intdiv($revenueToday, $roomNightsSoldToday) : 0,
            revenuePerAvailableRoom: $totalRooms > 0 ? intdiv($revenueToday, $totalRooms) : 0,
            newBookings: Booking::query()->visibleTo($administrator)->whereDate('created_at', $reportDate)->count(),
            cancellationsToday: Booking::query()->visibleTo($administrator)->whereDate('cancelled_at', $reportDate)->count(),
            noShowsToday: Booking::query()->visibleTo($administrator)
                ->where('status', BookingStatus::NoShow)
                ->whereDate('check_in_date', $reportDate->subDay())
                ->count(),
            revenueByDay: $revenueByDay,
            pendingTasks: $this->pendingTasks($administrator, $roomTypeIds, $reportDate),
            arrivals: (clone $arrivalsQuery)
                ->with(['rooms.roomType', 'rooms.room'])
                ->orderByRaw('estimated_arrival NULLS LAST')
                ->limit(self::ARRIVALS_LIMIT)
                ->get(),
            arrivalCount: $arrivalsQuery->count(),
        );
    }

    /**
     * Sum room revenue per night between two dates (inclusive), filling missing days with 0.
     *
     * @param  AdminUser  $administrator  Decides the hotel scope.
     * @param  CarbonImmutable  $fromDate  First night.
     * @param  CarbonImmutable  $toDate  Last night.
     *
     * @return array<string, int> Revenue keyed by Y-m-d, oldest first.
     */
    private function revenueByDay(AdminUser $administrator, CarbonImmutable $fromDate, CarbonImmutable $toDate): array
    {
        $revenueByDate = $this->soldNightsQuery($administrator)
            ->whereBetween('booking_room_nights.stay_date', [$fromDate->toDateString(), $toDate->toDateString()])
            ->groupBy('booking_room_nights.stay_date')
            ->selectRaw('booking_room_nights.stay_date::text as stay_date, sum(booking_room_nights.price) as revenue')
            ->pluck('revenue', 'stay_date');

        $series = [];
        for ($day = $fromDate; $day->lte($toDate); $day = $day->addDay()) {
            $series[$day->toDateString()] = (int) ($revenueByDate[$day->toDateString()] ?? 0);
        }

        return $series;
    }

    /**
     * Count room nights sold for one night.
     *
     * @param  AdminUser  $administrator  Decides the hotel scope.
     * @param  CarbonImmutable  $stayDate  Night to count.
     *
     * @return int
     */
    private function roomNightsSold(AdminUser $administrator, CarbonImmutable $stayDate): int
    {
        return $this->soldNightsQuery($administrator)
            ->where('booking_room_nights.stay_date', $stayDate->toDateString())
            ->count();
    }

    /**
     * Base query over sold nights of revenue-generating bookings within the administrator's scope.
     *
     * @param  AdminUser  $administrator  Decides the hotel scope.
     *
     * @return Builder
     */
    private function soldNightsQuery(AdminUser $administrator): Builder
    {
        return DB::table('booking_room_nights')
            ->join('booking_rooms', 'booking_rooms.id', '=', 'booking_room_nights.booking_room_id')
            ->join('bookings', 'bookings.id', '=', 'booking_rooms.booking_id')
            ->whereNull('bookings.deleted_at')
            ->whereIn('bookings.status', array_map(fn (BookingStatus $status) => $status->value, BookingStatus::revenueGenerating()))
            ->when($administrator->hotel_id !== null, fn (Builder $query) => $query->where('bookings.hotel_id', $administrator->hotel_id));
    }

    /**
     * Read total and sold rooms of one night from the daily inventory.
     *
     * @param  list<int>  $roomTypeIds  Room types in scope.
     * @param  CarbonImmutable  $stayDate  Night to read.
     *
     * @return array{0: int, 1: int} [total rooms, sold rooms]
     */
    private function inventoryTotals(array $roomTypeIds, CarbonImmutable $stayDate): array
    {
        $totals = DB::table('room_inventories')
            ->whereIn('room_type_id', $roomTypeIds)
            ->where('stay_date', $stayDate->toDateString())
            ->selectRaw('coalesce(sum(total_rooms), 0) as total_rooms, coalesce(sum(booked_rooms), 0) as booked_rooms')
            ->first();

        return [(int) ($totals->total_rooms ?? 0), (int) ($totals->booked_rooms ?? 0)];
    }

    /**
     * Count the items of the "Needs attention" panel.
     *
     * @param  AdminUser  $administrator  Decides the hotel scope.
     * @param  list<int>  $roomTypeIds  Room types in scope.
     * @param  CarbonImmutable  $reportDate  Today.
     *
     * @return PendingTasks
     */
    private function pendingTasks(AdminUser $administrator, array $roomTypeIds, CarbonImmutable $reportDate): PendingTasks
    {
        $hotelId = $administrator->hotel_id;

        return new PendingTasks(
            pendingRefunds: DB::table('refunds')
                ->join('bookings', 'bookings.id', '=', 'refunds.booking_id')
                ->where('refunds.status', 'PENDING')
                ->when($hotelId !== null, fn (Builder $query) => $query->where('bookings.hotel_id', $hotelId))
                ->count(),
            pendingReviews: DB::table('reviews')
                ->where('status', 'PENDING')
                ->whereNull('deleted_at')
                ->when($hotelId !== null, fn (Builder $query) => $query->where('hotel_id', $hotelId))
                ->count(),
            overdueDepartures: Booking::query()
                ->visibleTo($administrator)
                ->where('status', BookingStatus::CheckedIn)
                ->whereDate('check_out_date', '<=', $reportDate)
                ->count(),
            lowAvailabilityDays: DB::table('room_inventories')
                ->whereIn('room_type_id', $roomTypeIds)
                ->whereBetween('stay_date', [$reportDate->toDateString(), $reportDate->addDays(6)->toDateString()])
                ->where('stop_sell', false)
                ->whereRaw('total_rooms - booked_rooms - blocked_rooms <= ?', [self::LOW_AVAILABILITY_THRESHOLD])
                ->distinct()
                ->count('stay_date'),
        );
    }

    /**
     * Calculate a percentage rounded to one decimal.
     *
     * @param  int  $part  Numerator.
     * @param  int  $whole  Denominator.
     *
     * @return float|null Null when the denominator is 0 (no data).
     */
    private function percentage(int $part, int $whole): ?float
    {
        return $whole > 0 ? round($part / $whole * 100, 1) : null;
    }
}
