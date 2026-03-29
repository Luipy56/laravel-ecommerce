<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('mail.order_payment_confirmed.subject', ['id' => $order->id]) }}</title>
</head>
<body style="font-family: system-ui, sans-serif; line-height: 1.5; color: #1a1a1a;">
    <p>{{ __('mail.order_payment_confirmed.greeting') }}</p>
    <p>{{ __('mail.order_payment_confirmed.body', ['id' => $order->id]) }}</p>
    <p><strong>{{ __('mail.order_payment_confirmed.total_paid') }}:</strong> {{ number_format($grandTotal, 2, ',', '.') }} €</p>
    <p>{{ __('mail.order_payment_confirmed.cta') }}</p>
    <p><a href="{{ url('/orders/' . $order->id) }}" style="display: inline-block; padding: 10px 18px; background: #2563eb; color: #fff; text-decoration: none; border-radius: 6px;">{{ __('mail.order_payment_confirmed.button') }}</a></p>
    <p style="color: #666; font-size: 14px;">{{ __('mail.order_payment_confirmed.footer') }}</p>
</body>
</html>
