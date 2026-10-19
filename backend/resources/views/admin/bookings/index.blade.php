{{--
    AD-03 Booking list: search, filters (status, channel, check-in range) and the paginated table.
    Data: LengthAwarePaginator<Booking> $bookings, array $filters.
--}}
@extends('layouts.admin')

@inject('money', 'App\Support\Formatting\MoneyFormatter')

@section('title', __('admin.bookings.list_title'))

@section('breadcrumb')
    {{ __('admin.menu.groups.operations') }} / <b>{{ __('admin.bookings.list_title') }}</b>
@endsection

@section('content')
    <div class="ph-row">
        <h1>{{ __('admin.bookings.list_title') }}</h1>
        <span class="chip out">{{ __('admin.bookings.results', ['count' => number_format($bookings->total())]) }}</span>
    </div>

    <form method="GET" action="{{ route('admin.bookings.index') }}" class="bk-filters">
        <label class="inp s bk-filters__search">
            <x-admin.icon name="search" size="s" />
            <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="{{ __('admin.bookings.search_placeholder') }}"
                   aria-label="{{ __('admin.bookings.search') }}">
        </label>
        <select name="status" class="inp s" aria-label="{{ __('admin.bookings.status') }}">
            <option value="">{{ __('admin.bookings.all_statuses') }}</option>
            @foreach (\App\Enums\BookingStatus::cases() as $status)
                <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>{{ $status->label() }}</option>
            @endforeach
        </select>
        <select name="source" class="inp s" aria-label="{{ __('admin.bookings.source') }}">
            <option value="">{{ __('admin.bookings.all_sources') }}</option>
            @foreach (\App\Enums\BookingSource::cases() as $source)
                <option value="{{ $source->value }}" @selected(($filters['source'] ?? '') === $source->value)>{{ __('admin.bookings.source_'.$source->value) }}</option>
            @endforeach
        </select>
        <label class="bk-filters__date">{{ __('admin.bookings.check_in_from') }}
            <input type="date" name="from" class="inp s" value="{{ $filters['from'] ?? '' }}"></label>
        <label class="bk-filters__date">{{ __('admin.bookings.check_in_to') }}
            <input type="date" name="to" class="inp s" value="{{ $filters['to'] ?? '' }}"></label>
        <button type="submit" class="btn sm">{{ __('admin.bookings.filter') }}</button>
        @if (array_filter($filters))
            <a href="{{ route('admin.bookings.index') }}" class="t-s brand w6">{{ __('admin.bookings.clear') }}</a>
        @endif
    </form>

    <section class="panel p0">
        @if ($bookings->isEmpty())
            <div class="empty">
                <div class="ill"><x-admin.icon name="list" /></div>
                <p class="t-b sec">{{ __('admin.bookings.empty') }}</p>
            </div>
        @else
            <table class="tbl">
                <thead>
                    <tr>
                        <th>{{ __('admin.bookings.code') }}</th>
                        <th>{{ __('admin.bookings.guest') }}</th>
                        <th>{{ __('admin.bookings.check_in') }} – {{ __('admin.bookings.check_out') }}</th>
                        <th>{{ __('admin.bookings.room') }}</th>
                        <th>{{ __('admin.bookings.source') }}</th>
                        <th class="r">{{ __('admin.bookings.total_amount') }}</th>
                        <th class="r">{{ __('admin.bookings.paid_amount') }}</th>
                        <th>{{ __('admin.bookings.status') }}</th>
                        <th>{{ __('admin.bookings.created_at') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($bookings as $booking)
                        @php($firstRoom = $booking->rooms->first())
                        <tr>
                            <td><a class="link" href="{{ route('admin.bookings.show', $booking->booking_code) }}">{{ $booking->booking_code }}</a></td>
                            <td>
                                {{ $booking->guest_name }}
                                @if ($booking->user?->memberTier)
                                    <div class="t-xs"><span class="gold">{{ $booking->user->memberTier->code->label() }}</span></div>
                                @endif
                            </td>
                            <td class="num">{{ $booking->check_in_date->format('d/m') }} – {{ $booking->check_out_date->format('d/m') }}</td>
                            <td>{{ $firstRoom?->roomType->localizedName() }} × {{ $booking->rooms->count() }}</td>
                            <td>{{ __('admin.bookings.source_'.$booking->source->value) }}</td>
                            <td class="r num">{{ $money->full($booking->total_amount) }}</td>
                            <td class="r num">{{ $money->full($booking->paid_amount) }}</td>
                            <td><span class="chip {{ $booking->status->tone() }}">{{ $booking->status->label() }}</span></td>
                            <td class="sub num">{{ $booking->created_at->format('d/m H:i') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </section>

    @if ($bookings->hasPages())
        <nav class="bk-pager" aria-label="Pagination">
            @if ($bookings->onFirstPage())
                <span class="btn sm sec" aria-disabled="true">‹</span>
            @else
                <a class="btn sm sec" href="{{ $bookings->previousPageUrl() }}" rel="prev">‹</a>
            @endif
            <span class="t-s sub">{{ $bookings->currentPage() }} / {{ $bookings->lastPage() }}</span>
            @if ($bookings->hasMorePages())
                <a class="btn sm sec" href="{{ $bookings->nextPageUrl() }}" rel="next">›</a>
            @else
                <span class="btn sm sec" aria-disabled="true">›</span>
            @endif
        </nav>
    @endif
@endsection
