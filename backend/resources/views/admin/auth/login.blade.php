{{--
    AD-01 Administrator sign-in.
    Step 1: email + password. Step 2: 6-digit TOTP code (checked only when two-factor is enabled).
--}}
@extends('layouts.admin-base')

@section('title', __('admin.auth.title'))

@section('body')
<div class="login">
    <aside class="login__visual">
        <img class="login__photo" src="{{ asset('images/placeholders/pool.svg') }}" alt="">
        <div class="login__brand">
            <div class="t-l">{{ __('admin.app_name') }}</div>
            <div class="t-s">{{ __('admin.auth.tagline') }}</div>
        </div>
    </aside>

    <section class="login__panel">
        <div class="login__content col g16">
            <form method="POST" action="{{ route('admin.login.store') }}" class="col g16" novalidate>
                @csrf
                <h1 class="t-xl">{{ __('admin.auth.title') }}</h1>

                @if (session('status'))
                    <div class="toast brand" role="status"><x-admin.icon name="check" size="s" />{{ session('status') }}</div>
                @endif

                <x-admin.field name="email" type="email" :label="__('admin.auth.email')" :value="old('email')"
                               autocomplete="username" autofocus required />

                <x-admin.field name="password" type="password" :label="__('admin.auth.password')" revealable
                               autocomplete="current-password" required />

                <x-admin.field name="totp_code" :label="__('admin.auth.totp_code')" :help="__('admin.auth.totp_help')"
                               inputmode="numeric" maxlength="6" autocomplete="one-time-code" class="login__totp" />

                <button type="submit" class="btn full">{{ __('admin.auth.submit') }}</button>
                <p class="t-s center login__forgot">{{ __('admin.auth.forgot_password') }}</p>
            </form>

            <form method="POST" action="{{ route('admin.language') }}" class="ab-lang login__language">
                @csrf
                @foreach (config('booking.locales') as $locale)
                    <button type="submit" name="locale" value="{{ $locale }}" aria-pressed="{{ app()->getLocale() === $locale ? 'true' : 'false' }}">
                        {{ __("admin.languages.$locale") }}
                    </button>
                @endforeach
            </form>
        </div>
    </section>
</div>
@endsection
