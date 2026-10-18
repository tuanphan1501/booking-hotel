{{--
    KPI tile (design: .kpi). A positive change is shown with ▲ in brand red, a negative one with ▼.
    `hint` / `change-hint` explain industry abbreviations (ADR, RevPAR) on hover and to screen readers.
    Usage: <x-admin.kpi label="ADR" :hint="__('admin.dashboard.adr_hint')" value="1,6 tr ₫" :change="12.5" change-text="12,5% vs last week" />
--}}
@props(['label', 'value', 'hint' => null, 'change' => null, 'changeText' => null, 'changeHint' => null])

<div class="kpi">
    <span class="t-s sec">
        @if ($hint)<abbr title="{{ $hint }}">{{ $label }}</abbr>@else{{ $label }}@endif
    </span>
    <span class="n">{{ $value }}</span>
    @if ($changeText)
        <span @class(['d', 'brand' => $change !== null && $change >= 0, 'rose' => $change !== null && $change < 0, 'sub' => $change === null])
              @if ($changeHint) title="{{ $changeHint }}" @endif>
            @if ($change !== null){{ $change >= 0 ? '▲' : '▼' }} @endif{{ $changeText }}
        </span>
    @endif
</div>
