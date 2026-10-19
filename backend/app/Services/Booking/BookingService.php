<?php

namespace App\Services\Booking;

use App\Enums\BookingSource;
use App\Enums\BookingStatus;
use App\Enums\ErrorCode;
use App\Exceptions\BusinessException;
use App\Models\AdminUser;
use App\Models\Booking;
use App\Models\BookingStatusHistory;
use App\Models\Payment;
use App\Models\RatePlan;
use App\Models\Room;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Booking lifecycle shared by the Mobile App API and the Web Admin (Basic Design §6.1).
 *
 * PENDING_PAYMENT → CONFIRMED → CHECKED_IN → CHECKED_OUT; PENDING_PAYMENT / CONFIRMED → CANCELLED;
 * PENDING_PAYMENT → EXPIRED when the 15-minute hold runs out (BR-04).
 *
 * - Inventory (room_inventories.booked_rooms) is reserved on creation and released on cancel / expiry,
 *   with the inventory rows locked so two guests cannot take the last room.
 * - Every change is written to booking_status_histories.
 *
 * @author PH.Tuan
 * @created 2026-10-19
 */
class BookingService
{
    /**
     * @param  AvailabilityService  $availabilityService  Prices and availability.
     */
    public function __construct(private readonly AvailabilityService $availabilityService) {}

    /**
     * Create an app booking for one room and hold the inventory (MB-07 → MB-08).
     *
     * @param  User  $customer  Signed-in customer.
     * @param  array{rate_plan_id: int, check_in: string, check_out: string, adults: int, children?: int,
     *               guest_name: string, guest_phone: string, guest_email: string, estimated_arrival?: string|null,
     *               special_request?: string|null}  $input  Validated request data.
     *
     * @return Booking PENDING_PAYMENT booking with hold_expires_at.
     *
     * @throws BusinessException ROOM_NOT_AVAILABLE.
     */
    public function createForCustomer(User $customer, array $input): Booking
    {
        $ratePlan = RatePlan::query()->with('roomType.hotel')->where('status', 'ACTIVE')->findOrFail($input['rate_plan_id']);
        $checkIn = CarbonImmutable::parse($input['check_in'])->startOfDay();
        $checkOut = CarbonImmutable::parse($input['check_out'])->startOfDay();

        return DB::transaction(function () use ($customer, $input, $ratePlan, $checkIn, $checkOut): Booking {
            $this->lockInventory($ratePlan->room_type_id, $checkIn, $checkOut);
            [$roomsLeft, $nightPrices, $quote] = $this->availabilityService->priceStay($ratePlan, $checkIn, $checkOut);

            if ($roomsLeft < 1) {
                throw new BusinessException(ErrorCode::RoomNotAvailable);
            }

            $holdMinutes = (int) $this->availabilityService->setting('booking.hold_minutes', 15);
            $policy = DB::table('cancellation_policies')->where('id', $ratePlan->cancellation_policy_id)->first();

            $booking = Booking::query()->create([
                'booking_code' => $this->newBookingCode(),
                'user_id' => $customer->id,
                'hotel_id' => $ratePlan->roomType->hotel_id,
                'check_in_date' => $checkIn->toDateString(),
                'check_out_date' => $checkOut->toDateString(),
                'nights' => $quote->nights,
                'adults' => $input['adults'],
                'children' => $input['children'] ?? 0,
                'guest_name' => $input['guest_name'],
                'guest_email' => $input['guest_email'],
                'guest_phone' => $input['guest_phone'],
                'estimated_arrival' => $input['estimated_arrival'] ?? null,
                'special_request' => $input['special_request'] ?? null,
                'status' => BookingStatus::PendingPayment,
                'source' => BookingSource::App,
                'room_amount' => $quote->roomAmount,
                'service_charge' => $quote->serviceCharge,
                'tax_amount' => $quote->taxAmount,
                'total_amount' => $quote->totalAmount,
                'payable_amount' => $quote->totalAmount,
                'cancel_policy_snapshot' => [
                    'name' => $policy->name ?? null,
                    'is_refundable' => (bool) ($policy->is_refundable ?? true),
                    'free_cancel_hours' => (int) ($policy->free_cancel_hours ?? 48),
                    'penalty_percent' => (float) ($policy->penalty_percent ?? 100),
                    'no_show_percent' => (float) ($policy->no_show_percent ?? 100),
                ],
                'hold_expires_at' => now()->addMinutes($holdMinutes),
                'created_by' => null,
            ]);

            $bookingRoom = $booking->rooms()->create([
                'room_type_id' => $ratePlan->room_type_id,
                'rate_plan_id' => $ratePlan->id,
                'adults' => $input['adults'],
                'children' => $input['children'] ?? 0,
                'subtotal' => $quote->roomAmount,
            ]);

            DB::table('booking_room_nights')->insert(array_map(
                fn (string $stayDate, int $price) => ['booking_room_id' => $bookingRoom->id, 'stay_date' => $stayDate, 'price' => $price],
                array_keys($nightPrices),
                array_values($nightPrices),
            ));

            $this->changeInventory($ratePlan->room_type_id, $checkIn, $checkOut, +1);
            $this->recordHistory($booking, null, BookingStatus::PendingPayment, BookingStatusHistory::ACTOR_USER, $customer->id);

            return $booking;
        });
    }

