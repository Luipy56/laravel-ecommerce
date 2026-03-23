<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('mail.installation_price.subject', ['id' => $order->id]) }}</title>
</head>
<body style="font-family: system-ui, sans-serif; line-height: 1.5; color: #1a1a1a;">
    <p>{{ __('mail.installation_price.greeting') }}</p>
    <p>{{ __('mail.installation_price.body', ['id' => $order->id]) }}</p>
    <ul style="list-style: none; padding-left: 0;">
        <li><strong>{{ __('mail.installation_price.products_subtotal') }}:</strong> {{ number_format($linesSubtotal, 2, ',', '.') }} €</li>
        <li><strong>{{ __('mail.installation_price.installation_fee') }}:</strong> {{ number_format($installationPrice, 2, ',', '.') }} €</li>
        <li><strong>{{ __('mail.installation_price.total_due') }}:</strong> {{ number_format($grandTotal, 2, ',', '.') }} €</li>
    </ul>
    <p>{{ __('mail.installation_price.cta') }}</p>
    <p><a href="{{ url('/orders/' . $order->id) }}" style="display: inline-block; padding: 10px 18px; background: #2563eb; color: #fff; text-decoration: none; border-radius: 6px;">{{ __('mail.installation_price.button') }}</a></p>
    <p style="color: #666; font-size: 14px;">{{ __('mail.installation_price.footer') }}</p>
</body>
</html>
