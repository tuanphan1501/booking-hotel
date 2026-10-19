<?php

namespace App\Models;

use App\Enums\BookingStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * One status change of a booking (`booking_status_histories`), shown in the AD-04 timeline.
 *
 * - actor_type: USER (customer), ADMIN or SYSTEM (payment callback, batch).
 *
 * @property int $id
 * @property int $booking_id
 * @property BookingStatus|null $from_status
 * @property BookingStatus $to_status
 * @property string $actor_type
 * @property int|null $actor_id
 * @property string|null $note
 * @property Carbon $created_at
 *
 * @author PH.Tuan
 * @created 2026-10-19
 */
class BookingStatusHistory extends Model
{
    /** Status changed by the customer in the app. */
    public const ACTOR_USER = 'USER';

    /** Status changed by an administrator in the Web Admin. */
    public const ACTOR_ADMIN = 'ADMIN';

    /** Status changed automatically (payment result, batch). */
    public const ACTOR_SYSTEM = 'SYSTEM';

    /** The table has only `created_at`. */
    public const UPDATED_AT = null;

    /**
     * Attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = ['booking_id', 'from_status', 'to_status', 'actor_type', 'actor_id', 'note'];

    /**
     * Get the attribute casts.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['from_status' => BookingStatus::class, 'to_status' => BookingStatus::class, 'created_at' => 'datetime'];
    }
}
