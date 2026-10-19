<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Hotel;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

/**
 * Hotel card of the search result (MB-04) and header of the hotel screen (MB-05).
 *
 * - `rooms_left` / `from_price` (per night) are added by the controller for the requested stay.
 *
 * @mixin Hotel
 *
 * @author PH.Tuan
 * @created 2026-10-19
 */
class HotelResource extends JsonResource
{
    /**
     * Availability for the requested stay: rooms_left and from_price (per night).
     *
     * @var array{rooms_left?: int, from_price?: int|null}
     */
    public array $stay = [];

    /**
     * Transform the hotel into an array.
     *
     * @param  Request  $request  Current request.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->localizedName(),
            'city' => $this->city,
            'address' => $this->address,
            'star_rating' => $this->star_rating,
            'rating_avg' => (float) $this->rating_avg,
            'review_count' => (int) $this->review_count,
            'description' => app()->getLocale() === 'ja' && filled($this->description_ja) ? $this->description_ja : $this->description,
            'check_in_time' => substr((string) $this->check_in_time, 0, 5),
            'check_out_time' => substr((string) $this->check_out_time, 0, 5),
            'cover_url' => self::coverUrl($this->id),
            'rooms_left' => $this->stay['rooms_left'] ?? null,
            'from_price' => $this->stay['from_price'] ?? null,
        ];
    }

    /**
     * Absolute URL of the hotel's cover image.
     *
     * @param  int  $hotelId  Hotel id.
     *
     * @return string|null
     */
    public static function coverUrl(int $hotelId): ?string
    {
        $path = DB::table('hotel_images')->where('hotel_id', $hotelId)->orderByDesc('is_cover')->orderBy('sort_order')->value('url');

        return $path === null ? null : url($path);
    }
}
