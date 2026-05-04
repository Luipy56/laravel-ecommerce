@extends('emails.layouts.transactional')

@section('title')
    {{ __('mail.admin_return_request_received.subject', ['id' => $rma->order_id]) }}
@endsection

@section('content')
    <p>{{ __('mail.admin_return_request_received.intro', ['id' => $rma->order_id]) }}</p>
    <p><strong>{{ __('mail.admin_return_request_received.client_email') }}</strong> {{ $rma->order?->client?->login_email ?? '' }}</p>
    <p><strong>{{ __('mail.admin_return_request_received.reason') }}</strong></p>
    <p style="background:#f5f5f5;padding:12px;border-radius:6px;">{{ $rma->reason }}</p>
    <p style="margin: 24px 0;">
        <a href="{{ $adminUrl }}" style="display: inline-block; padding: 12px 20px; background: #F75211; color: #ffffff; text-decoration: none; border-radius: 8px; font-weight: 600;">{{ __('mail.admin_return_request_received.button') }}</a>
    </p>
@endsection
