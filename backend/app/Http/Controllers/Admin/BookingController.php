<?php

namespace App\Http\Controllers\Admin;

use App\Enums\BookingSource;
use App\Enums\BookingStatus;
use App\Exceptions\BusinessException;
use App\Http\Controllers\Controller;
use App\Models\AdminUser;
use App\Models\Booking;
use App\Models\Payment;
use App\Services\Booking\BookingService;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Web Admin bookings: AD-03 list and AD-04 detail with the front-desk actions.
 *
 * - Every query is limited to the administrator's hotel (head office sees the whole chain).
 * - Permissions: booking.view (screens), booking.checkin (check in / out), booking.cancel, booking.edit (note, payment).
 *
 * @author PH.Tuan
 * @created 2026-10-19
 */
class BookingController extends Controller
{
    /** Rows per page of the AD-03 list. */
    private const PER_PAGE = 20;

    /**
     * @param  BookingService  $bookingService  Booking lifecycle.
     */
    public function __construct(private readonly BookingService $bookingService) {}

    /**
     * AD-03 Booking list with search and filters.
     *
     * - GET /admin/bookings?q&status&source&from&to
     *
     * @param  Request  $request  Request with optional filters.
     *
     * @return View
     */
    public function index(Request $request): View
    {
        $administrator = $this->authorizeFor($request, 'booking.view');
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::enum(BookingStatus::class)],
            'source' => ['nullable', Rule::enum(BookingSource::class)],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
        ]);

        $bookings = Booking::query()
            ->visibleTo($administrator)
            ->with(['rooms.roomType', 'user.memberTier'])
            ->when($filters['q'] ?? null, function (Builder $query, string $keyword): void {
                $like = '%'.mb_strtolower($keyword).'%';
                $query->where(fn (Builder $match) => $match
                    ->whereRaw('lower(booking_code) like ?', [$like])
                    ->orWhereRaw('lower(guest_name) like ?', [$like])
                    ->orWhereRaw('lower(guest_email) like ?', [$like])
                    ->orWhere('guest_phone', 'like', '%'.preg_replace('/\D/', '', $keyword).'%'));
            })
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status))
            ->when($filters['source'] ?? null, fn (Builder $query, string $source) => $query->where('source', $source))
            ->when($filters['from'] ?? null, fn (Builder $query, string $from) => $query->whereDate('check_in_date', '>=', $from))
            ->when($filters['to'] ?? null, fn (Builder $query, string $to) => $query->whereDate('check_in_date', '<=', $to))
            ->latest('created_at')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return view('admin.bookings.index', ['bookings' => $bookings, 'filters' => $filters]);
    }

    /**
     * AD-04 Booking detail.
     *
     * - GET /admin/bookings/{code}
     *
     * @param  Request  $request  Authenticated request.
     * @param  string  $code  Booking code.
     *
     * @return View
     */
    public function show(Request $request, string $code): View
    {
        $administrator = $this->authorizeFor($request, 'booking.view');
        $booking = $this->findBooking($administrator, $code)->load([
            'hotel', 'user.memberTier', 'rooms.roomType', 'rooms.ratePlan', 'rooms.room', 'statusHistories', 'payments',
        ]);

        return view('admin.bookings.show', [
            'booking' => $booking,
            'administrator' => $administrator,
            'assignableRooms' => $booking->rooms->mapWithKeys(fn ($bookingRoom) => [
                $bookingRoom->id => $this->bookingService->assignableRooms($bookingRoom->room_type_id),
            ]),
            'counterMethods' => Payment::COUNTER_METHODS,
        ]);
    }

    /**
     * Check the guest in (POST /admin/bookings/{code}/check-in).
     *
     * @param  Request  $request  Request with rooms[booking_room_id] = room_id.
     * @param  string  $code  Booking code.
     *
     * @return RedirectResponse
     */
    public function checkIn(Request $request, string $code): RedirectResponse
    {
        $administrator = $this->authorizeFor($request, 'booking.checkin');
        $validated = $request->validate(['rooms' => ['array'], 'rooms.*' => ['integer']]);

        return $this->run($request, __('admin.bookings.checked_in'), fn () => $this->bookingService->checkIn(
            $this->findBooking($administrator, $code),
            array_map('intval', $validated['rooms'] ?? []),
            $administrator,
        ));
    }

    /**
     * Check the guest out (POST /admin/bookings/{code}/check-out).
     *
     * @param  Request  $request  Authenticated request.
     * @param  string  $code  Booking code.
     *
     * @return RedirectResponse
     */
    public function checkOut(Request $request, string $code): RedirectResponse
    {
        $administrator = $this->authorizeFor($request, 'booking.checkin');

        return $this->run($request, __('admin.bookings.checked_out'), fn () => $this->bookingService->checkOut(
            $this->findBooking($administrator, $code),
            $administrator,
        ));
    }

    /**
     * Cancel the booking (POST /admin/bookings/{code}/cancel).
     *
     * @param  Request  $request  Request with `reason`.
     * @param  string  $code  Booking code.
     *
     * @return RedirectResponse
     */
    public function cancel(Request $request, string $code): RedirectResponse
    {
        $administrator = $this->authorizeFor($request, 'booking.cancel');
        $validated = $request->validate(['reason' => ['required', 'string', 'max:500']], [], ['reason' => __('admin.bookings.cancel_reason')]);

        return $this->run($request, __('admin.bookings.cancelled'), fn () => $this->bookingService->cancel(
            $this->findBooking($administrator, $code),
            $validated['reason'],
            $administrator,
        ));
    }

    /**
     * Record a counter payment (POST /admin/bookings/{code}/payments).
     *
     * @param  Request  $request  Request with `amount` and `method`.
     * @param  string  $code  Booking code.
     *
     * @return RedirectResponse
     */
    public function recordPayment(Request $request, string $code): RedirectResponse
    {
        $administrator = $this->authorizeFor($request, 'booking.edit');
        $validated = $request->validate([
            'amount' => ['required', 'integer', 'min:1000'],
            'method' => ['required', Rule::in(Payment::COUNTER_METHODS)],
        ], [], ['amount' => __('admin.bookings.amount'), 'method' => __('admin.bookings.method')]);

        return $this->run($request, __('admin.bookings.payment_recorded'), fn () => $this->bookingService->recordCounterPayment(
            $this->findBooking($administrator, $code),
            (int) $validated['amount'],
            $validated['method'],
            $administrator,
        ));
    }

    /**
     * Save the internal note (PUT /admin/bookings/{code}/note).
     *
     * @param  Request  $request  Request with `internal_note`.
     * @param  string  $code  Booking code.
     *
     * @return RedirectResponse
     */
    public function updateNote(Request $request, string $code): RedirectResponse
    {
        $administrator = $this->authorizeFor($request, 'booking.edit');
        $validated = $request->validate(['internal_note' => ['nullable', 'string', 'max:2000']]);

        $this->findBooking($administrator, $code)
            ->forceFill(['internal_note' => $validated['internal_note'] ?? null, 'updated_by' => $administrator->id])
            ->save();

        return back()->with('status', __('admin.bookings.note_saved'));
    }

    /**
     * Run a lifecycle action and turn business errors into a flash error.
     *
     * @param  Request  $request  Current request.
     * @param  string  $successMessage  Notice shown after success.
     * @param  Closure(): mixed  $action  Action to run.
     *
     * @return RedirectResponse
     */
    private function run(Request $request, string $successMessage, Closure $action): RedirectResponse
    {
        try {
            $action();
        } catch (BusinessException $exception) {
            return back()->withInput()->withErrors(['booking' => $exception->getMessage()]);
        }

        return back()->with('status', $successMessage);
    }

    /**
     * Find a booking by code within the administrator's hotels.
     *
     * @param  AdminUser  $administrator  Signed-in administrator.
     * @param  string  $code  Booking code.
     *
     * @return Booking
     */
    private function findBooking(AdminUser $administrator, string $code): Booking
    {
        return Booking::query()->visibleTo($administrator)->where('booking_code', $code)->firstOrFail();
    }

    /**
     * Get the administrator and stop with 403 without the permission.
     *
     * @param  Request  $request  Authenticated request.
     * @param  string  $permission  Permission code.
     *
     * @return AdminUser
     */
    private function authorizeFor(Request $request, string $permission): AdminUser
    {
        /** @var AdminUser $administrator */
        $administrator = $request->user('admin');
        abort_unless($administrator->hasPermission($permission), 403);

        return $administrator;
    }
}
