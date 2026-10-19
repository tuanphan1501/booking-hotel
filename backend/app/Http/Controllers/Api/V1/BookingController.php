<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Booking\CreateBookingRequest;
use App\Http\Resources\Api\V1\BookingResource;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\User;
use App\Services\Booking\BookingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

/**
 * Bookings of the signed-in customer: create (MB-07), pay (MB-08), list (MB-10), detail (MB-11), cancel (MB-11c).
 *
 * @author PH.Tuan
 * @created 2026-10-19
 */
class BookingController extends Controller
{
    /** Relations returned with every booking. */
    private const RELATIONS = ['hotel', 'rooms.roomType', 'rooms.ratePlan', 'rooms.room'];

    /** Statuses of trips that are still ahead or in progress. */
    private const UPCOMING_STATUSES = ['PENDING_PAYMENT', 'CONFIRMED', 'CHECKED_IN'];

    /**
     * @param  BookingService  $bookingService  Booking lifecycle.
     */
    public function __construct(private readonly BookingService $bookingService) {}

    /**
     * List the customer's bookings, newest stay first.
     *
     * - GET /api/v1/bookings?scope=upcoming|past
     *
     * @param  Request  $request  Authenticated request.
     *
     * @return AnonymousResourceCollection
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $scope = $request->query('scope');

        $bookings = Booking::query()
            ->where('user_id', $this->customer($request)->id)
            ->with(self::RELATIONS)
            ->when($scope === 'upcoming', fn ($query) => $query->whereIn('status', self::UPCOMING_STATUSES))
            ->when($scope === 'past', fn ($query) => $query->whereNotIn('status', self::UPCOMING_STATUSES))
            ->orderByDesc('check_in_date')
            ->limit(100)
            ->get();

        return BookingResource::collection($bookings);
    }

    /**
     * Create a booking and hold the room for payment.
     *
     * - POST /api/v1/bookings → 201 with status PENDING_PAYMENT and hold_expires_at.
     *
     * @param  CreateBookingRequest  $request  Validated booking form.
     *
     * @return JsonResponse
     */
    public function store(CreateBookingRequest $request): JsonResponse
    {
        $booking = $this->bookingService->createForCustomer($this->customer($request), $request->validated());

        return (new BookingResource($booking->load(self::RELATIONS)))->response()->setStatusCode(201);
    }

    /**
     * Get one booking.
     *
     * - GET /api/v1/bookings/{code}
     *
     * @param  Request  $request  Authenticated request.
     * @param  string  $code  Booking code.
     *
     * @return BookingResource
     */
    public function show(Request $request, string $code): BookingResource
    {
        return new BookingResource($this->findBooking($request, $code)->load(self::RELATIONS));
    }

    /**
     * Pay the booking (sandbox gateway until VNPay / MoMo / Stripe are connected).
     *
     * - POST /api/v1/bookings/{code}/payments { method }
     *
     * @param  Request  $request  Request with `method`.
     * @param  string  $code  Booking code.
     *
     * @return BookingResource CONFIRMED booking.
     */
    public function pay(Request $request, string $code): BookingResource
    {
        $validated = $request->validate(['method' => ['required', Rule::in(Payment::APP_METHODS)]]);

        $booking = $this->bookingService->payInApp($this->findBooking($request, $code), $validated['method']);

        return new BookingResource($booking->load(self::RELATIONS));
    }

    /**
     * Cancel the booking following its cancellation policy.
     *
     * - POST /api/v1/bookings/{code}/cancel { reason? }
     *
     * @param  Request  $request  Request with an optional `reason`.
     * @param  string  $code  Booking code.
     *
     * @return BookingResource CANCELLED booking with cancel_fee.
     */
    public function cancel(Request $request, string $code): BookingResource
    {
        $validated = $request->validate(['reason' => ['nullable', 'string', 'max:500']]);

        $booking = $this->bookingService->cancel(
            $this->findBooking($request, $code),
            $validated['reason'] ?? 'Cancelled in the app',
            $this->customer($request),
        );

        return new BookingResource($booking->load(self::RELATIONS));
    }

    /**
     * Find one of the customer's bookings by code (404 for other customers' bookings).
     *
     * @param  Request  $request  Authenticated request.
     * @param  string  $code  Booking code.
     *
     * @return Booking
     */
    private function findBooking(Request $request, string $code): Booking
    {
        return Booking::query()->where('user_id', $this->customer($request)->id)->where('booking_code', $code)->firstOrFail();
    }

    /**
     * Get the signed-in customer.
     *
     * @param  Request  $request  Authenticated request.
     *
     * @return User
     */
    private function customer(Request $request): User
    {
        /** @var User $customer */
        $customer = $request->user();

        return $customer;
    }
}
