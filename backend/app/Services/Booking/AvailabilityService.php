<?php

namespace App\Services\Booking;

use App\Data\Booking\PriceQuote;
use App\Data\Booking\RoomOffer;
use App\Models\Hotel;
use App\Models\RatePlan;
use App\Models\RoomType;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

/**
 * Reads availability and prices from `room_inventories` (MB-04 search, MB-05/06 room list, BR-02/BR-03/BR-04).
 *
 * - A room type is available when every night has total − booked − blocked ≥ 1 and is not stop-sell.
 * - Night price = room_inventories.price (falls back to room_types.base_price), then the rate plan adjustment.
 * - Amounts (BR-03): room amount → service charge % → VAT % on (room amount + service charge).
 *
 * @author PH.Tuan
 * @created 2026-10-19
 */
class AvailabilityService
{
    /**
     * List the room types of a hotel that can host the guests on every night, with prices per rate plan.
     *
     * @param  Hotel  $hotel  Hotel to search.
     * @param  CarbonImmutable  $checkIn  Arrival day.
     * @param  CarbonImmutable  $checkOut  Departure day (after $checkIn).
     * @param  int  $adults  Adults per room.
     * @param  int  $children  Children per room.
     *
     * @return list<RoomOffer> Sorted by the room types' sort order; sold-out types are included with 0 rooms left.
     */
    public function roomOffers(Hotel $hotel, CarbonImmutable $checkIn, CarbonImmutable $checkOut, int $adults, int $children = 0): array
    {
        $roomTypes = RoomType::query()
            ->where('hotel_id', $hotel->id)
            ->where('status', 'ACTIVE')
            ->where('max_adults', '>=', $adults)
            ->where('max_children', '>=', $children)
            ->with('ratePlans')
            ->orderBy('sort_order')
            ->get();

        $offers = [];
        foreach ($roomTypes as $roomType) {
            [$roomsLeft, $nightPrices] = $this->nightsOf($roomType, $checkIn, $checkOut);

            $offers[] = new RoomOffer(
                roomType: $roomType,
                roomsLeft: $roomsLeft,
                ratePlans: $roomType->ratePlans
                    ->filter(fn (RatePlan $ratePlan) => $ratePlan->min_nights <= count($nightPrices))
                    ->map(fn (RatePlan $ratePlan) => [
                        'rate_plan' => $ratePlan,
                        'quote' => $this->quote(array_map(fn (int $price) => $ratePlan->priceFor($price), $nightPrices)),
                    ])
                    ->values()
                    ->all(),
            );
        }

        return $offers;
    }

    /**
     * Lowest total price per hotel for the stay, for the search result list (MB-04).
     *
     * @param  CarbonImmutable  $checkIn  Arrival day.
     * @param  CarbonImmutable  $checkOut  Departure day.
     * @param  int  $adults  Adults per room.
     *
     * @return array<int, array{rooms_left: int, from_price: int|null}> Keyed by hotel id; from_price is per night.
     */
    public function hotelSummaries(CarbonImmutable $checkIn, CarbonImmutable $checkOut, int $adults): array
    {
        $summaries = [];

        foreach (Hotel::query()->where('status', 'PUBLISHED')->get() as $hotel) {
            $offers = array_filter($this->roomOffers($hotel, $checkIn, $checkOut, $adults), fn (RoomOffer $offer) => $offer->roomsLeft > 0);
            $prices = [];
            foreach ($offers as $offer) {
                foreach ($offer->ratePlans as $plan) {
                    $prices[] = intdiv($plan['quote']->roomAmount, max(1, $plan['quote']->nights));
                }
            }

            $summaries[$hotel->id] = [
                'rooms_left' => array_sum(array_map(fn (RoomOffer $offer) => $offer->roomsLeft, $offers)),
                'from_price' => $prices === [] ? null : min($prices),
            ];
        }

        return $summaries;
    }

    /**
     * Price the stay for one room of a rate plan.
     *
     * @param  RatePlan  $ratePlan  Booked plan.
     * @param  CarbonImmutable  $checkIn  Arrival day.
     * @param  CarbonImmutable  $checkOut  Departure day.
     *
     * @return array{0: int, 1: array<string, int>, 2: PriceQuote} [rooms left, plan price per night keyed by Y-m-d, quote]
     */
    public function priceStay(RatePlan $ratePlan, CarbonImmutable $checkIn, CarbonImmutable $checkOut): array
    {
        [$roomsLeft, $nightPrices] = $this->nightsOf($ratePlan->roomType, $checkIn, $checkOut);
        $planPrices = array_map(fn (int $price) => $ratePlan->priceFor($price), $nightPrices);

        return [$roomsLeft, $planPrices, $this->quote(array_values($planPrices))];
    }

    /**
     * Build the BR-03 price breakdown from the nightly prices.
     *
     * @param  list<int>|array<string, int>  $nightPrices  Price of each night.
     * @param  int  $rooms  Number of rooms.
     *
     * @return PriceQuote
     */
    public function quote(array $nightPrices, int $rooms = 1): PriceQuote
    {
        $roomAmount = array_sum($nightPrices) * $rooms;
        $serviceCharge = (int) round($roomAmount * $this->setting('fee.service_charge_percent', 5) / 100);
        $taxAmount = (int) round(($roomAmount + $serviceCharge) * $this->setting('tax.vat_percent', 8) / 100);

        return new PriceQuote(
            nights: count($nightPrices),
            roomAmount: $roomAmount,
            serviceCharge: $serviceCharge,
            taxAmount: $taxAmount,
            totalAmount: $roomAmount + $serviceCharge + $taxAmount,
        );
    }

    /**
     * Read a numeric system setting.
     *
     * @param  string  $key  system_settings.key.
     * @param  float  $default  Value used when the setting is missing.
     *
     * @return float
     */
    public function setting(string $key, float $default): float
    {
        $value = DB::table('system_settings')->where('key', $key)->value('value');

        return is_numeric($value) ? (float) $value : $default;
    }

    /**
     * Read the inventory of every night of the stay.
     *
     * @param  RoomType  $roomType  Room type.
     * @param  CarbonImmutable  $checkIn  Arrival day.
     * @param  CarbonImmutable  $checkOut  Departure day.
     *
     * @return array{0: int, 1: array<string, int>} [rooms left on the fullest night, inventory price keyed by Y-m-d]
     */
    private function nightsOf(RoomType $roomType, CarbonImmutable $checkIn, CarbonImmutable $checkOut): array
    {
        $inventories = DB::table('room_inventories')
            ->where('room_type_id', $roomType->id)
            ->whereBetween('stay_date', [$checkIn->toDateString(), $checkOut->subDay()->toDateString()])
            ->get()
            ->keyBy(fn (object $row) => substr((string) $row->stay_date, 0, 10));

        $roomsLeft = PHP_INT_MAX;
        $nightPrices = [];
        for ($night = $checkIn; $night->lt($checkOut); $night = $night->addDay()) {
            $inventory = $inventories[$night->toDateString()] ?? null;
            $roomsLeft = $inventory === null || $inventory->stop_sell
                ? 0
                : min($roomsLeft, (int) $inventory->total_rooms - (int) $inventory->booked_rooms - (int) $inventory->blocked_rooms);
            $nightPrices[$night->toDateString()] = (int) ($inventory->price ?? $roomType->base_price);
        }

        return [max(0, $roomsLeft === PHP_INT_MAX ? 0 : $roomsLeft), $nightPrices];
    }
}
