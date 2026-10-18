{{--
    AD-19 Personal settings: profile picture, profile fields and display language of the signed-in administrator.
    Data: App\Models\AdminUser $administrator (role and hotel loaded).
    Behaviour: public/assets/admin/js/profile.js (picture preview, drag & drop, unsaved-change guard).
--}}
@extends('layouts.admin')

@section('title', __('admin.profile.title'))

@section('breadcrumb')
    {{ __('admin.profile.account') }} / <b>{{ __('admin.profile.title') }}</b>
@endsection

@section('content')
    {{-- ① Profile header --}}
    <section class="profile-hero" aria-labelledby="profile-name">
        <div class="profile-hero__cover"></div>

        <div class="profile-hero__body">
            <form method="POST" action="{{ route('admin.profile.avatar.update') }}" enctype="multipart/form-data"
                  class="profile-avatar" data-avatar-form>
                @csrf
                <div class="profile-avatar__frame" data-avatar-dropzone>
                    <x-admin.avatar :administrator="$administrator" :size="112" />
                    <label for="avatar" class="profile-avatar__camera" title="{{ __('admin.profile.avatar_change') }}">
                        <x-admin.icon name="camera" size="s" />
                        <span class="sr-only">{{ __('admin.profile.avatar_change') }}</span>
                    </label>
                </div>
                <input id="avatar" name="avatar" type="file" accept="image/jpeg,image/png,image/webp" class="sr-only" data-avatar-input>

                {{-- Review & crop dialog (opened by profile.js after a file is chosen or dropped) --}}
                <dialog class="avatar-editor" data-avatar-dialog aria-labelledby="avatar-editor-title">
                    <header class="avatar-editor__header">
                        <h2 id="avatar-editor-title" class="t-m">{{ __('admin.profile.avatar_review_title') }}</h2>
                        <button type="button" class="ib" data-avatar-cancel aria-label="{{ __('admin.profile.cancel') }}"><x-admin.icon name="close" /></button>
                    </header>

                    <div class="avatar-editor__body">
                        <div class="avatar-editor__viewport" data-avatar-viewport>
                            <canvas width="560" height="560" data-avatar-canvas aria-label="{{ __('admin.profile.avatar_drag_hint') }}"></canvas>
                        </div>
                        <p class="t-xs sub center">{{ __('admin.profile.avatar_drag_hint') }}</p>

                        <label class="avatar-editor__zoom">
                            <x-admin.icon name="minus" size="s" />
                            <input type="range" min="1" max="3" step="0.01" value="1" data-avatar-zoom aria-label="{{ __('admin.profile.avatar_zoom') }}">
                            <x-admin.icon name="plus" size="s" />
                        </label>

                        <div class="avatar-editor__previews" aria-label="{{ __('admin.profile.avatar_previews') }}">
                            <figure><canvas width="224" height="224" data-avatar-thumb style="width:112px;height:112px"></canvas><figcaption>112 px</figcaption></figure>
                            <figure><canvas width="96" height="96" data-avatar-thumb style="width:48px;height:48px"></canvas><figcaption>48 px</figcaption></figure>
                            <figure><canvas width="68" height="68" data-avatar-thumb style="width:34px;height:34px"></canvas><figcaption>34 px</figcaption></figure>
                        </div>
                    </div>

                    <footer class="avatar-editor__footer">
                        <button type="button" class="btn sm sec" data-avatar-reset>{{ __('admin.profile.avatar_reset') }}</button>
                        <span class="grow"></span>
                        <button type="button" class="btn sm sec" data-avatar-cancel>{{ __('admin.profile.cancel') }}</button>
                        <button type="button" class="btn sm" data-avatar-save><x-admin.icon name="check" size="s" />{{ __('admin.profile.avatar_save') }}</button>
                    </footer>
                </dialog>
            </form>

            <div class="profile-hero__identity">
                <h1 id="profile-name" class="t-xl">{{ $administrator->full_name }}</h1>
                <p class="t-b sec">{{ $administrator->email }}</p>
                <div class="row g4 wrap">
                    <span class="chip brand"><x-admin.icon name="shield" size="s" />{{ $administrator->role->localizedName() }}</span>
                    <span class="chip"><x-admin.icon name="building" size="s" />{{ $administrator->hotel?->localizedName() ?? __('admin.profile.all_hotels') }}</span>
                    <span @class(['chip', 'brand' => $administrator->hasTwoFactorEnabled(), 'amber' => ! $administrator->hasTwoFactorEnabled()])>
                        <x-admin.icon name="lock" size="s" />{{ __('admin.profile.two_factor') }}: {{ $administrator->hasTwoFactorEnabled() ? __('admin.profile.enabled') : __('admin.profile.disabled') }}
                    </span>
                </div>
            </div>

            <div class="profile-hero__actions">
                <label for="avatar" class="btn sm sec"><x-admin.icon name="upload" size="s" />{{ __('admin.profile.avatar_change') }}</label>
                @if ($administrator->avatar_path)
                    <form method="POST" action="{{ route('admin.profile.avatar.destroy') }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn sm danger"><x-admin.icon name="trash" size="s" />{{ __('admin.profile.avatar_remove') }}</button>
                    </form>
                @endif
            </div>
        </div>

        <p @class(['profile-hero__hint', 'help', 'err' => $errors->has('avatar')]) data-avatar-hint data-invalid-text="{{ __('admin.profile.avatar_invalid') }}">
            @error('avatar')
                <x-admin.icon name="warn" size="s" />{{ $message }}
            @else
                {{ __('admin.profile.avatar_help') }}
            @enderror
        </p>
    </section>

    <div class="profile-layout">
        {{-- ② Personal information + display language --}}
        <form method="POST" action="{{ route('admin.profile.update') }}" class="panel profile-form" novalidate data-dirty-guard>
            @csrf
            @method('PUT')

            <div class="settings-row">
                <div class="settings-row__intro">
                    <h2 class="t-m">{{ __('admin.profile.information') }}</h2>
                    <p class="t-s sub">{{ __('admin.profile.information_help') }}</p>
                </div>
                <div class="settings-row__fields">
                    <x-admin.field name="full_name" :label="__('admin.profile.full_name')" :value="old('full_name', $administrator->full_name)"
                                   maxlength="100" autocomplete="name" required />
                    <x-admin.field name="phone" type="tel" :label="__('admin.profile.phone')" :value="old('phone', $administrator->phone)"
                                   :help="__('admin.profile.phone_help')" autocomplete="tel" />
                    <x-admin.field name="email_readonly" type="email" :label="__('admin.auth.email')" :value="$administrator->email"
                                   :help="__('admin.profile.email_help')" readonly disabled />
                </div>
            </div>

            <div class="divider"></div>

            <div class="settings-row">
                <div class="settings-row__intro">
                    <h2 class="t-m">{{ __('admin.profile.language') }}</h2>
                    <p class="t-s sub">{{ __('admin.profile.language_help') }}</p>
                </div>
                <div class="settings-row__fields">
                    <div class="choice-cards" role="radiogroup" aria-label="{{ __('admin.profile.language') }}">
                        @foreach (config('booking.locales') as $locale)
                            <label class="choice-card">
                                <input type="radio" name="language" value="{{ $locale }}" class="sr-only"
                                       @checked(old('language', $administrator->language) === $locale)>
                                <span class="choice-card__code">{{ strtoupper($locale) }}</span>
                                <span class="choice-card__text">
                                    <b>{{ __("admin.languages.$locale") }}</b>
                                    <span class="t-xs sub">{{ __("admin.languages_en.$locale") }}</span>
                                </span>
                                <span class="choice-card__check"><x-admin.icon name="check" size="s" /></span>
                            </label>
                        @endforeach
                    </div>
                    @error('language')
                        <div class="help err"><x-admin.icon name="warn" size="s" />{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="profile-form__footer">
                <span class="t-s sub" data-dirty-label hidden>{{ __('admin.profile.unsaved') }}</span>
                <a href="{{ route('admin.profile.edit') }}" class="btn sm sec">{{ __('admin.profile.cancel') }}</a>
                <button type="submit" class="btn sm">{{ __('admin.profile.save') }}</button>
            </div>
        </form>

        {{-- ③ Account & security summary (read only) --}}
        <aside class="col g16">
            <section class="panel">
                <h3><x-admin.icon name="user" />{{ __('admin.profile.account') }}</h3>
                <dl class="profile-facts">
                    <div><dt>{{ __('admin.profile.role') }}</dt><dd>{{ $administrator->role->localizedName() }}</dd></div>
                    <div><dt>{{ __('admin.profile.hotel') }}</dt><dd>{{ $administrator->hotel?->localizedName() ?? __('admin.profile.all_hotels') }}</dd></div>
                    <div><dt>{{ __('admin.profile.last_login') }}</dt>
                        <dd>{{ $administrator->last_login_at?->locale(app()->getLocale())->isoFormat(__('admin.formats.datetime')) ?? '—' }}</dd></div>
                    <div><dt>{{ __('admin.profile.member_since') }}</dt>
                        <dd>{{ $administrator->created_at?->locale(app()->getLocale())->isoFormat(__('admin.formats.date')) ?? '—' }}</dd></div>
                </dl>
                <p class="help">{{ __('admin.profile.account_help') }}</p>
            </section>

            <section class="panel">
                <h3><x-admin.icon name="lock" />{{ __('admin.profile.security') }}</h3>
                <div class="list-row">
                    <span class="ic"><x-admin.icon name="shield" size="s" /></span>
                    <span class="grow">
                        {{ __('admin.profile.two_factor') }}
                        <div class="t-xs sub">{{ __('admin.profile.two_factor_help') }}</div>
                    </span>
                    <span @class(['chip', 'brand' => $administrator->hasTwoFactorEnabled(), 'amber' => ! $administrator->hasTwoFactorEnabled()])>
                        {{ $administrator->hasTwoFactorEnabled() ? __('admin.profile.enabled') : __('admin.profile.disabled') }}
                    </span>
                </div>
                <div class="list-row">
                    <span class="ic"><x-admin.icon name="lock" size="s" /></span>
                    <span class="grow">
                        {{ __('admin.auth.password') }}
                        <div class="t-xs sub">{{ __('admin.profile.password_help') }}</div>
                    </span>
                    <span class="chip">{{ __('admin.menu.coming_soon') }}</span>
                </div>
            </section>
        </aside>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/admin/js/profile.js') }}" defer></script>
@endpush
