{{--
    Centered card layout of the Web Admin sign-in screens (AD-01 sign-in, AD-01b verification).
    Sections: auth-content (card body). Logo on top, language switcher and footer under the card.
--}}
@extends('layouts.admin-base')

@section('body')
<div class="auth">
    <main class="auth__main">
        <div class="auth__card">
            <img class="auth__logo" src="{{ asset('assets/brand/vivustay-logo.svg') }}" alt="{{ __('admin.app_name') }}" width="186" height="48">
            @if (session('status'))
                <div class="toast brand" role="status"><x-admin.icon name="check" size="s" />{{ session('status') }}</div>
            @endif
            @yield('auth-content')
        </div>

        <form method="POST" action="{{ route('admin.language') }}" class="ab-lang auth__language">
            @csrf
            @foreach (config('booking.locales') as $locale)
                <button type="submit" name="locale" value="{{ $locale }}" aria-pressed="{{ app()->getLocale() === $locale ? 'true' : 'false' }}">
                    {{ __("admin.languages.$locale") }}
                </button>
            @endforeach
        </form>
        <p class="auth__footer">{{ __('admin.auth.footer', ['year' => date('Y')]) }}</p>
    </main>
</div>
@endsection
