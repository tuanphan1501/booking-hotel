<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Physical room (e.g. "801"), stored in the `rooms` table.
 *
 * - Status codes (ROOM_STATUS): AVAILABLE, OCCUPIED, CLEANING, MAINTENANCE.
 *
 * @property int $id
 * @property int $hotel_id
 * @property int $room_type_id
 * @property string $room_number
 * @property int|null $floor
 * @property string $status
 * @property-read RoomType $roomType
 *
 * @author PH.Tuan
 * @created 2026-10-18
 */
class Room extends Model
{
    use SoftDeletes;

    /**
     * Attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = ['hotel_id', 'room_type_id', 'room_number', 'floor', 'status', 'note'];

    /**
     * Get the type of the room.
     *
     * @return BelongsTo<RoomType, $this>
     */
    public function roomType(): BelongsTo
    {
        return $this->belongsTo(RoomType::class);
    }
}
