{{--
    One counter row of the "Needs attention" panel (design: .list-row). Counts above zero are highlighted.
    Usage: <x-admin.task-row icon="money" label="Refunds waiting" :count="3" />
--}}
@props(['icon', 'label', 'count'])

<div class="list-row">
    <span class="ic"><x-admin.icon :name="$icon" size="s" /></span>
    <span class="grow">{{ $label }}</span>
    <span @class(['chip', 'amber' => $count > 0])><b class="num">{{ $count }}</b></span>
</div>
