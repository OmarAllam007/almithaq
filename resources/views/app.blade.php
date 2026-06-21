<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" @class(['dark' => ($appearance ?? 'system') == 'dark'])>
<head>
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-1864972736029501"
            crossorigin="anonymous"></script>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title inertia>{{ config('app.name', 'Laravel') }}</title>

    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="icon" href="{{ asset('favicon.svg') }}" sizes="any">
    <link rel="apple-touch-icon" href="{{ asset('favicon.svg') }}">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
@include('_partials._header')
    {{-- @vite(['resources/js/app.ts', "resources/js/pages/{$page['component']}.vue"]) --}}
    @vite('resources/js/app.ts')

    @inertiaHead
    @if(config('services.google_adsense.client'))
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client={{ config('services.google_adsense.client') }}" crossorigin="anonymous"></script>
    @endif
</head>
<body id="kt_app_body" data-kt-app-sidebar-enabled="true" data-kt-app-sidebar-fixed="true"
      data-kt-app-sidebar-push-header="true" data-kt-app-sidebar-push-toolbar="true"
      data-kt-app-sidebar-push-footer="true" class="app-default font-sans antialiased h-full">
@routes

<script>var defaultThemeMode = 'light';
    var themeMode;
    if (document.documentElement) {
        if (document.documentElement.hasAttribute('data-bs-theme-mode')) {
            themeMode = document.documentElement.getAttribute('data-bs-theme-mode');
        } else {
            if (localStorage.getItem('data-bs-theme') !== null) {
                themeMode = localStorage.getItem('data-bs-theme');
            } else {
                themeMode = defaultThemeMode;
            }
        }
        if (themeMode === 'system') {
            themeMode = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        }
        document.documentElement.setAttribute('data-bs-theme', themeMode);
    }</script>

@inertia
@include('_partials._scripts')
</body>
</html>
