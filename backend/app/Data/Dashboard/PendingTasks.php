<?php

namespace App\Data\Dashboard;

/**
 * Counters of the "Needs attention" panel on the dashboard (AD-02 ④).
 *
 * @author PH.Tuan
 * @created 2026-10-18
 */
final readonly class PendingTasks
{
    /**
     * @param  int  $pendingRefunds  Refunds waiting for approval (AD-14).
     * @param  int  $pendingReviews  Reviews waiting for moderation (AD-12).
     * @param  int  $overdueDepartures  Checked-in guests due to leave today or earlier who have not checked out.
     * @param  int  $lowAvailabilityDays  Days in the next week with a room type that has 3 rooms or fewer left.
     */
    public function __construct(
        public int $pendingRefunds,
        public int $pendingReviews,
        public int $overdueDepartures,
        public int $lowAvailabilityDays,
    ) {}
}
