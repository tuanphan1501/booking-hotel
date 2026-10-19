{{--
    AD-04 Booking detail: stay & rooms (room assignment at check-in), history, customer + internal note,
    payment summary + counter payment, and the actions Cancel / Check in / Check out.
    Data: Booking $booking, AdminUser $administrator, Collection $assignableRooms (keyed by booking_rooms.id), array $counterMethods.
--}}
@extends('layouts.admin')

@inject('money', 'App\Support\Formatting\MoneyFormatter')

@section('title', $booking->booking_code)

@section('breadcrumb')
    {{ __('admin.menu.groups.operations') }} / <a href="{{ route('admin.bookings.index') }}">{{ __('admin.bookings.list_title') }}</a> / <b>{{ $booking->booking_code }}</b>
@endsection

@php
    use App\Enums\BookingStatus;
    $status = $booking->status;
    $canCheckIn = $status === BookingStatus::Confirmed && ! $booking->check_in_date->isFuture() && $administrator->hasPermission('booking.checkin');
    $canCheckOut = $status === BookingStatus::CheckedIn && $administrator->hasPermission('booking.checkin');
    $canCancel = in_array($status, [BookingStatus::PendingPayment, BookingStatus::Confirmed], true) && $administrator->hasPermission('booking.cancel');
    $canPay = $booking->outstandingAmount() > 0 && in_array($status, [BookingStatus::PendingPayment, BookingStatus::Confirmed, BookingStatus::CheckedIn], true) && $administrator->hasPermission('booking.edit');
@endphp