    /**
     * Pay an app booking (MB-08).
     *
     * - No payment gateway is connected yet: the sandbox driver approves the payment immediately.
     *   Replace this step with the VNPay / MoMo / Stripe redirect + IPN when the merchant accounts exist.
     *
     * @param  Booking  $booking  Booking of the signed-in customer.
     * @param  string  $method  One of Payment::APP_METHODS.
     *
     * @return Booking CONFIRMED booking.
     *
     * @throws BusinessException BOOKING_EXPIRED or INVALID_STATUS_TRANSITION.
     */
    public function payInApp(Booking $booking, string $method): Booking
    {
        // Expire in its own transaction so the release of the rooms is kept when the error is thrown.
        $isExpired = DB::transaction(function () use ($booking): bool {
            $booking = Booking::query()->lockForUpdate()->findOrFail($booking->id);

            if ($booking->status !== BookingStatus::PendingPayment || ! $booking->hold_expires_at?->isPast()) {
                return false;
            }

            $this->expire($booking);

            return true;
        });

        if ($isExpired) {
            throw new BusinessException(ErrorCode::BookingExpired);
        }

        return DB::transaction(function () use ($booking, $method): Booking {
            $booking = Booking::query()->lockForUpdate()->findOrFail($booking->id);

            $this->ensureStatus($booking, [BookingStatus::PendingPayment]);
            $amount = $booking->outstandingAmount();

            Payment::query()->create([
                'payment_code' => 'PM'.strtoupper(Str::random(10)),
                'booking_id' => $booking->id,
                'method' => $method,
                'amount' => $amount,
                'status' => 'SUCCESS',
                'provider_txn_id' => 'SANDBOX-'.strtoupper(Str::random(12)),
                'paid_at' => now(),
            ]);

            $booking->forceFill(['paid_amount' => $booking->paid_amount + $amount, 'hold_expires_at' => null]);
            $this->transition($booking, BookingStatus::Confirmed, BookingStatusHistory::ACTOR_SYSTEM, null, "Sandbox $method");

            return $booking;
        });
    }

