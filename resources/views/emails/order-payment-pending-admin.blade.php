@extends('emails.layouts.transactional')

@section('title')
    {{ __('mail.admin_order_payment_pending.subject', ['id' => $order->id]) }}
@endsection

@section('content')
    <p>{{ __('mail.admin_order_payment_pending.intro', ['id' => $order->id]) }}</p>
    <p><strong>{{ __('mail.admin_order_payment_pending.client_email') }}</strong> {{ $order->client?->login_email ?? '' }}</p>
    <p><strong>{{ __('mail.admin_order_payment_pending.grand_total') }}:</strong> {{ number_format($grandTotal, 2, ',', '.') }} €</p>
    <p><strong>{{ __('mail.admin_order_payment_pending.order_status') }}:</strong> {{ $order->status ? __('shop.status.' . $order->status) : '' }}</p>
    <p style="margin: 24px 0; text-align: center;">
        <a href="{{ $adminUrl }}" style="display: inline-block; padding: 12px 20px; background: #F75211; color: #ffffff; text-decoration: none; border-radius: 8px; font-weight: 600;">{{ __('mail.admin_order_payment_pending.button') }}</a>
    </p>
@endsection
