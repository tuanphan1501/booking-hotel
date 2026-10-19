<?php

namespace App\Data\Booking;

use App\Models\RatePlan;
use App\Models\RoomType;

/**
 * Room type that can be booked for a stay, with the price of each rate plan (MB-05 / MB-06).
 *
 * @author PH.Tuan
 * @created 2026-10-19
 */
final readonly class RoomOffer
{
    /**
     * @param  RoomType  $roomType  Room type.
     * @param  int  $roomsLeft  Rooms still free on the fullest night (0 = sold out).
     * @param  list<array{rate_plan: RatePlan, quote: PriceQuote}>  $ratePlans  Sellable plans with their price.
     */
    public function __construct(
        public RoomType $roomType,
        public int $roomsLeft,
        public array $ratePlans,
    ) {}
}