    /**
     * Cancel a booking (MB-11c by the customer, AD-04 by an administrator).
     *
     * - Customer cancellations follow the policy snapshot: free until free_cancel_hours before the
     *   hotel's check-in time, otherwise penalty_percent of the first night; non-refundable plans keep everything.
     * - Administrators cancel without a fee (they decide refunds in AD-14).
     * - A refund request (PENDING) is created for the paid amount above the fee.
     *
     * @param  Booking  $booking  Booking to cancel.
     * @param  string  $reason  Reason typed by the actor.
     * @param  User|AdminUser  $actor  Customer or administrator.
     *
     * @return Booking CANCELLED booking.
     *
     * @throws BusinessException INVALID_STATUS_TRANSITION.
     */
    public function cancel(Booking $booking, string $reason, User|AdminUser $actor): Booking
    {
        return DB::transaction(function () use ($booking, $reason, $actor): Booking {
            $booking = Booking::query()->lockForUpdate()->with('rooms')->findOrFail($booking->id);
            $this->ensureStatus($booking, [BookingStatus::PendingPayment, BookingStatus::Confirmed]);

            $isAdmin = $actor instanceof AdminUser;
            $fee = $isAdmin ? 0 : $this->cancellationFee($booking);
            $refundable = max(0, $booking->paid_amount - $booking->refunded_amount - $fee);

            foreach ($booking->rooms as $bookingRoom) {
                $this->changeInventory($bookingRoom->room_type_id, $booking->check_in_date->toImmutable(), $booking->check_out_date->toImmutable(), -1);
            }

            $payment = Payment::query()->where('booking_id', $booking->id)->where('status', 'SUCCESS')->latest('id')->first();
            if ($refundable > 0 && $payment !== null) {
                DB::table('refunds')->insert([
                    'refund_code' => 'RF'.strtoupper(Str::random(10)),
                    'payment_id' => $payment->id,
                    'booking_id' => $booking->id,
                    'amount' => $refundable,
                    'reason' => $reason,
                    'status' => 'PENDING',
                ]);
            }

            $booking->forceFill([
                'cancelled_at' => now(),
                'cancel_reason' => $reason,
                'cancel_fee' => $fee,
                'cancelled_by' => $isAdmin ? BookingStatusHistory::ACTOR_ADMIN : BookingStatusHistory::ACTOR_USER,
                'hold_expires_at' => null,
            ]);
            $this->transition($booking, BookingStatus::Cancelled, $isAdmin ? BookingStatusHistory::ACTOR_ADMIN : BookingStatusHistory::ACTOR_USER, $actor->id, $reason);

            return $booking;
        });
    }

    /**
     * Check the guest in and assign physical rooms (AD-04 "Check in").
     *
     * @param  Booking  $booking  CONFIRMED booking arriving today or earlier.
     * @param  array<int, int>  $roomIdsByBookingRoom  Room id to assign, keyed by booking_rooms.id.
     * @param  AdminUser  $administrator  Front-desk administrator.
     *
     * @return Booking CHECKED_IN booking.
     *
     * @throws BusinessException INVALID_STATUS_TRANSITION or ROOM_NOT_AVAILABLE.
     */
    public function checkIn(Booking $booking, array $roomIdsByBookingRoom, AdminUser $administrator): Booking
    {
        return DB::transaction(function () use ($booking, $roomIdsByBookingRoom, $administrator): Booking {
            $booking = Booking::query()->lockForUpdate()->with('rooms')->findOrFail($booking->id);
            $this->ensureStatus($booking, [BookingStatus::Confirmed]);

            if ($booking->check_in_date->isFuture()) {
                throw new BusinessException(ErrorCode::InvalidStatusTransition);
            }

            foreach ($booking->rooms as $bookingRoom) {
                $roomId = $roomIdsByBookingRoom[$bookingRoom->id] ?? $bookingRoom->room_id;
                $room = $roomId === null ? null : Room::query()->lockForUpdate()->find($roomId);

                if ($room === null || $room->room_type_id !== $bookingRoom->room_type_id || $room->status !== 'AVAILABLE') {
                    throw new BusinessException(ErrorCode::RoomNotAvailable);
                }

                $bookingRoom->forceFill(['room_id' => $room->id])->save();
                $room->forceFill(['status' => 'OCCUPIED'])->save();
            }

            $booking->forceFill(['checked_in_at' => now()]);
            $this->transition($booking, BookingStatus::CheckedIn, BookingStatusHistory::ACTOR_ADMIN, $administrator->id);

            return $booking;
        });
    }

    /**
     * Check the guest out; rooms go to housekeeping (AD-04 "Check out").
     *
     * @param  Booking  $booking  CHECKED_IN booking.
     * @param  AdminUser  $administrator  Front-desk administrator.
     *
     * @return Booking CHECKED_OUT booking.
     *
     * @throws BusinessException INVALID_STATUS_TRANSITION.
     */
    public function checkOut(Booking $booking, AdminUser $administrator): Booking
    {
        return DB::transaction(function () use ($booking, $administrator): Booking {
            $booking = Booking::query()->lockForUpdate()->with('rooms')->findOrFail($booking->id);
            $this->ensureStatus($booking, [BookingStatus::CheckedIn]);

            Room::query()->whereIn('id', $booking->rooms->pluck('room_id')->filter())->update(['status' => 'CLEANING']);
            $booking->forceFill(['checked_out_at' => now()]);
            $this->transition($booking, BookingStatus::CheckedOut, BookingStatusHistory::ACTOR_ADMIN, $administrator->id);

            return $booking;
        });
    }

