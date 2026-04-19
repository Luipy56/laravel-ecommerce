@extends('emails.layouts.transactional')

@section('title')
    {{ __('mail.installation_price.subject', ['id' => $order->id]) }}
@endsection

@section('content')
    <p>{{ __('mail.installation_price.greeting') }}</p>
    <p>{{ __('mail.installation_price.body', ['id' => $order->id]) }}</p>
    <ul style="list-style: none; padding-left: 0;">
        <li><strong>{{ __('mail.installation_price.products_subtotal') }}:</strong> {{ number_format($linesSubtotal, 2, ',', '.') }} €</li>
        <li><strong>{{ __('mail.installation_price.installation_fee') }}:</strong> {{ number_format($installationPrice, 2, ',', '.') }} €</li>
        <li><strong>{{ __('mail.installation_price.total_due') }}:</strong> {{ number_format($grandTotal, 2, ',', '.') }} €</li>
    </ul>
    <p>{{ __('mail.installation_price.cta') }}</p>
    <p style="margin: 24px 0;">
        <a href="{{ url('/orders/' . $order->id) }}" style="display: inline-block; padding: 12px 20px; background: #F75211; color: #ffffff; text-decoration: none; border-radius: 8px; font-weight: 600;">{{ __('mail.installation_price.button') }}</a>
    </p>
@endsection
