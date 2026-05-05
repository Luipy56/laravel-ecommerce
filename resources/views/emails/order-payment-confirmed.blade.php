@extends('emails.layouts.transactional')

@section('title')
    {{ __('mail.order_payment_confirmed.subject', ['id' => $order->id]) }}
@endsection

@section('content')
    <p>{{ __('mail.order_payment_confirmed.greeting') }}</p>
    <p>{{ __('mail.order_payment_confirmed.body', ['id' => $order->id]) }}</p>
    <p><strong>{{ __('mail.order_payment_confirmed.total_paid') }}:</strong> {{ number_format($grandTotal, 2, ',', '.') }} €</p>
    <p>{{ __('mail.order_payment_confirmed.cta') }}</p>
    <p style="margin: 24px 0; text-align: center;">
        <a href="{{ url('/orders/' . $order->id) }}" style="display: inline-block; padding: 12px 20px; background: #F75211; color: #ffffff; text-decoration: none; border-radius: 8px; font-weight: 600;">{{ __('mail.order_payment_confirmed.button') }}</a>
    </p>
@endsection
