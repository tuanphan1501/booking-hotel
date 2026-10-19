{{--
    AD-01b Sign-in verification — step 2: 6-digit code sent by email, or from the authenticator app.
    Data: App\Data\Auth\LoginChallenge $challenge.
--}}
@extends('layouts.admin-auth')

@section('title', __('admin.verify.title'))

@section('auth-content')
<div class="auth__head">
    <span class="auth__badge"><x-admin.icon :name="$challenge->usesEmail() ? 'bell' : 'shield'" /></span>
    <h1 class="t-xl">{{ __('admin.verify.title') }}</h1>
    <p class="t-s sub">
        {{ __($challenge->usesEmail() ? 'admin.verify.email_intro' : 'admin.verify.totp_intro', ['email' => $challenge->maskedEmail]) }}
    </p>
</div>

<form method="POST" action="{{ route('admin.login.verify.store') }}" class="col g16" novalidate>
    @csrf
    <x-admin.field name="code" type="password" revealable :label="__('admin.verify.code')" inputmode="numeric" maxlength="6" pattern="\d{6}"
                   autocomplete="one-time-code" placeholder="••••••" class="auth__code" autofocus required />
    <button type="submit" class="btn full">{{ __('admin.verify.submit') }}</button>
</form>

<div class="auth__links">
    @if ($challenge->usesEmail())
        <form method="POST" action="{{ route('admin.login.resend') }}">
            @csrf
            <button type="submit" class="auth__link" data-resend data-wait="{{ $challenge->resendWaitSeconds() }}"
                    data-label="{{ __('admin.verify.resend') }}" data-label-wait="{{ __('admin.verify.resend_in', ['seconds' => '__s__']) }}">
                {{ __('admin.verify.resend') }}
            </button>
        </form>
    @endif
    <form method="POST" action="{{ route('admin.login.cancel') }}">
        @csrf
        <button type="submit" class="auth__link auth__link--muted">{{ __('admin.verify.other_account') }}</button>
    </form>
</div>

@if ($challenge->usesEmail())
    <p class="auth__note"><x-admin.icon name="info" size="s" />{{ __('admin.verify.not_received') }}</p>
    {{-- Local development: mail is caught by Mailpit instead of reaching the real inbox. --}}
    @if (app()->isLocal() && in_array(config('mail.mailers.smtp.host'), ['mailpit', '127.0.0.1', 'localhost'], true))
        <a class="auth__note auth__dev" href="{{ config('booking.dev_mailbox_url') }}" target="_blank" rel="noopener">
            <x-admin.icon name="bell" size="s" />{{ __('admin.verify.dev_mailbox') }}
        </a>
    @endif
@endif
@endsection

@push('scripts')
<script>
// Resend countdown: disable "Resend code" until the server cooldown is over.
(function () {
    const button = document.querySelector('[data-resend]');
    if (!button) return;
    let seconds = parseInt(button.dataset.wait, 10) || 0;
    const tick = () => {
        button.disabled = seconds > 0;
        button.textContent = seconds > 0 ? button.dataset.labelWait.replace('__s__', seconds) : button.dataset.label;
        if (seconds-- > 0) setTimeout(tick, 1000);
    };
    tick();
})();
</script>
@endpush
