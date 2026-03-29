<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('mail.order_shipped.subject', ['id' => $order->id]) }}</title>
</head>
<body style="font-family: system-ui, sans-serif; line-height: 1.5; color: #1a1a1a;">
    <p>{{ __('mail.order_shipped.greeting') }}</p>
    <p>{{ __('mail.order_shipped.body', ['id' => $order->id]) }}</p>
    @if($shippingDate)
        <p><strong>{{ __('mail.order_shipped.shipping_date_label') }}:</strong> {{ $shippingDate->timezone(config('app.timezone'))->translatedFormat('d/m/Y') }}</p>
    @endif
    <p>{{ __('mail.order_shipped.cta') }}</p>
    <p><a href="{{ url('/orders/' . $order->id) }}" style="display: inline-block; padding: 10px 18px; background: #2563eb; color: #fff; text-decoration: none; border-radius: 6px;">{{ __('mail.order_shipped.button') }}</a></p>
    <p style="color: #666; font-size: 14px;">{{ __('mail.order_shipped.footer') }}</p>
</body>
</html>
