@extends('emails.layouts.transactional')

@section('title')
    {{ __('mail.admin_order_installation_quote.subject', ['id' => $order->id]) }}
@endsection

@section('content')
    <p>{{ __('mail.admin_order_installation_quote.intro', ['id' => $order->id]) }}</p>
    <p><strong>{{ __('mail.admin_order_installation_quote.client_email') }}</strong> {{ $order->client?->login_email ?? '' }}</p>
    <p><strong>{{ __('mail.admin_order_installation_quote.grand_total') }}:</strong> {{ number_format($order->grand_total, 2, ',', '.') }} €</p>
    <p style="margin: 24px 0;">
        <a href="{{ $adminUrl }}" style="display: inline-block; padding: 12px 20px; background: #F75211; color: #ffffff; text-decoration: none; border-radius: 8px; font-weight: 600;">{{ __('mail.admin_order_installation_quote.button') }}</a>
    </p>
@endsection