    /**
     * Record a payment taken at the front desk (AD-04 "Record counter payment").
     *
     * - A PENDING_PAYMENT booking becomes CONFIRMED once something is paid.
     *
     * @param  Booking  $booking  Booking to pay.
     * @param  int  $amount  Amount received (VND), at most the outstanding amount.
     * @param  string  $method  One of Payment::COUNTER_METHODS.
     * @param  AdminUser  $administrator  Administrator collecting the money.
     *
     * @return Booking
     *
     * @throws BusinessException INVALID_STATUS_TRANSITION.
     */
    public function recordCounterPayment(Booking $booking, int $amount, string $method, AdminUser $administrator): Booking
    {
        return DB::transaction(function () use ($booking, $amount, $method, $administrator): Booking {
            $booking = Booking::query()->lockForUpdate()->findOrFail($booking->id);
            $this->ensureStatus($booking, [BookingStatus::PendingPayment, BookingStatus::Confirmed, BookingStatus::CheckedIn]);
            $amount = min($amount, $booking->outstandingAmount());

            if ($amount <= 0) {
                throw new BusinessException(ErrorCode::InvalidStatusTransition);
            }

            Payment::query()->create([
                'payment_code' => 'PM'.strtoupper(Str::random(10)),
                'booking_id' => $booking->id,
                'method' => $method,
                'amount' => $amount,
                'status' => 'SUCCESS',
                'paid_at' => now(),
                'collected_by' => $administrator->id,
                'created_by' => $administrator->id,
            ]);

            $booking->forceFill(['paid_amount' => $booking->paid_amount + $amount, 'updated_by' => $administrator->id]);

            if ($booking->status === BookingStatus::PendingPayment) {
                $booking->hold_expires_at = null;
                $this->transition($booking, BookingStatus::Confirmed, BookingStatusHistory::ACTOR_ADMIN, $administrator->id, $method);
            } else {
                $booking->save();
            }

            return $booking;
        });
    }

    /**
     * Expire a PENDING_PAYMENT booking whose hold ran out and release its rooms (BR-04 / BAT01).
     *
     * @param  Booking  $booking  Locked booking.
     *
     * @return void
     */
    public function expire(Booking $booking): void
    {
        foreach ($booking->rooms()->get() as $bookingRoom) {
            $this->changeInventory($bookingRoom->room_type_id, $booking->check_in_date->toImmutable(), $booking->check_out_date->toImmutable(), -1);
        }

        $booking->hold_expires_at = null;
        $this->transition($booking, BookingStatus::Expired, BookingStatusHistory::ACTOR_SYSTEM, null);
    }

    /**
     * Fee the customer pays when cancelling now (policy snapshot of the booking).
     *
     * @param  Booking  $booking  Booking being cancelled.
     *
     * @return int VND.
     */
    public function cancellationFee(Booking $booking): int
    {
        $policy = $booking->cancel_policy_snapshot ?? [];

        if ($booking->paid_amount <= 0) {
            return 0;
        }

        if (! ($policy['is_refundable'] ?? true)) {
            return $booking->paid_amount;
        }

        $checkInTime = (string) (DB::table('hotels')->where('id', $booking->hotel_id)->value('check_in_time') ?? '14:00');
        $arrival = CarbonImmutable::parse($booking->check_in_date->toDateString().' '.$checkInTime);

        if (now()->lte($arrival->subHours((int) ($policy['free_cancel_hours'] ?? 48)))) {
            return 0;
        }

        $firstNight = (int) DB::table('booking_room_nights')
            ->join('booking_rooms', 'booking_rooms.id', '=', 'booking_room_nights.booking_room_id')
            ->where('booking_rooms.booking_id', $booking->id)
            ->where('booking_room_nights.stay_date', $booking->check_in_date->toDateString())
            ->sum('booking_room_nights.price');

        return (int) min($booking->paid_amount, round($firstNight * ($policy['penalty_percent'] ?? 100) / 100));
    }

