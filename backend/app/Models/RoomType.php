<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Room type of a hotel (e.g. "Deluxe Ocean View"), stored in the `room_types` table.
 *
 * @property int $id
 * @property int $hotel_id
 * @property string $code
 * @property string $name
 * @property string|null $name_ja
 * @property int $base_price
 * @property-read Hotel $hotel
 *
 * @author PH.Tuan
 * @created 2026-10-18
 */
class RoomType extends Model
{
    use SoftDeletes;

    /**
     * Attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'hotel_id', 'code', 'name', 'name_ja', 'description', 'description_ja', 'area_m2', 'bed_type',
        'max_adults', 'max_children', 'view_type', 'smoking', 'base_price', 'sort_order', 'status',
    ];

    /**
     * Get the attribute casts.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'base_price' => 'integer',
            'smoking' => 'boolean',
        ];
    }

    /**
     * Get the hotel that offers the room type.
     *
     * @return BelongsTo<Hotel, $this>
     */
    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }

    /**
     * Get the room type name in the current locale.
     *
     * - Falls back to the default name when no Japanese translation exists.
     *
     * @return string
     */
    public function localizedName(): string
    {
        return app()->getLocale() === 'ja' && filled($this->name_ja) ? (string) $this->name_ja : $this->name;
    }
}
