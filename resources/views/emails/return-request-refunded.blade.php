@extends('emails.layouts.transactional')

@section('title')
    {{ __('mail.return_request_refunded.subject', ['id' => $rma->order_id]) }}
@endsection

@section('content')
    <p>{{ __('mail.return_request_refunded.greeting') }}</p>
    <p>{{ __('mail.return_request_refunded.body', ['id' => $rma->order_id]) }}</p>
    @if($rma->refund_amount !== null)
        <p><strong>{{ __('mail.return_request_refunded.amount_label') }}:</strong> {{ number_format((float)$rma->refund_amount, 2, ',', '.') }} €</p>
    @endif
    <p>{{ __('mail.return_request_refunded.cta') }}</p>
    <p style="margin: 24px 0;">
        <a href="{{ $orderUrl }}" style="display: inline-block; padding: 12px 20px; background: #F75211; color: #ffffff; text-decoration: none; border-radius: 8px; font-weight: 600;">{{ __('mail.return_request_refunded.button') }}</a>
    </p>
@endsection
