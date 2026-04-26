<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title')</title>
</head>
<body style="margin: 0; padding: 0; font-family: system-ui, -apple-system, Segoe UI, sans-serif; line-height: 1.5; background-color: #f4f4f5; color: #18181b;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color: #f4f4f5; padding: 24px 12px;">
    <tr>
        <td align="center">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width: 600px; background: #ffffff; border-radius: 12px; overflow: hidden; border: 1px solid #e4e4e7;">
                <tr>
                    <td style="height: 4px; background: linear-gradient(90deg, #F75211 0%, #8B2400 100%);"></td>
                </tr>
                <tr>
                    <td style="padding: 28px 24px 16px;">
                        @php
                            $brandName = (string) config('mail.brand.display_name', 'Serralleria Solidària');
                            $envLogo = config('mail.brand.logo_url');
                            $logoUrl = $envLogo !== null && $envLogo !== '' ? (string) $envLogo : url('images/favicon.ico');
                        @endphp
                        <div style="text-align: center; margin: 0 0 8px;">
                            <img src="{{ $logoUrl }}" alt="" width="48" height="48" style="max-height: 48px; width: auto; height: auto; display: block; margin: 0 auto;">
                            <p style="margin: 10px 0 0; font-size: 18px; font-weight: 700; color: #18181b;">{{ $brandName }}</p>
                        </div>
                        <div style="font-size: 16px; color: #18181b;">
                            @yield('content')
                        </div>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 8px 24px 24px;">
                        @php($footerContact = config('mail.brand.footer_contact'))
                        @if(!empty($footerContact))
                            <p style="margin: 16px 0 8px; font-size: 13px; color: #52525b;">{{ $footerContact }}</p>
                        @endif
                        <p style="margin: 12px 0 0; font-size: 12px; color: #71717a;">
                            {{ __('mail.layout.footer_default', [], $mailLocale ?? null) }}
                        </p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
