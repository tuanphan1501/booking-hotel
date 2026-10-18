{{--
    SVG icon from the shared sprite (public/admin/icons.svg, 1.75px stroke, currentColor).
    Usage: <x-admin.icon name="door" size="s" />   sizes: s = 16px, default = 20px, l = 24px
--}}
@props(['name', 'size' => null])

<svg {{ $attributes->class(['i', $size]) }} aria-hidden="true"><use href="{{ asset('assets/admin/icons.svg') }}#i-{{ $name }}" /></svg>
