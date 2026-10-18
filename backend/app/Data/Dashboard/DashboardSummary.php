<?php

namespace App\Data\Dashboard;

use App\Models\Booking;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;

/**
 * Everything shown on the Web Admin home screen (AD-02) for one day and one hotel scope.
 *
 * @author PH.Tuan
 * @created 2026-10-18
 */
final readonly class DashboardSummary
{
    /**
     * @param  CarbonImmutable  $reportDate  Day the figures refer to (today in the hotel time zone).
     * @param  float|null  $occupancyRate  Sold rooms ÷ total rooms tonight, 0–100; null when no inventory exists.
     * @param  float|null  $occupancyChangePoints  Difference in percentage points versus the same day last week.
     * @param  int  $revenueToday  Room revenue of tonight's sold rooms (VND, before tax).
     * @param  float|null  $revenueChangePercent  Change versus the same day last week, in percent.
     * @param  int  $averageDailyRate  ADR: revenue ÷ rooms sold (VND).
     * @param  int  $revenuePerAvailableRoom  RevPAR: revenue ÷ total rooms (VND).
     * @param  int  $newBookings  Bookings created today.
     * @param  int  $cancellationsToday  Bookings cancelled today.
     * @param  int  $noShowsToday  Bookings marked as no-show for yesterday's arrivals.
     * @param  array<string, int>  $revenueByDay  Room revenue per night for the last 7 days, keyed by Y-m-d (oldest first).
     * @param  PendingTasks  $pendingTasks  Counters of the "Needs attention" panel.
     * @param  Collection<int, Booking>  $arrivals  Today's arrivals, earliest estimated arrival first.
     * @param  int  $arrivalCount  Total number of arrivals today (the list may be truncated).
     */
    public function __construct(
        public CarbonImmutable $reportDate,
        public ?float $occupancyRate,
        public ?float $occupancyChangePoints,
        public int $revenueToday,
        public ?float $revenueChangePercent,
        public int $averageDailyRate,
        public int $revenuePerAvailableRoom,
        public int $newBookings,
        public int $cancellationsToday,
        public int $noShowsToday,
        public array $revenueByDay,
        public PendingTasks $pendingTasks,
        public Collection $arrivals,
        public int $arrivalCount,
    ) {}

    /**
     * Get the highest daily revenue of the chart, used to scale the bars.
     *
     * @return int At least 1, so the view can divide by it safely.
     */
    public function maxDailyRevenue(): int
    {
        return max(1, ...array_values($this->revenueByDay));
    }
}
