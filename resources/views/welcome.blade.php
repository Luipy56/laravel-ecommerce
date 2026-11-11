<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="serralleria">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php
        /** @var \App\Support\ShareMeta|null $shareMeta */
        $meta = $shareMeta ?? null;
        $siteName = config('app.name');
        $title = $meta?->title ?? $siteName;
        $description = $meta?->description ?? __('seo.default_description', ['store' => $siteName]);
        $canonical = $meta?->canonicalUrl ?? url('/');
        $ogType = $meta?->ogType ?? 'website';
        $imageUrl = $meta?->imageUrl;
        $imageAlt = $meta?->imageAlt ?? $siteName;
        $twitterCard = $meta?->twitterCard() ?? ($imageUrl ? 'summary_large_image' : 'summary');
    @endphp
    <title>{{ $title }}</title>
    <meta name="description" content="{{ $description }}">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ $canonical }}">
    <meta property="og:type" content="{{ $ogType }}">
    <meta property="og:site_name" content="{{ $siteName }}">
    <meta property="og:title" content="{{ $title }}">
    <meta property="og:description" content="{{ $description }}">
    <meta property="og:url" content="{{ $canonical }}">
    @if($imageUrl)
    <meta property="og:image" content="{{ $imageUrl }}">
    <meta property="og:image:alt" content="{{ $imageAlt }}">
    @endif
    <meta name="twitter:card" content="{{ $twitterCard }}">
    <meta name="twitter:title" content="{{ $title }}">
    <meta name="twitter:description" content="{{ $description }}">
    @if($imageUrl)
    <meta name="twitter:image" content="{{ $imageUrl }}">
    @endif
    <link rel="icon" type="image/x-icon" href="{{ asset('images/favicon.ico') }}">
    {{-- Runtime semver from Laravel (reads root package.json); avoids stale __APP_VERSION__ until Vite dev restart --}}
    <script>window.__LARAVEL_APP_VERSION__ = @json(config('app.version'));</script>
    <script>window.__LARAVEL_ADMIN_AUTO_LOGIN__ = @json(config('app.admin_auto_login'));</script>
    @if (config('app.is_demo'))
    <script>window.__LARAVEL_IS_DEMO__ = true;</script>
    @endif
    @php
        $serraAnalyticsBoot = [
            'enabled' => (bool) config('analytics.enabled'),
            'gtm_container_id' => (string) config('analytics.gtm_container_id'),
            'ga4_measurement_id' => (string) config('analytics.ga4_measurement_id'),
        ];
    @endphp
    <script>
      window.__SERRA_ANALYTICS__ = @json($serraAnalyticsBoot);
      window.dataLayer = window.dataLayer || [];
      window.gtag = window.gtag || function () { window.dataLayer.push(arguments); };
      window.gtag('consent', 'default', {
        analytics_storage: 'denied',
        ad_storage: 'denied',
        ad_user_data: 'denied',
        ad_personalization: 'denied',
        wait_for_update: 500
      });
      try {
        var _c = JSON.parse(localStorage.getItem('cookie_consent_v2') || 'null');
        if (_c && _c.analytics) {
          window.gtag('consent', 'update', {
            analytics_storage: 'granted',
            ad_storage: 'denied',
            ad_user_data: 'denied',
            ad_personalization: 'denied'
          });
        }
      } catch (e) {}
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.jsx'])
</head>
<body>
    <div id="root"></div>
</body>
</html>
