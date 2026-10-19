{{--
    AD-01 Administrator sign-in — step 1: email (company or Gmail) + password, or "Sign in with Google".
    Step 2 (verification code by email / authenticator) is admin.auth.verify.
--}}
@extends('layouts.admin-auth')

@section('title', __('admin.auth.title'))

@section('auth-content')
<div class="auth__head">
    <h1 class="t-xl">{{ __('admin.auth.title') }}</h1>
    <p class="t-s sub">{{ __('admin.auth.subtitle') }}</p>
</div>

<form method="POST" action="{{ route('admin.login.store') }}" class="col g16" novalidate>
    @csrf
    <x-admin.field name="email" type="email" :label="__('admin.auth.email')" :value="old('email')"
                   :placeholder="__('admin.auth.email_placeholder')" autocomplete="username" autofocus required />

    <x-admin.field name="password" type="password" :label="__('admin.auth.password')" revealable
                   :placeholder="__('admin.auth.password_placeholder')" autocomplete="current-password" required />

    <button type="submit" class="btn full">{{ __('admin.auth.submit') }}<x-admin.icon name="chev" size="s" /></button>
</form>

@if (\App\Http\Controllers\Admin\Auth\GoogleLoginController::isEnabled())
    <div class="auth__or"><span>{{ __('admin.auth.or') }}</span></div>
    <a href="{{ route('admin.login.google') }}" class="btn sec full auth__google">
        <img src="{{ asset('assets/brand/google.svg') }}" alt="" width="20" height="20">{{ __('admin.auth.google') }}
    </a>
@endif

<p class="auth__note"><x-admin.icon name="shield" size="s" />{{ __('admin.auth.secure_note') }}</p>
<p class="t-s center auth__forgot">{{ __('admin.auth.forgot_password') }}</p>
@endsection
