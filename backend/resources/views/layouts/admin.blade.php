{{--
    Signed-in Web Admin layout: skip link, sidebar (drawer below 1100 px), top bar and page content.
    Behaviour: public/assets/admin/js/admin.js (drawer toggle).
--}}
@extends('layouts.admin-base')

@section('body')
@php($administrator = auth('admin')->user())
<a href="#main-content" class="skip-link">{{ __('admin.skip_to_content') }}</a>

<div class="adm" data-admin-shell>
    <x-admin.sidebar />
    <div class="nav-scrim" data-nav-close hidden></div>

    <div class="amain">
        <header class="abar">
            <button type="button" class="ib abar__menu" aria-controls="admin-sidebar" aria-expanded="false"
                    aria-label="{{ __('admin.menu.open') }}" data-nav-toggle
                    data-label-open="{{ __('admin.menu.open') }}" data-label-close="{{ __('admin.menu.close') }}">
                <x-admin.icon name="list" />
            </button>
            <div class="crumb">@yield('breadcrumb')</div>
            <a href="{{ route('admin.profile.edit') }}" class="abar__profile" title="{{ __('admin.profile.title') }}">
                <x-admin.avatar :administrator="$administrator" />
                <span class="abar__name">{{ $administrator->full_name }}</span>
            </a>
            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button type="submit" class="btn sm sec"><x-admin.icon name="logout" size="s" /><span class="abar__label">{{ __('admin.auth.sign_out') }}</span></button>
            </form>
        </header>

        <main id="main-content" class="apage" tabindex="-1">
            @if (session('status'))
                <div class="toast brand" role="status"><x-admin.icon name="check" size="s" />{{ session('status') }}</div>
            @endif

            @yield('content')
        </main>
    </div>
</div>

<script src="{{ asset('assets/admin/js/admin.js') }}" defer></script>
@endsection
