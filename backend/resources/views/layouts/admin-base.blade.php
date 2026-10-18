{{-- Base HTML document of every Web Admin page: fonts, design-system CSS and page title. --}}
<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <title>@yield('title') — {{ __('admin.app_name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700&family=Noto+Sans+JP:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/tokens.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/utilities.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/components.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/admin.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/app.css') }}">
</head>
<body>
    @yield('body')
    @stack('scripts')
</body>
</html>
