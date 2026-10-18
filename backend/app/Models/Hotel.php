<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Hotel (branch) of the chain, stored in the `hotels` table.
 *
 * - Administrators with a `hotel_id` only manage their own hotel (Basic Design §10).
 *
 * @property int $id
 * @property string $code
 * @property string $name
 * @property string|null $name_ja
 * @property string $city
 * @property string $status
 *
 * @author PH.Tuan
 * @created 2026-10-18
 */
class Hotel extends Model
{
    use SoftDeletes;

    /**
     * Attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'code', 'name', 'name_ja', 'description', 'description_ja', 'star_rating', 'address', 'city',
        'latitude', 'longitude', 'phone', 'email', 'check_in_time', 'check_out_time', 'status',
    ];

    /**
     * Get the room types offered by the hotel.
     *
     * @return HasMany<RoomType, $this>
     */
    public function roomTypes(): HasMany
    {
        return $this->hasMany(RoomType::class);
    }

    /**
     * Get the hotel name in the current locale.
     *
     * - Falls back to the Vietnamese name when no Japanese translation exists.
     *
     * @return string
     */
    public function localizedName(): string
    {
        return app()->getLocale() === 'ja' && filled($this->name_ja) ? (string) $this->name_ja : $this->name;
    }
}
