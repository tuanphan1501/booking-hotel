<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One booked room inside a booking, stored in the `booking_rooms` table.
 *
 * - `room_id` is empty until the front desk assigns a physical room (AD-04).
 *
 * @property int $id
 * @property int $booking_id
 * @property int $room_type_id
 * @property int $rate_plan_id
 * @property int|null $room_id
 * @property int $adults
 * @property int $children
 * @property int $subtotal
 * @property-read Booking $booking
 * @property-read RoomType $roomType
 * @property-read Room|null $room
 *
 * @author PH.Tuan
 * @created 2026-10-18
 */
class BookingRoom extends Model
{
    /**
     * The table has only `created_at`, so automatic timestamps are disabled.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = ['booking_id', 'room_type_id', 'rate_plan_id', 'room_id', 'adults', 'children', 'subtotal'];

    /**
     * Get the attribute casts.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['subtotal' => 'integer'];
    }

    /**
     * Get the booking the room belongs to.
     *
     * @return BelongsTo<Booking, $this>
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Get the booked room type.
     *
     * @return BelongsTo<RoomType, $this>
     */
    public function roomType(): BelongsTo
    {
        return $this->belongsTo(RoomType::class);
    }

    /**
     * Get the booked rate plan.
     *
     * @return BelongsTo<RatePlan, $this>
     */
    public function ratePlan(): BelongsTo
    {
        return $this->belongsTo(RatePlan::class);
    }

    /**
     * Get the assigned physical room, if any.
     *
     * @return BelongsTo<Room, $this>
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }
}
