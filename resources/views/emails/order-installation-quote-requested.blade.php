@extends('emails.layouts.transactional')

@section('title')
    {{ __('mail.order_installation_quote.subject', ['id' => $order->id]) }}
@endsection

@section('content')
    <p>{{ __('mail.order_installation_quote.greeting') }}</p>
    <p>{{ __('mail.order_installation_quote.body', ['id' => $order->id]) }}</p>
    <p>{{ __('mail.order_installation_quote.next_steps') }}</p>
    <p style="margin: 24px 0;">
        <a href="{{ url('/orders/' . $order->id) }}" style="display: inline-block; padding: 12px 20px; background: #F75211; color: #ffffff; text-decoration: none; border-radius: 8px; font-weight: 600;">{{ __('mail.order_installation_quote.button') }}</a>
    </p>
@endsection