@section('content')
    <div class="ph-row">
        <a href="{{ route('admin.bookings.index') }}" class="ib" style="width:38px;height:38px" aria-label="{{ __('admin.bookings.back') }}"><x-admin.icon name="back" /></a>
        <h1 style="flex:none">{{ $booking->booking_code }}</h1>
        <span class="chip {{ $status->tone() }}">{{ $status->label() }}</span>
        <span class="grow"></span>
        @if ($canCancel)
            <button type="button" class="btn sm danger" data-dialog-open="cancel-dialog">{{ __('admin.bookings.cancel') }}</button>
        @endif
        @if ($canCheckIn)
            <button type="submit" form="check-in-form" class="btn sm"><x-admin.icon name="door" size="s" />{{ __('admin.bookings.do_check_in') }}</button>
        @endif
        @if ($canCheckOut)
            <form method="POST" action="{{ route('admin.bookings.check-out', $booking->booking_code) }}">
                @csrf
                <button type="submit" class="btn sm"><x-admin.icon name="logout" size="s" />{{ __('admin.bookings.do_check_out') }}</button>
            </form>
        @endif
    </div>

    @error('booking')
        <div class="toast rose" role="alert"><x-admin.icon name="warn" size="s" />{{ $message }}</div>
    @enderror

    <div class="g2">
        <div class="col g16">
            {{-- Stay & rooms --}}
            <section class="panel">
                <h3><x-admin.icon name="bed" />{{ __('admin.bookings.stay') }}</h3>
                <div class="g3" style="gap:12px">
                    <div><div class="t-xs sub">{{ __('admin.bookings.check_in') }}</div>
                        <div class="w6">{{ $booking->check_in_date->format('d/m/Y') }} {{ substr((string) $booking->hotel->check_in_time, 0, 5) }}</div></div>
                    <div><div class="t-xs sub">{{ __('admin.bookings.check_out') }}</div>
                        <div class="w6">{{ $booking->check_out_date->format('d/m/Y') }} {{ substr((string) $booking->hotel->check_out_time, 0, 5) }}</div></div>
                    <div><div class="t-xs sub">{{ __('admin.bookings.guests_nights') }}</div>
                        <div class="w6">{{ $booking->adults + $booking->children }} · {{ $booking->nights }}</div></div>
                </div>

                <form id="check-in-form" method="POST" action="{{ route('admin.bookings.check-in', $booking->booking_code) }}">
                    @csrf
                    <table class="tbl" style="margin-top:14px">
                        <thead><tr>
                            <th>{{ __('admin.bookings.room_type') }}</th>
                            <th>{{ __('admin.bookings.rate_plan') }}</th>
                            <th>{{ __('admin.bookings.assigned_room') }}</th>
                            <th class="r">{{ __('admin.bookings.subtotal') }}</th>
                        </tr></thead>
                        <tbody>
                            @foreach ($booking->rooms as $bookingRoom)
                                <tr>
                                    <td>{{ $bookingRoom->roomType->localizedName() }}</td>
                                    <td>{{ $bookingRoom->ratePlan?->localizedName() }}</td>
                                    <td>
                                        @if ($canCheckIn && $bookingRoom->room_id === null)
                                            @php($rooms = $assignableRooms[$bookingRoom->id])
                                            @if ($rooms->isEmpty())
                                                <span class="chip amber">{{ __('admin.bookings.no_rooms') }}</span>
                                            @else
                                                <select name="rooms[{{ $bookingRoom->id }}]" class="inp s" aria-label="{{ __('admin.bookings.choose_room') }}" required>
                                                    @foreach ($rooms as $room)
                                                        <option value="{{ $room->id }}">{{ $room->room_number }}</option>
                                                    @endforeach
                                                </select>
                                            @endif
                                        @else
                                            <span class="num w6">{{ $bookingRoom->room?->room_number ?? '—' }}</span>
                                        @endif
                                    </td>
                                    <td class="r w6 num">{{ $money->full($bookingRoom->subtotal) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </form>
            </section>

            {{-- History --}}
            <section class="panel">
                <h3><x-admin.icon name="clock" />{{ __('admin.bookings.history') }}</h3>
                <div class="tl">
                    @foreach ($booking->statusHistories as $history)
                        <div @class(['g' => ! $loop->first])>
                            <span><b>{{ $history->to_status->label() }}</b> · {{ __('admin.bookings.actor_'.$history->actor_type) }}@if ($history->note) — {{ $history->note }}@endif<br>
                                <span class="sub t-xs">{{ $history->created_at->format('d/m/Y H:i') }}</span></span>
                        </div>
                    @endforeach
                    @if ($booking->statusHistories->isEmpty())
                        <div><span><b>{{ $status->label() }}</b><br><span class="sub t-xs">{{ $booking->created_at->format('d/m/Y H:i') }}</span></span></div>
                    @endif
                </div>
            </section>
        </div>

        <div class="col g16">
            {{-- Customer --}}
            <section class="panel">
                <h3><x-admin.icon name="user" />{{ __('admin.bookings.customer') }}</h3>
                <div class="row g12">
                    <span class="avatar">{{ mb_strtoupper(mb_substr($booking->guest_name, 0, 1)) }}</span>
                    <div>
                        <div class="w6">{{ $booking->guest_name }}
                            @if ($booking->user?->memberTier)<span class="chip gold">{{ $booking->user->memberTier->code->label() }}</span>@endif</div>
                        <div class="t-s sub">{{ $booking->guest_phone }} · {{ $booking->guest_email }}</div>
                    </div>
                </div>
                @if ($booking->estimated_arrival || $booking->special_request)
                    <div class="t-s" style="margin-top:10px">
                        @if ($booking->estimated_arrival){{ __('admin.bookings.arrival_time') }} {{ substr($booking->estimated_arrival, 0, 5) }}@endif
                        @if ($booking->special_request) · “{{ $booking->special_request }}”@endif
                    </div>
                @endif
                <form method="POST" action="{{ route('admin.bookings.note.update', $booking->booking_code) }}" class="col g8" style="margin-top:12px">
                    @csrf
                    @method('PUT')
                    <label class="t-xs sub" for="internal_note">{{ __('admin.bookings.internal_note') }}</label>
                    <textarea id="internal_note" name="internal_note" class="inp bk-note" rows="3" maxlength="2000"
                              placeholder="{{ __('admin.bookings.internal_note_placeholder') }}">{{ old('internal_note', $booking->internal_note) }}</textarea>
                    @if ($administrator->hasPermission('booking.edit'))
                        <button type="submit" class="btn xs sec" style="align-self:flex-end">{{ __('admin.bookings.save_note') }}</button>
                    @endif
                </form>
            </section>

            {{-- Payment --}}
            <section class="panel">
                <h3><x-admin.icon name="money" />{{ __('admin.bookings.payment_summary') }}</h3>
                <div class="col g4">
                    <div class="kv"><span class="k">{{ __('admin.bookings.room_amount') }}</span><span class="v num">{{ $money->full((int) $booking->room_amount) }}</span></div>
                    <div class="kv"><span class="k">{{ __('admin.bookings.service_tax') }}</span><span class="v num">{{ $money->full((int) $booking->service_charge + (int) $booking->tax_amount) }}</span></div>
                    <div class="divider" style="margin:6px 0"></div>
                    <div class="kv total"><span>{{ __('admin.bookings.total') }}</span><span class="v num">{{ $money->full($booking->total_amount) }}</span></div>
                    @foreach ($booking->payments->where('status', 'SUCCESS') as $payment)
                        <div class="kv"><span class="k">{{ __('admin.bookings.paid') }} · {{ __('admin.bookings.method_'.$payment->method) }}</span><span class="v num">{{ $money->full($payment->amount) }}</span></div>
                    @endforeach
                    @if ($booking->refunded_amount > 0)
                        <div class="kv"><span class="k">{{ __('admin.bookings.refunded') }}</span><span class="v num">{{ $money->full($booking->refunded_amount) }}</span></div>
                    @endif
                    @if ((int) $booking->cancel_fee > 0)
                        <div class="kv"><span class="k">{{ __('admin.bookings.cancel_fee') }}</span><span class="v num">{{ $money->full((int) $booking->cancel_fee) }}</span></div>
                    @endif
                    <div class="kv w7"><span>{{ __('admin.bookings.due') }}</span><span class="v num">{{ $money->full($booking->outstandingAmount()) }}</span></div>
                </div>

                @if ($canPay)
                    <form method="POST" action="{{ route('admin.bookings.payments.store', $booking->booking_code) }}" class="bk-pay">
                        @csrf
                        <input type="number" name="amount" class="inp s" min="1000" step="1000" value="{{ old('amount', $booking->outstandingAmount()) }}"
                               aria-label="{{ __('admin.bookings.amount') }}" required>
                        <select name="method" class="inp s" aria-label="{{ __('admin.bookings.method') }}">
                            @foreach ($counterMethods as $method)
                                <option value="{{ $method }}">{{ __('admin.bookings.method_'.$method) }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn sm sec full"><x-admin.icon name="money" size="s" />{{ __('admin.bookings.record_payment') }}</button>
                        @error('amount')<div class="help err">{{ $message }}</div>@enderror
                    </form>
                @endif
            </section>
        </div>
    </div>

    @if ($canCancel)
        <dialog id="cancel-dialog" class="bk-dialog">
            <form method="POST" action="{{ route('admin.bookings.cancel', $booking->booking_code) }}" class="col g12">
                @csrf
                <h3 class="t-l">{{ __('admin.bookings.cancel') }} {{ $booking->booking_code }}</h3>
                <label class="t-s w6" for="reason">{{ __('admin.bookings.cancel_reason') }}</label>
                <textarea id="reason" name="reason" class="inp bk-note" rows="3" maxlength="500" required>{{ old('reason') }}</textarea>
                @error('reason')<div class="help err">{{ $message }}</div>@enderror
                <div class="row g8" style="justify-content:flex-end">
                    <button type="button" class="btn sm sec" data-dialog-close>{{ __('admin.profile.cancel') }}</button>
                    <button type="submit" class="btn sm danger">{{ __('admin.bookings.cancel_confirm') }}</button>
                </div>
            </form>
        </dialog>
        @push('scripts')
            <script>
            // Opens / closes the cancellation dialog; reopens it when the reason failed validation.
            (function () {
                const dialog = document.getElementById('cancel-dialog');
                document.querySelector('[data-dialog-open="cancel-dialog"]')?.addEventListener('click', () => dialog.showModal());
                dialog.querySelector('[data-dialog-close]').addEventListener('click', () => dialog.close());
                @if ($errors->has('reason')) dialog.showModal(); @endif
            })();
            </script>
        @endpush
    @endif
@endsection
