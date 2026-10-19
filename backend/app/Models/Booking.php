<?php

namespace App\Models;

use App\Enums\BookingSource;
use App\Enums\BookingStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * Booking stored in the `bookings` table (Basic Design §6, BR-03 amounts).
 *
 * @property int $id
 * @property string $booking_code
 * @property int|null $user_id
 * @property int $hotel_id
 * @property Carbon $check_in_date
 * @property Carbon $check_out_date
 * @property int $nights
 * @property int $adults
 * @property int $children
 * @property string $guest_name
 * @property string|null $estimated_arrival
 * @property BookingStatus $status
 * @property BookingSource $source
 * @property int $total_amount
 * @property int $payable_amount
 * @property int $paid_amount
 * @property int $refunded_amount
 * @property-read Hotel $hotel
 * @property-read User|null $user
 * @property-read Collection<int, BookingRoom> $rooms
 * @property-read Collection<int, BookingStatusHistory> $statusHistories
 * @property-read Collection<int, Payment> $payments
 *
 * @author PH.Tuan
 * @created 2026-10-18
 */
class Booking extends Model
{
    use SoftDeletes;

    /**
     * Attributes that cannot be mass assigned; amounts and status change through services only.
     *
     * @var list<string>
     */
    protected $guarded = ['id'];

    /**
     * Get the attribute casts.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'check_in_date' => 'date',
            'check_out_date' => 'date',
            'status' => BookingStatus::class,
            'source' => BookingSource::class,
            'room_amount' => 'integer',
            'service_amount' => 'integer',
            'total_amount' => 'integer',
            'payable_amount' => 'integer',
            'paid_amount' => 'integer',
            'refunded_amount' => 'integer',
            'cancel_policy_snapshot' => 'array',
            'hold_expires_at' => 'datetime',
            'confirmed_at' => 'datetime',
            'checked_in_at' => 'datetime',
            'checked_out_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    /**
     * Get the hotel of the booking.
     *
     * @return BelongsTo<Hotel, $this>
     */
    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }

    /**
     * Get the customer who made the booking (null for counter bookings of walk-in guests).
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the booked rooms.
     *
     * @return HasMany<BookingRoom, $this>
     */
    public function rooms(): HasMany
    {
        return $this->hasMany(BookingRoom::class);
    }

    /**
     * Get the status changes, newest first (AD-04 timeline).
     *
     * @return HasMany<BookingStatusHistory, $this>
     */
    public function statusHistories(): HasMany
    {
        return $this->hasMany(BookingStatusHistory::class)->latest('id');
    }

    /**
     * Get the payment transactions.
     *
     * @return HasMany<Payment, $this>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class)->orderBy('id');
    }

    /**
     * Limit the query to the hotels an administrator may access.
     *
     * - Administrators without a hotel (head office) see every hotel.
     *
     * @param  Builder<self>  $query  Query being built.
     * @param  AdminUser  $administrator  Signed-in administrator.
     *
     * @return void
     */
    public function scopeVisibleTo(Builder $query, AdminUser $administrator): void
    {
        $query->when($administrator->hotel_id !== null, fn (Builder $scoped) => $scoped->where('hotel_id', $administrator->hotel_id));
    }

    /**
     * Get the amount still to be collected from the guest.
     *
     * @return int VND, never negative.
     */
    public function outstandingAmount(): int
    {
        return max(0, $this->payable_amount - ($this->paid_amount - $this->refunded_amount));
    }
}
