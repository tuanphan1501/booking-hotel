<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Booking;
use App\Models\BookingRoom;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Booking as returned to the Mobile App (MB-08 payment, MB-09 success, MB-10 trips, MB-11 detail).
 *
 * @mixin Booking
 *
 * @author PH.Tuan
 * @created 2026-10-19
 */
class BookingResource extends JsonResource
{
    /**
     * Transform the booking into an array.
     *
     * @param  Request  $request  Current request.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'booking_code' => $this->booking_code,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'hotel' => $this->whenLoaded('hotel', fn () => [
                'id' => $this->hotel->id,
                'name' => $this->hotel->localizedName(),
                'city' => $this->hotel->city,
                'cover_url' => HotelResource::coverUrl($this->hotel->id),
            ]),
            'check_in' => $this->check_in_date->toDateString(),
            'check_out' => $this->check_out_date->toDateString(),
            'nights' => $this->nights,
            'adults' => $this->adults,
            'children' => $this->children,
            'guest_name' => $this->guest_name,
            'guest_phone' => $this->guest_phone,
            'guest_email' => $this->guest_email,
            'estimated_arrival' => $this->estimated_arrival === null ? null : substr($this->estimated_arrival, 0, 5),
            'special_request' => $this->special_request,
            'rooms' => $this->whenLoaded('rooms', fn () => $this->rooms->map(fn (BookingRoom $bookingRoom) => [
                'room_type' => $bookingRoom->roomType->localizedName(),
                'rate_plan' => $bookingRoom->ratePlan?->localizedName(),
                'breakfast_included' => (bool) $bookingRoom->ratePlan?->breakfast_included,
                'room_number' => $bookingRoom->room?->room_number,
                'subtotal' => $bookingRoom->subtotal,
            ])->values()),
            'amounts' => [
                'room_amount' => (int) $this->room_amount,
                'service_charge' => (int) $this->service_charge,
                'tax_amount' => (int) $this->tax_amount,
                'total_amount' => $this->total_amount,
                'paid_amount' => $this->paid_amount,
                'refunded_amount' => $this->refunded_amount,
                'outstanding_amount' => $this->outstandingAmount(),
                'cancel_fee' => (int) $this->cancel_fee,
            ],
            'cancel_policy' => $this->cancel_policy_snapshot,
            'can_cancel' => in_array($this->status->value, ['PENDING_PAYMENT', 'CONFIRMED'], true),
            'hold_expires_at' => $this->hold_expires_at?->toIso8601String(),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