    /**
     * Rooms of the booked type that can be assigned at check-in (AD-04).
     *
     * @param  int  $roomTypeId  Booked room type.
     *
     * @return Collection<int, Room>
     */
    public function assignableRooms(int $roomTypeId): Collection
    {
        return Room::query()->where('room_type_id', $roomTypeId)->where('status', 'AVAILABLE')->orderBy('room_number')->get();
    }

    /**
     * Change the status and write the history row.
     *
     * @param  Booking  $booking  Booking (other pending changes are saved too).
     * @param  BookingStatus  $toStatus  New status.
     * @param  string  $actorType  BookingStatusHistory::ACTOR_*.
     * @param  int|null  $actorId  Customer or administrator id.
     * @param  string|null  $note  Optional note.
     *
     * @return void
     */
    private function transition(Booking $booking, BookingStatus $toStatus, string $actorType, ?int $actorId, ?string $note = null): void
    {
        $fromStatus = $booking->status;
        $booking->status = $toStatus;

        if ($toStatus === BookingStatus::Confirmed) {
            $booking->confirmed_at = now();
        }

        $booking->save();
        $this->recordHistory($booking, $fromStatus, $toStatus, $actorType, $actorId, $note);
    }

    /**
     * Write one booking_status_histories row.
     *
     * @return void
     */
    private function recordHistory(Booking $booking, ?BookingStatus $fromStatus, BookingStatus $toStatus, string $actorType, ?int $actorId, ?string $note = null): void
    {
        BookingStatusHistory::query()->create([
            'booking_id' => $booking->id,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'actor_type' => $actorType,
            'actor_id' => $actorId,
            'note' => $note === null ? null : mb_substr($note, 0, 500),
        ]);
    }

    /**
     * Stop when the booking is not in one of the allowed statuses.
     *
     * @param  Booking  $booking  Booking.
     * @param  list<BookingStatus>  $allowedStatuses  Statuses the action starts from.
     *
     * @return void
     *
     * @throws BusinessException INVALID_STATUS_TRANSITION.
     */
    private function ensureStatus(Booking $booking, array $allowedStatuses): void
    {
        if (! in_array($booking->status, $allowedStatuses, true)) {
            throw new BusinessException(ErrorCode::InvalidStatusTransition);
        }
    }

    /**
     * Lock the inventory rows of the stay until the transaction ends.
     *
     * @return void
     */
    private function lockInventory(int $roomTypeId, CarbonImmutable $checkIn, CarbonImmutable $checkOut): void
    {
        DB::table('room_inventories')
            ->where('room_type_id', $roomTypeId)
            ->whereBetween('stay_date', [$checkIn->toDateString(), $checkOut->subDay()->toDateString()])
            ->lockForUpdate()
            ->get();
    }

    /**
     * Add or remove one booked room on every night of the stay.
     *
     * @param  int  $delta  +1 to reserve, −1 to release.
     *
     * @return void
     */
    private function changeInventory(int $roomTypeId, CarbonImmutable $checkIn, CarbonImmutable $checkOut, int $delta): void
    {
        $query = DB::table('room_inventories')
            ->where('room_type_id', $roomTypeId)
            ->whereBetween('stay_date', [$checkIn->toDateString(), $checkOut->subDay()->toDateString()]);

        $delta > 0 ? $query->increment('booked_rooms', $delta) : $query->where('booked_rooms', '>', 0)->decrement('booked_rooms', -$delta);
    }

    /**
     * Build a booking code "HB" + yymmdd + 4 random characters, e.g. HB261018A7K2.
     *
     * @return string
     */
    private function newBookingCode(): string
    {
        do {
            $bookingCode = 'HB'.now()->format('ymd').strtoupper(Str::random(4));
        } while (Booking::query()->withTrashed()->where('booking_code', $bookingCode)->exists());

        return $bookingCode;
    }
}
