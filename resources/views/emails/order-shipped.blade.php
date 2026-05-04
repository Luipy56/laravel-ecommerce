@extends('emails.layouts.transactional')

@section('title')
    {{ __('mail.order_shipped.subject', ['id' => $order->id]) }}
@endsection

@section('content')
    <p>{{ __('mail.order_shipped.greeting') }}</p>
    <p>{{ __('mail.order_shipped.body', ['id' => $order->id]) }}</p>
    @if(!empty($deliveryEstimateKey))
        <p>{{ __('mail.order_shipped.delivery_estimate_' . $deliveryEstimateKey) }}</p>
    @endif
    @if($shippingDate)
        <p><strong>{{ __('mail.order_shipped.shipping_date_label') }}:</strong> {{ $shippingDate->timezone(config('app.timezone'))->translatedFormat('d/m/Y') }}</p>
    @endif
    <p>{{ __('mail.order_shipped.cta') }}</p>
    <p style="margin: 24px 0; text-align: center;">
        <a href="{{ url('/orders/' . $order->id) }}" style="display: inline-block; padding: 12px 20px; background: #F75211; color: #ffffff; text-decoration: none; border-radius: 8px; font-weight: 600;">{{ __('mail.order_shipped.button') }}</a>
    </p>
@endsection
