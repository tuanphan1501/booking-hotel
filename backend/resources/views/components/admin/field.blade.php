{{--
    Labeled form input with help text and validation error (design: .fld / .inp / .help).
    Usage: <x-admin.field name="email" type="email" :label="__('...')" :value="old('email')" required />
    Extra attributes (autocomplete, maxlength, class, ...) are passed to the <input>.
--}}
@props(['name', 'label', 'type' => 'text', 'value' => null, 'help' => null])

<div class="fld">
    <label for="{{ $name }}">{{ $label }}</label>
    <input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}" value="{{ $value }}"
           aria-invalid="{{ $errors->has($name) ? 'true' : 'false' }}"
           @if ($help || $errors->has($name)) aria-describedby="{{ $name }}-help" @endif
           {{ $attributes->class(['inp', 'err' => $errors->has($name)]) }}>

    @error($name)
        <div id="{{ $name }}-help" class="help err"><x-admin.icon name="warn" size="s" />{{ $message }}</div>
    @else
        @if ($help)
            <div id="{{ $name }}-help" class="help">{{ $help }}</div>
        @endif
    @enderror
</div>
