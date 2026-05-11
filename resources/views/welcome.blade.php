<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="serralleria">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }}</title>
    <meta name="description" content="Materials i solucions de fusteria al teu abast. Compra online a {{ config('app.name') }}.">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ rtrim(config('app.url'), '/') }}">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ config('app.name') }}">
    <meta property="og:title" content="{{ config('app.name') }}">
    <meta property="og:description" content="Materials i solucions de fusteria al teu abast.">
    <meta property="og:url" content="{{ rtrim(config('app.url'), '/') }}">
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="{{ config('app.name') }}">
    <meta name="twitter:description" content="Materials i solucions de fusteria al teu abast.">
    <link rel="icon" type="image/x-icon" href="{{ asset('images/favicon.ico') }}">
    {{-- Runtime semver from Laravel (reads root package.json); avoids stale __APP_VERSION__ until Vite dev restart --}}
    <script>window.__LARAVEL_APP_VERSION__ = @json(config('app.version'));</script>
    @vite(['resources/css/app.css', 'resources/js/app.jsx'])
</head>
<body>
    <div id="root"></div>
</body>
</html>
