@extends('emails.layouts.transactional')

@section('title')
    {{ __('mail.order_payment_pending.subject', ['id' => $order->id]) }}
@endsection

@section('content')
    <p>{{ __('mail.order_payment_pending.greeting') }}</p>
    <p>{{ __('mail.order_payment_pending.body', ['id' => $order->id]) }}</p>
    <p><strong>{{ __('mail.order_payment_pending.total_due') }}:</strong> {{ number_format($grandTotal, 2, ',', '.') }} €</p>
    <p>{{ __('mail.order_payment_pending.cta') }}</p>
    <p style="margin: 24px 0;">
        <a href="{{ url('/orders/' . $order->id) }}" style="display: inline-block; padding: 12px 20px; background: #F75211; color: #ffffff; text-decoration: none; border-radius: 8px; font-weight: 600;">{{ __('mail.order_payment_pending.button') }}</a>
    </p>
    <p style="font-size: 14px; color: #52525b;">{{ __('mail.order_payment_pending.footer') }}</p>
@endsection
