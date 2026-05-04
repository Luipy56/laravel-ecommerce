@extends('emails.layouts.transactional')

@section('title')
    {{ __('mail.return_request_approved.subject', ['id' => $rma->order_id]) }}
@endsection

@section('content')
    <p>{{ __('mail.return_request_approved.greeting') }}</p>
    <p>{{ __('mail.return_request_approved.body', ['id' => $rma->order_id]) }}</p>
    @if($rma->admin_notes)
        <p><strong>{{ __('mail.return_request_approved.notes_label') }}</strong></p>
        <p style="background:#f5f5f5;padding:12px;border-radius:6px;">{{ $rma->admin_notes }}</p>
    @endif
    <p>{{ __('mail.return_request_approved.cta') }}</p>
    <p style="margin: 24px 0;">
        <a href="{{ $orderUrl }}" style="display: inline-block; padding: 12px 20px; background: #F75211; color: #ffffff; text-decoration: none; border-radius: 8px; font-weight: 600;">{{ __('mail.return_request_approved.button') }}</a>
    </p>
@endsection
