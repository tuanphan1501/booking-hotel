{{--
    Labeled form input with help text and validation error (design: .fld / .inp / .help).
    Usage: <x-admin.field name="email" type="email" :label="__('...')" :value="old('email')" required />
    - revealable: adds the eye button that shows / hides a password (design: .inp with i-eye).
    Extra attributes (autocomplete, maxlength, class, ...) are passed to the <input>.
--}}
@props(['name', 'label', 'type' => 'text', 'value' => null, 'help' => null, 'revealable' => false])

<div class="fld">
    <label for="{{ $name }}">{{ $label }}</label>
    @if ($revealable)<div class="inp-reveal">@endif
    <input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}" value="{{ $value }}"
           aria-invalid="{{ $errors->has($name) ? 'true' : 'false' }}"
           @if ($help || $errors->has($name)) aria-describedby="{{ $name }}-help" @endif
           {{ $attributes->class(['inp', 'err' => $errors->has($name)]) }}>
    @if ($revealable)
        <button type="button" class="inp-reveal__toggle" aria-controls="{{ $name }}" aria-pressed="false"
                data-password-toggle data-label-show="{{ __('admin.auth.show_password') }}"
                data-label-hide="{{ __('admin.auth.hide_password') }}" aria-label="{{ __('admin.auth.show_password') }}">
            <x-admin.icon name="eye" />
        </button>
    </div>
        @once
            @push('scripts')
                <script src="{{ asset('assets/admin/js/password-reveal.js') }}" defer></script>
            @endpush
        @endonce
    @endif

    @error($name)
        <div id="{{ $name }}-help" class="help err"><x-admin.icon name="warn" size="s" />{{ $message }}</div>
    @else
        @if ($help)
            <div id="{{ $name }}-help" class="help">{{ $help }}</div>
        @endif
    @enderror
</div>
