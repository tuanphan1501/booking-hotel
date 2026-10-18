{{-- Sidebar markup. Data: App\View\Components\Admin\Sidebar::$menuGroups --}}
<nav id="admin-sidebar" class="side" aria-label="{{ __('admin.menu.label') }}">
    <div class="logo">
        <img src="{{ asset('assets/brand/vivustay-mark.svg') }}" alt="" width="30" height="30">{{ __('admin.app_name') }}
    </div>

    @foreach ($menuGroups as $menuGroup)
        @if ($menuGroup->title())
            <div class="grp">{{ $menuGroup->title() }}</div>
        @endif

        @foreach ($menuGroup->items as $menuItem)
            @if ($menuItem->isAvailable())
                <a href="{{ $menuItem->url() }}" @class(['on' => $menuItem->isActive()])
                   @if ($menuItem->isActive()) aria-current="page" @endif>
                    <x-admin.icon :name="$menuItem->iconName" />{{ $menuItem->label() }}
                </a>
            @else
                <a aria-disabled="true" title="{{ __('admin.menu.coming_soon') }}" class="is-disabled">
                    <x-admin.icon :name="$menuItem->iconName" />{{ $menuItem->label() }}
                </a>
            @endif
        @endforeach
    @endforeach
</nav>
