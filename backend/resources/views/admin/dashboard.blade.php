{{--
    AD-02 Home / Dashboard — the first screen after sign-in.
    Data: App\Data\Dashboard\DashboardSummary $summary (built by App\Services\Dashboard\DashboardService).
--}}
@extends('layouts.admin')

@inject('money', 'App\Support\Formatting\MoneyFormatter')

@section('title', __('admin.menu.items.dashboard'))

@section('breadcrumb')
    <b>{{ __('admin.menu.items.dashboard') }}</b>
@endsection

@section('content')
    <div class="ph-row">
        <h1>{{ __('admin.dashboard.today', ['date' => $summary->reportDate->locale(app()->getLocale())->isoFormat(__('admin.formats.date_long'))]) }}</h1>
        <span class="chip out">{{ __('admin.dashboard.greeting', ['name' => auth('admin')->user()->full_name]) }}</span>
    </div>

    {{-- ① KPIs --}}
    <section class="kpis" aria-label="{{ __('admin.dashboard.kpi_label') }}">
        <x-admin.kpi :label="__('admin.dashboard.occupancy')"
                     :value="$summary->occupancyRate === null ? '—' : number_format($summary->occupancyRate, 1).'%'"
                     :change="$summary->occupancyChangePoints"
                     :change-text="$summary->occupancyChangePoints === null ? null : __('admin.dashboard.points_vs_last_week', ['value' => number_format(abs($summary->occupancyChangePoints), 1)])" />

        <x-admin.kpi :label="__('admin.dashboard.revenue_today')"
                     :value="$money->compact($summary->revenueToday)"
                     :change="$summary->revenueChangePercent"
                     :change-text="$summary->revenueChangePercent === null ? null : __('admin.dashboard.percent_vs_last_week', ['value' => number_format(abs($summary->revenueChangePercent), 1)])" />

        <x-admin.kpi label="ADR" :hint="__('admin.dashboard.adr_hint')" :value="$money->compact($summary->averageDailyRate)"
                     :change-text="'RevPAR '.$money->compact($summary->revenuePerAvailableRoom)"
                     :change-hint="__('admin.dashboard.revpar_hint')" />

        <x-admin.kpi :label="__('admin.dashboard.new_bookings')" :value="(string) $summary->newBookings"
                     :change-text="__('admin.dashboard.cancellations', ['cancelled' => $summary->cancellationsToday, 'no_show' => $summary->noShowsToday])" />
    </section>

    <div class="g2">
        {{-- ② Revenue of the last 7 nights --}}
        <section class="panel">
            <h3><x-admin.icon name="chart" />{{ __('admin.dashboard.revenue_chart') }}</h3>
            <div class="bars" role="img" aria-label="{{ __('admin.dashboard.revenue_chart') }}">
                @foreach ($summary->revenueByDay as $stayDate => $dailyRevenue)
                    @php($isToday = $stayDate === $summary->reportDate->toDateString())
                    <div title="{{ $money->full($dailyRevenue) }}">
                        <span class="num">{{ $money->compact($dailyRevenue) }}</span>
                        <i @class(['t' => $isToday]) style="height: {{ max(2, round($dailyRevenue / $summary->maxDailyRevenue() * 100)) }}%"></i>
                        <span>{{ \Carbon\CarbonImmutable::parse($stayDate)->locale(app()->getLocale())->isoFormat(__('admin.formats.date_short')) }}</span>
                    </div>
                @endforeach
            </div>
            <div class="legend" style="margin-top:10px">
                <span><i style="background:var(--brand)"></i>{{ __('admin.dashboard.revenue') }}</span>
                <span><i style="background:var(--brand-soft)"></i>{{ __('admin.dashboard.today_provisional') }}</span>
            </div>
        </section>

        {{-- ③ Needs attention --}}
        <section class="panel">
            <h3><x-admin.icon name="bell" />{{ __('admin.dashboard.needs_attention') }}</h3>
            <x-admin.task-row icon="money" :label="__('admin.dashboard.tasks.pending_refunds')" :count="$summary->pendingTasks->pendingRefunds" />
            <x-admin.task-row icon="star" :label="__('admin.dashboard.tasks.pending_reviews')" :count="$summary->pendingTasks->pendingReviews" />
            <x-admin.task-row icon="logout" :label="__('admin.dashboard.tasks.overdue_departures')" :count="$summary->pendingTasks->overdueDepartures" />
            <x-admin.task-row icon="warn" :label="__('admin.dashboard.tasks.low_availability')" :count="$summary->pendingTasks->lowAvailabilityDays" />
        </section>
    </div>

    {{-- ④ Arrivals today --}}
    <section class="panel p0">
        <h3 style="padding:16px 16px 0"><x-admin.icon name="users" />{{ __('admin.dashboard.arrivals', ['count' => $summary->arrivalCount]) }}</h3>

        @if ($summary->arrivals->isEmpty())
            <div class="empty">
                <div class="ill"><x-admin.icon name="cal" /></div>
                <p class="t-b sec">{{ __('admin.dashboard.no_arrivals') }}</p>
            </div>
        @else
            <table class="tbl">
                <thead>
                    <tr>
                        <th>{{ __('admin.bookings.code') }}</th>
                        <th>{{ __('admin.bookings.guest') }}</th>
                        <th>{{ __('admin.bookings.room_type') }}</th>
                        <th>{{ __('admin.bookings.arrival_time') }}</th>
                        <th>{{ __('admin.bookings.room') }}</th>
                        <th>{{ __('admin.bookings.payment') }}</th>
                        <th>{{ __('admin.bookings.status') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($summary->arrivals as $booking)
                        @php($firstRoom = $booking->rooms->first())
                        <tr>
                            <td><a class="link" href="{{ route('admin.bookings.show', $booking->booking_code) }}">{{ $booking->booking_code }}</a></td>
                            <td>{{ $booking->guest_name }}</td>
                            <td>{{ $firstRoom?->roomType->localizedName() }}@if ($booking->rooms->count() > 1) × {{ $booking->rooms->count() }}@endif</td>
                            <td class="num">{{ $booking->estimated_arrival ? substr($booking->estimated_arrival, 0, 5) : '—' }}</td>
                            <td class="num">{{ $firstRoom?->room?->room_number ?? '—' }}</td>
                            <td>
                                @if ($booking->outstandingAmount() === 0)
                                    <span class="chip brand">{{ __('admin.bookings.paid_in_full') }}</span>
                                @else
                                    <span class="chip amber">{{ __('admin.bookings.outstanding', ['amount' => $money->full($booking->outstandingAmount())]) }}</span>
                                @endif
                            </td>
                            <td><span class="chip {{ $booking->status->tone() }}">{{ $booking->status->label() }}</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </section>
@endsection
