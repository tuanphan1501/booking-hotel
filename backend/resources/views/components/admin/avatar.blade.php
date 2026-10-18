{{--
    Administrator avatar: the uploaded picture, or the initials badge when there is none (design: .avatar).
    Usage: <x-admin.avatar :administrator="$administrator" :size="96" />
--}}
@props(['administrator', 'size' => 34])

@php($avatarUrl = $administrator->avatarUrl())

@if ($avatarUrl)
    <img src="{{ $avatarUrl }}" alt="{{ $administrator->full_name }}" width="{{ $size }}" height="{{ $size }}"
         {{ $attributes->class('avatar avatar--image') }} style="width:{{ $size }}px;height:{{ $size }}px">
@else
    <span {{ $attributes->class('avatar') }} title="{{ $administrator->full_name }}"
          style="width:{{ $size }}px;height:{{ $size }}px;font-size:{{ round($size * 0.38) }}px">{{ $administrator->initials() }}</span>
@endif
