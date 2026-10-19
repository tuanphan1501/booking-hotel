<?php

namespace App\Http\Controllers\Api\V1;

use App\Data\Booking\RoomOffer;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Booking\StayRequest;
use App\Http\Resources\Api\V1\HotelResource;
use App\Models\Hotel;
use App\Services\Booking\AvailabilityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

/**
 * Hotel search (MB-04) and room list with prices (MB-05 / MB-06). Public: guests can browse before signing in.
 *
 * @author PH.Tuan
 * @created 2026-10-19
 */
class HotelController extends Controller
{
    /**
     * @param  AvailabilityService  $availabilityService  Availability and prices.
     */
    public function __construct(private readonly AvailabilityService $availabilityService) {}

    /**
     * List the published hotels with availability for the stay.
     *
     * - GET /api/v1/hotels?check_in&check_out&adults[&city]
     *
     * @param  StayRequest  $request  Stay dates and guests.
     *
     * @return JsonResponse { data: Hotel[] } — hotels with rooms first, then by lowest price.
     */
    public function index(StayRequest $request): JsonResponse
    {
        $summaries = $this->availabilityService->hotelSummaries($request->checkIn(), $request->checkOut(), $request->integer('adults'));

        $hotels = Hotel::query()
            ->where('status', 'PUBLISHED')
            ->when($request->query('city'), fn ($query, $city) => $query->where('city', $city))
            ->get()
            ->sortBy(fn (Hotel $hotel) => [($summaries[$hotel->id]['rooms_left'] ?? 0) > 0 ? 0 : 1, $summaries[$hotel->id]['from_price'] ?? PHP_INT_MAX])
            ->values()
            ->map(function (Hotel $hotel) use ($summaries): HotelResource {
                $resource = new HotelResource($hotel);
                $resource->stay = $summaries[$hotel->id] ?? ['rooms_left' => 0, 'from_price' => null];

                return $resource;
            });

        return response()->json(['data' => $hotels]);
    }

    /**
     * Get one hotel with its room types and the price of each rate plan for the stay.
     *
     * - GET /api/v1/hotels/{id}/rooms?check_in&check_out&adults
     *
     * @param  StayRequest  $request  Stay dates and guests.
     * @param  int  $hotelId  Hotel id.
     *
     * @return JsonResponse { data: { hotel, amenities, room_types[] } }
     */
    public function rooms(StayRequest $request, int $hotelId): JsonResponse
    {
        $hotel = Hotel::query()->where('status', 'PUBLISHED')->findOrFail($hotelId);
        $offers = $this->availabilityService->roomOffers($hotel, $request->checkIn(), $request->checkOut(), $request->integer('adults'), $request->integer('children'));

        return response()->json(['data' => [
            'hotel' => new HotelResource($hotel),
            'amenities' => DB::table('hotel_amenities')
                ->join('amenities', 'amenities.id', '=', 'hotel_amenities.amenity_id')
                ->where('hotel_amenities.hotel_id', $hotel->id)
                ->pluck('amenities.code'),
            'room_types' => array_map(fn (RoomOffer $offer) => $this->roomTypeData($offer), $offers),
        ]]);
    }

    /**
     * Shape one room offer for the app.
     *
     * @param  RoomOffer  $offer  Room type with prices.
     *
     * @return array<string, mixed>
     */
    private function roomTypeData(RoomOffer $offer): array
    {
        $roomType = $offer->roomType;
        $imagePath = DB::table('room_type_images')->where('room_type_id', $roomType->id)->value('url');

        return [
            'id' => $roomType->id,
            'name' => $roomType->localizedName(),
            'area_m2' => $roomType->area_m2 === null ? null : (float) $roomType->area_m2,
            'bed_type' => $roomType->bed_type,
            'view_type' => $roomType->view_type,
            'max_adults' => $roomType->max_adults,
            'image_url' => $imagePath === null ? null : url($imagePath),
            'rooms_left' => $offer->roomsLeft,
            'rate_plans' => array_map(fn (array $plan) => [
                'id' => $plan['rate_plan']->id,
                'name' => $plan['rate_plan']->localizedName(),
                'breakfast_included' => $plan['rate_plan']->breakfast_included,
                'refundable' => $plan['rate_plan']->code !== 'NR',
                'price_per_night' => intdiv($plan['quote']->roomAmount, max(1, $plan['quote']->nights)),
                'quote' => $plan['quote']->toArray(),
            ], $offer->ratePlans),
        ];
    }
}
