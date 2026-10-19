<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Sellable rate plan of a room type (`rate_plans`), e.g. "Room + breakfast" or "Non-refundable".
 *
 * - price_adjust_type: NONE, FIXED (VND per night, may be negative) or PERCENT.
 *
 * @property int $id
 * @property int $room_type_id
 * @property int $cancellation_policy_id
 * @property string $code
 * @property string $name
 * @property string|null $name_ja
 * @property bool $breakfast_included
 * @property string $price_adjust_type
 * @property float $price_adjust_value
 * @property int $min_nights
 * @property string $status
 *
 * @author PH.Tuan
 * @created 2026-10-19
 */
class RatePlan extends Model
{
    /**
     * Get the attribute casts.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['breakfast_included' => 'boolean', 'price_adjust_value' => 'float', 'min_nights' => 'integer'];
    }

    /**
     * Get the room type the plan is sold for.
     *
     * @return BelongsTo<RoomType, $this>
     */
    public function roomType(): BelongsTo
    {
        return $this->belongsTo(RoomType::class);
    }

    /**
     * Apply the plan adjustment to one night price, rounded to 1,000 VND (BR-02).
     *
     * @param  int  $nightPrice  Inventory price of the night.
     *
     * @return int
     */
    public function priceFor(int $nightPrice): int
    {
        $adjusted = match ($this->price_adjust_type) {
            'FIXED' => $nightPrice + $this->price_adjust_value,
            'PERCENT' => $nightPrice * (1 + $this->price_adjust_value / 100),
            default => $nightPrice,
        };

        return (int) max(0, round($adjusted / 1000) * 1000);
    }

    /**
     * Get the plan name in the current locale.
     *
     * @return string
     */
    public function localizedName(): string
    {
        return app()->getLocale() === 'ja' && filled($this->name_ja) ? (string) $this->name_ja : $this->name;
    }
}
